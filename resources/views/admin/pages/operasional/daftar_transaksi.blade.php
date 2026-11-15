@extends('admin.admin_master')

@section('title', 'Daftar Transaksi - Admin Raya Kitchen')
@section('header_title', 'Daftar Transaksi')

@section('css')
<style>
    :root {
        /* 60-30-10 Claymorphism Token System */
        --clay-bg-base: #F5F2F8;
        --clay-surface: #FFFFFF;
        --clay-surface-alt: #FAF7FC;
        --clay-primary: #9368B7;
        --clay-accent-border: #E4CCFF;
        --clay-text-main: #2D2A32;
        --clay-text-muted: #716E77;

        --clay-shadow-card:
            inset 0 -3px 6px rgba(147, 104, 183, 0.1),
            inset 0 3px 6px rgba(255, 255, 255, 0.95),
            0 10px 24px -6px rgba(147, 104, 183, 0.12);
        --clay-shadow-inset:
            inset 0 3px 6px rgba(147, 104, 183, 0.16),
            inset 0 -2px 4px rgba(255, 255, 255, 0.85);

        /* Fluid Typography Tokens */
        --fs-title: clamp(1.125rem, 1.2vw + 0.85rem, 1.45rem);
        --fs-total: clamp(1.15rem, 1.3vw + 0.85rem, 1.5rem);
        --fs-body: clamp(0.8125rem, 0.25vw + 0.75rem, 0.9375rem);
    }

    .clay-panel {
        background-color: var(--clay-surface);
        box-shadow: var(--clay-shadow-card);
        border: 2px solid #FFFFFF;
    }

    .clay-inset {
        background-color: var(--clay-bg-base);
        box-shadow: var(--clay-shadow-inset);
        border: 1px solid rgba(255, 255, 255, 0.85);
    }

    .clay-btn {
        min-height: 44px;
        min-width: 44px;
        transition: transform 0.18s cubic-bezier(0.2, 0, 0, 1), opacity 0.18s ease;
        will-change: transform, opacity;
    }
    .clay-btn:hover:not(:disabled) {
        transform: translateY(-2px);
    }
    .clay-btn:active:not(:disabled) {
        transform: scale(0.96);
    }
    .clay-btn:focus-visible {
        outline: 3px solid var(--clay-primary);
        outline-offset: 2px;
    }
    .clay-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .order-card {
        cursor: pointer;
        border: 2px solid transparent;
        background-color: var(--clay-surface);
        box-shadow: 0 2px 8px rgba(147, 104, 183, 0.06);
        transition: transform 0.18s cubic-bezier(0.2, 0, 0, 1), opacity 0.18s ease;
        will-change: transform, opacity;
    }
    .order-card:hover {
        transform: translateY(-2px);
        background-color: var(--clay-surface-alt);
        border-color: var(--clay-accent-border);
    }
    .order-card:active {
        transform: scale(0.98);
    }
    .order-card:focus-visible {
        outline: 3px solid var(--clay-primary);
        outline-offset: 2px;
    }
    .order-card.active {
        background-color: var(--clay-bg-base);
        border-color: var(--clay-primary);
        box-shadow: inset 0 -2px 5px rgba(147, 104, 183, 0.15), 0 6px 16px rgba(147, 104, 183, 0.16);
    }

    .fluid-title { font-size: var(--fs-title); }
    .fluid-total { font-size: var(--fs-total); }
    .fluid-body { font-size: var(--fs-body); }

    .thin-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .thin-scroll::-webkit-scrollbar-track { background: transparent; }
    .thin-scroll::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }

    .drag-scroll {
        cursor: grab;
        overflow-x: auto;
        padding-bottom: 6px;
    }
    .drag-scroll:active { cursor: grabbing; }
    .drag-scroll::-webkit-scrollbar { height: 6px; }
    .drag-scroll::-webkit-scrollbar-track { background: #F3E8F0; border-radius: 9999px; }
    .drag-scroll::-webkit-scrollbar-thumb { background: #B38CDA; border-radius: 9999px; }

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
<!-- Contextual Toast Notification -->
<div id="trx-toast" role="status" aria-live="polite" class="fixed top-6 right-6 z-[210] hidden items-center gap-3 px-5 py-3.5 rounded-2xl bg-white border-2 border-purple-100 shadow-2xl transition-all duration-200 opacity-0 translate-y-[-10px] max-w-sm">
    <div id="trx-toast-icon" class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
        <i class="ph ph-check-circle text-xl"></i>
    </div>
    <div class="min-w-0 flex-1">
        <p id="trx-toast-title" class="text-xs font-black text-gray-900">Informasi</p>
        <p id="trx-toast-msg" class="text-xs font-semibold text-gray-600 truncate">Berhasil diperbarui.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 lg:h-[calc(100dvh-140px)] min-h-[650px] max-w-full overflow-x-hidden">

    <!-- LEFT PANEL: LIST PESANAN (Span 5 / XL 4) -->
    <section id="left-panel" aria-label="Daftar Transaksi" class="lg:col-span-5 xl:col-span-4 clay-panel rounded-[2rem] flex flex-col overflow-hidden h-[calc(100dvh-150px)] lg:h-full">

        <!-- Search & Filter Area -->
        <div class="p-4 sm:p-5 border-b border-purple-100/80 bg-[#FAF8FC] shrink-0 space-y-3">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-textMuted pointer-events-none"></i>
                    <input type="search" id="search-order" autocomplete="off" placeholder="Cari No. Order, Nama, WA, Resi..." class="w-full min-h-[44px] pl-11 pr-4 py-2 clay-inset rounded-xl text-xs sm:text-sm font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg">
                </div>
                <button type="button" id="btn-refresh-orders" title="Muat ulang transaksi" aria-label="Muat ulang daftar transaksi" class="clay-btn w-11 h-11 rounded-xl bg-white border border-purple-100 text-admin-sideBg flex items-center justify-center shadow-sm shrink-0">
                    <i id="icon-refresh-orders" class="ph ph-arrows-clockwise text-lg font-bold"></i>
                </button>
            </div>

            <!-- Sub-Filter Cepat: Status Bayar & Channel -->
            <div class="grid grid-cols-2 gap-2">
                <select id="filter-payment" aria-label="Filter Status Pembayaran" class="min-h-[38px] px-3 py-1.5 bg-white border border-purple-100 rounded-xl text-[11px] font-extrabold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg">
                    <option value="all">Semua Pembayaran</option>
                    <option value="paid_full">Lunas (Paid Full)</option>
                    <option value="paid_dp">DP 30% (Paid DP)</option>
                    <option value="unpaid">Belum Bayar (Unpaid)</option>
                    <option value="failed">Gagal / Batal</option>
                </select>
                <select id="filter-source" aria-label="Filter Sumber Pesanan" class="min-h-[38px] px-3 py-1.5 bg-white border border-purple-100 rounded-xl text-[11px] font-extrabold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg">
                    <option value="all">Semua Channel</option>
                    <option value="web">Website</option>
                    <option value="whatsapp">WhatsApp Bot</option>
                </select>
            </div>

            <!-- Filter Chips Status Pesanan (Draggable & Scrollable) -->
            <div class="flex flex-nowrap gap-2 drag-scroll select-none" id="filter-chips" role="tablist">
                <button type="button" class="chip-filter clay-btn active shrink-0 px-3.5 py-1.5 bg-admin-sideBg text-white rounded-xl text-xs font-black shadow-sm" data-status="all">
                    Semua (<span id="count-all">0</span>)
                </button>
                <button type="button" class="chip-filter clay-btn shrink-0 px-3.5 py-1.5 bg-white border border-admin-border text-admin-textMuted rounded-xl text-xs font-black" data-status="pending">
                    Menunggu (<span id="count-pending">0</span>)
                </button>
                <button type="button" class="chip-filter clay-btn shrink-0 px-3.5 py-1.5 bg-white border border-admin-border text-blue-600 rounded-xl text-xs font-black" data-status="diproses">
                    Diproses (<span id="count-diproses">0</span>)
                </button>
                <button type="button" class="chip-filter clay-btn shrink-0 px-3.5 py-1.5 bg-white border border-admin-border text-amber-600 rounded-xl text-xs font-black" data-status="siap_diambil">
                    Siap Kirim (<span id="count-siap">0</span>)
                </button>
                <button type="button" class="chip-filter clay-btn shrink-0 px-3.5 py-1.5 bg-white border border-admin-border text-emerald-600 rounded-xl text-xs font-black" data-status="selesai">
                    Selesai (<span id="count-selesai">0</span>)
                </button>
                <button type="button" class="chip-filter clay-btn shrink-0 px-3.5 py-1.5 bg-white border border-admin-border text-rose-600 rounded-xl text-xs font-black" data-status="dibatalkan">
                    Dibatalkan (<span id="count-batal">0</span>)
                </button>
            </div>
        </div>

        <!-- Skeleton Loader List -->
        <div id="list-skeleton" class="flex-1 overflow-y-auto p-3.5 space-y-2.5">
            @for ($i = 0; $i < 5; $i++)
            <div class="p-4 rounded-2xl bg-gray-100/80 border border-white animate-pulse space-y-2.5">
                <div class="flex justify-between items-center">
                    <div class="h-4 bg-gray-200 rounded w-32"></div>
                    <div class="h-5 bg-gray-200 rounded-lg w-16"></div>
                </div>
                <div class="flex justify-between items-end">
                    <div class="space-y-1.5">
                        <div class="h-3.5 bg-gray-200 rounded w-40"></div>
                        <div class="h-3 bg-gray-200 rounded w-28"></div>
                    </div>
                    <div class="h-4 bg-gray-200 rounded w-20"></div>
                </div>
            </div>
            @endfor
        </div>

        <!-- List Scrollable -->
        <div class="hidden flex-1 overflow-y-auto thin-scroll p-3.5 space-y-2.5 relative" id="order-list"></div>
    </section>

    <!-- RIGHT PANEL: ORDER DETAIL (Span 7 / XL 8) -->
    <section id="right-panel" aria-label="Rincian Transaksi" class="hidden lg:flex lg:col-span-7 xl:col-span-8 clay-panel rounded-[2rem] flex-col relative overflow-hidden h-[calc(100dvh-150px)] lg:h-full">

        <!-- Empty State -->
        <div id="detail-empty" class="absolute inset-0 bg-white z-20 flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 clay-inset rounded-3xl flex items-center justify-center mb-5">
                <i class="ph ph-receipt text-4xl text-admin-sideBg"></i>
            </div>
            <h3 class="fluid-title font-black text-admin-textMain mb-2">Rincian Transaksi</h3>
            <p class="fluid-body font-medium text-admin-textMuted max-w-sm leading-relaxed">
                Pilih salah satu transaksi di daftar sebelah kiri untuk memeriksa rincian item, jadwal kurir, status pembayaran, dan bukti pengiriman.
            </p>
        </div>

        <!-- Detail Konten -->
        <div id="detail-content" class="hidden flex-col h-full z-10 relative">

            <!-- Header Detail -->
            <div class="p-4 sm:p-6 border-b border-purple-100/80 flex flex-wrap justify-between items-center gap-3 bg-white shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" id="btn-back-mobile" aria-label="Kembali ke daftar" class="lg:hidden clay-btn w-11 h-11 flex items-center justify-center clay-inset text-admin-textMain rounded-xl shrink-0">
                        <i class="ph ph-arrow-left text-lg font-bold"></i>
                    </button>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5 mb-1">
                            <h2 class="fluid-title font-black text-admin-textMain tracking-tight truncate" id="det-order-id">#RK-0000</h2>
                            <div id="det-status-badge"></div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold text-admin-textMuted">
                            <span class="inline-flex items-center gap-1">
                                <i class="ph ph-globe text-admin-sideBg"></i>
                                Sumber: <strong id="det-source" class="uppercase text-admin-textMain">WEB</strong>
                            </span>
                            <span>•</span>
                            <span class="inline-flex items-center gap-1">
                                <i class="ph ph-calendar-blank text-admin-sideBg"></i>
                                <span id="det-created-at">-</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a id="btn-open-nota" href="#" target="_blank" rel="noopener noreferrer" class="clay-btn px-4 py-2.5 flex items-center justify-center gap-2 bg-admin-bg border border-purple-200 text-admin-sideBg rounded-xl hover:bg-admin-sideBg hover:text-white font-black text-xs shadow-sm" title="Buka & Cetak Nota Digital">
                        <i class="ph ph-printer text-lg font-bold"></i>
                        <span class="hidden sm:inline">Cetak Nota</span>
                    </a>
                </div>
            </div>

            <!-- Scrollable Info Area -->
            <div class="flex-1 overflow-y-auto thin-scroll p-4 sm:p-6 bg-[#FAF8FC] space-y-5">

                <!-- Banner Alasan Batal (Muncul jika dibatalkan) -->
                <div id="det-cancel-banner" class="hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white text-rose-600 flex items-center justify-center shrink-0 shadow-sm">
                        <i class="ph ph-x-circle text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-wider text-rose-500">Pesanan Dibatalkan</p>
                        <p id="det-cancel-reason" class="text-xs sm:text-sm font-bold text-rose-900 mt-0.5 break-words">-</p>
                        <p id="det-cancel-refund-info" class="text-[11px] font-extrabold text-rose-700 mt-1 hidden"></p>
                    </div>
                </div>

                <!-- Info Grid (Pelanggan & Pengiriman) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Pelanggan -->
                    <div class="clay-panel p-5 rounded-2xl flex flex-col justify-between">
                        <div>
                            <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                                <i class="ph ph-user text-base text-admin-sideBg"></i>
                                <span>Data Pelanggan</span>
                            </h4>
                            <div class="flex justify-between items-start gap-3">
                                <div class="min-w-0">
                                    <p class="font-black text-admin-textMain text-sm sm:text-base truncate" id="det-customer">-</p>
                                    <p class="text-xs font-bold text-admin-textMuted mt-0.5" id="det-phone">-</p>
                                </div>
                                <a href="#" id="det-wa-link" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp Pelanggan" class="clay-btn w-11 h-11 flex items-center justify-center bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-xl hover:bg-emerald-500 hover:text-white shrink-0" title="Chat WhatsApp">
                                    <i class="ph ph-whatsapp-logo text-xl font-bold"></i>
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                            <span class="font-bold text-admin-textMuted">Total Kuantitas</span>
                            <span id="det-qty-summary" class="font-black text-admin-sideBg bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-100">0 pcs</span>
                        </div>
                    </div>

                    <!-- Info Pengiriman / Kurir / Estimasi -->
                    <div class="clay-panel p-5 rounded-2xl flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2 gap-2">
                                <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest flex items-center gap-1.5">
                                    <i class="ph ph-truck text-base text-admin-sideBg"></i>
                                    <span>Pengiriman & Kurir</span>
                                </h4>
                                <span id="det-courier-badge" class="text-[10px] font-black px-2.5 py-0.5 rounded-lg bg-purple-50 text-admin-sideBg border border-purple-200 truncate">-</span>
                            </div>

                            <div class="space-y-2.5">
                                <div class="flex items-start gap-2">
                                    <i class="ph ph-map-pin text-admin-sideBg text-base mt-0.5 shrink-0"></i>
                                    <p class="text-xs font-semibold text-admin-textMain leading-relaxed break-words" id="det-address">-</p>
                                </div>

                                <div id="det-resi-box" class="hidden items-center justify-between gap-2 px-3 py-2 rounded-xl bg-indigo-50 border border-indigo-100 text-xs">
                                    <span class="font-bold text-indigo-700">No. Resi: <strong id="det-resi-num" class="font-mono font-black">-</strong></span>
                                    <button type="button" id="btn-copy-resi" class="clay-btn px-2.5 py-1 rounded-lg bg-white text-indigo-700 font-black text-[11px] border border-indigo-200">
                                        Salin
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                            <span id="det-schedule-label" class="text-[11px] font-bold text-admin-textMuted">Jadwal Kirim:</span>
                            <span id="det-deadline" class="text-xs font-black text-rose-600 text-right">-</span>
                        </div>
                    </div>
                </div>

                <!-- Rincian Produk -->
                <div class="clay-panel rounded-2xl overflow-hidden">
                    <div class="px-5 py-3.5 bg-[#FAF8FC] border-b border-purple-100/80 flex justify-between items-center">
                        <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest flex items-center gap-1.5">
                            <i class="ph ph-ExcludeSquare text-base text-admin-sideBg"></i>
                            <span>Rincian Item Roti</span>
                        </h4>
                    </div>
                    <ul class="divide-y divide-gray-100" id="det-items-list"></ul>
                </div>

                <!-- Grid Bukti Foto & Ringkasan Pembayaran -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">

                    <!-- Galeri Bukti Pengiriman / Pembayaran / Refund -->
                    <div id="det-proofs-panel" class="md:col-span-5 clay-panel p-5 rounded-2xl hidden">
                        <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                            <i class="ph ph-camera text-base text-admin-sideBg"></i>
                            <span>Dokumentasi Bukti</span>
                        </h4>
                        <div id="det-proofs-grid" class="grid grid-cols-2 gap-3"></div>
                    </div>

                    <!-- Ringkasan Pembayaran -->
                    <div id="det-payment-panel" class="md:col-span-12 clay-panel p-5 rounded-2xl ml-auto w-full">
                        <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                            <i class="ph ph-wallet text-base text-admin-sideBg"></i>
                            <span>Rincian Pembayaran</span>
                        </h4>

                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-admin-textMuted">Metode & Skema</span>
                                <span class="font-black text-admin-textMain uppercase" id="det-pay-method">-</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-admin-textMuted">Status Transaksi</span>
                                <span class="font-black uppercase" id="det-pay-status">-</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-admin-textMuted">Subtotal Produk</span>
                                <span class="font-bold text-admin-textMain" id="det-subtotal-items">Rp 0</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-admin-textMuted">Ongkos Kirim</span>
                                <span class="font-bold text-admin-textMain" id="det-shipping-cost">Rp 0</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-admin-textMuted">Dana Sudah Masuk</span>
                                <span class="font-extrabold text-emerald-600" id="det-pay-dp">Rp 0</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-admin-textMuted">Sisa Tagihan Belum Lunas</span>
                                <span class="font-extrabold text-rose-600" id="det-pay-rem">Rp 0</span>
                            </div>

                            <div class="h-px bg-gray-200 my-2"></div>

                            <div class="flex justify-between items-center pt-1">
                                <span class="font-black text-admin-textMain uppercase text-xs sm:text-sm">Total Pesanan</span>
                                <span class="fluid-total font-black text-admin-sideBg" id="det-total">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sticky Action Footer (Ergonomic Thumb-Zone) -->
            <div class="p-4 border-t border-purple-100/80 bg-white flex flex-wrap justify-between items-center gap-3 shrink-0">
                <div id="det-action-area" class="w-full flex flex-wrap justify-end items-center gap-2.5"></div>
            </div>

        </div>
    </section>
</div>

<!-- MODAL BATALKAN PESANAN OLEH ADMIN (Escape Hatches: X, Backdrop, ESC) -->
<div id="adminCancelModal" role="dialog" aria-modal="true" aria-labelledby="admin-cancel-title" class="fixed inset-0 z-[195] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-200">
    <div id="adminCancelDialog" class="clay-panel rounded-[2rem] max-w-md w-full p-6 sm:p-7 relative transform scale-95 transition-transform duration-200">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                    <i class="ph ph-warning-octagon text-2xl"></i>
                </div>
                <h3 id="admin-cancel-title" class="font-black text-base text-admin-textMain">Batalkan Pesanan</h3>
            </div>
            <button type="button" id="btn-close-cancel-x" aria-label="Tutup modal" class="clay-btn w-11 h-11 rounded-xl bg-gray-100 text-gray-500 hover:text-rose-600 flex items-center justify-center">
                <i class="ph ph-x text-xl font-bold"></i>
            </button>
        </div>

        <form id="form-admin-cancel" class="space-y-4">
            <p class="text-xs font-semibold text-admin-textMuted leading-relaxed">
                Anda akan membatalkan pesanan <strong id="cancel-modal-order-id" class="text-rose-600 font-black">#RK-000</strong>. Notifikasi pembatalan akan dikirimkan otomatis ke pelanggan.
            </p>

            <div>
                <label for="input-cancel-reason" class="block text-xs font-black text-admin-textMain mb-1.5">Alasan Pembatalan</label>
                <textarea id="input-cancel-reason" rows="2" required class="w-full p-3 clay-inset rounded-xl text-xs sm:text-sm font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-rose-500 resize-none" placeholder="Cth: Stok bahan baku varian ini sedang habis..."></textarea>
            </div>

            <div id="cancel-modal-refund-box" class="hidden p-3.5 rounded-xl bg-amber-50 border border-amber-200 space-y-1.5">
                <p class="text-[11px] font-black text-amber-800 flex items-center gap-1.5">
                    <i class="ph ph-info text-base shrink-0"></i>
                    <span>Pesanan ini sudah memiliki pembayaran masuk (DP / Lunas)</span>
                </p>
                <p class="text-[11px] font-semibold text-amber-700 leading-relaxed">
                    Sistem akan otomatis mengirimkan alasan pembatalan beserta instruksi kepada pembeli (via WhatsApp & Nota Web) untuk mengisi <strong>Nomor Rekening Pengembalian Dana</strong>. Pesanan ini akan langsung masuk ke menu <strong>Batal & Refund</strong>.
                </p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 pt-2">
                <button type="button" id="btn-cancel-modal-back" class="clay-btn px-5 py-3 rounded-xl bg-gray-100 text-gray-700 font-black text-xs sm:text-sm">
                    Kembali
                </button>
                <button type="submit" id="btn-submit-admin-cancel" class="clay-btn px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs sm:text-sm shadow-md flex items-center justify-center gap-2">
                    <i class="ph ph-trash text-lg"></i>
                    <span>Ya, Batalkan Pesanan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL ZOOM GAMBAR BUKTI (Escape Hatches: X, Backdrop, ESC) -->
<div id="trxProofZoomModal" role="dialog" aria-modal="true" aria-label="Preview Bukti Transaksi" class="fixed inset-0 z-[205] hidden items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-200">
    <div id="trxProofZoomDialog" class="bg-white rounded-[2rem] p-4 max-w-2xl w-full max-h-[90dvh] flex flex-col shadow-2xl transform scale-95 transition-transform duration-200">
        <div class="flex items-center justify-between pb-3 mb-2 border-b border-gray-100">
            <span id="trx-zoom-title" class="text-xs font-black uppercase tracking-wider text-admin-textMain">Bukti Transaksi</span>
            <button type="button" id="btn-close-trx-zoom" aria-label="Tutup preview" class="clay-btn w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i class="ph ph-x text-xl font-bold"></i>
            </button>
        </div>
        <div class="flex-1 overflow-auto flex items-center justify-center bg-slate-950/90 rounded-2xl p-2 min-h-[260px]">
            <img id="trx-zoom-image" src="" alt="Preview Bukti" class="max-h-[72dvh] w-auto object-contain rounded-xl">
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (() => {
        // =========================================================================
        // STATE & ZERO MEMORY LEAK REGISTRY
        // =========================================================================
        let masterOrders = [];
        let activeFilter = 'all';
        let activePaymentFilter = 'all';
        let activeSourceFilter = 'all';
        let searchQuery = '';
        let activeOrderNum = null;

        let fetchAbortController = null;
        let actionAbortController = null;
        const activeTimers = new Set();
        const boundListeners = [];

        // Drag-to-scroll state
        let isDraggingSlider = false;
        let dragStartX = 0;
        let dragScrollLeft = 0;

        const formatRupiah = (num) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(Number(num) || 0);

        function registerTimer(fn, delay) {
            const id = window.setTimeout(() => {
                activeTimers.delete(id);
                fn();
            }, delay);
            activeTimers.add(id);
            return id;
        }

        function bindEvent(el, type, fn, options = false) {
            if (!el) return;
            el.addEventListener(type, fn, options);
            boundListeners.push({ el, type, fn, options });
        }

        // =========================================================================
        // TOAST ENGINE
        // =========================================================================
        function showToast(title, message, isSuccess = true) {
            const toast = document.getElementById('trx-toast');
            const iconBox = document.getElementById('trx-toast-icon');
            const titleEl = document.getElementById('trx-toast-title');
            const msgEl = document.getElementById('trx-toast-msg');
            if (!toast || !iconBox || !titleEl || !msgEl) return;

            titleEl.textContent = title;
            msgEl.textContent = message;

            if (isSuccess) {
                iconBox.className = 'w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0';
                iconBox.innerHTML = '<i class="ph ph-check-circle text-xl"></i>';
            } else {
                iconBox.className = 'w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0';
                iconBox.innerHTML = '<i class="ph ph-warning-circle text-xl"></i>';
            }

            toast.classList.remove('hidden');
            toast.classList.add('flex');

            registerTimer(() => {
                toast.classList.remove('opacity-0', 'translate-y-[-10px]');
                toast.classList.add('opacity-100', 'translate-y-0');
            }, 15);

            registerTimer(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-[-10px]');
                registerTimer(() => {
                    toast.classList.remove('flex');
                    toast.classList.add('hidden');
                }, 200);
            }, 3200);
        }

        // =========================================================================
        // FETCH DATA TRANSAKSI DARI API CONTROLLER
        // =========================================================================
        async function fetchTransactions(preserveSelection = true) {
            if (fetchAbortController) {
                fetchAbortController.abort();
            }
            fetchAbortController = new AbortController();

            const skeleton = document.getElementById('list-skeleton');
            const listEl = document.getElementById('order-list');
            const refreshIcon = document.getElementById('icon-refresh-orders');

            if (refreshIcon) refreshIcon.classList.add('animate-spin');
            if (!preserveSelection && skeleton && listEl) {
                skeleton.classList.remove('hidden');
                listEl.classList.add('hidden');
            }

            try {
                const response = await fetch('/api/admin/daftar-transaksi', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: fetchAbortController.signal
                });

                const res = await response.json();
                if (response.ok && res.status === 'success' && res.data) {
                    masterOrders = Array.isArray(res.data.orders) ? res.data.orders : [];
                    renderList();

                    if (preserveSelection && activeOrderNum) {
                        const exists = masterOrders.some(o => o.order_number === activeOrderNum);
                        if (exists) {
                            showDetail(activeOrderNum);
                        }
                    }
                } else {
                    showToast('Gagal Memuat', res.message || 'Gagal memuat daftar transaksi.', false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showToast('Gangguan Jaringan', 'Tidak dapat terhubung ke server.', false);
                }
            } finally {
                if (skeleton) skeleton.classList.add('hidden');
                if (listEl) listEl.classList.remove('hidden');
                if (refreshIcon) refreshIcon.classList.remove('animate-spin');
            }
        }

        // =========================================================================
        // BADGE GENERATORS
        // =========================================================================
        function getStatusBadge(status, isSmall = false) {
            const sizeCls = isSmall ? 'px-2.5 py-0.5 text-[10px]' : 'px-3 py-1 text-[10px]';
            const base = `${sizeCls} rounded-lg font-black uppercase tracking-wider inline-flex items-center gap-1 `;

            switch (status) {
                case 'pending':
                    return `<span class="${base} bg-gray-100 text-gray-700 border border-gray-200"><i class="ph ph-hourglass-low"></i> Menunggu</span>`;
                case 'diproses':
                    return `<span class="${base} bg-blue-50 text-blue-700 border border-blue-200"><i class="ph ph-cooking-pot"></i> Diproses</span>`;
                case 'siap_diambil':
                    return `<span class="${base} bg-amber-50 text-amber-700 border border-amber-200"><i class="ph ph-package"></i> Siap Kirim</span>`;
                case 'selesai':
                    return `<span class="${base} bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="ph ph-check-circle"></i> Selesai</span>`;
                case 'dibatalkan':
                    return `<span class="${base} bg-rose-50 text-rose-700 border border-rose-200"><i class="ph ph-x-circle"></i> Batal</span>`;
                default:
                    return `<span class="${base} bg-gray-100 text-gray-600">${status}</span>`;
            }
        }

        function getPaymentBadge(payStatus) {
            switch (payStatus) {
                case 'paid_full':
                    return { text: 'LUNAS (PAID FULL)', cls: 'text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200' };
                case 'paid_dp':
                    return { text: 'DP 30% (PAID DP)', cls: 'text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-lg border border-amber-200' };
                case 'unpaid':
                    return { text: 'BELUM BAYAR', cls: 'text-gray-600 bg-gray-100 px-2.5 py-0.5 rounded-lg border border-gray-200' };
                default:
                    return { text: 'GAGAL / BATAL', cls: 'text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-lg border border-rose-200' };
            }
        }

        // =========================================================================
        // RENDER LIST & CHIPS COUNT
        // =========================================================================
        function renderList() {
            const listEl = document.getElementById('order-list');
            if (!listEl) return;

            const counts = { all: 0, pending: 0, diproses: 0, siap_diambil: 0, selesai: 0, dibatalkan: 0 };

            const filteredOrders = masterOrders.filter(o => {
                const haystack = `${o.order_number} ${o.recipient_name} ${o.recipient_whatsapp} ${o.tracking_number || ''} ${o.courier_name}`.toLowerCase();
                const matchSearch = haystack.includes(searchQuery);
                const matchPayment = activePaymentFilter === 'all' ? true : o.payment_status === activePaymentFilter;
                const matchSource = activeSourceFilter === 'all' ? true : o.order_source === activeSourceFilter;

                if (matchSearch && matchPayment && matchSource) {
                    counts.all++;
                    if (counts[o.status] !== undefined) {
                        counts[o.status]++;
                    }
                }

                const matchStatus = activeFilter === 'all' ? true : o.status === activeFilter;
                return matchSearch && matchPayment && matchSource && matchStatus;
            });

            document.getElementById('count-all').textContent = String(counts.all);
            document.getElementById('count-pending').textContent = String(counts.pending);
            document.getElementById('count-diproses').textContent = String(counts.diproses);
            document.getElementById('count-siap').textContent = String(counts.siap_diambil);
            document.getElementById('count-selesai').textContent = String(counts.selesai);
            document.getElementById('count-batal').textContent = String(counts.dibatalkan);

            if (filteredOrders.length === 0) {
                listEl.innerHTML = `
                    <div class="py-14 px-4 text-center flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-2xl clay-inset flex items-center justify-center text-admin-sideBg mb-3">
                            <i class="ph ph-clipboard-text text-3xl"></i>
                        </div>
                        <p class="font-black text-admin-textMain text-sm mb-1">Tidak Ada Data Transaksi</p>
                        <p class="text-xs font-medium text-admin-textMuted max-w-[230px]">Coba ubah kata kunci pencarian atau filter status di atas.</p>
                    </div>
                `;
                return;
            }

            listEl.innerHTML = filteredOrders.map(o => {
                const activeClass = activeOrderNum === o.order_number ? 'active' : '';
                const badge = getStatusBadge(o.status, true);
                const sourceIcon = o.order_source === 'whatsapp'
                    ? '<i class="ph ph-whatsapp-logo text-emerald-600" title="WhatsApp Bot"></i>'
                    : '<i class="ph ph-globe text-admin-sideBg" title="Website"></i>';

                const payDotColor = o.payment_status === 'paid_full'
                    ? 'bg-emerald-500'
                    : (o.payment_status === 'paid_dp' ? 'bg-amber-500' : 'bg-rose-500');

                return `
                    <button type="button" class="order-card w-full text-left p-3.5 sm:p-4 rounded-2xl focus:outline-none ${activeClass}" data-id="${o.order_number}">
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <span class="font-black text-admin-textMain text-sm truncate flex items-center gap-1.5">
                                ${sourceIcon}
                                <span>#${o.order_number}</span>
                            </span>
                            ${badge}
                        </div>
                        <div class="flex justify-between items-end gap-2">
                            <div class="min-w-0">
                                <p class="font-bold text-admin-textMain text-xs truncate flex items-center gap-1.5 mb-0.5">
                                    <i class="ph ph-user text-admin-sideBg"></i>
                                    <span class="truncate">${o.recipient_name}</span>
                                </p>
                                <p class="font-semibold text-admin-textMuted text-[11px] truncate flex items-center gap-1">
                                    <i class="ph ph-clock"></i>
                                    <span class="truncate">${o.schedule_text}</span>
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-black text-admin-sideBg text-xs sm:text-sm block">${formatRupiah(o.total_amount)}</span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-admin-textMuted">
                                    <span class="w-1.5 h-1.5 rounded-full ${payDotColor}"></span>
                                    ${o.payment_scheme_label}
                                </span>
                            </div>
                        </div>
                    </button>
                `;
            }).join('');
        }

        // =========================================================================
        // RENDER DETAIL DI PANEL KANAN
        // =========================================================================
        function showDetail(orderNum) {
            activeOrderNum = orderNum;
            renderList();

            const order = masterOrders.find(o => o.order_number === orderNum);
            if (!order) return;

            const leftPanel = document.getElementById('left-panel');
            const rightPanel = document.getElementById('right-panel');
            const detailEmpty = document.getElementById('detail-empty');
            const detailContent = document.getElementById('detail-content');

            if (window.innerWidth < 1024 && leftPanel && rightPanel) {
                leftPanel.classList.add('hidden');
                rightPanel.classList.remove('hidden');
                rightPanel.classList.add('flex');
            }

            if (detailEmpty) detailEmpty.classList.add('hidden');
            if (detailContent) {
                detailContent.classList.remove('hidden');
                detailContent.classList.add('flex');
            }

            // Header
            document.getElementById('det-order-id').textContent = '#' + order.order_number;
            document.getElementById('det-status-badge').innerHTML = getStatusBadge(order.status, false);
            document.getElementById('det-source').textContent = order.order_source;
            document.getElementById('det-created-at').textContent = order.created_at_formatted;
            document.getElementById('btn-open-nota').href = order.nota_url;

            // Banner Pembatalan
            const cancelBanner = document.getElementById('det-cancel-banner');
            const cancelReason = document.getElementById('det-cancel-reason');
            const cancelRefundInfo = document.getElementById('det-cancel-refund-info');

            if (order.status === 'dibatalkan') {
                cancelBanner.classList.remove('hidden');
                cancelBanner.classList.add('flex');
                cancelReason.textContent = order.cancellation_reason || 'Dibatalkan.';
                if (order.refund_status && order.refund_status !== 'none') {
                    cancelRefundInfo.classList.remove('hidden');
                    cancelRefundInfo.textContent = order.refund_status === 'refunded'
                        ? `Status Refund: SELESAI (${order.refund_account_number || '-'})`
                        : `Status Refund: MENUNGGU PROSES ADMIN (${order.refund_account_number || '-'})`;
                } else {
                    cancelRefundInfo.classList.add('hidden');
                }
            } else {
                cancelBanner.classList.remove('flex');
                cancelBanner.classList.add('hidden');
            }

            // Data Pelanggan & Pengiriman
            document.getElementById('det-customer').textContent = order.recipient_name;
            document.getElementById('det-phone').textContent = order.recipient_whatsapp;
            document.getElementById('det-qty-summary').textContent = `${order.items_count} pcs`;

            const waLink = document.getElementById('det-wa-link');
            if (order.wa_chat_url) {
                waLink.href = order.wa_chat_url;
                waLink.classList.remove('hidden');
            } else {
                waLink.classList.add('hidden');
            }

            document.getElementById('det-courier-badge').textContent = `${order.courier_name} (${order.shipping_service})`;
            document.getElementById('det-address').textContent = order.shipping_address;
            document.getElementById('det-schedule-label').textContent = order.schedule_label + ':';
            document.getElementById('det-deadline').textContent = order.schedule_text;

            const resiBox = document.getElementById('det-resi-box');
            if (!order.is_internal && order.tracking_number) {
                document.getElementById('det-resi-num').textContent = order.tracking_number;
                resiBox.classList.remove('hidden');
                resiBox.classList.add('flex');
            } else {
                resiBox.classList.remove('flex');
                resiBox.classList.add('hidden');
            }

            // Rincian Item
            const itemsListEl = document.getElementById('det-items-list');
            itemsListEl.innerHTML = order.items.map(i => `
                <li class="px-5 py-3.5 flex justify-between items-center gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 clay-inset text-admin-sideBg rounded-xl flex items-center justify-center font-black text-xs shrink-0">${i.quantity}x</div>
                        <div class="min-w-0">
                            <p class="font-bold text-admin-textMain text-xs sm:text-sm truncate">${i.name}</p>
                            <p class="text-[11px] font-semibold text-admin-textMuted">@ ${formatRupiah(i.price_at_order)}</p>
                        </div>
                    </div>
                    <span class="font-black text-admin-textMain text-xs sm:text-sm shrink-0">${formatRupiah(i.subtotal)}</span>
                </li>
            `).join('');

            // Dokumentasi Bukti (Jika ada POD / Bukti Pelunasan / Bukti Refund)
            const proofsPanel = document.getElementById('det-proofs-panel');
            const proofsGrid = document.getElementById('det-proofs-grid');
            const payPanel = document.getElementById('det-payment-panel');

            const proofItems = [];
            if (order.arrival_proof_url) {
                proofItems.push({ label: 'Bukti Sampai (POD)', url: order.arrival_proof_url });
            }
            if (order.payment_proof_url) {
                proofItems.push({ label: 'Bukti Pelunasan', url: order.payment_proof_url });
            }
            if (order.refund_proof_url) {
                proofItems.push({ label: 'Bukti Refund', url: order.refund_proof_url });
            }

            if (proofItems.length > 0) {
                proofsPanel.classList.remove('hidden');
                payPanel.className = 'md:col-span-7 clay-panel p-5 rounded-2xl ml-auto w-full';
                proofsGrid.innerHTML = proofItems.map(p => `
                    <button type="button" class="btn-preview-proof clay-btn group relative rounded-xl overflow-hidden border border-purple-100 bg-gray-50 aspect-square flex flex-col" data-url="${p.url}" data-title="${p.label} (#${order.order_number})">
                        <img src="${p.url}" alt="${p.label}" class="w-full h-full object-cover">
                        <span class="absolute inset-x-0 bottom-0 bg-slate-900/75 text-white text-[10px] font-bold py-1 px-1.5 truncate">${p.label}</span>
                    </button>
                `).join('');
            } else {
                proofsPanel.classList.add('hidden');
                payPanel.className = 'md:col-span-12 clay-panel p-5 rounded-2xl ml-auto w-full';
                proofsGrid.innerHTML = '';
            }

            // Keuangan
            document.getElementById('det-pay-method').textContent = `${order.payment_method.toUpperCase()} (${order.payment_scheme_label})`;
            const payBadge = getPaymentBadge(order.payment_status);
            const payStatusEl = document.getElementById('det-pay-status');
            payStatusEl.className = `font-black uppercase text-[11px] ${payBadge.cls}`;
            payStatusEl.textContent = payBadge.text;

            document.getElementById('det-subtotal-items').textContent = formatRupiah(order.subtotal_items);
            document.getElementById('det-shipping-cost').textContent = order.shipping_cost > 0 ? formatRupiah(order.shipping_cost) : 'GRATIS';
            document.getElementById('det-pay-dp').textContent = formatRupiah(order.paid_amount);
            document.getElementById('det-pay-rem').textContent = formatRupiah(order.remaining_amount);
            document.getElementById('det-total').textContent = formatRupiah(order.total_amount);

            // Action Buttons
            renderActionFooter(order);
        }

        function renderActionFooter(order) {
            const actionArea = document.getElementById('det-action-area');
            if (!actionArea) return;

            const isUnpaid = (order.payment_status === 'unpaid' || order.payment_status === 'failed');
            let buttons = '';

            if (order.status === 'pending') {
                buttons += `
                    <button type="button" id="btn-trigger-cancel-modal" class="clay-btn px-4 py-2.5 border-2 border-rose-200 bg-rose-50 text-rose-600 rounded-xl font-black text-xs flex items-center gap-1.5">
                        <i class="ph ph-x-circle text-base"></i>
                        <span>Batalkan Pesanan</span>
                    </button>
                `;
                if (!isUnpaid) {
                    buttons += `
                        <a href="/admin/pantauan_dapur" class="clay-btn px-4 py-2.5 bg-purple-50 border border-purple-200 text-admin-sideBg rounded-xl font-black text-xs inline-flex items-center gap-1.5">
                            <i class="ph ph-cooking-pot text-base"></i>
                            <span>Buka Dapur (KDS)</span>
                        </a>
                        <button type="button" data-action="diproses" class="btn-status-action clay-btn px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black text-xs sm:text-sm shadow-sm flex items-center gap-1.5">
                            <i class="ph ph-play-circle text-lg"></i>
                            <span>Mulai Proses Dapur</span>
                        </button>
                    `;
                } else {
                    buttons += `
                        <span class="text-xs font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-3.5 py-2.5 rounded-xl inline-flex items-center gap-1.5">
                            <i class="ph ph-clock-countdown text-base"></i>
                            <span>Menunggu Pembayaran QRIS Pelanggan</span>
                        </span>
                    `;
                }
            } else if (order.status === 'diproses') {
                buttons += `
                    <a href="/admin/pantauan_dapur" class="clay-btn px-4 py-2.5 bg-purple-50 border border-purple-200 text-admin-sideBg rounded-xl font-black text-xs inline-flex items-center gap-1.5">
                        <i class="ph ph-cooking-pot text-base"></i>
                        <span>Pantauan Dapur</span>
                    </a>
                    <button type="button" data-action="siap_diambil" class="btn-status-action clay-btn px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black text-xs sm:text-sm shadow-sm flex items-center gap-1.5">
                        <i class="ph ph-package text-lg"></i>
                        <span>Tandai Siap Dikirim / Selesai Masak</span>
                    </button>
                `;
            } else if (order.status === 'siap_diambil') {
                if (order.payment_status === 'paid_dp' && order.remaining_amount > 0) {
                    buttons += `
                        <button type="button" data-action="remind_pelunasan" class="btn-status-action clay-btn px-4 py-2.5 bg-amber-50 border-2 border-amber-300 text-amber-800 rounded-xl font-black text-xs flex items-center gap-1.5">
                            <i class="ph ph-bell-ringing text-base"></i>
                            <span>Kirim Pengingat Pelunasan WA</span>
                        </button>
                    `;
                }
                buttons += `
                    <a href="/admin/kelola_pengiriman" class="clay-btn px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs sm:text-sm shadow-sm inline-flex items-center gap-2">
                        <i class="ph ph-truck text-lg"></i>
                        <span>Proses & Verifikasi di Kelola Pengiriman</span>
                    </a>
                `;
            } else if (order.status === 'dibatalkan' && order.refund_status === 'pending') {
                buttons += `
                    <a href="/admin/refund_pembatalan" class="clay-btn px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-xs sm:text-sm shadow-sm inline-flex items-center gap-2">
                        <i class="ph ph-arrow-u-up-left text-lg"></i>
                        <span>Proses Pengembalian Dana (Refund)</span>
                    </a>
                `;
            } else {
                buttons += `
                    <span class="text-xs font-bold text-admin-textMuted py-2.5 px-4 clay-inset rounded-xl inline-flex items-center gap-1.5">
                        <i class="ph ph-check-circle text-emerald-600 text-base"></i>
                        <span>Transaksi telah ditutup / selesai</span>
                    </span>
                `;
            }

            actionArea.innerHTML = buttons;
        }

        // =========================================================================
        // API ACTION: UPDATE STATUS / REMIND PELUNASAN / CANCEL
        // =========================================================================
        async function executeOrderAction(action, extraPayload = {}, triggerBtn = null) {
            if (!activeOrderNum) return;

            if (actionAbortController) {
                actionAbortController.abort();
            }
            actionAbortController = new AbortController();

            const oriHtml = triggerBtn ? triggerBtn.innerHTML : '';
            if (triggerBtn) {
                triggerBtn.disabled = true;
                triggerBtn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-base"></i><span>Memproses...</span>';
            }

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            if (csrfMeta) {
                headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
            }

            try {
                const response = await fetch(`/api/admin/daftar-transaksi/${encodeURIComponent(activeOrderNum)}/status`, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({ action, ...extraPayload }),
                    signal: actionAbortController.signal
                });

                const res = await response.json();
                if (response.ok && res.status === 'success') {
                    closeCancelModal();
                    showToast('Berhasil', res.message, true);

                    const idx = masterOrders.findIndex(o => o.order_number === activeOrderNum);
                    if (idx !== -1 && res.data?.order) {
                        masterOrders[idx] = res.data.order;
                    }

                    if (typeof res.data?.pending_refund_count !== 'undefined') {
                        window.dispatchEvent(new CustomEvent('refund:updated', {
                            detail: { pending_count: res.data.pending_refund_count }
                        }));
                    }

                    renderList();
                    showDetail(activeOrderNum);
                } else {
                    showToast('Gagal Memproses', res.message || 'Terjadi kendala saat memperbarui transaksi.', false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showToast('Kesalahan Koneksi', 'Gagal menghubungi server.', false);
                }
            } finally {
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.innerHTML = oriHtml;
                }
            }
        }

        // =========================================================================
        // MODAL HANDLERS
        // =========================================================================
        function openCancelModal() {
            const order = masterOrders.find(o => o.order_number === activeOrderNum);
            if (!order) return;

            document.getElementById('cancel-modal-order-id').textContent = '#' + order.order_number;
            document.getElementById('input-cancel-reason').value = '';

            const refundBox = document.getElementById('cancel-modal-refund-box');
            const isPaid = (order.payment_status === 'paid_dp' || order.payment_status === 'paid_full');
            if (isPaid) {
                refundBox.classList.remove('hidden');
            } else {
                refundBox.classList.add('hidden');
            }

            const modal = document.getElementById('adminCancelModal');
            const dialog = document.getElementById('adminCancelDialog');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            registerTimer(() => {
                modal.classList.remove('opacity-0');
                dialog.classList.remove('scale-95');
            }, 15);
        }

        function closeCancelModal() {
            const modal = document.getElementById('adminCancelModal');
            const dialog = document.getElementById('adminCancelDialog');
            if (!modal || !dialog || modal.classList.contains('hidden')) return;

            modal.classList.add('opacity-0');
            dialog.classList.add('scale-95');
            registerTimer(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }

        function openProofZoomModal(url, title) {
            if (!url) return;
            const modal = document.getElementById('trxProofZoomModal');
            const dialog = document.getElementById('trxProofZoomDialog');
            document.getElementById('trx-zoom-image').src = url;
            document.getElementById('trx-zoom-title').textContent = title || 'Bukti Transaksi';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            registerTimer(() => {
                modal.classList.remove('opacity-0');
                dialog.classList.remove('scale-95');
            }, 15);
        }

        function closeProofZoomModal() {
            const modal = document.getElementById('trxProofZoomModal');
            const dialog = document.getElementById('trxProofZoomDialog');
            if (!modal || !dialog || modal.classList.contains('hidden')) return;

            modal.classList.add('opacity-0');
            dialog.classList.add('scale-95');
            registerTimer(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }

        // =========================================================================
        // NAMED EVENT LISTENERS
        // =========================================================================
        function onOrderListClick(e) {
            const card = e.target.closest('.order-card');
            if (!card) return;
            const orderId = card.getAttribute('data-id');
            if (orderId) showDetail(orderId);
        }

        function onSearchInput(e) {
            searchQuery = (e.target.value || '').toLowerCase().trim();
            renderList();
        }

        function onPaymentFilterChange(e) {
            activePaymentFilter = e.target.value || 'all';
            renderList();
        }

        function onSourceFilterChange(e) {
            activeSourceFilter = e.target.value || 'all';
            renderList();
        }

        function onFilterChipsClick(e) {
            const chip = e.target.closest('.chip-filter');
            if (!chip) return;

            activeFilter = chip.getAttribute('data-status') || 'all';

            document.querySelectorAll('.chip-filter').forEach(btn => {
                const st = btn.getAttribute('data-status');
                btn.className = 'chip-filter clay-btn shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-black ';
                if (st === activeFilter) {
                    btn.className += 'bg-admin-sideBg text-white shadow-sm';
                } else if (st === 'diproses') {
                    btn.className += 'bg-white border border-admin-border text-blue-600';
                } else if (st === 'siap_diambil') {
                    btn.className += 'bg-white border border-admin-border text-amber-600';
                } else if (st === 'selesai') {
                    btn.className += 'bg-white border border-admin-border text-emerald-600';
                } else if (st === 'dibatalkan') {
                    btn.className += 'bg-white border border-admin-border text-rose-600';
                } else {
                    btn.className += 'bg-white border border-admin-border text-admin-textMuted';
                }
            });

            renderList();
        }

        function onActionAreaClick(e) {
            const cancelBtn = e.target.closest('#btn-trigger-cancel-modal');
            if (cancelBtn) {
                openCancelModal();
                return;
            }

            const statusBtn = e.target.closest('.btn-status-action');
            if (statusBtn) {
                const action = statusBtn.getAttribute('data-action');
                if (action) {
                    executeOrderAction(action, {}, statusBtn);
                }
            }
        }

        function onAdminCancelFormSubmit(e) {
            e.preventDefault();
            const reason = document.getElementById('input-cancel-reason').value.trim();
            const submitBtn = document.getElementById('btn-submit-admin-cancel');

            if (!reason) {
                showToast('Alasan Kosong', 'Harap isi alasan pembatalan pesanan.', false);
                return;
            }

            executeOrderAction('dibatalkan', {
                cancellation_reason: reason
            }, submitBtn);
        }

        function onProofsGridClick(e) {
            const btn = e.target.closest('.btn-preview-proof');
            if (!btn) return;
            openProofZoomModal(btn.getAttribute('data-url'), btn.getAttribute('data-title'));
        }

        function onCopyResiClick() {
            const resi = (document.getElementById('det-resi-num')?.textContent || '').trim();
            if (!resi || resi === '-') return;
            navigator.clipboard.writeText(resi).then(() => {
                showToast('Nomor Resi Disalin', `Resi ${resi} berhasil disalin ke clipboard.`, true);
            });
        }

        function onMobileBackClick() {
            document.getElementById('right-panel').classList.remove('flex');
            document.getElementById('right-panel').classList.add('hidden');
            document.getElementById('left-panel').classList.remove('hidden');
        }

        function onKeyDown(e) {
            if (e.key === 'Escape') {
                closeCancelModal();
                closeProofZoomModal();
            }
        }

        // Drag-to-scroll handlers
        function onSliderMouseDown(e) {
            const slider = document.getElementById('filter-chips');
            if (!slider) return;
            isDraggingSlider = true;
            dragStartX = e.pageX - slider.offsetLeft;
            dragScrollLeft = slider.scrollLeft;
        }
        function onSliderMouseStop() {
            isDraggingSlider = false;
        }
        function onSliderMouseMove(e) {
            if (!isDraggingSlider) return;
            const slider = document.getElementById('filter-chips');
            if (!slider) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - dragStartX) * 1.5;
            slider.scrollLeft = dragScrollLeft - walk;
        }

        // =========================================================================
        // INIT & TEARDOWN (ZERO MEMORY LEAK)
        // =========================================================================
        function initPage() {
            bindEvent(document.getElementById('order-list'), 'click', onOrderListClick);
            bindEvent(document.getElementById('search-order'), 'input', onSearchInput);
            bindEvent(document.getElementById('filter-payment'), 'change', onPaymentFilterChange);
            bindEvent(document.getElementById('filter-source'), 'change', onSourceFilterChange);
            bindEvent(document.getElementById('filter-chips'), 'click', onFilterChipsClick);
            bindEvent(document.getElementById('det-action-area'), 'click', onActionAreaClick);
            bindEvent(document.getElementById('det-proofs-grid'), 'click', onProofsGridClick);
            bindEvent(document.getElementById('btn-copy-resi'), 'click', onCopyResiClick);
            bindEvent(document.getElementById('btn-back-mobile'), 'click', onMobileBackClick);
            bindEvent(document.getElementById('btn-refresh-orders'), 'click', () => fetchTransactions(true));

            // Drag-to-scroll
            const slider = document.getElementById('filter-chips');
            bindEvent(slider, 'mousedown', onSliderMouseDown);
            bindEvent(slider, 'mouseleave', onSliderMouseStop);
            bindEvent(slider, 'mouseup', onSliderMouseStop);
            bindEvent(slider, 'mousemove', onSliderMouseMove);

            // Cancel Modal
            bindEvent(document.getElementById('form-admin-cancel'), 'submit', onAdminCancelFormSubmit);
            bindEvent(document.getElementById('btn-close-cancel-x'), 'click', closeCancelModal);
            bindEvent(document.getElementById('btn-cancel-modal-back'), 'click', closeCancelModal);
            bindEvent(document.getElementById('adminCancelModal'), 'click', (e) => {
                if (e.target.id === 'adminCancelModal') closeCancelModal();
            });

            // Proof Zoom Modal
            bindEvent(document.getElementById('btn-close-trx-zoom'), 'click', closeProofZoomModal);
            bindEvent(document.getElementById('trxProofZoomModal'), 'click', (e) => {
                if (e.target.id === 'trxProofZoomModal') closeProofZoomModal();
            });

            bindEvent(document, 'keydown', onKeyDown);

            fetchTransactions(false);
        }

        function cleanupPage() {
            if (fetchAbortController) {
                fetchAbortController.abort();
                fetchAbortController = null;
            }
            if (actionAbortController) {
                actionAbortController.abort();
                actionAbortController = null;
            }
            activeTimers.forEach(id => window.clearTimeout(id));
            activeTimers.clear();

            while (boundListeners.length > 0) {
                const { el, type, fn, options } = boundListeners.pop();
                el.removeEventListener(type, fn, options);
            }
        }

        document.addEventListener('DOMContentLoaded', initPage, { once: true });
        window.addEventListener('pagehide', cleanupPage, { once: true });
    })();
</script>
@endpush