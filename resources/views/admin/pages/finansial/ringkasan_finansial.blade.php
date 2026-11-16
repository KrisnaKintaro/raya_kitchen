@extends('admin.admin_master')

@section('title', 'Dashboard Finansial - Admin Raya Kitchen')
@section('header_title', 'Dashboard Finansial')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
:root {
    /* 60-30-10 Claymorphism Token Engine */
    --clay-bg-60: #F5F2F8;
    --clay-surface-30: #FFFFFF;
    --clay-surface-lilac: #F9F6FC;
    --clay-accent-10: #9368B7;
    --clay-accent-hover: #7E54A3;
    --clay-border: #E8DEF5;
    --clay-text-main: #2D2A32;
    --clay-text-muted: #6E6A75;

    /* Semantic Fintech Accents */
    --clay-emerald-main: #059669;
    --clay-emerald-soft: #ECFDF5;
    --clay-emerald-tint: #D1FAE5;
    --clay-amber-main: #D97706;
    --clay-amber-soft: #FFFBEB;
    --clay-amber-tint: #FEF3C7;
    --clay-rose-main: #E11D48;

    /* Tactile 3D Clay Shadows */
    --clay-shadow-card:
        inset 0 -5px 8px rgba(147, 104, 183, 0.1),
        inset 0 5px 8px rgba(255, 255, 255, 0.98),
        0 12px 26px -6px rgba(147, 104, 183, 0.12);

    --clay-shadow-vault-emerald:
        inset 0 -6px 10px rgba(5, 150, 105, 0.11),
        inset 0 6px 10px rgba(255, 255, 255, 1),
        0 14px 30px -8px rgba(5, 150, 105, 0.14);

    --clay-shadow-vault-amber:
        inset 0 -6px 10px rgba(217, 119, 6, 0.11),
        inset 0 6px 10px rgba(255, 255, 255, 1),
        0 14px 30px -8px rgba(217, 119, 6, 0.14);

    --clay-shadow-well:
        inset 0 3px 6px rgba(147, 104, 183, 0.16),
        inset 0 -2px 4px rgba(255, 255, 255, 0.95);

    --clay-shadow-btn:
        inset 0 -3px 5px rgba(0, 0, 0, 0.16),
        inset 0 3px 5px rgba(255, 255, 255, 0.38),
        0 6px 14px rgba(147, 104, 183, 0.28);

    --clay-shadow-btn-amber:
        inset 0 -3px 5px rgba(146, 64, 14, 0.2),
        inset 0 3px 5px rgba(255, 255, 255, 0.45),
        0 6px 14px rgba(217, 119, 6, 0.24);

    --clay-radius-lg: clamp(1.5rem, 2vw, 2rem);
    --clay-radius-md: clamp(1rem, 1.4vw, 1.35rem);
}

/* Fluid Typography & Tabular Numerals for Financial Precision */
.fluid-h2 { font-size: clamp(1.125rem, 1.1vw + 0.85rem, 1.45rem); line-height: 1.25; }
.fluid-stat-lg { font-size: clamp(1.65rem, 2.2vw + 1rem, 2.45rem); line-height: 1.12; font-variant-numeric: tabular-nums; }
.fluid-stat-md { font-size: clamp(1.35rem, 1.5vw + 0.85rem, 1.85rem); line-height: 1.18; font-variant-numeric: tabular-nums; }
.num-tabular { font-variant-numeric: tabular-nums; }

/* Claymorphism Surfaces */
.clay-card {
    background-color: var(--clay-surface-30);
    border: 2px solid #FFFFFF;
    border-radius: var(--clay-radius-lg);
    box-shadow: var(--clay-shadow-card);
}

