<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC ROUTES (Langsung ke Index/Katalog)
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

Route::get('/pembayaran/{order_number}', function ($order_number) {
    return view('pembeli.pages.pembayaran');
})->name('pembayaran');

Route::get('/nota', function (Request $request) {
    return view('pembeli.pages.nota');
})->name('nota');

Route::get('/pin-lokasi/{id}', function (Request $request, $id) {
    $wa = $request->query('wa');
    return view('pembeli.pages.pin_lokasi', compact('id', 'wa'));
})->name('pin-lokasi');


// ==========================================
// PROTECTED ROUTES (Hanya buat user yang udah Login)
// ==========================================
Route::middleware(['auth'])->group(function () {

    Route::get('/keranjang', function () {
        return view('pembeli.pages.keranjang');
    })->name('keranjang');

    Route::get('/checkout', function () {
        return view('pembeli.pages.checkout');
    })->name('checkout');

    Route::get('/profil', function () {
        return view('pembeli.pages.profil');
    })->name('profil');

    // Tambahkan rute alamat di sini dan beri name('alamat')
    Route::get('/alamat', function () {
        return view('pembeli.pages.alamat');
    })->name('alamat');

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


// ==========================================
// ROUTES UNTUK ADMIN
// ==========================================
Route::prefix('admin')->group(function () {

    // Auth Admin
    Route::get('/login', function () {
        return view('auth.admin.login');
    })->name('admin.login');

    Route::get('/lupa-password', function () {
        return view('auth.admin.forget_password');
    })->name('admin.password.request');

    Route::get('/reset-password', function () {
        return view('auth.admin.reset_password');
    })->name('admin.password.reset');

    // Halaman Admin
    Route::get('/ringkasan_finansial', function () {
        return view('admin.pages.finansial.ringkasan_finansial');
    })->name('admin.ringkasan-finansial');

    Route::get('/riwayat_penjualan', function () {
        return view('admin.pages.finansial.riwayat_penjualan');
    })->name('admin.riwayat-penjualan');

    Route::get('/profil_perusahaan', function () {
        return view('admin.pages.sistem_dan_toko.profil_perusahaan');
    })->name('admin.profil');

    Route::get('/operasional_libur', function () {
        return view('admin.pages.sistem_dan_toko.operasional_libur');
    })->name('admin.operasional');

        Route::get('/kelola-akses', function () {
        return view('admin.pages.kelola_akun.akses');
    })->name('admin.kelola-akses');

    Route::get('/pelanggan', function () {
        return view('admin.pages.kelola_akun.pelanggan');
    })->name('admin.pelanggan');

    Route::get('/template_pesan', function () {
        return view('admin.pages.whatsapp_bot.template_pesan');
    })->name('admin.template-pesan');

    Route::get('/katalog_varian', function () {
        return view('admin.pages.produk_resep.katalog_varian');
    })->name('admin.katalog-varian');

    Route::get('/resep_digital', function () {
        return view('admin.pages.produk_resep.resep_digital');
    })->name('admin.resep-digital');

    Route::get('/pantauan_dapur', function () {
        return view('admin.pages.operasional.pantauan_dapur');
    })->name('admin.pantauan-dapur');

    Route::get('/daftar_transaksi', function() {
        return view('admin.pages.operasional.daftar_transaksi');
    })->name('admin.daftar-transaksi');

    Route::get('/refund_pembatalan', function() {
        return view('admin.pages.operasional.batal_refund');
    })->name('admin.refund_pembatalan');

    Route::get('/kelola_pengiriman', function () {
        return view('admin.pages.operasional.kelola_pengiriman');
    })->name('admin.kelola_pengiriman');
});
