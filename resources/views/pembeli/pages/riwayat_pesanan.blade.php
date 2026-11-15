@extends('pembeli.pembeli_master')
@section('title', 'Riwayat Pesanan Saya - Raya Kitchen')

@section('css')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection

@section('content')
<div class="mb-8 text-center md:text-left">
    <h2 class="text-3xl md:text-4xl font-black text-buyer-primary drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)] mb-3">
        Riwayat Pesanan Saya
    </h2>
    <p class="text-buyer-textSecondary font-semibold text-sm md:text-base">
        Pantau status pesanan, estimasi kurir, dan bukti pengiriman roti & kue kamu di sini.
    </p>
</div>

<!-- Alert Sukses -->
<div id="alert-success" class="hidden mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-2xl text-sm font-bold shadow-sm"></div>

<!-- Filter Chips -->
<div class="flex gap-3 overflow-x-auto hide-scrollbar mb-6 pb-2">
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-buyer-primary text-white border-buyer-primary shadow-sm whitespace-nowrap" data-filter="semua">
        Semua
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="unpaid">
        Belum Bayar
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="paid_dp">
        Sudah DP
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="paid_full">
        Lunas
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="pending">
        Dalam Antrean (Pending)
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="diproses">
        Sedang Diproses
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="siap_diambil">
        Siap Diantar/Diambil
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="selesai">
        Selesai
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="dibatalkan">
        Batal
    </button>
</div>

<!-- Container Data Pesanan -->
<div id="orders-container" class="space-y-6">
    <p id="loading-text" class="text-center font-bold text-gray-500 animate-pulse py-10">Memuat data pesanan...</p>
</div>

<!-- Template Kosong -->
<div id="empty-state" class="hidden bg-buyer-bg p-12 rounded-[2rem] text-center shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white">
    <div class="w-16 h-16 bg-buyer-primary/10 text-buyer-primary rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">🍞</div>
    <h3 class="font-extrabold text-buyer-textPrimary text-lg mb-1">Belum Ada Pesanan di Kategori Ini</h3>
    <p class="text-buyer-textSecondary text-sm font-semibold mb-6">Coba cek tab status yang lain atau mulai belanja lagi yuk!</p>
    <a href="{{ route('katalog') }}" class="inline-block py-3 px-6 bg-buyer-primary text-white font-extrabold text-sm rounded-xl shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4)] hover:-translate-y-[2px] transition-all">Mulai Belanja</a>
</div>

<!-- Modal Lihat Bukti Pengiriman Kurir (Internal & Eksternal) -->
<div id="proofModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-buyer-textPrimary/70 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 p-4" onclick="closeProofModal()">
    <div class="bg-buyer-bg rounded-[2rem] max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl border-2 border-white relative transform scale-95 transition-transform duration-300 [&::-webkit-scrollbar]:hidden" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200">
            <div>
                <span id="proof-modal-badge" class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-green-100 text-green-700">Bukti Pengiriman</span>
                <h3 id="proof-modal-title" class="text-lg font-black text-buyer-textPrimary mt-1">Pesanan #RK-000</h3>
            </div>
            <button type="button" onclick="closeProofModal()" class="w-9 h-9 flex items-center justify-center bg-white text-gray-500 hover:text-red-500 font-black text-xl rounded-full shadow-sm transition-colors">×</button>
        </div>

        <div class="space-y-4">
            <div class="p-3.5 bg-white rounded-2xl border border-purple-100 text-xs space-y-1">
                <p class="font-bold text-gray-700">Kurir: <span id="proof-modal-courier" class="text-purple-700 font-black">-</span></p>
                <p id="proof-modal-resi-row" class="font-bold text-gray-700 hidden">No. Resi: <span id="proof-modal-resi" class="font-mono text-gray-900 font-black">-</span></p>
                <p class="text-gray-500 font-medium">Foto berikut merupakan bukti serah terima bahwa barang telah sampai di tujuan.</p>
            </div>

            <!-- Foto Bukti Barang Sampai -->
            <div class="bg-white p-3 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-extrabold text-gray-700 mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    Foto Bukti Barang Sampai (POD)
                </p>
                <div class="rounded-xl overflow-hidden bg-gray-50 border border-gray-100 flex justify-center">
                    <img id="proof-modal-arrival-img" src="" alt="Bukti Barang Sampai" class="max-h-72 w-auto object-contain">
                </div>
                <a id="proof-modal-arrival-link" href="#" target="_blank" class="mt-2 inline-block w-full text-center text-xs font-bold text-purple-600 hover:underline">Buka Gambar Ukuran Penuh →</a>
            </div>

            <!-- Foto Bukti Pelunasan COD/DP (Jika Ada) -->
            <div id="proof-modal-payment-box" class="hidden bg-white p-3 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-extrabold text-gray-700 mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Foto Bukti Pelunasan Saat Diterima
                </p>
                <div class="rounded-xl overflow-hidden bg-gray-50 border border-gray-100 flex justify-center">
                    <img id="proof-modal-payment-img" src="" alt="Bukti Pelunasan" class="max-h-72 w-auto object-contain">
                </div>
                <a id="proof-modal-payment-link" href="#" target="_blank" class="mt-2 inline-block w-full text-center text-xs font-bold text-purple-600 hover:underline">Buka Bukti Pelunasan Ukuran Penuh →</a>
            </div>
        </div>

        <button type="button" onclick="closeProofModal()" class="w-full mt-5 py-3 bg-buyer-primary text-white font-black text-sm rounded-2xl shadow-md hover:bg-buyer-hover transition-all">
            Tutup
        </button>
    </div>
