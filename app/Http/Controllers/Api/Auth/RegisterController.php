<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;

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

        if (User::where('whatsapp_number', $waNumber)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor WhatsApp udah kepake, pake nomor lain atau langsung login aja!'
            ], 422);
        }

        // 4. Generate OTP 6 Digit & Simpan di Cache (valid 5 menit)
        $otp = rand(100000, 999999);
        Cache::put('otp_' . $waNumber, $otp, now()->addMinutes(5));

        // 5. Simpan Data Registrasi ke Cache (Biar user dibikin SETELAH OTP bener)
        Cache::put('register_data_' . $waNumber, [
            'name' => $request->name,
            'whatsapp_number' => $waNumber,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
        ], now()->addMinutes(5));

        $pesan = "Halo *{$request->name}*! 👋\n\nSelamat datang di *Raya Kitchen*!\nIni kode OTP buat verifikasi pendaftaran mu:\n\n*{$otp}*\n\nKode ini hanya berlaku 5 menit ya. Jangan kasih tau siapa-siapa!";

        try {
            $waId = $waNumber . '@c.us';

            WhatsApp::web('bot_bakery')->messages()->sendText($waId, $pesan);

        } catch (\Throwable $e) {
            Log::error('WA Error: ' . $e->getMessage());
            // Tetep return success ke UI pindah ke halaman OTP
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP berhasil dikirim cuy!',
            'whatsapp_number' => $waNumber // Dibawa buat dikirim ke form OTP nanti
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required',
            'otp' => 'required|digits:6',
        ]);

        $waNumber =$request->whatsapp_number;

        // 1. Ambil OTP dari Cache (Bukan dari Database!)
        $cachedOtp = Cache::get('otp_' .$waNumber);

        // Kalau cache-nya kosong, berarti udah lewat 5 menit atau nomor salah
        if (!$cachedOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP udah kadaluarsa atau tidak valid, silakan daftar ulang!'
            ], 400);
        }

        // 2. Cocokin OTP dari form sama yang ada di Cache
        if ($cachedOtp !=$request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP salah, coba cek lagi cuy!'
            ], 400);
        }

        // 3. OTP Bener! Sekarang ambil data registrasi dari Cache
        $registerData = Cache::get('register_data_' .$waNumber);

        if (!$registerData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pendaftaran kadaluarsa, silakan daftar ulang dari awal.'
            ], 400);
        }

        // 4. Masukin datanya ke table `users` SEKARANG!
        $user = User::create([
            'name' => $registerData['name'],
            'whatsapp_number' => $registerData['whatsapp_number'],
            'password' => $registerData['password'],
            'role' => $registerData['role'],
            'email_verified_at' => now(), 
        ]);

        // 5. Bersihin sampah di Cache biar OTP-nya ga bisa dipake 2x
        Cache::forget('otp_' . $waNumber);
        Cache::forget('register_data_' . $waNumber);

        // 6. Auto login user-nya
        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil diverifikasi dan dibuat!'
        ]);
    }
}
