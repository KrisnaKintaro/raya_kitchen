@extends('admin.admin_master')

<!-- Title untuk tab browser -->
@section('title', 'Ringkasan Finansial - Admin Raya Kitchen')

<!-- Ini yang bakal ngubah teks di Kapsul Navbar lu cuy! -->
@section('header_title', 'Ringkasan Finansial')

@section('content')
    <!-- KONTEN HALAMAN RINGKASAN FINANSIAL -->

    <!-- Contoh 3 Card Statistik Atas (Sesuai gaya SaaS High-Density) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <!-- Card 1: Pendapatan -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-5 interactive-element">
            <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center shrink-0">
                <i class="ph ph-money text-3xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-admin-textMuted uppercase tracking-wider mb-1">Pendapatan Hari Ini</p>
                <h3 class="text-2xl font-black text-admin-textMain tracking-tight">Rp 2.450.000</h3>
            </div>
        </div>

        <!-- Card 2: Pesanan Masuk -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-5 interactive-element">
            <div class="w-14 h-14 bg-admin-accent/20 text-admin-sideBg rounded-2xl flex items-center justify-center shrink-0">
                <i class="ph ph-shopping-bag text-3xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-admin-textMuted uppercase tracking-wider mb-1">Pesanan Baru</p>
                <h3 class="text-2xl font-black text-admin-textMain tracking-tight">18 <span class="text-sm font-bold text-admin-textMuted">Order</span></h3>
            </div>
        </div>

        <!-- Card 3: Menunggu Pembayaran -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center gap-5 interactive-element">
            <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center shrink-0">
                <i class="ph ph-hourglass-high text-3xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-admin-textMuted uppercase tracking-wider mb-1">Menunggu Bayar</p>
                <h3 class="text-2xl font-black text-admin-textMain tracking-tight">5 <span class="text-sm font-bold text-admin-textMuted">Order</span></h3>
            </div>
        </div>

    </div>

    <!-- Area Chart / Tabel -->
    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 min-h-[400px] flex items-center justify-center">
        <div class="text-center">
            <i class="ph ph-chart-bar text-6xl text-admin-textMuted/30 mb-4 inline-block"></i>
            <h4 class="font-bold text-admin-textMain">Area Grafik Penjualan</h4>
            <p class="text-sm text-admin-textMuted">Nanti lu bisa pasang chart.js atau apexcharts di sini cuy.</p>
        </div>
    </div>

@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Nanti AJAX buat narik data chart atau angka ringkasan ditaruh di sini
        // Begitu lu panggil $.ajax(), loader ungu di atas navbar bakal otomatis jalan
    });
</script>
@endpush
