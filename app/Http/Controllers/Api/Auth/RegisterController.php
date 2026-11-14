<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $waNumber = $request->whatsapp_number;
        $waNumber = preg_replace('/[^0-9]/', '', $waNumber);
        if (substr($waNumber, 0, 1) == '0') {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (substr($waNumber, 0, 2) != '62') {
            $waNumber = '62' . $waNumber;
        }

        // Cek apakah nomor WA sudah ada di database
        $existingUser = User::where('whatsapp_number', $waNumber)->first();
        $defaultBotName = 'Pelanggan ' . substr($waNumber, -4);

        // Tolak HANYA JIKA user sudah ada DAN bukan akun sementara buatan Bot WA
        if ($existingUser && $existingUser->name !== $defaultBotName) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor WhatsApp udah kepake, pake nomor lain atau langsung login aja!'
            ], 422);
        }

        $otp = rand(100000, 999999);
        Cache::put('otp_' . $waNumber, $otp, now()->addMinutes(5));
        Cache::put('otp_cooldown_' . $waNumber, true, now()->addSeconds(60));

        Cache::put('register_data_' . $waNumber, [
            'name' => $request->name,
            'whatsapp_number' => $waNumber,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
        ], now()->addMinutes(5));

        $pesan = "Halo *{$request->name}*! 👋\n\nSelamat datang di *Raya Kitchen*!\nIni kode OTP buat verifikasi pendaftaran mu:\n\n*{$otp}*\n\nKode ini hanya berlaku 5 menit ya. Jangan kasih tau siapa-siapa!";

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

            if (!$response->successful() || $response->json('status') == false) {
                Log::error('Fonnte Error: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('WA Error: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP berhasil dikirim cuy!',
            'whatsapp_number' => $waNumber
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required',
            'otp' => 'required|digits:6',
        ]);

        $waNumber = preg_replace('/[^0-9]/', '', $request->whatsapp_number);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '62')) {
            $waNumber = '62' . $waNumber;
        }

        // 1. Ambil OTP dari Cache
        $cachedOtp = Cache::get('otp_' . $waNumber);

        if (!$cachedOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP udah kadaluarsa atau tidak valid, silakan daftar ulang!'
            ], 400);
        }

        // 2. Cocokkan OTP dari form dengan yang ada di Cache
        if ($cachedOtp != $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP salah, coba cek lagi cuy!'
            ], 400);
        }

        // 3. Ambil data registrasi dari Cache
        $registerData = Cache::get('register_data_' . $waNumber);

        if (!$registerData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pendaftaran kadaluarsa, silakan daftar ulang dari awal.'
            ], 400);
        }

        // 4. Gunakan updateOrCreate agar jika sebelumnya sudah ada akun dari WA Bot,
        // datanya di-update menjadi akun rill tanpa merusak relasi user_id di tabel lain
        $user = User::updateOrCreate(
            ['whatsapp_number' => $registerData['whatsapp_number']],
            [
                'name' => $registerData['name'],
                'password' => $registerData['password'],
                'role' => $registerData['role'],
            ]
        );

        // 5. Bersihkan Cache OTP
        Cache::forget('otp_' . $waNumber);
        Cache::forget('register_data_' . $waNumber);
        Cache::forget('otp_cooldown_' . $waNumber);

        Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil diverifikasi dan dibuat!',
            'token' => $token,
            'data' => $user
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
        ]);

        $waNumber = preg_replace('/[^0-9]/', '', $request->whatsapp_number);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '62')) {
            $waNumber = '62' . $waNumber;
        }

        // 1. Cek apakah sesi pendaftaran masih ada
        $registerData = Cache::get('register_data_' . $waNumber);

        if (!$registerData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi pendaftaranmu udah habis, silakan isi form daftar ulang dari awal ya.'
            ], 400);
        }

        // 2. Cek Perlindungan Spam (Cooldown 60 Detik)
        if (Cache::has('otp_cooldown_' . $waNumber)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sabar ya, tunggu 60 detik dulu sebelum minta kode OTP baru!'
            ], 429);
        }

        // 3. Generate OTP Baru & Perbarui Cache
        $newOtp = rand(100000, 999999);
        Cache::put('otp_' . $waNumber, $newOtp, now()->addMinutes(5));
        Cache::put('otp_cooldown_' . $waNumber, true, now()->addSeconds(60));

        // 4. Kirim pesan WA via Fonnte
        $pesan = "Halo *{$registerData['name']}*! 👋\n\nIni kode OTP *BARU* buat verifikasi pendaftaranmu:\n\n*{$newOtp}*\n\nKode ini berlaku 5 menit ya.";

        try {
            $randomDelay = rand(2, 6);
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $waNumber,
                'message' => $pesan,
                'delay' => (string) $randomDelay,
                'typing' => true,
            ]);

            if (!$response->successful() || $response->json('status') == false) {
                Log::error('Fonnte Error Resend OTP: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('WA Error Resend OTP: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sip! Kode OTP baru udah dikirim ke WA kamu.'
        ], 200);
    }
}   
