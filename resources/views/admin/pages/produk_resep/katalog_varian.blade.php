@extends('admin.admin_master')

@section('title', 'Produk & Varian - Admin Raya Kitchen')
@section('header_title', 'Produk & Varian')

@section('content')
<!-- Header Aksi & Informasi -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 bg-white border border-admin-border rounded-3xl p-5 shadow-sm">
    <div>
        <p class="text-sm font-bold text-slate-800">Manajemen Katalog Produk & Varian</p>
        <p class="text-xs text-admin-textMuted mt-0.5">Kelola produk utama, daftar varian, harga, foto produk, serta status penjualan.</p>
    </div>
    <div class="shrink-0">
        <button type="button" id="btn-open-add-product" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-admin-sideBg hover:bg-admin-sideHover text-white font-bold text-xs shadow-md transition-all">
            <i class="ph ph-plus text-base"></i> Tambah Produk
        </button>
    </div>
</div>

<!-- Statistik Ringkas -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-admin-border shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-admin-textMuted">Total Produk</p>
        <p class="text-3xl font-black mt-2 text-slate-800" id="count-products">0</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-admin-border shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-admin-textMuted">Produk Aktif</p>
        <p class="text-3xl font-black text-emerald-600 mt-2" id="count-active">0</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-admin-border shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-admin-textMuted">Total Varian</p>
        <p class="text-3xl font-black mt-2 text-slate-800" id="count-variants">0</p>
    </div>
</div>

<!-- Filter Pencarian & Status -->
<div class="bg-white border border-admin-border rounded-3xl p-4 md:p-5 mb-6 flex flex-col md:flex-row gap-3 shadow-sm">
    <div class="relative flex-1">
        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-admin-textMuted text-lg"></i>
        <input id="search-product" type="search" placeholder="Cari nama produk atau varian..." class="w-full pl-11 pr-4 py-3 rounded-xl border border-admin-border bg-admin-bg/50 text-sm focus:outline-none focus:ring-2 focus:ring-admin-sideHover">
    </div>
    <select id="filter-product" class="px-4 py-3 rounded-xl border border-admin-border bg-white text-sm font-semibold focus:outline-none">
        <option value="all">Semua status</option>
        <option value="active">Aktif</option>
        <option value="inactive">Nonaktif</option>
    </select>
</div>

<!-- Tabel Daftar Produk Utama -->
<div class="bg-white border border-admin-border rounded-3xl p-6 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-admin-border text-admin-textMuted text-xs uppercase tracking-wider">
                    <th class="py-3.5 px-4">#</th>
                    <th class="py-3.5 px-4">Produk Utama</th>
                    <th class="py-3.5 px-4">Deskripsi Singkat</th>
                    <th class="py-3.5 px-4 text-center">Jumlah Varian</th>
                    <th class="py-3.5 px-4 text-center">Status</th>
                    <th class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody-products" class="text-slate-800 text-sm font-medium divide-y divide-admin-border/60">
                <tr>
                    <td colspan="6" class="py-8 text-center text-admin-textMuted text-xs">Memuat katalog produk...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
<!-- Modal Detail Produk & Kelola Varian -->
<div id="detail-product-modal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-2xl">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-5 border-b border-admin-border">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-admin-sideBg">Detail Produk & Daftar Varian</span>
                <h2 id="detail-prod-name" class="text-2xl font-black text-slate-800 mt-1"></h2>
                <p id="detail-prod-desc" class="text-xs text-admin-textMuted mt-1"></p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="openVariantForm()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-admin-sideBg hover:bg-admin-sideHover text-white font-bold text-xs shadow-sm transition-all">
                    <i class="ph ph-plus text-base"></i> Tambah Varian
                </button>
                <button type="button" onclick="closeModal('detail-product-modal')" class="w-9 h-9 rounded-xl bg-admin-bg flex items-center justify-center hover:bg-slate-200">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-admin-border text-admin-textMuted text-xs uppercase tracking-wider">
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-3">Foto</th>
                        <th class="py-3 px-3">Nama Varian</th>
                        <th class="py-3 px-3">Harga</th>
                        <th class="py-3 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-variants" class="text-slate-800 text-sm font-medium divide-y divide-admin-border/60"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Produk Utama -->
