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
                <span class="text-base font-black text-admin-textMain">2 Akun Terdaftar</span>
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
                            <button onclick="showDetailModal('Budi Setiawan', 'budi@gmail.com', '081234567890', 'Aktif')" class="text-admin-sideBg hover:opacity-80 mx-1" title="Detail Pelanggan"><i class="ph ph-eye text-lg"></i></button>
                            <button onclick="showSuccessModal('Status akun Budi Setiawan berhasil diubah!')" class="text-rose-500 hover:text-rose-600 mx-1" title="Blokir Akun"><i class="ph ph-lock text-lg"></i></button>
                        </td>
                    </tr>
                    <tr class="border-b border-admin-border/50">
                        <td class="py-4 px-4">2</td>
                        <td class="py-4 px-4 font-bold">Siti Rahma</td>
                        <td class="py-4 px-4">siti.rahma@gmail.com</td>
                        <td class="py-4 px-4">089876543210</td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold">Aktif</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <button onclick="showDetailModal('Siti Rahma', 'siti.rahma@gmail.com', '089876543210', 'Aktif')" class="text-admin-sideBg hover:opacity-80 mx-1" title="Detail Pelanggan"><i class="ph ph-eye text-lg"></i></button>
                            <button onclick="showSuccessModal('Status akun Siti Rahma berhasil diubah!')" class="text-rose-500 hover:text-rose-600 mx-1" title="Blokir Akun"><i class="ph ph-lock text-lg"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DETAIL PELANGGAN (Kustom & Cantik) -->
    <div id="modalDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-[2rem] p-6 lg:p-8 w-full max-w-md shadow-xl border border-admin-border">
            <div class="w-12 h-12 bg-admin-sideBg/10 text-admin-sideBg rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                <i class="ph ph-user"></i>
            </div>
            <h3 class="text-lg font-black text-admin-textMain text-center mb-4">Detail Informasi Pelanggan</h3>
            <div class="space-y-3 bg-gray-50 p-4 rounded-2xl text-sm mb-6 border border-admin-border/50">
                <div class="flex justify-between"><span class="text-admin-textMuted">Nama:</span> <span id="detName" class="font-bold text-admin-textMain"></span></div>
                <div class="flex justify-between"><span class="text-admin-textMuted">Email:</span> <span id="detEmail" class="font-bold text-admin-textMain"></span></div>
                <div class="flex justify-between"><span class="text-admin-textMuted">No. Telepon:</span> <span id="detPhone" class="font-bold text-admin-textMain"></span></div>
                <div class="flex justify-between"><span class="text-admin-textMuted">Status:</span> <span id="detStatus" class="font-bold text-emerald-600"></span></div>
            </div>
            <button onclick="closeModal('modalDetail')" class="w-full py-3 bg-admin-sideBg text-white rounded-xl text-sm font-bold shadow-sm hover:opacity-90 transition-all">
                Tutup
            </button>
        </div>
    </div>

    <!-- MODAL NOTIFIKASI SUKSES -->
    <div id="modalSuccess" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-[2rem] p-6 lg:p-8 w-full max-w-sm text-center shadow-xl border border-admin-border">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold">
                <i class="ph ph-check"></i>
            </div>
            <h3 class="text-lg font-black text-admin-textMain mb-1">Berhasil!</h3>
            <p id="successMessage" class="text-xs text-admin-textMuted mb-6">Pesan sukses...</p>
            <button onclick="closeModal('modalSuccess')" class="w-full py-3 bg-admin-sideBg text-white rounded-xl text-sm font-bold shadow-sm hover:opacity-90 transition-all">
                OK
            </button>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function showDetailModal(name, email, phone, status) {
        document.getElementById('detName').innerText = name;
        document.getElementById('detEmail').innerText = email;
        document.getElementById('detPhone').innerText = phone;
        document.getElementById('detStatus').innerText = status;
        openModal('modalDetail');
    }
    function showSuccessModal(message) {
        document.getElementById('successMessage').innerText = message;
        openModal('modalSuccess');
    }
</script>
@endpush