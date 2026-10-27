@extends('admin.admin_master')

@section('title', 'Kelola Akses Admin & Staf - Raya Kitchen')
@section('header_title', 'Kelola Akses')

@section('css')
<!-- Tambahan CSS khusus jika diperlukan -->
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-black text-admin-textMain">Manajemen Akses Admin & Staf</h2>
        <button class="px-5 py-3 bg-admin-sideBg text-white rounded-2xl font-bold text-sm shadow-sm hover:opacity-90 transition-all">
            + Tambah Akun Baru
        </button>
    </div>

    <!-- Card Tabel Data Akses -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border p-6 lg:p-8">
        <div class="table-responsive">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-admin-border text-admin-textMuted text-xs uppercase tracking-wider">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role / Akses</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-admin-textMain text-sm font-medium">
                    <tr class="border-b border-admin-border/50">
                        <td class="py-4 px-4">1</td>
                        <td class="py-4 px-4 font-bold">Admin Utama</td>
                        <td class="py-4 px-4">admin@rayakitchen.com</td>
                        <td class="py-4 px-4"><span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-full text-xs font-bold">Super Admin</span></td>
                        <td class="py-4 px-4 text-center">
                            <button class="text-amber-500 hover:text-amber-600 mx-1" title="Reset Password"><i class="ph ph-key text-lg"></i></button>
                            <button class="text-rose-500 hover:text-rose-600 mx-1" title="Hapus"><i class="ph ph-trash text-lg"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
<!-- Script JS tambahan jika diperlukan -->
@endpush