<div id="product-modal" class="hidden fixed inset-0 z-[110] bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="bg-white rounded-3xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 id="product-modal-title" class="text-xl font-black text-slate-800">Tambah Produk Utama</h2>
                <p class="text-sm text-admin-textMuted mt-1">Kelola produk utama Raya Kitchen.</p>
            </div>
            <button type="button" onclick="closeModal('product-modal')" class="w-9 h-9 rounded-xl bg-admin-bg flex items-center justify-center hover:bg-slate-200">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="product-form" class="space-y-4">
            <input type="hidden" id="edit-product-id">
            <label class="block text-sm font-bold text-slate-700">Nama Produk
                <input id="new-product-name" required class="mt-2 w-full border border-admin-border rounded-xl p-3 font-normal focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="Contoh: Roti Sobek Cokelat">
            </label>
            <label class="block text-sm font-bold text-slate-700">Deskripsi
                <textarea id="new-product-description" rows="3" class="mt-2 w-full border border-admin-border rounded-xl p-3 font-normal focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="Deskripsi singkat produk..."></textarea>
            </label>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('product-modal')" class="px-5 py-3 rounded-xl border border-admin-border font-bold text-sm hover:bg-slate-50">Batal</button>
                <button type="submit" id="btn-save-product" class="px-5 py-3 rounded-xl bg-admin-sideBg text-white font-bold text-sm hover:bg-admin-sideHover">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah/Edit Varian -->
<div id="variant-modal" class="hidden fixed inset-0 z-[120] bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="bg-white rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-2xl">
        <div class="flex justify-between items-start mb-5">
            <div>
                <h2 id="variant-modal-title" class="text-xl font-black text-slate-800">Tambah Varian Produk</h2>
                <p id="variant-parent-name" class="text-sm text-admin-textMuted mt-1"></p>
            </div>
            <button type="button" onclick="closeModal('variant-modal')" class="w-9 h-9 rounded-xl bg-admin-bg flex items-center justify-center hover:bg-slate-200">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="variant-form" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="edit-variant-id">
            <label class="block text-sm font-bold text-slate-700">Nama Varian (Rasa / Ukuran / Kemasan)
                <input required id="new-variant-name" class="mt-2 w-full border border-admin-border rounded-xl p-3 font-normal focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="Contoh: Toples 250gr">
            </label>
            <label class="block text-sm font-bold text-slate-700">Harga (Rp)
                <input required id="new-variant-price" type="number" min="0" class="mt-2 w-full border border-admin-border rounded-xl p-3 font-normal focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="35000">
            </label>
            <label class="block text-sm font-bold text-slate-700">Upload Foto Varian
                <input id="new-variant-image" type="file" accept="image/*" class="mt-2 w-full border border-admin-border rounded-xl p-3 font-normal text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-admin-sideBg file:text-white hover:file:bg-admin-sideHover" onchange="previewVariantImage(event)">
            </label>
            <div id="image-preview-container" class="hidden mt-2">
                <span class="text-xs font-bold text-admin-textMuted block mb-1">Pratinjau Gambar:</span>
                <img id="image-preview" src="" alt="Preview" class="w-28 h-28 object-cover rounded-xl border border-admin-border shadow-sm">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('variant-modal')" class="px-5 py-3 rounded-xl border border-admin-border font-bold text-sm hover:bg-slate-50">Batal</button>
                <button type="submit" id="btn-save-variant" class="px-5 py-3 rounded-xl bg-admin-sideBg text-white font-bold text-sm hover:bg-admin-sideHover">Simpan Varian</button>
            </div>
        </form>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let productsData = [];
let selectedProductId = null;

