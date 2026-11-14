@extends('pembeli.pembeli_master')

@section('title', 'Pembayaran QRIS - Raya Kitchen')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center py-10 px-4">
    <!-- Box Pembayaran Claymorphism -->
    <div class="bg-buyer-bg p-8 sm:p-10 rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_20px_40px_rgba(192,153,206,0.2)] border border-white w-full max-w-lg text-center relative overflow-hidden">

        <h2 id="page-title" class="text-2xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Selesaikan Pembayaran</h2>
        <p id="page-subtitle" class="text-buyer-textSecondary text-sm font-semibold mb-6">Scan QRIS di bawah ini pakai aplikasi M-Banking atau e-Wallet kesayanganmu cuy.</p>

        <!-- Loading State Skeleton -->
        <div id="loading-skeleton" class="animate-pulse flex flex-col items-center w-full mb-6">
            <div class="h-16 bg-gray-200 rounded-2xl w-full mb-6"></div>
            <div class="h-72 w-72 bg-gray-200 rounded-3xl mb-6"></div>
            <div class="h-10 bg-gray-200 rounded-xl w-3/4"></div>
        </div>

        <!-- Konten Utama -->
        <div id="payment-content" class="hidden w-full">

            <!-- Timer Countdown (Khusus Unpaid 24 Jam) -->
            <div id="timer-container" class="hidden bg-red-50 border border-red-200 p-3 rounded-2xl mb-6 shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] text-center">
                <p class="text-xs text-red-600 font-bold uppercase tracking-wider mb-1">Sisa Waktu Pembayaran (24 Jam)</p>
                <p id="countdown-timer" class="text-2xl font-black text-red-600 tracking-widest">00:00:00</p>
            </div>

            <!-- Info Batas Waktu Pelunasan DP (Sampai Siap Diantar) -->
            <div id="pelunasan-deadline-container" class="hidden bg-purple-50 border border-purple-200 p-3.5 rounded-2xl mb-6 shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] text-center">
                <p class="text-[11px] text-purple-600 font-extrabold uppercase tracking-wider mb-1">Pelunasan Sisa Tagihan (70%)</p>
                <p class="text-xs font-bold text-gray-700">Batas waktu pelunasan berlaku sampai pesanan berstatus <span class="text-purple-700 font-black">Siap Diantar / Diambil</span>.</p>
            </div>

            <!-- Informasi Tagihan -->
            <div class="bg-white p-4 rounded-2xl shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] border border-gray-100 mb-6 flex justify-between items-center text-left">
                <div>
                    <p id="label-tagihan-box" class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Tagihan</p>
                    <p id="tagihan-amount" class="text-xl font-black text-buyer-primary">Rp 0</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">No. Order</p>
                    <p id="order-number" class="text-sm font-bold text-gray-800"># -</p>
                </div>
            </div>

            <!-- Bagian Aktif QRIS (Disembunyikan jika sudah Lunas) -->
            <div id="qris-active-section">
                <div class="relative w-72 h-72 mx-auto bg-white rounded-3xl p-3 shadow-[0_10px_20px_rgba(192,153,206,0.2)] border border-purple-100 mb-6 cursor-pointer hover:scale-105 transition-transform" onclick="openZoomModal()">
                    <img id="qris-image" src="" alt="QRIS Duitku" class="hidden w-full h-full object-contain rounded-2xl">
                    <div id="qris-failed" class="hidden w-full h-full flex flex-col items-center justify-center bg-gray-100 rounded-2xl border-2 border-dashed border-red-300">
                        <p class="text-sm font-bold text-red-500">QRIS Gagal Dimuat</p>
                    </div>
                    <div class="absolute inset-0 bg-black/5 opacity-0 hover:opacity-100 rounded-3xl flex flex-col items-center justify-center transition-opacity">
                        <div class="bg-white p-2 rounded-full shadow-md text-buyer-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                        </div>
                    </div>
                    <!-- Animasi Loading Scan overlay -->
                    <div class="absolute inset-0 bg-white/70 backdrop-blur-sm rounded-3xl flex flex-col items-center justify-center opacity-0 transition-opacity pointer-events-none" id="scan-overlay">
                        <div class="animate-spin w-10 h-10 border-4 border-buyer-primary/30 border-t-buyer-primary rounded-full mb-3"></div>
                        <p class="text-sm font-bold text-buyer-primary">Mengecek Pembayaran...</p>
                    </div>
                </div>

                <p class="text-xs text-buyer-textSecondary font-medium mb-4 italic text-center w-full">Klik gambar QRIS untuk memperbesar.</p>

                <!-- Indikator Menunggu -->
                <div class="flex items-center justify-center gap-3 text-sm font-bold text-amber-500 animate-pulse bg-amber-50 py-3 rounded-xl border border-amber-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Menunggu Pembayaran...</span>
                </div>
            </div>

            <!-- Bagian Khusus Jika Pesanan Sudah Lunas (QRIS Di-hide) -->
            <div id="qris-lunas-section" class="hidden">
                <div class="p-6 bg-green-50 border-2 border-green-200 rounded-3xl mb-6 text-center shadow-sm">
                    <div class="w-16 h-16 bg-white text-green-500 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-lg font-black text-green-800 mb-1">Pembayaran Sudah Lunas Dibayar</h3>
                    <p class="text-xs font-semibold text-green-700 leading-relaxed">Terima kasih! Seluruh tagihan untuk pesanan ini telah lunas dibayar.</p>
                </div>
                <a id="btn-lihat-nota-lunas" href="#" class="w-full py-3.5 bg-buyer-primary text-white font-black rounded-2xl shadow-md hover:-translate-y-0.5 transition-all block text-center text-sm">
                    Lihat Nota Digital
                </a>
            </div>

        </div>
    </div>
