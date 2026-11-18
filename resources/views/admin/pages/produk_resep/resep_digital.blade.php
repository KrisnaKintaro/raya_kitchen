@extends('admin.admin_master')

@section('title', 'Resep Digital - Admin Raya Kitchen')
@section('header_title', 'Resep Digital')

@section('content')
<!-- Header Aksi & Informasi -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 bg-white border border-admin-border rounded-3xl p-5 shadow-sm">
    <div>
        <p class="text-sm font-bold text-slate-800">Arsip Resep Internal Dapur</p>
        <p class="text-xs text-admin-textMuted mt-0.5">Berisi standar komposisi bahan dan instruksi pembuatan produk Raya Kitchen.</p>
        <span class="inline-flex items-center gap-1 text-[11px] text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg mt-2 font-semibold">
            <i class="ph ph-lock-key"></i> Informasi bersifat internal dan rahasia dapur.
        </span>
    </div>
    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
        <button type="button" id="btn-export-all-pdf" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-admin-border hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition-all">
            <i class="ph ph-file-pdf text-base text-rose-600"></i> Ekspor Semua PDF
        </button>
        <button type="button" id="btn-open-add-recipe" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-admin-sideBg hover:bg-admin-sideHover text-white font-bold text-xs shadow-md transition-all">
            <i class="ph ph-plus text-base"></i> Tambah Resep
        </button>
    </div>
</div>

<!-- Statistik Ringkas & Kolom Pencarian -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-admin-border shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-admin-textMuted">Total Arsip Resep</p>
            <p class="text-2xl font-black mt-1 text-slate-800" id="recipe-count">0</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-admin-bg flex items-center justify-center text-admin-sideBg">
            <i class="ph ph-book-open text-xl font-bold"></i>
        </div>
    </div>

    <div class="md:col-span-3 bg-white border border-admin-border rounded-2xl p-2 shadow-sm flex items-center">
        <div class="relative w-full">
            <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-admin-textMuted text-lg"></i>
            <input id="search-recipe" type="search" placeholder="Cari judul resep atau menu..." class="w-full pl-10 pr-4 py-2 rounded-xl border-0 bg-transparent text-sm focus:outline-none focus:ring-0">
        </div>
    </div>
</div>

<!-- Tabel Daftar Resep -->
<div class="bg-white border border-admin-border rounded-3xl p-6 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-admin-border text-admin-textMuted text-xs uppercase tracking-wider">
                    <th class="py-3.5 px-4">#</th>
                    <th class="py-3.5 px-4">Kode</th>
                    <th class="py-3.5 px-4">Judul Resep</th>
                    <th class="py-3.5 px-4 text-center">Komposisi Bahan</th>
                    <th class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody-recipes" class="text-slate-800 text-sm font-medium divide-y divide-admin-border/60">
                <tr>
                    <td colspan="5" class="py-8 text-center text-admin-textMuted text-xs">Memuat arsip resep dapur...</td>
                </tr>
            </tbody>
        </table>
    </div>
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
<div id="recipe-form-modal" class="hidden fixed inset-0 z-[110] bg-slate-900/50 backdrop-blur-sm p-4 items-center justify-center">
    <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-2xl">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 id="recipe-form-title" class="text-xl font-black text-slate-800">Tambah Resep</h2>
                <p class="text-sm text-admin-textMuted mt-1">Simpan dokumen resep standar dapur Raya Kitchen.</p>
            </div>
            <button type="button" onclick="closeRecipeModal('recipe-form-modal')" class="w-9 h-9 rounded-xl bg-admin-bg flex items-center justify-center hover:bg-slate-200">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="recipe-form" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="edit-recipe-id">
            <label class="block text-sm font-bold text-slate-700">Judul Resep
                <input id="recipe-title-input" required class="mt-1.5 w-full border border-admin-border rounded-xl p-3 font-normal text-sm focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="Contoh: Roti Manis Special">
            </label>
            <label class="block text-sm font-bold text-slate-700">Foto Dokumentasi Resep
                <input id="recipe-image-input" type="file" accept="image/*" class="mt-1.5 w-full border border-admin-border rounded-xl p-3 font-normal text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-admin-sideBg file:text-white hover:file:bg-admin-sideHover" onchange="previewRecipeImage(event)">
            </label>
            <div id="recipe-image-preview-container" class="hidden mt-2">
                <span class="text-xs font-bold text-admin-textMuted block mb-1">Pratinjau Gambar:</span>
                <img id="recipe-image-preview" src="" alt="Preview" class="w-28 h-28 object-cover rounded-xl border border-admin-border shadow-sm">
            </div>
            <label class="block text-sm font-bold text-slate-700">Komposisi Bahan <span class="font-normal text-admin-textMuted text-xs">(format: nama bahan - takaran, satu per baris)</span>
                <textarea id="recipe-ingredients-input" rows="5" required class="mt-1.5 w-full border border-admin-border rounded-xl p-3 font-normal text-sm focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="Tepung terigu - 250 gram
