@extends('admin.admin_master')
@section('title', 'Kelola Pengiriman - Admin Raya Kitchen')

@section('css')
<style>
    :root {
        --kp-bg-60: #F5F2F8;
        --kp-surface-30: #FFFFFF;
        --kp-surface-tint: #E8D5F6;
        --kp-border-tint: #E4CCFF;
        --kp-accent-10: #9368B7;
        --kp-accent-hover: #7E54A3;
        --kp-text-main: #2D2A32;
        --kp-text-muted: #716E77;
        --kp-radius-card: clamp(1.25rem, 2vw, 2rem);
        --kp-radius-pill: 9999px;
    }

    .text-fluid-h1 { font-size: clamp(1.35rem, 2vw + 0.5rem, 1.85rem); line-height: 1.2; }
    .text-fluid-h2 { font-size: clamp(1.05rem, 1.2vw + 0.5rem, 1.35rem); line-height: 1.3; }
    .text-fluid-body { font-size: clamp(0.8125rem, 0.4vw + 0.7rem, 0.9375rem); }

    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    .clay-card {
        background: var(--kp-bg-60);
        border: 1px solid var(--kp-surface-30);
        box-shadow: inset 0 -4px 6px rgba(147, 104, 183, 0.14),
                    inset 0 4px 6px rgba(255, 255, 255, 1),
                    0 8px 18px rgba(147, 104, 183, 0.09);
        border-radius: var(--kp-radius-card);
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .clay-modal-surface {
        background: var(--kp-bg-60);
        border: 2px solid var(--kp-surface-30);
        box-shadow: 0 20px 40px rgba(28, 22, 38, 0.35);
        border-radius: var(--kp-radius-card);
        will-change: transform, opacity;
        transform: translate3d(0, 12px, 0) scale(0.97);
        opacity: 0;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.18s ease-out;
    }
    .modal-backdrop-open .clay-modal-surface {
        transform: translate3d(0, 0, 0) scale(1);
        opacity: 1;
    }

    .clay-btn {
        background: var(--kp-accent-10);
        color: var(--kp-surface-30);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: inset 0 -3px 5px rgba(0, 0, 0, 0.16),
                    inset 0 3px 5px rgba(255, 255, 255, 0.35),
                    0 4px 10px rgba(147, 104, 183, 0.25);
        min-height: 44px;
        min-width: 44px;
        transition: transform 0.18s ease, opacity 0.18s ease;
    }
    .clay-btn:hover:not(:disabled) { transform: translateY(-2px); }
    .clay-btn:focus-visible { outline: 3px solid var(--kp-accent-10); outline-offset: 2px; }
    .clay-btn:active:not(:disabled) { transform: scale(0.96); }
    .clay-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .clay-btn-emerald {
        background: #059669;
        color: #FFFFFF;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: inset 0 -3px 5px rgba(0, 0, 0, 0.16),
                    inset 0 3px 5px rgba(255, 255, 255, 0.35),
                    0 4px 10px rgba(5, 150, 105, 0.25);
        min-height: 44px;
        min-width: 44px;
        transition: transform 0.18s ease, opacity 0.18s ease;
    }
    .clay-btn-emerald:hover:not(:disabled) { transform: translateY(-2px); }
    .clay-btn-emerald:focus-visible { outline: 3px solid #059669; outline-offset: 2px; }
    .clay-btn-emerald:active:not(:disabled) { transform: scale(0.96); }
    .clay-btn-emerald:disabled { opacity: 0.5; cursor: not-allowed; }

    .clay-btn-secondary {
        background: var(--kp-surface-30);
        color: var(--kp-accent-10);
        border: 1px solid var(--kp-border-tint);
        box-shadow: inset 0 -2px 4px rgba(147, 104, 183, 0.1),
                    0 3px 8px rgba(147, 104, 183, 0.08);
        min-height: 44px;
        min-width: 44px;
        transition: transform 0.18s ease, opacity 0.18s ease;
    }
    .clay-btn-secondary:hover:not(:disabled) { transform: translateY(-2px); }
    .clay-btn-secondary:focus-visible { outline: 3px solid var(--kp-accent-10); outline-offset: 2px; }
    .clay-btn-secondary:active:not(:disabled) { transform: scale(0.96); }
    .clay-btn-secondary:disabled { opacity: 0.5; cursor: not-allowed; }

    .accordion-content {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows 0.25s ease-out;
    }
    .accordion-content.is-open { grid-template-rows: 1fr; }
    .accordion-inner { overflow: hidden; }

    @keyframes cardEnter {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stagger-card {
        opacity: 0;
        animation: cardEnter 0.28s ease-out forwards;
    }

    .slide-track {
        position: relative;
        background: var(--kp-surface-tint);
        border-radius: var(--kp-radius-pill);
        box-shadow: inset 0 2px 5px rgba(147, 104, 183, 0.25);
        overflow: hidden;
        height: 3.5rem;
        user-select: none;
    }
    .slide-thumb {
        position: absolute;
        top: 0.25rem;
        left: 0.25rem;
        height: 3rem;
        width: 3rem;
        min-height: 44px;
        min-width: 44px;
        background: var(--kp-accent-10);
        border-radius: var(--kp-radius-pill);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        cursor: grab;
        box-shadow: 0 3px 8px rgba(147, 104, 183, 0.35);
        touch-action: none;
        z-index: 10;
        will-change: transform;
    }
    .slide-thumb:focus-visible { outline: 3px solid var(--kp-text-main); outline-offset: 2px; }
    .slide-thumb:active { cursor: grabbing; }
    .slide-text {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--kp-accent-10);
        font-weight: 900;
        font-size: 0.875rem;
        z-index: 2;
        pointer-events: none;
        will-change: opacity;
    }
    .slide-fill {
        position: absolute;
        inset: 0;
        background: var(--kp-accent-10);
        opacity: 0.18;
        z-index: 1;
        transform-origin: left center;
        transform: scaleX(0);
        will-change: transform;
    }

    @media (prefers-reduced-motion: reduce) {
        *, ::before, ::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
@endsection

@section('content')
<div class="flex flex-col gap-6 pb-8">
    <!-- Top Header & Refresh Action -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-fluid-h1 font-black text-admin-textMain drop-shadow-sm flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-admin-navBg text-admin-sideBg flex items-center justify-center shadow-inner shrink-0">
                    <i class="ph ph-moped text-2xl"></i>
                </span>
                <span>Kelola Pengiriman</span>
            </h1>
            <p class="text-fluid-body font-semibold text-admin-textMuted mt-1">
                Navigasi kurir internal, validasi keaslian resi & kesesuaian ekspedisi pilihan pembeli, serta verifikasi bukti sampai.
            </p>
        </div>

        <button type="button" id="btn-refresh-pengiriman" class="clay-btn-secondary px-4 py-2.5 rounded-2xl text-xs font-black flex items-center gap-2 shrink-0">
            <i id="icon-refresh-pengiriman" class="ph ph-arrows-clockwise text-base"></i>
            <span>Muat Ulang Data</span>
        </button>
    </div>

    <!-- Segmented Control (Tabs) -->
    <div class="flex p-1.5 bg-[#E8D5F6] rounded-2xl w-full max-w-lg shadow-[inset_0_2px_4px_rgba(147,104,183,0.2)] border border-white" role="tablist">
        <button type="button" role="tab" aria-selected="true" data-target="tab-internal" class="tab-btn min-h-[44px] flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-transform bg-white text-admin-sideBg shadow-sm flex items-center justify-center gap-2">
            <i class="ph ph-motorcycle text-base"></i>
            <span>Kurir Internal</span>
            <span id="badge-count-internal" class="px-2 py-0.5 text-[11px] rounded-full bg-admin-navBg text-admin-sideBg font-black">0</span>
        </button>
        <button type="button" role="tab" aria-selected="false" data-target="tab-ekspedisi" class="tab-btn min-h-[44px] flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-transform text-admin-sideBg/70 hover:text-admin-sideBg flex items-center justify-center gap-2">
            <i class="ph ph-truck text-base"></i>
            <span>Ekspedisi Eksternal</span>
            <span id="badge-count-ekspedisi" class="px-2 py-0.5 text-[11px] rounded-full bg-white/60 text-admin-sideBg font-black">0</span>
        </button>
    </div>

    <!-- Tab 1: Kurir Internal -->
    <div id="tab-internal" class="tab-pane flex flex-col gap-4">
        <div id="internal-list" class="flex flex-col gap-5"></div>
    </div>

    <!-- Tab 2: Ekspedisi Eksternal -->
    <div id="tab-ekspedisi" class="tab-pane flex flex-col gap-4 hidden">
        <div id="ekspedisi-list" class="flex flex-col gap-5"></div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL PENYELESAIAN PENGIRIMAN                             -->
<!-- Mode 1: Upload Bukti (Khusus Kurir Internal)              -->
<!-- Mode 2: Cek/Inspeksi Bukti Kurir (Khusus Ekspedisi)       -->
<!-- ========================================================= -->
<div id="modal-penyelesaian" role="dialog" aria-modal="true" aria-labelledby="modal-title-penyelesaian" class="fixed inset-0 z-50 flex items-center justify-center bg-[#1E1B24]/65 opacity-0 pointer-events-none transition-opacity duration-150 p-4">
    <div id="modal-penyelesaian-card" class="clay-modal-surface w-full max-w-lg p-6 md:p-7 relative max-h-[90dvh] overflow-y-auto hide-scrollbar">
        <button type="button" id="btn-close-modal" aria-label="Tutup modal" class="min-w-[44px] min-h-[44px] absolute top-5 right-5 rounded-full bg-white text-admin-textMuted shadow-sm hover:text-red-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-admin-sideBg transition-transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center">
            <i class="ph ph-x text-lg"></i>
        </button>

        <div class="flex items-center gap-3.5 mb-5 border-b border-admin-accent/60 pb-4 pr-12">
            <div class="w-12 h-12 bg-admin-navBg text-admin-sideBg rounded-2xl flex items-center justify-center shrink-0">
                <i id="modal-header-icon" class="ph ph-package text-2xl"></i>
            </div>
            <div>
                <h2 id="modal-title-penyelesaian" class="text-fluid-h2 font-black text-admin-textMain">Validasi Pengiriman</h2>
                <p class="text-xs font-bold text-admin-textMuted">
                    Pesanan <span id="modal-order-id" class="text-admin-sideBg font-black">#---</span> • <span id="modal-channel-badge" class="uppercase">Kurir</span>
                </p>
            </div>
        </div>

        <form id="form-penyelesaian" class="flex flex-col gap-4" enctype="multipart/form-data">
            <!-- MODE A: FORM UPLOAD (KHUSUS KURIR INTERNAL TOKO) -->
            <div id="mode-internal-upload" class="flex flex-col gap-4">
                <div class="p-4 rounded-2xl bg-white border border-admin-accent/40">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="input-bukti-sampai" class="text-xs font-black uppercase tracking-wider text-admin-textMain flex items-center gap-1.5">
                            <i class="ph ph-camera text-admin-sideBg text-base"></i>
                            <span>1. Foto Bukti Barang Sampai</span>
                        </label>
                        <span class="px-2 py-0.5 rounded-md bg-purple-100 text-admin-sideBg text-[10px] font-black uppercase">Wajib</span>
                    </div>
                    <p class="text-[11px] font-semibold text-admin-textMuted mb-3">
                        Unggah foto paket saat diserahkan kurir internal ke penerima.
                    </p>
                    <label for="input-bukti-sampai" class="flex flex-col items-center justify-center w-full min-h-[104px] p-3.5 border-2 border-dashed border-admin-accent rounded-xl bg-admin-bg/60 hover:bg-admin-navBg/30 cursor-pointer transition-colors text-center">
                        <i class="ph ph-upload-simple text-2xl text-admin-sideBg mb-1"></i>
                        <span class="text-xs font-bold text-admin-textMuted">
                            Klik untuk pilih foto barang sampai (Maks. 5MB)
                        </span>
                        <input type="file" id="input-bukti-sampai" name="arrival_proof" class="sr-only" accept="image/jpeg,image/png,image/webp">
                    </label>
                    <p id="preview-bukti-sampai" class="text-xs font-bold text-emerald-700 mt-2 hidden text-center truncate"></p>
                </div>

                <!-- Upload 2: Khusus COD / Sisa DP Internal -->
                <div id="section-internal-cod" class="hidden flex-col gap-3.5 p-4 rounded-2xl bg-amber-50 border border-amber-200">
                    <div class="flex justify-between items-center pb-2.5 border-b border-amber-200/70">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-900 block">2. Pelunasan Tagihan COD / DP</span>
                            <span id="modal-metode-label" class="text-[11px] font-bold text-amber-700">Wajib Dilunasi Saat Serah Terima</span>
                        </div>
                        <span id="modal-sisa-tagihan" data-raw="0" class="text-lg font-black text-amber-700">Rp 0</span>
                    </div>

                    <div>
                        <label for="input-nominal" class="block text-xs font-black uppercase tracking-wider text-amber-900 mb-1">
                            Nominal Uang Diterima
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-amber-800 font-bold text-sm">Rp</span>
                            <input type="number" id="input-nominal" name="nominal_diterima" min="1" class="w-full min-h-[44px] pl-10 pr-24 py-2 rounded-xl bg-white border border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-black text-admin-textMain text-sm" placeholder="Masukkan angka persis...">
                            <button type="button" id="btn-isi-pas" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-lg bg-amber-100 text-amber-900 text-[11px] font-black hover:bg-amber-600 hover:text-white transition-colors">
                                Uang Pas
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="input-bukti-bayar" class="block text-xs font-black uppercase tracking-wider text-amber-900 mb-1.5">
                            Foto Bukti Pelunasan Pembayaran
                        </label>
                        <label for="input-bukti-bayar" class="flex flex-col items-center justify-center w-full min-h-[96px] p-3 border-2 border-dashed border-amber-300 rounded-xl bg-white hover:bg-amber-100/40 cursor-pointer transition-colors text-center">
                            <i class="ph ph-wallet text-2xl text-amber-600 mb-1"></i>
                            <span class="text-xs font-bold text-amber-800">
                                Pilih foto uang tunai / bukti transfer COD
                            </span>
                            <input type="file" id="input-bukti-bayar" name="payment_proof" class="sr-only" accept="image/jpeg,image/png,image/webp">
                        </label>
                        <p id="preview-bukti-bayar" class="text-xs font-bold text-amber-800 mt-2 hidden text-center truncate"></p>
                    </div>
                </div>
            </div>

            <!-- MODE B: INSPEKSI BUKTI DARI KURIR EKSTERNAL (READONLY) -->
            <div id="mode-ekspedisi-review" class="hidden flex-col gap-4">
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-2.5 text-xs font-bold text-emerald-800">
                    <i class="ph ph-shield-check text-lg text-emerald-600 shrink-0 mt-0.5"></i>
                    <span>Bukti berikut dikirimkan oleh <strong>Kurir Eksternal</strong>. Silakan periksa foto sebelum menyelesaikan pesanan.</span>
                </div>

                <div class="p-4 rounded-2xl bg-white border border-admin-accent/50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black uppercase tracking-wider text-admin-textMain flex items-center gap-1.5">
                            <i class="ph ph-package text-admin-sideBg text-base"></i>
                            <span>Bukti Barang Sampai (POD Kurir)</span>
                        </span>
                        <a id="link-full-arrival" href="#" target="_blank" rel="noopener noreferrer" class="text-[11px] font-black text-admin-sideBg hover:underline flex items-center gap-1">
                            <span>Buka Ukuran Penuh</span>
                            <i class="ph ph-arrow-square-out"></i>
                        </a>
                    </div>
                    <div class="w-full h-48 rounded-xl overflow-hidden bg-admin-bg border border-admin-accent/30 flex items-center justify-center">
                        <img id="img-review-arrival" src="" alt="Bukti Barang Sampai Kurir" class="w-full h-full object-cover" loading="lazy">
                    </div>
                </div>

                <div id="section-ekspedisi-cod-review" class="hidden flex-col gap-2.5 p-4 rounded-2xl bg-amber-50 border border-amber-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-900 block">Bukti Setoran COD Kurir</span>
                            <span id="review-cod-nominal" class="text-xs font-black text-amber-700">Tagihan: Rp 0</span>
                        </div>
                        <a id="link-full-payment" href="#" target="_blank" rel="noopener noreferrer" class="text-[11px] font-black text-amber-800 hover:underline flex items-center gap-1">
                            <span>Buka Ukuran Penuh</span>
                            <i class="ph ph-arrow-square-out"></i>
                        </a>
                    </div>
                    <div class="w-full h-48 rounded-xl overflow-hidden bg-white border border-amber-200 flex items-center justify-center">
                        <img id="img-review-payment" src="" alt="Bukti Pelunasan COD Kurir" class="w-full h-full object-cover" loading="lazy">
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
                <button type="button" id="btn-batal-modal" class="clay-btn-secondary flex-1 py-3 px-4 rounded-2xl font-black text-xs sm:text-sm flex items-center justify-center gap-2">
                    <span>Tutup</span>
                </button>
                <button type="submit" id="btn-submit-penyelesaian" class="clay-btn-emerald flex-1 py-3 px-4 rounded-2xl font-black text-xs sm:text-sm flex items-center justify-center gap-2">
                    <i class="ph ph-check-circle text-lg"></i>
                    <span id="btn-submit-text">Verifikasi & Selesaikan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
/**
 * ===================================================================
 * KELOLA PENGIRIMAN MODULE (v13.0 - Clean Anti-Fake & Anti-Cross Courier)
 * ===================================================================
 */
(function() {
    'use strict';

    let fetchAbortController = null;
    let actionAbortController = null;
    const activeTimers = new Set();

    let ordersState = {
        internal: [],
        ekspedisi: []
    };
    let activeSlider = null;
    let targetOrderContext = null;

    // DOM References
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const internalList = document.getElementById('internal-list');
    const ekspedisiList = document.getElementById('ekspedisi-list');
    const badgeInternal = document.getElementById('badge-count-internal');
    const badgeEkspedisi = document.getElementById('badge-count-ekspedisi');
    const btnRefresh = document.getElementById('btn-refresh-pengiriman');
    const iconRefresh = document.getElementById('icon-refresh-pengiriman');

    const modalPenyelesaian = document.getElementById('modal-penyelesaian');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const btnBatalModal = document.getElementById('btn-batal-modal');
    const btnIsiPas = document.getElementById('btn-isi-pas');
    const formPenyelesaian = document.getElementById('form-penyelesaian');

    const modalTitle = document.getElementById('modal-title-penyelesaian');
    const modalOrderId = document.getElementById('modal-order-id');
    const modalChannelBadge = document.getElementById('modal-channel-badge');

    const modeInternalUpload = document.getElementById('mode-internal-upload');
    const sectionInternalCod = document.getElementById('section-internal-cod');
    const modeEkspedisiReview = document.getElementById('mode-ekspedisi-review');
    const sectionEkspedisiCodReview = document.getElementById('section-ekspedisi-cod-review');

    const modalSisaTagihan = document.getElementById('modal-sisa-tagihan');
    const modalMetodeLabel = document.getElementById('modal-metode-label');
    const inputBuktiSampai = document.getElementById('input-bukti-sampai');
    const previewBuktiSampai = document.getElementById('preview-bukti-sampai');
    const inputNominal = document.getElementById('input-nominal');
    const inputBuktiBayar = document.getElementById('input-bukti-bayar');
    const previewBuktiBayar = document.getElementById('preview-bukti-bayar');

    const imgReviewArrival = document.getElementById('img-review-arrival');
    const linkFullArrival = document.getElementById('link-full-arrival');
    const imgReviewPayment = document.getElementById('img-review-payment');
    const linkFullPayment = document.getElementById('link-full-payment');
    const reviewCodNominal = document.getElementById('review-cod-nominal');

    const btnSubmitPenyelesaian = document.getElementById('btn-submit-penyelesaian');
    const btnSubmitText = document.getElementById('btn-submit-text');

    function registerTimeout(fn, delay) {
        const id = window.setTimeout(() => {
            activeTimers.delete(id);
            fn();
        }, delay);
        activeTimers.add(id);
        return id;
    }

    function clearAllTimers() {
        activeTimers.forEach((id) => window.clearTimeout(id));
        activeTimers.clear();
    }

    function formatRp(num) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(Number(num) || 0);
    }

    function formatTime(timeStr) {
        if (!timeStr) return '--:--';
        return String(timeStr).substring(0, 5);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&' + 'amp;')
            .replace(/</g, '&' + 'lt;')
            .replace(/>/g, '&' + 'gt;')
            .replace(/"/g, '&' + 'quot;')
            .replace(/'/g, '&' + '#039;');
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function findOrderById(orderId) {
        return ordersState.internal.find((o) => o.id === orderId)
            || ordersState.ekspedisi.find((o) => o.id === orderId)
            || null;
    }

    function renderSkeletons(container) {
        if (!container) return;
        container.innerHTML = [1, 2].map(() => `
            <div class="clay-card p-5 md:p-6 animate-pulse flex flex-col gap-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex gap-4 items-center w-full md:w-2/3">
                        <div class="w-14 h-14 rounded-2xl bg-admin-navBg shrink-0"></div>
                        <div class="flex flex-col gap-2 w-full">
                            <div class="w-28 h-3.5 bg-admin-accent rounded-full"></div>
                            <div class="w-48 h-5 bg-admin-navBg rounded-lg"></div>
                            <div class="w-3/4 h-3.5 bg-admin-accent/60 rounded-full"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 self-end md:self-center">
                        <div class="w-24 h-6 bg-admin-accent rounded-full"></div>
                        <div class="w-11 h-11 bg-white rounded-full"></div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderEmptyState(container, iconClass, title, subtitle) {
        if (!container) return;
        container.innerHTML = `
            <div class="clay-card p-10 md:p-14 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-admin-navBg text-admin-sideBg rounded-full flex items-center justify-center mb-4 shadow-inner">
                    <i class="ph ${iconClass} text-4xl"></i>
                </div>
                <h3 class="text-fluid-h2 font-black text-admin-textMain mb-1">${escapeHtml(title)}</h3>
                <p class="text-fluid-body font-semibold text-admin-textMuted max-w-md">${escapeHtml(subtitle)}</p>
            </div>
        `;
    }

    function getCourierStatusMeta(status) {
        const map = {
            waiting_resi: {
                label: 'Menunggu Input Resi',
                badgeClass: 'bg-gray-100 text-gray-700 border-gray-300',
                icon: 'ph-barcode',
                step: 1
            },
            in_transit: {
                label: 'Paket Dikirim (Transit)',
                badgeClass: 'bg-blue-100 text-blue-800 border-blue-300',
                icon: 'ph-truck',
                step: 2
            },
            on_delivery: {
                label: 'Kurir Menuju Lokasi',
                badgeClass: 'bg-indigo-100 text-indigo-800 border-indigo-300',
                icon: 'ph-navigation-arrow',
                step: 3
            },
            delivered: {
                label: 'Barang Sampai (POD Masuk)',
                badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-300',
                icon: 'ph-check-circle',
                step: 4
            },
            problem: {
                label: 'Kendala Pengiriman',
                badgeClass: 'bg-red-100 text-red-800 border-red-300',
                icon: 'ph-warning-octagon',
                step: 2
            }
        };
        return map[status] || map.waiting_resi;
    }

    // --- CARD BUILDER ---
    function generateCardHTML(order, isInternal, index) {
        const isUnpaidRemainder = Number(order.remaining_amount) > 0;
        const badgeColor = isUnpaidRemainder
            ? 'bg-amber-100 text-amber-800 border-amber-300'
            : 'bg-emerald-100 text-emerald-800 border-emerald-300';

        const badgeText = isUnpaidRemainder
            ? (order.payment_method === 'cod' ? `COD - Sisa ${formatRp(order.remaining_amount)}` : `DP 30% - Sisa ${formatRp(order.remaining_amount)}`)
            : 'Lunas 100%';

        const addr = order.address_detail || {};
        const staggerDelay = Math.min(index * 50, 250);

        const itemsHtml = (order.items || []).map((item) => `
            <div class="flex justify-between items-center py-2.5 border-b border-white last:border-0 text-xs sm:text-sm">
                <span class="font-bold text-admin-textMain flex items-center gap-2">
                    <i class="ph ph-check-circle text-emerald-600 text-sm"></i>
                    <span>${escapeHtml(item.name)}</span>
                    <span class="text-admin-sideBg font-black ml-1">x${Number(item.qty)}</span>
                </span>
                <span class="font-black text-admin-textMuted">${formatRp(item.subtotal || (item.qty * item.price))}</span>
            </div>
        `).join('');

        let detailedAddressSection = '';
        let actionHtml = '';

        if (isInternal) {
            const coordBadge = addr.has_coordinates
                ? `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                       <i class="ph ph-gps-fix text-xs"></i> Koordinat Akurat (${Number(addr.latitude).toFixed(5)}, ${Number(addr.longitude).toFixed(5)})
                   </span>`
                : `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                       <i class="ph ph-map-pin-line text-xs"></i> Rute Teks Alamat
                   </span>`;

            const distanceBadge = (addr.distance_km !== null && addr.distance_km !== undefined)
                ? `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-purple-100 text-admin-sideBg border border-purple-200">
                       <i class="ph ph-path text-xs"></i> ${Number(addr.distance_km)} KM dari Dapur
                   </span>`
                : '';

            const landmarkHtml = addr.landmark
                ? `<div class="flex items-start gap-2 text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl mt-2">
                       <i class="ph ph-flag-banner text-base text-amber-600 shrink-0 mt-0.5"></i>
                       <div><span class="font-black uppercase text-[10px] tracking-wider block text-amber-600">Patokan Lokasi</span>${escapeHtml(addr.landmark)}</div>
                   </div>`
                : '';

            const waButtonHtml = order.wa_chat_url
                ? `<a href="${escapeHtml(order.wa_chat_url)}" target="_blank" rel="noopener noreferrer" class="clay-btn-secondary px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 flex-1 sm:flex-initial">
                       <i class="ph ph-whatsapp-logo text-base text-emerald-600"></i>
                       <span>Hubungi Penerima</span>
                   </a>`
                : '';

            detailedAddressSection = `
                <div class="mb-4 p-4 rounded-2xl bg-white border border-white">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3 pb-2.5 border-b border-admin-bg">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-admin-navBg text-admin-sideBg text-[10px] font-black uppercase tracking-wider">
                                ${escapeHtml(addr.label || 'Alamat Tujuan')}
                            </span>
                            ${distanceBadge}
                        </div>
                        ${coordBadge}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="space-y-1.5">
                            <p class="text-[10px] font-black uppercase tracking-wider text-admin-textMuted">Penerima & Kontak</p>
                            <p class="font-black text-sm text-admin-textMain flex items-center gap-1.5">
                                <i class="ph ph-user text-admin-sideBg"></i>
                                <span>${escapeHtml(order.recipient)}</span>
                            </p>
                            <p class="font-bold text-admin-textMuted flex items-center gap-1.5">
                                <i class="ph ph-phone text-admin-sideBg"></i>
                                <span>${escapeHtml(order.recipient_whatsapp || '-')}</span>
                            </p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-[10px] font-black uppercase tracking-wider text-admin-textMuted">Detail Alamat Lengkap</p>
                            <p class="font-extrabold text-admin-textMain leading-relaxed">
                                ${escapeHtml(addr.street_address || order.address)}
                            </p>
                            <p class="font-semibold text-admin-textMuted">
                                Kec. ${escapeHtml(addr.district || '-')}, ${escapeHtml(addr.city || '-')}, ${escapeHtml(addr.province || '-')} (${escapeHtml(addr.postal_code || '-')})
                            </p>
                        </div>
                    </div>

                    ${landmarkHtml}

                    <div class="mt-4 pt-3 border-t border-admin-bg flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                        ${waButtonHtml}
                        <a href="${escapeHtml(addr.gmaps_url || '#')}" target="_blank" rel="noopener noreferrer" class="clay-btn px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 flex-1">
                            <i class="ph ph-navigation-arrow text-base"></i>
                            <span>Buka Rute Google Maps (Dari GPS Admin)</span>
                            <i class="ph ph-arrow-square-out text-sm"></i>
                        </a>
                    </div>
                </div>
            `;

            const slideLabel = isUnpaidRemainder
                ? 'Geser: Upload Bukti Sampai & Pelunasan COD'
                : 'Geser: Upload Bukti Barang Sampai & Selesai';

            actionHtml = `
                <div class="mt-4 pt-4 border-t border-admin-accent/50">
                    <div class="slide-track w-full" data-order-id="${escapeHtml(order.id)}">
                        <div class="slide-fill"></div>
                        <div class="slide-text">
                            <span>${slideLabel}</span>
                            <i class="ph ph-caret-double-right ml-2 text-base"></i>
                        </div>
                        <div class="slide-thumb" tabindex="0" role="slider" aria-label="${slideLabel}">
                            <i class="ph ph-moped text-xl"></i>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // === PANEL EKSPEDISI EKSTERNAL: SIMPEL, BERSIH (CEK RESI ASLI & ANTI-SILANG KURIR) ===
            const cMeta = getCourierStatusMeta(order.courier_status);
            const hasResi = Boolean(order.tracking_number && String(order.tracking_number).trim() !== '');
            const isPermanentlyLocked = hasResi && ['on_delivery', 'delivered'].includes(order.courier_status);
            const isDeliveredByCourier = order.courier_status === 'delivered';
            const manifestList = Array.isArray(order.courier_manifest) ? order.courier_manifest : [];
            const courierCodeUpper = escapeHtml(order.courier_code || 'JNE');
            const resiHintText = escapeHtml(order.resi_format_hint || `Wajib format resi ${courierCodeUpper}`);

            const manifestHtml = manifestList.length > 0
                ? `<div class="mt-3 pt-3 border-t border-admin-bg max-h-40 overflow-y-auto space-y-2 pr-1">
                       ${manifestList.map((m, mIdx) => `
                           <div class="flex items-start gap-2.5 text-xs ${mIdx === 0 ? 'text-admin-textMain font-bold' : 'text-admin-textMuted font-medium'}">
                               <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 ${mIdx === 0 ? 'bg-emerald-500' : 'bg-admin-accent'}"></span>
                               <div class="flex-1 min-w-0">
                                   <div class="flex flex-wrap items-center gap-2">
                                       <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-admin-bg text-admin-sideBg">${escapeHtml(m.status || 'LOG')}</span>
                                       <span class="text-[10px] text-admin-textMuted font-bold">${escapeHtml(m.time || '-')}</span>
                                   </div>
                                   <p class="mt-0.5 leading-snug">${escapeHtml(m.description || '-')}</p>
                               </div>
                           </div>
                       `).join('')}
                   </div>`
                : `<p class="text-[11px] font-semibold text-admin-textMuted italic mt-2">Belum ada riwayat pengiriman.</p>`;

            const steps = [
                { num: 1, label: 'Input Resi' },
                { num: 2, label: 'Paket Dikirim' },
                { num: 3, label: 'Menuju Lokasi' },
                { num: 4, label: 'Barang Sampai' }
            ];
            const stepperHtml = `
                <div class="grid grid-cols-4 gap-2 my-3">
                    ${steps.map((s) => {
                        const active = cMeta.step >= s.num;
                        return `
                            <div class="flex flex-col items-center text-center p-2 rounded-xl border ${active ? 'bg-admin-navBg/60 border-admin-sideBg/40 text-admin-sideBg' : 'bg-admin-bg/50 border-white text-admin-textMuted opacity-60'}">
                                <span class="w-6 h-6 rounded-full text-[11px] font-black flex items-center justify-center mb-1 ${active ? 'bg-admin-sideBg text-white' : 'bg-white text-admin-textMuted'}">${s.num}</span>
                                <span class="text-[10px] font-black leading-tight">${s.label}</span>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;

            const lockedResiBoxHtml = hasResi ? `
                <div id="box-locked-resi-${escapeHtml(order.id)}" class="mt-3 p-3.5 rounded-2xl bg-admin-bg/70 border border-admin-accent flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-admin-textMuted">No. Resi (${courierCodeUpper})</span>
                            ${isPermanentlyLocked
                                ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase border border-emerald-200">
                                       <i class="ph ph-lock-key text-xs"></i> Terkunci Permanen
                                   </span>`
                                : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-100 text-admin-sideBg text-[10px] font-black uppercase border border-purple-200">
                                       <i class="ph ph-shield-check text-xs"></i> Resi Asli & Sesuai Kurir ${courierCodeUpper}
                                   </span>`
                            }
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-base font-black text-admin-textMain tracking-wider">${escapeHtml(order.tracking_number)}</span>
                            <button type="button" class="btn-copy-resi px-2 py-1 rounded-lg bg-white border border-admin-accent text-[11px] font-black text-admin-sideBg hover:bg-admin-navBg transition-colors flex items-center gap-1" data-resi="${escapeHtml(order.tracking_number)}">
                                <i class="ph ph-copy"></i> Salin
                            </button>
                        </div>
                    </div>

                    ${!isPermanentlyLocked ? `
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button type="button"
                                    class="btn-buka-koreksi-resi clay-btn-secondary px-3.5 py-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 w-full sm:w-auto"
                                    data-order-id="${escapeHtml(order.id)}">
                                <i class="ph ph-pencil-simple text-sm"></i>
                                <span>Koreksi Resi</span>
                            </button>
                        </div>
                    ` : ''}
                </div>
            ` : '';

            const editResiBoxHtml = !isPermanentlyLocked ? `
                <div id="box-edit-resi-${escapeHtml(order.id)}" class="${hasResi ? 'hidden' : ''} mt-3 p-4 rounded-2xl bg-admin-bg/50 border-2 border-dashed border-admin-accent space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-black text-admin-textMain flex items-center gap-1.5">
                            <i class="ph ph-barcode text-base text-admin-sideBg"></i>
                            <span>${hasResi ? 'Koreksi Nomor Resi Pengiriman' : 'Input Nomor Resi Ekspedisi'}</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-black uppercase border border-indigo-200">
                            Pilihan Pembeli: ${escapeHtml(order.courier)}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-admin-accent/50 text-[11px] space-y-1">
                        <p class="font-bold text-admin-textMain">
                            <span class="text-admin-sideBg font-black">Format Wajib ${courierCodeUpper}:</span> ${resiHintText}.
                        </p>
                        <p class="font-semibold text-admin-textMuted">
                            Sistem otomatis menolak <strong>resi bohongan/asal ketik</strong> serta menolak <strong>resi dari ekspedisi lain</strong> yang tidak sesuai dengan pilihan checkout pembeli.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <i class="ph ph-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-admin-textMuted text-base"></i>
                            <input type="text"
                                   id="input-resi-${escapeHtml(order.id)}"
                                   value="${escapeHtml(order.tracking_number || '')}"
                                   placeholder="Masukkan No. Resi ${courierCodeUpper}..."
                                   class="w-full min-h-[44px] pl-10 pr-3 py-2 rounded-xl bg-white border border-admin-accent text-xs font-black text-admin-textMain uppercase focus:outline-none focus:ring-2 focus:ring-admin-sideBg">
                        </div>
                        ${hasResi ? `
                            <button type="button"
                                    class="btn-batal-koreksi-resi clay-btn-secondary px-4 py-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 shrink-0"
                                    data-order-id="${escapeHtml(order.id)}">
                                <i class="ph ph-x text-sm"></i>
                                <span>Batal</span>
                            </button>
                        ` : ''}
                        <button type="button"
                                class="btn-simpan-resi clay-btn px-4 py-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 shrink-0"
                                data-order-id="${escapeHtml(order.id)}">
                            <i class="ph ph-shield-check text-base"></i>
                            <span>${hasResi ? 'Simpan Koreksi' : 'Validasi & Kunci Resi'}</span>
                        </button>
                    </div>
                </div>
            ` : '';

            const courierProofPreviewHtml = isDeliveredByCourier && order.arrival_proof_url
                ? `<div class="mt-3 pt-3 border-t border-emerald-200/70 bg-emerald-50/60 p-3 rounded-xl flex flex-wrap items-center justify-between gap-3">
                       <div class="flex items-center gap-3">
                           <img src="${escapeHtml(order.arrival_proof_url)}" alt="POD Kurir" class="w-12 h-12 rounded-lg object-cover border border-emerald-300 shrink-0" loading="lazy">
                           <div class="text-xs">
                               <span class="font-black text-emerald-900 block">Bukti Pengiriman Kurir Tersedia</span>
                               <span class="text-[11px] font-semibold text-emerald-700">
                                   ${isUnpaidRemainder ? 'Termasuk Bukti Barang Sampai & Bukti Setoran COD' : 'Foto Bukti Barang Sampai (POD) dari Kurir'}
                               </span>
                           </div>
                       </div>
                   </div>`
                : '';

            detailedAddressSection = `
                <div class="mb-4 p-4 rounded-2xl bg-white border border-white text-xs space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-admin-textMuted">Penerima & Alamat Tujuan</span>
                        <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-black text-[10px] border border-blue-200">
                            Ekspedisi Checkout: ${escapeHtml(order.courier)}
                        </span>
                    </div>
                    <p class="font-black text-sm text-admin-textMain">${escapeHtml(order.recipient)} <span class="text-admin-textMuted font-bold">(${escapeHtml(order.recipient_whatsapp || '-')})</span></p>
                    <p class="font-semibold text-admin-textMuted leading-relaxed">${escapeHtml(order.address)}</p>
                </div>

                <div class="mb-4 p-4 rounded-2xl bg-white border-2 border-admin-accent/60">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-admin-bg">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-truck text-lg text-admin-sideBg"></i>
                            <div>
                                <h5 class="text-xs font-black uppercase tracking-wider text-admin-textMain">Status Resi & Pengiriman Ekspedisi</h5>
                                <p class="text-[11px] font-semibold text-admin-textMuted">Filter keaslian resi & kecocokan ekspedisi (${courierCodeUpper})</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-black uppercase tracking-wider ${cMeta.badgeClass}">
                            <i class="ph ${cMeta.icon} text-xs"></i>
                            <span>${escapeHtml(cMeta.label)}</span>
                        </span>
                    </div>

                    ${stepperHtml}
                    ${lockedResiBoxHtml}
                    ${editResiBoxHtml}
                    ${courierProofPreviewHtml}
                    ${manifestHtml}
                </div>
            `;

            const statusNoticeHtml = !hasResi
                ? `<div class="bg-gray-100 border border-gray-300 rounded-xl px-3.5 py-2.5 flex items-center gap-2.5 text-gray-700 font-bold text-xs flex-1">
                       <i class="ph ph-lock-simple text-lg text-gray-600 shrink-0"></i>
                       <span>Masukkan <strong>Nomor Resi ${courierCodeUpper}</strong> yang valid di atas untuk mengirim notifikasi resi ke WA & Web pembeli.</span>
                   </div>`
                : (isDeliveredByCourier
                    ? `<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-3.5 py-2.5 flex items-center gap-2.5 text-emerald-800 font-bold text-xs flex-1">
                           <i class="ph ph-check-circle text-lg text-emerald-600 shrink-0"></i>
                           <span><strong>Paket Telah Sampai (POD Masuk)</strong>. Silakan klik tombol hijau di bawah untuk menyelesaikan pesanan.</span>
                       </div>`
                    : `<div class="bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2.5 flex items-center gap-2.5 text-amber-800 font-bold text-xs flex-1">
                           <i class="ph ph-clock-countdown text-lg text-amber-600 shrink-0"></i>
                           <span>Resi <strong>${escapeHtml(order.tracking_number)}</strong> aktif. Gunakan tombol di bawah untuk memperbarui tahapan kurir ke pembeli.</span>
                       </div>`);

            actionHtml = `
                <div class="mt-4 pt-4 border-t border-admin-accent/50 flex flex-col gap-3">
                    ${statusNoticeHtml}
                    <div class="flex flex-wrap items-center justify-end gap-2.5">
                        ${hasResi && order.courier_status === 'in_transit' ? `
                            <button type="button"
                                    class="btn-simulasi-signal clay-btn-secondary px-3.5 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-1.5"
                                    data-order-id="${escapeHtml(order.id)}"
                                    data-signal-status="on_delivery">
                                <i class="ph ph-navigation-arrow text-base text-indigo-600"></i>
                                <span>1. Update: Kurir Menuju Lokasi</span>
                            </button>
                        ` : ''}

                        ${hasResi && !isDeliveredByCourier ? `
                            <button type="button"
                                    class="btn-simulasi-signal clay-btn-secondary px-3.5 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-1.5"
                                    data-order-id="${escapeHtml(order.id)}"
                                    data-signal-status="delivered">
                                <i class="ph ph-broadcast text-base text-emerald-600"></i>
                                <span>${order.courier_status === 'in_transit' ? '2. ' : ''}Update: Barang Sampai & Bukti POD</span>
                            </button>
                        ` : ''}

                        <button type="button"
                                class="btn-cek-bukti-ekspedisi ${isDeliveredByCourier ? 'clay-btn-emerald' : 'clay-btn'} px-5 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2"
                                data-order-id="${escapeHtml(order.id)}"
                                ${!isDeliveredByCourier ? 'disabled title="Menunggu status Barang Sampai & POD"' : ''}>
                            <i class="ph ph-eye text-base"></i>
                            <span>Cek Bukti Kurir & Selesaikan</span>
                        </button>
                    </div>
                </div>
            `;
        }

        const courierPill = !isInternal
            ? `<span class="px-2.5 py-1 rounded-full border text-[10px] font-black uppercase tracking-wider ${getCourierStatusMeta(order.courier_status).badgeClass}">
                   ${escapeHtml(getCourierStatusMeta(order.courier_status).label)}
               </span>`
            : '';

        return `
            <div class="clay-card stagger-card p-5 md:p-6" id="card-${escapeHtml(order.id)}" style="animation-delay: ${staggerDelay}ms;">
                <button type="button" class="accordion-toggle w-full text-left cursor-pointer select-none flex flex-col md:flex-row justify-between items-start md:items-center gap-4 group focus-visible:outline focus-visible:outline-2 focus-visible:outline-admin-sideBg rounded-xl">
                    <div class="flex gap-4 items-start sm:items-center w-full md:w-auto">
                        <div class="w-14 h-14 rounded-2xl bg-admin-navBg text-admin-sideBg flex flex-col items-center justify-center shadow-inner shrink-0">
                            <i class="ph ${isInternal ? 'ph-clock' : 'ph-truck'} text-lg"></i>
                            <span class="text-xs font-black">${escapeHtml(isInternal ? formatTime(order.delivery_time) : (order.courier_code || 'EKS'))}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                <span class="text-[10px] font-extrabold text-admin-sideBg uppercase tracking-wider">
                                    ${escapeHtml(formatDate(order.delivery_date))}
                                </span>
                                <span class="text-[10px] font-black text-admin-textMuted">• #${escapeHtml(order.id)}</span>
                                ${order.tracking_number ? `<span class="text-[10px] font-black px-2 py-0.5 rounded bg-white text-admin-sideBg border border-admin-accent">RESI: ${escapeHtml(order.tracking_number)}</span>` : ''}
                            </div>
                            <h3 class="font-black text-base text-admin-textMain leading-snug truncate">
                                ${escapeHtml(order.recipient)}
                            </h3>
                            <p class="text-xs font-bold text-admin-textMuted flex items-center gap-1 mt-1">
                                <i class="ph ph-map-pin text-admin-sideBg shrink-0"></i>
                                <span class="line-clamp-1">${escapeHtml(addr.street_address || order.address)}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between md:justify-end w-full md:w-auto gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-white/60">
                        ${courierPill}
                        <span class="px-3 py-1 rounded-full border text-[10px] font-black uppercase tracking-wider ${badgeColor}">
                            ${escapeHtml(badgeText)}
                        </span>
                        <div class="w-11 h-11 rounded-full bg-white border border-admin-accent flex items-center justify-center text-admin-sideBg shadow-sm transition-transform duration-200 icon-chevron shrink-0">
                            <i class="ph ph-caret-down text-base"></i>
                        </div>
                    </div>
                </button>

                <div class="accordion-content">
                    <div class="accordion-inner">
                        <div class="pt-4 mt-4 border-t border-admin-accent/50">
                            ${detailedAddressSection}

                            <h4 class="text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="ph ph-receipt text-admin-sideBg"></i>
                                    <span>Rincian Item Pesanan (#${escapeHtml(order.id)})</span>
                                </span>
                                <span class="text-[11px] font-black text-admin-sideBg">${Number(order.items_count || 0)} Pcs Roti</span>
                            </h4>
                            <div class="bg-admin-bg p-3.5 rounded-2xl border border-white">
                                ${itemsHtml}
                                <div class="flex justify-between items-center pt-2.5 mt-2 border-t border-white text-xs font-bold text-admin-textMuted">
                                    <span>Ongkos Kirim (${escapeHtml(order.courier)})</span>
                                    <span>${order.shipping_cost > 0 ? formatRp(order.shipping_cost) : 'GRATIS'}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 mt-2 border-t border-white">
                                    <span class="text-xs font-black uppercase text-admin-textMuted">Total Transaksi</span>
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

    function fetchDeliveryData() {
        if (fetchAbortController) fetchAbortController.abort();
        fetchAbortController = new AbortController();

        renderSkeletons(internalList);
        renderSkeletons(ekspedisiList);

        if (btnRefresh) btnRefresh.disabled = true;
        if (iconRefresh) iconRefresh.classList.add('animate-spin');

        fetch('/api/admin/kelola-pengiriman', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: fetchAbortController.signal
        })
        .then((res) => {
            if (!res.ok) throw new Error('Gagal memuat data pengiriman dari server.');
            return res.json();
        })
        .then((res) => {
            if (res.status === 'success' && res.data) {
                ordersState.internal = res.data.internal || [];
                ordersState.ekspedisi = res.data.ekspedisi || [];
                renderAllLists();
            } else {
                throw new Error(res.message || 'Respons server tidak valid.');
            }
        })
        .catch((err) => {
            if (err.name === 'AbortError') return;
            renderEmptyState(internalList, 'ph-warning-circle', 'Gagal Memuat Data', err.message || 'Periksa koneksi jaringan Anda lalu muat ulang.');
            renderEmptyState(ekspedisiList, 'ph-warning-circle', 'Gagal Memuat Data', err.message || 'Periksa koneksi jaringan Anda lalu muat ulang.');
            showToast('Koneksi Bermasalah', err.message || 'Gagal mengambil data pengiriman.', 'error');
        })
        .finally(() => {
            if (btnRefresh) btnRefresh.disabled = false;
            if (iconRefresh) iconRefresh.classList.remove('animate-spin');
        });
    }

    function renderAllLists() {
        const internalOrders = ordersState.internal;
        const ekspedisiOrders = ordersState.ekspedisi;

        if (badgeInternal) badgeInternal.textContent = String(internalOrders.length);
        if (badgeEkspedisi) badgeEkspedisi.textContent = String(ekspedisiOrders.length);

        if (internalOrders.length > 0) {
            internalList.innerHTML = internalOrders.map((o, idx) => generateCardHTML(o, true, idx)).join('');
        } else {
            renderEmptyState(
                internalList,
                'ph-moped',
                'Tidak Ada Antrean Kurir Internal',
                'Semua pesanan kurir toko yang selesai dimasak akan otomatis muncul di daftar ini.'
            );
        }

        if (ekspedisiOrders.length > 0) {
            ekspedisiList.innerHTML = ekspedisiOrders.map((o, idx) => generateCardHTML(o, false, idx)).join('');
        } else {
            renderEmptyState(
                ekspedisiList,
                'ph-package',
                'Tidak Ada Paket Ekspedisi',
                'Belum ada pesanan luar kota/ekspedisi yang berstatus siap dikirim saat ini.'
            );
        }
    }

    // --- SIMPAN & VALIDASI RESI ---
    function saveTrackingResi(orderNumber, btnEl) {
        const order = findOrderById(orderNumber);
        if (!order) return;

        const inputEl = document.getElementById('input-resi-' + orderNumber);
        const resiVal = inputEl ? inputEl.value.trim().toUpperCase() : '';

        if (!resiVal) {
            showToast('Resi Kosong', `Masukkan nomor resi ${order.courier_code || 'ekspedisi'} terlebih dahulu.`, 'warning');
            if (inputEl) inputEl.focus();
            return;
        }

        if (actionAbortController) actionAbortController.abort();
        actionAbortController = new AbortController();

        const ogHtml = btnEl.innerHTML;
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-base"></i><span>Memvalidasi...</span>';

        fetch(`/api/admin/kelola-pengiriman/${encodeURIComponent(orderNumber)}/resi`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                tracking_number: resiVal
            }),
            signal: actionAbortController.signal
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok || data.status !== 'success') throw new Error(data.message || 'Gagal menyimpan resi.');
            return data;
        })
        .then((data) => {
            showToast('Resi Valid & Dikunci', data.message, 'success');
            updateEkspedisiOrderInState(orderNumber, {
                tracking_number: data.data.tracking_number,
                courier_status: data.data.courier_status,
                courier_manifest: data.data.courier_manifest
            });
        })
        .catch((err) => {
            if (err.name === 'AbortError') return;
            showToast('Validasi Resi Ditolak', err.message, 'error');
        })
        .finally(() => {
            btnEl.disabled = false;
            btnEl.innerHTML = ogHtml;
        });
    }

    function triggerCourierSignal(orderNumber, targetStatus, btnEl) {
        const order = findOrderById(orderNumber);
        if (!order || !order.tracking_number) {
            showToast('Resi Belum Diinput', 'Simpan nomor resi yang valid terlebih dahulu.', 'warning');
            return;
        }

        if (actionAbortController) actionAbortController.abort();
        actionAbortController = new AbortController();

        const ogHtml = btnEl.innerHTML;
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-base"></i><span>Memperbarui...</span>';

        fetch('/api/courier/webhook', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                order_number: orderNumber,
                tracking_number: order.tracking_number,
                courier_status: targetStatus,
                cod_amount_paid: order.remaining_amount > 0 ? order.remaining_amount : null
            }),
            signal: actionAbortController.signal
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok || data.status !== 'success') throw new Error(data.message || 'Gagal memperbarui status kurir.');
            return data;
        })
        .then((data) => {
            showToast('Status Kurir Diperbarui', data.message, 'success');
            updateEkspedisiOrderInState(orderNumber, {
                courier_status: data.data.courier_status,
                courier_manifest: data.data.courier_manifest,
                arrival_proof_url: data.data.arrival_proof_url,
                payment_proof_url: data.data.payment_proof_url
            });
        })
        .catch((err) => {
            if (err.name === 'AbortError') return;
            showToast('Gagal Update Kurir', err.message, 'error');
        })
        .finally(() => {
            btnEl.disabled = false;
            btnEl.innerHTML = ogHtml;
        });
    }

    function updateEkspedisiOrderInState(orderNumber, patch) {
        ordersState.ekspedisi = ordersState.ekspedisi.map((o) => {
            if (o.id === orderNumber) {
                return Object.assign({}, o, patch);
            }
            return o;
        });
        renderAllLists();
        const updatedCard = document.getElementById('card-' + orderNumber);
        if (updatedCard) {
            const content = updatedCard.querySelector('.accordion-content');
            const icon = updatedCard.querySelector('.icon-chevron');
            if (content) content.classList.add('is-open');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    }

    function executeCompleteOrder(orderNumber, formData, triggerBtn) {
        if (actionAbortController) actionAbortController.abort();
        actionAbortController = new AbortController();

        let originalBtnHtml = '';
        if (triggerBtn) {
            originalBtnHtml = triggerBtn.innerHTML;
            triggerBtn.disabled = true;
            triggerBtn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-lg"></i><span>Memproses...</span>';
        }

        fetch(`/api/admin/kelola-pengiriman/${encodeURIComponent(orderNumber)}/selesai`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            signal: actionAbortController.signal
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok || data.status !== 'success') {
                throw new Error(data.message || 'Gagal menyelesaikan pesanan.');
            }
            return data;
        })
        .then((data) => {
            toggleModal(false);
            showToast('Pesanan Selesai', data.message || `Pesanan #${orderNumber} berhasil diselesaikan.`, 'success');
            removeCardFromView(orderNumber);
        })
        .catch((err) => {
            if (err.name === 'AbortError') return;
            showToast('Gagal Validasi', err.message || 'Terjadi kendala saat menyimpan penyelesaian.', 'error');
            resetAllSliders();
        })
        .finally(() => {
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.innerHTML = originalBtnHtml;
            }
        });
    }

    function removeCardFromView(orderNumber) {
        ordersState.internal = ordersState.internal.filter((o) => o.id !== orderNumber);
        ordersState.ekspedisi = ordersState.ekspedisi.filter((o) => o.id !== orderNumber);

        if (badgeInternal) badgeInternal.textContent = String(ordersState.internal.length);
        if (badgeEkspedisi) badgeEkspedisi.textContent = String(ordersState.ekspedisi.length);

        const card = document.getElementById('card-' + orderNumber);
        if (card) {
            card.style.transform = 'scale(0.96) translateX(20px)';
            card.style.opacity = '0';
            registerTimeout(() => {
                card.remove();
                if (ordersState.internal.length === 0) {
                    renderEmptyState(
                        internalList,
                        'ph-moped',
                        'Tidak Ada Antrean Kurir Internal',
                        'Semua pesanan kurir toko yang selesai dimasak akan otomatis muncul di daftar ini.'
                    );
                }
                if (ordersState.ekspedisi.length === 0) {
                    renderEmptyState(
                        ekspedisiList,
                        'ph-package',
                        'Tidak Ada Paket Ekspedisi',
                        'Belum ada pesanan luar kota/ekspedisi yang berstatus siap dikirim saat ini.'
                    );
                }
            }, 220);
        }
    }

    // --- MODAL CONTROL ---
    function openModalForOrder(orderId) {
        const order = findOrderById(orderId);
        if (!order) return;

        const isInternal = order.type === 'internal';
        const sisa = Number(order.remaining_amount || 0);
        const reqPelunasan = sisa > 0;

        targetOrderContext = {
            id: order.id,
            isInternal: isInternal,
            reqPelunasan: reqPelunasan,
            sisa: sisa
        };

        formPenyelesaian.reset();
        previewBuktiSampai.classList.add('hidden');
        previewBuktiBayar.classList.add('hidden');

        modalOrderId.textContent = '#' + order.id;
        modalChannelBadge.textContent = isInternal ? 'Kurir Internal' : `Ekspedisi (${order.courier})`;

        if (isInternal) {
            modeInternalUpload.classList.remove('hidden');
            modeInternalUpload.classList.add('flex');
            modeEkspedisiReview.classList.add('hidden');
            modeEkspedisiReview.classList.remove('flex');

            btnSubmitText.textContent = 'Simpan Bukti & Selesaikan';

            if (reqPelunasan) {
                modalTitle.textContent = 'Upload Bukti Sampai & Pelunasan';
                sectionInternalCod.classList.remove('hidden');
                sectionInternalCod.classList.add('flex');
                modalSisaTagihan.textContent = formatRp(sisa);
                modalSisaTagihan.dataset.raw = String(sisa);
                modalMetodeLabel.textContent = order.payment_method === 'cod'
                    ? 'Pembayaran COD (Wajib Upload Bukti Bayar)'
                    : 'Pelunasan Sisa DP 70% (Wajib Upload Bukti Bayar)';
            } else {
                modalTitle.textContent = 'Upload Bukti Barang Sampai';
                sectionInternalCod.classList.add('hidden');
                sectionInternalCod.classList.remove('flex');
                modalSisaTagihan.dataset.raw = '0';
            }
        } else {
            modeInternalUpload.classList.add('hidden');
            modeInternalUpload.classList.remove('flex');
            modeEkspedisiReview.classList.remove('hidden');
            modeEkspedisiReview.classList.add('flex');

            modalTitle.textContent = 'Cek Bukti Pengiriman Kurir';
            btnSubmitText.textContent = 'Verifikasi & Tandai Selesai';

            const arrivalUrl = order.arrival_proof_url || '';
            imgReviewArrival.src = arrivalUrl;
            linkFullArrival.href = arrivalUrl || '#';

            if (reqPelunasan) {
                sectionEkspedisiCodReview.classList.remove('hidden');
                sectionEkspedisiCodReview.classList.add('flex');
                reviewCodNominal.textContent = `Nominal COD Diterima Kurir: ${formatRp(sisa)}`;
                const paymentUrl = order.payment_proof_url || '';
                imgReviewPayment.src = paymentUrl;
                linkFullPayment.href = paymentUrl || '#';
            } else {
                sectionEkspedisiCodReview.classList.add('hidden');
                sectionEkspedisiCodReview.classList.remove('flex');
            }
        }

        toggleModal(true);
    }

    function toggleModal(show) {
        if (!modalPenyelesaian) return;
        if (show) {
            modalPenyelesaian.classList.remove('opacity-0', 'pointer-events-none');
            window.requestAnimationFrame(() => {
                modalPenyelesaian.classList.add('modal-backdrop-open');
            });
        } else {
            modalPenyelesaian.classList.remove('modal-backdrop-open');
            modalPenyelesaian.classList.add('opacity-0', 'pointer-events-none');
            targetOrderContext = null;
            resetAllSliders();
        }
    }

    function resetAllSliders() {
        document.querySelectorAll('.slide-track').forEach((track) => {
            const thumb = track.querySelector('.slide-thumb');
            const fill = track.querySelector('.slide-fill');
            const text = track.querySelector('.slide-text');
            if (thumb) thumb.style.transform = 'translateX(0px)';
            if (fill) fill.style.transform = 'scaleX(0)';
            if (text) text.style.opacity = '1';
        });
    }

    // --- EVENT HANDLERS ---
    function handleTabClick(e) {
        const btn = e.currentTarget;
        tabBtns.forEach((b) => {
            b.setAttribute('aria-selected', 'false');
            b.classList.remove('bg-white', 'text-admin-sideBg', 'shadow-sm', 'font-black');
            b.classList.add('text-admin-sideBg/70', 'hover:text-admin-sideBg', 'font-bold');
        });
        btn.setAttribute('aria-selected', 'true');
        btn.classList.remove('text-admin-sideBg/70', 'hover:text-admin-sideBg', 'font-bold');
        btn.classList.add('bg-white', 'text-admin-sideBg', 'shadow-sm', 'font-black');

        tabPanes.forEach((pane) => pane.classList.add('hidden'));
        const targetPane = document.getElementById(btn.dataset.target);
        if (targetPane) targetPane.classList.remove('hidden');
    }

    function handleDocumentClick(e) {
        const toggle = e.target.closest('.accordion-toggle');
        if (toggle) {
            const content = toggle.nextElementSibling;
            const icon = toggle.querySelector('.icon-chevron');
            if (content) {
                content.classList.toggle('is-open');
                if (icon) {
                    icon.style.transform = content.classList.contains('is-open') ? 'rotate(180deg)' : 'rotate(0deg)';
                }
            }
            return;
        }

        const btnCopy = e.target.closest('.btn-copy-resi');
        if (btnCopy) {
            const resiText = btnCopy.dataset.resi || '';
            if (resiText && navigator.clipboard) {
                navigator.clipboard.writeText(resiText).then(() => {
                    showToast('Resi Disalin', `Nomor resi ${resiText} berhasil disalin ke clipboard.`, 'success');
                });
            }
            return;
        }

        const btnOpenKoreksi = e.target.closest('.btn-buka-koreksi-resi');
        if (btnOpenKoreksi) {
            const orderId = btnOpenKoreksi.dataset.orderId;
            const lockedBox = document.getElementById('box-locked-resi-' + orderId);
            const editBox = document.getElementById('box-edit-resi-' + orderId);
            const inputEl = document.getElementById('input-resi-' + orderId);
            if (lockedBox && editBox) {
                lockedBox.classList.add('hidden');
                editBox.classList.remove('hidden');
                if (inputEl) inputEl.focus();
            }
            return;
        }

        const btnCancelKoreksi = e.target.closest('.btn-batal-koreksi-resi');
        if (btnCancelKoreksi) {
            const orderId = btnCancelKoreksi.dataset.orderId;
            const order = findOrderById(orderId);
            const lockedBox = document.getElementById('box-locked-resi-' + orderId);
            const editBox = document.getElementById('box-edit-resi-' + orderId);
            const inputEl = document.getElementById('input-resi-' + orderId);
            if (inputEl && order) inputEl.value = order.tracking_number || '';
            if (lockedBox && editBox) {
                editBox.classList.add('hidden');
                lockedBox.classList.remove('hidden');
            }
            return;
        }

        const btnResi = e.target.closest('.btn-simpan-resi');
        if (btnResi) {
            saveTrackingResi(btnResi.dataset.orderId, btnResi);
            return;
        }

        const btnSignal = e.target.closest('.btn-simulasi-signal');
        if (btnSignal) {
            const targetStatus = btnSignal.dataset.signalStatus || 'delivered';
            triggerCourierSignal(btnSignal.dataset.orderId, targetStatus, btnSignal);
            return;
        }

        const btnCekBukti = e.target.closest('.btn-cek-bukti-ekspedisi');
        if (btnCekBukti && !btnCekBukti.disabled) {
            openModalForOrder(btnCekBukti.dataset.orderId);
        }
    }

    function handleModalBackdropClick(e) {
        if (e.target === modalPenyelesaian) toggleModal(false);
    }

    function handleKeyDown(e) {
        if (e.key === 'Escape' && !modalPenyelesaian.classList.contains('pointer-events-none')) {
            toggleModal(false);
        }
    }

    function handleCloseModalBtn() {
        toggleModal(false);
    }

    function handleIsiNominalPas() {
        const raw = modalSisaTagihan.dataset.raw || '0';
        inputNominal.value = raw;
    }

    function handleArrivalFileChange() {
        if (inputBuktiSampai.files && inputBuktiSampai.files[0]) {
            previewBuktiSampai.textContent = 'Foto terpilih: ' + inputBuktiSampai.files[0].name;
            previewBuktiSampai.classList.remove('hidden');
        } else {
            previewBuktiSampai.classList.add('hidden');
        }
    }

    function handlePaymentFileChange() {
        if (inputBuktiBayar.files && inputBuktiBayar.files[0]) {
            previewBuktiBayar.textContent = 'Foto terpilih: ' + inputBuktiBayar.files[0].name;
            previewBuktiBayar.classList.remove('hidden');
        } else {
            previewBuktiBayar.classList.add('hidden');
        }
    }

    function handleFormPenyelesaianSubmit(e) {
        e.preventDefault();
        if (!targetOrderContext) return;

        if (targetOrderContext.isInternal) {
            if (!inputBuktiSampai.files || !inputBuktiSampai.files[0]) {
                showToast('Bukti Sampai Wajib', 'Unggah foto bukti barang sudah sampai terlebih dahulu.', 'warning');
                return;
            }

            if (targetOrderContext.reqPelunasan && targetOrderContext.sisa > 0) {
                const inputVal = parseInt(inputNominal.value || '0', 10);
                if (inputVal !== targetOrderContext.sisa) {
                    showToast('Nominal Belum Sesuai', `Nominal yang diinput harus sama persis dengan sisa tagihan (${formatRp(targetOrderContext.sisa)}).`, 'warning');
                    return;
                }
                if (!inputBuktiBayar.files || !inputBuktiBayar.files[0]) {
                    showToast('Bukti Pelunasan Wajib', 'Unggah foto bukti pelunasan pembayaran COD/DP terlebih dahulu.', 'warning');
                    return;
                }
            }
        }

        const fd = targetOrderContext.isInternal ? new FormData(formPenyelesaian) : new FormData();
        executeCompleteOrder(targetOrderContext.id, fd, btnSubmitPenyelesaian);
    }

    function handlePointerDown(e) {
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
            maxDist: Math.max(1, track.offsetWidth - thumb.offsetWidth - 8),
            currentDist: 0,
            completed: false
        };

        thumb.style.transition = 'none';
        fill.style.transition = 'none';
    }

    function handlePointerMove(e) {
        if (!activeSlider || activeSlider.completed) return;

        let deltaX = e.clientX - activeSlider.startX;
        if (deltaX < 0) deltaX = 0;
        if (deltaX > activeSlider.maxDist) deltaX = activeSlider.maxDist;

        const progress = deltaX / activeSlider.maxDist;
        activeSlider.currentDist = deltaX;
        activeSlider.thumb.style.transform = `translateX(${deltaX}px)`;
        activeSlider.fill.style.transform = `scaleX(${progress})`;
        activeSlider.text.style.opacity = String(Math.max(0, 1 - progress));

        if (deltaX >= activeSlider.maxDist) {
            activeSlider.completed = true;
            openModalForOrder(activeSlider.track.dataset.orderId);
        }
    }

    function handlePointerUp() {
        if (!activeSlider) return;

        if (!activeSlider.completed) {
            activeSlider.thumb.style.transition = 'transform 0.2s ease-out';
            activeSlider.fill.style.transition = 'transform 0.2s ease-out';
            activeSlider.thumb.style.transform = 'translateX(0px)';
            activeSlider.fill.style.transform = 'scaleX(0)';
            activeSlider.text.style.opacity = '1';
        }
        activeSlider = null;
    }

    function showToast(title, message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none w-full max-w-md px-4';
            document.body.appendChild(container);
        }

        const iconMap = {
            success: 'ph-check-circle text-emerald-600 bg-emerald-100',
            warning: 'ph-warning text-amber-600 bg-amber-100',
            error: 'ph-x-circle text-red-600 bg-red-100'
        };
        const iconStyle = iconMap[type] || iconMap.success;

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border border-admin-accent shadow-xl bg-white transition-all transform -translate-y-4 opacity-0 duration-200';
        toast.innerHTML = `
            <div class="w-10 h-10 rounded-xl ${iconStyle} flex items-center justify-center shrink-0">
                <i class="ph ${iconStyle.split(' ')[0]} text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-black text-sm text-admin-textMain">${escapeHtml(title)}</h4>
                <p class="text-xs font-bold text-admin-textMuted mt-0.5 leading-relaxed">${escapeHtml(message)}</p>
            </div>
        `;
        container.appendChild(toast);

        registerTimeout(() => {
            toast.classList.remove('-translate-y-4', 'opacity-0');
        }, 20);

        registerTimeout(() => {
            toast.classList.add('-translate-y-4', 'opacity-0');
            registerTimeout(() => toast.remove(), 220);
        }, 4000);
    }

    function mount() {
        tabBtns.forEach((btn) => btn.addEventListener('click', handleTabClick));
        document.addEventListener('click', handleDocumentClick);
        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('pointerdown', handlePointerDown);
        document.addEventListener('pointermove', handlePointerMove);
        document.addEventListener('pointerup', handlePointerUp);

        if (btnRefresh) btnRefresh.addEventListener('click', fetchDeliveryData);
        if (modalPenyelesaian) modalPenyelesaian.addEventListener('click', handleModalBackdropClick);
        if (btnCloseModal) btnCloseModal.addEventListener('click', handleCloseModalBtn);
        if (btnBatalModal) btnBatalModal.addEventListener('click', handleCloseModalBtn);
        if (btnIsiPas) btnIsiPas.addEventListener('click', handleIsiNominalPas);
        if (inputBuktiSampai) inputBuktiSampai.addEventListener('change', handleArrivalFileChange);
        if (inputBuktiBayar) inputBuktiBayar.addEventListener('change', handlePaymentFileChange);
        if (formPenyelesaian) formPenyelesaian.addEventListener('submit', handleFormPenyelesaianSubmit);

        window.addEventListener('beforeunload', teardown);

        fetchDeliveryData();
    }

    function teardown() {
        if (fetchAbortController) fetchAbortController.abort();
        if (actionAbortController) actionAbortController.abort();
        clearAllTimers();

        tabBtns.forEach((btn) => btn.removeEventListener('click', handleTabClick));
        document.removeEventListener('click', handleDocumentClick);
        document.removeEventListener('keydown', handleKeyDown);
        document.removeEventListener('pointerdown', handlePointerDown);
        document.removeEventListener('pointermove', handlePointerMove);
        document.removeEventListener('pointerup', handlePointerUp);

        if (btnRefresh) btnRefresh.removeEventListener('click', fetchDeliveryData);
        if (modalPenyelesaian) modalPenyelesaian.removeEventListener('click', handleModalBackdropClick);
        if (btnCloseModal) btnCloseModal.removeEventListener('click', handleCloseModalBtn);
        if (btnBatalModal) btnBatalModal.removeEventListener('click', handleCloseModalBtn);
        if (btnIsiPas) btnIsiPas.removeEventListener('click', handleIsiNominalPas);
        if (inputBuktiSampai) inputBuktiSampai.removeEventListener('change', handleArrivalFileChange);
        if (inputBuktiBayar) inputBuktiBayar.removeEventListener('change', handlePaymentFileChange);
        if (formPenyelesaian) formPenyelesaian.removeEventListener('submit', handleFormPenyelesaianSubmit);

        window.removeEventListener('beforeunload', teardown);
    }

    mount();
})();
</script>
@endpush