</div>

<!-- Modal Zoom QRIS -->
<div id="qrisZoomModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-buyer-textPrimary/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300" onclick="closeZoomModal()">
    <div class="relative bg-white p-4 rounded-3xl max-w-[90vw] max-h-[90vh] shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
        <button type="button" onclick="closeZoomModal()" class="absolute -top-4 -right-4 w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg font-bold hover:scale-105 active:scale-95 transition-transform">
            &times;
        </button>
        <img id="qris-zoom-image" src="" alt="QRIS Raya Kitchen Besar" class="w-full h-full object-contain max-h-[80vh] rounded-2xl hidden">
    </div>
</div>

<!-- Modal Pembayaran Berhasil -->
<div id="paymentSuccessModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-buyer-textPrimary/40 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
    <div class="bg-buyer-bg border-2 border-white rounded-[2.5rem] max-w-sm w-full p-8 text-center shadow-[inset_0_-4px_8px_rgba(192,153,206,0.3),inset_0_4px_8px_rgba(255,255,255,1),0_20px_40px_-10px_rgba(192,153,206,0.4)] transform scale-95 transition-transform duration-300 relative mx-4">
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-[inset_0_4px_10px_rgba(192,153,206,0.2),0_4px_10px_rgba(192,153,206,0.15)] border-4 border-buyer-bg">
            <svg class="w-10 h-10 text-green-500 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h3 class="text-2xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Asyik, Berhasil!</h3>
        <p class="text-buyer-textSecondary font-medium text-sm mb-2 leading-relaxed">Pembayaranmu udah masuk cuy.</p>
        <p class="text-buyer-primary text-xs font-bold animate-pulse">Mengalihkan ke Nota...</p>
    </div>
</div>

<!-- Modal Gagal (Custom Alert) -->
<div id="paymentFailedModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-buyer-textPrimary/40 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
    <div class="bg-buyer-bg border-2 border-white rounded-[2.5rem] max-w-sm w-full p-8 text-center shadow-[inset_0_-4px_8px_rgba(192,153,206,0.3),inset_0_4px_8px_rgba(255,255,255,1),0_20px_40px_-10px_rgba(192,153,206,0.4)] transform scale-95 transition-transform duration-300 relative mx-4">
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-[inset_0_4px_10px_rgba(192,153,206,0.2),0_4px_10px_rgba(192,153,206,0.15)] border-4 border-buyer-bg">
            <svg class="w-10 h-10 text-red-500 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
        </div>
        <h3 class="text-2xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Waduh, Gagal!</h3>
        <p class="text-buyer-textSecondary font-medium text-sm mb-8 leading-relaxed">Pembayaranmu gagal diproses cuy. Waktu habis atau pesanan dibatalkan.</p>
        <a id="btn-failed-back" href="/riwayat-pesanan" class="w-full py-3.5 bg-buyer-primary text-white font-black rounded-2xl shadow-[inset_0_-4px_6px_rgba(0,0,0,0.15),inset_0_4px_6px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all block text-center">
            Lihat Detail Nota
        </a>
    </div>
</div>
@endsection

@push('script')
<script>
function openZoomModal() {
    $('#qrisZoomModal').removeClass('hidden');
    setTimeout(() => {
        $('#qrisZoomModal').removeClass('opacity-0');
        $('#qrisZoomModal > div').removeClass('scale-95');
    }, 10);
}

function closeZoomModal() {
    $('#qrisZoomModal').addClass('opacity-0');
    $('#qrisZoomModal > div').addClass('scale-95');
    setTimeout(() => { $('#qrisZoomModal').addClass('hidden'); }, 300);
}

