@extends('pembeli.pembeli_master')

@section('title', 'Riwayat Pesanan - Raya Kitchen')

@section('content')
<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-6">
        
        {{-- Header Title --}}
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Aktivitas Akun</span>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Riwayat Pesanan Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar transaksi dan status pengiriman roti favoritmu.</p>
        </div>

        {{-- List Riwayat Pesanan (Card Container) --}}
        <div class="space-y-4">
            
            {{-- Item Pesanan 1 (Status: Selesai) --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-purple-300 transition">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-900 text-base">#RK-20261007-01</span>
                        <span class="bg-green-100 text-green-700 text-xs font-extrabold px-2.5 py-1 rounded-full">Selesai</span>
                    </div>
                    <p class="text-xs text-gray-400">07 Okt 2026, 14:00 WIB • 3 Item</p>
                    <p class="text-sm font-semibold text-gray-700">Roti Coklat (x2), Roti Sosis (x1)</p>
                </div>
                <div class="flex flex-col md:items-end gap-2 w-full md:w-auto">
                    <span class="text-lg font-black text-purple-700">Rp 37.000</span>
                    <a href="{{ url('/nota') }}" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs rounded-xl transition text-center">
                        Lihat Nota
                    </a>
                </div>
            </div>

            {{-- Item Pesanan 2 (Status: Diproses / Menunggu Pembayaran) --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-purple-300 transition">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-900 text-base">#RK-20261011-04</span>
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-extrabold px-2.5 py-1 rounded-full">Menunggu Pembayaran</span>
                    </div>
                    <p class="text-xs text-gray-400">11 Okt 2026, 09:30 WIB • 1 Item</p>
                    <p class="text-sm font-semibold text-gray-700">Roti Keju Spesial (x1)</p>
                </div>
                <div class="flex flex-col md:items-end gap-2 w-full md:w-auto">
                    <span class="text-lg font-black text-purple-700">Rp 15.000</span>
                    <a href="{{ url('/checkout') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl transition text-center shadow-sm">
                        Bayar Sekarang
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection