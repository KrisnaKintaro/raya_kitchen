@extends('admin.admin_master')

@section('title', 'Daftar Pelanggan - Admin Raya Kitchen')
@section('header_title', 'Kelola Pelanggan')

@section('content')
<div class="container-fluid">
    <!-- Header Halaman & Total Counter -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-black text-admin-textMain">Daftar Pelanggan Terdaftar</h2>
            <p class="text-xs text-admin-textMuted mt-0.5">Pantau data pembeli dan pelanggan yang aktif di platform.</p>
        </div>

        <!-- Badge / Kotak Jumlah Total Akun -->
        <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-2xl shadow-sm border border-admin-border">
            <div class="w-10 h-10 rounded-xl bg-admin-sideBg/10 text-admin-sideBg flex items-center justify-center font-bold">
                <i class="ph ph-users text-xl"></i>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-bold text-admin-textMuted tracking-wider">Total Pelanggan</span>
                <!-- Nanti angka di bawah ini bisa diganti variabel dynamic backend, misal {{ $totalPelanggan ?? 1 }} -->
                <span class="text-base font-black text-admin-textMain">1 Akun Terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Card Tabel Data Pelanggan -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border p-6 lg:p-8">
        <div class="table-responsive">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-admin-border text-admin-textMuted text-xs uppercase tracking-wider">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Nama Pelanggan</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">No. Telepon</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-admin-textMain text-sm font-medium">
                    <tr class="border-b border-admin-border/50">
                        <td class="py-4 px-4">1</td>
                        <td class="py-4 px-4 font-bold">Budi Setiawan</td>
                        <td class="py-4 px-4">budi@gmail.com</td>
                        <td class="py-4 px-4">081234567890</td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold">Aktif</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <button class="text-admin-sideBg hover:opacity-80 mx-1" title="Detail Pelanggan"><i class="ph ph-eye text-lg"></i></button>
                            <button class="text-rose-500 hover:text-rose-600 mx-1" title="Blokir Akun"><i class="ph ph-lock text-lg"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection