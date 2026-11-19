<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Pembeli\NotificationController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AdminAuthController extends Controller
{
    /**
     * Helper normalisasi nomor WhatsApp ke format 62xxxxxxxxx
     */
    private function normalizeWaNumber(string $number): string
    {
        $waNumber = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($waNumber, '0')) {
            return '62' . substr($waNumber, 1);
        }

        if (!str_starts_with($waNumber, '62')) {
            return '62' . $waNumber;
        }

        return $waNumber;
    }

    /**
     * Format data user admin agar seragam di semua platform (Web / Mobile / API)
     */
    private function formatAdminData(User $admin): array
    {
        return [
            'id'              => $admin->id,
            'name'            => $admin->name,
            'whatsapp_number' => $admin->whatsapp_number,
            'role'            => $admin->role,
            'profile_photo'   => $admin->profile_photo,
        ];
    }

    /**
     * 1. Login Universal Khusus Admin (Session Web untuk Blade + Bearer Token Sanctum untuk Mobile/External)
     */
    public function login(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
            'password'        => 'required|string',
            'remember'        => 'nullable|boolean',
            'device_name'     => 'nullable|string|max:100',
        ]);

        $waNumber = $this->normalizeWaNumber($request->whatsapp_number);

        $admin = User::where('whatsapp_number', $waNumber)
            ->where('role', 'admin')
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nomor WA atau password salah, atau akun tidak memiliki akses admin.',
            ], 401);
        }

        // Tulis Session Web HANYA jika request membawa session (Laravel Blade / Browser)
        if ($request->hasSession()) {
            Auth::guard('web')->login($admin, $request->boolean('remember'));
            $request->session()->regenerate();
        }

        // Terbitkan Sanctum Token dengan ability khusus admin (dipakai Mobile / API client)
        $deviceName = $request->input('device_name', 'admin_auth_token');
        $token = $admin->createToken($deviceName, ['role:admin'])->plainTextToken;

        return response()->json([
            'status'     => 'success',
            'message'    => 'Login admin berhasil!',
            'token_type' => 'Bearer',
            'token'      => $token,
            'redirect'   => route('admin.dashboard-finansial'),
            'data'       => $this->formatAdminData($admin),
        ], 200);
    }

    /**
     * 2. Cek Profil Admin yang Sedang Login (Support Session & Bearer Token)
     */
    public function me(Request $request)
    {
        /** @var \App\Models\User|null $admin */
        $admin = $request->user();

        if (!$admin || $admin->role !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak. Hanya untuk akun admin.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatAdminData($admin),
        ], 200);
    }

    /**
     * 3. Logout Universal Admin (Hapus Bearer Token Sanctum & Invalidate Session Blade jika ada)
     */
    public function logout(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user) {
            if ($request->boolean('logout_all')) {
                // Opsional: keluar dari semua perangkat/token sekaligus
                $user->tokens()->delete();
            } else {
                $token = $user->currentAccessToken();

                // Hapus jika autentikasi menggunakan PersonalAccessToken (bukan TransientToken session)
                if ($token instanceof PersonalAccessToken) {
                    $token->delete();
                }
            }
        }

        // Bersihkan Session Web jika request berasal dari browser/Blade
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Berhasil logout dari panel admin.',
                'redirect' => route('admin.login'),
            ], 200);
        }

        return redirect()->route('admin.login')->with('success', 'Berhasil logout dari panel admin.');
    }

    /**
     * 4. Kirim Link Reset Password Khusus Admin via WhatsApp (Fonnte)
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
        ]);

        $waNumber = $this->normalizeWaNumber($request->whatsapp_number);

        $admin = User::where('whatsapp_number', $waNumber)
            ->where('role', 'admin')
            ->first();

        if (!$admin) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nomor WA tidak terdaftar sebagai akun admin.',
            ], 404);
        }

        // Anti-spam cooldown 60 detik
        $cooldownKey = 'admin_reset_cooldown_' . $waNumber;
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tunggu 60 detik sebelum meminta link reset password lagi.',
            ], 429);
        }

        $token = Str::random(40);
        Cache::put('admin_reset_token_' . $waNumber, $token, now()->addMinutes(15));
        Cache::put($cooldownKey, true, now()->addSeconds(60));

        $resetLink = url("/admin/reset-password?wa={$waNumber}&token={$token}");

        $pesan = NotificationController::getTemplateMessage('auth_reset_password', [
            'nama'       => $admin->name,
            'link_reset' => $resetLink,
        ]);

        try {
            $randomDelay = rand(2, 6);

            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target'  => $waNumber,
                'message' => $pesan,
                'delay'   => (string) $randomDelay,
                'typing'  => true,
            ]);

            if (!$response->successful() || $response->json('status') == false) {
                Log::error('Fonnte Admin Reset Error: ' . $response->body());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal mengirim pesan WhatsApp. Coba beberapa saat lagi.',
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('WA Admin Reset Exception: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat menghubungi layanan WhatsApp.',
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Link reset password admin berhasil dikirim! Cek WhatsApp kamu.',
        ], 200);
    }

    /**
     * 5. Simpan Password Baru Admin & Cabut Semua Token Lama
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
            'token'           => 'required|string',
            'password'        => 'required|string|min:6|confirmed',
        ]);

        $waNumber = $this->normalizeWaNumber($request->whatsapp_number);
        $cacheKey = 'admin_reset_token_' . $waNumber;
        $cachedToken = Cache::get($cacheKey);

        if (!$cachedToken || !hash_equals($cachedToken, $request->token)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Link reset tidak valid atau sudah kadaluarsa.',
            ], 400);
        }

        $admin = User::where('whatsapp_number', $waNumber)
            ->where('role', 'admin')
            ->first();

        if (!$admin) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun admin tidak ditemukan.',
            ], 404);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        // Cabut semua token Sanctum lama agar sesi di perangkat lain ikut keluar
        $admin->tokens()->delete();

        Cache::forget($cacheKey);
        Cache::forget('admin_reset_cooldown_' . $waNumber);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Password admin berhasil diubah! Silakan login kembali.',
            'redirect' => route('admin.login'),
        ], 200);
    }
}
