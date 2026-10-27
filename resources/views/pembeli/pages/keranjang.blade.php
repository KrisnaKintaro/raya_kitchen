@extends('pembeli.pembeli_master')
@section('title', 'Keranjang Saya - Raya Kitchen')

@section('content')
<div class="mb-8 text-center md:text-left">
    <h2 class="text-3xl md:text-4xl font-black text-buyer-primary drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)] mb-3">Keranjang Belanja</h2>
    <p class="text-buyer-textSecondary font-semibold text-sm md:text-base">Periksa kembali pesanan roti & kue lezatmu sebelum checkout!</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- KOLOM KIRI: Daftar Produk -->
    <div class="lg:col-span-2 space-y-4" id="cart-items-container">
        <p class="text-center font-bold text-gray-500 animate-pulse py-10">Memuat keranjang...</p>
    </div>

    <!-- KOLOM KANAN: Ringkasan -->
    <div class="lg:col-span-1">
        <div class="bg-buyer-bg p-6 rounded-[2rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white sticky top-8">
            <h3 class="font-extrabold text-buyer-textPrimary text-lg mb-4">Ringkasan Belanja</h3>
            <div class="space-y-3 text-sm text-buyer-textSecondary mb-6 border-b border-buyer-primary/10 pb-4">
                <div class="flex justify-between">
                    <span>Total Item (<span id="summary-qty">0</span>)</span>
                    <span class="font-bold text-buyer-textPrimary" id="summary-subtotal">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span>Biaya Pengiriman</span>
                    <span class="font-bold text-buyer-textPrimary">Rp 10.000</span>
                </div>
            </div>
            <div class="flex justify-between items-center mb-6">
                <span class="font-extrabold text-buyer-textPrimary text-base">Total Harga</span>
                <span class="font-black text-buyer-primary text-xl" id="summary-total">Rp 0</span>
            </div>
            <a href="{{ url('/checkout') }}" id="btn-checkout" class="block w-full py-3 bg-buyer-primary text-white font-extrabold text-sm rounded-xl shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)] hover:-translate-y-[2px] transition-all text-center pointer-events-none opacity-50">
                Lanjut ke Checkout
            </a>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    loadCart();

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    function loadCart() {
        $.ajax({
            url: '/api/pembeli/cart',
            type: 'GET',
            success: function(res) {
                if(res.status === 'success') {
                    renderCart(res.data.items);
                }
            }
        });
    }

    function renderCart(items) {
        let container = $('#cart-items-container');
        container.empty();
        let totalQty = 0, subtotal = 0;

        if(items.length === 0) {
            container.html(`<div class="bg-white p-8 rounded-[2rem] text-center border border-purple-100 shadow-sm">
                <p class="text-gray-500 font-medium text-sm mb-4">Keranjang belanjaanmu masih kosong nih</p>
                <a href="/katalog" class="inline-block py-2.5 px-6 bg-purple-600 text-white font-bold text-xs rounded-xl hover:bg-purple-700">Cari Roti Dulu Yuk</a>
            </div>`);
            $('#summary-qty').text(0);
            $('#summary-subtotal').text('Rp 0');
            $('#summary-total').text('Rp 0');
            $('#btn-checkout').addClass('pointer-events-none opacity-50');
            return;
        }

        $('#btn-checkout').removeClass('pointer-events-none opacity-50');

        items.forEach(item => {
            totalQty += item.qty;
            subtotal += (item.price * item.qty);
            let imgUrl = item.image.startsWith('http') ? item.image : `/storage/produk/${item.image}`;
            
            container.append(`
                <div class="bg-buyer-bg p-4 sm:p-5 rounded-[2rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white flex flex-col sm:flex-row items-center gap-4">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden shadow-[inset_0_2px_8px_rgba(0,0,0,0.1)] bg-gray-100 shrink-0">
                        <img src="${imgUrl}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-grow text-center sm:text-left">
                        <h4 class="font-extrabold text-buyer-textPrimary text-base lg:text-lg mb-1">${item.name}</h4>
                        <p class="font-black text-buyer-primary text-base mb-3">${formatRupiah(item.price)}</p>
                        <div class="flex items-center justify-center sm:justify-start gap-3">
                            <button class="btn-qty min w-8 h-8 rounded-xl bg-buyer-bg text-buyer-primary font-extrabold shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white transition-all" data-id="${item.id}" data-qty="${item.qty - 1}">-</button>
                            <span class="font-extrabold text-buyer-textPrimary text-sm min-w-[20px] text-center">${item.qty}</span>
                            <button class="btn-qty plus w-8 h-8 rounded-xl bg-buyer-bg text-buyer-primary font-extrabold shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white transition-all" data-id="${item.id}" data-qty="${item.qty + 1}">+</button>
                        </div>
                    </div>
                    <button class="btn-del text-red-400 hover:text-red-600 p-2 transition-colors cursor-pointer" data-id="${item.id}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            `);
        });

        $('#summary-qty').text(totalQty);
        $('#summary-subtotal').text(formatRupiah(subtotal));
        $('#summary-total').text(formatRupiah(subtotal + 10000));
    }

    $(document).on('click', '.btn-qty', function() {
        let qty = $(this).data('qty');
        if(qty < 1) return;
        updateCart($(this).data('id'), qty);
    });

    $(document).on('click', '.btn-del', function() {
        if(confirm('Hapus roti ini dari keranjang?')) deleteCart($(this).data('id'));
    });

    function updateCart(id, qty) {
        $.post('/api/pembeli/cart/update', { item_id: id, qty: qty, _token: $('meta[name="csrf-token"]').attr('content') }, loadCart);
    }

    function deleteCart(id) {
        $.post('/api/pembeli/cart/delete', { item_id: id, _token: $('meta[name="csrf-token"]').attr('content') }, loadCart);
    }
});
</script>
@endpush