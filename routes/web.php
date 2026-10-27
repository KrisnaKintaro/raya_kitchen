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
    // Controller web.php hanya kirim ID ke view, AJAX yang ngurus datanya
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

    // Halaman Staging Pembayaran (Full AJAX)
    Route::get('/pembayaran/{order_number}', function ($order_number) {
        return view('pembeli.pages.pembayaran');
    })->name('pembayaran');

    // Halaman Profil Utama
    Route::get('/profil', function () {
        return view('pembeli.pages.profil');
    })->name('profil');

    // Halaman Kelola Alamat Terpisah
    Route::get('/alamat', function () {
        return view('pembeli.pages.alamat');
    })->name('alamat');

    // Halaman Checkout
    Route::get('/checkout', function () {
        return view('pembeli.pages.checkout');
    })->name('checkout');

    // Halaman Nota (Full AJAX, ambil order_number dari URL query parameter via JS)
    Route::get('/nota', function () {
        return view('pembeli.pages.nota');
    })->name('nota');

    // Halaman Riwayat Pesanan
    Route::get('/riwayat-pesanan', function () {
        return view('pembeli.pages.riwayat_pesanan');
    })->name('riwayat-pesanan');

});


// ==========================================
// AUTH ROUTES (Login, Register, dll)
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
});

// Logout harus pakai POST demi keamanan, arahin ke halaman utama setelahnya
Route::post('/logout', function () {
    return redirect('/');
})->name('logout')->middleware('auth');

    Route::post('/logout', function () {
        return redirect('/');
    })->name('logout');
});


// ==========================================
// ADMIN ROUTES - KELOLA AKUN (Tugas Kamu)
// ==========================================
Route::prefix('admin')->group(function () {
    
    // Halaman Kelola Akses (Admin & Staf)
    Route::get('/kelola-akses', function () {
        return view('admin.pages.kelola_akun.akses');
    })->name('admin.kelola-akses');

    // Halaman Daftar Pelanggan yang Registrasi
    Route::get('/pelanggan', function () {
        return view('admin.pages.kelola_akun.pelanggan');
    })->name('admin.pelanggan');

});
// ==========================================
// ROUTES UNTUK ADMIN (Hanya bisa diakses user dengan role admin)
// ==========================================
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/ringkasan_finansial', function () {
        return view('admin.pages.finansial.ringkasan_finansial');
    })->name('admin.ringkasan-finansial');

    Route::get('/riwayat_penjualan', function () {
        return view('admin.pages.finansial.riwayat_penjualan');
    })->name('admin.riwayat-penjualan');
});
