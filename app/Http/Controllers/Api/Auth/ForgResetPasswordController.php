<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;

class ForgResetPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['whatsapp_number' => 'required']);

        $waNumber = $request->whatsapp_number;
        $waNumber = preg_replace('/[^0-9]/', '', $waNumber);

        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '62')) {
            $waNumber = '62' . $waNumber;
        }

        $user = User::where('whatsapp_number', $waNumber)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor WA belum terdaftar cuy!'
            ], 404);
        }

        // Generate Token acak & simpan di Cache (valid 15 menit)
        $token = Str::random(40);
        Cache::put('reset_token_' . $waNumber, $token, now()->addMinutes(15));

        // Bikin URL Link Reset (Arahin ke halaman blade reset password)
        $resetLink = url("/reset-password?wa={$waNumber}&token={$token}");


        $pesan = "Halo *{$user->name}*! 👋\n\n"
            . "Ada yang minta reset password buat akun Raya Kitchen kamu nih.\n\n"
            . "Klik link di bawah buat bikin password baru:\n\n"
            . "{$resetLink}\n\n"
            . "Link ini cuma berlaku 15 menit ya. Kalau bukan kamu yang minta, cuekin aja pesan ini.";

        try {
            $target = $waNumber;

            $randomDelay = rand(2, 6);

            // Tembak API Fonnte
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $pesan,
                'delay' => (string) $randomDelay,
                'typing' => true,
            ]);

            // Cek kalau API Fonnte ngasih pesan gagal
            if (!$response->successful() || $response->json('status') == false) {
                Log::error('Fonnte Error: ' . $response->body());
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal kirim WA bjir: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Link reset berhasil dikirim! Cek WA kamu ya.'
        ]);
    }

    // Fungsi 2: Simpan Password Baru
    public function resetPassword(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $waNumber = $request->whatsapp_number;
        $cachedToken = Cache::get('reset_token_' . $waNumber);

        // Validasi Token
        if (!$cachedToken || $cachedToken !== $request->token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Link reset tidak valid atau udah kadaluarsa bjir!'
            ], 400);
        }

        $user = User::where('whatsapp_number', $waNumber)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User nggak ketemu!'], 404);
        }

        // Update password baru & hapus token dari cache
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        Cache::forget('reset_token_' . $waNumber);

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah!'
        ]);
    }
}