function safeText(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

function showToast(title, message, isError = false) {
    let el = document.getElementById('katalog-toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'katalog-toast';
        el.className = 'fixed bottom-5 right-5 z-[200] transition-all duration-300';
        document.body.appendChild(el);
    }
    el.innerHTML = `
        <div class="flex items-start gap-3 p-4 rounded-2xl border border-admin-border shadow-2xl bg-white max-w-sm">
            <i class="ph ${isError ? 'ph-warning-circle text-rose-600' : 'ph-check-circle text-emerald-600'} text-2xl shrink-0 mt-0.5"></i>
            <div>
                <h4 class="font-black text-sm text-slate-800">${safeText(title)}</h4>
                <p class="text-xs text-slate-600 mt-0.5">${safeText(message)}</p>
            </div>
        </div>
    `;
    el.classList.remove('hidden');
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => el.classList.add('hidden'), 3500);
}

async function loadProducts() {
    const tbody = document.getElementById('tbody-products');
    try {
        const res = await fetch('/api/admin/produk', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (json.status === 'success') {
            productsData = json.data;
            updateHeaderStats();
            applyProductFilter();

            if (selectedProductId) {
                const prod = productsData.find(p => p.id === selectedProductId);
                if (prod) {
                    renderDetailModalContent(prod);
                } else {
                    selectedProductId = null;
                    closeModal('detail-product-modal');
                }
            }
        } else {
            tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-rose-500 text-xs">Gagal memuat data: ${safeText(json.message || 'Error')}</td></tr>`;
        }
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-rose-500 text-xs">Gagal terhubung ke API /api/admin/produk.</td></tr>`;
    }
}

function applyProductFilter() {
    const q = document.getElementById('search-product').value.toLowerCase();
    const statusFilter = document.getElementById('filter-product').value;

    const filtered = productsData.filter(p => {
        const matchVariant = p.variants.some(v => v.variant_name.toLowerCase().includes(q));
        const matchName = p.name.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q) || matchVariant;
        const matchStatus = statusFilter === 'all'
            || (statusFilter === 'active' && p.is_active)
            || (statusFilter === 'inactive' && !p.is_active);
        return matchName && matchStatus;
    });

    renderProductsTable(filtered);
}