</div>

<!-- Modal Pembatalan Pesanan -->
<div id="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-buyer-textPrimary/40 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
    <div class="bg-buyer-bg rounded-[2rem] max-w-md w-full max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-[inset_0_-4px_8px_rgba(192,153,206,0.3),inset_0_4px_8px_rgba(255,255,255,1),0_20px_40px_-10px_rgba(192,153,206,0.4)] relative transform scale-95 transition-transform duration-300 border-2 border-white [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-buyer-textPrimary drop-shadow-sm">Batalkan Pesanan</h3>
            <button type="button" onclick="closeCancelModal()" class="w-8 h-8 flex items-center justify-center bg-white text-gray-400 hover:text-red-500 font-bold text-xl rounded-full shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] transition-colors">×</button>
        </div>

        <form id="form-cancel-order" class="space-y-4">
            <input type="hidden" id="cancel_order_number" name="order_number">

            <div>
                <label class="text-sm font-bold text-buyer-textPrimary block mb-3">Pilih Alasan Pembatalan</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-xl cursor-pointer hover:border-red-300 transition shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]">
                        <input type="radio" name="cancellation_reason" value="Ingin merubah rincian pesanan (alamat/item)" class="accent-red-500 w-4 h-4" required>
                        <span class="text-sm text-gray-600 font-medium leading-tight">Ingin merubah rincian pesanan (alamat/item)</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-xl cursor-pointer hover:border-red-300 transition shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]">
                        <input type="radio" name="cancellation_reason" value="Jadwal pengiriman terlalu lama/tidak sesuai" class="accent-red-500 w-4 h-4">
                        <span class="text-sm text-gray-600 font-medium leading-tight">Jadwal pengiriman terlalu lama/tidak sesuai</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-xl cursor-pointer hover:border-red-300 transition shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]">
                        <input type="radio" name="cancellation_reason" value="Berubah pikiran" class="accent-red-500 w-4 h-4">
                        <span class="text-sm text-gray-600 font-medium leading-tight">Berubah pikiran</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-xl cursor-pointer hover:border-red-300 transition shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]">
                        <input type="radio" name="cancellation_reason" value="Lainnya" id="radio-lainnya" class="accent-red-500 w-4 h-4">
                        <span class="text-sm text-gray-600 font-medium leading-tight">Lainnya...</span>
                    </label>
                </div>
                <textarea id="cancel_reason_other" name="cancel_reason_other" rows="2" class="w-full mt-3 p-3 bg-white border border-gray-200 rounded-xl text-sm hidden focus:ring-2 focus:ring-red-400 focus:outline-none shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)]" placeholder="Tulis alasanmu di sini..."></textarea>
            </div>

            <div id="refund_info_box" class="hidden bg-amber-50 border-2 border-amber-200 p-4 rounded-xl mt-2 shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)]">
                <p class="text-xs font-bold text-amber-800 mb-3">Dana Sudah Masuk. Form Pengembalian (Refund):</p>
                <div class="space-y-3">
                    <input type="text" name="refund_bank" id="refund_bank" class="w-full p-2.5 border border-amber-200 rounded-lg text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 bg-white" placeholder="Nama Bank / E-Wallet (Cth: BCA, GoPay)">
                    <input type="number" name="refund_norek" id="refund_norek" class="w-full p-2.5 border border-amber-200 rounded-lg text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 bg-white" placeholder="Nomor Rekening / No. HP">
                    <input type="text" name="refund_nama" id="refund_nama" class="w-full p-2.5 border border-amber-200 rounded-lg text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 bg-white" placeholder="Atas Nama (A.N)">
                </div>
                <p class="text-[10px] text-amber-700 mt-2 font-medium leading-tight">*Admin akan mengecek dan mentransfer balik danamu dalam 1x24 jam.</p>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeCancelModal()" class="flex-1 py-3.5 bg-white text-gray-500 font-extrabold rounded-2xl shadow-[inset_0_-2px_4px_rgba(0,0,0,0.05),0_4px_10px_rgba(0,0,0,0.05)] border-2 border-gray-100 hover:text-gray-700 transition-all">Kembali</button>
                <button type="submit" id="btn-submit-cancel" class="flex-1 py-3.5 bg-red-500 text-white font-black rounded-2xl shadow-[inset_0_-4px_6px_rgba(0,0,0,0.15),inset_0_4px_6px_rgba(255,255,255,0.4),0_8px_20px_rgba(239,68,68,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all">Ya, Batalkan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    let allOrders = [];
    const ordersContainer = $('#orders-container');
    const emptyState = $('#empty-state');

    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('success') === 'true') {
        $('#alert-success').text('Pesanan berhasil dibuat!').removeClass('hidden');
        setTimeout(() => $('#alert-success').addClass('hidden'), 5000);
    }

    function fetchOrders(filterToApply = 'semua') {
        $('#loading-text').removeClass('hidden');
        ordersContainer.empty();

        $.ajax({
            url: '/api/pembeli/riwayat-pesanan-data',
            type: 'GET',
            dataType: 'json',
            xhrFields: { withCredentials: true },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(res) {
                $('#loading-text').addClass('hidden');
                if(res.status === 'success') {
                    allOrders = res.data;
                    renderOrders(filterToApply);
                }
            },
            error: function() {
                $('#loading-text').text('Gagal memuat data pesanan.');
            }
        });
    }

    fetchOrders('semua');

    $('.filter-chip').click(function() {
        $('.filter-chip').removeClass('bg-buyer-primary text-white border-buyer-primary').addClass('bg-white text-gray-500 border-gray-200');
        $(this).removeClass('bg-white text-gray-500 border-gray-200').addClass('bg-buyer-primary text-white border-buyer-primary');
        renderOrders($(this).data('filter'));
    });

    function renderOrders(filterType) {
        ordersContainer.empty();

        let filteredOrders = allOrders.filter(order => {
            if (filterType === 'semua') return true;

            let pStatus = (order.payment_status || '').toLowerCase();
            let oStatus = (order.raw_status || order.status || '').toLowerCase().replace(/\s+/g, '_');
            if (['siap_diantar', 'siap_dikirim', 'dikirim', 'diantar'].includes(oStatus)) {
                oStatus = 'siap_diambil';
            }

            if (filterType === 'unpaid') return (pStatus === 'unpaid' || pStatus === 'failed') && oStatus !== 'dibatalkan';
            if (filterType === 'paid_dp') return pStatus === 'paid_dp';
            if (filterType === 'paid_full') return pStatus === 'paid_full';
            if (filterType === 'pending') return oStatus === 'pending' && pStatus !== 'unpaid' && pStatus !== 'failed';
            if (filterType === 'diproses') return oStatus === 'diproses';
            if (filterType === 'siap_diambil') return oStatus === 'siap_diambil';
            if (filterType === 'selesai') return oStatus === 'selesai';
            if (filterType === 'dibatalkan') return oStatus === 'dibatalkan';

            return false;
        });

        if (filteredOrders.length === 0) {
            emptyState.removeClass('hidden');
            return;
        }

        emptyState.addClass('hidden');
        let htmlContent = '';

        filteredOrders.forEach(order => {
            let statusColor = 'bg-gray-100 text-gray-600';
            let pStatus = (order.payment_status || '').toLowerCase();
            let oStatus = (order.raw_status || order.status || '').toLowerCase().replace(/\s+/g, '_');
            if (['siap_diantar', 'siap_dikirim', 'dikirim', 'diantar'].includes(oStatus)) {
                oStatus = 'siap_diambil';
            }
            let pMethod = (order.payment_method || '').toLowerCase();
            let remainingAmount = parseFloat(order.remaining_amount || 0);
            let displayStatus = 'DALAM ANTREAN';

            if (oStatus === 'dibatalkan') {
                statusColor = 'bg-red-100 text-red-700 border border-red-200';
                displayStatus = 'DIBATALKAN';
            } else if (pStatus === 'unpaid' || pStatus === 'failed') {
                statusColor = 'bg-red-100 text-red-700 border border-red-200';
                displayStatus = 'BELUM DIBAYAR';
            } else if (oStatus === 'pending') {
                statusColor = 'bg-amber-100 text-amber-700 border border-amber-200';
                displayStatus = 'DALAM ANTREAN';
            } else if (oStatus === 'diproses') {
                statusColor = 'bg-blue-100 text-blue-700 border border-blue-200';
                displayStatus = 'SEDANG DIPROSES';
            } else if (oStatus === 'siap_diambil') {
                statusColor = 'bg-purple-100 text-purple-700 border border-purple-200';
                displayStatus = 'SIAP DIKIRIM/DIAMBIL';
            } else if (oStatus === 'selesai') {
                statusColor = 'bg-green-100 text-green-700 border border-green-200';
                displayStatus = 'SELESAI';
            }

            let methodText = pMethod === 'cod' ? 'COD' : 'TRANSFER';
            let methodColor = pMethod === 'cod' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-blue-50 text-blue-700 border-blue-200';
            let methodTag = `<span class="px-2.5 py-1 text-[10px] font-black rounded-[12px] border ${methodColor} uppercase tracking-wider">${methodText} (${order.tipe_bayar})</span>`;

            let courierBadge = order.is_internal
                ? `<span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-200">🛵 ${order.courier_name}</span>`
                : `<span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">📦 Ekspedisi: ${order.courier_name}</span>`;

            let resiHtml = (!order.is_internal && order.tracking_number)
                ? `<p><strong class="text-gray-700">No. Resi:</strong> <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">${order.tracking_number}</span></p>`
                : '';

            // Banner pengingat pelunasan ketika status siap dikirim & masih DP
            let reminderBanner = '';
            if (oStatus === 'siap_diambil' && pStatus === 'paid_dp' && remainingAmount > 0) {
                if (pMethod === 'cod') {
                    reminderBanner = `
                        <div class="mt-3 p-3 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800 font-bold flex items-center gap-2">
                            <span>⚠️</span>
                            <span>Pesanan siap dikirim! Segera siapkan uang tunai pelunasan COD sebesar <strong>${order.sisa_tagihan_formatted}</strong> saat kurir tiba.</span>
                        </div>
                    `;
                } else {
                    reminderBanner = `
                        <div class="mt-3 p-3 rounded-2xl bg-red-50 border border-red-200 text-xs text-red-700 font-bold flex items-center gap-2">
                            <span>⚠️</span>
                            <span>Pesanan siap dikirim! Segera lunasi sisa pembayaran sebesar <strong>${order.sisa_tagihan_formatted}</strong> via QRIS.</span>
                        </div>
                    `;
                }
            }

            let actionButtons = '';

            if (oStatus === 'dibatalkan') {
                actionButtons = `<a href="/nota?order_number=${order.order_number}" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-buyer-bg text-buyer-primary font-extrabold text-xs rounded-xl shadow-sm hover:bg-white transition-all">Detail Pembatalan</a>`;
            } else {
                if (pStatus !== 'unpaid' && pStatus !== 'failed') {
                    actionButtons += `<a href="/nota?order_number=${order.order_number}" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-buyer-bg text-buyer-primary font-extrabold text-xs rounded-xl shadow-sm hover:bg-white transition-all">Lihat Nota</a>`;
                }

                // Tombol Lihat Bukti Pengiriman jika barang sudah sampai (ada arrival_proof_url)
                if (order.arrival_proof_url) {
                    actionButtons += `
                        <button type="button" onclick="openProofModal('${order.order_number}')" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Bukti Pengiriman
                        </button>
                    `;
                }

                if (pStatus === 'unpaid' || pStatus === 'failed') {
                    actionButtons += `<a href="/pembayaran/${order.order_number}" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-amber-500 text-white font-extrabold text-xs rounded-xl shadow-md hover:bg-amber-600 transition-all">Bayar Sekarang</a>`;
                }

                if (pStatus === 'paid_dp' && remainingAmount > 0 && oStatus !== 'selesai') {
                    if (pMethod === 'cod' && oStatus !== 'siap_diambil') {
                        actionButtons += `<span class="flex-1 md:flex-none text-center py-2.5 px-4 bg-amber-50 text-amber-700 font-extrabold text-xs rounded-xl border border-amber-200">Sisa COD: ${order.sisa_tagihan_formatted}</span>`;
                    } else if (pMethod === 'cod' && oStatus === 'siap_diambil') {
                        actionButtons += `<a href="/nota?order_number=${order.order_number}" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md animate-pulse transition-all">Siapkan Pelunasan (${order.sisa_tagihan_formatted})</a>`;
                    } else {
                        let highlightClass = (oStatus === 'siap_diambil') ? 'bg-red-500 hover:bg-red-600 animate-pulse' : 'bg-purple-600 hover:bg-purple-700';
                        actionButtons += `<button type="button" onclick="lunasiDariRiwayat('${order.order_number}', this)" class="flex-1 md:flex-none text-center py-2.5 px-4 ${highlightClass} text-white font-extrabold text-xs rounded-xl shadow-md transition-all">Bayar Sisa (${order.sisa_tagihan_formatted})</button>`;
                    }
                }

                if (oStatus === 'pending') {
                    let isPaid = (pStatus === 'paid_dp' || pStatus === 'paid_full');
                    actionButtons += `<button onclick="openCancelModal('${order.order_number}', ${isPaid})" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-red-100 text-red-600 font-extrabold text-xs rounded-xl shadow-sm hover:bg-red-200 transition-all">Batalkan</button>`;
                }
            }

            htmlContent += `
            <div class="bg-white p-5 sm:p-6 rounded-3xl shadow-sm border border-purple-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="space-y-2 w-full md:w-auto flex-1">
                    <div class="flex flex-wrap items-center justify-between md:justify-start gap-2 w-full">
                        <span class="text-xs font-bold text-gray-400">${order.date}</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 text-[10px] font-black rounded-full uppercase tracking-wider ${statusColor}">
                                ${displayStatus}
                            </span>
                            ${methodTag}
                            ${courierBadge}
                        </div>
                    </div>
                    <h3 class="font-extrabold text-gray-800 text-base md:text-lg">
                        No. Pesanan: <span class="text-purple-600">${order.order_number}</span>
                    </h3>
                    <div class="text-xs md:text-sm text-gray-500 space-y-1 font-medium">
                        <p><strong class="text-gray-700">Penerima:</strong> ${order.recipient_name} (${order.recipient_whatsapp})</p>
                        <p><strong class="text-gray-700">Alamat:</strong> ${order.shipping_address || 'Belum diatur'}</p>
                        <p><strong class="text-purple-700">${order.schedule_label || 'Jadwal / Estimasi'}:</strong> <span class="font-bold text-gray-800">${order.schedule_text || '-'}</span></p>
                        ${resiHtml}
                    </div>
                    ${reminderBanner}
                </div>
                <div class="flex flex-col md:items-end justify-between w-full md:w-auto gap-3 border-t md:border-t-0 pt-4 md:pt-0 border-gray-100 shrink-0">
                    <div class="text-left md:text-right flex justify-between md:block items-center w-full">
                        <span class="text-xs font-bold text-gray-400 block">Total Pembayaran</span>
                        <span class="font-black text-purple-700 text-lg md:text-xl">${order.total_amount_formatted}</span>
                    </div>
                    <div class="flex flex-wrap md:flex-nowrap items-center gap-2 w-full md:w-auto mt-2 md:mt-0">
                        ${actionButtons}
                    </div>
                </div>
            </div>`;
        });

        ordersContainer.html(htmlContent);
    }

    // Modal Bukti Pengiriman Kurir
    window.openProofModal = function(orderNumber) {
        const order = allOrders.find(o => o.order_number === orderNumber);
        if (!order || !order.arrival_proof_url) return;

        $('#proof-modal-title').text('Pesanan #' + order.order_number);
        $('#proof-modal-badge').text(order.is_internal ? 'Bukti Kurir Internal' : 'Bukti Kurir Ekspedisi');
        $('#proof-modal-courier').text(`${order.courier_name} (${order.shipping_service})`);

        if (!order.is_internal && order.tracking_number) {
            $('#proof-modal-resi').text(order.tracking_number);
            $('#proof-modal-resi-row').removeClass('hidden');
        } else {
            $('#proof-modal-resi-row').addClass('hidden');
        }

        $('#proof-modal-arrival-img').attr('src', order.arrival_proof_url);
        $('#proof-modal-arrival-link').attr('href', order.arrival_proof_url);

        if (order.payment_proof_url) {
            $('#proof-modal-payment-img').attr('src', order.payment_proof_url);
            $('#proof-modal-payment-link').attr('href', order.payment_proof_url);
            $('#proof-modal-payment-box').removeClass('hidden');
        } else {
            $('#proof-modal-payment-box').addClass('hidden');
        }

        $('#proofModal').removeClass('hidden');
        setTimeout(() => {
            $('#proofModal').removeClass('opacity-0');
            $('#proofModal > div').removeClass('scale-95');
        }, 10);
    };

    window.closeProofModal = function() {
        $('#proofModal').addClass('opacity-0');
        $('#proofModal > div').addClass('scale-95');
        setTimeout(() => { $('#proofModal').addClass('hidden'); }, 300);
    };

    window.lunasiDariRiwayat = function(orderNumber, btnEl) {
        let btn = $(btnEl);
        let ogText = btn.html();
        btn.html('Menyiapkan QRIS...').prop('disabled', true);

        $.ajax({
            url: "/api/pembeli/checkout/pay-remaining",
            type: "POST",
            data: {
                order_number: orderNumber,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if(res.status === 'success') {
                    window.location.href = res.data.redirect_url;
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal menyiapkan QRIS pelunasan.');
                btn.html(ogText).prop('disabled', false);
            }
        });
    };

    window.openCancelModal = function(orderNumber, isPaid) {
        $('#cancel_order_number').val(orderNumber);

        if(isPaid) {
            $('#refund_info_box').removeClass('hidden');
            $('#refund_bank, #refund_norek, #refund_nama').prop('required', true);
        } else {
            $('#refund_info_box').addClass('hidden');
            $('#refund_bank, #refund_norek, #refund_nama').prop('required', false);
        }

        $('#cancelModal').removeClass('hidden');
        setTimeout(() => {
            $('#cancelModal').removeClass('opacity-0');
            $('#cancelModal > div').removeClass('scale-95');
        }, 10);
    };

    window.closeCancelModal = function() {
        $('#cancelModal').addClass('opacity-0');
        $('#cancelModal > div').addClass('scale-95');
        setTimeout(() => {
            $('#cancelModal').addClass('hidden');
            $('#form-cancel-order')[0].reset();
            $('#cancel_reason_other').addClass('hidden');
        }, 300);
    };

    $('input[name="cancellation_reason"]').change(function() {
        if ($('#radio-lainnya').is(':checked')) {
            $('#cancel_reason_other').removeClass('hidden').prop('required', true);
        } else {
            $('#cancel_reason_other').addClass('hidden').prop('required', false);
        }
    });

    $('#form-cancel-order').submit(function(e) {
        e.preventDefault();
        let reason = $('input[name="cancellation_reason"]:checked').val();
        if (reason === 'Lainnya') {
            reason = 'Lainnya: ' + $('#cancel_reason_other').val();
        }

        let btn = $('#btn-submit-cancel');
        let ogText = btn.text();
        btn.html('<span class="animate-pulse">Memproses...</span>').prop('disabled', true);

        $.ajax({
            url: "/api/pembeli/checkout/cancel",
            type: "POST",
            data: {
                order_number: $('#cancel_order_number').val(),
                cancellation_reason: reason,
                refund_bank: $('#refund_bank').val() || null,
                refund_norek: $('#refund_norek').val() || null,
                refund_nama: $('#refund_nama').val() || null,
            },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if(res.status === 'success') {
                    closeCancelModal();
                    $('#alert-success').text('Pesanan berhasil dibatalkan.').removeClass('hidden');
                    setTimeout(() => $('#alert-success').addClass('hidden'), 5000);

                    let currentFilter = $('.filter-chip.bg-buyer-primary').data('filter') || 'semua';
                    fetchOrders(currentFilter);
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal membatalkan pesanan.');
            },
            complete: function() {
                btn.text(ogText).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
