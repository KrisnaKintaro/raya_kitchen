<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckStoreOpen; // Wajib di-import biar middleware jalan

// ==========================================
// HALAMAN TOKO TUTUP (Tanpa Middleware biar gak looping)
// ==========================================
Route::get('/toko-tutup', function () {
    return view('pembeli.pages.toko_tutup');
})->name('toko.tutup');


// ==========================================
// PUBLIC ROUTES (Bisa diakses siapa aja)
// ==========================================

// BUNGKUS PAKAI MIDDLEWARE: Rute yang diblokir kalau toko tutup
Route::middleware([CheckStoreOpen::class])->group(function () {
    Route::get('/', function () {
        return view('pembeli.pages.produk.katalog');
    })->name('katalog');

    Route::get('/katalogProduk', function(){
        return view('pembeli.pages.produk.katalog');
    });

    Route::get('/produk/{id}', function ($id) {
        return view('pembeli.pages.produk.detail_produk', compact('id'));
    })->name('produk.detail');
});

// TIDAK DIBUNGKUS: Tetap bisa diakses 24 jam
Route::get('/tentang-kami', function () {
    return view('pembeli.pages.tentang_kami');
})->name('tentang-kami');


// ==========================================
// PROTECTED ROUTES (Hanya buat user yang udah Login)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // BUNGKUS PAKAI MIDDLEWARE: Transaksi diblokir kalau toko tutup
    Route::middleware([CheckStoreOpen::class])->group(function () {
        Route::get('/keranjang', function () {
            return view('pembeli.pages.keranjang');
        })->name('keranjang');

        Route::get('/checkout', function () {
            return view('pembeli.pages.checkout');
        })->name('checkout');

        Route::get('/pembayaran/{order_number}', function ($order_number) {
            return view('pembeli.pages.pembayaran');
        })->name('pembayaran');
    });

    // TIDAK DIBUNGKUS: Fitur profil tetap jalan 24 jam
    Route::get('/profil', function () {
        return view('pembeli.pages.profil');
    })->name('profil');

    Route::get('/alamat', function () {
        return view('pembeli.pages.alamat');
    })->name('alamat');

    Route::get('/nota', function () {
        return view('pembeli.pages.nota');
    })->name('nota');

    Route::get('/riwayat-pesanan', function () {
        return view('pembeli.pages.riwayat_pesanan');
    })->name('riwayat-pesanan');

    Route::post('/logout', function () {
        return redirect('/');
    })->name('logout');

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
// ROUTES UNTUK ADMIN (Hanya bisa diakses user dengan role admin)
// ==========================================
Route::prefix('admin')->group(function () {

    // ==========================================
    // AUTH ADMIN (Login & Lupa Password Admin)
    // ==========================================
    Route::get('/login', function () {
        return view('auth.admin.login');
    })->name('admin.login');

    Route::get('/lupa-password', function () {
        return view('auth.admin.forget_password');
    })->name('admin.password.request');

    Route::get('/reset-password', function () {
        return view('auth.admin.reset_password');
    })->name('admin.password.reset');

    // ==========================================
    // HALAMAN DASHBOARD & KELOLA ADMIN LAINNYA
    // ==========================================
    Route::get('/ringkasan_finansial', function () {
        return view('admin.pages.finansial.ringkasan_finansial');
    })->name('admin.ringkasan-finansial');

    Route::get('/riwayat_penjualan', function () {
        return view('admin.pages.finansial.riwayat_penjualan');
    })->name('admin.riwayat-penjualan');

    Route::get('/profil_perusahaan', function () {
        return view('admin.pages.profil_perusahaan');
    })->name('admin.profil');

    Route::get('/operasional_libur', function () {
        return view('admin.pages.operasional_libur');
    })->name('admin.operasional');


    // ==========================================
    // ADMIN ROUTES - KELOLA AKUN
    // ==========================================
    Route::get('/kelola-akses', function () {
        return view('admin.pages.kelola_akun.akses');
    })->name('admin.kelola-akses');

    Route::get('/pelanggan', function () {
        return view('admin.pages.kelola_akun.pelanggan');
    })->name('admin.pelanggan');

    Route::get('/template_pesan', function () {
        return view('admin.pages.whatsapp_bot.template_pesan');
    })->name('admin.template-pesan');


    // ADMIN ROUTES - PRODUK & RESEP (UI dummy, tanpa controller/API)
    Route::get('/katalog_varian', function () {
        return view('admin.pages.produk_resep.katalog_varian');
    })->name('admin.katalog-varian');

    Route::get('/resep_digital', function () {
        return view('admin.pages.produk_resep.resep_digital');
    })->name('admin.resep-digital');

    Route::get('/pantauan_dapur', function () {
        return view('admin.pages.operasional.pantauan_dapur');
    })->name('admin.pantauan-dapur');

    Route::get('daftar_transaksi', function() {
        return view('admin.pages.operasional.daftar_transaksi');
    })->name('admin.daftar-transaksi');

    Route::get('/refund_pembatalan', function() {
        return view('admin.pages.operasional.batal_refund');
    })->name('admin.refund_pembatalan');
});
