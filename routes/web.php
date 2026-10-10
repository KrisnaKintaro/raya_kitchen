<?php

use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC ROUTES (Bisa diakses siapa aja)
// ==========================================

Route::get('/', function () {
    return view('pembeli.pages.produk.katalog');
})->name('katalog');

Route::get('/katalogProduk', function(){
    return view('pembeli.pages.produk.katalog');
});

Route::get('/produk/{id}', function ($id) {
    return view('pembeli.pages.produk.detail_produk', compact('id'));
})->name('produk.detail');

Route::get('/tentang-kami', function () {
    return view('pembeli.pages.tentang_kami');
})->name('tentang-kami');


// ==========================================
// PROTECTED ROUTES (Hanya buat user yang udah Login)
// ==========================================
Route::middleware(['auth'])->group(function () {

    Route::get('/keranjang', function () {
        return view('pembeli.pages.keranjang');
    })->name('keranjang');

    Route::get('/pesanan', function () {
        return view('pembeli.pages.pesanan');
    })->name('pesanan');

    Route::get('/profil', function () {
        return view('pembeli.pages.profil');
    })->name('profil');

});


// ==========================================
// AUTH ROUTES (Login, Register, dll). Nani saja untuk ini
// ==========================================
// Nanti ini bakal fiisi pakai Controller khusus Auth lu.

Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('auth.pembeli.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.pembeli.register');
    })->name('register');

    Route::get('/otp', function () {
        return view('auth.pembeli.otp');
    })->name('otp');

    Route::get('/forgot-password', function () {
        return view('auth.pembeli.forgot_password');
    })->name('password.request');

    Route::get('/reset-password', function () {
        return view('auth.pembeli.reset_password');
    })->name('password.reset');
});

// Route Logout (Harus POST sesuai standar keamanan Laravel)
Route::post('/logout', function () {
    // Nanti diganti Auth::logout() di controller
    return redirect('/');
})->name('logout');

Route::get('/checkout', function () {
    return view('pembeli.pages.checkout');
});

Route::get('/nota', function () {
    return view('pembeli.pages.nota');
});