.clay-vault-emerald {
    background: linear-gradient(145deg, #FFFFFF 0%, #F3FAF7 100%);
    border: 2px solid #FFFFFF;
    border-radius: var(--clay-radius-lg);
    box-shadow: var(--clay-shadow-vault-emerald);
}

.clay-vault-amber {
    background: linear-gradient(145deg, #FFFFFF 0%, #FDF9F0 100%);
    border: 2px solid #FFFFFF;
    border-radius: var(--clay-radius-lg);
    box-shadow: var(--clay-shadow-vault-amber);
}

.clay-well {
    background-color: var(--clay-bg-60);
    border: 1.5px solid #FFFFFF;
    border-radius: var(--clay-radius-md);
    box-shadow: var(--clay-shadow-well);
}

.clay-icon-squircle {
    border: 2px solid #FFFFFF;
    border-radius: 1.15rem;
    box-shadow:
        inset 0 -3px 5px rgba(0, 0, 0, 0.06),
        inset 0 3px 5px rgba(255, 255, 255, 0.9),
        0 6px 12px rgba(147, 104, 183, 0.12);
}

.clay-badge-pill {
    border: 1.5px solid #FFFFFF;
    box-shadow:
        inset 0 -2px 3px rgba(0, 0, 0, 0.05),
        inset 0 2px 3px rgba(255, 255, 255, 0.9),
        0 3px 8px rgba(0, 0, 0, 0.04);
}

.clay-btn-primary {
    background-color: var(--clay-accent-10);
    color: #FFFFFF;
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: var(--clay-shadow-btn);
}
.clay-btn-primary:hover:not(:disabled) {
    background-color: var(--clay-accent-hover);
}

.clay-btn-amber {
    background-color: var(--clay-amber-main);
    color: #FFFFFF;
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: var(--clay-shadow-btn-amber);
}
.clay-btn-amber:hover:not(:disabled) {
    background-color: #B45309;
}

/* 5-State Feedback Loop & Fitts's Law (Min 44x44px) */
.ux-interactive {
    min-height: 44px;
    min-width: 44px;
    will-change: transform, opacity;
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
}
.ux-interactive:hover:not(:disabled) {
    transform: translateY(-3px);
}
.ux-interactive:active:not(:disabled) {
    transform: scale(0.95);
}
.ux-interactive:focus-visible {
    outline: 3px solid var(--clay-accent-10);
    outline-offset: 2px;
}
.ux-interactive:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

/* Staggered Entrance (60fps transform & opacity only) */
@keyframes clayFadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to { opacity: 1; transform: translateY(0); }
}
.stagger-item {
    opacity: 0;
    will-change: transform, opacity;
    animation: clayFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.delay-1 { animation-delay: 40ms; }
.delay-2 { animation-delay: 90ms; }
.delay-3 { animation-delay: 140ms; }
.delay-4 { animation-delay: 190ms; }
.delay-5 { animation-delay: 240ms; }

.custom-cb {
    appearance: none;
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid #CBD5E1;
    border-radius: 0.4rem;
    cursor: pointer;
    position: relative;
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.custom-cb:checked {
    background-color: var(--clay-accent-10);
    border-color: var(--clay-accent-10);
}
.custom-cb:checked::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 1px;
    width: 5px;
    height: 10px;
    border: solid #FFFFFF;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.flatpickr-calendar {
    font-family: inherit;
    box-shadow: 0 16px 32px -8px rgba(147, 104, 183, 0.25);
    border: 2px solid #FFFFFF;
    border-radius: 1.5rem;
}
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.inRange {
    background: var(--clay-accent-10) !important;
    border-color: var(--clay-accent-10) !important;
    color: #FFFFFF !important;
}

@media (prefers-reduced-motion: reduce) {
    *, ::before, ::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

@media print {
    #sidebar, header, .no-print { display: none !important; }
    #main-content { overflow: visible !important; height: auto !important; }
    body { overflow: visible !important; background: #FFFFFF !important; }
    .clay-card, .clay-vault-emerald, .clay-vault-amber { box-shadow: none !important; border: 1px solid #CBD5E1 !important; }
}
</style>
@endsection

@section('content')

    <!-- TOAST NOTIFICATION -->
    <div id="ux-toast" role="status" aria-live="polite" class="fixed bottom-6 right-6 z-[200] hidden items-center gap-3 px-5 py-3.5 clay-card border-2 border-white shadow-2xl no-print">
        <div id="ux-toast-icon" class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
            <i class="ph ph-check-circle text-xl"></i>
        </div>
        <div>
            <p id="ux-toast-title" class="text-xs font-black text-admin-textMain">Berhasil</p>
            <p id="ux-toast-msg" class="text-[11px] font-bold text-admin-textMuted">Data berhasil diperbarui.</p>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- LAPIS 1: FILTER UNIVERSAL & CONTROL COCKPIT                           -->
    <!-- ===================================================================== -->
    <section aria-label="Filter Universal Dashboard" class="clay-card p-5 lg:p-6 mb-7 stagger-item delay-1 no-print">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-5">

            <!-- Kiri: Identitas Panel & Indikator Periode Aktif -->
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-12 h-12 rounded-2xl clay-btn-primary flex items-center justify-center shrink-0">
                    <i class="ph ph-sliders-horizontal text-2xl"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg bg-admin-navBg text-admin-sideBg text-[10px] font-black uppercase tracking-wider">Filter Universal</span>
                        <span class="text-xs font-bold text-admin-textMuted">Rentang Aktif:</span>
                        <span id="active-period-badge" class="text-xs font-black text-admin-sideBg bg-purple-50 px-2.5 py-0.5 rounded-lg border border-purple-200">Memuat...</span>
                        <span id="active-channel-badge" class="text-xs font-black text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200">Semua Channel</span>
                    </div>
                    <h2 class="fluid-h2 font-black text-admin-textMain mt-1">Pusat Kendali Arus Kas & Piutang</h2>
                </div>
            </div>

            <!-- Kanan: Preset Tanggal, Kalender Bebas, Channel & Cetak -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="clay-well p-1 flex items-center gap-1" role="group" aria-label="Preset Rentang Tanggal">
                    <button type="button" data-preset="7d" class="preset-date-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black bg-white text-admin-sideBg shadow-sm">7 Hari</button>
                    <button type="button" data-preset="30d" class="preset-date-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">30 Hari</button>
                    <button type="button" data-preset="month" class="preset-date-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">Bulan Ini</button>
                    <button type="button" data-preset="year" class="preset-date-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">Tahun Ini</button>
                </div>

                <div class="relative min-w-[245px] flex-1 sm:flex-none" id="wrapper-custom-date">
                    <i class="ph ph-calendar-blank absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-sideBg pointer-events-none"></i>
                    <input type="text" id="universal-date-range" aria-label="Pilih Rentang Tanggal Bebas" placeholder="Pilih Rentang Tanggal" class="ux-interactive w-full pl-11 pr-4 py-2.5 clay-well text-admin-textMain text-xs sm:text-sm font-black cursor-pointer focus:outline-none" readonly>
                </div>

                <div class="clay-well p-1 flex items-center gap-1" role="group" aria-label="Filter Sumber Pesanan">
                    <button type="button" data-source="all" class="source-filter-btn ux-interactive px-3 py-2 rounded-xl text-xs font-black bg-white text-admin-sideBg shadow-sm flex items-center gap-1.5">
                        <i class="ph ph-stack text-sm"></i>
                        <span>Semua</span>
                    </button>
                    <button type="button" data-source="web" class="source-filter-btn ux-interactive px-3 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain flex items-center gap-1.5">
                        <i class="ph ph-globe text-sm"></i>
                        <span>Web</span>
                    </button>
                    <button type="button" data-source="whatsapp" class="source-filter-btn ux-interactive px-3 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain flex items-center gap-1.5">
                        <i class="ph ph-whatsapp-logo text-sm"></i>
                        <span>WA Bot</span>
                    </button>
                </div>

                <button type="button" id="btn-print-report" class="ux-interactive clay-btn-primary px-4 py-2.5 rounded-2xl font-black text-xs sm:text-sm inline-flex items-center gap-2">
                    <i class="ph ph-printer text-lg"></i>
                    <span>Cetak PDF</span>
                </button>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- LAPIS 2A: AKUMULASI SEMUA LINI MASA (TACTILE CLAY VAULT CARDS)        -->
    <!-- ===================================================================== -->
    <div class="mb-3.5 flex flex-wrap items-center justify-between gap-2 stagger-item delay-2">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl clay-well flex items-center justify-center text-admin-sideBg">
                <i class="ph ph-infinity text-base"></i>
            </span>
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-admin-textMain">Rekapitulasi Kas & Piutang Sepanjang Waktu</h3>
                <p class="text-[11px] font-bold text-admin-textMuted">Akumulasi permanen sejak awal toko beroperasi (kebal terhadap filter periode di atas)</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 stagger-item delay-2">

        <!-- KARTU 1: ALL-TIME KAS MASUK (TACTILE EMERALD CLAY VAULT) -->
        <article class="clay-vault-emerald p-6 lg:p-7 flex flex-col justify-between relative overflow-hidden">
            <!-- Top Row: Icon Squircle, Label & Order Counter Badge -->
            <div class="flex flex-wrap justify-between items-start gap-4 mb-5">
                <div class="flex items-center gap-3.5">
                    <div class="w-13 h-13 p-3 clay-icon-squircle bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                        <i class="ph ph-vault text-2xl"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-emerald-100/90 text-emerald-800 text-[10px] font-black uppercase tracking-wider border border-white">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            <span>Kas Masuk Terverifikasi</span>
                        </div>
                        <h4 class="text-sm font-black text-admin-textMain mt-1">Total Pendapatan Diterima</h4>
                    </div>
                </div>

                <div class="clay-badge-pill px-3.5 py-1.5 rounded-2xl bg-white/90 flex items-center gap-2">
                    <i class="ph ph-check-circle text-emerald-600 text-base"></i>
                    <span class="text-xs font-black text-admin-textMain num-tabular" id="alltime-orders-count">0 Order Sukses</span>
                </div>
            </div>

            <!-- Middle Row: Primary Figure & Live Composition Progress Bar -->
            <div class="mb-5">
                <p class="text-[11px] font-bold text-admin-textMuted mb-1">Akumulasi Dana Bersih Masuk (Lunas + DP):</p>
                <h3 class="fluid-stat-lg font-black text-emerald-700 tracking-tight" id="alltime-revenue-total">Rp 0</h3>

                <!-- Tactile Composition Bar -->
                <div class="mt-3.5">
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5">
                        <span id="alltime-rev-lunas-pct">Lunas: 100%</span>
                        <span id="alltime-rev-dp-pct">DP Masuk: 0%</span>
                    </div>
                    <div class="w-full h-2.5 clay-well rounded-full overflow-hidden flex p-0.5 gap-0.5">
                        <div id="bar-alltime-lunas" class="h-full bg-emerald-600 rounded-l-full transition-transform duration-300 origin-left" style="width: 100%"></div>
                        <div id="bar-alltime-dp" class="h-full bg-teal-400 rounded-r-full transition-transform duration-300 origin-right" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Recessed Clay Well Breakdown -->
            <div class="clay-well p-4 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="bg-white/85 p-3 rounded-xl border border-white flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[11px] font-bold text-admin-textMuted">Pesanan Lunas (100%)</span>
                        <i class="ph ph-seal-check text-emerald-600 text-sm"></i>
                    </div>
                    <span class="text-sm sm:text-base font-black text-emerald-700 num-tabular" id="alltime-revenue-lunas">Rp 0</span>
                </div>

                <div class="bg-white/85 p-3 rounded-xl border border-white flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[11px] font-bold text-admin-textMuted">Uang Muka DP (30%)</span>
                        <i class="ph ph-coins text-teal-600 text-sm"></i>
                    </div>
                    <span class="text-sm sm:text-base font-black text-teal-700 num-tabular" id="alltime-revenue-dp">Rp 0</span>
                </div>
            </div>
        </article>

        <!-- KARTU 2: ALL-TIME PIUTANG / TAGIHAN GANTUNG (TACTILE AMBER CLAY VAULT) -->
        <article class="clay-vault-amber p-6 lg:p-7 flex flex-col justify-between relative overflow-hidden">
            <!-- Top Row: Icon Squircle, Label & Action CTA -->
            <div class="flex flex-wrap justify-between items-start gap-4 mb-5">
                <div class="flex items-center gap-3.5">
                    <div class="w-13 h-13 p-3 clay-icon-squircle bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                        <i class="ph ph-hand-coins text-2xl"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-amber-100/90 text-amber-900 text-[10px] font-black uppercase tracking-wider border border-white">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                            <span>Piutang & Tagihan Aktif</span>
                        </div>
                        <h4 class="text-sm font-black text-admin-textMain mt-1">Total Tagihan Belum Terbayar</h4>
                    </div>
                </div>

                <button type="button" id="btn-jump-piutang" class="ux-interactive clay-btn-amber px-4 py-2 rounded-2xl text-xs font-black inline-flex items-center gap-2 no-print">
                    <span>Tampilkan di Tabel</span>
                    <i class="ph ph-arrow-down-right text-sm"></i>
                </button>
            </div>

            <!-- Middle Row: Primary Figure & Live Composition Progress Bar -->
            <div class="mb-5">
                <p class="text-[11px] font-bold text-admin-textMuted mb-1">Akumulasi Dana Menunggu Pelunasan Pelanggan:</p>
                <h3 class="fluid-stat-lg font-black text-amber-700 tracking-tight" id="alltime-unpaid-total">Rp 0</h3>

                <!-- Tactile Composition Bar -->
                <div class="mt-3.5">
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5">
                        <span id="alltime-unp-dp-pct">Sisa DP (70%): 100%</span>
                        <span id="alltime-unp-full-pct">Unpaid: 0%</span>
                    </div>
                    <div class="w-full h-2.5 clay-well rounded-full overflow-hidden flex p-0.5 gap-0.5">
                        <div id="bar-alltime-unp-dp" class="h-full bg-amber-500 rounded-l-full transition-transform duration-300 origin-left" style="width: 100%"></div>
                        <div id="bar-alltime-unp-full" class="h-full bg-rose-500 rounded-r-full transition-transform duration-300 origin-right" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Recessed Clay Well Breakdown -->
            <div class="clay-well p-4 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="bg-white/85 p-3 rounded-xl border border-white flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[11px] font-bold text-admin-textMuted">Sisa Pelunasan DP (70%)</span>
                        <i class="ph ph-Split-horizontal text-amber-600 text-sm"></i>
                    </div>
                    <span class="text-sm sm:text-base font-black text-amber-700 num-tabular" id="alltime-unpaid-dp">Rp 0</span>
                </div>

                <div class="bg-white/85 p-3 rounded-xl border border-white flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[11px] font-bold text-admin-textMuted">Belum Bayar (Unpaid)</span>
                        <i class="ph ph-warning-circle text-rose-600 text-sm"></i>
                    </div>
                    <span class="text-sm sm:text-base font-black text-rose-600 num-tabular" id="alltime-unpaid-full">Rp 0</span>
                </div>
            </div>
        </article>

    </div>

    <!-- ===================================================================== -->
    <!-- LAPIS 2B: METRIK BERDASARKAN FILTER TANGGAL & CHANNEL                 -->
    <!-- ===================================================================== -->
    <div class="mb-3.5 flex items-center justify-between stagger-item delay-3">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl clay-well flex items-center justify-center text-emerald-600">
                <i class="ph ph-funnel text-base"></i>
            </span>
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-admin-textMain">Performa Periode & Channel Terpilih</h3>
                <p class="text-[11px] font-bold text-admin-textMuted">Metrik di bawah otomatis beradaptasi dengan rentang waktu pada Filter Universal</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 stagger-item delay-3">
        <!-- Card 1: Kas Masuk Terfilter -->
        <div class="clay-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 clay-icon-squircle bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="ph ph-wallet text-2xl"></i>
                </div>
                <span id="filtered-rev-growth" class="clay-badge-pill px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 num-tabular">+0%</span>
            </div>
            <div>
                <p class="text-[11px] font-black text-admin-textMuted uppercase tracking-wider mb-1">Kas Masuk (Sesuai Filter)</p>
                <h3 class="fluid-stat-md font-black text-admin-textMain tracking-tight mb-3" id="filtered-revenue-total">Rp 0</h3>
                <div class="clay-well p-3 flex justify-between text-xs font-bold text-admin-textMuted">
                    <span>Lunas: <strong class="text-admin-sideBg num-tabular" id="filtered-revenue-lunas">Rp 0</strong></span>
                    <span>DP 30%: <strong class="text-teal-600 num-tabular" id="filtered-revenue-dp">Rp 0</strong></span>
                </div>
            </div>
        </div>

        <!-- Card 2: Tagihan Belum Terbayar Terfilter -->
        <div class="clay-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 clay-icon-squircle bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="ph ph-clock-countdown text-2xl"></i>
                </div>
                <span id="filtered-unpaid-growth" class="clay-badge-pill px-3 py-1 rounded-xl text-xs font-black bg-amber-50 text-amber-700 num-tabular">0%</span>
            </div>
            <div>
                <p class="text-[11px] font-black text-admin-textMuted uppercase tracking-wider mb-1">Tagihan Gantung (Sesuai Filter)</p>
                <h3 class="fluid-stat-md font-black text-amber-600 tracking-tight mb-3" id="filtered-unpaid-total">Rp 0</h3>
                <div class="clay-well p-3 flex justify-between text-xs font-bold text-admin-textMuted">
                    <span>Sisa DP: <strong class="text-orange-600 num-tabular" id="filtered-unpaid-dp">Rp 0</strong></span>
                    <span>Unpaid: <strong class="text-rose-600 num-tabular" id="filtered-unpaid-full">Rp 0</strong></span>
                </div>
            </div>
        </div>

        <!-- Card 3: Volume Transaksi & Rata-Rata Order -->
        <div class="clay-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 clay-icon-squircle bg-purple-50 text-admin-sideBg flex items-center justify-center">
                    <i class="ph ph-receipt text-2xl"></i>
                </div>
                <span class="clay-badge-pill px-3 py-1 rounded-xl text-xs font-black bg-rose-50 text-rose-600 num-tabular" id="filtered-cancelled-badge">0 Dibatalkan</span>
            </div>
            <div>
                <p class="text-[11px] font-black text-admin-textMuted uppercase tracking-wider mb-1">Transaksi Sukses (Sesuai Filter)</p>
                <h3 class="fluid-stat-md font-black text-admin-textMain tracking-tight mb-3" id="filtered-success-orders">0 Order</h3>
                <div class="clay-well p-3 flex justify-between text-xs font-bold text-admin-textMuted">
                    <span>Rata-rata: <strong class="text-admin-sideBg num-tabular" id="filtered-aov">Rp 0</strong></span>
                    <span>Unpaid: <strong class="text-rose-600 num-tabular" id="filtered-unpaid-count">0 Order</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- LAPIS 3: DUA GRAFIK RINCIAN KOMPONEN (MENGIKUTI FILTER UNIVERSAL)     -->
    <!-- ===================================================================== -->
    <section aria-label="Grafik Kas Masuk dan Tagihan" class="clay-card p-6 lg:p-7 mb-8 stagger-item delay-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 mb-6 border-b border-admin-border">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 clay-icon-squircle bg-purple-50 text-admin-sideBg flex items-center justify-center shrink-0 mt-0.5">
                    <i class="ph ph-chart-line-up text-xl"></i>
                </div>
                <div>
                    <h2 class="fluid-h2 font-black text-admin-textMain">Grafik Rincian Kas Masuk vs Tagihan Belum Dibayar</h2>
                    <p class="text-xs font-bold text-admin-textMuted mt-0.5">
                        Tren arus kas dan piutang berdasarkan rentang waktu serta channel yang aktif.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start lg:self-auto no-print">
                <span class="text-[11px] font-black text-admin-textMuted uppercase tracking-wider hidden sm:inline">Kelompokkan:</span>
                <div class="clay-well p-1 flex items-center gap-1" role="group" aria-label="Kelompok Waktu Grafik">
                    <button type="button" data-granularity="daily" class="granularity-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black bg-admin-sideBg text-white shadow-sm">Per Hari</button>
                    <button type="button" data-granularity="weekly" class="granularity-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">Per Minggu</button>
                    <button type="button" data-granularity="monthly" class="granularity-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">Per Bulan</button>
                    <button type="button" data-granularity="yearly" class="granularity-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">Per Tahun</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-7">
            <!-- GRAFIK 1: RINCIAN UANG MASUK -->
            <div class="clay-well p-5 flex flex-col justify-between">
                <div class="flex justify-between items-start gap-4 mb-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-wider">
                            <i class="ph ph-arrow-down-left text-xs"></i> Grafik 1: Uang Sudah Masuk
                        </div>
                        <h3 class="text-base font-black text-admin-textMain mt-1">Rincian Pendapatan Diterima</h3>
                        <p class="text-[11px] font-bold text-admin-textMuted">Total Kas Masuk = Pembayaran Lunas 100% + Uang DP 30% Masuk</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10px] font-black uppercase text-admin-textMuted block">Total di Grafik</span>
                        <span class="text-sm font-black text-emerald-600 num-tabular" id="chart-rev-sum">Rp 0</span>
                    </div>
                </div>

                <div class="w-full h-[290px] relative bg-white rounded-2xl p-3 border border-white shadow-sm">
                    <canvas id="revenueChartCanvas"></canvas>
                </div>
            </div>

            <!-- GRAFIK 2: RINCIAN TAGIHAN GANTUNG / PIUTANG -->
            <div class="clay-well p-5 flex flex-col justify-between">
                <div class="flex justify-between items-start gap-4 mb-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-black uppercase tracking-wider">
                            <i class="ph ph-hourglass-high text-xs"></i> Grafik 2: Uang Belum Masuk
                        </div>
                        <h3 class="text-base font-black text-admin-textMain mt-1">Rincian Tagihan & Piutang Pelanggan</h3>
                        <p class="text-[11px] font-bold text-admin-textMuted">Total Tagihan = Sisa Pelunasan DP 70% + Pesanan Belum Bayar (Unpaid)</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10px] font-black uppercase text-admin-textMuted block">Total di Grafik</span>
                        <span class="text-sm font-black text-amber-600 num-tabular" id="chart-unpaid-sum">Rp 0</span>
                    </div>
                </div>

                <div class="w-full h-[290px] relative bg-white rounded-2xl p-3 border border-white shadow-sm">
                    <canvas id="unpaidChartCanvas"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- LAPIS 4: TOP 5 PRODUK PENYUMBANG OMZET & KOMPOSISI CHANNEL            -->
    <!-- ===================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 stagger-item delay-4">
        <div class="lg:col-span-2 clay-card p-6 lg:p-7">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 clay-icon-squircle bg-purple-50 text-admin-sideBg flex items-center justify-center">
                    <i class="ph ph-trophy text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-admin-textMain">5 Varian Produk Penyumbang Omzet Terbesar</h3>
                    <p class="text-xs font-bold text-admin-textMuted">Dihitung dari pesanan sukses (Lunas & DP) pada rentang tanggal & channel terpilih.</p>
                </div>
            </div>
            <div id="top-products-container" class="space-y-3.5">
                <div class="clay-well p-3.5 animate-pulse flex flex-col gap-2">
                    <div class="h-3.5 bg-purple-200/60 rounded w-1/2"></div>
                    <div class="h-2 bg-white rounded-full w-full"></div>
                </div>
                <div class="clay-well p-3.5 animate-pulse flex flex-col gap-2">
                    <div class="h-3.5 bg-purple-200/60 rounded w-2/3"></div>
                    <div class="h-2 bg-white rounded-full w-full"></div>
                </div>
            </div>
        </div>

        <div class="clay-card p-6 lg:p-7 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 clay-icon-squircle bg-purple-50 text-admin-sideBg flex items-center justify-center">
                        <i class="ph ph-share-network text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-admin-textMain">Distribusi Channel Pesanan</h3>
                        <p class="text-xs font-bold text-admin-textMuted">Pesanan aktif (tidak dibatalkan) pada filter terpilih.</p>
                    </div>
                </div>

                <div class="space-y-3.5">
                    <div class="clay-well p-4 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-admin-sideBg text-white flex items-center justify-center">
                                <i class="ph ph-globe text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs font-black text-admin-textMain">Website E-Commerce</p>
                                <p class="text-[11px] font-bold text-admin-textMuted">Checkout via Katalog Web</p>
                            </div>
                        </div>
                        <span class="text-base font-black text-admin-sideBg num-tabular" id="channel-web-count">0 Order</span>
                    </div>

                    <div class="clay-well p-4 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center">
                                <i class="ph ph-whatsapp-logo text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs font-black text-admin-textMain">WhatsApp Bot</p>
                                <p class="text-[11px] font-bold text-admin-textMuted">Checkout via Chat Otomatis</p>
                            </div>
                        </div>
                        <span class="text-base font-black text-emerald-600 num-tabular" id="channel-wa-count">0 Order</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-admin-border no-print">
                <button type="button" id="btn-export-csv" class="ux-interactive clay-btn-primary w-full px-5 py-3 rounded-2xl font-black text-xs sm:text-sm flex justify-between items-center">
                    <span class="flex items-center gap-2">
                        <i class="ph ph-file-csv text-lg"></i>
                        <span>Unduh Data Tabel (CSV)</span>
                    </span>
                    <i class="ph ph-download-simple text-base"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- LAPIS 5: TABEL RIWAYAT PENJUALAN & STATUS TAGIHAN                     -->
    <!-- ===================================================================== -->
    <section id="section-tabel-riwayat" aria-label="Tabel Riwayat Penjualan" class="clay-card overflow-hidden flex flex-col stagger-item delay-5">

        <!-- Header & Filter Tabel -->
        <div class="p-6 border-b border-admin-border flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-admin-bg/40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 clay-icon-squircle bg-white text-admin-sideBg flex items-center justify-center">
                    <i class="ph ph-table text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base lg:text-lg font-black text-admin-textMain">Rincian Transaksi & Status Tagihan</h2>
                    <p class="text-xs font-bold text-admin-textMuted">Menampilkan daftar pesanan sesuai rentang tanggal & channel di atas.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 no-print">
                <div class="relative min-w-[240px] flex-1 sm:flex-none">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-base text-admin-textMuted pointer-events-none"></i>
                    <input type="text" id="filter-search" aria-label="Cari pesanan" placeholder="Cari No. Order / Nama / WA..." class="ux-interactive w-full pl-10 pr-4 py-2.5 bg-white border border-admin-border rounded-2xl text-xs sm:text-sm font-bold text-admin-textMain focus:outline-none">
                </div>

                <div class="relative min-w-[195px]" id="wrapper-status">
                    <button type="button" id="btn-status" aria-haspopup="true" aria-expanded="false" class="ux-interactive flex items-center justify-between w-full bg-white border border-admin-border text-admin-textMain text-xs sm:text-sm font-black rounded-2xl pl-4 pr-3 py-2.5">
                        <span id="lbl-status" class="truncate">Semua Status Bayar</span>
                        <i class="ph ph-caret-down text-admin-textMuted ml-2"></i>
                    </button>

                    <div id="menu-status" role="menu" class="absolute right-0 mt-2 w-64 clay-card p-3 hidden z-50 border border-admin-border">
                        <div class="flex justify-between items-center pb-2 mb-2 border-b border-admin-border">
                            <span class="text-[11px] font-black uppercase text-admin-textMuted">Filter Status (ESC tutup)</span>
                            <button type="button" id="btn-close-status-menu" class="w-8 h-8 rounded-lg hover:bg-admin-bg flex items-center justify-center text-admin-textMuted" aria-label="Tutup Menu Filter">
                                <i class="ph ph-x text-sm"></i>
                            </button>
                        </div>
                        <label class="flex items-center gap-3 p-2.5 hover:bg-admin-bg rounded-xl cursor-pointer">
                            <input type="checkbox" value="paid_full" class="cb-status custom-cb">
                            <span class="text-xs font-black text-emerald-700">Lunas (100%)</span>
                        </label>
                        <label class="flex items-center gap-3 p-2.5 hover:bg-admin-bg rounded-xl cursor-pointer">
                            <input type="checkbox" value="paid_dp" class="cb-status custom-cb">
                            <span class="text-xs font-black text-amber-700">Bayar DP (Sisa Tagihan 70%)</span>
                        </label>
                        <label class="flex items-center gap-3 p-2.5 hover:bg-admin-bg rounded-xl cursor-pointer">
                            <input type="checkbox" value="unpaid" class="cb-status custom-cb">
                            <span class="text-xs font-black text-rose-600">Belum Bayar (Unpaid 100%)</span>
                        </label>
                        <div class="h-px bg-admin-border my-1"></div>
                        <label class="flex items-center gap-3 p-2.5 hover:bg-admin-bg rounded-xl cursor-pointer">
                            <input type="checkbox" value="dibatalkan" class="cb-status custom-cb">
                            <span class="text-xs font-black text-slate-500">Pesanan Dibatalkan</span>
                        </label>
                    </div>
                </div>

                <button type="button" id="btn-reset-table-filter" class="ux-interactive px-4 py-2.5 bg-white hover:bg-admin-bg text-admin-textMuted font-black text-xs rounded-2xl border border-admin-border flex items-center gap-1.5">
                    <i class="ph ph-arrow-counter-clockwise text-sm"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto hide-scrollbar w-full">
            <table class="w-full text-sm text-left">
                <thead class="text-[11px] text-admin-textMuted uppercase bg-admin-bg/60 border-b border-admin-border">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider">No. Pesanan</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider">Waktu Checkout</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider">Pelanggan</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider text-center">Channel & Metode</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider text-right">Kas Masuk</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider text-right">Sisa Tagihan</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider text-right">Total Nota</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider text-center">Status Bayar</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-wider text-center no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-admin-border"></tbody>
            </table>
        </div>

        <!-- Empty State Illustration -->
        <div id="empty-state" class="hidden flex-col items-center justify-center py-16 px-4 text-center">
            <div class="w-16 h-16 clay-well flex items-center justify-center mb-4 text-admin-textMuted">
                <i class="ph ph-receipt-x text-3xl"></i>
            </div>
            <h3 class="text-base font-black text-admin-textMain mb-1">Tidak Ada Transaksi pada Filter Ini</h3>
            <p class="text-xs font-bold text-admin-textMuted max-w-xs">Coba perluas rentang tanggal, ganti channel pesanan, atau klik tombol Reset di atas.</p>
        </div>

        <!-- Pagination Bar -->
        <div class="p-5 border-t border-admin-border flex flex-col sm:flex-row items-center justify-between gap-3 bg-admin-bg/30 no-print">
            <span class="text-xs font-bold text-admin-textMuted" id="pagination-info">Menampilkan 0 transaksi</span>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-prev-page" class="ux-interactive px-4 py-2 rounded-xl border border-admin-border bg-white text-xs font-black text-admin-textMain flex items-center gap-1.5">
                    <i class="ph ph-caret-left text-sm"></i>
                    <span>Sebelumnya</span>
                </button>
                <span id="page-indicator" class="text-xs font-black px-3 py-2 text-admin-sideBg num-tabular">Hal 1 / 1</span>
                <button type="button" id="btn-next-page" class="ux-interactive px-4 py-2 rounded-xl border border-admin-border bg-white text-xs font-black text-admin-textMain flex items-center gap-1.5">
                    <span>Selanjutnya</span>
                    <i class="ph ph-caret-right text-sm"></i>
                </button>
            </div>
        </div>

    </section>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function() {
    'use strict';

    let revChartInstance = null;
    let unpaidChartInstance = null;
    let fpUniversal = null;
    let activeAbortController = null;
    const activeTimers = new Set();

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

    const todayDate = new Date();
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(todayDate.getDate() - 6);

    function toYMD(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    const filterState = {
        start_date: toYMD(sevenDaysAgo),
        end_date: toYMD(todayDate),
        granularity: 'daily',
        order_source: 'all'
    };

    let allTransactions = [];
    let filteredTransactions = [];
    let selectedStatuses = [];
    let currentPage = 1;
    const perPage = 6;

    const formatRupiah = (num) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(Number(num) || 0);

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&' + 'amp;')
            .replace(/</g, '&' + 'lt;')
            .replace(/>/g, '&' + 'gt;')
            .replace(/"/g, '&' + 'quot;')
            .replace(/'/g, '&' + '#039;');
    }

    function showToast(title, message, isSuccess = true) {
        const toast = document.getElementById('ux-toast');
        const icon  = document.getElementById('ux-toast-icon');
        if (!toast || !icon) return;

        document.getElementById('ux-toast-title').textContent = title;
        document.getElementById('ux-toast-msg').textContent = message;

        icon.className = isSuccess
            ? 'w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0'
            : 'w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0';
        icon.innerHTML = isSuccess
            ? '<i class="ph ph-check-circle text-xl"></i>'
            : '<i class="ph ph-warning-circle text-xl"></i>';

        toast.classList.remove('hidden');
        toast.classList.add('flex');

        registerTimeout(() => {
            toast.classList.remove('flex');
            toast.classList.add('hidden');
        }, 3200);
    }

    function renderTableSkeleton() {
        const tbody = document.getElementById('table-body');
        if (!tbody) return;
        tbody.innerHTML = [1, 2, 3, 4].map(() => `
            <tr class="animate-pulse">
                <td class="px-6 py-4"><div class="h-4 bg-purple-100 rounded w-28"></div></td>
                <td class="px-6 py-4"><div class="h-3.5 bg-gray-100 rounded w-32"></div></td>
                <td class="px-6 py-4">
                    <div class="h-3.5 bg-gray-200 rounded w-28 mb-1.5"></div>
                    <div class="h-3 bg-gray-100 rounded w-24"></div>
                </td>
                <td class="px-6 py-4 text-center"><div class="h-5 bg-purple-50 rounded-lg w-24 mx-auto"></div></td>
                <td class="px-6 py-4 text-right"><div class="h-4 bg-emerald-50 rounded w-20 ml-auto"></div></td>
                <td class="px-6 py-4 text-right"><div class="h-4 bg-amber-50 rounded w-20 ml-auto"></div></td>
                <td class="px-6 py-4 text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
                <td class="px-6 py-4 text-center"><div class="h-6 bg-gray-100 rounded-lg w-20 mx-auto"></div></td>
                <td class="px-6 py-4 text-center"><div class="h-9 bg-purple-50 rounded-xl w-20 mx-auto"></div></td>
            </tr>
        `).join('');
    }

    // --- FETCH DATA REAL DARI CONTROLLER API ---
    function fetchDashboardData() {
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();

        renderTableSkeleton();

        const loader = document.getElementById('global-loader');
        const loaderBar = document.getElementById('loader-bar');
        if (loader) loader.classList.remove('opacity-0', 'pointer-events-none');
        if (loaderBar) loaderBar.style.transform = 'scaleX(0.7)';

        const params = new URLSearchParams({
            start_date: filterState.start_date,
            end_date: filterState.end_date,
            granularity: filterState.granularity,
            order_source: filterState.order_source
        });

        fetch(`/api/admin/dashboard-finansial?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: activeAbortController.signal
        })
        .then(async (response) => {
            const res = await response.json();
            if (!response.ok || res.status !== 'success') {
                throw new Error(res.message || `HTTP Error ${response.status}`);
            }
            return res;
        })
        .then((res) => {
            renderDashboardUI(res.data);
        })
        .catch((err) => {
            if (err.name !== 'AbortError') {
                showToast('Gagal Memuat Data', err.message || 'Tidak dapat terhubung ke server finansial.', false);
            }
        })
        .finally(() => {
            if (loaderBar) loaderBar.style.transform = 'scaleX(1)';
            registerTimeout(() => {
                if (loader) loader.classList.add('opacity-0', 'pointer-events-none');
                registerTimeout(() => {
                    if (loaderBar) loaderBar.style.transform = 'scaleX(0)';
                }, 200);
            }, 300);
        });
    }

    function renderDashboardUI(data) {
        const allTime  = data.all_time_stats || {};
        const filtered = data.filtered_stats || {};

        document.getElementById('active-period-badge').textContent = filtered.period_label || `${filterState.start_date} - ${filterState.end_date}`;
        const channelNames = { all: 'Semua Channel', web: 'Khusus Website', whatsapp: 'Khusus WA Bot' };
        document.getElementById('active-channel-badge').textContent = channelNames[filterState.order_source] || 'Semua Channel';

        // 1. All-Time Stats & Dynamic Composition Bars
        const revTotalAll = Number(allTime.revenue_total || 0);
        const revLunasAll = Number(allTime.revenue_lunas || 0);
        const revDpAll    = Number(allTime.revenue_dp_paid || 0);
        const lunasPct    = revTotalAll > 0 ? Math.round((revLunasAll / revTotalAll) * 100) : 100;
        const dpPct       = revTotalAll > 0 ? Math.max(0, 100 - lunasPct) : 0;

        document.getElementById('alltime-revenue-total').textContent = formatRupiah(revTotalAll);
        document.getElementById('alltime-revenue-lunas').textContent = formatRupiah(revLunasAll);
        document.getElementById('alltime-revenue-dp').textContent    = formatRupiah(revDpAll);
        document.getElementById('alltime-orders-count').textContent  = `${allTime.success_orders || 0} Order Sukses`;
        document.getElementById('alltime-rev-lunas-pct').textContent = `Lunas: ${lunasPct}%`;
        document.getElementById('alltime-rev-dp-pct').textContent    = `DP Masuk: ${dpPct}%`;
        document.getElementById('bar-alltime-lunas').style.width     = `${lunasPct}%`;
        document.getElementById('bar-alltime-dp').style.width        = `${dpPct}%`;

        const unpTotalAll = Number(allTime.unpaid_total || 0);
        const unpDpAll    = Number(allTime.unpaid_dp_sisa || 0);
        const unpFullAll  = Number(allTime.unpaid_full || 0);
        const unpDpPct    = unpTotalAll > 0 ? Math.round((unpDpAll / unpTotalAll) * 100) : 100;
        const unpFullPct  = unpTotalAll > 0 ? Math.max(0, 100 - unpDpPct) : 0;

        document.getElementById('alltime-unpaid-total').textContent   = formatRupiah(unpTotalAll);
        document.getElementById('alltime-unpaid-dp').textContent      = formatRupiah(unpDpAll);
        document.getElementById('alltime-unpaid-full').textContent    = formatRupiah(unpFullAll);
        document.getElementById('alltime-unp-dp-pct').textContent     = `Sisa DP (70%): ${unpDpPct}%`;
        document.getElementById('alltime-unp-full-pct').textContent   = `Unpaid: ${unpFullPct}%`;
        document.getElementById('bar-alltime-unp-dp').style.width     = `${unpDpPct}%`;
        document.getElementById('bar-alltime-unp-full').style.width   = `${unpFullPct}%`;

        // 2. Filtered Stats
        document.getElementById('filtered-revenue-total').textContent = formatRupiah(filtered.revenue_total);
        document.getElementById('filtered-revenue-lunas').textContent = formatRupiah(filtered.revenue_lunas);
        document.getElementById('filtered-revenue-dp').textContent    = formatRupiah(filtered.revenue_dp_paid);

        const revPct = Number(filtered.revenue_growth_pct || 0);
        const revSign = revPct >= 0 ? '+' : '';
        const revGrowthEl = document.getElementById('filtered-rev-growth');
        revGrowthEl.textContent = `${revSign}${revPct}% vs periode lalu`;
        revGrowthEl.className = `clay-badge-pill px-3 py-1 rounded-xl text-xs font-black num-tabular ${revPct >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600'}`;

        document.getElementById('filtered-unpaid-total').textContent = formatRupiah(filtered.unpaid_total);
        document.getElementById('filtered-unpaid-dp').textContent    = formatRupiah(filtered.unpaid_dp_sisa);
        document.getElementById('filtered-unpaid-full').textContent  = formatRupiah(filtered.unpaid_full);

        const unpPct = Number(filtered.unpaid_growth_pct || 0);
        const unpSign = unpPct >= 0 ? '+' : '';
        document.getElementById('filtered-unpaid-growth').textContent = `${unpSign}${unpPct}% vs periode lalu`;

        document.getElementById('filtered-success-orders').textContent  = `${filtered.success_orders || 0} Order`;
        document.getElementById('filtered-aov').textContent             = formatRupiah(filtered.avg_order_value);
        document.getElementById('filtered-unpaid-count').textContent    = `${filtered.unpaid_count || 0} Order`;
        document.getElementById('filtered-cancelled-badge').textContent = `${filtered.cancelled_count || 0} Dibatalkan`;

        // 3. Render 2 Grafik
        renderBreakdownCharts(data.charts || {});

        // 4. Render Top 5 Produk & Channel
        renderTopProducts(data.top_products || []);

        document.getElementById('channel-web-count').textContent = `${data.channel_breakdown?.web_orders || 0} Order`;
        document.getElementById('channel-wa-count').textContent  = `${data.channel_breakdown?.whatsapp_orders || 0} Order`;

        // 5. Render Tabel
        allTransactions = data.transactions || [];
        currentPage = 1;
        applyTableFilters();
    }

    function renderBreakdownCharts(c) {
        const sumRev = (c.revenue_current || []).reduce((a, b) => a + Number(b || 0), 0);
        const sumUnp = (c.unpaid_current || []).reduce((a, b) => a + Number(b || 0), 0);
        document.getElementById('chart-rev-sum').textContent    = formatRupiah(sumRev);
        document.getElementById('chart-unpaid-sum').textContent = formatRupiah(sumUnp);

        if (revChartInstance) { revChartInstance.destroy(); revChartInstance = null; }
        if (unpaidChartInstance) { unpaidChartInstance.destroy(); unpaidChartInstance = null; }

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            elements: { point: { radius: 4, hoverRadius: 6 } },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 16, font: { family: 'Inter', size: 11, weight: 'bold' } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return `${ctx.dataset.label}: ${formatRupiah(ctx.raw)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [4, 4], color: '#EBE4F2' },
                    ticks: {
                        font: { size: 10, weight: 'bold' },
                        callback: function(val) {
                            if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                            if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' Rb';
                            return 'Rp ' + val;
                        }
                    }
                },
                x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }
            },
            interaction: { mode: 'index', intersect: false }
        };

        revChartInstance = new Chart(document.getElementById('revenueChartCanvas').getContext('2d'), {
            type: 'line',
            data: {
                labels: c.labels || [],
                datasets: [
                    {
                        label: 'Total Kas Masuk (Lunas + DP)',
                        data: c.revenue_current || [],
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.14)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3
                    },
                    {
                        label: 'Bayar Penuh (Lunas 100%)',
                        data: c.revenue_lunas || [],
                        borderColor: '#9368B7',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2.5
                    },
                    {
                        label: 'Uang DP Masuk (30%)',
                        data: c.revenue_dp || [],
                        borderColor: '#0D9488',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2
                    }
                ]
            },
            options: commonOptions
        });

        unpaidChartInstance = new Chart(document.getElementById('unpaidChartCanvas').getContext('2d'), {
            type: 'line',
            data: {
                labels: c.labels || [],
                datasets: [
                    {
                        label: 'Total Tagihan Belum Dibayar',
                        data: c.unpaid_current || [],
                        borderColor: '#D97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.14)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3
                    },
                    {
                        label: 'Sisa Pelunasan DP (70%)',
                        data: c.unpaid_dp_sisa || [],
                        borderColor: '#EA580C',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2.5
                    },
                    {
                        label: 'Belum Bayar Sama Sekali (Unpaid 100%)',
                        data: c.unpaid_only || [],
                        borderColor: '#E11D48',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2
                    }
                ]
            },
            options: commonOptions
        });
    }

    function renderTopProducts(list) {
        const container = document.getElementById('top-products-container');
        if (!container) return;

        if (!list || list.length === 0) {
            container.innerHTML = `
                <div class="clay-well p-6 text-center">
                    <i class="ph ph-package text-2xl text-admin-textMuted mb-1 block"></i>
                    <p class="text-xs font-bold text-admin-textMuted">Belum ada produk terjual (Lunas/DP) pada rentang tanggal & channel ini.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = list.map((p, i) => `
            <div class="clay-well p-3.5">
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-black text-admin-textMain">${i + 1}. ${escapeHtml(p.name)} <span class="text-admin-textMuted font-bold">(${Number(p.qty)} pcs)</span></span>
                    <span class="font-black text-admin-sideBg num-tabular">${formatRupiah(p.revenue)} (${Number(p.percentage)}%)</span>
                </div>
                <div class="w-full h-2 bg-white rounded-full overflow-hidden">
                    <div class="h-full bg-admin-sideBg rounded-full" style="width: ${Math.min(100, Number(p.percentage))}%"></div>
                </div>
            </div>
        `).join('');
    }

    function updateStatusLabel() {
        const checkedBoxes = Array.from(document.querySelectorAll('.cb-status:checked'));
        selectedStatuses = checkedBoxes.map((cb) => cb.value);

        const lbl = document.getElementById('lbl-status');
        if (!lbl) return;

        if (selectedStatuses.length === 0) {
            lbl.textContent = 'Semua Status Bayar';
        } else if (selectedStatuses.length === 1) {
            const nextSpan = checkedBoxes[0].nextElementSibling;
            lbl.textContent = nextSpan ? nextSpan.textContent : '1 Status Dipilih';
        } else {
            lbl.textContent = `${selectedStatuses.length} Status Dipilih`;
        }
    }

    function applyTableFilters() {
        const searchEl = document.getElementById('filter-search');
        const keyword = (searchEl ? searchEl.value : '').toLowerCase();

        filteredTransactions = allTransactions.filter((item) => {
            const matchSearch = (item.order_number || '').toLowerCase().includes(keyword) ||
                                (item.recipient_name || '').toLowerCase().includes(keyword) ||
                                (item.recipient_whatsapp || '').toLowerCase().includes(keyword);

            let matchStatus = true;
            if (selectedStatuses.length > 0) {
                const actualStatus = item.order_status === 'dibatalkan'
                    ? 'dibatalkan'
                    : (item.payment_status === 'failed' ? 'unpaid' : item.payment_status);
                matchStatus = selectedStatuses.includes(actualStatus);
            }

            return matchSearch && matchStatus;
        });

        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('table-body');
        const emptyState = document.getElementById('empty-state');
        if (!tbody || !emptyState) return;

        const totalItems = filteredTransactions.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const slice = filteredTransactions.slice((currentPage - 1) * perPage, currentPage * perPage);

        if (slice.length === 0) {
            tbody.innerHTML = '';
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
        } else {
            emptyState.classList.remove('flex');
            emptyState.classList.add('hidden');

            tbody.innerHTML = slice.map((item) => {
                let badge = '';
                if (item.order_status === 'dibatalkan') {
                    badge = '<span class="px-3 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-lg text-[10px] font-black uppercase">Dibatalkan</span>';
                } else if (item.payment_status === 'paid_full') {
                    badge = '<span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-black uppercase">Lunas 100%</span>';
                } else if (item.payment_status === 'paid_dp') {
                    badge = '<span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-[10px] font-black uppercase">DP 30%</span>';
                } else {
                    badge = '<span class="px-3 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-[10px] font-black uppercase">Unpaid</span>';
                }

                const channelBadge = item.order_source === 'whatsapp'
                    ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[10px] font-black"><i class="ph ph-whatsapp-logo"></i> WA BOT</span>'
                    : '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-50 text-admin-sideBg border border-purple-200 rounded text-[10px] font-black"><i class="ph ph-globe"></i> WEB</span>';

                const safeDateStr = (item.created_at || '').replace(' ', 'T');
                const d = new Date(safeDateStr);
                const dateFormatted = isNaN(d.getTime())
                    ? escapeHtml(item.created_at)
                    : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                return `
                    <tr class="hover:bg-admin-bg/50 transition-colors">
                        <td class="px-6 py-4 font-black text-admin-sideBg whitespace-nowrap num-tabular">#${escapeHtml(item.order_number)}</td>
                        <td class="px-6 py-4 font-bold text-admin-textMuted whitespace-nowrap text-xs num-tabular">${dateFormatted} WIB</td>
                        <td class="px-6 py-4">
                            <p class="font-black text-admin-textMain text-xs">${escapeHtml(item.recipient_name)}</p>
                            <p class="text-[11px] font-semibold text-admin-textMuted num-tabular">${escapeHtml(item.recipient_whatsapp || '-')}</p>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            ${channelBadge}
                            <span class="ml-1 text-[10px] font-black text-admin-textMuted bg-gray-100 px-2 py-0.5 rounded uppercase">${escapeHtml(item.payment_method)}</span>
                        </td>
                        <td class="px-6 py-4 font-black text-emerald-600 text-right whitespace-nowrap num-tabular">${formatRupiah(item.paid_amount)}</td>
                        <td class="px-6 py-4 font-black ${item.remaining_amount > 0 ? 'text-amber-600' : 'text-gray-400'} text-right whitespace-nowrap num-tabular">${formatRupiah(item.remaining_amount)}</td>
                        <td class="px-6 py-4 font-black text-admin-textMain text-right whitespace-nowrap num-tabular">${formatRupiah(item.total_amount)}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">${badge}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap no-print">
                            <a href="/nota?order_number=${encodeURIComponent(item.order_number)}" target="_blank" class="ux-interactive inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-admin-bg hover:bg-admin-sideBg hover:text-white text-admin-sideBg font-black text-xs">
                                <i class="ph ph-receipt text-sm"></i>
                                <span>Nota</span>
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        document.getElementById('pagination-info').textContent = `Menampilkan ${slice.length} dari ${totalItems} transaksi`;
        document.getElementById('page-indicator').textContent  = `Hal ${currentPage} / ${totalPages}`;
        document.getElementById('btn-prev-page').disabled      = currentPage <= 1;
        document.getElementById('btn-next-page').disabled      = currentPage >= totalPages;
    }

    function setGranularityUI(gran) {
        filterState.granularity = gran;
        document.querySelectorAll('.granularity-btn').forEach((btn) => {
            if (btn.dataset.granularity === gran) {
                btn.classList.remove('text-admin-textMuted');
                btn.classList.add('bg-admin-sideBg', 'text-white', 'shadow-sm');
            } else {
                btn.classList.remove('bg-admin-sideBg', 'text-white', 'shadow-sm');
                btn.classList.add('text-admin-textMuted');
            }
        });
    }

    // --- NAMED EVENT HANDLERS FOR ZERO MEMORY LEAK ---
    function handleDocClick(e) {
        if (!e.target.closest('#wrapper-status')) {
            document.getElementById('menu-status')?.classList.add('hidden');
            document.getElementById('btn-status')?.setAttribute('aria-expanded', 'false');
        }

        const presetBtn = e.target.closest('.preset-date-btn');
        if (presetBtn) {
            document.querySelectorAll('.preset-date-btn').forEach((b) => {
                b.classList.remove('bg-white', 'text-admin-sideBg', 'shadow-sm');
                b.classList.add('text-admin-textMuted');
            });
            presetBtn.classList.remove('text-admin-textMuted');
            presetBtn.classList.add('bg-white', 'text-admin-sideBg', 'shadow-sm');

            const preset = presetBtn.dataset.preset;
            const end = new Date();
            let start = new Date();

            if (preset === '7d') {
                start.setDate(end.getDate() - 6);
                setGranularityUI('daily');
            } else if (preset === '30d') {
                start.setDate(end.getDate() - 29);
                setGranularityUI('weekly');
            } else if (preset === 'month') {
                start = new Date(end.getFullYear(), end.getMonth(), 1);
                setGranularityUI('daily');
            } else if (preset === 'year') {
                start = new Date(end.getFullYear(), 0, 1);
                setGranularityUI('monthly');
            }

            filterState.start_date = toYMD(start);
            filterState.end_date   = toYMD(end);
            if (fpUniversal) fpUniversal.setDate([filterState.start_date, filterState.end_date], false);

            fetchDashboardData();
            showToast('Preset Rentang Aktif', `Menampilkan data ${presetBtn.textContent.trim()} (${filterState.start_date} s/d ${filterState.end_date})`);
            return;
        }

        const sourceBtn = e.target.closest('.source-filter-btn');
        if (sourceBtn) {
            document.querySelectorAll('.source-filter-btn').forEach((b) => {
                b.classList.remove('bg-white', 'text-admin-sideBg', 'shadow-sm');
                b.classList.add('text-admin-textMuted');
            });
            sourceBtn.classList.remove('text-admin-textMuted');
            sourceBtn.classList.add('bg-white', 'text-admin-sideBg', 'shadow-sm');
            filterState.order_source = sourceBtn.dataset.source;
            fetchDashboardData();
            showToast('Filter Channel Diubah', `Menampilkan transaksi dari: ${sourceBtn.textContent.trim()}`);
            return;
        }

        const granBtn = e.target.closest('.granularity-btn');
        if (granBtn) {
            const gran = granBtn.dataset.granularity;
            setGranularityUI(gran);

            const sDate = new Date(filterState.start_date + 'T00:00:00');
            const eDate = new Date(filterState.end_date + 'T23:59:59');
            const diffDays = Math.round((eDate - sDate) / 86400000) + 1;

            if (gran === 'weekly' && diffDays < 14) {
                const newStart = new Date(eDate);
                newStart.setDate(eDate.getDate() - 27);
                filterState.start_date = toYMD(newStart);
                if (fpUniversal) fpUniversal.setDate([filterState.start_date, filterState.end_date], false);
            } else if (gran === 'monthly' && diffDays < 45) {
                const newStart = new Date(eDate.getFullYear(), eDate.getMonth() - 5, 1);
                filterState.start_date = toYMD(newStart);
                if (fpUniversal) fpUniversal.setDate([filterState.start_date, filterState.end_date], false);
            } else if (gran === 'yearly' && diffDays < 366) {
                const newStart = new Date(eDate.getFullYear() - 3, 0, 1);
                filterState.start_date = toYMD(newStart);
                if (fpUniversal) fpUniversal.setDate([filterState.start_date, filterState.end_date], false);
            }

            fetchDashboardData();
            return;
        }
    }

    function handleDocKeydown(e) {
        if (e.key === 'Escape') {
            document.getElementById('menu-status')?.classList.add('hidden');
            document.getElementById('btn-status')?.setAttribute('aria-expanded', 'false');
        }
    }

    function handlePrintClick() {
        window.print();
    }

    function handleJumpPiutangClick() {
        document.querySelectorAll('.cb-status').forEach((cb) => {
            cb.checked = (cb.value === 'paid_dp' || cb.value === 'unpaid');
        });
        updateStatusLabel();
        currentPage = 1;
        applyTableFilters();
        document.getElementById('section-tabel-riwayat')?.scrollIntoView({ behavior: 'smooth' });
        showToast('Filter Piutang Aktif', 'Menampilkan pesanan yang belum lunas (DP 30% & Unpaid).');
    }

    function handleStatusBtnClick(e) {
        e.stopPropagation();
        const menu = document.getElementById('menu-status');
        const btn  = document.getElementById('btn-status');
        if (!menu || !btn) return;
        const isHidden = menu.classList.contains('hidden');
        menu.classList.toggle('hidden', !isHidden);
        btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    }

    function handleCloseStatusMenuClick() {
        document.getElementById('menu-status')?.classList.add('hidden');
        document.getElementById('btn-status')?.setAttribute('aria-expanded', 'false');
    }

    function handleStatusCheckboxChange(e) {
        if (e.target.classList.contains('cb-status')) {
            updateStatusLabel();
            currentPage = 1;
            applyTableFilters();
        }
    }

    function handleResetTableFilterClick() {
        const searchEl = document.getElementById('filter-search');
        if (searchEl) searchEl.value = '';
        document.querySelectorAll('.cb-status').forEach((cb) => { cb.checked = false; });
        updateStatusLabel();
        currentPage = 1;
        applyTableFilters();
        showToast('Filter Tabel Direset', 'Menampilkan seluruh transaksi pada periode aktif.');
    }

    function handleSearchInput() {
        currentPage = 1;
        applyTableFilters();
    }

    function handlePrevPageClick() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    }

    function handleNextPageClick() {
        const totalPages = Math.ceil(filteredTransactions.length / perPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    }

    function handleExportCsvClick() {
        if (filteredTransactions.length === 0) {
            showToast('Data Kosong', 'Tidak ada transaksi yang dapat diunduh.', false);
            return;
        }
        let csv = 'No Pesanan,Waktu Checkout,Pelanggan,No WA,Channel,Metode,Kas Masuk,Sisa Tagihan,Total Order,Status Bayar\n';
        filteredTransactions.forEach((r) => {
            csv += `"${r.order_number}","${r.created_at}","${r.recipient_name}","${r.recipient_whatsapp}","${r.order_source}","${r.payment_method}",${r.paid_amount},${r.remaining_amount},${r.total_amount},"${r.payment_status}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `Laporan_Finansial_RayaKitchen_${filterState.start_date}_${filterState.end_date}.csv`;
        link.click();
        URL.revokeObjectURL(url);
        showToast('CSV Berhasil Diunduh', `${filteredTransactions.length} baris transaksi berhasil diekspor.`);
    }

    function mount() {
        document.addEventListener('click', handleDocClick);
        document.addEventListener('keydown', handleDocKeydown);
        document.addEventListener('change', handleStatusCheckboxChange);

        document.getElementById('btn-print-report')?.addEventListener('click', handlePrintClick);
        document.getElementById('btn-jump-piutang')?.addEventListener('click', handleJumpPiutangClick);
        document.getElementById('btn-status')?.addEventListener('click', handleStatusBtnClick);
        document.getElementById('btn-close-status-menu')?.addEventListener('click', handleCloseStatusMenuClick);
        document.getElementById('btn-reset-table-filter')?.addEventListener('click', handleResetTableFilterClick);
        document.getElementById('filter-search')?.addEventListener('input', handleSearchInput);
        document.getElementById('btn-prev-page')?.addEventListener('click', handlePrevPageClick);
        document.getElementById('btn-next-page')?.addEventListener('click', handleNextPageClick);
        document.getElementById('btn-export-csv')?.addEventListener('click', handleExportCsvClick);

        window.addEventListener('beforeunload', teardown);

        fpUniversal = flatpickr("#universal-date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            locale: "id",
            defaultDate: [filterState.start_date, filterState.end_date],
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    filterState.start_date = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                    filterState.end_date   = flatpickr.formatDate(selectedDates[1], "Y-m-d");

                    document.querySelectorAll('.preset-date-btn').forEach((b) => {
                        b.classList.remove('bg-white', 'text-admin-sideBg', 'shadow-sm');
                        b.classList.add('text-admin-textMuted');
                    });

                    const diffDays = Math.round((selectedDates[1] - selectedDates[0]) / 86400000) + 1;
                    if (diffDays <= 14) setGranularityUI('daily');
                    else if (diffDays <= 62) setGranularityUI('weekly');
                    else if (diffDays <= 366) setGranularityUI('monthly');
                    else setGranularityUI('yearly');

                    fetchDashboardData();
                    showToast('Filter Tanggal Bebas Aktif', `Seluruh data difilter: ${filterState.start_date} s/d ${filterState.end_date}`);
                }
            }
        });

        fetchDashboardData();
    }

    function teardown() {
        document.removeEventListener('click', handleDocClick);
        document.removeEventListener('keydown', handleDocKeydown);
        document.removeEventListener('change', handleStatusCheckboxChange);

        document.getElementById('btn-print-report')?.removeEventListener('click', handlePrintClick);
        document.getElementById('btn-jump-piutang')?.removeEventListener('click', handleJumpPiutangClick);
        document.getElementById('btn-status')?.removeEventListener('click', handleStatusBtnClick);
        document.getElementById('btn-close-status-menu')?.removeEventListener('click', handleCloseStatusMenuClick);
        document.getElementById('btn-reset-table-filter')?.removeEventListener('click', handleResetTableFilterClick);
        document.getElementById('filter-search')?.removeEventListener('input', handleSearchInput);
        document.getElementById('btn-prev-page')?.removeEventListener('click', handlePrevPageClick);
        document.getElementById('btn-next-page')?.removeEventListener('click', handleNextPageClick);
        document.getElementById('btn-export-csv')?.removeEventListener('click', handleExportCsvClick);

        window.removeEventListener('beforeunload', teardown);

        if (activeAbortController) activeAbortController.abort();
        clearAllTimers();
        if (revChartInstance) revChartInstance.destroy();
        if (unpaidChartInstance) unpaidChartInstance.destroy();
        if (fpUniversal) fpUniversal.destroy();
    }

    mount();
})();
</script>
@endpush