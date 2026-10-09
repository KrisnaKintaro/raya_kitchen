<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogInOutController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required',
            'password' => 'required',
        ]);

        $waNumber = $request->whatsapp_number;
        $waNumber = preg_replace('/[^0-9]/', '', $waNumber);

        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '62')) {
            $waNumber = '62' . $waNumber;
        }

        // 3. Eksekusi Login ngecek ke tabel users
        if (Auth::attempt(['whatsapp_number' => $waNumber, 'password' => $request->password], $request->remember)) {

            // 4. Setup Session buat Web (Blade)
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil cuy!',
                'token' => $token,
                'data' => $user
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Nomor WA atau Password salah!'
        ], 401);
    }

    public function logout(Request $request)
    {
        if ($user = $request->user()) {
            $token = $user->currentAccessToken();

            // Cek apakah token punya method delete (Bukan TransientToken)
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kalau request dari AJAX/API, balikin JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil logout dari semua platform cuy!'
            ]);
        }

        // Kalau dari form biasa, lempar ke home
        return redirect('/')->with('success', 'Berhasil logout!');
    }
}