function renderProductsTable(list) {
    const tbody = document.getElementById('tbody-products');
    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="py-10 text-center text-admin-textMuted text-xs">Belum ada data produk atau tidak sesuai filter.</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map((product, idx) => {
        const thumb = product.variants[0]?.image_url || 'https://placehold.co/100x100?text=Produk';
        return `
            <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-4 px-4">${idx + 1}</td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-3">
                        <img src="${thumb}" alt="${safeText(product.name)}" class="w-11 h-11 rounded-xl object-cover border border-admin-border shrink-0" onerror="this.src='https://placehold.co/100x100?text=Produk'">
                        <span class="font-bold text-slate-800">${safeText(product.name)}</span>
                    </div>
                </td>
                <td class="py-4 px-4 text-xs text-admin-textMuted max-w-xs truncate">${safeText(product.description || '-')}</td>
                <td class="py-4 px-4 text-center">
                    <span class="px-3 py-1 bg-purple-50 text-admin-sideBg rounded-full text-xs font-bold">${product.variants.length} Varian</span>
                </td>
                <td class="py-4 px-4 text-center">
                    <button type="button" onclick="toggleProduct(${product.id})" class="px-3 py-1 rounded-full text-xs font-bold transition-all ${product.is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'}" title="Klik untuk ubah status">
                        ${product.is_active ? 'Aktif' : 'Nonaktif'}
                    </button>
                </td>
                <td class="py-4 px-4 text-center">
                    <div class="inline-flex items-center gap-1.5">
                        <button type="button" onclick="openDetailProduct(${product.id})" class="px-3 py-1.5 rounded-xl bg-admin-sideBg/10 text-admin-sideBg hover:bg-admin-sideBg hover:text-white text-xs font-bold transition-colors" title="Detail & Kelola Varian">
                            <i class="ph ph-eye mr-1"></i> Detail
                        </button>
                        <button type="button" onclick="editProduct(${product.id})" class="p-2 rounded-xl border border-admin-border hover:bg-slate-100 text-slate-600" title="Edit Produk">
                            <i class="ph ph-pencil-simple text-sm"></i>
                        </button>
                        <button type="button" onclick="deleteProduct(${product.id})" class="p-2 rounded-xl border border-admin-border hover:bg-red-50 text-red-600" title="Hapus Produk">
                            <i class="ph ph-trash text-sm"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function openDetailProduct(id) {
    selectedProductId = id;
    const prod = productsData.find(p => p.id === id);
    if (!prod) return;

    renderDetailModalContent(prod);
    document.getElementById('detail-product-modal').classList.remove('hidden');
    document.getElementById('detail-product-modal').classList.add('flex');
}

function renderDetailModalContent(prod) {
    document.getElementById('detail-prod-name').textContent = prod.name;
    document.getElementById('detail-prod-desc').textContent = prod.description || 'Tidak ada deskripsi.';

    const tbody = document.getElementById('tbody-variants');
    if (prod.variants.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-admin-textMuted text-xs">Belum ada varian untuk produk ini. Klik "+ Tambah Varian" di atas.</td></tr>`;
        return;
    }

    tbody.innerHTML = prod.variants.map((v, idx) => {
        const imgSrc = v.image_url || 'https://placehold.co/120x120?text=Varian';
        return `
            <tr class="hover:bg-slate-50/60">
                <td class="py-3 px-3">${idx + 1}</td>
                <td class="py-3 px-3">
                    <img src="${imgSrc}" alt="${safeText(v.variant_name)}" class="w-12 h-12 rounded-xl object-cover border border-admin-border" onerror="this.src='https://placehold.co/120x120?text=Varian'">
                </td>
                <td class="py-3 px-3 font-bold text-slate-800">${safeText(v.variant_name)}</td>
                <td class="py-3 px-3 font-black text-admin-sideBg">Rp ${Number(v.price).toLocaleString('id-ID')}</td>
                <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1">
                        <button type="button" onclick="editVariant(${v.id})" class="p-2 rounded-lg border border-admin-border hover:bg-slate-100 text-slate-600" title="Edit Varian">
                            <i class="ph ph-pencil-simple text-sm"></i>
                        </button>
                        <button type="button" onclick="deleteVariant(${v.id})" class="p-2 rounded-lg border border-admin-border hover:bg-red-50 text-red-600" title="Hapus Varian">
                            <i class="ph ph-trash text-sm"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function updateHeaderStats() {
    const totalProducts = productsData.length;
    const activeProducts = productsData.filter(p => p.is_active).length;
    let totalVariants = 0;
    productsData.forEach(p => { totalVariants += p.variants.length; });

    document.getElementById('count-products').textContent = totalProducts;
    document.getElementById('count-active').textContent = activeProducts;
    document.getElementById('count-variants').textContent = totalVariants;
}

function openProductModal() {
    document.getElementById('product-form').reset();
    document.getElementById('edit-product-id').value = '';
    document.getElementById('product-modal-title').textContent = 'Tambah Produk Utama';
    document.getElementById('product-modal').classList.remove('hidden');
    document.getElementById('product-modal').classList.add('flex');
}

function editProduct(id) {
    const prod = productsData.find(p => p.id === id);
    if (!prod) return;
    document.getElementById('edit-product-id').value = prod.id;
    document.getElementById('new-product-name').value = prod.name;
    document.getElementById('new-product-description').value = prod.description || '';
    document.getElementById('product-modal-title').textContent = 'Edit Produk Utama';
    document.getElementById('product-modal').classList.remove('hidden');
    document.getElementById('product-modal').classList.add('flex');
}

async function toggleProduct(id) {
    try {
        const res = await fetch(`/api/admin/produk/${id}/toggle`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
            showToast('Status Diperbarui', json.message);
            await loadProducts();
        }
    } catch (err) {
        showToast('Error', 'Gagal mengubah status produk.', true);
    }
}

async function deleteProduct(id) {
    if (!confirm('Yakin ingin menghapus produk utama ini beserta seluruh variannya?')) return;
    try {
        const res = await fetch(`/api/admin/produk/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
            showToast('Terhapus', json.message);
            await loadProducts();
        }
    } catch (err) {
        showToast('Error', 'Gagal menghapus produk.', true);
    }
}

function openVariantForm() {
    if (!selectedProductId) return showToast('Perhatian', 'Pilih produk utama terlebih dahulu!', true);
    document.getElementById('variant-form').reset();
    document.getElementById('edit-variant-id').value = '';
    document.getElementById('variant-modal-title').textContent = 'Tambah Varian Produk';
    document.getElementById('image-preview-container').classList.add('hidden');
    const prod = productsData.find(p => p.id === selectedProductId);
    document.getElementById('variant-parent-name').textContent = 'Produk: ' + prod.name;
    document.getElementById('variant-modal').classList.remove('hidden');
    document.getElementById('variant-modal').classList.add('flex');
}

function editVariant(variantId) {
    const prod = productsData.find(p => p.id === selectedProductId);
    if (!prod) return;
    const v = prod.variants.find(item => item.id === variantId);
    if (!v) return;

    document.getElementById('variant-form').reset();
    document.getElementById('edit-variant-id').value = v.id;
    document.getElementById('new-variant-name').value = v.variant_name;
    document.getElementById('new-variant-price').value = v.price;
    document.getElementById('variant-modal-title').textContent = 'Edit Varian Produk';
    document.getElementById('variant-parent-name').textContent = 'Produk: ' + prod.name;

    const previewContainer = document.getElementById('image-preview-container');
    const previewImg = document.getElementById('image-preview');
    if (v.image_url) {
        previewImg.src = v.image_url;
        previewContainer.classList.remove('hidden');
    } else {
        previewContainer.classList.add('hidden');
    }

    document.getElementById('variant-modal').classList.remove('hidden');
    document.getElementById('variant-modal').classList.add('flex');
}

async function deleteVariant(variantId) {
    if (!confirm('Hapus varian ini secara permanen?')) return;
    try {
        const res = await fetch(`/api/admin/varian/${variantId}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
            showToast('Terhapus', json.message);
            await loadProducts();
        }
    } catch (err) {
        showToast('Error', 'Gagal menghapus varian.', true);
    }
}

function previewVariantImage(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('image-preview-container');
    const previewImg = document.getElementById('image-preview');
    if (file) {
        previewImg.src = URL.createObjectURL(file);
        previewContainer.classList.remove('hidden');
    } else {
        previewContainer.classList.add('hidden');
    }
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btn-open-add-product').addEventListener('click', openProductModal);
    document.getElementById('search-product').addEventListener('input', applyProductFilter);
    document.getElementById('filter-product').addEventListener('change', applyProductFilter);

    document.getElementById('product-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-product-id').value;
        const name = document.getElementById('new-product-name').value;
        const description = document.getElementById('new-product-description').value;
        const btn = document.getElementById('btn-save-product');

        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        const url = id ? `/api/admin/produk/${id}` : '/api/admin/produk';
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ name, description })
            });
            const json = await res.json();

            if (res.ok && json.status === 'success') {
                closeModal('product-modal');
                showToast('Berhasil', json.message);
                await loadProducts();
            } else {
                showToast('Gagal', json.message || 'Gagal menyimpan produk.', true);
            }
        } catch (err) {
            showToast('Error', 'Terjadi kesalahan jaringan.', true);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Simpan Produk';
        }
    });

    document.getElementById('variant-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const variantId = document.getElementById('edit-variant-id').value;
        const btn = document.getElementById('btn-save-variant');

        const formData = new FormData();
        formData.append('variant_name', document.getElementById('new-variant-name').value);
        formData.append('price', document.getElementById('new-variant-price').value);
        const fileInput = document.getElementById('new-variant-image');
        if (fileInput.files.length > 0) {
            formData.append('image', fileInput.files[0]);
        }

        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        const url = variantId
            ? `/api/admin/varian/${variantId}`
            : `/api/admin/produk/${selectedProductId}/varian`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const json = await res.json();

            if (res.ok && json.status === 'success') {
                closeModal('variant-modal');
                showToast('Berhasil', json.message);
                await loadProducts();
            } else {
                const msg = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal menyimpan varian.');
                showToast('Gagal', msg, true);
            }
        } catch (err) {
            showToast('Error', 'Terjadi kesalahan jaringan.', true);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Simpan Varian';
        }
    });

    loadProducts();
});
</script>
@endpush