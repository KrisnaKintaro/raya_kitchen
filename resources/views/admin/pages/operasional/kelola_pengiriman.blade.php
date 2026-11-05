@extends('admin.admin_master')
@section('title', 'Kelola Pengiriman - Admin Raya Kitchen')

@section('css')
<style>
    /* Fluid Typography */
    .text-fluid-h1 { font-size: clamp(1.25rem, 2vw + 0.5rem, 1.75rem); }
    .text-fluid-h2 { font-size: clamp(1.125rem, 1.5vw + 0.5rem, 1.5rem); }

    /* Hide Scrollbar but keep functionality */
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Claymorphism Base */
    .clay-card {
        background: var(--color-admin-bg, #F5F2F8);
        border: 1px solid #FFFFFF;
        box-shadow: inset 0 -4px 6px rgba(147, 104, 183, 0.15),
                    inset 0 4px 6px rgba(255, 255, 255, 1),
                    0 10px 20px rgba(147, 104, 183, 0.1);
        border-radius: 2rem;
    }
    .clay-btn {
        background: #9368B7;
        color: #fff;
        border: 1px solid rgba(255,255,255,0.3);
        box-shadow: inset 0 -4px 6px rgba(0, 0, 0, 0.15),
                    inset 0 4px 6px rgba(255, 255, 255, 0.3),
                    0 6px 12px rgba(147, 104, 183, 0.3);
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
    }
    .clay-btn:active {
        transform: scale(0.96) translateY(2px);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.2), 0 2px 4px rgba(147, 104, 183, 0.2);
    }

    /* Accordion Transition */
    .accordion-content {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .accordion-content.is-open {
        grid-template-rows: 1fr;
    }
    .accordion-inner {
        overflow: hidden;
    }

    /* Slider Component */
    .slide-track {
        position: relative;
        background: #E8D5F6;
        border-radius: 999px;
        box-shadow: inset 0 2px 5px rgba(147, 104, 183, 0.3), 0 1px 2px rgba(255,255,255,0.8);
        overflow: hidden;
        height: 3.5rem;
    }
    .slide-thumb {
        position: absolute;
        top: 0.25rem;
        left: 0.25rem;
        height: 3rem;
        width: 3rem;
        background: #9368B7;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: grab;
        box-shadow: inset 0 -3px 4px rgba(0,0,0,0.15), inset 0 3px 4px rgba(255,255,255,0.4), 0 4px 6px rgba(147, 104, 183, 0.4);
        touch-action: none; /* Prevent scrolling on mobile while sliding */
        z-index: 10;
        transition: transform 0.1s linear;
    }
    .slide-thumb:active { cursor: grabbing; }
    .slide-text {
        position: absolute;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9368B7;
        font-weight: 900;
        font-size: 0.875rem;
        z-index: 1;
        opacity: 0.8;
        pointer-events: none;
    }
    .slide-fill {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 0;
        background: #A57BCB;
        opacity: 0.2;
        z-index: 2;
    }
</style>
@endsection

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-fluid-h1 font-black text-admin-textMain drop-shadow-sm flex items-center gap-3">
                <i class="ph ph-moped text-admin-sideBg"></i> Kelola Pengiriman
            </h1>
            <p class="text-sm font-semibold text-admin-textMuted mt-1">Pantau, proses, dan validasi pesanan yang siap dikirim ke tangan pelanggan.</p>
        </div>
    </div>

    <!-- Segmented Control (Tabs) -->
    <div class="flex p-1.5 bg-[#E8D5F6] rounded-2xl w-full max-w-md shadow-[inset_0_2px_4px_rgba(147,104,183,0.2)] border border-white">
        <button data-target="tab-internal" class="tab-btn flex-1 py-2.5 rounded-xl text-sm font-black transition-all bg-white text-admin-sideBg shadow-sm pointer-events-none">
            Kurir Internal (Toko)
        </button>
        <button data-target="tab-ekspedisi" class="tab-btn flex-1 py-2.5 rounded-xl text-sm font-bold transition-all text-admin-sideBg/70 hover:text-admin-sideBg">
            Ekspedisi Eksternal
        </button>
    </div>

    <!-- Tab 1: Kurir Internal (Aktif) -->
    <div id="tab-internal" class="tab-pane flex flex-col gap-4 animate-[fadeInUp_0.4s_ease-out]">
        <!-- Area List Pesanan Internal (diisi via JS Skeleton -> Data) -->
        <div id="internal-list" class="flex flex-col gap-5"></div>
    </div>

    <!-- Tab 2: Ekspedisi Eksternal -->
    <div id="tab-ekspedisi" class="tab-pane flex flex-col gap-4 hidden">
        <div id="ekspedisi-list" class="flex flex-col gap-5"></div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL: VALIDASI PELUNASAN (COD / DP) -->
<!-- ========================================================= -->
<div id="modal-pelunasan" class="fixed inset-0 z-50 flex items-center justify-center bg-admin-textMain/40 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="clay-card w-full max-w-lg p-6 md:p-8 transform scale-95 transition-transform duration-300 m-4 relative max-h-[90vh] overflow-y-auto hide-scrollbar">
        <!-- Close Button -->
        <button type="button" class="btn-close-modal absolute top-6 right-6 p-2 rounded-full bg-white text-admin-textMuted shadow-sm hover:text-red-500 transition-colors">
            <i class="ph ph-x text-lg"></i>
        </button>

        <div class="flex items-center gap-4 mb-6 border-b border-admin-accent/50 pb-4">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center shadow-inner">
                <i class="ph ph-wallet text-2xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-admin-textMain">Validasi Pelunasan</h2>
                <p class="text-xs font-bold text-admin-textMuted">Pesanan <span id="modal-order-id" class="text-admin-sideBg">#---</span></p>
            </div>
        </div>

        <form id="form-pelunasan" class="flex flex-col gap-5">
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex justify-between items-center shadow-[inset_0_2px_4px_rgba(245,158,11,0.05)]">
                <span class="text-sm font-bold text-amber-800">Sisa Tagihan (Wajib Lunas)</span>
                <span id="modal-sisa-tagihan" class="text-xl font-black text-amber-600">Rp 0</span>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Nominal Diterima</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-admin-textMuted font-bold">Rp</span>
                    <input type="number" id="input-nominal" required class="w-full pl-10 pr-4 py-3 rounded-2xl bg-white border border-admin-accent shadow-[inset_0_2px_4px_rgba(147,104,183,0.05)] focus:outline-none focus:ring-2 focus:ring-admin-sideBg font-bold text-admin-textMain" placeholder="Masukkan angka persis...">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Bukti Pembayaran / Serah Terima</label>
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-admin-accent rounded-2xl bg-white hover:bg-admin-navBg/30 cursor-pointer transition-colors group">
                    <i class="ph ph-upload-simple text-2xl text-admin-textMuted group-hover:text-admin-sideBg mb-2"></i>
                    <span class="text-xs font-bold text-admin-textMuted group-hover:text-admin-sideBg">Pilih atau letakkan gambar</span>
                    <input type="file" id="input-bukti" class="hidden" accept="image/*" required>
                </label>
                <p id="file-name-preview" class="text-[10px] font-bold text-admin-sideBg mt-2 hidden text-center truncate"></p>
            </div>

            <button type="submit" class="clay-btn w-full py-4 mt-2 rounded-2xl font-black text-sm flex items-center justify-center gap-2">
                <i class="ph ph-check-circle text-lg"></i> Konfirmasi Selesai
            </button>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
/**
 * -------------------------------------------------------------------
 * ZERO MEMORY LEAK PROTOCOL & STATE MANAGEMENT
 * -------------------------------------------------------------------
 */
const DeliveryManager = (function() {
    // === DUMMY DATA ===
    const dummyOrders = [
        {
            id: 'RK-20261106-001',
            type: 'internal',
            recipient: 'Budi Santoso',
            address: 'Jl. Merdeka No 10, Bekasi (Patokan: Depan Masjid)',
            delivery_date: '2026-11-06',
            delivery_time: '12:00:00',
            payment_method: 'transfer',
            payment_status: 'paid_full',
            remaining_amount: 0,
            total_amount: 150000,
            items: [
                { name: 'Roti Coklat Lumer', qty: 5, price: 15000 },
                { name: 'Roti Keju Susu', qty: 5, price: 15000 }
            ]
        },
        {
            id: 'RK-20261106-002',
            type: 'internal',
            recipient: 'Siti Aminah',
            address: 'Perum. Indah Blok A/4, Bekasi',
            delivery_date: '2026-11-06',
            delivery_time: '14:30:00',
            payment_method: 'cod',
            payment_status: 'paid_dp', // COD but DP paid
            remaining_amount: 50000,
            total_amount: 80000,
            items: [
                { name: 'Abon Sapi Spesial', qty: 4, price: 20000 }
            ]
        },
        {
            id: 'RK-20261106-003',
            type: 'ekspedisi',
            courier: 'JNT - EZ',
            recipient: 'Ahmad Dahlan',
            address: 'Jl. Sudirman 15, Jakarta Pusat',
            delivery_date: '2026-11-06',
            delivery_time: '16:00:00',
            payment_method: 'transfer',
            payment_status: 'paid_full',
            remaining_amount: 0,
            total_amount: 120000,
            items: [
                { name: 'Kue Kering Nastar', qty: 2, price: 60000 }
            ]
        }
    ];

    // Format Rupiah
    const formatRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
    const formatTime = (time) => time.substring(0, 5);
    const formatDate = (dateStr) => {
        return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    // State Variables
    let activeSlider = null; // Menyimpan data slider yang sedang digeser
    let targetOrderForModal = null; // Menyimpan ID order saat modal terbuka

    // === DOM ELEMENTS ===
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const internalList = document.getElementById('internal-list');
    const ekspedisiList = document.getElementById('ekspedisi-list');
    const modalPelunasan = document.getElementById('modal-pelunasan');
    const modalCard = modalPelunasan.querySelector('.clay-card');

    // === RENDER ENGINE ===
    function renderSkeletons(container) {
        container.innerHTML = `
            <div class="clay-card p-6 animate-pulse flex flex-col gap-4">
                <div class="flex justify-between items-center"><div class="w-1/3 h-5 bg-admin-accent rounded-full"></div><div class="w-16 h-5 bg-admin-accent rounded-full"></div></div>
                <div class="w-1/2 h-8 bg-admin-navBg rounded-xl"></div>
                <div class="w-full h-12 bg-admin-accent/50 rounded-xl"></div>
            </div>
        `;
    }

    function renderEmptyState(container, message) {
        container.innerHTML = `
            <div class="clay-card p-12 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-admin-navBg text-admin-sideBg rounded-full flex items-center justify-center mb-4">
                    <i class="ph ph-package text-4xl"></i>
                </div>
                <h3 class="text-lg font-black text-admin-textMain mb-1">Data Kosong</h3>
                <p class="text-sm font-semibold text-admin-textMuted">${message}</p>
            </div>
        `;
    }

    function generateCardHTML(order, isInternal) {
        // Tag Status Pembayaran
        let badgeColor = order.remaining_amount > 0 ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200';
        let badgeText = order.remaining_amount > 0 ? (order.payment_method === 'cod' ? 'COD - Belum Lunas' : 'Sisa DP - Belum Lunas') : 'Lunas';

        let itemsHtml = order.items.map(item => `
            <div class="flex justify-between items-center py-2 border-b border-white last:border-0">
                <span class="text-sm font-bold text-admin-textMain">${item.name} <span class="text-admin-sideBg">x${item.qty}</span></span>
                <span class="text-sm font-black text-admin-textMuted">${formatRp(item.qty * item.price)}</span>
            </div>
        `).join('');

        // Action Area
        let actionHtml = '';
        if (isInternal) {
            actionHtml = `
                <div class="mt-4 pt-4 border-t border-admin-accent/50">
                    <div class="slide-track w-full group" data-order-id="${order.id}" data-req-pelunasan="${order.remaining_amount > 0 ? 'yes' : 'no'}" data-sisa="${order.remaining_amount}">
                        <div class="slide-fill"></div>
                        <div class="slide-text">Geser untuk Selesaikan <i class="ph ph-arrow-right ml-2 animate-bounce"></i></div>
                        <div class="slide-thumb">
                            <i class="ph ph-moped text-xl"></i>
                        </div>
                    </div>
                </div>
            `;
        } else {
            actionHtml = `
                <div class="mt-4 pt-4 border-t border-admin-accent/50">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center justify-center gap-2 text-blue-600 font-bold text-xs">
                        <i class="ph ph-spinner-gap animate-spin text-base"></i> Menunggu pembaruan sistem RajaOngkir (${order.courier})
                    </div>
                </div>
            `;
        }

        return `
            <div class="clay-card p-5 md:p-6 transition-transform duration-300" id="card-${order.id}">
                <!-- Header Card (Klik untuk Accordion) -->
                <div class="accordion-toggle cursor-pointer select-none flex flex-col md:flex-row justify-between items-start md:items-center gap-4 group">
                    <div class="flex gap-4 items-center w-full md:w-auto">
                        <div class="w-14 h-14 rounded-2xl bg-admin-navBg text-admin-sideBg flex flex-col items-center justify-center shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                            <i class="ph ph-clock text-xl"></i>
                            <span class="text-xs font-black">${formatTime(order.delivery_time)}</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-extrabold text-admin-sideBg uppercase tracking-wider mb-0.5">${formatDate(order.delivery_date)}</p>

                            <h3 class="font-black text-base text-admin-textMain leading-tight">${order.recipient}</h3>
                            <p class="text-xs font-bold text-admin-textMuted flex items-center gap-1 mt-0.5"><i class="ph ph-map-pin"></i> <span class="line-clamp-1">${order.address}</span></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between w-full md:w-auto gap-3">
                        <span class="px-3 py-1 rounded-full border text-[10px] font-black uppercase tracking-wider ${badgeColor}">${badgeText}</span>
                        <div class="w-8 h-8 rounded-full bg-white border border-admin-accent flex items-center justify-center text-admin-sideBg shadow-sm transition-transform duration-300 icon-chevron">
                            <i class="ph ph-caret-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Accordion Content -->
                <div class="accordion-content mt-0">
                    <div class="accordion-inner">
                        <div class="pt-4 mt-4 border-t border-admin-accent/50">
                            <h4 class="text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Detail Rincian (#${order.id})</h4>
                            <div class="bg-admin-bg p-3 rounded-xl border border-white shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]">
                                ${itemsHtml}
                                <div class="flex justify-between items-center pt-2 mt-2 border-t border-white">
                                    <span class="text-xs font-black uppercase text-admin-textMuted">Total</span>
                                    <span class="text-base font-black text-admin-sideBg">${formatRp(order.total_amount)}</span>
                                </div>
                            </div>
                            ${actionHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function initData() {
        renderSkeletons(internalList);
        renderSkeletons(ekspedisiList);

        setTimeout(() => {
            const internalOrders = dummyOrders.filter(o => o.type === 'internal').sort((a, b) => a.delivery_time.localeCompare(b.delivery_time));
            const ekspedisiOrders = dummyOrders.filter(o => o.type === 'ekspedisi').sort((a, b) => a.delivery_time.localeCompare(b.delivery_time));

            internalList.innerHTML = internalOrders.length ? internalOrders.map(o => generateCardHTML(o, true)).join('') : '';
            if(!internalOrders.length) renderEmptyState(internalList, 'Tidak ada pengiriman internal yang perlu diantar saat ini.');

            ekspedisiList.innerHTML = ekspedisiOrders.length ? ekspedisiOrders.map(o => generateCardHTML(o, false)).join('') : '';
            if(!ekspedisiOrders.length) renderEmptyState(ekspedisiList, 'Tidak ada paket ekspedisi yang siap dikirim.');

        }, 600); // Simulasi delay jaringan
    }

    // === INTERAKSI: Tabs ===
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => {
                b.classList.remove('bg-white', 'text-admin-sideBg', 'shadow-sm', 'pointer-events-none');
                b.classList.add('text-admin-sideBg/70', 'hover:text-admin-sideBg');
            });
            this.classList.remove('text-admin-sideBg/70', 'hover:text-admin-sideBg');
            this.classList.add('bg-white', 'text-admin-sideBg', 'shadow-sm', 'pointer-events-none');

            tabPanes.forEach(pane => pane.classList.add('hidden'));
            document.getElementById(this.dataset.target).classList.remove('hidden');
        });
    });

    // === INTERAKSI: Accordion (Delegated) ===
    document.addEventListener('click', function(e) {
        const toggle = e.target.closest('.accordion-toggle');
        if (!toggle) return;

        const content = toggle.nextElementSibling;
        const icon = toggle.querySelector('.icon-chevron');

        content.classList.toggle('is-open');
        content.classList.contains('is-open') ? content.classList.add('mt-4') : content.classList.remove('mt-4');
        icon.style.transform = content.classList.contains('is-open') ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    // === INTERAKSI: Modal Pelunasan ===
    const toggleModal = (show) => {
        if(show) {
            modalPelunasan.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => modalCard.classList.remove('scale-95'), 10);
        } else {
            modalCard.classList.add('scale-95');
            setTimeout(() => modalPelunasan.classList.add('opacity-0', 'pointer-events-none'), 300);
            targetOrderForModal = null;
            document.getElementById('form-pelunasan').reset();
            document.getElementById('file-name-preview').classList.add('hidden');
        }
    }

    document.querySelectorAll('.btn-close-modal').forEach(btn => btn.addEventListener('click', () => toggleModal(false)));

    // File Input Preview
    document.getElementById('input-bukti').addEventListener('change', function(e) {
        const preview = document.getElementById('file-name-preview');
        if (this.files && this.files[0]) {
            preview.textContent = 'File: ' + this.files[0].name;
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    });

    // Form Pelunasan Submit
    document.getElementById('form-pelunasan').addEventListener('submit', function(e) {
        e.preventDefault();
        const inputNominal = parseInt(document.getElementById('input-nominal').value);
        const sisaTagihan = parseInt(document.getElementById('modal-sisa-tagihan').dataset.raw);

        if(inputNominal !== sisaTagihan) {
            showToast('Peringatan', 'Nominal yang dimasukkan harus pas dengan sisa tagihan.');
            return;
        }

        const btn = this.querySelector('button[type="submit"]');
        const ogText = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-lg"></i> Memproses...';
        btn.disabled = true;

        // Simulasi API
        setTimeout(() => {
            btn.innerHTML = ogText;
            btn.disabled = false;
            toggleModal(false);
            showToast('Berhasil', 'Pelunasan divalidasi dan pesanan diselesaikan.');

            // Hapus card dari UI
            const card = document.getElementById('card-' + targetOrderForModal);
            if(card) {
                card.style.transform = 'scale(0.9) translateY(20px)';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
        }, 1000);
    });

    // === INTERAKSI: Slide to Confirm ===
    document.addEventListener('pointerdown', function(e) {
        const thumb = e.target.closest('.slide-thumb');
        if (!thumb) return;

        const track = thumb.parentElement;
        const fill = track.querySelector('.slide-fill');
        const text = track.querySelector('.slide-text');

        activeSlider = {
            thumb: thumb,
            track: track,
            fill: fill,
            text: text,
            startX: e.clientX,
            maxDist: track.offsetWidth - thumb.offsetWidth - 8, // padding
            currentDist: 0,
            completed: false
        };

        // UI Feedback
        thumb.style.transition = 'none';
        fill.style.transition = 'none';

        // Prevent accidental selections
        document.body.style.userSelect = 'none';
    });

    document.addEventListener('pointermove', function(e) {
        if (!activeSlider || activeSlider.completed) return;

        let deltaX = e.clientX - activeSlider.startX;
        if (deltaX < 0) deltaX = 0;
        if (deltaX > activeSlider.maxDist) deltaX = activeSlider.maxDist;

        activeSlider.currentDist = deltaX;
        activeSlider.thumb.style.transform = `translateX(${deltaX}px)`;
        activeSlider.fill.style.width = `${deltaX + 24}px`; // + radius

        // Memudarkan teks saat digeser
        activeSlider.text.style.opacity = 1 - (deltaX / activeSlider.maxDist);

        if (deltaX === activeSlider.maxDist) {
            activeSlider.completed = true;
            handleSliderComplete(activeSlider.track);
        }
    });

    document.addEventListener('pointerup', function(e) {
        if (!activeSlider) return;
        document.body.style.userSelect = '';

        if (!activeSlider.completed) {
            // Snap back
            activeSlider.thumb.style.transition = 'transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1)';
            activeSlider.fill.style.transition = 'width 0.2s cubic-bezier(0.34, 1.56, 0.64, 1)';
            activeSlider.thumb.style.transform = `translateX(0px)`;
            activeSlider.fill.style.width = `0px`;
            activeSlider.text.style.opacity = 1;
        }
        activeSlider = null;
    });

    function handleSliderComplete(track) {
        const orderId = track.dataset.orderId;
        const reqPelunasan = track.dataset.reqPelunasan;
        const sisa = parseInt(track.dataset.sisa);

        if (reqPelunasan === 'yes') {
            // Buka Modal
            targetOrderForModal = orderId;
            document.getElementById('modal-order-id').textContent = '#' + orderId;
            document.getElementById('modal-sisa-tagihan').textContent = formatRp(sisa);
            document.getElementById('modal-sisa-tagihan').dataset.raw = sisa;

            toggleModal(true);

            // Reset Slider diam-diam setelah modal kebuka
            setTimeout(() => {
                const thumb = track.querySelector('.slide-thumb');
                const fill = track.querySelector('.slide-fill');
                const text = track.querySelector('.slide-text');
                thumb.style.transform = `translateX(0px)`;
                fill.style.width = `0px`;
                text.style.opacity = 1;
            }, 500);

        } else {
            // Langsung Selesai (Lunas)
            showToast('Pesanan Dikirim', `Pesanan #${orderId} telah ditandai selesai.`);
            const card = document.getElementById('card-' + orderId);
            if(card) {
                card.style.transition = 'transform 0.3s, opacity 0.3s';
                card.style.transform = 'scale(0.95) translateX(20px)';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
        }
    }

    // === GLOBAL TOAST ===
    function showToast(title, message) {
        // Asumsi ada #toast-container di layout master, jika tidak buat on the fly
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-[200] flex flex-col gap-3 pointer-events-none w-full max-w-sm px-4';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border border-admin-accent shadow-2xl bg-white transition-all transform -translate-y-5 opacity-0 duration-300';
        toast.innerHTML = `
            <div class="w-10 h-10 rounded-xl bg-admin-navBg text-admin-sideBg flex items-center justify-center shrink-0">
                <i class="ph ph-check-circle text-xl"></i>
            </div>
            <div>
                <h4 class="font-black text-sm text-admin-textMain">${title}</h4>
                <p class="text-xs font-bold text-admin-textMuted mt-0.5">${message}</p>
            </div>
        `;
        container.appendChild(toast);
        setTimeout(() => toast.classList.remove('-translate-y-5', 'opacity-0'), 50);
        setTimeout(() => {
            toast.classList.add('-translate-y-5', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Eksekusi Awal
    initData();

    return {
        // Expose public method jika butuh dipanggil dari luar (opsional)
    };
})();
</script>
@endpush
