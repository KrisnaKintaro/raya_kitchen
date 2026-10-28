@extends('admin.admin_master')

@section('title', 'Kelola Akses Admin & Staf - Raya Kitchen')
@section('header_title', 'Kelola Akses')

@section('content')
<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-black text-admin-textMain">Manajemen Akses Admin & Staf</h2>
        <button onclick="openModal('modalTambah')" class="px-5 py-3 bg-admin-sideBg text-white rounded-2xl font-bold text-sm shadow-sm hover:opacity-90 transition-all">
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
                            <button onclick="showSuccessModal('Link reset password berhasil dikirim ke email staf!')" class="text-amber-500 hover:text-amber-600 mx-1" title="Reset Password"><i class="ph ph-key text-lg"></i></button>
                            <button onclick="openModal('modalHapus')" class="text-rose-500 hover:text-rose-600 mx-1" title="Hapus"><i class="ph ph-trash text-lg"></i></button>
                        </td>
                    </tr>
                    <tr class="border-b border-admin-border/50">
                        <td class="py-4 px-4">2</td>
                        <td class="py-4 px-4 font-bold">Siti Staf</td>
                        <td class="py-4 px-4">siti@rayakitchen.com</td>
                        <td class="py-4 px-4"><span class="px-3 py-1 bg-purple-50 text-admin-sideBg rounded-full text-xs font-bold">Staf Operasional</span></td>
                        <td class="py-4 px-4 text-center">
                            <button onclick="showSuccessModal('Link reset password berhasil dikirim ke email staf!')" class="text-amber-500 hover:text-amber-600 mx-1" title="Reset Password"><i class="ph ph-key text-lg"></i></button>
                            <button onclick="openModal('modalHapus')" class="text-rose-500 hover:text-rose-600 mx-1" title="Hapus"><i class="ph ph-trash text-lg"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH AKUN -->
    <div id="modalTambah" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-[2rem] p-6 lg:p-8 w-full max-w-md shadow-xl border border-admin-border animate-scaleUp">
            <h3 class="text-lg font-black text-admin-textMain mb-4">Tambah Akun Admin / Staf</h3>
            <form onsubmit="event.preventDefault(); closeModal('modalTambah'); showSuccessModal('Data akun baru berhasil disimpan secara dummy!');">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Nama Lengkap</label>
                        <input type="text" class="w-full px-4 py-3 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg" placeholder="Masukkan nama..." required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Email</label>
                        <input type="email" class="w-full px-4 py-3 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg" placeholder="email@rayakitchen.com" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Role / Akses</label>
                        <select class="w-full px-4 py-3 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg">
                            <option value="Super Admin">Super Admin</option>
                            <option value="Staf Operasional">Staf Operasional</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('modalTambah')" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-admin-sideBg text-white rounded-xl text-sm font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL HAPUS -->
    <div id="modalHapus" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-[2rem] p-6 lg:p-8 w-full max-w-sm text-center shadow-xl border border-admin-border">
            <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                <i class="ph ph-warning"></i>
            </div>
            <h3 class="text-lg font-black text-admin-textMain mb-2">Hapus Akun Ini?</h3>
            <p class="text-xs text-admin-textMuted mb-6">Tindakan ini tidak dapat dibatalkan dari sistem.</p>
            <div class="flex justify-center gap-3">
                <button onclick="closeModal('modalHapus')" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold">Batal</button>
                <button onclick="closeModal('modalHapus'); showSuccessModal('Akun berhasil dihapus secara dummy!');" class="px-5 py-2 bg-rose-500 text-white rounded-xl text-sm font-bold">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- MODAL NOTIFIKASI SUKSES (Ganti alert bawaan browser) -->
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
    function showSuccessModal(message) {
        document.getElementById('successMessage').innerText = message;
        openModal('modalSuccess');
    }
</script>
@endpush