<?php

use App\Http\Controllers\Api\Admin\BatalRefundController;
use App\Http\Controllers\Api\Admin\DaftarTransaksiController;
use App\Http\Controllers\Api\Admin\DashboardFinansialController;
use App\Http\Controllers\Api\Admin\KelolaPengirimanController;
use App\Http\Controllers\Api\Admin\OperasionalLiburController;
use App\Http\Controllers\Api\Admin\PantauanDapurController;
use App\Http\Controllers\Api\Admin\SystemStoreController;
use App\Http\Controllers\Api\Admin\WhatsappBotTemplateController;
use App\Http\Controllers\Api\Auth\ForgResetPasswordController;
use App\Http\Controllers\Api\Auth\LogInOutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Pembeli\CartController;
use App\Http\Controllers\Api\Pembeli\CheckoutController;
use App\Http\Controllers\Api\Pembeli\KatalogController;
use App\Http\Controllers\Api\Pembeli\NotificationController;
use App\Http\Controllers\Api\Pembeli\ProfileController;
use App\Http\Controllers\Api\Pembeli\TentangKamiController;
use App\Http\Controllers\Api\WhatsAppBotController;
use Illuminate\Support\Facades\Route;

// |--------------------------------------------------------------------------
// | 1. AUTHENTICATION ROUTES (/api/auth)
// |--------------------------------------------------------------------------
// | Menangani registrasi, verifikasi OTP, login, logout, dan reset password.
Route::prefix('auth')->middleware('web')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/verify-otp', [RegisterController::class, 'verifyOtp']);
    Route::post('/resend-otp', [RegisterController::class, 'resendOtp']);
    Route::post('/login', [LogInOutController::class, 'login']);
    Route::get('/logout', [LogInOutController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [ForgResetPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ForgResetPasswordController::class, 'resetPassword']);
});

// |--------------------------------------------------------------------------
// | 2. BUYER PUBLIC ROUTES (/api/pembeli)
// |--------------------------------------------------------------------------
// | Endpoint publik yang dapat diakses tanpa login (termasuk akses langsung
// | dari tautan WhatsApp Bot seperti halaman pembayaran QRIS, nota, & pin lokasi).
Route::prefix('pembeli')->group(function () {
    // Katalog & Informasi Toko
    Route::get('/katalog', [KatalogController::class, 'index']);
    Route::get('/katalog/{id}', [KatalogController::class, 'show']);
    Route::get('/tentang-kami', [TentangKamiController::class, 'index']);

    // Pin Koordinat Maps (Dari Link WhatsApp Bot)
    Route::post('/alamat-update-koordinat', [WhatsAppBotController::class, 'updateKoordinatMap']);

    // Nota Digital, Pembayaran QRIS, & Pelunasan Tagihan
    Route::get('/nota', [CheckoutController::class, 'showNota']);
    Route::get('/pembayaran/{order_number}', [CheckoutController::class, 'showPembayaran']);
    Route::get('/pembayaran/status/{order_number}', [CheckoutController::class, 'cekStatusPembayaran']);
    Route::post('/checkout/pay-remaining', [CheckoutController::class, 'payRemaining']);
});

// |--------------------------------------------------------------------------
// | 3. BUYER PROTECTED ROUTES (/api/pembeli)
// |--------------------------------------------------------------------------
// | Endpoint khusus pelanggan yang sudah login pada website.
Route::prefix('pembeli')->middleware(['web', 'auth:web'])->group(function () {
    // Profil & Manajemen Alamat
    Route::get('/profil-data', [ProfileController::class, 'edit'])->name('profil.data');
    Route::post('/profil-data', [ProfileController::class, 'update']);
    Route::post('/update-profil-pribadi', [ProfileController::class, 'updateProfilePribadi']);
    Route::delete('/alamat/{id}', [ProfileController::class, 'hapusAlamat']);
    Route::post('/checkout/alamat-ajax', [CheckoutController::class, 'storeAddressAjax'])->name('alamat.ajax');

    // Keranjang Belanja (Cart)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/store', [CartController::class, 'store']);
    Route::post('/cart/update', [CartController::class, 'update']);
    Route::post('/cart/delete', [CartController::class, 'destroy']);

    // Kalender Kapasitas Produksi (Heatmap) & Slot Jam Operasional
    Route::get('/checkout/heatmap', [CheckoutController::class, 'getHeatmap']);
    Route::get('/checkout/times', [CheckoutController::class, 'getAvailableTimes']);

    // Proses Checkout, Cek Tarif Ongkir, & Pembatalan Pesanan
    Route::post('/checkout/cek-ongkir', [CheckoutController::class, 'cekOngkir'])->name('checkout.cek_ongkir');
    Route::post('/checkout/process', [CheckoutController::class, 'store']);
    Route::post('/checkout/cancel', [CheckoutController::class, 'cancelOrder']);

    // Riwayat Pesanan & Notifikasi Pelanggan
    Route::get('/riwayat-pesanan-data', [CheckoutController::class, 'index'])->name('pesanan.data');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead']);
});

