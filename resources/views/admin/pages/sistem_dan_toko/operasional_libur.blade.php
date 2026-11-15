@extends('admin.admin_master')

@section('title', 'Operasional & Libur - Admin Raya Kitchen')
@section('header_title', 'Operasional & Libur')

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
        --clay-danger: #F43F5E;

        --clay-shadow-card:
            inset 0 -4px 6px rgba(147, 104, 183, 0.1),
            inset 0 4px 6px rgba(255, 255, 255, 1),
            0 14px 32px -8px rgba(147, 104, 183, 0.14);
        --clay-shadow-inset:
            inset 0 2px 5px rgba(147, 104, 183, 0.14),
            inset 0 -2px 4px rgba(255, 255, 255, 0.9);
        --clay-shadow-chip-idle:
            inset 0 -2px 4px rgba(147, 104, 183, 0.1),
            0 2px 6px rgba(147, 104, 183, 0.08);
        --clay-shadow-chip-active:
            inset 0 3px 6px rgba(0, 0, 0, 0.25),
            0 4px 10px rgba(147, 104, 183, 0.28);

        /* Fluid Typography Tokens */
        --fs-section-title: clamp(1.1rem, 1vw + 0.8rem, 1.4rem);
        --fs-stat-val: clamp(0.95rem, 0.9vw + 0.7rem, 1.2rem);
        --fs-body: clamp(0.8125rem, 0.25vw + 0.75rem, 0.9375rem);
    }

    .clay-panel {
        background-color: var(--clay-bg-base);
        box-shadow: var(--clay-shadow-card);
        border: 2px solid #FFFFFF;
    }

    .clay-input {
        min-height: 46px;
        background-color: var(--clay-surface);
        box-shadow: var(--clay-shadow-inset);
        border: 1.5px solid transparent;
        transition: transform 0.15s ease, opacity 0.15s ease;
    }
    .clay-input:focus {
        outline: none;
        border-color: var(--clay-primary);
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

    .courier-chip {
        min-height: 44px;
        min-width: 72px;
        transition: transform 0.16s cubic-bezier(0.2, 0, 0, 1), opacity 0.16s ease;
        will-change: transform, opacity;
    }
    .courier-chip.is-idle {
        background-color: var(--clay-surface);
        color: var(--clay-text-muted);
        box-shadow: var(--clay-shadow-chip-idle);
        border: 1.5px solid #F3E8FF;
    }
    .courier-chip.is-active {
        background-color: var(--clay-primary);
        color: #FFFFFF;
        box-shadow: var(--clay-shadow-chip-active);
        border: 1.5px solid rgba(255, 255, 255, 0.35);
    }
    .courier-chip:hover {
        transform: translateY(-2px);
    }
    .courier-chip:active {
        transform: scale(0.95);
    }
    .courier-chip:focus-visible {
        outline: 3px solid var(--clay-primary);
        outline-offset: 2px;
    }

    .fluid-section-title { font-size: var(--fs-section-title); }
    .fluid-stat-val { font-size: var(--fs-stat-val); }
    .fluid-body { font-size: var(--fs-body); }

    .thin-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .thin-scroll::-webkit-scrollbar-track { background: transparent; }
    .thin-scroll::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }

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
<div id="op-toast" role="status" aria-live="polite" class="fixed top-6 right-6 z-[210] hidden items-center gap-3 px-5 py-3.5 rounded-2xl bg-white border-2 border-purple-100 shadow-2xl transition-all duration-200 opacity-0 translate-y-[-10px] max-w-sm">
    <div id="op-toast-icon" class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
        <i class="ph ph-check-circle text-xl"></i>
    </div>
    <div class="min-w-0 flex-1">
        <p id="op-toast-title" class="text-xs font-black text-gray-900">Berhasil</p>
        <p id="op-toast-msg" class="text-xs font-semibold text-gray-600 break-words">Pengaturan diperbarui.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 xl:gap-8 max-w-7xl mx-auto items-start pb-8 overflow-x-hidden">

    <!-- =================================================================== -->
    <!-- KOLOM KIRI: PENGATURAN OPERASIONAL & SIMULATOR KAPASITAS (Span 6)   -->
    <!-- =================================================================== -->
    <section aria-label="Pengaturan Operasional" class="lg:col-span-6 clay-panel p-5 sm:p-7 rounded-[2.25rem] min-w-0 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b-2 border-white">
            <h2 class="fluid-section-title font-black text-admin-textMain flex items-center gap-3 drop-shadow-sm min-w-0">
                <span class="w-11 h-11 rounded-2xl bg-white flex items-center justify-center shadow-[inset_0_-2px_4px_rgba(147,104,183,0.2),0_4px_8px_rgba(147,104,183,0.15)] shrink-0">
                    <i class="ph ph-sliders-horizontal text-2xl text-admin-sideBg"></i>
                </span>
                <span class="truncate">Pengaturan Operasional</span>
            </h2>
            <span id="op-status-badge" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200 shrink-0">
                Aktif
            </span>
        </div>

        <!-- Skeleton Loader Form Operasional -->
        <div id="op-skeleton" class="space-y-4 animate-pulse">
            <div class="h-20 bg-white/80 rounded-2xl"></div>
            <div class="grid grid-cols-2 gap-4">
                <div class="h-16 bg-white/80 rounded-2xl"></div>
                <div class="h-16 bg-white/80 rounded-2xl"></div>
            </div>
            <div class="h-28 bg-white/80 rounded-2xl"></div>
            <div class="h-12 bg-white/80 rounded-2xl"></div>
        </div>

        <form id="form-operasional" class="hidden space-y-5" novalidate>
            @csrf

            <!-- Live Capacity Simulator Card -->
            <div class="p-4 rounded-2xl bg-white border border-purple-100 shadow-sm space-y-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-admin-sideBg flex items-center gap-1.5">
                        <i class="ph ph-gauge text-sm"></i>
                        <span>Estimasi Kapasitas Harian</span>
                    </span>
                    <span id="sim-hours-badge" class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-purple-50 text-admin-sideBg">9 Jam Kerja</span>
                </div>
                <div class="grid grid-cols-3 gap-2 pt-1 text-center">
                    <div class="p-2.5 rounded-xl bg-[#FAF8FC] border border-purple-50 min-w-0">
                        <p class="text-[10px] font-bold text-admin-textMuted truncate">Kuota Produksi</p>
                        <p id="sim-daily-qty" class="fluid-stat-val font-black text-admin-textMain mt-0.5 truncate">60 pcs</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-[#FAF8FC] border border-purple-50 min-w-0">
                        <p class="text-[10px] font-bold text-admin-textMuted truncate">Slot Kirim/Hari</p>
                        <p id="sim-daily-slots" class="fluid-stat-val font-black text-admin-sideBg mt-0.5 truncate">50 order</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-[#FAF8FC] border border-purple-50 min-w-0">
                        <p class="text-[10px] font-bold text-admin-textMuted truncate">Radius Gratis</p>
                        <p id="sim-free-dist" class="fluid-stat-val font-black text-emerald-600 mt-0.5 truncate">10 KM</p>
                    </div>
                </div>
            </div>

            <!-- Input Kuota Produksi & Antrean Jam -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="max_qty_per_day" class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-1">
                        Kuota Roti / Hari (Pcs)
                    </label>
                    <div class="relative">
                        <input type="number" min="1" max="10000" name="max_qty_per_day" id="max_qty_per_day" required class="clay-input w-full p-3.5 pr-12 rounded-2xl text-sm font-black text-admin-textMain">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted font-black text-[11px] pointer-events-none">PCS</span>
                    </div>
                </div>

                <div>
                    <label for="max_orders_per_hour" class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-1">
                        Antrean Kirim / Jam
                    </label>
                    <div class="relative">
                        <input type="number" min="1" max="500" name="max_orders_per_hour" id="max_orders_per_hour" required class="clay-input w-full p-3.5 pr-14 rounded-2xl text-sm font-black text-admin-textMain">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted font-black text-[11px] pointer-events-none">ORDER</span>
                    </div>
                </div>
            </div>

            <!-- Jam Buka & Jam Tutup -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="operational_time_start" class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-1">
                        Jam Buka Kurir
                    </label>
                    <input type="time" name="operational_time_start" id="operational_time_start" required class="clay-input w-full p-3.5 rounded-2xl text-sm font-black text-admin-textMain cursor-pointer">
                </div>
                <div>
                    <label for="operational_time_end" class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-1">
                        Jam Tutup Kurir
                    </label>
                    <input type="time" name="operational_time_end" id="operational_time_end" required class="clay-input w-full p-3.5 rounded-2xl text-sm font-black text-admin-textMain cursor-pointer">
                </div>
            </div>

            <!-- Batas Jarak Gratis Ongkir -->
            <div>
                <label for="free_delivery_max_distance" class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-1">
                    Batas Jarak Gratis Ongkir Kurir Internal (KM)
                </label>
                <div class="relative">
                    <input type="number" step="0.1" min="0" max="500" name="free_delivery_max_distance" id="free_delivery_max_distance" required class="clay-input w-full p-3.5 pr-12 rounded-2xl text-sm font-black text-admin-textMain">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted font-black text-xs pointer-events-none">KM</span>
                </div>
                <p class="text-[11px] text-admin-textMuted font-semibold ml-1 mt-1">
                    Pelanggan di luar radius ini otomatis diarahkan memilih ekspedisi RajaOngkir saat checkout.
                </p>
            </div>

            <hr class="border-white my-1">

            <!-- Chips Ekspedisi RajaOngkir -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                    <label class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted ml-1">
                        Ekspedisi RajaOngkir Aktif (<span id="active-courier-count">0</span> terpilih)
                    </label>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" id="btn-courier-default" class="px-2.5 py-1 rounded-lg bg-white border border-purple-200 text-[10px] font-black text-admin-sideBg hover:bg-purple-50 transition-colors">
                            Default (JNE, J&T, SiCepat)
                        </button>
                        <button type="button" id="btn-courier-all" class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-[10px] font-black text-admin-textMuted hover:bg-gray-50 transition-colors">
                            Pilih Semua
                        </button>
                    </div>
                </div>

                <input type="hidden" name="active_couriers" id="active_couriers_input">

                <div class="flex flex-wrap gap-2" id="courier-container" role="group" aria-label="Daftar Kurir Ekspedisi">
                    @foreach(['jne', 'sicepat', 'jnt', 'anteraja', 'ninja', 'pos', 'tiki', 'ide', 'sap', 'lion', 'wahana', 'spx', 'ncs', 'rex', 'rpx', 'sentral', 'star'] as $c)
                        <button type="button" data-courier="{{ $c }}" aria-pressed="false" class="courier-chip is-idle px-3.5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wide select-none flex items-center justify-center gap-1.5">
                            <span>{{ strtoupper($c) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="btn-submit-op" class="clay-btn w-full mt-4 py-4 bg-admin-sideBg hover:bg-admin-sideHover text-white font-black text-sm rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.35),0_8px_20px_rgba(147,104,183,0.35)] flex justify-center items-center gap-2">
                <i class="ph ph-floppy-disk text-xl"></i>
                <span>Simpan Pengaturan Operasional</span>
            </button>
        </form>
    </section>

    <!-- =================================================================== -->
    <!-- KOLOM KANAN: KALENDER HARI LIBUR DAPUR (Span 6)                     -->
    <!-- =================================================================== -->
    <section aria-label="Manajemen Kalender Libur" class="lg:col-span-6 clay-panel p-5 sm:p-7 rounded-[2.25rem] flex flex-col min-w-0 overflow-hidden">

        <!-- Header Kalender Libur (Susunan Bertingkat yang Responsif Tanpa Nabrak Kanan) -->
        <div class="flex flex-col gap-3.5 mb-5 pb-4 border-b-2 border-white">
            <div class="flex items-center justify-between gap-3 min-w-0">
                <h2 class="fluid-section-title font-black text-admin-textMain flex items-center gap-3 drop-shadow-sm min-w-0">
                    <span class="w-11 h-11 rounded-2xl bg-white flex items-center justify-center shadow-[inset_0_-2px_4px_rgba(244,63,94,0.2),0_4px_8px_rgba(244,63,94,0.15)] shrink-0">
                        <i class="ph ph-calendar-x text-2xl text-rose-500"></i>
                    </span>
                    <span class="truncate">Kalender Libur Dapur</span>
                </h2>
            </div>

            <!-- Baris Tombol Aksi Header (Grid 2 Kolom Rata & Tidak Pernah Terpotong) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 w-full">
                <button type="button" id="btn-clear-past" title="Hapus tanggal libur yang sudah lewat" class="clay-btn w-full px-3.5 py-2.5 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 font-black rounded-xl text-xs flex items-center justify-center gap-1.5 min-w-0">
                    <i class="ph ph-broom text-base shrink-0"></i>
                    <span class="truncate">Bersihkan Lewat (<span id="count-past-holidays">0</span>)</span>
                </button>
                <button type="button" id="btn-open-generate-modal" class="clay-btn w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl shadow-[inset_0_-2px_4px_rgba(0,0,0,0.15),0_4px_10px_rgba(37,99,235,0.28)] text-xs flex items-center justify-center gap-1.5 min-w-0">
                    <i class="ph ph-magic-wand text-base shrink-0"></i>
                    <span class="truncate">Auto-Generate Kalender</span>
                </button>
            </div>
        </div>

        <!-- Form Tambah Libur Manual (Proporsional & Tidak Memotong dd/mm/yyyy) -->
        <form id="form-libur" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 mb-5" novalidate>
            @csrf
            <div class="sm:col-span-5">
                <label for="holiday_date" class="sr-only">Tanggal Libur</label>
                <input type="date" name="holiday_date" id="holiday_date" required aria-label="Tanggal Libur" class="clay-input w-full px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-black text-admin-textMain cursor-pointer">
            </div>
            <div class="sm:col-span-7">
                <label for="holiday_desc" class="sr-only">Keterangan Libur</label>
                <input type="text" name="description" id="holiday_desc" placeholder="Keterangan (Cth: Renovasi Dapur)..." required aria-label="Keterangan Libur" class="clay-input w-full px-3.5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold text-admin-textMain">
            </div>
            <div class="sm:col-span-12">
                <button type="submit" id="btn-add-libur" class="clay-btn w-full py-3 px-4 bg-rose-500 hover:bg-rose-600 text-white font-black rounded-2xl shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),0_6px_14px_rgba(244,63,94,0.28)] text-xs sm:text-sm flex items-center justify-center gap-2">
                    <i class="ph ph-plus-circle text-lg"></i>
                    <span>Tambah Tanggal Libur</span>
                </button>
            </div>
        </form>

        <!-- Bar Filter & Pencarian Hari Libur -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 mb-4">
            <div class="flex gap-1 bg-white/80 p-1 rounded-xl border border-white shrink-0">
                <button type="button" data-filter="upcoming" class="holiday-tab px-3 py-1.5 rounded-lg text-[11px] font-black bg-admin-sideBg text-white transition-colors">
                    Akan Datang (<span id="count-upcoming-holidays">0</span>)
                </button>
                <button type="button" data-filter="all" class="holiday-tab px-3 py-1.5 rounded-lg text-[11px] font-black text-admin-textMuted hover:bg-gray-100 transition-colors">
                    Semua (<span id="count-all-holidays">0</span>)
                </button>
            </div>

            <div class="relative flex-1 min-w-[160px]">
                <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-admin-textMuted text-sm pointer-events-none"></i>
                <input type="search" id="search-holiday" placeholder="Cari keterangan / tanggal..." class="w-full pl-9 pr-3 py-2 bg-white rounded-xl border border-purple-100 text-xs font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg">
            </div>
        </div>

        <!-- Tabel Daftar Hari Libur (Proporsional Tanpa Memotong Kolom Aksi) -->
        <div class="rounded-3xl max-h-[380px] overflow-y-auto overflow-x-hidden thin-scroll relative bg-white shadow-[inset_0_2px_6px_rgba(147,104,183,0.12)] border-2 border-white">
            <table class="w-full table-fixed text-xs sm:text-sm text-left">
                <thead class="text-admin-textMuted uppercase font-black text-[10px] tracking-wider sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-purple-100">
                    <tr>
                        <th class="w-[44%] px-4 py-3.5">Tanggal</th>
                        <th class="w-[40%] px-3 py-3.5">Keterangan</th>
                        <th class="w-[16%] px-3 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-libur" class="divide-y divide-gray-100">
                    @for ($i = 0; $i < 4; $i++)
                    <tr class="animate-pulse">
                        <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded w-3/4"></div></td>
                        <td class="px-3 py-4"><div class="h-4 bg-gray-200 rounded w-full"></div></td>
                        <td class="px-3 py-4 text-right"><div class="h-9 w-9 bg-gray-200 rounded-xl ml-auto"></div></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- ======================================================================= -->
<!-- MODAL AUTO-GENERATE KALENDER LIBUR (Escape Hatches: X, Backdrop, ESC)   -->
<!-- ======================================================================= -->
<div id="generateModal" role="dialog" aria-modal="true" aria-labelledby="gen-modal-title" class="fixed inset-0 z-[195] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-200">
    <div id="generateDialog" class="clay-panel rounded-[2rem] max-w-md w-full p-6 sm:p-7 relative transform scale-95 transition-transform duration-200">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-white">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="ph ph-magic-wand text-2xl"></i>
                </div>
                <h3 id="gen-modal-title" class="font-black text-base text-admin-textMain">Auto-Generate Kalender Libur</h3>
            </div>
            <button type="button" id="btn-close-gen-x" aria-label="Tutup modal" class="clay-btn w-11 h-11 rounded-xl bg-white text-gray-500 hover:text-rose-600 flex items-center justify-center">
                <i class="ph ph-x text-xl font-bold"></i>
            </button>
        </div>

        <form id="form-generate-libur" class="space-y-4">
            <p class="text-xs font-semibold text-admin-textMuted leading-relaxed">
                Sistem akan mendeteksi dan menambahkan tanggal libur secara otomatis ke kalender toko tanpa menduplikasi tanggal yang sudah ada.
            </p>

            <div>
                <label for="gen_year" class="block text-xs font-black text-admin-textMain mb-1.5">Pilih Tahun Kalender</label>
                <select id="gen_year" name="year" class="clay-input w-full px-4 py-3 rounded-xl text-sm font-black text-admin-textMain">
                    @php $currYear = (int) date('Y'); @endphp
                    <option value="{{ $currYear }}">{{ $currYear }} (Tahun Ini)</option>
                    <option value="{{ $currYear + 1 }}">{{ $currYear + 1 }} (Tahun Depan)</option>
                </select>
            </div>

            <div>
                <label for="gen_mode" class="block text-xs font-black text-admin-textMain mb-1.5">Cakupan Hari Libur</label>
                <select id="gen_mode" name="mode" class="clay-input w-full px-4 py-3 rounded-xl text-xs sm:text-sm font-bold text-admin-textMain">
                    <option value="all">Sabtu, Minggu & Hari Libur Nasional</option>
                    <option value="national_only">Hanya Hari Libur Nasional</option>
                    <option value="weekend_only">Hanya Akhir Pekan (Sabtu & Minggu)</option>
                </select>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 pt-3">
                <button type="button" id="btn-cancel-gen" class="clay-btn px-5 py-3 rounded-xl bg-white text-gray-700 font-black text-xs sm:text-sm">
                    Batal
                </button>
                <button type="submit" id="btn-exec-gen" class="clay-btn px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs sm:text-sm shadow-md flex items-center justify-center gap-2">
                    <i class="ph ph-sparkle text-lg"></i>
                    <span>Generate Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL KONFIRMASI HAPUS LIBUR (Escape Hatches: X, Backdrop, ESC)         -->
<!-- ======================================================================= -->
<div id="deleteHolidayModal" role="dialog" aria-modal="true" aria-labelledby="del-modal-title" class="fixed inset-0 z-[195] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-200">
    <div id="deleteHolidayDialog" class="clay-panel rounded-[2rem] max-w-sm w-full p-6 relative transform scale-95 transition-transform duration-200 text-center">
        <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
            <i class="ph ph-trash text-3xl"></i>
        </div>
        <h3 id="del-modal-title" class="font-black text-base text-admin-textMain mb-1">Hapus Tanggal Libur?</h3>
        <p id="del-modal-desc" class="text-xs font-semibold text-admin-textMuted leading-relaxed mb-5">
            Tanggal ini akan dibuka kembali untuk menerima pesanan pelanggan.
        </p>
        <div class="flex gap-2.5">
            <button type="button" id="btn-cancel-del" class="clay-btn flex-1 py-3 rounded-xl bg-white text-gray-700 font-black text-xs">
                Batal
            </button>
            <button type="button" id="btn-confirm-del" class="clay-btn flex-1 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs shadow-md">
                Ya, Hapus
            </button>
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
        const ALL_COURIERS = ['jne', 'sicepat', 'jnt', 'anteraja', 'ninja', 'pos', 'tiki', 'ide', 'sap', 'lion', 'wahana', 'spx', 'ncs', 'rex', 'rpx', 'sentral', 'star'];
        const DEFAULT_COURIERS = ['jne', 'sicepat', 'jnt'];

        let activeCouriersArray = [];
        let holidaysMaster = [];
        let holidayFilter = 'upcoming';
        let holidaySearch = '';
        let pendingDeleteAction = null;

        let opLoadController = null;
        let opSaveController = null;
        let liburLoadController = null;
        let liburActionController = null;

        const activeTimers = new Set();
        const boundListeners = [];

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

        function getCsrfHeaders() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            const input = document.querySelector('input[name="_token"]');
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': meta?.getAttribute('content') || input?.value || ''
            };
        }

        // =========================================================================
        // CONTEXTUAL TOAST NOTIFICATION
        // =========================================================================
        function showToast(title, message, isSuccess = true) {
            const toast = document.getElementById('op-toast');
            const iconBox = document.getElementById('op-toast-icon');
            const titleEl = document.getElementById('op-toast-title');
            const msgEl = document.getElementById('op-toast-msg');
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
        // SIMULATOR KAPASITAS & UI CHIPS KURIR
        // =========================================================================
        function updateCapacitySimulator() {
            const maxQty = parseInt(document.getElementById('max_qty_per_day')?.value, 10) || 0;
            const maxPerHour = parseInt(document.getElementById('max_orders_per_hour')?.value, 10) || 0;
            const startStr = document.getElementById('operational_time_start')?.value || '08:00';
            const endStr = document.getElementById('operational_time_end')?.value || '17:00';
            const freeDist = parseFloat(document.getElementById('free_delivery_max_distance')?.value) || 0;

            const startHour = parseInt(startStr.split(':')[0], 10) || 8;
            const endHour = parseInt(endStr.split(':')[0], 10) || 17;
            const totalHours = Math.max(0, endHour - startHour);
            const totalSlots = Math.max(0, (totalHours + 1) * maxPerHour);

            document.getElementById('sim-hours-badge').textContent = totalHours > 0 ? `${totalHours} Jam Operasional` : 'Jam Belum Valid';
            document.getElementById('sim-daily-qty').textContent = `${maxQty.toLocaleString('id-ID')} pcs`;
            document.getElementById('sim-daily-slots').textContent = `${totalSlots.toLocaleString('id-ID')} order`;
            document.getElementById('sim-free-dist').textContent = `${freeDist} KM`;
        }

        function updateCourierUI() {
            const chips = document.querySelectorAll('.courier-chip');
            chips.forEach(chip => {
                const code = chip.getAttribute('data-courier');
                const isActive = activeCouriersArray.includes(code);
                chip.setAttribute('aria-pressed', isActive ? 'true' : 'false');

                if (isActive) {
                    chip.classList.remove('is-idle');
                    chip.classList.add('is-active');
                } else {
                    chip.classList.remove('is-active');
                    chip.classList.add('is-idle');
                }
            });

            document.getElementById('active_couriers_input').value = activeCouriersArray.join(',');
            document.getElementById('active-courier-count').textContent = String(activeCouriersArray.length);
        }

        // =========================================================================
        // 1. FETCH & SUBMIT PENGATURAN OPERASIONAL
        // =========================================================================
        async function loadOperasional() {
            if (opLoadController) opLoadController.abort();
            opLoadController = new AbortController();

            try {
                const response = await fetch('/api/admin/operasional', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: opLoadController.signal
                });
                const res = await response.json();

                if (response.ok && res.status === 'success' && res.data) {
                    document.getElementById('max_qty_per_day').value = res.data.max_qty_per_day ?? 60;
                    document.getElementById('max_orders_per_hour').value = res.data.max_orders_per_hour ?? 5;
                    document.getElementById('operational_time_start').value = res.data.operational_time_start || '08:00';
                    document.getElementById('operational_time_end').value = res.data.operational_time_end || '17:00';
                    document.getElementById('free_delivery_max_distance').value = res.data.free_delivery_max_distance ?? 10;

                    const rawCouriers = res.data.active_couriers || 'jne,sicepat,jnt';
                    activeCouriersArray = rawCouriers.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
                    updateCourierUI();
                    updateCapacitySimulator();
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showToast('Gagal Memuat', 'Tidak dapat memuat pengaturan operasional.', false);
                }
            } finally {
                document.getElementById('op-skeleton')?.classList.add('hidden');
                document.getElementById('form-operasional')?.classList.remove('hidden');
            }
        }

        async function onOperasionalSubmit(e) {
            e.preventDefault();
            if (activeCouriersArray.length === 0) {
                showToast('Kurir Kosong', 'Pilih minimal 1 kurir ekspedisi aktif.', false);
                return;
            }

            if (opSaveController) opSaveController.abort();
            opSaveController = new AbortController();

            const btn = document.getElementById('btn-submit-op');
            const oriHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-xl"></i><span>Menyimpan...</span>';

            const payload = {
                max_qty_per_day: document.getElementById('max_qty_per_day').value,
                max_orders_per_hour: document.getElementById('max_orders_per_hour').value,
                operational_time_start: document.getElementById('operational_time_start').value,
                operational_time_end: document.getElementById('operational_time_end').value,
                free_delivery_max_distance: document.getElementById('free_delivery_max_distance').value,
                active_couriers: activeCouriersArray.join(',')
            };

            try {
                const response = await fetch('/api/admin/operasional', {
                    method: 'POST',
                    headers: getCsrfHeaders(),
                    body: JSON.stringify(payload),
                    signal: opSaveController.signal
                });

                const res = await response.json();
                if (response.ok && res.status === 'success') {
                    showToast('Tersimpan', res.message, true);
                } else {
                    showToast('Gagal Menyimpan', res.message || 'Periksa kembali input pengaturan operasional.', false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showToast('Gangguan Jaringan', 'Gagal menghubungi server.', false);
                }
            } finally {
                btn.disabled = false;
                btn.innerHTML = oriHtml;
            }
        }

        // =========================================================================
        // 2. FETCH & RENDER KALENDER HARI LIBUR
        // =========================================================================
        async function loadLibur() {
            if (liburLoadController) liburLoadController.abort();
            liburLoadController = new AbortController();

            try {
                const response = await fetch('/api/admin/libur', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: liburLoadController.signal
                });
                const res = await response.json();

                if (response.ok && res.status === 'success') {
                    holidaysMaster = Array.isArray(res.data) ? res.data : [];
                    renderHolidayTable();
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showToast('Gagal Memuat', 'Tidak dapat memuat kalender libur.', false);
                }
            }
        }

        function renderHolidayTable() {
            const tbody = document.getElementById('table-libur');
            if (!tbody) return;

            let upcomingCount = 0;
            let pastCount = 0;

            holidaysMaster.forEach(item => {
                if (item.time_state === 'past') pastCount++;
                else upcomingCount++;
            });

            document.getElementById('count-upcoming-holidays').textContent = String(upcomingCount);
            document.getElementById('count-all-holidays').textContent = String(holidaysMaster.length);
            document.getElementById('count-past-holidays').textContent = String(pastCount);

            const filtered = holidaysMaster.filter(item => {
                if (holidayFilter === 'upcoming' && item.time_state === 'past') {
                    return false;
                }
                if (holidaySearch !== '') {
                    const hay = `${item.holiday_date} ${item.formatted_date} ${item.description}`.toLowerCase();
                    return hay.includes(holidaySearch);
                }
                return true;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center py-12 px-4">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-admin-sideBg flex items-center justify-center mx-auto mb-2">
                                <i class="ph ph-calendar-check text-2xl"></i>
                            </div>
                            <p class="font-black text-admin-textMain text-xs sm:text-sm">Tidak Ada Jadwal Libur pada Filter Ini</p>
                            <p class="text-[11px] font-semibold text-admin-textMuted mt-0.5">Tambahkan manual atau gunakan tombol Auto-Generate di atas.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = filtered.map(item => {
                let stateBadge = '';
                if (item.time_state === 'today') {
                    stateBadge = '<span class="px-2 py-0.5 text-[9px] font-black rounded-md bg-rose-100 text-rose-700 uppercase shrink-0">Hari Ini</span>';
                } else if (item.time_state === 'past') {
                    stateBadge = '<span class="px-2 py-0.5 text-[9px] font-black rounded-md bg-gray-100 text-gray-500 uppercase shrink-0">Lewat</span>';
                }

                const orderWarning = item.active_orders_count > 0
                    ? `<span class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-black"><i class="ph ph-warning-circle"></i> ${item.active_orders_count} pesanan aktif</span>`
                    : '';

                return `
                    <tr class="group hover:bg-purple-50/40 transition-colors ${item.time_state === 'past' ? 'opacity-60' : ''}">
                        <td class="px-4 py-3.5 align-top">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="font-black text-admin-textMain text-xs sm:text-sm leading-snug">${item.formatted_date || item.holiday_date}</span>
                                ${stateBadge}
                            </div>
                            <span class="text-[10px] font-mono font-bold text-admin-textMuted block mt-0.5">${item.holiday_date}</span>
                        </td>
                        <td class="px-3 py-3.5 align-top">
                            <p class="font-bold text-admin-textMain text-xs sm:text-sm break-words leading-snug">${item.description}</p>
                            ${orderWarning}
                        </td>
                        <td class="px-3 py-3.5 text-right align-middle">
                            <button type="button" data-delete-id="${item.id}" data-delete-date="${item.formatted_date || item.holiday_date}" aria-label="Hapus tanggal libur" class="btn-delete-holiday clay-btn w-10 h-10 inline-flex items-center justify-center bg-white border border-rose-100 shadow-sm text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white shrink-0">
                                <i class="ph ph-trash text-base"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // =========================================================================
        // 3. TAMBAH, HAPUS, & AUTO-GENERATE LIBUR
        // =========================================================================
        async function onAddLiburSubmit(e) {
            e.preventDefault();
            const dateInput = document.getElementById('holiday_date');
            const descInput = document.getElementById('holiday_desc');
            const btn = document.getElementById('btn-add-libur');

            if (!dateInput.value || !descInput.value.trim()) {
                showToast('Form Belum Lengkap', 'Pilih tanggal dan isi keterangan libur terlebih dahulu.', false);
                return;
            }

            if (liburActionController) liburActionController.abort();
            liburActionController = new AbortController();

            const oriHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-lg"></i><span>Menambahkan...</span>';

            try {
                const response = await fetch('/api/admin/libur', {
                    method: 'POST',
                    headers: getCsrfHeaders(),
                    body: JSON.stringify({
                        holiday_date: dateInput.value,
                        description: descInput.value.trim()
                    }),
                    signal: liburActionController.signal
                });

                const res = await response.json();
                if (response.ok && res.status === 'success') {
                    dateInput.value = '';
                    descInput.value = '';
                    showToast('Berhasil Ditambahkan', res.message, true);
                    loadLibur();
                } else {
                    showToast('Gagal Menambahkan', res.message || 'Tanggal tersebut sudah terdaftar.', false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showToast('Gangguan Jaringan', 'Gagal menyimpan tanggal libur.', false);
                }
            } finally {
                btn.disabled = false;
                btn.innerHTML = oriHtml;
            }
        }

        async function executeDeleteHoliday() {
            if (!pendingDeleteAction) return;

            if (liburActionController) liburActionController.abort();
            liburActionController = new AbortController();

            const btn = document.getElementById('btn-confirm-del');
            const oriText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Menghapus...';

            const url = pendingDeleteAction.type === 'past'
                ? '/api/admin/libur-past/clear'
                : `/api/admin/libur/${encodeURIComponent(pendingDeleteAction.id)}`;

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: getCsrfHeaders(),
                    signal: liburActionController.signal
                });
                const res = await response.json();

                closeDeleteModal();
                if (response.ok && res.status === 'success') {
                    showToast('Berhasil Dihapus', res.message, true);
                    loadLibur();
                } else {
                    showToast('Gagal Menghapus', res.message || 'Tidak dapat menghapus data.', false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    closeDeleteModal();
                    showToast('Gangguan Jaringan', 'Gagal menghubungi server.', false);
                }
            } finally {
                btn.disabled = false;
                btn.textContent = oriText;
            }
        }

        async function onGenerateSubmit(e) {
            e.preventDefault();
            if (liburActionController) liburActionController.abort();
            liburActionController = new AbortController();

            const btn = document.getElementById('btn-exec-gen');
            const oriHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-lg"></i><span>Memproses...</span>';

            const year = document.getElementById('gen_year').value;
            const mode = document.getElementById('gen_mode').value;

            try {
                const response = await fetch('/api/admin/libur/generate', {
                    method: 'POST',
                    headers: getCsrfHeaders(),
                    body: JSON.stringify({ year, mode }),
                    signal: liburActionController.signal
                });

                const res = await response.json();
                closeGenerateModal();

                if (response.ok && res.status === 'success') {
                    showToast('Kalender Diperbarui', res.message, true);
                    loadLibur();
                } else {
                    showToast('Gagal Generate', res.message || 'Terjadi kendala saat generate kalender.', false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    closeGenerateModal();
                    showToast('Gangguan Jaringan', 'Gagal menghubungi server.', false);
                }
            } finally {
                btn.disabled = false;
                btn.innerHTML = oriHtml;
            }
        }

        // =========================================================================
        // MODAL HANDLERS (ESCAPE HATCHES: X, BACKDROP, ESC)
        // =========================================================================
        function openGenerateModal() {
            const modal = document.getElementById('generateModal');
            const dialog = document.getElementById('generateDialog');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            registerTimer(() => {
                modal.classList.remove('opacity-0');
                dialog.classList.remove('scale-95');
            }, 15);
        }

        function closeGenerateModal() {
            const modal = document.getElementById('generateModal');
            const dialog = document.getElementById('generateDialog');
            if (!modal || modal.classList.contains('hidden')) return;
            modal.classList.add('opacity-0');
            dialog.classList.add('scale-95');
            registerTimer(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }

        function openDeleteModal(actionObj, title, desc) {
            pendingDeleteAction = actionObj;
            document.getElementById('del-modal-title').textContent = title;
            document.getElementById('del-modal-desc').textContent = desc;

            const modal = document.getElementById('deleteHolidayModal');
            const dialog = document.getElementById('deleteHolidayDialog');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            registerTimer(() => {
                modal.classList.remove('opacity-0');
                dialog.classList.remove('scale-95');
            }, 15);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteHolidayModal');
            const dialog = document.getElementById('deleteHolidayDialog');
            if (!modal || modal.classList.contains('hidden')) return;
            modal.classList.add('opacity-0');
            dialog.classList.add('scale-95');
            registerTimer(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                pendingDeleteAction = null;
            }, 200);
        }

        // =========================================================================
        // NAMED EVENT LISTENERS
        // =========================================================================
        function onCourierContainerClick(e) {
            const chip = e.target.closest('.courier-chip');
            if (!chip) return;
            const code = chip.getAttribute('data-courier');
            if (!code) return;

            if (activeCouriersArray.includes(code)) {
                activeCouriersArray = activeCouriersArray.filter(c => c !== code);
            } else {
                activeCouriersArray.push(code);
            }
            updateCourierUI();
        }

        function onTableLiburClick(e) {
            const btn = e.target.closest('.btn-delete-holiday');
            if (!btn) return;
            const id = btn.getAttribute('data-delete-id');
            const dateLabel = btn.getAttribute('data-delete-date');
            openDeleteModal(
                { type: 'single', id },
                'Hapus Tanggal Libur?',
                `Tanggal ${dateLabel} akan dihapus dari daftar libur operasional.`
            );
        }

        function onClearPastClick() {
            const pastCount = holidaysMaster.filter(h => h.time_state === 'past').length;
            if (pastCount === 0) {
                showToast('Kalender Bersih', 'Tidak ada tanggal libur lampau yang perlu dihapus.', true);
                return;
            }
            openDeleteModal(
                { type: 'past', id: null },
                'Bersihkan Libur Lewat?',
                `Sebanyak ${pastCount} tanggal libur yang sudah lewat akan dihapus dari database.`
            );
        }

        function onHolidayTabClick(e) {
            const tab = e.target.closest('.holiday-tab');
            if (!tab) return;
            holidayFilter = tab.getAttribute('data-filter') || 'upcoming';

            document.querySelectorAll('.holiday-tab').forEach(btn => {
                const isSelected = btn.getAttribute('data-filter') === holidayFilter;
                btn.className = isSelected
                    ? 'holiday-tab px-3 py-1.5 rounded-lg text-[11px] font-black bg-admin-sideBg text-white transition-colors'
                    : 'holiday-tab px-3 py-1.5 rounded-lg text-[11px] font-black text-admin-textMuted hover:bg-gray-100 transition-colors';
            });
            renderHolidayTable();
        }

        function onKeyDown(e) {
            if (e.key === 'Escape') {
                closeGenerateModal();
                closeDeleteModal();
            }
        }

        // =========================================================================
        // INIT & TEARDOWN (ZERO MEMORY LEAK)
        // =========================================================================
        function initPage() {
            const formOp = document.getElementById('form-operasional');
            bindEvent(formOp, 'submit', onOperasionalSubmit);
            bindEvent(formOp, 'input', updateCapacitySimulator);

            bindEvent(document.getElementById('courier-container'), 'click', onCourierContainerClick);
            bindEvent(document.getElementById('btn-courier-default'), 'click', () => {
                activeCouriersArray = [...DEFAULT_COURIERS];
                updateCourierUI();
            });
            bindEvent(document.getElementById('btn-courier-all'), 'click', () => {
                activeCouriersArray = [...ALL_COURIERS];
                updateCourierUI();
            });

            bindEvent(document.getElementById('form-libur'), 'submit', onAddLiburSubmit);
            bindEvent(document.getElementById('table-libur'), 'click', onTableLiburClick);
            bindEvent(document.getElementById('btn-clear-past'), 'click', onClearPastClick);
            bindEvent(document.getElementById('search-holiday'), 'input', (e) => {
                holidaySearch = (e.target.value || '').toLowerCase().trim();
                renderHolidayTable();
            });

            document.querySelectorAll('.holiday-tab').forEach(tab => {
                bindEvent(tab, 'click', onHolidayTabClick);
            });

            // Modal Generate
            bindEvent(document.getElementById('btn-open-generate-modal'), 'click', openGenerateModal);
            bindEvent(document.getElementById('btn-close-gen-x'), 'click', closeGenerateModal);
            bindEvent(document.getElementById('btn-cancel-gen'), 'click', closeGenerateModal);
            bindEvent(document.getElementById('form-generate-libur'), 'submit', onGenerateSubmit);
            bindEvent(document.getElementById('generateModal'), 'click', (e) => {
                if (e.target.id === 'generateModal') closeGenerateModal();
            });

            // Modal Delete
            bindEvent(document.getElementById('btn-cancel-del'), 'click', closeDeleteModal);
            bindEvent(document.getElementById('btn-confirm-del'), 'click', executeDeleteHoliday);
            bindEvent(document.getElementById('deleteHolidayModal'), 'click', (e) => {
                if (e.target.id === 'deleteHolidayModal') closeDeleteModal();
            });

            bindEvent(document, 'keydown', onKeyDown);

            loadOperasional();
            loadLibur();
        }

        function cleanupPage() {
            [opLoadController, opSaveController, liburLoadController, liburActionController].forEach(ctrl => {
                if (ctrl) ctrl.abort();
            });
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