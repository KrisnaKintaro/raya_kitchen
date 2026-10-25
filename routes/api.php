<?php

use App\Http\Controllers\Api\Auth\ForgResetPasswordController;
use App\Http\Controllers\Api\Auth\LogInOutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Pembeli\CartController;
use App\Http\Controllers\Api\Pembeli\CheckoutController;
use App\Http\Controllers\Api\Pembeli\KatalogController;
use App\Http\Controllers\Api\Pembeli\PreorderController;
use App\Http\Controllers\Api\Pembeli\ProfileController;
use App\Http\Controllers\Api\Pembeli\TentangKamiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// ===========================
// ROUTE AUTH
// ===========================
Route::prefix('auth')->middleware('web')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/verify-otp', [RegisterController::class, 'verifyOtp']);
    Route::post('/resend-otp', [RegisterController::class, 'resendOtp']);
    Route::post('/login', [LogInOutController::class, 'login']);
    Route::get('/logout',[LogInOutController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [ForgResetPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ForgResetPasswordController::class, 'resetPassword']);
});

// ========================================================================================
// ROUTE TAMPILAN PEMBELI
// ========================================================================================
// API ga perlu login
Route::prefix('pembeli')->group(function () {
    Route::get('/katalog', [KatalogController::class, 'index']);
    Route::get('/katalog/{id}', [KatalogController::class, 'show']);
    Route::get('/tentang-kami', [TentangKamiController::class, 'index']);
});



// Perlu login untuk akses route di bawah ini
Route::prefix('pembeli')->middleware(['web', 'auth:web'])->group(function () {
    // --- PROFIL & ALAMAT ---
    Route::get('profil-data', [ProfileController::class, 'edit'])->name('profil.data');
    Route::post('update-profil-pribadi', [ProfileController::class, 'update']); // Disinkronkan dengan AJAX di profil.blade.php
    Route::delete('alamat/{id}', [ProfileController::class, 'hapusAlamat']);
    Route::post('/checkout/alamat-ajax', [CheckoutController::class, 'storeAddressAjax'])->name('alamat.ajax'); // Untuk form modal alamat di Checkout

    // --- TRANSAKSI & PESANAN ---
    Route::get('riwayat-pesanan-data', [CheckoutController::class, 'index'])->name('pesanan.data');
    Route::get('/nota', [CheckoutController::class, 'showNota']); // Rute API untuk data Nota
    Route::get('/pembayaran/{order_number}', [CheckoutController::class, 'showPembayaran']); // Rute API untuk data Pembayaran

    // --- CART (KERANJANG) ---
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/store', [CartController::class, 'store']);
    Route::post('/cart/update', [CartController::class, 'update']);
    Route::post('/cart/delete', [CartController::class, 'destroy']);

    // --- CHECKOUT & PREORDER ---
    Route::post('/checkout/process', [CheckoutController::class, 'store']);
    Route::get('/checkout/heatmap', [PreorderController::class, 'getHeatmap']);
    Route::get('/checkout/times', [PreorderController::class, 'getAvailableTimes']);

    // --- PAYMENT POLLING & ACTIONS ---
    Route::get('/pembayaran/status/{order_number}', [CheckoutController::class, 'cekStatusPembayaran']);
    Route::post('/checkout/pay-remaining', [CheckoutController::class, 'payRemaining']);
    Route::post('/checkout/cancel', [CheckoutController::class, 'cancelOrder']);
});

// ========================================================================================
// ROUTE TAMPILAN ADMIN
// ========================================================================================
Route::prefix('admin')->group(function () {
    // Tambahkan route admin di sini
});

// ========================================================================================
// ROUTE WEBHOOK DUITKU (HARUS BISA DIAKSES PUBLIK)
// ========================================================================================
Route::post('/duitku/callback', [CheckoutController::class, 'duitkuCallback']);

// ROUTE BUAT NGETES WEBHOOK LOKAL
Route::get('/test-webhook/{orderNumber}/{amount}', function($orderNumber, $amount) {
    $merchantCode = env('DUITKU_MERCHANT_CODE');
    $merchantKey = env('DUITKU_MERCHANT_KEY');

    // Bikin signature valid otomatis pake rumus Duitku
    $signature = md5($merchantCode . $amount . $orderNumber . $merchantKey);
    $urlCallback = url('/api/duitku/callback');

    // Bikin form HTML yang langsung auto-submit nge-POST ke webhook
    return "
        <html>
        <body onload=\"document.getElementById('formWebhook').submit();\" style=\"font-family: sans-serif; padding: 2rem;\">
            <h3>Tunggu bentar, lagi nembak webhook... 🚀</h3>
            <form id=\"formWebhook\" action=\"{$urlCallback}\" method=\"POST\" style=\"display: none;\">
                <input type=\"text\" name=\"merchantOrderId\" value=\"{$orderNumber}\">
                <input type=\"text\" name=\"amount\" value=\"{$amount}\">
                <input type=\"text\" name=\"merchantCode\" value=\"{$merchantCode}\">
                <input type=\"text\" name=\"resultCode\" value=\"00\">
                <input type=\"text\" name=\"signature\" value=\"{$signature}\">
            </form>
        </body>
        </html>
    ";
});
