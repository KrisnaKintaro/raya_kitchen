@extends('pembeli.pembeli_master')

@section('content')
<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8" x-data="{ payment: 'transfer' }">
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
                
                {{-- Card Data Pengiriman (Alamat Shopee-like) --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">Data Pengiriman</h2>
                    <p class="text-sm text-gray-500 mb-6">Alamat utama otomatis terpilih. Kamu bisa menggantinya jika perlu.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                            <input type="text" id="penerima_name" value="{{ auth()->user()->name ?? 'Pembeli Raya Kitchen' }}" placeholder="Masukkan nama penerima" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                            <input type="text" value="{{ auth()->user()->phone ?? '' }}" placeholder="Contoh: 081234567890" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>

                        {{-- Pilihan Alamat (Default ke is_primary = true) --}}
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-sm font-medium text-gray-700">Pilih Alamat Pengiriman</label>
                                <a href="{{ url('/profil') }}" class="text-xs text-purple-600 hover:underline font-semibold">+ Tambah / Kelola Alamat</a>
                            </div>
                            <select id="alamat_select" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium text-gray-700">
                                <option value="🏠 [Utama] Rumah - Jl. Raya Kediri No. 12, Kediri" selected>🏠 [Utama] Rumah - Jl. Raya Kediri No. 12, Kediri</option>
                                <option value="🏢 Kantor - Jl. Pemuda No. 45, Kediri">🏢 Kantor - Jl. Pemuda No. 45, Kediri</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pesanan (Opsional)</label>
                            <input type="text" placeholder="Contoh: Titip di satpam / Minta sambal ekstra" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Card Metode Pembayaran (Transfer Bank & COD) --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Metode Pembayaran</h2>
                    <div class="space-y-3">
                        {{-- Opsi Transfer Bank --}}
                        <label @click="payment = 'transfer'" :class="payment === 'transfer' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 bg-white text-gray-700'" class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer font-semibold text-sm transition">
                            <div class="flex items-center gap-3">
                                <input type="radio" id="pay_transfer" name="payment_method" value="transfer" class="accent-purple-600 w-4 h-4" checked>
                                <span>Transfer Bank (BCA / BRI / Mandiri)</span>
                            </div>
                            <span class="text-xs bg-purple-200 text-purple-800 px-2.5 py-1 rounded-full font-bold">Upload Bukti</span>
                        </label>

                        {{-- Opsi COD --}}
                        <label @click="payment = 'cod'" :class="payment === 'cod' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 bg-white text-gray-700'" class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer font-semibold text-sm transition">
                            <div class="flex items-center gap-3">
                                <input type="radio" id="pay_cod" name="payment_method" value="cod" class="accent-purple-600 w-4 h-4">
                                <span>COD (Bayar di Tempat)</span>
                            </div>
                            <span class="text-xs bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full font-bold">Status: Unpaid</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">*Pesanan dengan metode apapun akan tercatat berstatus <strong class="text-gray-600">Unpaid</strong> hingga diverifikasi/diupdate oleh Admin.</p>
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

                        {{-- Tombol Konfirmasi Pesanan (Interaktif) --}}
                        <button type="button" onclick="prosesKonfirmasi(event)" class="w-full py-3.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition duration-200 text-center cursor-pointer">
                            Konfirmasi Pesanan
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- SCRIPT JAVASCRIPT UNTUK PINDAH KE NOTA SECARA DINAMIS --}}
<script>
function prosesKonfirmasi(e) {
    if (e) e.preventDefault();
    
    // 1. Cek radio button mana yang terpilih
    let selectedMethod = 'transfer';
    let radioCod = document.getElementById('pay_cod');
    if (radioCod && radioCod.checked) {
        selectedMethod = 'cod';
    }

    // 2. Ambil alamat dari dropdown select
    let selectAddress = document.getElementById('alamat_select');
    let addressVal = selectAddress ? selectAddress.value : '🏠 [Utama] Rumah - Jl. Raya Kediri No. 12, Kediri';

    // 3. Pindah ke halaman nota
    let destinationUrl = "{{ url('/nota') }}?method=" + selectedMethod + "&address=" + encodeURIComponent(addressVal);
    window.location.href = destinationUrl;
}
</script>
@endsection