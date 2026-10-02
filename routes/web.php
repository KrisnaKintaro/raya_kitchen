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

Route::get('/produk/{slug}', function ($slug) {
    return view('pembeli.pages.produk.detail_produk');
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
// Gw bikinin dummy routes-nya dulu biar link di navbar/footer lu nggak error 404.

Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('auth.pembeli.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.pembeli.register');
    })->name('register');
});

// Route Logout (Harus POST sesuai standar keamanan Laravel)
Route::post('/logout', function () {
    // Nanti diganti Auth::logout() di controller
    return redirect('/');
})->name('logout');