// |--------------------------------------------------------------------------
// | 4. ADMIN PANEL ROUTES (/api/admin)
// |--------------------------------------------------------------------------
// | Endpoint manajemen operasional, finansial, sistem toko, dan bot WhatsApp.
Route::prefix('admin')->group(function () {
    // Dashboard Finansial
    Route::get('/dashboard-finansial', [DashboardFinansialController::class, 'index']);

    // Operasional: Pantauan Dapur (KDS)
    Route::get('/pantauan-dapur', [PantauanDapurController::class, 'index']);
    Route::post('/pantauan-dapur/move', [PantauanDapurController::class, 'moveItem']);

    // Operasional: Daftar Transaksi
    Route::get('/daftar-transaksi', [DaftarTransaksiController::class, 'index']);
    Route::post('/daftar-transaksi/{order_number}/status', [DaftarTransaksiController::class, 'updateStatus']);

    // Operasional: Kelola Pengiriman (Kurir Internal & Ekspedisi)
    Route::get('/kelola-pengiriman', [KelolaPengirimanController::class, 'index']);
    Route::post('/kelola-pengiriman/{order_number}/resi', [KelolaPengirimanController::class, 'updateResi']);
    Route::post('/kelola-pengiriman/{order_number}/cek-status-kurir', [KelolaPengirimanController::class, 'syncCourierStatus']);
    Route::post('/kelola-pengiriman/{order_number}/selesai', [KelolaPengirimanController::class, 'selesaikanPengiriman']);

    // Operasional: Pembatalan & Refund Dana
    Route::get('/batal-refund', [BatalRefundController::class, 'index']);
    Route::get('/batal-refund/count', [BatalRefundController::class, 'pendingCount']);
    Route::post('/batal-refund/{order_number}/proses', [BatalRefundController::class, 'prosesRefund']);

    // Sistem & Toko: Pengaturan Operasional & Kalender Libur
    Route::get('/operasional', [OperasionalLiburController::class, 'getOperasional']);
    Route::post('/operasional', [OperasionalLiburController::class, 'updateOperasional']);
    Route::get('/libur', [OperasionalLiburController::class, 'getLibur']);
    Route::post('/libur', [OperasionalLiburController::class, 'addLibur']);
    Route::delete('/libur/{id}', [OperasionalLiburController::class, 'deleteLibur']);
    Route::delete('/libur-past/clear', [OperasionalLiburController::class, 'clearPastLibur']);
    Route::post('/libur/generate', [OperasionalLiburController::class, 'generateLiburOtomatis']);

    // WhatsApp Bot: Manajemen Template Pesan
    Route::get('/bot-templates', [WhatsappBotTemplateController::class, 'index']);
    Route::post('/bot-templates/update', [WhatsappBotTemplateController::class, 'update']);
    Route::post('/bot-templates/reset', [WhatsappBotTemplateController::class, 'reset']);
});

// |--------------------------------------------------------------------------
// | 5. EXTERNAL WEBHOOKS & SIMULATOR ROUTES
// |--------------------------------------------------------------------------
// | Endpoint callback pihak ketiga (Duitku, Fonnte, Kurir) & simulator lokal.
Route::post('/duitku/callback', [CheckoutController::class, 'duitkuCallback']);
Route::post('/fonnte/webhook', [WhatsAppBotController::class, 'webhook']);
Route::post('/courier/webhook', [KelolaPengirimanController::class, 'webhookSignalKurir']);

// Simulator Webhook Pembayaran Duitku (Khusus Pengujian Lokal)
Route::get('/test-webhook/{orderNumber}/{amount}', function ($orderNumber, $amount) {
    $merchantCode = env('DUITKU_MERCHANT_CODE');
    $merchantKey  = env('DUITKU_MERCHANT_KEY');
    $signature    = md5($merchantCode . $amount . $orderNumber . $merchantKey);
    $urlCallback  = url('/api/duitku/callback');

    return "
        <html>
        <body onload=\"document.getElementById('formWebhook').submit();\" style=\"font-family: sans-serif; padding: 2rem;\">
            <h3>Memproses simulasi webhook pembayaran...</h3>
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