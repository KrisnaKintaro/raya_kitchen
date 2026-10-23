@extends('pembeli.pembeli_master')

@section('title', 'Riwayat Pesanan Saya - Raya Kitchen')

@section('css')
<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection

@section('content')
<div class="mb-8 text-center md:text-left">
    <h2 class="text-3xl md:text-4xl font-black text-buyer-primary drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)] mb-3">
        Riwayat Pesanan Saya
    </h2>
    <p class="text-buyer-textSecondary font-semibold text-sm md:text-base">
        Daftar pesanan roti & kue yang pernah kamu buat di Raya Kitchen.
    </p>
</div>

<!-- Alert Sukses (Dikontrol via JS jika ada) -->
<div id="alert-success" class="hidden mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-2xl text-sm font-bold shadow-sm"></div>

<!-- Container Tempat Data Pesanan Muncul -->
<div id="orders-container" class="space-y-6">
    <p id="loading-text" class="text-center font-bold text-gray-500 animate-pulse">Memuat data pesanan...</p>
</div>

<!-- Template Jika Belum Ada Pesanan (Disembunyikan secara default) -->
<div id="empty-state" class="hidden bg-buyer-bg p-12 rounded-[2rem] text-center shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white">
    <div class="w-16 h-16 bg-buyer-primary/10 text-buyer-primary rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">
        🛍️
    </div>
    <h3 class="font-extrabold text-buyer-textPrimary text-lg mb-1">Belum Ada Riwayat Pesanan</h3>
    <p class="text-buyer-textSecondary text-sm font-semibold mb-6">Yuk, mulai pilih roti dan kue favoritmu sekarang!</p>
    
    <a href="{{ route('katalog') }}" class="inline-block py-3 px-6 bg-buyer-primary text-white font-extrabold text-sm rounded-xl shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)] hover:-translate-y-[2px] active:translate-y-0.5 transition-all text-center">
        Mulai Belanja
    </a>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    const ordersContainer = $('#orders-container');
    const loadingText = $('#loading-text');
    const emptyState = $('#empty-state');
    const alertSuccess = $('#alert-success');

    // Cek jika ada parameter ?success=true di URL (misal dari halaman checkout)
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('success') === 'true') {
        alertSuccess.text('Pesanan berhasil dibuat!');
        alertSuccess.removeClass('hidden');
        setTimeout(() => alertSuccess.addClass('hidden'), 5000);
    }

    // Ambil token CSRF dari meta tag header master layout
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Mengambil data JSON menggunakan jQuery AJAX GET
    $.ajax({
        url: '/api/pembeli/riwayat-pesanan-data',
        type: 'GET',
        dataType: 'json',
        xhrFields: {
            withCredentials: true // Wajib agar session cookie web terbawa ke api.php
        },
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(res) {
            loadingText.remove(); // Hapus tulisan loading

            if(res.status === 'success' && res.data.length > 0) {
                let htmlContent = '';
                
                // Looping data JSON dan merakit desain HTML
                res.data.forEach(order => {
                    htmlContent += `
                    <div class="bg-buyer-bg p-5 sm:p-6 rounded-[2rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        
                        <!-- Info Utama Pesanan -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 bg-buyer-primary/10 text-buyer-primary text-xs font-black rounded-full uppercase tracking-wider">
                                    Status: ${order.status}
                                </span>
                                <span class="text-xs font-bold text-gray-400">
                                    ${order.date}
                                </span>
                            </div>

                            <h3 class="font-extrabold text-buyer-textPrimary text-lg">
                                No. Pesanan: <span class="text-buyer-primary">${order.order_number}</span>
                            </h3>

                            <div class="text-sm text-buyer-textSecondary space-y-1 font-medium">
                                <p><strong class="text-buyer-textPrimary">Penerima:</strong> ${order.recipient_name} (${order.recipient_whatsapp})</p>
                                <p><strong class="text-buyer-textPrimary">Alamat Pengiriman:</strong> ${order.shipping_address}</p>
                            </div>
                        </div>

                        <!-- Bagian Total & Aksi -->
                        <div class="flex flex-col md:items-end justify-between w-full md:w-auto gap-3 border-t md:border-t-0 pt-4 md:pt-0 border-buyer-primary/10">
                            <div class="text-left md:text-right">
                                <span class="text-xs font-bold text-buyer-textSecondary block">Total Pembayaran</span>
                                <span class="font-black text-buyer-primary text-xl">${order.total_amount_formatted}</span>
                            </div>

                            <div class="flex items-center gap-2 w-full md:w-auto">
                                <a href="/nota?order_number=${order.order_number}" class="flex-1 md:flex-none text-center py-2.5 px-4 bg-buyer-bg text-buyer-primary font-extrabold text-xs rounded-xl shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_2px_5px_rgba(192,153,206,0.15)] hover:bg-white transition-all">
                                    Lihat Nota
                                </a>
                            </div>
                        </div>

                    </div>
                    `;
                });
                
                ordersContainer.html(htmlContent);
            } else {
                emptyState.removeClass('hidden');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching orders:', error);
            loadingText.text('Gagal memuat data pesanan.');
        }
    });
});
</script>
@endpush
