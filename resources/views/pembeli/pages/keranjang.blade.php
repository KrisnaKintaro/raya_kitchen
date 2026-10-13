@extends('pembeli.pembeli_master')

@section('title', 'Keranjang Saya - Raya Kitchen')

@section('css')
<style>
    /* Styling tambahan khusus keranjang jika diperlukan */
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
        Keranjang Belanja
    </h2>
    <p class="text-buyer-textSecondary font-semibold text-sm md:text-base">
        Periksa kembali pesanan roti & kue lezatmu sebelum checkout!
    </p>
</div>

<!-- Layout Utama Keranjang (Grid: Kiri Daftar Item, Kanan Ringkasan Belanja) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{
    shippingFee: 10000,
    items: [
        {
            id: 1,
            name: 'Croissant Almond Special',
            price: 25000,
            qty: 2,
            image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=300&q=80'
        }
    ],
    get totalQty() {
        return this.items.reduce((sum, item) => sum + item.qty, 0);
    },
    get subtotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },
    get grandTotal() {
        return this.items.length > 0 ? this.subtotal + this.shippingFee : 0;
    },
    formatRupiah(number) {
        return 'Rp ' + number.toLocaleString('id-ID');
    },
    increaseQty(item) {
        item.qty++;
    },
    decreaseQty(item) {
        if (item.qty > 1) {
            item.qty--;
        }
    },
    removeItem(index) {
        this.items.splice(index, 1);
    }
}">
    
    <!-- KOLOM KIRI: Daftar Produk di Keranjang -->
    <div class="lg:col-span-2 space-y-4" id="cart-items-container">
        
        <template x-for="(item, index) in items" :key="item.id">
            <div class="bg-buyer-bg p-4 sm:p-5 rounded-[2rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white flex flex-col sm:flex-row items-center gap-4">
                <div class="w-24 h-24 rounded-2xl overflow-hidden shadow-[inset_0_2px_8px_rgba(0,0,0,0.1)] bg-gray-100 flex-shrink-0">
                    <img :src="item.image" class="w-full h-full object-cover" :alt="item.name">
                </div>
                
                <div class="flex-grow text-center sm:text-left">
                    <h4 class="font-extrabold text-buyer-textPrimary text-base lg:text-lg mb-1" x-text="item.name"></h4>
                    <p class="font-black text-buyer-primary text-base mb-3" x-text="formatRupiah(item.price)"></p>
                    
                    <!-- Tombol Jumlah (Qty) Interaktif -->
                    <div class="flex items-center justify-center sm:justify-start gap-3">
                        <button type="button" @click="decreaseQty(item)" class="w-8 h-8 rounded-xl bg-buyer-bg shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_2px_5px_rgba(192,153,206,0.15)] font-extrabold text-buyer-primary hover:bg-white active:scale-95 transition-all flex items-center justify-center cursor-pointer">
                            -
                        </button>
                        <span class="font-extrabold text-buyer-textPrimary text-sm min-w-[20px] text-center" x-text="item.qty"></span>
                        <button type="button" @click="increaseQty(item)" class="w-8 h-8 rounded-xl bg-buyer-bg shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_2px_5px_rgba(192,153,206,0.15)] font-extrabold text-buyer-primary hover:bg-white active:scale-95 transition-all flex items-center justify-center cursor-pointer">
                            +
                        </button>
                    </div>
                </div>

                <!-- Tombol Hapus Item -->
                <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 p-2 transition-colors cursor-pointer" title="Hapus item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </template>

        <!-- Tampilan Keranjang Kosong -->
        <div x-show="items.length === 0" class="bg-white p-8 rounded-[2rem] text-center border border-purple-100 shadow-sm">
            <p class="text-gray-500 font-medium text-sm mb-4">Keranjang belanjaanmu masih kosong nih 😢</p>
          <a href="{{ route('katalog') }}" class="inline-block py-2.5 px-6 bg-purple-600 text-white font-bold text-xs rounded-xl hover:bg-purple-700 transition">
                Cari Roti Dulu Yuk
          </a>
        </div>

    </div>

    <!-- KOLOM KANAN: Ringkasan Belanja / Checkout Box -->
    <div class="lg:col-span-1">
        <div class="bg-buyer-bg p-6 rounded-[2rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white sticky top-8">
            <h3 class="font-extrabold text-buyer-textPrimary text-lg mb-4">Ringkasan Belanja</h3>
            
            <div class="space-y-3 text-sm text-buyer-textSecondary mb-6 border-b border-buyer-primary/10 pb-4">
                <div class="flex justify-between">
                    <span>Total Item (<span x-text="totalQty"></span>)</span>
                    <span class="font-bold text-buyer-textPrimary" x-text="formatRupiah(subtotal)"></span>
                </div>
                <div class="flex justify-between">
                    <span>Biaya Pengiriman</span>
                    <span class="font-bold text-buyer-textPrimary" x-text="items.length > 0 ? formatRupiah(shippingFee) : 'Rp 0'"></span>
                </div>
            </div>

            <div class="flex justify-between items-center mb-6">
                <span class="font-extrabold text-buyer-textPrimary text-base">Total Harga</span>
                <span class="font-black text-buyer-primary text-xl" x-text="formatRupiah(grandTotal)"></span>
            </div>

            {{-- Link Aktif ke Checkout --}}
            <a href="{{ url('/checkout') }}" 
               :class="items.length === 0 ? 'pointer-events-none opacity-50' : ''"
               class="block w-full py-3 bg-buyer-primary text-white font-extrabold text-sm rounded-xl shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)] hover:-translate-y-[2px] active:translate-y-0.5 transition-all text-center">
                Lanjut ke Checkout
            </a>
        </div>
    </div>

</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    console.log("Halaman keranjang interaktif siap!");
});
</script>
@endpush