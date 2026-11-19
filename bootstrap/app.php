<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Daftarkan alias middleware custom
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Kecualikan endpoint API dari CSRF agar client Mobile/External (Bearer Token) tetap jalan mulus
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Redirect jika user BELUM login saat membuka halaman Web terproteksi
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            return route('login');
        });

        // Redirect jika user SUDAH login tapi membuka halaman guest (login/register)
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->user()?->role === 'admin') {
                return route('admin.dashboard-finansial');
            }

            return route('katalog');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
