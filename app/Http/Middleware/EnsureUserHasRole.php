<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $isApiRequest = $request->is('api/*') || $request->expectsJson();

        // 1. Jika belum terautentikasi
        if (!$user) {
            if ($isApiRequest) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                ], 401);
            }

            return ($request->is('admin') || $request->is('admin/*'))
                ? redirect()->route('admin.login')
                : redirect()->route('login');
        }

        // 2. Cek kecocokan role user di database
        if (!in_array($user->role, $roles, true)) {
            if ($isApiRequest) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak. Akun Anda tidak memiliki izin untuk mengakses resource ini.',
                ], 403);
            }

            // Jika user sudah login tapi salah kamar di Web, arahkan ke beranda role masing-masing
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard-finansial');
            }

            return redirect()->route('katalog');
        }

        // 3. Validasi tambahan khusus Bearer Token Sanctum (Mobile / API Client)
        // Memastikan token pembeli tidak bisa dipakai untuk endpoint admin
        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken && in_array('admin', $roles, true)) {
            if (!$user->tokenCan('role:admin') && !$user->tokenCan('*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Token tidak memiliki izin akses admin.',
                ], 403);
            }
        }

        return $next($request);
    }
}
