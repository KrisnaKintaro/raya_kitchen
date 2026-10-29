@extends('admin.admin_master')

@section('title', 'Resep Digital - Admin Raya Kitchen')
@section('header_title', 'Resep Digital')

@section('content')
@php
$recipes = [
    [
        'id' => 1,
        'title' => 'Brownies Cokelat Premium',
        'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800',
        'ingredients' => [
            ['name' => 'Dark chocolate', 'amount' => '200 gram'],
            ['name' => 'Mentega', 'amount' => '150 gram'],
            ['name' => 'Telur', 'amount' => '3 butir'],
            ['name' => 'Gula pasir', 'amount' => '150 gram'],
            ['name' => 'Tepung terigu', 'amount' => '100 gram'],
            ['name' => 'Bubuk kakao', 'amount' => '25 gram']
        ],
        'instructions' => "1. Lelehkan dark chocolate bersama mentega, lalu diamkan hingga hangat.\n2. Kocok telur dan gula hingga tercampur rata.\n3. Masukkan campuran cokelat, kemudian aduk perlahan.\n4. Tambahkan tepung dan bubuk kakao yang telah diayak.\n5. Tuang ke loyang dan panggang pada suhu 170°C selama 25–30 menit."
    ],
    [
        'id' => 2,
        'title' => 'Cookies Choco Chip',
        'image_url' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800',
        'ingredients' => [
            ['name' => 'Tepung terigu', 'amount' => '250 gram'],
            ['name' => 'Mentega', 'amount' => '125 gram'],
            ['name' => 'Gula palem', 'amount' => '100 gram'],
            ['name' => 'Telur', 'amount' => '1 butir'],
            ['name' => 'Choco chip', 'amount' => '150 gram']
        ],
        'instructions' => "1. Aduk mentega dan gula hingga lembut.\n2. Masukkan telur dan aduk rata.\n3. Tambahkan tepung secara bertahap.\n4. Masukkan choco chip, lalu bentuk adonan.\n5. Panggang pada suhu 160°C selama 12–15 menit."
    ],
    [
        'id' => 3,
        'title' => 'Banana Cake Moist',
        'image_url' => 'https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=800',
        'ingredients' => [
            ['name' => 'Pisang matang', 'amount' => '300 gram'],
            ['name' => 'Tepung terigu', 'amount' => '200 gram'],
            ['name' => 'Telur', 'amount' => '2 butir'],
            ['name' => 'Gula pasir', 'amount' => '120 gram'],
            ['name' => 'Minyak sayur', 'amount' => '100 ml'],
            ['name' => 'Baking powder', 'amount' => '1 sdt']
        ],
        'instructions' => "1. Haluskan pisang matang menggunakan garpu.\n2. Aduk telur dan gula hingga tercampur.\n3. Masukkan pisang dan minyak sayur.\n4. Tambahkan tepung serta baking powder, aduk rata.\n5. Panggang pada suhu 170°C selama 35–40 menit."
    ],
];
@endphp

<!-- Header Aksi & Informasi yang Dirapikan Sejajar -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 bg-white border border-admin-border rounded-3xl p-5 shadow-sm">
    <div>
        <p class="text-sm font-bold text-slate-800">Arsip Resep Internal Dapur</p>
        <p class="text-xs text-admin-textMuted mt-0.5">Berisi standar komposisi bahan dan instruksi pembuatan produk Raya Kitchen.</p>
        <span class="inline-flex items-center gap-1 text-[11px] text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg mt-2 font-semibold">
            <i class="ph ph-lock-key"></i> Informasi bersifat internal dan rahasia dapur.
        </span>
    </div>
    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
        <button type="button" onclick="exportAllRecipesPDF()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-admin-border hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition-all">
            <i class="ph ph-file-pdf text-base text-rose-600"></i> Ekspor Semua PDF
        </button>
        <button type="button" onclick="openRecipeModal()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-admin-sideBg hover:bg-admin-sideHover text-white font-bold text-xs shadow-md transition-all">
            <i class="ph ph-plus text-base"></i> Tambah Resep
        </button>
    </div>
</div>