Gula pasir - 100 gram
Telur - 2 butir"></textarea>
            </label>
            <label class="block text-sm font-bold text-slate-700">Instruksi Pembuatan
                <textarea id="recipe-instructions-input" rows="5" required class="mt-1.5 w-full border border-admin-border rounded-xl p-3 font-normal text-sm focus:ring-2 focus:ring-admin-sideHover focus:outline-none" placeholder="1. Siapkan bahan...
2. Campurkan..."></textarea>
            </label>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeRecipeModal('recipe-form-modal')" class="px-5 py-2.5 rounded-xl border border-admin-border font-bold text-sm hover:bg-slate-50">Batal</button>
                <button type="submit" id="btn-save-recipe" class="px-5 py-2.5 rounded-xl bg-admin-sideBg text-white font-bold text-sm hover:bg-admin-sideHover">Simpan Resep</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let recipesData = [];
let activeModalRecipeId = null;

function safeText(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

function closeRecipeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

function showRecipeModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function showNotification(title, message, type = 'success') {
    let notifEl = document.getElementById('custom-toast-notification');
    if (!notifEl) {
        notifEl = document.createElement('div');
        notifEl.id = 'custom-toast-notification';
        notifEl.className = 'fixed bottom-5 right-5 z-[200] transition-all duration-300';
        document.body.appendChild(notifEl);
    }

    const iconClass = type === 'success'
        ? 'ph-check-circle text-emerald-600'
        : (type === 'error' ? 'ph-warning-circle text-rose-600' : 'ph-file-pdf text-rose-600');

    notifEl.innerHTML = '';
    const box = document.createElement('div');
    box.className = 'flex items-start gap-3 p-4 rounded-2xl border border-admin-border shadow-2xl bg-white max-w-sm';

    const icon = document.createElement('i');
    icon.className = 'ph ' + iconClass + ' text-2xl shrink-0 mt-0.5';

    const textWrap = document.createElement('div');
    const h4 = document.createElement('h4');
    h4.className = 'font-black text-sm text-slate-800';
    h4.textContent = title;

    const p = document.createElement('p');
    p.className = 'text-xs text-slate-600 mt-0.5';
    p.textContent = message;

    textWrap.append(h4, p);
    box.append(icon, textWrap);
    notifEl.appendChild(box);

    notifEl.classList.remove('hidden');
    clearTimeout(window._resepToastTimer);
    window._resepToastTimer = setTimeout(() => notifEl.classList.add('hidden'), 3500);
}

async function loadRecipes() {
    const tbody = document.getElementById('tbody-recipes');
    try {
        const res = await fetch('/api/admin/resep', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (json.status === 'success') {
            recipesData = json.data || [];
            document.getElementById('recipe-count').textContent = recipesData.length;
            filterRecipes();
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-rose-500 text-xs">Gagal memuat data resep.</td></tr>';
        }
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-rose-500 text-xs">Gagal terhubung ke API /api/admin/resep.</td></tr>';
    }
}

function filterRecipes() {
    const q = document.getElementById('search-recipe').value.toLowerCase();
    const filtered = recipesData.filter(r => (r.title || '').toLowerCase().includes(q));
    renderRecipesTable(filtered);
}

function renderRecipesTable(list) {
    const tbody = document.getElementById('tbody-recipes');
    if (list.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="py-10 text-center text-admin-textMuted text-xs">Belum ada arsip resep di database. Klik "+ Tambah Resep" untuk mengisi.</td></tr>';
        return;
    }

    tbody.innerHTML = list.map((recipe, idx) => {
        const imgSrc = recipe.image_url || 'https://placehold.co/100x100?text=Resep';
        const code = String(recipe.id).padStart(3, '0');
        const totalBahan = Array.isArray(recipe.ingredients) ? recipe.ingredients.length : 0;
        return `
            <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-4 px-4">${idx + 1}</td>
                <td class="py-4 px-4">
                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-black">RESEP #${code}</span>
                </td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-3">
                        <img src="${imgSrc}" alt="${safeText(recipe.title)}" class="w-11 h-11 rounded-xl object-cover border border-admin-border shrink-0" onerror="this.src='https://placehold.co/100x100?text=Resep'">
                        <span class="font-bold text-slate-800">${safeText(recipe.title)}</span>
                    </div>
                </td>
                <td class="py-4 px-4 text-center">
                    <span class="px-3 py-1 bg-purple-50 text-admin-sideBg rounded-full text-xs font-bold">${totalBahan} Bahan</span>
                </td>
                <td class="py-4 px-4 text-center">
                    <div class="inline-flex items-center gap-1.5">
                        <button type="button" onclick="viewRecipe(${recipe.id})" class="px-3 py-1.5 rounded-xl bg-admin-sideBg/10 text-admin-sideBg hover:bg-admin-sideBg hover:text-white text-xs font-bold transition-colors" title="Detail Resep">
                            <i class="ph ph-eye mr-1"></i> Detail
                        </button>
                        <button type="button" onclick="editRecipe(${recipe.id})" class="p-2 rounded-xl border border-admin-border hover:bg-slate-100 text-slate-600" title="Edit Resep">
                            <i class="ph ph-pencil-simple text-sm"></i>
                        </button>
                        <button type="button" onclick="deleteRecipe(${recipe.id})" class="p-2 rounded-xl border border-admin-border hover:bg-red-50 text-red-600" title="Hapus Resep">
                            <i class="ph ph-trash text-sm"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function viewRecipe(id) {
    activeModalRecipeId = id;
    const r = recipesData.find(item => item.id === id);
    if (!r) return;

    document.getElementById('detail-title').textContent = r.title;
    document.getElementById('detail-image').src = r.image_url || 'https://placehold.co/600x400?text=Resep+Raya+Kitchen';

    const list = document.getElementById('detail-ingredients');
    list.innerHTML = '';
    (r.ingredients || []).forEach(i => {
        const li = document.createElement('li');
        li.className = 'flex justify-between gap-3 border-b border-admin-border pb-2 text-xs text-slate-700';
        const n = document.createElement('span');
        n.textContent = i.name;
        const a = document.createElement('strong');
        a.textContent = i.amount;
        li.append(n, a);
        list.appendChild(li);
    });

    document.getElementById('detail-instructions').textContent = r.instructions || '';
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
    const r = recipesData.find(item => item.id === id);
    if (!r) return;

    document.getElementById('recipe-form').reset();
    document.getElementById('edit-recipe-id').value = r.id;
    document.getElementById('recipe-form-title').textContent = 'Edit Resep';
    document.getElementById('recipe-title-input').value = r.title;
    document.getElementById('recipe-ingredients-input').value = (r.ingredients || []).map(i => `${i.name} - ${i.amount}`).join('\n');
    document.getElementById('recipe-instructions-input').value = r.instructions || '';

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

function parseIngredientsText(rawText) {
    return rawText
        .split('\n')
        .map(line => line.trim())
        .filter(line => line.length > 0)
        .map(line => {
            const parts = line.split('-');
            const name = parts[0]?.trim() || line;
            const amount = parts.slice(1).join('-').trim() || 'Secukupnya';
            return { name, amount };
        });
}

async function deleteRecipe(id) {
    if (!confirm('Yakin ingin menghapus resep digital ini dari arsip dapur?')) return;
    try {
        const res = await fetch(`/api/admin/resep/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
            showNotification('Terhapus', json.message, 'success');
            await loadRecipes();
        }
    } catch (err) {
        showNotification('Error', 'Gagal menghapus resep.', 'error');
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

function openPrintPdfWindow(recipesToPrint, docTitle) {
    const win = window.open('', '_blank', 'width=900,height=700');
    if (!win) return showNotification('Pop-up Diblokir', 'Izinkan pop-up browser untuk mengekspor PDF.', 'error');

    const doc = win.document;
    doc.title = docTitle;
    doc.body.style.fontFamily = 'Arial, sans-serif';
    doc.body.style.padding = '24px';
    doc.body.style.color = '#1e293b';

    const mainTitle = doc.createElement('h1');
    mainTitle.style.fontSize = '22px';
    mainTitle.style.marginBottom = '4px';
    mainTitle.textContent = 'Raya Kitchen - Dokumen Resep Dapur';

    const subTitle = doc.createElement('p');
    subTitle.style.fontSize = '12px';
    subTitle.style.color = '#64748b';
    subTitle.style.marginTop = '0';
    subTitle.style.marginBottom = '20px';
    subTitle.textContent = 'Dokumen Internal Rahasia · Dicetak pada ' + new Date().toLocaleDateString('id-ID');

    doc.body.append(mainTitle, subTitle);

    recipesToPrint.forEach(r => {
        const card = doc.createElement('div');
        card.style.pageBreakInside = 'avoid';
        card.style.border = '1px solid #e2e8f0';
        card.style.borderRadius = '14px';
        card.style.padding = '20px';
        card.style.marginBottom = '24px';

        const header = doc.createElement('div');
        header.style.display = 'flex';
        header.style.justifyContent = 'space-between';
        header.style.alignItems = 'center';
        header.style.borderBottom = '1px solid #f1f5f9';
        header.style.paddingBottom = '10px';
        header.style.marginBottom = '14px';

        const titleBox = doc.createElement('div');
        const codeTag = doc.createElement('div');
        codeTag.style.fontSize = '11px';
        codeTag.style.color = '#7e22ce';
        codeTag.style.fontWeight = 'bold';
        codeTag.textContent = 'ARSIP RESEP INTERNAL RAYA KITCHEN #' + String(r.id).padStart(3, '0');

        const titleEl = doc.createElement('h2');
        titleEl.style.margin = '4px 0 0';
        titleEl.style.fontSize = '20px';
        titleEl.textContent = r.title;
        titleBox.append(codeTag, titleEl);
        header.appendChild(titleBox);

        if (r.image_url) {
            const img = doc.createElement('img');
            img.src = r.image_url;
            img.style.width = '85px';
            img.style.height = '85px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '10px';
            img.style.border = '1px solid #e2e8f0';
            header.appendChild(img);
        }

        const grid = doc.createElement('div');
        grid.style.display = 'flex';
        grid.style.gap = '24px';

        const leftCol = doc.createElement('div');
        leftCol.style.width = '40%';
        const hBahan = doc.createElement('h4');
        hBahan.style.margin = '0 0 8px';
        hBahan.style.fontSize = '13px';
        hBahan.textContent = 'Komposisi Bahan';

        const tbl = doc.createElement('table');
        tbl.style.width = '100%';
        tbl.style.borderCollapse = 'collapse';
        tbl.style.fontSize = '12px';

        (r.ingredients || []).forEach(i => {
            const tr = doc.createElement('tr');
            const tdName = doc.createElement('td');
            tdName.style.padding = '5px 0';
            tdName.style.borderBottom = '1px dashed #e2e8f0';
            tdName.textContent = i.name;

            const tdAmt = doc.createElement('td');
            tdAmt.style.padding = '5px 0';
            tdAmt.style.borderBottom = '1px dashed #e2e8f0';
            tdAmt.style.textAlign = 'right';
            tdAmt.style.fontWeight = 'bold';
            tdAmt.textContent = i.amount;

            tr.append(tdName, tdAmt);
            tbl.appendChild(tr);
        });
        leftCol.append(hBahan, tbl);

        const rightCol = doc.createElement('div');
        rightCol.style.width = '60%';
        const hStep = doc.createElement('h4');
        hStep.style.margin = '0 0 8px';
        hStep.style.fontSize = '13px';
        hStep.textContent = 'Instruksi Pembuatan';

        const stepBox = doc.createElement('div');
        stepBox.style.fontSize = '12px';
        stepBox.style.lineHeight = '1.6';
        stepBox.style.whiteSpace = 'pre-line';
        stepBox.style.background = '#f8fafc';
        stepBox.style.padding = '12px';
        stepBox.style.borderRadius = '10px';
        stepBox.textContent = r.instructions || '';
        rightCol.append(hStep, stepBox);

        grid.append(leftCol, rightCol);
        card.append(header, grid);
        doc.body.appendChild(card);
    });

    win.focus();
    setTimeout(() => win.print(), 400);
}

function exportAllRecipesPDF() {
    if (recipesData.length === 0) {
        return showNotification('Kosong', 'Belum ada data resep untuk diekspor.', 'error');
    }
    openPrintPdfWindow(recipesData, 'Semua_Resep_Raya_Kitchen');
}

function exportSingleRecipePDF() {
    if (!activeModalRecipeId) return;
    const r = recipesData.find(item => item.id === activeModalRecipeId);
    if (!r) return;
    openPrintPdfWindow([r], 'Resep_' + r.title.replace(/\s+/g, '_'));
}

$(document).ready(function() {
    $('#btn-open-add-recipe').on('click', openRecipeModal);
    $('#btn-export-all-pdf').on('click', exportAllRecipesPDF);
    $('#search-recipe').on('input', filterRecipes);

    $('#recipe-form').on('submit', async function(e) {
        e.preventDefault();
        const id = $('#edit-recipe-id').val();
        const title = $('#recipe-title-input').val();
        const rawIngredients = $('#recipe-ingredients-input').val();
        const instructions = $('#recipe-instructions-input').val();
        const fileInput = document.getElementById('recipe-image-input');
        const btn = $('#btn-save-recipe');

        const parsedIngredients = parseIngredientsText(rawIngredients);
        if (parsedIngredients.length === 0) {
            return showNotification('Validasi', 'Masukkan minimal satu komposisi bahan.', 'error');
        }

        const formData = new FormData();
        formData.append('title', title);
        formData.append('ingredients', JSON.stringify(parsedIngredients));
        formData.append('instructions', instructions);
        if (fileInput.files.length > 0) {
            formData.append('image', fileInput.files[0]);
        }

        btn.prop('disabled', true).text('Menyimpan...');

        const url = id ? `/api/admin/resep/${id}` : '/api/admin/resep';
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
                closeRecipeModal('recipe-form-modal');
                showNotification('Berhasil', json.message, 'success');
                await loadRecipes();
            } else {
                const msg = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal menyimpan resep.');
                showNotification('Gagal', msg, 'error');
            }
        } catch (err) {
            showNotification('Error', 'Terjadi kesalahan koneksi server.', 'error');
        } finally {
            btn.prop('disabled', false).text('Simpan Resep');
        }
    });

    loadRecipes();
});
</script>
@endpush