$(document).ready(function() {
    const pathArray = window.location.pathname.split('/');
    const orderNumber = pathArray[pathArray.length - 1];

    let initialStatus = '';
    let expireTime = 0;
    let intervalCheck = null;
    let timerInterval = null;

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    $.ajax({
        url: `/api/pembeli/pembayaran/${orderNumber}`,
        type: 'GET',
        success: function(res) {
            if(res.status === 'success') {
                const order = res.data.order;
                const transaction = order.transaction;
                const isLunas = res.data.is_lunas || transaction.payment_status === 'paid_full';
                const isPelunasan = res.data.is_pelunasan || (transaction.payment_status === 'paid_dp' && parseFloat(order.remaining_amount) > 0);

                initialStatus = transaction.payment_status;
                expireTime = new window.Date(res.data.expired_at).getTime();

                $('#order-number').text('#' + order.order_number);
                $('#btn-lihat-nota-lunas').attr('href', '/nota?order_number=' + order.order_number);
                $('#btn-failed-back').attr('href', '/nota?order_number=' + order.order_number);

                // KASUS 1: SUDAH LUNAS -> Sembunyikan QRIS dan tampilkan info Lunas
                if (isLunas) {
                    $('#page-title').text('Status Pembayaran');
                    $('#page-subtitle').text('Detail status pembayaran untuk pesananmu.');
                    $('#label-tagihan-box').text('Total Dibayar (Lunas)');
                    $('#tagihan-amount').text(formatRupiah(order.total_amount));

                    $('#qris-active-section').addClass('hidden');
                    $('#timer-container').addClass('hidden');
                    $('#pelunasan-deadline-container').addClass('hidden');
                    $('#qris-lunas-section').removeClass('hidden');

                    $('#loading-skeleton').addClass('hidden');
                    $('#payment-content').removeClass('hidden');
                    return;
                }

                // KASUS 2: BELUM LUNAS (UNPAID ATAU PELUNASAN DP)
                let tagihan = isPelunasan ? order.remaining_amount : transaction.amount;
                $('#label-tagihan-box').text(isPelunasan ? 'Sisa Tagihan (Pelunasan)' : 'Total Tagihan');
                $('#tagihan-amount').text(formatRupiah(tagihan));

                if (transaction.qr_string) {
                    let qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${encodeURIComponent(transaction.qr_string)}`;
                    let qrZoomUrl = `https://api.qrserver.com/v1/create-qr-code/?size=600x600&data=${encodeURIComponent(transaction.qr_string)}`;
                    $('#qris-image').attr('src', qrUrl).removeClass('hidden');
                    $('#qris-zoom-image').attr('src', qrZoomUrl).removeClass('hidden');
                } else {
                    $('#qris-failed').removeClass('hidden');
                }

                if (initialStatus === 'unpaid') {
                    // Waktu 24 jam khusus untuk pembayaran pertama (unpaid)
                    $('#timer-container').removeClass('hidden');
                    timerInterval = setInterval(updateTimer, 1000);
                    updateTimer();
                } else if (isPelunasan) {
                    // Untuk pelunasan DP 70%, waktunya sampai status pesanan siap diantar/diambil
                    $('#pelunasan-deadline-container').removeClass('hidden');
                }

                $('#loading-skeleton').addClass('hidden');
                $('#payment-content').removeClass('hidden');

                startPolling();
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Gagal memuat detail pembayaran.');
            window.location.href = '/';
        }
    });

    function updateTimer() {
        const now = new window.Date().getTime();
        const distance = expireTime - now;

        if (distance < 0) {
            $('#countdown-timer').text("WAKTU HABIS");
            if (timerInterval) clearInterval(timerInterval);
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        $('#countdown-timer').text(
            (hours < 10 ? "0" : "") + hours + ":" +
            (minutes < 10 ? "0" : "") + minutes + ":" +
            (seconds < 10 ? "0" : "") + seconds
        );
    }

    function startPolling() {
        intervalCheck = setInterval(function() {
            $.ajax({
                url: "/api/pembeli/pembayaran/status/" + orderNumber,
                type: "GET",
                success: function(res) {
                    if(res.status === 'success') {
                        let currentStatus = res.payment_status;

                        if ((initialStatus === 'unpaid' && currentStatus === 'paid_dp') || currentStatus === 'paid_full') {
                            clearInterval(intervalCheck);
                            if (timerInterval) clearInterval(timerInterval);
                            closeZoomModal();
                            $('#scan-overlay').removeClass('opacity-0');

                            $('#paymentSuccessModal').removeClass('hidden');
                            setTimeout(() => {
                                $('#paymentSuccessModal').removeClass('opacity-0');
                                $('#paymentSuccessModal > div').removeClass('scale-95');
                            }, 10);

                            setTimeout(() => {
                                window.location.href = "/nota?order_number=" + orderNumber;
                            }, 2000);
                        }
                        else if (currentStatus === 'failed' || res.order_status === 'dibatalkan') {
                            clearInterval(intervalCheck);
                            if (timerInterval) clearInterval(timerInterval);
                            closeZoomModal();

                            $('#paymentFailedModal').removeClass('hidden');
                            setTimeout(() => {
                                $('#paymentFailedModal').removeClass('opacity-0');
                                $('#paymentFailedModal > div').removeClass('scale-95');
                            }, 10);
                        }
                    }
                }
            });
        }, 3000);
    }
});
</script>
@endpush