<!-- Statistik Ringkas & Kolom Pencarian dalam Satu Baris / Grid Rapi -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <!-- Kotak Statistik Kecil di Kiri -->
    <div class="bg-white rounded-2xl p-4 border border-admin-border shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-admin-textMuted">Total Arsip Resep</p>
            <p class="text-2xl font-black mt-1 text-slate-800" id="recipe-count">{{ count($recipes) }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-admin-bg flex items-center justify-center text-admin-sideBg">
            <i class="ph ph-book-open text-xl font-bold"></i>
        </div>
    </div>

    <!-- Kolom Pencarian di Kanan (Lebih Panjang) -->
    <div class="md:col-span-3 bg-white border border-admin-border rounded-2xl p-2 shadow-sm flex items-center">
        <div class="relative w-full">
            <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-admin-textMuted text-lg"></i>
            <input id="search-recipe" type="search" placeholder="Cari judul resep atau menu..." class="w-full pl-10 pr-4 py-2 rounded-xl border-0 bg-transparent text-sm focus:outline-none focus:ring-0">
        </div>
    </div>
</div>

<!-- Grid Daftar Resep -->
<div id="recipe-list" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
@foreach($recipes as $recipe)
    <article class="recipe-card bg-white rounded-3xl overflow-hidden border border-admin-border shadow-sm hover:shadow-md transition-all flex flex-col justify-between" data-id="{{ $recipe['id'] }}" data-name="{{ strtolower($recipe['title']) }}">
        <div>
            <div class="h-48 bg-admin-bg relative">
                <img src="{{ $recipe['image_url'] }}" alt="{{ $recipe['title'] }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400?text=Resep+Raya+Kitchen'">
                <span class="absolute top-3 left-3 bg-white/95 text-admin-sideBg px-3 py-1.5 rounded-xl text-xs font-black shadow-xs">
                    <i class="ph ph-lock-key mr-1"></i> Internal
                </span>
            </div>
            <div class="p-5">
                <div class="text-[11px] font-black text-admin-textMuted uppercase tracking-wider mb-1">RESEP #{{ str_pad($recipe['id'], 3, '0', STR_PAD_LEFT) }}</div>
                <h2 class="text-lg font-black mb-2 text-slate-800">{{ $recipe['title'] }}</h2>
                <p class="text-xs text-admin-textMuted mb-4">{{ count($recipe['ingredients']) }} komponen bahan · Instruksi siap</p>
            </div>
        </div>
        <div class="p-5 pt-0">
            <div class="flex gap-2">
                <button type="button" onclick="viewRecipe({{ $recipe['id'] }})" class="flex-1 py-2.5 rounded-xl bg-admin-sideBg text-white text-xs font-bold hover:bg-admin-sideHover transition-colors">
                    <i class="ph ph-book-open mr-1"></i> Lihat Resep
                </button>
                <button type="button" onclick="editRecipe({{ $recipe['id'] }})" class="px-3 py-2.5 rounded-xl border border-admin-border hover:border-admin-sideBg text-slate-600 transition-colors" title="Edit Resep">
                    <i class="ph ph-pencil-simple text-base"></i>
                </button>
                <button type="button" onclick="deleteRecipe({{ $recipe['id'] }})" class="px-3 py-2.5 rounded-xl border border-admin-border hover:bg-red-50 text-red-600 transition-colors" title="Hapus Resep">
                    <i class="ph ph-trash text-base"></i>
                </button>
            </div>
        </div>
        <script type="application/json" id="recipe-data-{{ $recipe['id'] }}">{!! json_encode($recipe, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!}</script>
    </article>
@endforeach
</div>

<!-- Kondisi Kosong Jika Pencarian Tidak Ketemu -->
<div id="recipe-empty" class="hidden bg-white border border-admin-border rounded-2xl p-10 text-center text-admin-textMuted shadow-sm">
    <i class="ph ph-magnifying-glass text-3xl"></i>
    <p class="font-bold mt-2">Resep tidak ditemukan</p>
    <p class="text-sm mt-1">Coba gunakan kata kunci lain.</p>
</div>

<!-- Modal Detail Resep -->
<div id="recipe-detail-modal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="relative h-52 md:h-64 bg-admin-bg">
            <img id="detail-image" src="" class="w-full h-full object-cover">
            <button type="button" onclick="closeRecipeModal('recipe-detail-modal')" class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-white/90 flex items-center justify-center hover:bg-white shadow-md">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <div class="p-6 md:p-8">
            <span class="text-xs font-black text-admin-sideBg uppercase tracking-widest">RESEP INTERNAL DAPUR</span>
            <h2 id="detail-title" class="text-2xl font-black mt-2 mb-6 text-slate-800"></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
                <section>
                    <h3 class="font-black mb-3 text-slate-800 text-sm"><i class="ph ph-basket text-admin-sideBg mr-2"></i>Komposisi Bahan</h3>
                    <ul id="detail-ingredients" class="space-y-2 text-sm"></ul>
                </section>
                <section>
                    <h3 class="font-black mb-3 text-slate-800 text-sm"><i class="ph ph-notebook text-admin-sideBg mr-2"></i>Instruksi Pembuatan</h3>
                    <div id="detail-instructions" class="text-xs leading-6 text-admin-textMuted whitespace-pre-line bg-admin-bg/40 p-4 rounded-2xl border border-admin-border"></div>
                </section>
            </div>
            <div class="mt-7 flex flex-col sm:flex-row justify-between items-center gap-3 pt-4 border-t border-admin-border">
                <p class="text-xs text-slate-500">
                    <i class="ph ph-shield-check text-emerald-600 mr-1"></i> Standar operasional dapur Raya Kitchen.
                </p>
                <button type="button" onclick="exportSingleRecipePDF()" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md inline-flex items-center gap-1.5 shrink-0 transition-all">
                    <i class="ph ph-file-pdf text-base"></i> Ekspor PDF Resep Ini
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Tambah / Edit Resep -->
<div id="recipe-form-modal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-2xl">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 id="recipe-form-title" class="text-xl font-black text-slate-800">Tambah Resep</h2>
                <p class="text-sm text-admin-textMuted mt-1">Form simulasi dokumen resep dapur.</p>
            </div>
            <button type="button" onclick="closeRecipeModal('recipe-form-modal')" class="w-9 h-9 rounded-xl bg-admin-bg flex items-center justify-center hover:bg-slate-200">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="recipe-form" class="space-y-4">
            <input type="hidden" id="edit-recipe-id">
            <label class="block text-sm font-bold text-slate-700">Judul Resep
                <input id="recipe-title-input" required class="mt-1.5 w-full border border-admin-border rounded-xl p-3 font-normal text-sm focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="Contoh: Roti Manis Special">
            </label>
            <label class="block text-sm font-bold text-slate-700">Foto Dokumentasi Resep
                <input id="recipe-image-input" type="file" accept="image/*" class="mt-1.5 w-full border border-admin-border rounded-xl p-3 font-normal text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-admin-sideBg file:text-white hover:file:bg-admin-sideHover" onchange="previewRecipeImage(event)">
            </label>
            <!-- Preview Gambar di Bawah Input -->
            <div id="recipe-image-preview-container" class="hidden mt-2">
                <span class="text-xs font-bold text-admin-textMuted block mb-1">Pratinjau Gambar:</span>
                <img id="recipe-image-preview" src="" alt="Preview" class="w-28 h-28 object-cover rounded-xl border border-admin-border shadow-sm">
            </div>
            <label class="block text-sm font-bold text-slate-700">Komposisi Bahan <span class="font-normal text-admin-textMuted text-xs">(format: nama - jumlah, satu per baris)</span>
                <textarea id="recipe-ingredients-input" rows="4" required class="mt-1.5 w-full border border-admin-border rounded-xl p-3 font-normal text-sm focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="Tepung terigu - 250 gram&#10;Gula - 100 gram"></textarea>
            </label>
            <label class="block text-sm font-bold text-slate-700">Instruksi Pembuatan
                <textarea id="recipe-instructions-input" rows="4" required class="mt-1.5 w-full border border-admin-border rounded-xl p-3 font-normal text-sm focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="1. Siapkan bahan...&#10;2. Campurkan..."></textarea>
            </label>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeRecipeModal('recipe-form-modal')" class="px-5 py-2.5 rounded-xl border border-admin-border font-bold text-sm hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-admin-sideBg text-white font-bold text-sm hover:bg-admin-sideHover">Simpan Resep</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
let activeModalRecipeId = null;

function closeRecipeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

function showRecipeModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function getRecipe(id) {
    const scriptEl = document.getElementById('recipe-data-' + id);
    if (!scriptEl) return null;
    return JSON.parse(scriptEl.textContent);
}

function viewRecipe(id) {
    activeModalRecipeId = id;
    const r = getRecipe(id);
    if (!r) return;
    
    document.getElementById('detail-title').textContent = r.title;
    document.getElementById('detail-image').src = r.image_url || 'https://placehold.co/600x400?text=Resep';
    
    const list = document.getElementById('detail-ingredients');
    list.innerHTML = '';
    r.ingredients.forEach(i => {
        const li = document.createElement('li');
        li.className = 'flex justify-between gap-3 border-b border-admin-border pb-2 text-xs text-slate-700';
        const n = document.createElement('span');
        n.textContent = i.name;
        const a = document.createElement('strong');
        a.textContent = i.amount;
        li.append(n, a);
        list.appendChild(li);
    });
    
    document.getElementById('detail-instructions').textContent = r.instructions;
    showRecipeModal('recipe-detail-modal');
}

function openRecipeModal() {
    document.getElementById('recipe-form').reset();
    document.getElementById('edit-recipe-id').value = '';
    document.getElementById('recipe-form-title').textContent = 'Tambah Resep';
    document.getElementById('recipe-image-preview-container').classList.add('hidden');
    showRecipeModal('recipe-form-modal');
}

function editRecipe(id) {
    const r = getRecipe(id);
    if (!r) return;

    document.getElementById('recipe-form').reset();
    document.getElementById('edit-recipe-id').value = id;
    document.getElementById('recipe-form-title').textContent = 'Edit Resep';
    document.getElementById('recipe-title-input').value = r.title;
    document.getElementById('recipe-ingredients-input').value = r.ingredients.map(i => i.name + ' - ' + i.amount).join('\n');
    document.getElementById('recipe-instructions-input').value = r.instructions;
    
    const previewContainer = document.getElementById('recipe-image-preview-container');
    const previewImg = document.getElementById('recipe-image-preview');
    if (r.image_url) {
        previewImg.src = r.image_url;
        previewContainer.classList.remove('hidden');
    } else {
        previewContainer.classList.add('hidden');
    }

    showRecipeModal('recipe-form-modal');
}

function deleteRecipe(id) {
    if (confirm('Yakin ingin menghapus resep digital ini dari arsip dapur?')) {
        const card = document.querySelector(`.recipe-card[data-id="${id}"]`);
        if (card) card.remove();
        updateRecipeCount();
    }
}

function previewRecipeImage(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('recipe-image-preview-container');
    const previewImg = document.getElementById('recipe-image-preview');
    if (file) {
        previewImg.src = URL.createObjectURL(file);
        previewContainer.classList.remove('hidden');
    } else {
        previewContainer.classList.add('hidden');
    }
}

function updateRecipeCount() {
    const cards = document.querySelectorAll('.recipe-card');
    document.getElementById('recipe-count').textContent = cards.length;
}

function showNotification(title, message, type = 'success') {
    let notifEl = document.getElementById('custom-toast-notification');
    if (!notifEl) {
        notifEl = document.createElement('div');
        notifEl.id = 'custom-toast-notification';
        notifEl.className = 'fixed bottom-5 right-5 z-[200] transition-all transform translate-y-10 opacity-0 duration-300';
        document.body.appendChild(notifEl);
    }

    const iconClass = type === 'success' ? 'ph-check-circle text-emerald-600' : 'ph-file-pdf text-rose-600';

    notifEl.innerHTML = `
        <div class="flex items-start gap-3 p-4 rounded-2xl border border-admin-border shadow-2xl bg-white max-w-sm">
            <i class="ph ${iconClass} text-2xl shrink-0 mt-0.5"></i>
            <div>
                <h4 class="font-black text-sm text-slate-800">${title}</h4>
                <p class="text-xs text-slate-600 mt-0.5">${message}</p>
            </div>
        </div>
    `;

    setTimeout(() => notifEl.classList.remove('translate-y-10', 'opacity-0'), 50);
    setTimeout(() => notifEl.classList.add('translate-y-10', 'opacity-0'), 3500);
}

function exportAllRecipesPDF() {
    showNotification('Memproses Ekspor PDF', 'Menyusun dokumen lengkap seluruh judul, gambar, komposisi bahan, dan instruksi resep...', 'pdf');
    setTimeout(() => showNotification('Unduhan Berhasil', 'File PDF semua resep berhasil disiapkan.', 'success'), 2000);
}

function exportSingleRecipePDF() {
    if (!activeModalRecipeId) return;
    const r = getRecipe(activeModalRecipeId);
    if (!r) return;
    
    showNotification('Memproses Ekspor PDF', `Menyiapkan dokumen PDF untuk: "${r.title}"...`, 'pdf');
    setTimeout(() => showNotification('Unduhan Berhasil', `PDF resep "${r.title}" berhasil disiapkan.`, 'success'), 1800);
}

document.getElementById('search-recipe').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    let visible = 0;
    document.querySelectorAll('.recipe-card').forEach(c => {
        const show = c.dataset.name.includes(q);
        c.classList.toggle('hidden', !show);
        if (show) visible++;
    });
    document.getElementById('recipe-empty').classList.toggle('hidden', visible > 0);
});

document.getElementById('recipe-form').addEventListener('submit', function(e) {
    e.preventDefault();
    showNotification('Berhasil', 'Simpan resep berhasil disimulasikan.');
    closeRecipeModal('recipe-form-modal');
});
</script>
@endpush