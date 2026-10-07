@extends('pembeli.pembeli_master')

@section('content')
<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        
        {{-- Header Title --}}
        <div class="mb-6">
            <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Proses Pesanan</span>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Konfirmasi Pesanan</h1>
        </div>

        {{-- Grid Container (2 Kolom) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- KOLOM KIRI: Data Pengiriman & Metode Pembayaran --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Card Data Pengiriman --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">Data Pengiriman</h2>
                    <p class="text-sm text-gray-500 mb-6">Pastikan informasi penerima sudah benar.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                            <input type="text" value="Andi Pratama" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                            <input type="text" value="+62 812 3456 7890" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Pengiriman</label>
                            <textarea rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm resize-none" readonly>Jl. Contoh No. 12, Kediri</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                            <input type="text" placeholder="Contoh: Titip di satpam / Minta sambal ekstra" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Card Metode Pembayaran --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Metode Pembayaran</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center justify-center p-3 rounded-xl border-2 border-purple-500 bg-purple-50 cursor-pointer text-purple-700 font-semibold text-sm">
                            <input type="radio" name="payment_method" value="transfer" class="hidden" checked>
                            Transfer
                        </label>
                        <label class="flex items-center justify-center p-3 rounded-xl border-2 border-gray-200 bg-white cursor-pointer text-gray-600 font-semibold text-sm hover:border-purple-300">
                            <input type="radio" name="payment_method" value="cod" class="hidden">
                            COD (Bayar di Tempat)
                        </label>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN: Ringkasan Pesanan --}}
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100 sticky top-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Pesanan</h2>

                    {{-- List Item Roti --}}
                    <div class="space-y-3 pb-4 border-b border-gray-100 text-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">Roti Coklat <span class="text-purple-600">x 2</span></p>
                                <p class="text-xs text-gray-400">Roti lembut isi coklat</p>
                            </div>
                            <span class="font-medium text-gray-700">Rp 20.000</span>
                        </div>

                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">Roti Sosis <span class="text-purple-600">x 1</span></p>
                                <p class="text-xs text-gray-400">Roti gurih + sosis</p>
                            </div>
                            <span class="font-medium text-gray-700">Rp 12.000</span>
                        </div>
                    </div>

                    {{-- Hitungan Subtotal & Ongkir --}}
                    <div class="py-4 space-y-2 border-b border-gray-100 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span>Rp 32.000</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Ongkir</span>
                            <span>Rp 5.000</span>
                        </div>
                    </div>

                    {{-- Total Harga & Tombol Konfirmasi --}}
                    <div class="pt-4">
                        <div class="flex justify-between items-center mb-6">
                            <span class="font-bold text-gray-800">Total</span>
                            <span class="text-xl font-extrabold text-purple-700">Rp 37.000</span>
                        </div>

                        <button type="button" class="w-full py-3.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition duration-200 text-center">
                            Konfirmasi Pesanan
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection