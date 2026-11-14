@extends('admin.admin_master')

@section('title', 'Dashboard Finansial - Admin Raya Kitchen')
@section('header_title', 'Dashboard Finansial')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    :root {
        /* 60-30-10 Claymorphism Cockpit Tokens */
        --clay-bg: #F5F2F8;
        --clay-surface: #FFFFFF;
        --clay-surface-alt: #FAF8FC;
        --clay-dark: #1E1B24;
        --clay-primary: #9368B7;
        --clay-primary-hover: #7E54A3;
        --clay-accent-soft: #E8D5F6;
        --clay-border: #EBE4F2;
        --clay-text-main: #2D2A32;
        --clay-text-muted: #6E6A75;

        /* Semantic Status Tokens */
        --clay-emerald: #10B981;
        --clay-teal: #0D9488;
        --clay-amber: #F59E0B;
        --clay-orange: #EA580C;
        --clay-rose: #F43F5E;

        /* Tactile Claymorphism Shadows */
        --clay-shadow-card:
            inset 0 -4px 6px rgba(147, 104, 183, 0.08),
            inset 0 4px 6px rgba(255, 255, 255, 0.95),
            0 10px 24px -4px rgba(147, 104, 183, 0.1);
        --clay-shadow-dark:
            inset 0 -4px 8px rgba(0, 0, 0, 0.45),
            inset 0 2px 4px rgba(255, 255, 255, 0.08),
            0 14px 28px -6px rgba(30, 27, 36, 0.28);
        --clay-shadow-well:
            inset 0 3px 6px rgba(147, 104, 183, 0.14),
            inset 0 -2px 4px rgba(255, 255, 255, 0.9);
        --clay-shadow-btn:
            inset 0 -3px 4px rgba(0, 0, 0, 0.15),
            inset 0 3px 4px rgba(255, 255, 255, 0.35),
            0 6px 14px rgba(147, 104, 183, 0.28);
    }

    /* Fluid Typography Engine */
    .fluid-h2 { font-size: clamp(1.15rem, 1.2vw + 0.85rem, 1.5rem); line-height: 1.25; }
    .fluid-stat-lg { font-size: clamp(1.5rem, 2vw + 0.9rem, 2.25rem); line-height: 1.15; }
    .fluid-stat-md { font-size: clamp(1.35rem, 1.6vw + 0.8rem, 1.85rem); line-height: 1.2; }

    /* Claymorphism Surfaces */
    .clay-card {
        background-color: var(--clay-surface);
        border: 2px solid #FFFFFF;
        border-radius: 2rem;
        box-shadow: var(--clay-shadow-card);
    }
    .clay-card-dark {
        background-color: var(--clay-dark);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2rem;
        box-shadow: var(--clay-shadow-dark);
    }
    .clay-well {
        background-color: var(--clay-bg);
        border: 1px solid #FFFFFF;
        border-radius: 1.25rem;
        box-shadow: var(--clay-shadow-well);
    }
    .clay-btn-primary {
        background-color: var(--clay-primary);
        color: #FFFFFF;
        box-shadow: var(--clay-shadow-btn);
    }
    .clay-btn-primary:hover {
        background-color: var(--clay-primary-hover);
    }

    /* 5-State Feedback Loop Machine (60fps Transform & Opacity Only) */
    .ux-interactive {
        min-height: 44px;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
    }
    .ux-interactive:hover:not(:disabled) {
        transform: translateY(-3px);
    }
    .ux-interactive:active:not(:disabled) {
        transform: scale(0.95);
    }
    .ux-interactive:focus-visible {
        outline: 3px solid var(--clay-primary);
        outline-offset: 2px;
    }
    .ux-interactive:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    /* Staggered Entrance Animations */
    @keyframes clayFadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stagger-item {
        opacity: 0;
        animation: clayFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .delay-1 { animation-delay: 40ms; }
    .delay-2 { animation-delay: 90ms; }
    .delay-3 { animation-delay: 140ms; }
    .delay-4 { animation-delay: 190ms; }
    .delay-5 { animation-delay: 240ms; }

    /* Custom Checkbox */
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
        background-color: var(--clay-primary);
        border-color: var(--clay-primary);
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

    /* Flatpickr Clay Override */
    .flatpickr-calendar {
        font-family: inherit;
        box-shadow: 0 16px 32px -8px rgba(147, 104, 183, 0.25);
        border: 2px solid #FFFFFF;
        border-radius: 1.5rem;
    }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.inRange {
        background: var(--clay-primary) !important;
        border-color: var(--clay-primary) !important;
        color: #FFFFFF !important;
    }

    /* Respect OS Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
        *, ::before, ::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }

    /* Print Mode */
    @media print {
        #sidebar, header, .no-print { display: none !important; }
        #main-content { overflow: visible !important; height: auto !important; }
        body { overflow: visible !important; background: #FFFFFF !important; }
        .clay-card, .clay-card-dark { box-shadow: none !important; border: 1px solid #CBD5E1 !important; }
    }
</style>
@endsection

@section('content')

    <!-- TOAST NOTIFICATION (Contextual Feedback) -->
    <div id="ux-toast" role="status" aria-live="polite" class="fixed bottom-6 right-6 z-[200] hidden items-center gap-3 px-5 py-3.5 clay-card border-2 border-white shadow-2xl no-print">
        <div id="ux-toast-icon" class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
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
                <!-- Segmented Quick Date Presets -->
                <div class="clay-well p-1 flex items-center gap-1" role="group" aria-label="Preset Rentang Tanggal">
                    <button type="button" data-preset="7d" class="preset-date-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black bg-white text-admin-sideBg shadow-sm">7 Hari</button>
                    <button type="button" data-preset="30d" class="preset-date-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">30 Hari</button>
                    <button type="button" data-preset="month" class="preset-date-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">Bulan Ini</button>
                    <button type="button" data-preset="year" class="preset-date-btn ux-interactive px-3.5 py-2 rounded-xl text-xs font-black text-admin-textMuted hover:text-admin-textMain">Tahun Ini</button>
                </div>

                <!-- Flatpickr Custom Date Range Picker -->
                <div class="relative min-w-[245px] flex-1 sm:flex-none" id="wrapper-custom-date">
                    <i class="ph ph-calendar-blank absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-sideBg pointer-events-none"></i>
                    <input type="text" id="universal-date-range" aria-label="Pilih Rentang Tanggal Bebas" placeholder="Pilih Rentang Tanggal" class="ux-interactive w-full pl-11 pr-4 py-2.5 clay-well text-admin-textMain text-xs sm:text-sm font-black cursor-pointer focus:outline-none" readonly>
                </div>

                <!-- Segmented Channel Filter (100% Vector Icons) -->
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

                <!-- Tombol Cetak Laporan -->
                <button type="button" id="btn-print-report" class="ux-interactive clay-btn-primary px-4 py-2.5 rounded-2xl font-black text-xs sm:text-sm inline-flex items-center gap-2">
                    <i class="ph ph-printer text-lg"></i>
                    <span>Cetak PDF</span>
                </button>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- LAPIS 2A: AKUMULASI SEMUA LINI MASA (ALL-TIME / KEBAL FILTER)         -->
    <!-- ===================================================================== -->
    <div class="mb-3 flex items-center justify-between stagger-item delay-2">
        <div class="flex items-center gap-2">
            <i class="ph ph-infinity text-lg text-admin-sideBg"></i>
            <h3 class="text-xs font-black uppercase tracking-widest text-admin-textMuted">Total Akumulasi Sejak Awal Operasional (Tidak Terpengaruh Filter)</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-7 stagger-item delay-2">
        <!-- All-Time Kas Masuk -->
        <div class="clay-card-dark text-white p-6 lg:p-7 flex flex-col justify-between">
            <div class="flex justify-between items-start gap-4 mb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-400 flex items-center justify-center shrink-0">
                        <i class="ph ph-vault text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">All-Time Kas Masuk</span>
                        <h4 class="text-sm font-bold text-slate-300 mt-1">Grand Total Pendapatan Diterima</h4>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-xl bg-white/10 text-xs font-black text-slate-200" id="alltime-orders-count">0 Order Sukses</span>
            </div>

            <div>
                <h3 class="fluid-stat-lg font-black text-white tracking-tight mb-4" id="alltime-revenue-total">Rp 0</h3>
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold block mb-0.5">Dari Pesanan Lunas (100%):</span>
                        <span class="font-black text-emerald-400 text-sm" id="alltime-revenue-lunas">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block mb-0.5">Dari Uang Muka DP (30%):</span>
                        <span class="font-black text-teal-300 text-sm" id="alltime-revenue-dp">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- All-Time Piutang / Belum Terbayar -->
        <div class="clay-card-dark text-white p-6 lg:p-7 flex flex-col justify-between">
            <div class="flex justify-between items-start gap-4 mb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/20 border border-amber-400/30 text-amber-400 flex items-center justify-center shrink-0">
                        <i class="ph ph-hand-coins text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30">All-Time Piutang</span>
                        <h4 class="text-sm font-bold text-slate-300 mt-1">Grand Total Tagihan Belum Terbayar</h4>
                    </div>
                </div>
                <button type="button" id="btn-jump-piutang" class="ux-interactive px-3.5 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-400/30 text-xs font-black flex items-center gap-1.5 no-print">
                    <span>Filter Tabel Piutang</span>
                    <i class="ph ph-arrow-down-right text-sm"></i>
                </button>
            </div>

            <div>
                <h3 class="fluid-stat-lg font-black text-amber-400 tracking-tight mb-4" id="alltime-unpaid-total">Rp 0</h3>
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold block mb-0.5">Sisa Pelunasan DP (70%):</span>
                        <span class="font-black text-amber-300 text-sm" id="alltime-unpaid-dp">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block mb-0.5">Belum Bayar Sama Sekali (Unpaid):</span>
                        <span class="font-black text-rose-400 text-sm" id="alltime-unpaid-full">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- LAPIS 2B: METRIK BERDASARKAN FILTER TANGGAL & CHANNEL                 -->
    <!-- ===================================================================== -->
    <div class="mb-3 flex items-center justify-between stagger-item delay-3">
        <div class="flex items-center gap-2">
            <i class="ph ph-funnel text-lg text-emerald-600"></i>
            <h3 class="text-xs font-black uppercase tracking-widest text-admin-textMuted">Ringkasan Performa Sesuai Filter Universal Terpilih</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 stagger-item delay-3">
        <!-- Card 1: Kas Masuk Terfilter -->
        <div class="clay-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                    <i class="ph ph-wallet text-2xl"></i>
                </div>
                <span id="filtered-rev-growth" class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">+0%</span>
            </div>
            <div>
                <p class="text-[11px] font-black text-admin-textMuted uppercase tracking-wider mb-1">Kas Masuk (Sesuai Filter)</p>
                <h3 class="fluid-stat-md font-black text-admin-textMain tracking-tight mb-3" id="filtered-revenue-total">Rp 0</h3>
                <div class="flex justify-between text-xs font-bold text-admin-textMuted pt-3 border-t border-admin-border">
                    <span>Lunas 100%: <strong class="text-admin-sideBg" id="filtered-revenue-lunas">Rp 0</strong></span>
                    <span>DP 30%: <strong class="text-teal-600" id="filtered-revenue-dp">Rp 0</strong></span>
                </div>
            </div>
        </div>

        <!-- Card 2: Tagihan Belum Terbayar Terfilter -->
        <div class="clay-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200">
                    <i class="ph ph-clock-countdown text-2xl"></i>
                </div>
                <span id="filtered-unpaid-growth" class="px-3 py-1 rounded-xl text-xs font-black bg-amber-50 text-amber-700 border border-amber-200">0%</span>
            </div>
            <div>
                <p class="text-[11px] font-black text-admin-textMuted uppercase tracking-wider mb-1">Tagihan Gantung (Sesuai Filter)</p>
                <h3 class="fluid-stat-md font-black text-amber-600 tracking-tight mb-3" id="filtered-unpaid-total">Rp 0</h3>
                <div class="flex justify-between text-xs font-bold text-admin-textMuted pt-3 border-t border-admin-border">
                    <span>Sisa DP 70%: <strong class="text-orange-600" id="filtered-unpaid-dp">Rp 0</strong></span>
                    <span>Unpaid 100%: <strong class="text-rose-600" id="filtered-unpaid-full">Rp 0</strong></span>
                </div>
            </div>
        </div>

        <!-- Card 3: Volume Transaksi & Rata-Rata Order -->
        <div class="clay-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-admin-sideBg flex items-center justify-center border border-purple-200">
                    <i class="ph ph-receipt text-2xl"></i>
                </div>
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-rose-50 text-rose-600 border border-rose-200" id="filtered-cancelled-badge">0 Dibatalkan</span>
            </div>
            <div>
                <p class="text-[11px] font-black text-admin-textMuted uppercase tracking-wider mb-1">Transaksi Sukses (Sesuai Filter)</p>
                <h3 class="fluid-stat-md font-black text-admin-textMain tracking-tight mb-3" id="filtered-success-orders">0 Order</h3>
                <div class="flex justify-between text-xs font-bold text-admin-textMuted pt-3 border-t border-admin-border">
                    <span>Rata-rata/Order: <strong class="text-admin-sideBg" id="filtered-aov">Rp 0</strong></span>
                    <span>Menunggu Bayar: <strong class="text-rose-600" id="filtered-unpaid-count">0 Order</strong></span>
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
                <div class="w-11 h-11 rounded-2xl bg-purple-50 text-admin-sideBg flex items-center justify-center border border-purple-200 shrink-0 mt-0.5">
                    <i class="ph ph-chart-line-up text-xl"></i>
                </div>
                <div>
                    <h2 class="fluid-h2 font-black text-admin-textMain">Grafik Rincian Kas Masuk vs Tagihan Belum Dibayar</h2>
                    <p class="text-xs font-bold text-admin-textMuted mt-0.5">
                        Data grafik di bawah otomatis mengikuti rentang tanggal dan channel yang dipilih pada Filter Universal di atas.
                    </p>
                </div>
            </div>

            <!-- Pengelompokan Titik Waktu di dalam Rentang Tanggal Terpilih -->
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
                        <span class="text-sm font-black text-emerald-600" id="chart-rev-sum">Rp 0</span>
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
                        <span class="text-sm font-black text-amber-600" id="chart-unpaid-sum">Rp 0</span>
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
        <!-- Top 5 Varian Produk -->
        <div class="lg:col-span-2 clay-card p-6 lg:p-7">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-admin-sideBg flex items-center justify-center border border-purple-200">
                    <i class="ph ph-trophy text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-admin-textMain">5 Varian Produk Penyumbang Omzet Terbesar</h3>
                    <p class="text-xs font-bold text-admin-textMuted">Dihitung khusus dari transaksi pada rentang tanggal & channel terpilih.</p>
                </div>
            </div>
            <div id="top-products-container" class="space-y-3.5">
                <!-- Di-inject via JS -->
            </div>
        </div>

        <!-- Komposisi Channel & Export -->
        <div class="clay-card p-6 lg:p-7 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-admin-sideBg flex items-center justify-center border border-purple-200">
                        <i class="ph ph-share-network text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-admin-textMain">Distribusi Channel Pesanan</h3>
                        <p class="text-xs font-bold text-admin-textMuted">Perbandingan asal pesanan pada filter aktif.</p>
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
                        <span class="text-base font-black text-admin-sideBg" id="channel-web-count">0 Order</span>
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
                        <span class="text-base font-black text-emerald-600" id="channel-wa-count">0 Order</span>
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
                <div class="w-10 h-10 rounded-xl bg-white text-admin-sideBg flex items-center justify-center border border-admin-border shadow-sm">
                    <i class="ph ph-table text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base lg:text-lg font-black text-admin-textMain">Rincian Transaksi & Status Tagihan</h2>
                    <p class="text-xs font-bold text-admin-textMuted">Menampilkan daftar pesanan sesuai rentang tanggal & channel di atas.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 no-print">
                <!-- Search Input -->
                <div class="relative min-w-[240px] flex-1 sm:flex-none">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-base text-admin-textMuted pointer-events-none"></i>
                    <input type="text" id="filter-search" aria-label="Cari pesanan" placeholder="Cari No. Order / Nama / WA..." class="ux-interactive w-full pl-10 pr-4 py-2.5 bg-white border border-admin-border rounded-2xl text-xs sm:text-sm font-bold text-admin-textMain focus:outline-none">
                </div>

                <!-- Multi-Select Dropdown Status Bayar -->
                <div class="relative min-w-[190px]" id="wrapper-status">
                    <button type="button" id="btn-status" aria-haspopup="true" aria-expanded="false" class="ux-interactive flex items-center justify-between w-full bg-white border border-admin-border text-admin-textMain text-xs sm:text-sm font-black rounded-2xl pl-4 pr-3 py-2.5">
                        <span id="lbl-status" class="truncate">Semua Status Bayar</span>
                        <i class="ph ph-caret-down text-admin-textMuted ml-2"></i>
                    </button>

                    <div id="menu-status" role="menu" class="absolute right-0 mt-2 w-64 clay-card p-3 hidden z-50 border border-admin-border">
                        <div class="flex justify-between items-center pb-2 mb-2 border-b border-admin-border">
                            <span class="text-[11px] font-black uppercase text-admin-textMuted">Filter Status (Tekan ESC tutup)</span>
                            <button type="button" id="btn-close-status-menu" class="w-7 h-7 rounded-lg hover:bg-admin-bg flex items-center justify-center text-admin-textMuted" aria-label="Tutup Menu Filter">
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

                <!-- Reset Filter Tabel -->
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
                <tbody id="table-body" class="divide-y divide-admin-border">
                    <tr class="animate-pulse">
                        <td colspan="9" class="px-6 py-8 text-center text-xs font-bold text-admin-textMuted">Memuat rincian transaksi...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty State Illustration -->
        <div id="empty-state" class="hidden flex-col items-center justify-center py-16 px-4 text-center">
            <div class="w-16 h-16 clay-well flex items-center justify-center mb-4 text-admin-textMuted">
                <i class="ph ph-receipt-x text-3xl"></i>
            </div>
            <h3 class="text-base font-black text-admin-textMain mb-1">Tidak Ada Transaksi pada Filter Ini</h3>
            <p class="text-xs font-bold text-admin-textMuted max-w-xs">Coba perluas rentang tanggal, ganti channel pesanan, atau reset filter status bayar.</p>
        </div>

        <!-- Pagination Bar -->
        <div class="p-5 border-t border-admin-border flex flex-col sm:flex-row items-center justify-between gap-3 bg-admin-bg/30 no-print">
            <span class="text-xs font-bold text-admin-textMuted" id="pagination-info">Menampilkan 0 transaksi</span>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-prev-page" class="ux-interactive px-4 py-2 rounded-xl border border-admin-border bg-white text-xs font-black text-admin-textMain flex items-center gap-1.5">
                    <i class="ph ph-caret-left text-sm"></i>
                    <span>Sebelumnya</span>
                </button>
                <span id="page-indicator" class="text-xs font-black px-3 py-2 text-admin-sideBg">Hal 1 / 1</span>
                <button type="button" id="btn-next-page" class="ux-interactive px-4 py-2 rounded-xl border border-admin-border bg-white text-xs font-black text-admin-textMain flex items-center gap-1.5">
                    <span>Selanjutnya</span>
                    <i class="ph ph-caret-right text-sm"></i>
                </button>
            </div>
        </div>

    </section>
@endsection


{{-- ========================================================================= --}}
{{-- BLOK SCRIPT 1 (AKTIF SEKARANG): DYNAMIC DUMMY ENGINE                      --}}
{{-- Menghitung ulang seluruh metrik, grafik, top 5 produk, channel, dan tabel --}}
{{-- secara dinamis 100% mengikuti Filter Universal (Tanggal & Channel)!       --}}
{{-- ========================================================================= --}}
@push('script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function() {
    let revChartInstance = null;
    let unpaidChartInstance = null;
    let fpUniversal = null;
    let activeAbortController = null;
    let toastTimerId = null;

    // Default: 7 Hari Terakhir dari Tanggal Hari Ini
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

    const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);

    // --- TOAST NOTIFICATION ---
    function showToast(title, message, isSuccess = true) {
        if (toastTimerId) clearTimeout(toastTimerId);
        const toast = document.getElementById('ux-toast');
        const icon  = document.getElementById('ux-toast-icon');
        document.getElementById('ux-toast-title').textContent = title;
        document.getElementById('ux-toast-msg').textContent = message;

        icon.className = isSuccess
            ? 'w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0'
            : 'w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0';
        icon.innerHTML = isSuccess
            ? '<i class="ph ph-check-circle text-xl"></i>'
            : '<i class="ph ph-warning-circle text-xl"></i>';

        toast.classList.remove('hidden');
        toast.classList.add('flex');

        toastTimerId = setTimeout(() => {
            toast.classList.remove('flex');
            toast.classList.add('hidden');
        }, 3200);
    }

    // --- MASTER DATABASE DUMMY (140 TRANSAKSI TERSEBAR 400 HARI TERAKHIR) ---
    // Dibuat deterministik supaya saat difilter tanggal/channel apa pun hasilnya nyata & konsisten!
    const CATALOG_PRODUCTS = [
        { id: 1, name: 'Roti Manis - Coklat Keju Spesial', price: 14000 },
        { id: 2, name: 'Roti Gurih - Abon Sapi Lumer', price: 15000 },
        { id: 3, name: 'Kue Kering - Nastar Wisman Premium', price: 70000 },
        { id: 4, name: 'Roti Manis - Pisang Coklat Lumer', price: 12000 },
        { id: 5, name: 'Roti Gurih - Smoke Beef Cheese', price: 16000 },
        { id: 6, name: 'Roti Tawar - Gandum Utuh Premium', price: 24000 },
        { id: 7, name: 'Kue Kering - Kastengel Keju Edam', price: 75000 }
    ];

    const CUSTOMERS = [
        { name: 'Budi Santoso', wa: '6281234567801' },
        { name: 'Siti Aminah', wa: '6281234567802' },
        { name: 'Andi Wijaya', wa: '6281234567803' },
        { name: 'Dewi Lestari', wa: '6281234567804' },
        { name: 'Rizky Pratama', wa: '6281234567805' },
        { name: 'Hendra Gunawan', wa: '6281234567806' },
        { name: 'Maya Sari', wa: '6281234567807' },
        { name: 'Fajar Nugroho', wa: '6281234567808' },
        { name: 'Nadia Putri', wa: '6281234567809' },
        { name: 'ika Rahmawati', wa: '6281234567810' }
    ];

    const MASTER_DUMMY_ORDERS = (function generateMasterDataset() {
        const list = [];
        const baseNow = new Date();

        // Buat transaksi tersebar dari 0 hari lalu (hari ini) sampai 420 hari lalu
        const dayOffsets = [
            0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, // 7 hari terakhir (padat)
            7, 8, 9, 10, 11, 12, 13, 14, 16, 18, 20, 22, 24, 26, 28, 29, // 30 hari terakhir
            32, 35, 40, 45, 50, 55, 60, 70, 80, 95, 110, 130, 155, 180, 210, 250, 300, 340, 380, 410 // bulan & tahun lalu
        ];

        dayOffsets.forEach((offset, idx) => {
            const d = new Date(baseNow);
            d.setDate(baseNow.getDate() - offset);
            const hour = 8 + ((idx * 3) % 11);
            const min  = (idx * 7) % 60;
            d.setHours(hour, min, 0, 0);

            const cust = CUSTOMERS[idx % CUSTOMERS.length];
            const source = (idx % 2 === 0) ? 'whatsapp' : 'web';
            const method = (idx % 5 === 0) ? 'cod' : 'transfer';

            let pStatus = 'paid_full';
            let oStatus = 'selesai';
            if (idx % 7 === 1) { pStatus = 'paid_dp'; oStatus = 'siap_diambil'; }
            else if (idx % 7 === 3) { pStatus = 'paid_dp'; oStatus = 'diproses'; }
            else if (idx % 7 === 5) { pStatus = 'unpaid'; oStatus = 'pending'; }
            else if (idx % 11 === 0 && idx > 0) { pStatus = 'failed'; oStatus = 'dibatalkan'; }

            // Pilih 2 produk per order
            const p1 = CATALOG_PRODUCTS[idx % CATALOG_PRODUCTS.length];
            const p2 = CATALOG_PRODUCTS[(idx + 2) % CATALOG_PRODUCTS.length];
            const q1 = 5 + ((idx * 2) % 15);
            const q2 = 4 + (idx % 10);

            const sub1 = p1.price * q1;
            const sub2 = p2.price * q2;
            const totalAmount = sub1 + sub2;

            let paidAmount = 0;
            let remainingAmount = 0;

            if (oStatus !== 'dibatalkan') {
                if (pStatus === 'paid_full') {
                    paidAmount = totalAmount;
                } else if (pStatus === 'paid_dp') {
                    paidAmount = Math.round(totalAmount * 0.3);
                    remainingAmount = totalAmount - paidAmount;
                } else {
                    remainingAmount = totalAmount;
                }
            }

            const ymd = toYMD(d);
            const hms = `${String(hour).padStart(2, '0')}:${String(min).padStart(2, '0')}:00`;

            list.push({
                order_number: `RK-${ymd.replace(/-/g, '')}-${1000 + idx}`,
                created_at: `${ymd} ${hms}`,
                timestamp: d.getTime(),
                recipient_name: cust.name,
                recipient_whatsapp: cust.wa,
                order_source: source,
                payment_method: method,
                payment_status: pStatus,
                order_status: oStatus,
                total_amount: totalAmount,
                paid_amount: paidAmount,
                remaining_amount: remainingAmount,
                items_count: q1 + q2,
                items: [
                    { id: p1.id, name: p1.name, qty: q1, subtotal: sub1 },
                    { id: p2.id, name: p2.name, qty: q2, subtotal: sub2 }
                ]
            });
        });

        return list;
    })();

    // --- FUNGSI KALKULASI METRIK DARI ARRAY ORDER (IDENTIK DENGAN PHP CONTROLLER) ---
    function computeMetricsFromOrders(orders) {
        let revenueLunas = 0, revenueDpPaid = 0, unpaidFull = 0, unpaidDpSisa = 0;
        let paidFullCount = 0, paidDpCount = 0, unpaidCount = 0, cancelledCount = 0;

        orders.forEach(o => {
            if (o.order_status === 'dibatalkan') {
                cancelledCount++;
                return;
            }
            if (o.payment_status === 'paid_full') {
                revenueLunas += o.total_amount;
                paidFullCount++;
            } else if (o.payment_status === 'paid_dp') {
                revenueDpPaid += o.paid_amount;
                unpaidDpSisa  += o.remaining_amount;
                paidDpCount++;
            } else {
                unpaidFull += o.total_amount;
                unpaidCount++;
            }
        });

        const revenueTotal  = revenueLunas + revenueDpPaid;
        const unpaidTotal   = unpaidFull + unpaidDpSisa;
        const successOrders = paidFullCount + paidDpCount;

        return {
            revenue_total: revenueTotal,
            revenue_lunas: revenueLunas,
            revenue_dp_paid: revenueDpPaid,
            unpaid_total: unpaidTotal,
            unpaid_full: unpaidFull,
            unpaid_dp_sisa: unpaidDpSisa,
            success_orders: successOrders,
            unpaid_count: unpaidCount,
            cancelled_count: cancelledCount,
            avg_order_value: successOrders > 0 ? Math.round(revenueTotal / successOrders) : 0
        };
    }

    // --- ENGINE DUMMY DINAMIS YANG PATUH 100% PADA FILTER UNIVERSAL ---
    function getDummyApiResponse(state) {
        const startTs = new Date(state.start_date + 'T00:00:00').getTime();
        const endTs   = new Date(state.end_date + 'T23:59:59').getTime();
        const diffMs  = Math.max(86400000, endTs - startTs + 1000);

        const prevStartTs = startTs - diffMs;
        const prevEndTs   = startTs - 1000;

        // 1. ALL-TIME STATS (TIDAK TERPENGARUH FILTER TANGGAL / CHANNEL)
        const allTimeStats = computeMetricsFromOrders(MASTER_DUMMY_ORDERS);

        // 2. FILTERED ORDERS (SESUAI TANGGAL UNIVERSAL & CHANNEL)
        const filteredOrders = MASTER_DUMMY_ORDERS.filter(o => {
            const matchDate   = o.timestamp >= startTs && o.timestamp <= endTs;
            const matchSource = state.order_source === 'all' || o.order_source === state.order_source;
            return matchDate && matchSource;
        });

        const prevOrders = MASTER_DUMMY_ORDERS.filter(o => {
            const matchDate   = o.timestamp >= prevStartTs && o.timestamp <= prevEndTs;
            const matchSource = state.order_source === 'all' || o.order_source === state.order_source;
            return matchDate && matchSource;
        });

        const filteredStats = computeMetricsFromOrders(filteredOrders);
        const prevStats     = computeMetricsFromOrders(prevOrders);

        filteredStats.revenue_growth_pct = prevStats.revenue_total > 0
            ? Number((((filteredStats.revenue_total - prevStats.revenue_total) / prevStats.revenue_total) * 100).toFixed(1))
            : (filteredStats.revenue_total > 0 ? 100 : 0);

        filteredStats.unpaid_growth_pct = prevStats.unpaid_total > 0
            ? Number((((filteredStats.unpaid_total - prevStats.unpaid_total) / prevStats.unpaid_total) * 100).toFixed(1))
            : (filteredStats.unpaid_total > 0 ? 100 : 0);

        const dStart = new Date(startTs).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const dEnd   = new Date(endTs).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        filteredStats.period_label = `${dStart} - ${dEnd}`;

        // 3. BANGUN TITIK GRAFIK DARI RENTANG TANGGAL UNIVERSAL [startTs, endTs]
        const buckets = [];
        if (state.granularity === 'daily') {
            let cur = new Date(startTs);
            while (cur.getTime() <= endTs) {
                const bStart = new Date(cur.getFullYear(), cur.getMonth(), cur.getDate(), 0, 0, 0).getTime();
                const bEnd   = new Date(cur.getFullYear(), cur.getMonth(), cur.getDate(), 23, 59, 59).getTime();
                buckets.push({
                    label: cur.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }),
                    start: bStart,
                    end: bEnd
                });
                cur.setDate(cur.getDate() + 1);
            }
        } else if (state.granularity === 'weekly') {
            let cur = new Date(startTs);
            let wIdx = 1;
            while (cur.getTime() <= endTs) {
                const bStart = new Date(cur.getFullYear(), cur.getMonth(), cur.getDate(), 0, 0, 0).getTime();
                const wEndObj = new Date(cur);
                wEndObj.setDate(wEndObj.getDate() + 6);
                wEndObj.setHours(23, 59, 59, 999);
                const bEnd = Math.min(endTs, wEndObj.getTime());
                buckets.push({
                    label: `Mgg ${wIdx} (${cur.getDate()}/${cur.getMonth() + 1})`,
                    start: bStart,
                    end: bEnd
                });
                cur.setDate(cur.getDate() + 7);
                wIdx++;
            }
        } else if (state.granularity === 'monthly') {
            let cur = new Date(new Date(startTs).getFullYear(), new Date(startTs).getMonth(), 1);
            while (cur.getTime() <= endTs) {
                const mStart = new Date(cur.getFullYear(), cur.getMonth(), 1, 0, 0, 0).getTime();
                const mEnd   = new Date(cur.getFullYear(), cur.getMonth() + 1, 0, 23, 59, 59).getTime();
                buckets.push({
                    label: cur.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }),
                    start: Math.max(startTs, mStart),
                    end: Math.min(endTs, mEnd)
                });
                cur.setMonth(cur.getMonth() + 1);
            }
        } else {
            // yearly
            let curYear = new Date(startTs).getFullYear();
            const endYear = new Date(endTs).getFullYear();
            while (curYear <= endYear) {
                const yStart = new Date(curYear, 0, 1, 0, 0, 0).getTime();
                const yEnd   = new Date(curYear, 11, 31, 23, 59, 59).getTime();
                buckets.push({
                    label: String(curYear),
                    start: Math.max(startTs, yStart),
                    end: Math.min(endTs, yEnd)
                });
                curYear++;
            }
        }

        const charts = {
            labels: [],
            revenue_current: [],
            revenue_lunas: [],
            revenue_dp: [],
            unpaid_current: [],
            unpaid_dp_sisa: [],
            unpaid_only: []
        };

        buckets.forEach(b => {
            const slice = filteredOrders.filter(o => o.timestamp >= b.start && o.timestamp <= b.end);
            const m = computeMetricsFromOrders(slice);
            charts.labels.push(b.label);
            charts.revenue_current.push(m.revenue_total);
            charts.revenue_lunas.push(m.revenue_lunas);
            charts.revenue_dp.push(m.revenue_dp_paid);
            charts.unpaid_current.push(m.unpaid_total);
            charts.unpaid_dp_sisa.push(m.unpaid_dp_sisa);
            charts.unpaid_only.push(m.unpaid_full);
        });

        // 4. HITUNG TOP 5 PRODUK DARI FILTERED ORDERS
        const prodMap = {};
        let totalProdOmzet = 0;
        filteredOrders.forEach(o => {
            if (o.order_status === 'dibatalkan') return;
            o.items.forEach(it => {
                if (!prodMap[it.id]) {
                    prodMap[it.id] = { name: it.name, qty: 0, revenue: 0 };
                }
                prodMap[it.id].qty += it.qty;
                prodMap[it.id].revenue += it.subtotal;
                totalProdOmzet += it.subtotal;
            });
        });

        const topProducts = Object.values(prodMap)
            .sort((a, b) => b.revenue - a.revenue)
            .slice(0, 5)
            .map(p => ({
                ...p,
                percentage: totalProdOmzet > 0 ? Number(((p.revenue / totalProdOmzet) * 100).toFixed(1)) : 0
            }));

        // 5. DISTRIBUSI CHANNEL DARI FILTERED ORDERS
        const channelBreakdown = {
            web_orders: filteredOrders.filter(o => o.order_source === 'web').length,
            whatsapp_orders: filteredOrders.filter(o => o.order_source === 'whatsapp').length
        };

        return {
            status: 'success',
            data: {
                all_time_stats: allTimeStats,
                filtered_stats: filteredStats,
                charts: charts,
                top_products: topProducts,
                channel_breakdown: channelBreakdown,
                transactions: filteredOrders
            }
        };
    }

    // --- FETCH / RENDER CONTROLLER ---
    function fetchDashboardData() {
        const res = getDummyApiResponse(filterState);
        if (res.status === 'success') {
            renderDashboardUI(res.data);
        }
    }

    function renderDashboardUI(data) {
        const allTime  = data.all_time_stats;
        const filtered = data.filtered_stats;

        $('#active-period-badge').text(filtered.period_label);
        const channelNames = { all: 'Semua Channel', web: 'Khusus Website', whatsapp: 'Khusus WA Bot' };
        $('#active-channel-badge').text(channelNames[filterState.order_source] || 'Semua Channel');

        // All-Time Stats (Tidak berubah saat filter diganti)
        $('#alltime-revenue-total').text(formatRupiah(allTime.revenue_total));
        $('#alltime-revenue-lunas').text(formatRupiah(allTime.revenue_lunas));
        $('#alltime-revenue-dp').text(formatRupiah(allTime.revenue_dp_paid));
        $('#alltime-orders-count').text(`${allTime.success_orders} Order Sukses`);

        $('#alltime-unpaid-total').text(formatRupiah(allTime.unpaid_total));
        $('#alltime-unpaid-dp').text(formatRupiah(allTime.unpaid_dp_sisa));
        $('#alltime-unpaid-full').text(formatRupiah(allTime.unpaid_full));

        // Filtered Stats (Berubah mengikuti Filter Universal)
        $('#filtered-revenue-total').text(formatRupiah(filtered.revenue_total));
        $('#filtered-revenue-lunas').text(formatRupiah(filtered.revenue_lunas));
        $('#filtered-revenue-dp').text(formatRupiah(filtered.revenue_dp_paid));

        const revSign = filtered.revenue_growth_pct >= 0 ? '+' : '';
        $('#filtered-rev-growth')
            .text(`${revSign}${filtered.revenue_growth_pct}% vs periode lalu`)
            .attr('class', `px-3 py-1 rounded-xl text-xs font-black border ${filtered.revenue_growth_pct >= 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'}`);

        $('#filtered-unpaid-total').text(formatRupiah(filtered.unpaid_total));
        $('#filtered-unpaid-dp').text(formatRupiah(filtered.unpaid_dp_sisa));
        $('#filtered-unpaid-full').text(formatRupiah(filtered.unpaid_full));

        const unpSign = filtered.unpaid_growth_pct >= 0 ? '+' : '';
        $('#filtered-unpaid-growth').text(`${unpSign}${filtered.unpaid_growth_pct}% vs periode lalu`);

        $('#filtered-success-orders').text(`${filtered.success_orders} Order`);
        $('#filtered-aov').text(formatRupiah(filtered.avg_order_value));
        $('#filtered-unpaid-count').text(`${filtered.unpaid_count} Order`);
        $('#filtered-cancelled-badge').text(`${filtered.cancelled_count} Dibatalkan`);

        renderBreakdownCharts(data.charts);
        renderTopProducts(data.top_products);

        $('#channel-web-count').text(`${data.channel_breakdown.web_orders} Order`);
        $('#channel-wa-count').text(`${data.channel_breakdown.whatsapp_orders} Order`);

        allTransactions = data.transactions || [];
        currentPage = 1;
        applyTableFilters();
    }

    // --- ENGINE 2 GRAFIK FOKUS RINCIAN KOMPONEN ---
    function renderBreakdownCharts(c) {
        const sumRev = (c.revenue_current || []).reduce((a, b) => a + b, 0);
        const sumUnp = (c.unpaid_current || []).reduce((a, b) => a + b, 0);
        $('#chart-rev-sum').text(formatRupiah(sumRev));
        $('#chart-unpaid-sum').text(formatRupiah(sumUnp));

        if (revChartInstance) { revChartInstance.destroy(); revChartInstance = null; }
        if (unpaidChartInstance) { unpaidChartInstance.destroy(); unpaidChartInstance = null; }

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
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
                labels: c.labels,
                datasets: [
                    {
                        label: 'Total Kas Masuk (Lunas + DP)',
                        data: c.revenue_current,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.14)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3
                    },
                    {
                        label: 'Bayar Penuh (Lunas 100%)',
                        data: c.revenue_lunas,
                        borderColor: '#9368B7',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2.5
                    },
                    {
                        label: 'Uang DP Masuk (30%)',
                        data: c.revenue_dp,
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
                labels: c.labels,
                datasets: [
                    {
                        label: 'Total Tagihan Belum Dibayar',
                        data: c.unpaid_current,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.14)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3
                    },
                    {
                        label: 'Sisa Pelunasan DP (70%)',
                        data: c.unpaid_dp_sisa,
                        borderColor: '#EA580C',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2.5
                    },
                    {
                        label: 'Belum Bayar Sama Sekali (Unpaid 100%)',
                        data: c.unpaid_only,
                        borderColor: '#F43F5E',
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

    // --- RENDER TOP 5 PRODUK ---
    function renderTopProducts(list) {
        const container = $('#top-products-container');
        container.empty();

        if (!list || list.length === 0) {
            container.html('<p class="text-xs font-bold text-admin-textMuted py-4">Tidak ada produk terjual pada rentang tanggal & channel ini.</p>');
            return;
        }

        list.forEach((p, i) => {
            container.append(`
                <div class="clay-well p-3.5">
                    <div class="flex justify-between items-center text-xs mb-1.5">
                        <span class="font-black text-admin-textMain">${i + 1}. ${p.name} <span class="text-admin-textMuted font-bold">(${p.qty} pcs)</span></span>
                        <span class="font-black text-admin-sideBg">${formatRupiah(p.revenue)} (${p.percentage}%)</span>
                    </div>
                    <div class="w-full h-2 bg-white rounded-full overflow-hidden">
                        <div class="h-full bg-admin-sideBg rounded-full" style="width: ${p.percentage}%"></div>
                    </div>
                </div>
            `);
        });
    }

    // --- TABEL FILTER & RENDER ---
    function updateStatusLabel() {
        selectedStatuses = [];
        $('.cb-status:checked').each(function() {
            selectedStatuses.push($(this).val());
        });

        if (selectedStatuses.length === 0) {
            $('#lbl-status').text('Semua Status Bayar');
        } else if (selectedStatuses.length === 1) {
            $('#lbl-status').text($('.cb-status:checked').next('span').text());
        } else {
            $('#lbl-status').text(`${selectedStatuses.length} Status Dipilih`);
        }
    }

    function applyTableFilters() {
        const keyword = ($('#filter-search').val() || '').toLowerCase();

        filteredTransactions = allTransactions.filter(item => {
            const matchSearch = item.order_number.toLowerCase().includes(keyword) ||
                                item.recipient_name.toLowerCase().includes(keyword) ||
                                (item.recipient_whatsapp || '').toLowerCase().includes(keyword);

            let matchStatus = true;
            if (selectedStatuses.length > 0) {
                const actualStatus = item.order_status === 'dibatalkan' ? 'dibatalkan' : (item.payment_status === 'failed' ? 'unpaid' : item.payment_status);
                matchStatus = selectedStatuses.includes(actualStatus);
            }

            return matchSearch && matchStatus;
        });

        renderTable();
    }

    function renderTable() {
        const tbody = $('#table-body');
        const emptyState = $('#empty-state');
        tbody.empty();

        const totalItems = filteredTransactions.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const slice = filteredTransactions.slice((currentPage - 1) * perPage, currentPage * perPage);

        if (slice.length === 0) {
            emptyState.removeClass('hidden').addClass('flex');
        } else {
            emptyState.removeClass('flex').addClass('hidden');
            let html = '';

            slice.forEach(item => {
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

                const d = new Date(item.created_at);
                const dateFormatted = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                html += `
                    <tr class="hover:bg-admin-bg/50 transition-colors">
                        <td class="px-6 py-4 font-black text-admin-sideBg whitespace-nowrap">#${item.order_number}</td>
                        <td class="px-6 py-4 font-bold text-admin-textMuted whitespace-nowrap text-xs">${dateFormatted} WIB</td>
                        <td class="px-6 py-4">
                            <p class="font-black text-admin-textMain text-xs">${item.recipient_name}</p>
                            <p class="text-[11px] font-semibold text-admin-textMuted">${item.recipient_whatsapp || '-'}</p>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            ${channelBadge}
                            <span class="ml-1 text-[10px] font-black text-admin-textMuted bg-gray-100 px-2 py-0.5 rounded uppercase">${item.payment_method}</span>
                        </td>
                        <td class="px-6 py-4 font-black text-emerald-600 text-right whitespace-nowrap">${formatRupiah(item.paid_amount)}</td>
                        <td class="px-6 py-4 font-black ${item.remaining_amount > 0 ? 'text-amber-600' : 'text-gray-400'} text-right whitespace-nowrap">${formatRupiah(item.remaining_amount)}</td>
                        <td class="px-6 py-4 font-black text-admin-textMain text-right whitespace-nowrap">${formatRupiah(item.total_amount)}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">${badge}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap no-print">
                            <a href="/nota?order_number=${item.order_number}" target="_blank" class="ux-interactive inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-admin-bg hover:bg-admin-sideBg hover:text-white text-admin-sideBg font-black text-xs">
                                <i class="ph ph-receipt text-sm"></i>
                                <span>Nota</span>
                            </a>
                        </td>
                    </tr>
                `;
            });
            tbody.html(html);
        }

        $('#pagination-info').text(`Menampilkan ${slice.length} dari ${totalItems} transaksi`);
        $('#page-indicator').text(`Hal ${currentPage} / ${totalPages}`);
        $('#btn-prev-page').prop('disabled', currentPage <= 1);
        $('#btn-next-page').prop('disabled', currentPage >= totalPages);
    }

    function setGranularityUI(gran) {
        filterState.granularity = gran;
        $('.granularity-btn').removeClass('bg-admin-sideBg text-white shadow-sm').addClass('text-admin-textMuted');
        $(`.granularity-btn[data-granularity="${gran}"]`).removeClass('text-admin-textMuted').addClass('bg-admin-sideBg text-white shadow-sm');
    }

    // --- NAMED EVENT HANDLERS (ZERO MEMORY LEAK PROTOCOL) ---
    function handleDocClick(e) {
        if (!e.target.closest('#wrapper-status')) {
            document.getElementById('menu-status')?.classList.add('hidden');
            document.getElementById('btn-status')?.setAttribute('aria-expanded', 'false');
        }
    }

    function handleDocKeydown(e) {
        if (e.key === 'Escape') {
            document.getElementById('menu-status')?.classList.add('hidden');
            document.getElementById('btn-status')?.setAttribute('aria-expanded', 'false');
        }
    }

    function cleanupResources() {
        document.removeEventListener('click', handleDocClick);
        document.removeEventListener('keydown', handleDocKeydown);
        if (activeAbortController) activeAbortController.abort();
        if (toastTimerId) clearTimeout(toastTimerId);
        if (revChartInstance) revChartInstance.destroy();
        if (unpaidChartInstance) unpaidChartInstance.destroy();
        if (fpUniversal) fpUniversal.destroy();
    }

    $(document).ready(function() {
        document.addEventListener('click', handleDocClick);
        document.addEventListener('keydown', handleDocKeydown);
        window.addEventListener('beforeunload', cleanupResources, { once: true });

        // 1. KALENDER PILIH TANGGAL BEBAS (MEMATIKAN PRESET TOMBOL SEBELUMNYA)
        fpUniversal = flatpickr("#universal-date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            locale: "id",
            defaultDate: [filterState.start_date, filterState.end_date],
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    filterState.start_date = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                    filterState.end_date   = flatpickr.formatDate(selectedDates[1], "Y-m-d");

                    // Matikan highlight tombol preset (7 Hari / 30 Hari / Bulan Ini / Tahun Ini)
                    $('.preset-date-btn').removeClass('bg-white text-admin-sideBg shadow-sm').addClass('text-admin-textMuted');

                    // Otomatis sesuaikan pengelompokan grafik agar enak dilihat sesuai lebar rentang tanggal
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

        // 2. KLIK PRESET TANGGAL (7 Hari, 30 Hari, Bulan Ini, Tahun Ini)
        $('.preset-date-btn').on('click', function() {
            $('.preset-date-btn').removeClass('bg-white text-admin-sideBg shadow-sm').addClass('text-admin-textMuted');
            $(this).removeClass('text-admin-textMuted').addClass('bg-white text-admin-sideBg shadow-sm');

            const preset = $(this).data('preset');
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
            fpUniversal.setDate([filterState.start_date, filterState.end_date], false);

            fetchDashboardData();
            showToast('Preset Rentang Aktif', `Menampilkan data ${$(this).text()} (${filterState.start_date} s/d ${filterState.end_date})`);
        });

        // 3. KLIK FILTER SUMBER TRANSAKSI (Semua / Web / WA Bot)
        $('.source-filter-btn').on('click', function() {
            $('.source-filter-btn').removeClass('bg-white text-admin-sideBg shadow-sm').addClass('text-admin-textMuted');
            $(this).removeClass('text-admin-textMuted').addClass('bg-white text-admin-sideBg shadow-sm');
            filterState.order_source = $(this).data('source');
            fetchDashboardData();
            showToast('Filter Channel Diubah', `Menampilkan transaksi dari: ${$(this).find('span').text()}`);
        });

        // 4. KLIK KELOMPOK WAKTU GRAFIK (Per Hari / Minggu / Bulan / Tahun dalam rentang aktif)
        $('.granularity-btn').on('click', function() {
            setGranularityUI($(this).data('granularity'));
            fetchDashboardData();
        });

        $('#btn-print-report').on('click', function() {
            window.print();
        });

        $('#btn-jump-piutang').on('click', function() {
            $('.cb-status').prop('checked', false);
            $('.cb-status[value="paid_dp"], .cb-status[value="unpaid"]').prop('checked', true);
            updateStatusLabel();
            currentPage = 1;
            applyTableFilters();
            document.getElementById('section-tabel-riwayat')?.scrollIntoView({ behavior: 'smooth' });
            showToast('Filter Piutang Aktif', 'Menampilkan pesanan yang belum lunas (DP 30% & Unpaid).');
        });

        $('#btn-status').on('click', function(e) {
            e.stopPropagation();
            const menu = $('#menu-status');
            const isHidden = menu.hasClass('hidden');
            menu.toggleClass('hidden', !isHidden);
            $(this).attr('aria-expanded', isHidden ? 'true' : 'false');
        });

        $('#btn-close-status-menu').on('click', function() {
            $('#menu-status').addClass('hidden');
            $('#btn-status').attr('aria-expanded', 'false');
        });

        $('.cb-status').on('change', function() {
            updateStatusLabel();
            currentPage = 1;
            applyTableFilters();
        });

        $('#btn-reset-table-filter').on('click', function() {
            $('#filter-search').val('');
            $('.cb-status').prop('checked', false);
            updateStatusLabel();
            currentPage = 1;
            applyTableFilters();
            showToast('Filter Tabel Direset', 'Menampilkan seluruh transaksi pada periode aktif.');
        });

        $('#filter-search').on('input', function() {
            currentPage = 1;
            applyTableFilters();
        });

        $('#btn-prev-page').on('click', function() {
            if (currentPage > 1) { currentPage--; renderTable(); }
        });

        $('#btn-next-page').on('click', function() {
            const totalPages = Math.ceil(filteredTransactions.length / perPage);
            if (currentPage < totalPages) { currentPage++; renderTable(); }
        });

        $('#btn-export-csv').on('click', function() {
            if (filteredTransactions.length === 0) {
                showToast('Data Kosong', 'Tidak ada transaksi yang dapat diunduh.', false);
                return;
            }
            let csv = 'No Pesanan,Waktu Checkout,Pelanggan,No WA,Channel,Metode,Kas Masuk,Sisa Tagihan,Total Order,Status Bayar\n';
            filteredTransactions.forEach(r => {
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
        });

        fetchDashboardData();
    });
})();
</script>
@endpush


{{-- ========================================================================= --}}
{{-- BLOK SCRIPT 2 (MODE REAL AJAX DATABASE DENGAN ABORTCONTROLLER)            --}}
{{-- Jika siap menyambungkan ke database, ganti fungsi fetchDashboardData()    --}}
{{-- pada Blok Script 1 di atas dengan fungsi ber-AbortController di bawah ini:--}}
{{-- ========================================================================= --}}
{{--
@push('script')
<script>
    function fetchDashboardData() {
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();

        $('#global-loader').removeClass('opacity-0 pointer-events-none');
        $('#loader-bar').css('transform', 'scaleX(0.7)');

        const params = new URLSearchParams({
            start_date: filterState.start_date,
            end_date: filterState.end_date,
            granularity: filterState.granularity,
            order_source: filterState.order_source
        });

        fetch(`/api/admin/dashboard-finansial?${params.toString()}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            signal: activeAbortController.signal
        })
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                renderDashboardUI(res.data);
            }
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                showToast('Gagal Memuat Data', 'Tidak dapat terhubung ke server finansial.', false);
            }
        })
        .finally(() => {
            $('#loader-bar').css('transform', 'scaleX(1)');
            setTimeout(() => {
                $('#global-loader').addClass('opacity-0 pointer-events-none');
                setTimeout(() => $('#loader-bar').css('transform', 'scaleX(0)'), 200);
            }, 300);
        });
    }
</script>
@endpush
--}}
