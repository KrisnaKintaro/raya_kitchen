<?php

use App\Http\Controllers\Api\Pembeli\CheckoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Pembeli\ProfileController;

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
// taruh semua route tampilan yang baru bisa diakses jika sudah login disini
// ==========================================
Route::middleware(['auth'])->group(function () {

    Route::get('/keranjang', function () {
        return view('pembeli.pages.keranjang');
    })->name('keranjang');

    // ==========================================
    // PERUBAHAN DI SINI UNTUK PESANAN & CHECKOUT
    // ==========================================

    // Halaman Staging Pembayaran
    Route::get('/pembayaran/{order_number}', [CheckoutController::class, 'showPembayaran'])->name('pembayaran');

    // ==========================================
    // PERUBAHAN DI SINI UNTUK PROFIL
    // ==========================================

    // Route HANYA untuk menampilkan file Blade (Halaman visual)
    // Halaman Profil Utama (Hanya Informasi Akun)
    Route::get('/profil', function () {
        return view('pembeli.pages.profil');
    })->name('profil');

    // Halaman Kelola Alamat Terpisah
    Route::get('/alamat', function () {
        return view('pembeli.pages.alamat');
    })->name('alamat');

    // 2. Route untuk mengambil dan menyimpan DATA berformat JSON ke ProfileController

    // Halaman Transaksi Pembeli (Checkout, Nota, Riwayat)
    Route::get('/checkout', function () {
        return view('pembeli.pages.checkout');
    })->name('checkout');

    // Nota diubah mengarah ke CheckoutController agar data dari database tampil secara dinamis
    Route::get('/nota', [CheckoutController::class, 'showNota'])->name('nota');

    Route::get('/riwayat-pesanan', function () {
        return view('pembeli.pages.riwayat_pesanan');
    })->name('riwayat-pesanan');

});


// ==========================================
// AUTH ROUTES (Login, Register, dll). Nanti saja untuk ini
// ==========================================
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

    Route::post('/logout', function () {
        return redirect('/');
    })->name('logout');
});
Route::post('/checkout/alamat-ajax', [\App\Http\Controllers\Api\Pembeli\CheckoutController::class, 'storeAddressAjax'])->name('alamat.ajax');
