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
                    <p class="text-sm text-gray-500 mb-6">Pilih alamat tersimpan atau masukkan alamat baru.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                            <input type="text" value="{{ auth()->user()->name ?? '' }}" placeholder="Masukkan nama penerima" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                            <input type="text" value="{{ auth()->user()->phone ?? '' }}" placeholder="Contoh: 081234567890" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>

                        {{-- Pilihan Alamat Pengiriman --}}
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-sm font-medium text-gray-700">Alamat Pengiriman</label>
                                <a href="{{ url('/profil') }}" class="text-xs text-purple-600 hover:underline font-semibold">+ Kelola Alamat di Profil</a>
                            </div>
                            <select class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium text-gray-700">
                                <option value="">-- Pilih Alamat Tersimpan --</option>
                                <option value="1" selected>🏠 Rumah - Jl. Raya Kediri No. 12, Kediri</option>
                                <option value="2">🏢 Kantor - Jl. Pemuda No. 45, Kediri</option>
                            </select>
                        </div>

                        {{-- Catatan Pesanan --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pesanan (Opsional)</label>
                            <input type="text" placeholder="Contoh: Titip di satpam / Minta sambal ekstra" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Card Metode Pembayaran (Transfer Bank Only) --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Metode Pembayaran</h2>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-4 rounded-xl border-2 border-purple-500 bg-purple-50 cursor-pointer text-purple-700 font-semibold text-sm">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="transfer" class="accent-purple-600 w-4 h-4" checked>
                                <span>Transfer Bank (BCA / BRI / Mandiri)</span>
                            </div>
                            <span class="text-xs bg-purple-200 text-purple-800 px-2.5 py-1 rounded-full font-bold">Rekomendasi</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">*Nomor rekening tujuan transfer akan muncul pada halaman Nota Digital setelah pesanan dikonfirmasi.</p>
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

                        <a href="{{ url('/nota') }}" class="block w-full py-3.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition duration-200 text-center">
                            Konfirmasi Pesanan
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection