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
use App\Http\Controllers\Api\Admin\KelolaAkunController;
use App\Http\Controllers\Api\Admin\ProfilPerusahaanController;
use App\Http\Controllers\Api\Admin\ProdukResepController;
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
    Route::post('/pembayaran/simulasi/{order_number}', [CheckoutController::class, 'simulasiBayarSandbox']);
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
    Route::post('/checkout/refund-account', [CheckoutController::class, 'submitRefundAccount']);

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

    // Kelola Akun: Akses Admin & Staf
    Route::get('/kelola-akses', [KelolaAkunController::class, 'indexAkses']);
    Route::post('/kelola-akses', [KelolaAkunController::class, 'storeAkses']);
    Route::post('/kelola-akses/{id}/reset-password', [KelolaAkunController::class, 'resetPasswordAkses']);
    Route::delete('/kelola-akses/{id}', [KelolaAkunController::class, 'destroyAkses']);

    // Kelola Akun: Daftar Pelanggan
    Route::get('/pelanggan', [KelolaAkunController::class, 'indexPelanggan']);

    // Sistem & Toko: Profil Perusahaan
    Route::get('/profil-perusahaan', [ProfilPerusahaanController::class, 'show']);
    Route::post('/profil-perusahaan', [ProfilPerusahaanController::class, 'update']);

    // Produk & Resep: Katalog Produk & Varian
    Route::get('/produk', [ProdukResepController::class, 'indexProduk']);
    Route::post('/produk', [ProdukResepController::class, 'storeProduk']);
    Route::post('/produk/{id}', [ProdukResepController::class, 'updateProduk']);
    Route::post('/produk/{id}/toggle', [ProdukResepController::class, 'toggleProduk']);
    Route::delete('/produk/{id}', [ProdukResepController::class, 'destroyProduk']);

    Route::post('/produk/{productId}/varian', [ProdukResepController::class, 'storeVarian']);
    Route::post('/varian/{variantId}', [ProdukResepController::class, 'updateVarian']);
    Route::delete('/varian/{variantId}', [ProdukResepController::class, 'destroyVarian']);

    // Produk & Resep: Resep Digital
    Route::get('/resep', [ProdukResepController::class, 'indexResep']);
    Route::post('/resep', [ProdukResepController::class, 'storeResep']);
    Route::post('/resep/{id}', [ProdukResepController::class, 'updateResep']);
    Route::delete('/resep/{id}', [ProdukResepController::class, 'destroyResep']);
});

// |--------------------------------------------------------------------------
// | 5. EXTERNAL WEBHOOKS & SIMULATOR ROUTES
// |--------------------------------------------------------------------------
// | Endpoint callback pihak ketiga (Duitku, Fonnte, Kurir) & simulator lokal.
Route::post('/duitku/callback', [CheckoutController::class, 'duitkuCallback']);
Route::post('/fonnte/webhook', [WhatsAppBotController::class, 'webhook']);
Route::post('/courier/webhook', [KelolaPengirimanController::class, 'webhookSignalKurir']);

// Simulator Webhook Pembayaran Duitku (Khusus Pengujian Lokal)
Route::get('/test-webhook/{orderNumber}/{amount}', function (\Illuminate\Http\Request $request, $orderNumber, $amount) {
    $merchantCode    = env('DUITKU_MERCHANT_CODE');
    $merchantKey     = env('DUITKU_MERCHANT_KEY');
    $signature       = md5($merchantCode . $amount . $orderNumber . $merchantKey);
    $urlCallback     = url('/api/duitku/callback');
    $amountFormatted = number_format((int) $amount, 0, ',', '.');
    $autoTrigger     = $request->query('auto') == '1' ? 'true' : 'false';

    return "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Simulasi QRIS - Raya Kitchen</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-[#F8F7FA] min-h-screen flex items-center justify-center p-4 font-sans'>
            <div class='bg-white w-full max-w-sm rounded-3xl p-6 shadow-xl border border-purple-100 text-center'>
                <div id='confirm-box'>
                    <div class='w-16 h-16 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 font-black text-xl'>
                        QRIS
                    </div>
                    <p class='text-xs font-bold text-gray-400 uppercase tracking-wider'>Merchant Tujuan</p>
                    <h2 class='text-xl font-black text-gray-800 mb-4'>Raya Kitchen (Sandbox)</h2>

                    <div class='bg-gray-50 p-4 rounded-2xl border border-gray-100 mb-6 text-left space-y-2 text-sm'>
                        <div class='flex justify-between'>
                            <span class='text-gray-500 font-medium'>No. Pesanan</span>
                            <span class='font-bold text-gray-800'>#{$orderNumber}</span>
                        </div>
                        <div class='flex justify-between border-t border-gray-200 pt-2'>
                            <span class='text-gray-500 font-bold'>Total Bayar</span>
                            <span class='font-black text-purple-600 text-lg'>Rp {$amountFormatted}</span>
                        </div>
                    </div>

                    <button id='btnBayar' onclick='prosesBayar()' class='w-full py-4 bg-[#C099CE] hover:bg-[#9B72AB] text-white font-black rounded-2xl shadow-lg transition-all'>
                        Konfirmasi Bayar Sekarang
                    </button>
                </div>

                <div id='success-box' class='hidden py-4'>
                    <div class='w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold'>
                        ✓
                    </div>
                    <h3 class='text-xl font-black text-gray-800 mb-1'>Pembayaran Berhasil!</h3>
                    <p class='text-xs text-gray-500 font-medium'>Halaman pembayaran di layar utama akan otomatis beralih ke Nota Digital.</p>
                </div>
            </div>

            <script>
                function prosesBayar() {
                    const btn = document.getElementById('btnBayar');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerText = 'Memproses Pembayaran...';
                    }

                    const formData = new URLSearchParams();
                    formData.append('merchantOrderId', '{$orderNumber}');
                    formData.append('amount', '{$amount}');
                    formData.append('merchantCode', '{$merchantCode}');
                    formData.append('resultCode', '00');
                    formData.append('signature', '{$signature}');

                    fetch('{$urlCallback}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData.toString()
                    }).then(() => {
                        document.getElementById('confirm-box').classList.add('hidden');
                        document.getElementById('success-box').classList.remove('hidden');
                    }).catch(() => {
                        alert('Gagal menghubungi server callback.');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerText = 'Konfirmasi Bayar Sekarang';
                        }
                    });
                }

                if ({$autoTrigger}) {
                    prosesBayar();
                }
            </script>
        </body>
        </html>
    ";
});
