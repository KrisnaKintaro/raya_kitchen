@extends('admin.admin_master')

@section('title', 'Daftar Pelanggan - Admin Raya Kitchen')
@section('header_title', 'Kelola Pelanggan')

@section('content')
<div class="container-fluid">
    <!-- Header Halaman & Total Counter -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-black text-admin-textMain">Daftar Pelanggan Terdaftar</h2>
            <p class="text-xs text-admin-textMuted mt-0.5">Pantau data pembeli, riwayat jumlah pesanan, dan alamat pengiriman tersimpan.</p>
        </div>

        <!-- Badge / Kotak Jumlah Total Akun -->
        <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-2xl shadow-sm border border-admin-border">
            <div class="w-10 h-10 rounded-xl bg-admin-sideBg/10 text-admin-sideBg flex items-center justify-center font-bold">
                <i class="ph ph-users text-xl"></i>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-bold text-admin-textMuted tracking-wider">Total Pelanggan</span>
                <span id="totalPelangganBadge" class="text-base font-black text-admin-textMain">0 Akun Terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Card Tabel Data Pelanggan -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border p-6 lg:p-8">
        <!-- Input Pencarian Cepat -->
        <div class="mb-5">
            <input type="text" id="searchPelanggan" onkeyup="filterPelanggan()" placeholder="Cari nama pelanggan atau nomor WhatsApp..." class="w-full sm:w-80 px-4 py-2.5 rounded-xl border border-admin-border text-sm focus:outline-none focus:border-admin-sideBg">
        </div>

        <div class="table-responsive">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-admin-border text-admin-textMuted text-xs uppercase tracking-wider">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Nama Pelanggan</th>
                        <th class="py-3 px-4">No. WhatsApp</th>
                        <th class="py-3 px-4">Alamat Utama</th>
                        <th class="py-3 px-4 text-center">Total Pesanan</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyPelanggan" class="text-admin-textMain text-sm font-medium">
                    <tr>
                        <td colspan="6" class="py-8 text-center text-admin-textMuted text-xs">Memuat data pelanggan...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DETAIL PELANGGAN -->
    <div id="modalDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-[2rem] p-6 lg:p-8 w-full max-w-lg shadow-xl border border-admin-border">
            <div class="w-12 h-12 bg-admin-sideBg/10 text-admin-sideBg rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-bold">
                <i class="ph ph-user"></i>
            </div>
            <h3 class="text-lg font-black text-admin-textMain text-center mb-4">Detail Informasi Pelanggan</h3>
            
            <div class="space-y-2.5 bg-gray-50 p-4 rounded-2xl text-sm mb-4 border border-admin-border/50">
                <div class="flex justify-between"><span class="text-admin-textMuted">Nama Lengkap:</span> <span id="detName" class="font-bold text-admin-textMain"></span></div>
                <div class="flex justify-between"><span class="text-admin-textMuted">No. WhatsApp:</span> <span id="detPhone" class="font-bold text-admin-textMain"></span></div>
                <div class="flex justify-between"><span class="text-admin-textMuted">Terdaftar Sejak:</span> <span id="detJoined" class="font-bold text-admin-textMain"></span></div>
                <div class="flex justify-between"><span class="text-admin-textMuted">Total Transaksi:</span> <span id="detOrders" class="font-bold text-admin-sideBg"></span></div>
            </div>

            <div class="mb-6">
                <span class="block text-xs font-bold text-admin-textMuted uppercase mb-2">Daftar Alamat Tersimpan</span>
                <div id="detAddressList" class="space-y-2 max-h-40 overflow-y-auto text-xs"></div>
            </div>

            <button onclick="closeModal('modalDetail')" class="w-full py-3 bg-admin-sideBg text-white rounded-xl text-sm font-bold shadow-sm hover:opacity-90 transition-all">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    let allPelanggan = [];

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    async function loadDataPelanggan() {
        const tbody = document.getElementById('tbodyPelanggan');
        try {
            const res = await fetch('/api/admin/pelanggan', {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();

            if (json.status === 'success') {
                allPelanggan = json.data;
                document.getElementById('totalPelangganBadge').innerText = `${json.total} Akun Terdaftar`;
                renderTabelPelanggan(allPelanggan);
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-rose-500 text-xs">Gagal memuat data pelanggan.</td></tr>`;
        }
    }

    function renderTabelPelanggan(list) {
        const tbody = document.getElementById('tbodyPelanggan');
        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-admin-textMuted text-xs">Tidak ada data pelanggan ditemukan.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map((item, idx) => {
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
                    <td class="py-4 px-4 text-xs text-admin-textMuted max-w-xs truncate">${item.primary_address}</td>
                    <td class="py-4 px-4 text-center">
                        <span class="px-3 py-1 bg-purple-50 text-admin-sideBg rounded-full text-xs font-bold">${item.orders_count} Pesanan</span>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <button onclick="showDetailPelanggan(${item.id})" class="text-admin-sideBg hover:opacity-80 mx-1" title="Detail Pelanggan">
                            <i class="ph ph-eye text-lg"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function filterPelanggan() {
        const q = document.getElementById('searchPelanggan').value.toLowerCase();
        const filtered = allPelanggan.filter(p => 
            p.name.toLowerCase().includes(q) || 
            p.whatsapp_number.toLowerCase().includes(q)
        );
        renderTabelPelanggan(filtered);
    }

    function showDetailPelanggan(id) {
        const user = allPelanggan.find(u => u.id === id);
        if (!user) return;

        document.getElementById('detName').innerText = user.name;
        document.getElementById('detPhone').innerText = user.whatsapp_number;
        document.getElementById('detJoined').innerText = user.joined_at;
        document.getElementById('detOrders').innerText = `${user.orders_count} Pesanan`;

        const addrWrap = document.getElementById('detAddressList');
        if (!user.addresses || user.addresses.length === 0) {
            addrWrap.innerHTML = `<div class="p-3 bg-gray-50 rounded-xl text-admin-textMuted text-center">Pelanggan ini belum menyimpan alamat.</div>`;
        } else {
            addrWrap.innerHTML = user.addresses.map(addr => `
                <div class="p-3 bg-gray-50 rounded-xl border border-admin-border/50">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-admin-textMain">${addr.label || 'Alamat'}</span>
                        ${addr.is_primary ? '<span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[10px] font-bold">Utama</span>' : ''}
                    </div>
                    <p class="text-admin-textMuted">${addr.street_address}, ${addr.district}, ${addr.city}, ${addr.province} ${addr.postal_code}</p>
                </div>
            `).join('');
        }

        openModal('modalDetail');
    }

    document.addEventListener('DOMContentLoaded', loadDataPelanggan);
</script>
@endpush