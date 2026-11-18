@extends('admin.admin_master')

@section('title', 'Kelola Akses Admin & Staf - Raya Kitchen')
@section('header_title', 'Kelola Akses')

@section('content')
<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-lg font-black text-admin-textMain">Manajemen Akses Admin & Staf</h2>
            <p class="text-xs text-admin-textMuted mt-0.5">Kelola akun pengelola toko beserta hak akses login menggunakan nomor WhatsApp.</p>
        </div>
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
                        <th class="py-3 px-4">No. WhatsApp</th>
                        <th class="py-3 px-4">Role / Akses</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyAkses" class="text-admin-textMain text-sm font-medium">
                    <tr>
                        <td colspan="5" class="py-8 text-center text-admin-textMuted text-xs">Memuat data akun pengelola...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH AKUN -->
    <div id="modalTambah" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-[2rem] p-6 lg:p-8 w-full max-w-md shadow-xl border border-admin-border animate-scaleUp">
            <h3 class="text-lg font-black text-admin-textMain mb-4">Tambah Akun Admin / Staf</h3>
            <form id="formTambahAkses" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="name" class="w-full px-4 py-3 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg" placeholder="Masukkan nama lengkap..." required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Nomor WhatsApp (Untuk Login)</label>
                        <input type="text" name="whatsapp_number" class="w-full px-4 py-3 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg" placeholder="Contoh: 081234567890" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Password Awal</label>
                        <input type="password" name="password" minlength="6" class="w-full px-4 py-3 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Role / Akses</label>
                        <select name="role" class="w-full px-4 py-3 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg">
                            <option value="admin">Admin</option>
                            <option value="staf">Staf Operasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Foto Profil (Opsional)</label>
                        <input type="file" name="profile_photo" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-admin-border text-xs text-admin-textMuted">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('modalTambah')" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold">Batal</button>
                    <button type="submit" id="btnSimpanAkses" class="px-5 py-2 bg-admin-sideBg text-white rounded-xl text-sm font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL RESET PASSWORD -->
    <div id="modalResetPassword" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-[2rem] p-6 lg:p-8 w-full max-w-md shadow-xl border border-admin-border">
            <h3 class="text-lg font-black text-admin-textMain mb-1">Reset Password Akun</h3>
            <p id="resetTargetName" class="text-xs text-admin-textMuted mb-4">Masukkan password baru untuk akun ini.</p>
            <form id="formResetPassword">
                <input type="hidden" id="resetUserId">
                <div>
                    <label class="block text-xs font-bold text-admin-textMuted uppercase mb-1">Password Baru</label>
                    <input type="password" id="newPasswordInput" minlength="6" class="w-full px-4 py-3 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg" placeholder="Minimal 6 karakter..." required>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal('modalResetPassword')" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 text-white rounded-xl text-sm font-bold">Update Password</button>
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
            <p id="deleteTargetText" class="text-xs text-admin-textMuted mb-6">Tindakan ini tidak dapat dibatalkan dari sistem.</p>
            <input type="hidden" id="deleteUserId">
            <div class="flex justify-center gap-3">
                <button onclick="closeModal('modalHapus')" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold">Batal</button>
                <button onclick="confirmDeleteAkses()" class="px-5 py-2 bg-rose-500 text-white rounded-xl text-sm font-bold">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- MODAL NOTIFIKASI -->
    <div id="modalSuccess" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-[2rem] p-6 lg:p-8 w-full max-w-sm text-center shadow-xl border border-admin-border">
            <div id="notifIconWrap" class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold">
                <i id="notifIcon" class="ph ph-check"></i>
            </div>
            <h3 id="notifTitle" class="text-lg font-black text-admin-textMain mb-1">Berhasil!</h3>
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function showNotifModal(message, isError = false) {
        const titleEl = document.getElementById('notifTitle');
        const iconWrap = document.getElementById('notifIconWrap');
        const iconEl = document.getElementById('notifIcon');

        if (isError) {
            titleEl.innerText = 'Gagal!';
            iconWrap.className = 'w-14 h-14 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold';
            iconEl.className = 'ph ph-x';
        } else {
            titleEl.innerText = 'Berhasil!';
            iconWrap.className = 'w-14 h-14 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold';
            iconEl.className = 'ph ph-check';
        }

        document.getElementById('successMessage').innerText = message;
        openModal('modalSuccess');
    }

    function formatRoleBadge(role) {
        if (role === 'admin' || role === 'super_admin' || role === 'pemilik_toko') {
            return `<span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-full text-xs font-bold uppercase">${role.replace('_', ' ')}</span>`;
        }
        return `<span class="px-3 py-1 bg-purple-50 text-admin-sideBg rounded-full text-xs font-bold uppercase">${role}</span>`;
    }

    async function loadDataAkses() {
        const tbody = document.getElementById('tbodyAkses');
        try {
            const res = await fetch('/api/admin/kelola-akses', {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();

            if (json.status === 'success') {
                if (json.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-admin-textMuted text-xs">Belum ada data akun admin/staf.</td></tr>`;
                    return;
                }

                tbody.innerHTML = json.data.map((item, idx) => {
                    const avatar = item.profile_photo_url
                        ? `<img src="${item.profile_photo_url}" class="w-8 h-8 rounded-full object-cover border border-admin-border">`
                        : `<div class="w-8 h-8 rounded-full bg-admin-sideBg/10 text-admin-sideBg flex items-center justify-center text-xs font-black">${item.name.charAt(0).toUpperCase()}</div>`;

                    return `
                        <tr class="border-b border-admin-border/50 hover:bg-gray-50/50 transition-colors">
                            <td class="py-4 px-4">${idx + 1}</td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    ${avatar}
                                    <span class="font-bold">${item.name}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">${item.whatsapp_number}</td>
                            <td class="py-4 px-4">${formatRoleBadge(item.role)}</td>
                            <td class="py-4 px-4 text-center">
                                <button onclick="openResetModal(${item.id}, '${item.name.replace(/'/g, "\\'")}')" class="text-amber-500 hover:text-amber-600 mx-1" title="Reset Password">
                                    <i class="ph ph-key text-lg"></i>
                                </button>
                                <button onclick="openDeleteModal(${item.id}, '${item.name.replace(/'/g, "\\'")}')" class="text-rose-500 hover:text-rose-600 mx-1" title="Hapus Akun">
                                    <i class="ph ph-trash text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-rose-500 text-xs">Gagal memuat data dari server.</td></tr>`;
        }
    }

    // Submit Tambah Akun
    document.getElementById('formTambahAkses').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanAkses');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        const formData = new FormData(this);
        try {
            const res = await fetch('/api/admin/kelola-akses', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const json = await res.json();

            if (res.ok && json.status === 'success') {
                this.reset();
                closeModal('modalTambah');
                showNotifModal(json.message);
                loadDataAkses();
            } else {
                const errMsg = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal menyimpan akun.');
                showNotifModal(errMsg, true);
            }
        } catch (err) {
            showNotifModal('Terjadi kesalahan koneksi server.', true);
        } finally {
            btn.disabled = false;
            btn.innerText = 'Simpan';
        }
    });

    // Reset Password
    function openResetModal(id, name) {
        document.getElementById('resetUserId').value = id;
        document.getElementById('resetTargetName').innerText = `Atur password baru untuk akun: ${name}`;
        document.getElementById('newPasswordInput').value = '';
        openModal('modalResetPassword');
    }

    document.getElementById('formResetPassword').addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('resetUserId').value;
        const newPassword = document.getElementById('newPasswordInput').value;

        try {
            const res = await fetch(`/api/admin/kelola-akses/${id}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ new_password: newPassword })
            });
            const json = await res.json();

            if (res.ok && json.status === 'success') {
                closeModal('modalResetPassword');
                showNotifModal(json.message);
            } else {
                showNotifModal(json.message || 'Gagal mereset password.', true);
            }
        } catch (err) {
            showNotifModal('Terjadi kesalahan koneksi server.', true);
        }
    });

    // Hapus Akun
    function openDeleteModal(id, name) {
        document.getElementById('deleteUserId').value = id;
        document.getElementById('deleteTargetText').innerText = `Hapus permanen akun "${name}" dari sistem?`;
        openModal('modalHapus');
    }

    async function confirmDeleteAkses() {
        const id = document.getElementById('deleteUserId').value;
        try {
            const res = await fetch(`/api/admin/kelola-akses/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const json = await res.json();

            closeModal('modalHapus');
            if (res.ok && json.status === 'success') {
                showNotifModal(json.message);
                loadDataAkses();
            } else {
                showNotifModal(json.message || 'Gagal menghapus akun.', true);
            }
        } catch (err) {
            closeModal('modalHapus');
            showNotifModal('Terjadi kesalahan koneksi server.', true);
        }
    }

    document.addEventListener('DOMContentLoaded', loadDataAkses);
</script>
@endpush