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
        Pantau status pesanan roti & kue kamu dengan mudah di sini.
    </p>
</div>

<!-- Alert Sukses -->
<div id="alert-success" class="hidden mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-2xl text-sm font-bold shadow-sm"></div>

<!-- Filter Chips ala Mobile Mockup -->
<div class="flex gap-3 overflow-x-auto hide-scrollbar mb-6 pb-2">
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-buyer-primary text-white border-buyer-primary shadow-sm whitespace-nowrap" data-filter="semua">
        Semua
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="unpaid">
        Belum Bayar
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="verified">
        Diverifikasi / Diproses
    </button>
    <button class="filter-chip px-5 py-2 rounded-full text-xs font-extrabold transition-all border-2 bg-white text-gray-500 border-gray-200 hover:border-buyer-primary whitespace-nowrap" data-filter="rejected">
        Ditolak / Batal
    </button>
</div>

<!-- Container Data Pesanan -->
<div id="orders-container" class="space-y-6">
    <p id="loading-text" class="text-center font-bold text-gray-500 animate-pulse py-10">Memuat data pesanan...</p>
</div>

<!-- Template Kosong -->
<div id="empty-state" class="hidden bg-buyer-bg p-12 rounded-[2rem] text-center shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white">
    <div class="w-16 h-16 bg-buyer-primary/10 text-buyer-primary rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">🛍️</div>
    <h3 class="font-extrabold text-buyer-textPrimary text-lg mb-1">Belum Ada Pesanan di Kategori Ini</h3>
    <p class="text-buyer-textSecondary text-sm font-semibold mb-6">Coba cek tab status yang lain atau mulai belanja lagi yuk!</p>
    <a href="{{ route('katalog') }}" class="inline-block py-3 px-6 bg-buyer-primary text-white font-extrabold text-sm rounded-xl shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4)] hover:-translate-y-[2px] transition-all">Mulai Belanja</a>
</div>

<!-- Modal Upload -->
<div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800">Upload Bukti Pembayaran</h3>
            <button type="button" onclick="$('#uploadModal').addClass('hidden')" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); alert('Modul upload sedang dibangun cuy!'); $('#uploadModal').addClass('hidden');" class="space-y-4">
            <input type="file" accept="image/*" required class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 cursor-pointer">
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="$('#uploadModal').addClass('hidden')" class="flex-1 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-purple-600 text-white rounded-xl text-xs font-bold transition shadow-sm">Kirim Bukti</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    let allOrders = []; // Variabel global untuk menyimpan data aslinya
    const ordersContainer = $('#orders-container');
    const emptyState = $('#empty-state');

    // Cek Notifikasi Checkout
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('success') === 'true') {
        $('#alert-success').text('Pesanan berhasil dibuat!').removeClass('hidden');
        setTimeout(() => $('#alert-success').addClass('hidden'), 5000);
    }

    // Ambil Data Pesanan dari API
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
            $('#loading-text').remove();
            if(res.status === 'success') {
                allOrders = res.data;
                renderOrders('semua'); // Tampilkan semua saat pertama dimuat
            }
        },
        error: function() {
            $('#loading-text').text('Gagal memuat data pesanan.');
        }
    });

    // Logika Klik Tombol Filter
    $('.filter-chip').click(function() {
        // Reset warna semua tombol
        $('.filter-chip').removeClass('bg-buyer-primary text-white border-buyer-primary').addClass('bg-white text-gray-500 border-gray-200');
        // Set warna tombol yang diklik
        $(this).removeClass('bg-white text-gray-500 border-gray-200').addClass('bg-buyer-primary text-white border-buyer-primary');
        
        // Panggil fungsi render dengan filter baru
        renderOrders($(this).data('filter'));
    });

    // Fungsi Render HTML Pesanan
    function renderOrders(filterType) {
        ordersContainer.empty();
        
        // Saring data berdasarkan filter
        let filteredOrders = allOrders.filter(order => {
            if (filterType === 'semua') return true;
            
            let pStatus = order.payment_status ? order.payment_status.toLowerCase() : '';
            let oStatus = order.status ? order.status.toLowerCase() : '';

            if (filterType === 'unpaid') return pStatus === 'unpaid';
            if (filterType === 'verified') return pStatus === 'verified' || oStatus.includes('proses');
            if (filterType === 'rejected') return pStatus === 'rejected' || oStatus.includes('batal');
            
            return false;
        });

        if (filteredOrders.length === 0) {
            emptyState.removeClass('hidden');
            return;
        }

        emptyState.addClass('hidden');
        let htmlContent = '';
        
        filteredOrders.forEach(order => {
            // Styling Status Badge ala Mobile
            let statusColor = 'bg-gray-100 text-gray-600';
            let pStatus = order.payment_status ? order.payment_status.toLowerCase() : '';
            
            if (pStatus === 'unpaid') statusColor = 'bg-amber-100 text-amber-700 border border-amber-200';
            else if (pStatus === 'verified' || order.status.toLowerCase().includes('proses')) statusColor = 'bg-green-100 text-green-700 border border-green-200';
            else if (pStatus === 'rejected') statusColor = 'bg-red-100 text-red-700 border border-red-200';

            let actionButtons = `<a href="/nota?order_number=${order.order_number}" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-buyer-bg text-buyer-primary font-extrabold text-xs rounded-xl shadow-sm hover:bg-white transition-all">Lihat Nota</a>`;

            if (order.payment_method === 'transfer') {
                if (pStatus === 'unpaid') {
                    actionButtons += `<button onclick="$('#uploadModal').removeClass('hidden')" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-amber-500 text-white font-extrabold text-xs rounded-xl shadow-md hover:bg-amber-600 transition-all">Upload Bukti</button>`;
                } else if (pStatus === 'rejected') {
                    actionButtons += `<button onclick="$('#uploadModal').removeClass('hidden')" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-red-500 text-white font-extrabold text-xs rounded-xl shadow-md hover:bg-red-600 transition-all">Ditolak! Upload Ulang</button>`;
                }
            }

            htmlContent += `
            <div class="bg-white p-5 sm:p-6 rounded-3xl shadow-sm border border-purple-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                
                <div class="space-y-2 w-full md:w-auto">
                    <div class="flex items-center justify-between md:justify-start gap-3 w-full">
                        <span class="text-xs font-bold text-gray-400">${order.date}</span>
                        <span class="px-3 py-1 text-[10px] font-black rounded-full uppercase tracking-wider ${statusColor}">
                            ${pStatus === 'unpaid' ? 'Belum Bayar' : (pStatus === 'verified' ? 'Diverifikasi' : order.status)}
                        </span>
                    </div>

                    <h3 class="font-extrabold text-gray-800 text-base md:text-lg">
                        No. Pesanan: <span class="text-purple-600">${order.order_number}</span>
                    </h3>

                    <div class="text-xs md:text-sm text-gray-500 space-y-1 font-medium">
                        <p><strong class="text-gray-700">Penerima:</strong> ${order.recipient_name} (${order.recipient_whatsapp})</p>
                        <p><strong class="text-gray-700">Alamat:</strong> ${order.shipping_address || 'Belum diatur'}</p>
                    </div>
                </div>

                <div class="flex flex-col md:items-end justify-between w-full md:w-auto gap-3 border-t md:border-t-0 pt-4 md:pt-0 border-gray-100">
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
});
</script>
@endpush