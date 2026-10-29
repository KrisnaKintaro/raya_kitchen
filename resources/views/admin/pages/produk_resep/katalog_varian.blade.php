@extends('admin.admin_master')

@section('title', 'Produk & Varian - Admin Raya Kitchen')
@section('header_title', 'Produk & Varian')

@section('content')
@php
$products = [
    [
        'id' => 1,
        'name' => 'Brownies Cokelat',
        'description' => 'Brownies lembut dengan cokelat premium.',
        'is_active' => true,
        'variants' => [
            ['id' => 101, 'variant_name' => 'Small (10 x 10 cm)', 'price' => 35000, 'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600', 'is_active' => true],
            ['id' => 102, 'variant_name' => 'Large (20 x 20 cm)', 'price' => 65000, 'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600', 'is_active' => true],
        ]
    ],
    [
        'id' => 2,
        'name' => 'Cookies Choco Chip',
        'description' => 'Cookies renyah dengan taburan choco chip.',
        'is_active' => true,
        'variants' => [
            ['id' => 201, 'variant_name' => 'Toples 250 gram', 'price' => 42000, 'image_url' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600', 'is_active' => true],
            ['id' => 202, 'variant_name' => 'Toples 500 gram', 'price' => 78000, 'image_url' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600', 'is_active' => false],
        ]
    ],
    [
        'id' => 3,
        'name' => 'Banana Cake',
        'description' => 'Cake pisang moist, cocok untuk teman minum teh.',
        'is_active' => false,
        'variants' => [
            ['id' => 301, 'variant_name' => 'Loyang 18 cm', 'price' => 55000, 'image_url' => 'https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=600', 'is_active' => true],
        ]
    ],
];
@endphp

<!-- Header Aksi & Informasi yang Dirapikan Menjadi Satu Card Sejajar -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 bg-white border border-admin-border rounded-3xl p-5 shadow-sm">
    <div>
        <p class="text-sm font-bold text-slate-800">Manajemen Katalog Produk & Varian</p>
        <p class="text-xs text-admin-textMuted mt-0.5">Atur produk utama, kelola varian, harga, file foto, dan status penjualan produk.</p>
    </div>
    <div class="shrink-0">
        <button type="button" onclick="openProductModal()" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-admin-sideBg hover:bg-admin-sideHover text-white font-bold text-xs shadow-md transition-all">
            <i class="ph ph-plus text-base"></i> Tambah Produk
        </button>
    </div>
</div>

<!-- Statistik Ringkas -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-admin-border shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-admin-textMuted">Total Produk</p>
        <p class="text-3xl font-black mt-2 text-slate-800" id="count-products">{{ count($products) }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-admin-border shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-admin-textMuted">Produk Aktif</p>
        <p class="text-3xl font-black text-emerald-600 mt-2" id="count-active">{{ count(array_filter($products, fn($p) => $p['is_active'])) }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-admin-border shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-admin-textMuted">Total Varian</p>
        <p class="text-3xl font-black mt-2 text-slate-800" id="count-variants">5</p>
    </div>
</div>

<!-- Filter Pencarian & Status -->
<div class="bg-white border border-admin-border rounded-3xl p-4 md:p-5 mb-6 flex flex-col md:flex-row gap-3 shadow-sm">
    <div class="relative flex-1">
        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-admin-textMuted text-lg"></i>
        <input id="search-product" type="search" placeholder="Cari nama produk..." class="w-full pl-11 pr-4 py-3 rounded-xl border border-admin-border bg-admin-bg/50 text-sm focus:outline-none focus:ring-2 focus:ring-admin-sideHover">
    </div>
    <select id="filter-product" class="px-4 py-3 rounded-xl border border-admin-border bg-white text-sm font-semibold focus:outline-none">
        <option value="all">Semua status</option>
        <option value="active">Aktif</option>
        <option value="inactive">Nonaktif</option>
    </select>
</div>

<!-- Daftar Produk Utama (Klik produk untuk melihat varian di bawah) -->
<div class="mb-8">
    <h3 class="text-sm font-bold text-admin-textMuted uppercase tracking-wider mb-3"><i class="ph ph-list-bullets mr-1"></i> Pilih Produk Utama (Klik untuk lihat varian)</h3>
    <div id="product-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($products as $product)
        <div class="product-card bg-white border-2 border-admin-border rounded-2xl p-5 cursor-pointer hover:border-admin-sideBg transition-all relative shadow-sm" data-id="{{ $product['id'] }}" data-name="{{ strtolower($product['name']) }}" data-active="{{ $product['is_active'] ? 'active' : 'inactive' }}" onclick="selectProduct({{ $product['id'] }})">
            <div class="flex justify-between items-start mb-2">
                <h4 class="text-lg font-black text-slate-800">{{ $product['name'] }}</h4>
                <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                    <span class="product-status px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $product['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $product['is_active'] ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <button type="button" onclick="toggleProduct({{ $product['id'] }}, event)" class="text-[11px] font-bold px-2 py-1 rounded border border-admin-border hover:border-admin-sideBg text-slate-600">
                        {{ $product['is_active'] ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </div>
            </div>
            <p class="text-xs text-admin-textMuted line-clamp-2 mb-4">{{ $product['description'] }}</p>
            <div class="flex items-center justify-between pt-3 border-t border-admin-border text-xs font-bold text-admin-textMuted">
                <span>{{ count($product['variants']) }} Varian</span>
                <div class="flex gap-1" onclick="event.stopPropagation()">
                    <button type="button" onclick="editProduct({{ $product['id'] }}, '{{ addslashes($product['name']) }}', '{{ addslashes($product['description']) }}')" class="p-2 rounded-lg border border-admin-border hover:bg-slate-50 text-slate-600" title="Edit Produk"><i class="ph ph-pencil-simple text-sm"></i></button>
                    <button type="button" onclick="deleteProduct({{ $product['id'] }})" class="p-2 rounded-lg border border-admin-border hover:bg-red-50 text-red-600" title="Hapus Produk"><i class="ph ph-trash text-sm"></i></button>
                </div>
            </div>
        </div>
    @endforeach
    </div>
</div>

<!-- Kotak / Panel Khusus Varian di Bawah (Muncul saat produk diklik) -->
<div id="variant-section-container" class="bg-white border border-admin-border rounded-3xl p-6 shadow-sm hidden">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-4 border-b border-admin-border">
        <div>
            <span class="text-xs font-black uppercase tracking-wider text-admin-sideBg">Panel Varian Terpilih</span>
            <h2 id="selected-product-title" class="text-xl font-black text-slate-800 mt-0.5">Pilih Produk Terlebih Dahulu</h2>
            <p id="selected-product-desc" class="text-xs text-admin-textMuted mt-1"></p>
        </div>
        <button type="button" onclick="openVariantForm()" id="btn-add-variant" class="hidden inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-admin-sideBg hover:bg-admin-sideHover text-white font-bold text-sm shadow-md transition-all">
            <i class="ph ph-plus text-base"></i> Tambah Varian
        </button>
    </div>

    <!-- Container Daftar Varian -->
    <div id="variant-cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Rendered via JS -->
    </div>
</div>

<!-- Modal Tambah/Edit Produk Utama -->
<div id="product-modal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="bg-white rounded-3xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 id="product-modal-title" class="text-xl font-black text-slate-800">Tambah Produk Utama</h2>
                <p class="text-sm text-admin-textMuted mt-1">Kelola kategori produk Raya Kitchen.</p>
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
                <button type="submit" class="px-5 py-3 rounded-xl bg-admin-sideBg text-white font-bold text-sm hover:bg-admin-sideHover">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah/Edit Varian (Dengan Fitur Preview Gambar) -->
<div id="variant-modal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
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
        <form id="variant-form" class="space-y-4">
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
            <!-- Preview Gambar di Bawah Input -->
            <div id="image-preview-container" class="hidden mt-2">
                <span class="text-xs font-bold text-admin-textMuted block mb-1">Pratinjau Gambar:</span>
                <img id="image-preview" src="" alt="Preview" class="w-32 h-32 object-cover rounded-xl border border-admin-border shadow-sm">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('variant-modal')" class="px-5 py-3 rounded-xl border border-admin-border font-bold text-sm hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-3 rounded-xl bg-admin-sideBg text-white font-bold text-sm hover:bg-admin-sideHover">Simpan Varian</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
let productsData = [
    {
        id: 1, name: 'Brownies Cokelat', description: 'Brownies lembut dengan cokelat premium.', is_active: true,
        variants: [
            { id: 101, variant_name: 'Small (10 x 10 cm)', price: 35000, image_url: 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600', is_active: true },
            { id: 102, variant_name: 'Large (20 x 20 cm)', price: 65000, image_url: 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600', is_active: true }
        ]
    },
    {
        id: 2, name: 'Cookies Choco Chip', description: 'Cookies renyah dengan taburan choco chip.', is_active: true,
        variants: [
            { id: 201, variant_name: 'Toples 250 gram', price: 42000, image_url: 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600', is_active: true },
            { id: 202, variant_name: 'Toples 500 gram', price: 78000, image_url: 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600', is_active: false }
        ]
    },
    {
        id: 3, name: 'Banana Cake', description: 'Cake pisang moist, cocok untuk teman minum teh.', is_active: false,
        variants: [
            { id: 301, variant_name: 'Loyang 18 cm', price: 55000, image_url: 'https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=600', is_active: true }
        ]
    }
];

let selectedProductId = null;

function selectProduct(id) {
    selectedProductId = id;
    const prod = productsData.find(p => p.id === id);
    if (!prod) return;

    document.querySelectorAll('.product-card').forEach(c => c.classList.remove('border-admin-sideBg', 'ring-2', 'ring-admin-sideBg/20'));
    const card = document.querySelector(`.product-card[data-id="${id}"]`);
    if (card) card.classList.add('border-admin-sideBg', 'ring-2', 'ring-admin-sideBg/20');

    document.getElementById('variant-section-container').classList.remove('hidden');
    document.getElementById('selected-product-title').textContent = prod.name;
    document.getElementById('selected-product-desc').textContent = prod.description || 'Tidak ada deskripsi.';
    document.getElementById('btn-add-variant').classList.remove('hidden');

    renderVariants(prod.variants);
}

function renderVariants(variants) {
    const grid = document.getElementById('variant-cards-grid');
    grid.innerHTML = '';

    if (variants.length === 0) {
        grid.innerHTML = `<div class="col-span-full py-10 text-center text-admin-textMuted bg-admin-bg/40 rounded-2xl border border-admin-border"><i class="ph ph-package text-3xl mb-1"></i><p class="font-bold text-sm">Belum ada varian untuk produk ini.</p><p class="text-xs mt-1">Klik tombol "Tambah Varian" di atas untuk menambahkan.</p></div>`;
        return;
    }

    variants.forEach(v => {
        const item = document.createElement('div');
        item.className = 'bg-white border border-admin-border rounded-2xl p-4 flex gap-4 items-center shadow-xs relative';
        item.innerHTML = `
            <img src="${v.image_url}" alt="${v.variant_name}" class="w-20 h-20 object-cover rounded-xl border border-admin-border shrink-0" onerror="this.src='https://placehold.co/200x200?text=Varian'">
            <div class="flex-1 min-w-0">
                <div class="font-black text-sm text-slate-800 truncate">${escapeHtml(v.variant_name)}</div>
                <div class="font-black text-admin-sideBg mt-0.5">Rp ${Number(v.price).toLocaleString('id-ID')}</div>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-[11px] font-bold ${v.is_active ? 'text-emerald-600' : 'text-slate-400'}">
                        ${v.is_active ? '● Aktif' : '● Nonaktif'}
                    </span>
                    <button type="button" onclick="toggleVariant(${v.id}, event)" class="text-[10px] font-bold px-2 py-0.5 rounded border border-admin-border hover:border-admin-sideBg text-slate-600">
                        ${v.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                    </button>
                </div>
            </div>
            <div class="flex flex-col gap-1 shrink-0">
                <button type="button" onclick="editVariant(${v.id})" class="p-2 rounded-lg border border-admin-border hover:bg-slate-50 text-slate-600" title="Edit Varian"><i class="ph ph-pencil-simple text-sm"></i></button>
                <button type="button" onclick="deleteVariant(${v.id})" class="p-2 rounded-lg border border-admin-border hover:bg-red-50 text-red-600" title="Hapus Varian"><i class="ph ph-trash text-sm"></i></button>
            </div>
        `;
        grid.appendChild(item);
    });
}

function toggleProduct(id, event) {
    if (event) event.stopPropagation();
    const prod = productsData.find(p => p.id === id);
    if (!prod) return;
    prod.is_active = !prod.is_active;

    const card = document.querySelector(`.product-card[data-id="${id}"]`);
    if (card) {
        card.dataset.active = prod.is_active ? 'active' : 'inactive';
        const badge = card.querySelector('.product-status');
        const btnToggle = card.querySelector('button[onclick*="toggleProduct"]');
        if (badge) {
            badge.textContent = prod.is_active ? 'Aktif' : 'Nonaktif';
            badge.className = `product-status px-2.5 py-0.5 rounded-full text-[11px] font-bold ${prod.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`;
        }
        if (btnToggle) {
            btnToggle.textContent = prod.is_active ? 'Nonaktifkan' : 'Aktifkan';
        }
    }
    updateHeaderStats();
}

function toggleVariant(variantId, event) {
    if (event) event.stopPropagation();
    const prod = productsData.find(p => p.id === selectedProductId);
    if (!prod) return;
    const v = prod.variants.find(item => item.id === variantId);
    if (!v) return;
    v.is_active = !v.is_active;

    renderVariants(prod.variants);
    updateHeaderStats();
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

function editProduct(id, name, desc) {
    document.getElementById('edit-product-id').value = id;
    document.getElementById('new-product-name').value = name;
    document.getElementById('new-product-description').value = desc;
    document.getElementById('product-modal-title').textContent = 'Edit Produk Utama';
    document.getElementById('product-modal').classList.remove('hidden');
    document.getElementById('product-modal').classList.add('flex');
}

function deleteProduct(id) {
    if (confirm('Yakin ingin menghapus produk utama ini beserta seluruh variannya?')) {
        productsData = productsData.filter(p => p.id !== id);
        if (selectedProductId === id) {
            selectedProductId = null;
            document.getElementById('variant-section-container').classList.add('hidden');
        }
        location.reload();
    }
}

function openVariantForm() {
    if (!selectedProductId) return alert('Pilih produk utama terlebih dahulu!');
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
    const v = prod.variants.find(item => item.id === variantId);
    if (!v) return;

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

function deleteVariant(variantId) {
    if (confirm('Hapus varian ini?')) {
        const prod = productsData.find(p => p.id === selectedProductId);
        if (prod) {
            prod.variants = prod.variants.filter(v => v.id !== variantId);
            renderVariants(prod.variants);
            updateHeaderStats();
        }
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

document.getElementById('product-form').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Simpan produk utama berhasil (Simulasi Frontend).');
    closeModal('product-modal');
});

document.getElementById('variant-form').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Simpan varian berhasil (Simulasi Frontend).');
    closeModal('variant-modal');
});

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>
@endpush