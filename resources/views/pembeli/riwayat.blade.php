@extends('pembeli.pembeli_master')

@section('content')
<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-6">
        
        {{-- Header Title --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Daftar Transaksi</span>
                <h1 class="text-2xl font-extrabold text-gray-900 mt-2">Riwayat Pesanan</h1>
            </div>
        </div>

        {{-- Filter Tab Status --}}
        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
            <button class="px-4 py-2 bg-purple-600 text-white font-semibold text-sm rounded-xl whitespace-nowrap shadow-sm">Semua</button>
            <button class="px-4 py-2 bg-white text-gray-600 hover:bg-purple-50 font-semibold text-sm rounded-xl whitespace-nowrap border border-gray-200">Sedang Diproses</button>
            <button class="px-4 py-2 bg-white text-gray-600 hover:bg-purple-50 font-semibold text-sm rounded-xl whitespace-nowrap border border-gray-200">Selesai</button>
            <button class="px-4 py-2 bg-white text-gray-600 hover:bg-purple-50 font-semibold text-sm rounded-xl whitespace-nowrap border border-gray-200">Dibatalkan</button>
        </div>

        {{-- Daftar Pesanan (Card List) --}}
        <div class="space-y-4">
            
            {{-- Card 1: Pesanan Sedang Diproses --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100 space-y-4 hover:shadow-md transition">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-800">#RK-20261007-01</span>
                        <span class="text-xs text-gray-400">• 07 Okt 2026</span>
                    </div>
                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">Sedang Diproses</span>
                </div>

                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-bold text-gray-800">Roti Coklat, Roti Sosis</p>
                        <p class="text-xs text-gray-400 mt-0.5">3 Total Barang</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Total Belanja</p>
                        <p class="font-extrabold text-purple-700 text-base">Rp 37.000</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex justify-end gap-3">
                    <a href="/nota" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-semibold text-xs rounded-xl transition border border-purple-200">
                        Lihat Nota
                    </a>
                    <a href="/lacak" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl transition shadow-sm">
                        Lacak Pesanan
                    </a>
                </div>
            </div>

            {{-- Card 2: Pesanan Selesai --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100 space-y-4 hover:shadow-md transition">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-800">#RK-20261001-05</span>
                        <span class="text-xs text-gray-400">• 01 Okt 2026</span>
                    </div>
                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2.5 py-1 rounded-lg border border-green-200">Selesai</span>
                </div>

                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-bold text-gray-800">Roti Keju Spesial</p>
                        <p class="text-xs text-gray-400 mt-0.5">2 Total Barang</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Total Belanja</p>
                        <p class="font-extrabold text-purple-700 text-base">Rp 28.000</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex justify-end gap-3">
                    <a href="/nota" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-semibold text-xs rounded-xl transition border border-purple-200">
                        Lihat Nota
                    </a>
                    <button class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl transition shadow-sm">
                        Beli Lagi
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection