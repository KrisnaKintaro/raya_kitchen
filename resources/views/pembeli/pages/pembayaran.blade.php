@extends('pembeli.pembeli_master')
@section('title', 'Pembayaran QRIS - Raya Kitchen')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center py-10 px-4">
    <!-- Box Pembayaran Claymorphism -->
    <div class="bg-buyer-bg p-8 sm:p-10 rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_20px_40px_rgba(192,153,206,0.2)] border border-white w-full max-w-lg text-center relative overflow-hidden">

        <h2 class="text-2xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Selesaikan Pembayaran</h2>
        <p class="text-buyer-textSecondary text-sm font-semibold mb-6">Scan QRIS di bawah ini pakai aplikasi M-Banking atau e-Wallet kesayanganmu cuy.</p>

        <!-- Informasi Tagihan -->
        @php
            $tagihan = $order->transaction->payment_status === 'paid_dp' ? $order->remaining_amount : $order->transaction->amount;
        @endphp
        <div class="bg-white p-4 rounded-2xl shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] border border-gray-100 mb-6 flex justify-between items-center text-left">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Tagihan</p>
                <p class="text-xl font-black text-buyer-primary">Rp {{ number_format($tagihan, 0, ',', '.') }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">No. Order</p>
                <p class="text-sm font-bold text-gray-800">#{{ $order->order_number }}</p>
            </div>
        </div>

        <!-- Gambar QRIS -->
        <div class="relative w-72 h-72 mx-auto bg-white rounded-3xl p-3 shadow-[0_10px_20px_rgba(192,153,206,0.2)] border border-purple-100 mb-6 cursor-pointer hover:scale-105 transition-transform" onclick="openZoomModal()">
            @if(!empty($order->transaction->qr_string))
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ urlencode($order->transaction->qr_string) }}" alt="QRIS Duitku" class="w-full h-full object-contain rounded-2xl">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center bg-gray-100 rounded-2xl border-2 border-dashed border-red-300">
                    <p class="text-sm font-bold text-red-500">QRIS Gagal Dimuat</p>
                </div>
            @endif

            <div class="absolute inset-0 bg-black/5 opacity-0 hover:opacity-100 rounded-3xl flex flex-col items-center justify-center transition-opacity">
                <div class="bg-white p-2 rounded-full shadow-md text-buyer-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                </div>
            </div>

            <!-- Animasi Loading Scan overlay (Dipindah dari img container) -->
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
</div>

<!-- Modal Zoom QRIS -->
<div id="qrisZoomModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-buyer-textPrimary/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300" onclick="closeZoomModal()">
    <div class="relative bg-white p-4 rounded-3xl max-w-[90vw] max-h-[90vh] shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
        <button type="button" onclick="closeZoomModal()" class="absolute -top-4 -right-4 w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg font-bold hover:scale-105 active:scale-95 transition-transform">
            &times;
        </button>
        @if(!empty($order->transaction->qr_string))
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=600x600&data={{ urlencode($order->transaction->qr_string) }}" alt="QRIS Raya Kitchen Besar" class="w-full h-full object-contain max-h-[80vh] rounded-2xl">
        @endif
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
        <p class="text-buyer-textSecondary font-medium text-sm mb-8 leading-relaxed">Pembayaranmu gagal diproses cuy. Coba bayar lagi atau gunakan metode lain ya.</p>
        <button type="button" onclick="closeFailedModal()" class="w-full py-3.5 bg-buyer-primary text-white font-black rounded-2xl shadow-[inset_0_-4px_6px_rgba(0,0,0,0.15),inset_0_4px_6px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all">
            Coba Lagi
        </button>
    </div>
</div>
@endsection

@push('script')
<script>
// Fungsi Zoom
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

// Logic Polling Status
// Logic Polling Status
$(document).ready(function() {
    let orderNumber = "{{ $order->order_number }}";

    // Tangkap status saat halaman ini pertama kali diload
    let initialStatus = "{{ $order->transaction->payment_status }}"; // 'unpaid' atau 'paid_dp'

    // Looping otomatis tiap 3 detik ngecek API
    let intervalCheck = setInterval(function() {
        $.ajax({
            url: "/api/pembeli/pembayaran/status/" + orderNumber,
            type: "GET",
            success: function(res) {
                if(res.status === 'success') {

                    let currentStatus = res.payment_status;

                    // KONDISI 1: Dari unpaid berubah jadi paid_dp (Berhasil Bayar DP)
                    if (initialStatus === 'unpaid' && currentStatus === 'paid_dp') {
                        clearInterval(intervalCheck);
                        closeZoomModal();

                        $('#scan-overlay').removeClass('opacity-0');
                        $('#paymentSuccessModal').removeClass('hidden');
                        setTimeout(() => {
                            $('#paymentSuccessModal').removeClass('opacity-0');
                            $('#paymentSuccessModal > div').removeClass('scale-95');
                        }, 10);

                        // Arahin ke nota (nanti di nota bakal muncul tombol lunasi karena sisa tagihan > 0)
                        setTimeout(() => {
                            window.location.href = "/nota?order_number=" + orderNumber;
                        }, 2000);
                    }

                    // KONDISI 2: Berubah jadi paid_full (Berhasil Bayar Lunas, baik dari DP maupun dari awal)
                    else if (currentStatus === 'paid_full') {
                        clearInterval(intervalCheck);
                        closeZoomModal();

                        $('#scan-overlay').removeClass('opacity-0');
                        $('#paymentSuccessModal').removeClass('hidden');
                        setTimeout(() => {
                            $('#paymentSuccessModal').removeClass('opacity-0');
                            $('#paymentSuccessModal > div').removeClass('scale-95');
                        }, 10);

                        // Arahin ke nota (nanti di nota ga ada tombol lunasi karena sisa tagihan = 0)
                        setTimeout(() => {
                            window.location.href = "/nota?order_number=" + orderNumber;
                        }, 2000);
                    }

                    // KONDISI 3: Gagal bayar (Ditolak gateway, dll)
                    else if (currentStatus === 'failed') {
                        clearInterval(intervalCheck);
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
});

function closeFailedModal() {
    $('#paymentFailedModal').addClass('opacity-0');
    $('#paymentFailedModal > div').addClass('scale-95');
    setTimeout(() => {
        $('#paymentFailedModal').addClass('hidden');
        location.reload();
    }, 300);
}
</script>
@endpush
