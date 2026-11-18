@extends('admin.admin_master')

@section('title', 'Batal & Refund - Admin Raya Kitchen')
@section('header_title', 'Batal & Refund')

@section('css')
<style>
    :root {
        /* 60-30-10 Claymorphism Token System */
        --clay-bg-base: #F5F2F8;
        --clay-surface: #FFFFFF;
        --clay-surface-Alt: #FAF7FC;
        --clay-primary: #9368B7;
        --clay-primary-hover: #7E54A3;
        --clay-accent-border: #E4CCFF;
        --clay-text-main: #2D2A32;
        --clay-text-muted: #716E77;
        --clay-danger: #E11D48;
        --clay-warning: #F59E0B;
        --clay-success: #10B981;

        /* Claymorphism Shadows (Ringan & Tanpa Blur Berlebih) */
        --clay-shadow-card:
            inset 0 -3px 6px rgba(147, 104, 183, 0.12),
            inset 0 3px 6px rgba(255, 255, 255, 0.95),
            0 10px 24px -6px rgba(147, 104, 183, 0.12);
        --clay-shadow-pressed:
            inset 0 3px 6px rgba(147, 104, 183, 0.18),
            inset 0 -2px 4px rgba(255, 255, 255, 0.85);

        /* Fluid Typography Tokens */
        --fs-title: clamp(1.125rem, 1.2vw + 0.85rem, 1.5rem);
        --fs-amount: clamp(1.25rem, 1.5vw + 0.9rem, 1.75rem);
        --fs-account: clamp(1.15rem, 1.4vw + 0.8rem, 1.5rem);
        --fs-body: clamp(0.8125rem, 0.3vw + 0.75rem, 0.9375rem);
    }

    /* Warna teks eksplisit dari token halaman ini (nggak bergantung config Tailwind / warna body layout) */
    .rfd-text-main    { color: var(--clay-text-main); }
    .rfd-text-muted   { color: var(--clay-text-muted); }
    .rfd-text-primary { color: var(--clay-primary); }

    .clay-panel {
        background-color: var(--clay-surface);
        color: var(--clay-text-main);
        box-shadow: var(--clay-shadow-card);
        border: 2px solid #FFFFFF;
    }

    .clay-inset {
        background-color: var(--clay-bg-base);
        box-shadow: var(--clay-shadow-pressed);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }

    /* PERF: tanpa will-change (tiap elemen jadi layer GPU sendiri), hover cuma di perangkat yang punya hover */
    .clay-btn {
        min-height: 44px;
        min-width: 44px;
        transition: transform 0.18s cubic-bezier(0.2, 0, 0, 1), opacity 0.18s ease;
    }
    @media (hover: hover) {
        .clay-btn:hover:not(:disabled) { transform: translateY(-2px); }
    }
    .clay-btn:active:not(:disabled) { transform: scale(0.96); }
    .clay-btn:focus-visible { outline: 3px solid var(--clay-primary); outline-offset: 2px; }
    .clay-btn:disabled { opacity: 0.55; cursor: not-allowed; }

    .order-card {
        cursor: pointer;
        border: 2px solid transparent;
        background-color: var(--clay-surface);
        box-shadow: 0 2px 8px rgba(147, 104, 183, 0.06);
        transition: transform 0.18s cubic-bezier(0.2, 0, 0, 1), background-color 0.18s ease, border-color 0.18s ease;
        /* PERF: kartu di luar layar nggak di-render sampai discroll */
        content-visibility: auto;
        contain-intrinsic-size: auto 96px;
    }
    @media (hover: hover) {
        .order-card:hover {
            transform: translateY(-2px);
            background-color: var(--clay-surface-Alt);
            border-color: var(--clay-accent-border);
        }
    }
    .order-card:active { transform: scale(0.98); }
    .order-card:focus-visible { outline: 3px solid var(--clay-primary); outline-offset: 2px; }
    .order-card.active {
        background-color: var(--clay-bg-base);
        border-color: var(--clay-primary);
        box-shadow: inset 0 -2px 5px rgba(147, 104, 183, 0.15), 0 6px 16px rgba(147, 104, 183, 0.16);
    }

    .upload-box {
        border: 2px dashed #CBD5E1;
        transition: border-color 0.18s ease, background-color 0.18s ease;
    }
    .upload-box:hover, .upload-box.dragover {
        border-color: var(--clay-primary);
        background-color: var(--clay-surface-Alt);
    }
    .upload-box.is-error {
        border-color: var(--clay-danger);
        background-color: #FFF1F2;
    }

    .fluid-title { font-size: var(--fs-title); }
    .fluid-amount { font-size: var(--fs-amount); }
    .fluid-account { font-size: var(--fs-account); }
    .fluid-body { font-size: var(--fs-body); }

    .thin-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .thin-scroll::-webkit-scrollbar-track { background: transparent; }
    .thin-scroll::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }

    /* Mobile: tampilan list <-> detail diatur lewat data-view (nggak lagi toggle class hidden/flex dari JS,
       jadi nggak ada bug panel kiri hilang kalau layar di-rotate ke lebar >= 1024px) */
    @media (max-width: 1023.98px) {
        #refund-root[data-view="list"] #right-panel { display: none; }
        #refund-root[data-view="detail"] #left-panel { display: none; }
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
<!-- Toast (dipindah ke <body> oleh JS biar nggak ketahan stacking context layout) -->
<div id="refund-toast" role="status" aria-live="polite" class="fixed top-6 right-6 z-[210] hidden items-center gap-3 px-5 py-3.5 rounded-2xl bg-white border-2 border-purple-100 shadow-2xl transition duration-200 opacity-0 -translate-y-2.5 max-w-sm">
    <div id="refund-toast-icon" class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
        <i class="ph ph-check-circle text-xl"></i>
    </div>
    <div class="min-w-0 flex-1">
        <p id="refund-toast-title" class="text-xs font-black text-gray-900">Informasi</p>
        <p id="refund-toast-msg" class="text-xs font-semibold text-gray-600 truncate">Berhasil diproses.</p>
    </div>
</div>

<div id="refund-root" data-view="list" class="relative z-0 mt-2 grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 lg:h-[calc(100dvh-140px)] min-h-[640px] max-w-full overflow-x-hidden">

    <!-- LEFT PANEL: DAFTAR ANTREAN & RIWAYAT REFUND -->
    <section id="left-panel" aria-label="Daftar Antrean Refund" class="lg:col-span-5 xl:col-span-4 clay-panel rounded-[2rem] flex flex-col overflow-hidden h-[calc(100dvh-150px)] lg:h-full">

        <!-- Search & Filter Area -->
        <div class="p-4 sm:p-5 border-b border-purple-100/80 bg-[#FAF8FC] shrink-0 space-y-3.5">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="font-black rfd-text-main text-sm uppercase tracking-wider">Antrean Refund</h2>
                    <p class="text-[11px] font-semibold rfd-text-muted">Pesanan batal yang memiliki dana masuk</p>
                </div>
                <button type="button" id="btn-refresh-refund" title="Muat ulang data" aria-label="Muat ulang daftar refund" class="clay-btn w-11 h-11 rounded-xl bg-white border border-purple-100 rfd-text-primary flex items-center justify-center shadow-sm">
                    <i id="icon-refresh-refund" class="ph ph-arrows-clockwise text-lg font-bold"></i>
                </button>
            </div>

            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-lg rfd-text-muted pointer-events-none"></i>
                <input type="search" id="search-refund" autocomplete="off" aria-label="Cari pesanan refund" placeholder="Cari No. Pesanan, Nama, atau Bank..." class="w-full min-h-[44px] pl-11 pr-4 py-2.5 clay-inset rounded-xl text-xs sm:text-sm font-bold rfd-text-main focus:outline-none focus:ring-2 focus:ring-purple-400">
            </div>

            <div class="grid grid-cols-2 gap-2.5" id="filter-chips" role="tablist">
                <button type="button" role="tab" aria-selected="true" class="chip-filter clay-btn px-3 py-2.5 bg-amber-500 text-white rounded-xl text-xs font-black shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.35)] flex items-center justify-center gap-1.5" data-status="pending">
                    <i class="ph ph-clock-countdown text-base"></i>
                    <span>Belum Refund (<span id="count-pending">0</span>)</span>
                </button>
                <button type="button" role="tab" aria-selected="false" class="chip-filter clay-btn px-3 py-2.5 bg-white border border-purple-100 text-emerald-700 rounded-xl text-xs font-black flex items-center justify-center gap-1.5" data-status="refunded">
                    <i class="ph ph-check-circle text-base"></i>
                    <span>Selesai (<span id="count-refunded">0</span>)</span>
                </button>
            </div>
        </div>

        <!-- Skeleton Loader List -->
        <div id="list-skeleton" class="flex-1 overflow-y-auto p-3.5 space-y-3">
            @for ($i = 0; $i < 4; $i++)
            <div class="p-4 rounded-2xl bg-gray-100/80 border border-white animate-pulse space-y-3">
                <div class="flex justify-between items-center">
                    <div class="h-4 bg-gray-200 rounded w-28"></div>
                    <div class="h-5 bg-gray-200 rounded-lg w-16"></div>
                </div>
                <div class="flex justify-between items-end">
                    <div class="space-y-1.5">
                        <div class="h-3.5 bg-gray-200 rounded w-36"></div>
                        <div class="h-3 bg-gray-200 rounded w-24"></div>
                    </div>
                    <div class="h-4 bg-gray-200 rounded w-20"></div>
                </div>
            </div>
            @endfor
        </div>

        <!-- List Scrollable -->
        <div class="hidden flex-1 overflow-y-auto thin-scroll p-3.5 space-y-2.5 relative" id="refund-list"></div>
    </section>

    <!-- RIGHT PANEL: DETAIL RESOLUSI REFUND -->
    <section id="right-panel" aria-label="Detail Resolusi Refund" class="lg:col-span-7 xl:col-span-8 clay-panel rounded-[2rem] flex flex-col relative overflow-hidden h-[calc(100dvh-150px)] lg:h-full">

        <!-- Empty State (Saat belum ada item yang dipilih) -->
        <div id="detail-empty" class="absolute inset-0 bg-white z-20 flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 clay-inset rounded-3xl flex items-center justify-center mb-5">
                <i class="ph ph-bank text-4xl rfd-text-primary"></i>
            </div>
            <h3 class="fluid-title font-black rfd-text-main mb-2">Pusat Resolusi Refund</h3>
            <p class="fluid-body font-medium rfd-text-muted max-w-md leading-relaxed">
                Pilih salah satu pesanan batal di panel kiri untuk memeriksa rincian rekening tujuan pelanggan dan mengunggah foto bukti pengembalian dana.
            </p>
        </div>

        <!-- Detail Konten -->
        <div id="detail-content" class="hidden flex-col h-full z-10 relative">

            <!-- Header Detail -->
            <div class="p-4 sm:p-6 border-b border-purple-100/80 flex flex-wrap justify-between items-center gap-3 bg-white shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" id="btn-back-mobile" aria-label="Kembali ke daftar" class="lg:hidden clay-btn w-11 h-11 flex items-center justify-center clay-inset rfd-text-main rounded-xl shrink-0">
                        <i class="ph ph-arrow-left text-lg font-bold"></i>
                    </button>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5 mb-1">
                            <h2 class="fluid-title font-black text-rose-600 tracking-tight truncate" id="det-order-id">#RK-0000</h2>
                            <div id="det-status-badge"></div>
                            <span id="det-source-badge" class="px-2.5 py-0.5 text-[10px] font-black rounded-lg bg-purple-50 rfd-text-primary border border-purple-200 uppercase">WEB</span>
                        </div>
                        <p class="text-xs font-bold rfd-text-muted flex items-center gap-1.5 truncate">
                            <i class="ph ph-clock rfd-text-primary shrink-0"></i>
                            <span>Dibatalkan: <span id="det-cancelled-at" class="rfd-text-main">-</span></span>
                        </p>
                    </div>
                </div>

                <a id="det-wa-link" href="#" target="_blank" rel="noopener noreferrer" class="clay-btn px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-black inline-flex items-center gap-2 shrink-0">
                    <i class="ph ph-whatsapp-logo text-lg"></i>
                    <span>Hubungi Pelanggan</span>
                </a>
            </div>

            <!-- Scrollable Info Area -->
            <div class="flex-1 overflow-y-auto thin-scroll p-4 sm:p-6 bg-[#FAF8FC] space-y-5">

                <!-- Alasan Pembatalan Banner -->
                <div class="p-4 rounded-2xl bg-rose-50/90 border border-rose-200 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white text-rose-600 flex items-center justify-center shrink-0 shadow-sm">
                        <i class="ph ph-warning-circle text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-wider text-rose-500">Alasan Pembatalan Pelanggan</p>
                        <p id="det-reason" class="text-xs sm:text-sm font-bold text-rose-900 mt-0.5 break-words">-</p>
                    </div>
                </div>

                <!-- Grid Informasi Nominal & Rekening Tujuan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Kartu Nominal Pengembalian & Pelanggan -->
                    <div class="clay-panel p-5 rounded-2xl flex flex-col justify-between space-y-4">
                        <div>
                            <h4 class="text-[11px] font-black rfd-text-muted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                                <i class="ph ph-user-circle text-base rfd-text-primary"></i>
                                <span>Data Pelanggan & Tagihan</span>
                            </h4>
                            <p class="font-black rfd-text-main text-sm sm:text-base" id="det-customer">-</p>
                            <p class="text-xs font-bold rfd-text-muted mt-0.5" id="det-phone">-</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 shadow-inner">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-black text-rose-600 uppercase tracking-wider">Nominal Wajib Refund</span>
                                <span id="det-paid-badge" class="text-[10px] font-black px-2 py-0.5 rounded-md bg-white text-rose-700 border border-rose-200">DP 30%</span>
                            </div>
                            <p class="fluid-amount font-black text-rose-700 leading-tight" id="det-refund-amount">Rp 0</p>
                            <p class="text-[11px] font-bold text-rose-600/90 mt-1" id="det-paid-info">-</p>
                        </div>
                    </div>

                    <!-- Kartu Rekening Tujuan (Claymorphism Accent) -->
                    <div class="bg-admin-sideBg p-5 rounded-2xl shadow-[inset_0_-4px_6px_rgba(0,0,0,0.2),inset_0_4px_6px_rgba(255,255,255,0.25),0_12px_24px_rgba(147,104,183,0.28)] border border-white/25 flex flex-col justify-between relative overflow-hidden text-white">
                        <i class="ph ph-credit-card absolute -right-4 -bottom-4 text-8xl text-white opacity-10 pointer-events-none"></i>

                        <div>
                            <h4 class="text-[11px] font-black text-white/80 uppercase tracking-widest mb-3 border-b border-white/15 pb-2 flex items-center gap-1.5">
                                <i class="ph ph-bank text-base"></i>
                                <span>Rekening Tujuan Transfer</span>
                            </h4>
                            <p class="text-[11px] font-bold text-white/75">Bank / E-Wallet</p>
                            <p class="font-black text-white text-base sm:text-lg tracking-wide mt-0.5" id="det-bank-name">-</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-white/15">
                            <p class="text-[11px] font-bold text-white/75 mb-1">Nomor Rekening / Tujuan</p>
                            <div class="flex items-center justify-between gap-2 bg-black/15 px-3.5 py-2 rounded-xl border border-white/15">
                                <p class="fluid-account font-black text-white tracking-wider font-mono truncate" id="det-account-num">-</p>
                                <button type="button" id="btn-copy-rekening" class="clay-btn w-11 h-11 flex items-center justify-center bg-white/20 text-white rounded-xl hover:bg-white/30 shrink-0" title="Salin Nomor Rekening" aria-label="Salin Nomor Rekening">
                                    <i id="icon-copy-rekening" class="ph ph-copy text-lg font-bold"></i>
                                </button>
                            </div>
                            <p class="font-extrabold text-white/95 text-xs sm:text-sm mt-2.5 uppercase tracking-wide flex items-center gap-1.5">
                                <i class="ph ph-identification-card text-base"></i>
                                <span id="det-account-name">A.N -</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Rincian Item yang Dibatalkan -->
                <div class="clay-panel p-5 rounded-2xl">
                    <h4 class="text-[11px] font-black rfd-text-muted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                        <i class="ph ph-shopping-bag text-base rfd-text-primary"></i>
                        <span>Ringkasan Item Pesanan</span>
                    </h4>
                    <div id="det-items-list" class="divide-y divide-gray-100 text-xs sm:text-sm"></div>
                </div>

                <!-- Form Upload / Bukti Refund -->
                <div class="clay-panel rounded-2xl overflow-hidden p-5">
                    <h4 class="text-[11px] font-black rfd-text-muted uppercase tracking-widest mb-4 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                        <i class="ph ph-receipt text-base rfd-text-primary"></i>
                        <span>Bukti Transfer Pengembalian Dana</span>
                    </h4>

                    <!-- Area Upload (Jika status = pending) -->
                    <div id="area-upload" class="hidden">
                        <form id="form-refund" class="space-y-4" novalidate>
                            <div id="upload-dropzone" class="upload-box relative w-full min-h-[180px] rounded-2xl flex flex-col items-center justify-center bg-[#FAF8FC] p-4 text-center cursor-pointer overflow-hidden">
                                <input type="file" id="refund_file" name="refund_proof" accept="image/jpeg,image/png,image/jpg,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required aria-label="Unggah foto bukti transfer">
                                <div class="flex flex-col items-center pointer-events-none">
                                    <div class="w-12 h-12 rounded-2xl bg-purple-100 rfd-text-primary flex items-center justify-center mb-2.5 shadow-inner">
                                        <i class="ph ph-upload-simple text-2xl font-bold"></i>
                                    </div>
                                    <p class="text-xs sm:text-sm font-black rfd-text-main mb-1">Klik atau seret foto bukti transfer ke sini</p>
                                    <p class="text-[11px] font-semibold rfd-text-muted">Format JPG, PNG, WEBP (Maksimal 2MB)</p>
                                </div>
                                <img id="preview-image" decoding="async" class="absolute inset-0 w-full h-full object-contain bg-slate-900/90 p-2 rounded-2xl hidden z-10 pointer-events-none" alt="Preview Bukti Refund">
                            </div>

                            <div id="selected-file-bar" class="hidden items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-purple-50 border border-purple-200 text-xs">
                                <span id="selected-file-name" class="font-bold rfd-text-primary truncate">-</span>
                                <button type="button" id="btn-clear-file" class="clay-btn px-3 py-1.5 rounded-lg bg-white text-rose-600 font-black text-[11px] border border-rose-200 shrink-0">
                                    Ganti Foto
                                </button>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-end items-stretch sm:items-center gap-3 pt-2">
                                <button type="submit" id="btn-open-confirm" class="clay-btn px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-xs sm:text-sm shadow-[inset_0_-3px_4px_rgba(0,0,0,0.18),inset_0_3px_4px_rgba(255,255,255,0.35),0_8px_16px_rgba(16,185,129,0.25)] flex items-center justify-center gap-2">
                                    <i class="ph ph-check-circle text-xl"></i>
                                    <span>Konfirmasi Selesai Refund</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Area Bukti (Jika status = refunded) -->
                    <div id="area-proof" class="hidden">
                        <div class="flex flex-col sm:flex-row gap-5 items-start">
                            <button type="button" id="btn-zoom-proof" class="clay-btn relative w-full sm:w-48 h-48 rounded-2xl overflow-hidden border-2 border-emerald-200 bg-gray-100 shadow-sm shrink-0 group">
                                <img id="img-proof" loading="lazy" decoding="async" alt="Bukti Refund Tersimpan" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-900/45 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-white">
                                    <i class="ph ph-magnifying-glass-plus text-3xl mb-1"></i>
                                    <span class="text-[11px] font-black">Perbesar Bukti</span>
                                </div>
                            </button>
                            <div class="flex-1 bg-emerald-50 p-5 rounded-2xl border border-emerald-200 w-full space-y-2">
                                <div class="flex items-center gap-2 text-emerald-800 font-black text-sm sm:text-base">
                                    <i class="ph ph-seal-check text-2xl text-emerald-600"></i>
                                    <span>Dana Berhasil Dikembalikan</span>
                                </div>
                                <p class="text-xs font-semibold text-emerald-700 leading-relaxed">
                                    Bukti transfer pengembalian dana untuk pesanan ini telah diverifikasi dan dikirimkan ke halaman Nota Digital serta WhatsApp pelanggan.
                                </p>
                                <div class="pt-2">
                                    <button type="button" id="btn-zoom-proof-text" class="clay-btn px-4 py-2 rounded-xl bg-white text-emerald-700 border border-emerald-300 font-black text-xs inline-flex items-center gap-1.5">
                                        <i class="ph ph-corners-out text-base"></i>
                                        <span>Lihat Gambar Ukuran Penuh</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODAL KONFIRMASI SELESAIKAN REFUND (dipindah ke <body> oleh JS; ESC / klik backdrop / tombol X) -->
<div id="rfd-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="rfd-confirm-title" class="fixed inset-0 z-[190] hidden items-center justify-center bg-slate-900/60 p-4 opacity-0 transition-opacity duration-200">
    <div class="clay-panel rounded-[2rem] max-w-md w-full p-6 sm:p-7 relative transform scale-95 transition-transform duration-200">
        <div class="flex items-center justify-between gap-3 pb-3 mb-4 border-b border-gray-100">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="ph ph-shield-check text-2xl"></i>
                </div>
                <h3 id="rfd-confirm-title" class="font-black text-base rfd-text-main">Konfirmasi Penyelesaian Refund</h3>
            </div>
            <button type="button" id="btn-close-confirm-x" aria-label="Tutup modal konfirmasi" class="clay-btn w-11 h-11 rounded-xl bg-gray-100 text-gray-500 hover:text-rose-600 flex items-center justify-center shrink-0">
                <i class="ph ph-x text-xl font-bold"></i>
            </button>
        </div>

        <!-- Teks default biar modal nggak pernah kosong walau JS telat/gagal -->
        <p id="rfd-confirm-desc" class="text-xs sm:text-sm font-semibold rfd-text-muted leading-relaxed mb-4 text-center">Pastikan dana sudah ditransfer ke rekening pelanggan sebelum menyimpan bukti.</p>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 pt-2">
            <button type="button" id="btn-cancel-confirm" class="clay-btn px-5 py-3 rounded-xl bg-gray-100 text-gray-700 font-black text-xs sm:text-sm">
                Periksa Kembali
            </button>
            <button type="button" id="btn-execute-refund" class="clay-btn px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs sm:text-sm shadow-md flex items-center justify-center gap-2">
                <i class="ph ph-paper-plane-tilt text-lg"></i>
                <span>Ya, Simpan & Kirim Bukti</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW GAMBAR BUKTI TRANSFER -->
<div id="rfd-zoom-modal" role="dialog" aria-modal="true" aria-label="Preview Bukti Transfer" class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-900/80 p-4 opacity-0 transition-opacity duration-200">
    <div class="bg-white rounded-[2rem] p-4 max-w-2xl w-full max-h-[90dvh] flex flex-col shadow-2xl transform scale-95 transition-transform duration-200">
        <div class="flex items-center justify-between pb-3 mb-2 border-b border-gray-100">
            <span id="zoom-modal-title" class="text-xs font-black uppercase tracking-wider rfd-text-main">Bukti Transfer Pengembalian Dana</span>
            <button type="button" id="btn-close-zoom-x" aria-label="Tutup preview gambar" class="clay-btn w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i class="ph ph-x text-xl font-bold"></i>
            </button>
        </div>
        <div class="flex-1 overflow-auto flex items-center justify-center bg-slate-950/90 rounded-2xl p-2 min-h-[260px]">
            <img id="zoom-modal-image" decoding="async" alt="Bukti Refund Ukuran Penuh" class="max-h-[72dvh] w-auto object-contain rounded-xl">
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (() => {
        'use strict';

        // =====================================================================
        // STATE
        // =====================================================================
        let refundOrders = [];
        let activeFilter = 'pending';
        let searchQuery = '';
        let activeOrderNum = null;
        let isSubmitting = false;

        let fetchAbort = null;
        let currentObjectUrl = null;
        let lastFocusEl = null;
        let searchTimer = null;
        let toastTimer = null;
        let toastHideTimer = null;
        const modalTimers = new WeakMap();

        const MAX_FILE_SIZE = 2 * 1024 * 1024;
        const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

        // =====================================================================
        // HELPERS
        // =====================================================================
        // Satu instance Intl.NumberFormat dipakai ulang (bikin baru per panggilan itu mahal)
        const rupiahFmt = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
        const formatRupiah = (num) => rupiahFmt.format(Number(num) || 0);

        // Escape HTML: data dari pelanggan (nama, bank, dll) TIDAK BOLEH masuk innerHTML mentah
        const ESC_MAP = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ESC_MAP[c]);

        const setText = (el, value) => { if (el) el.textContent = value; };
        const on = (el, type, fn, opts) => { if (el) el.addEventListener(type, fn, opts); };

        // Cache semua elemen sekali di awal (id kebab/snake -> camelCase)
        const ELEMENT_IDS = [
            'refund-root', 'left-panel', 'right-panel', 'refund-list', 'list-skeleton', 'search-refund',
            'filter-chips', 'count-pending', 'count-refunded', 'btn-refresh-refund', 'icon-refresh-refund',
            'detail-empty', 'detail-content', 'btn-back-mobile',
            'det-order-id', 'det-status-badge', 'det-source-badge', 'det-cancelled-at', 'det-wa-link',
            'det-reason', 'det-customer', 'det-phone', 'det-refund-amount', 'det-paid-badge', 'det-paid-info',
            'det-bank-name', 'det-account-num', 'btn-copy-rekening', 'icon-copy-rekening', 'det-account-name',
            'det-items-list',
            'area-upload', 'area-proof', 'form-refund', 'upload-dropzone', 'refund_file', 'preview-image',
            'selected-file-bar', 'selected-file-name', 'btn-clear-file', 'btn-open-confirm',
            'btn-zoom-proof', 'img-proof', 'btn-zoom-proof-text',
            'rfd-confirm-modal', 'rfd-confirm-title', 'rfd-confirm-desc', 'btn-close-confirm-x',
            'btn-cancel-confirm', 'btn-execute-refund',
            'rfd-zoom-modal', 'zoom-modal-title', 'zoom-modal-image', 'btn-close-zoom-x',
            'refund-toast', 'refund-toast-icon', 'refund-toast-title', 'refund-toast-msg'
        ];
        const els = {};
        function cacheElements() {
            ELEMENT_IDS.forEach((id) => {
                const key = id.replace(/[-_]([a-z0-9])/g, (_, c) => c.toUpperCase());
                els[key] = document.getElementById(id);
            });
        }

        // Modal & toast dipindah ke <body>: lepas dari stacking context / overflow / transform layout admin
        function teleportToBody() {
            [els.refundToast, els.rfdConfirmModal, els.rfdZoomModal].forEach((el) => {
                if (el && el.parentElement !== document.body) document.body.appendChild(el);
            });
        }

        async function requestJson(url, options = {}) {
            const response = await fetch(url, {
                ...options,
                headers: { 'Accept': 'application/json', ...(options.headers || {}) }
            });
            let data = null;
            try { data = await response.json(); } catch (_) { /* respons bukan JSON */ }
            return { ok: response.ok, status: response.status, data };
        }

        function emitRefundUpdated(pendingCount) {
            window.dispatchEvent(new CustomEvent('refund:updated', { detail: { pending_count: pendingCount } }));
        }

        function withSearchKey(order) {
            // Haystack dihitung sekali per data, bukan tiap ketikan
            order._q = [order.order_number, order.recipient_name, order.bank_name, order.account_number]
                .map((v) => v ?? '').join(' ').toLowerCase();
            return order;
        }
        const normalizeOrders = (list) => (Array.isArray(list) ? list : []).map(withSearchKey);
        const findOrder = (num) => refundOrders.find((o) => String(o.order_number) === String(num));

        // =====================================================================
        // TOAST
        // =====================================================================
        function hideToast() {
            const toast = els.refundToast;
            if (!toast) return;
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', '-translate-y-2.5');
            clearTimeout(toastHideTimer);
            toastHideTimer = setTimeout(() => {
                toast.classList.remove('flex');
                toast.classList.add('hidden');
            }, 200);
        }

        function showToast(title, message, isSuccess = true) {
            const toast = els.refundToast;
            if (!toast) return;

            setText(els.refundToastTitle, title);
            setText(els.refundToastMsg, message);

            if (els.refundToastIcon) {
                els.refundToastIcon.className = isSuccess
                    ? 'w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0'
                    : 'w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0';
                els.refundToastIcon.innerHTML = isSuccess
                    ? '<i class="ph ph-check-circle text-xl"></i>'
                    : '<i class="ph ph-warning-circle text-xl"></i>';
            }

            clearTimeout(toastTimer);
            clearTimeout(toastHideTimer);
            toast.classList.remove('hidden');
            toast.classList.add('flex');
            void toast.offsetWidth; // paksa reflow biar transisi jalan tanpa setTimeout
            toast.classList.remove('opacity-0', '-translate-y-2.5');
            toast.classList.add('opacity-100', 'translate-y-0');
            toastTimer = setTimeout(hideToast, 3200);
        }

        // =====================================================================
        // MODAL (satu helper buat semua modal)
        // =====================================================================
        function showModal(modal, focusEl) {
            if (!modal) return;
            clearTimeout(modalTimers.get(modal));
            lastFocusEl = document.activeElement;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
            if (focusEl) focusEl.focus({ preventScroll: true });
        }

        function hideModal(modal) {
            if (!modal || modal.classList.contains('hidden')) return;
            modal.classList.add('opacity-0');
            modal.firstElementChild.classList.add('scale-95');
            clearTimeout(modalTimers.get(modal));
            modalTimers.set(modal, setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                if (lastFocusEl && typeof lastFocusEl.focus === 'function') lastFocusEl.focus({ preventScroll: true });
            }, 200));
        }

        function openConfirmModal() {
            const order = findOrder(activeOrderNum);
            if (!order) {
                showToast('Kesalahan Sinkronisasi', 'Data pesanan tidak ditemukan. Muat ulang daftar.', false);
                return;
            }
            if (!order.has_valid_account) {
                showToast('Rekening Belum Ada', 'Pelanggan belum mengirim nomor rekening refund.', false);
                return;
            }

            const amount = formatRupiah(order.refund_amount);
            const bank = `${order.bank_name || '-'} (${order.account_number || '-'})`;

            setText(els.rfdConfirmTitle, 'Konfirmasi Penyelesaian Refund');
            if (els.rfdConfirmDesc) {
                // Semua data dinamis di-escape (bank_name & account_number datang dari input pelanggan)
                els.rfdConfirmDesc.innerHTML =
                    `Pastikan dana sebesar <strong class="font-black text-rose-600">${esc(amount)}</strong> ` +
                    `sudah ditransfer ke rekening <strong class="font-black rfd-text-main">${esc(bank)}</strong> ` +
                    `untuk pesanan <strong class="font-black rfd-text-primary">#${esc(order.order_number)}</strong>.`;
            }

            showModal(els.rfdConfirmModal, els.btnCancelConfirm); // fokus awal ke tombol aman
        }

        function closeConfirmModal() {
            if (isSubmitting) return; // jangan tutup di tengah upload
            hideModal(els.rfdConfirmModal);
        }

        function openZoomModal() {
            const order = findOrder(activeOrderNum);
            if (!order || !order.refund_proof_url || !els.zoomModalImage) return;

            els.zoomModalImage.src = order.refund_proof_url;
            setText(els.zoomModalTitle, `Bukti Refund #${order.order_number}`);
            showModal(els.rfdZoomModal, els.btnCloseZoomX);
        }

        const closeZoomModal = () => hideModal(els.rfdZoomModal);

        // =====================================================================
        // DATA
        // =====================================================================
        async function fetchRefundOrders({ silent = false } = {}) {
            if (fetchAbort) fetchAbort.abort();
            const ctrl = new AbortController();
            fetchAbort = ctrl;

            if (els.iconRefreshRefund) els.iconRefreshRefund.classList.add('animate-spin');
            if (!silent) {
                if (els.listSkeleton) els.listSkeleton.classList.remove('hidden');
                if (els.refundList) els.refundList.classList.add('hidden');
            }

            try {
                const { status, data } = await requestJson('/api/admin/batal-refund', { signal: ctrl.signal });

                if (data && data.status === 'success' && data.data) {
                    refundOrders = normalizeOrders(data.data.orders);
                    emitRefundUpdated(data.data.summary?.pending_count ?? 0);
                    renderList();

                    if (activeOrderNum !== null && findOrder(activeOrderNum)) {
                        showDetail(activeOrderNum); // file yang sudah dipilih admin TIDAK ikut ke-reset
                    }
                } else {
                    showToast('Gagal Memuat', data?.message || `Tidak dapat memuat data refund (HTTP ${status}).`, false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showToast('Gangguan Jaringan', 'Gagal terhubung ke server.', false);
                }
            } finally {
                // Hanya request terbaru yang boleh mengubah UI (hindari skeleton berkedip saat request lama di-abort)
                if (fetchAbort === ctrl) {
                    fetchAbort = null;
                    if (els.listSkeleton) els.listSkeleton.classList.add('hidden');
                    if (els.refundList) els.refundList.classList.remove('hidden');
                    if (els.iconRefreshRefund) els.iconRefreshRefund.classList.remove('animate-spin');
                }
            }
        }

        // =====================================================================
        // RENDER LIST
        // =====================================================================
        const CARD_BADGE = {
            pending: '<span class="px-2.5 py-0.5 text-[10px] rounded-lg font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">Menunggu</span>',
            refunded: '<span class="px-2.5 py-0.5 text-[10px] rounded-lg font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">Selesai</span>'
        };
        const DETAIL_BADGE = {
            pending: '<span class="px-2.5 py-1 text-[10px] rounded-lg font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1"><i class="ph ph-clock-countdown"></i> Belum Refund</span>',
            refunded: '<span class="px-2.5 py-1 text-[10px] rounded-lg font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1"><i class="ph ph-check-circle"></i> Selesai Refund</span>'
        };

        function emptyStateHtml() {
            const isPending = activeFilter === 'pending';
            const title = isPending ? 'Tidak Ada Antrean Refund' : 'Belum Ada Riwayat Refund Selesai';
            const desc = isPending
                ? 'Seluruh pengembalian dana pelanggan sudah diselesaikan.'
                : 'Pesanan yang telah direfund akan tercatat di tab ini.';
            return `
                <div class="py-14 px-4 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl clay-inset flex items-center justify-center rfd-text-primary mb-3">
                        <i class="ph ph-check-square-offset text-3xl"></i>
                    </div>
                    <p class="font-black rfd-text-main text-sm mb-1">${title}</p>
                    <p class="text-xs font-medium rfd-text-muted max-w-[240px]">${desc}</p>
                </div>`;
        }

        function cardHtml(o) {
            const isActive = String(o.order_number) === String(activeOrderNum) ? ' active' : '';
            return `
                <button type="button" class="order-card w-full text-left p-4 rounded-2xl focus:outline-none${isActive}" data-id="${esc(o.order_number)}">
                    <div class="flex justify-between items-start gap-2 mb-2">
                        <span class="font-black rfd-text-main text-sm truncate">#${esc(o.order_number)}</span>
                        ${CARD_BADGE[o.refund_status] || ''}
                    </div>
                    <div class="flex justify-between items-end gap-2">
                        <div class="min-w-0">
                            <p class="font-bold rfd-text-main text-xs truncate flex items-center gap-1.5">
                                <i class="ph ph-user rfd-text-primary"></i>
                                <span class="truncate">${esc(o.recipient_name)}</span>
                            </p>
                            <p class="text-[11px] font-semibold rfd-text-muted truncate mt-0.5 flex items-center gap-1">
                                <i class="ph ph-bank"></i>
                                <span class="truncate">${esc(o.bank_name)} • ${esc(o.payment_type_label)}</span>
                            </p>
                        </div>
                        <span class="font-black text-rose-600 text-xs sm:text-sm shrink-0">${formatRupiah(o.refund_amount)}</span>
                    </div>
                </button>`;
        }

        function renderList() {
            if (!els.refundList) return;

            const counts = { pending: 0, refunded: 0 };
            const visible = [];
            for (const o of refundOrders) {
                if (searchQuery && !o._q.includes(searchQuery)) continue;
                if (o.refund_status === 'pending' || o.refund_status === 'refunded') counts[o.refund_status]++;
                if (o.refund_status === activeFilter) visible.push(o);
            }

            setText(els.countPending, String(counts.pending));
            setText(els.countRefunded, String(counts.refunded));

            els.refundList.innerHTML = visible.length ? visible.map(cardHtml).join('') : emptyStateHtml();
        }

        // Ganti kartu aktif tanpa render ulang seluruh list
        function markActiveCard() {
            if (!els.refundList) return;
            const activeId = String(activeOrderNum);
            for (const card of els.refundList.children) {
                card.classList.toggle('active', card.dataset.id === activeId);
            }
        }

        const CHIP_BASE = 'chip-filter clay-btn px-3 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 ';
        const CHIP_SHADOW = 'shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.35)]';
        const CHIP_ON = { pending: `bg-amber-500 text-white ${CHIP_SHADOW}`, refunded: `bg-emerald-600 text-white ${CHIP_SHADOW}` };
        const CHIP_OFF = {
            pending: 'bg-white border border-purple-100 text-amber-700',
            refunded: 'bg-white border border-purple-100 text-emerald-700'
        };

        function setActiveFilterTab(status) {
            activeFilter = status;
            document.querySelectorAll('.chip-filter').forEach((chip) => {
                const chipStatus = chip.dataset.status;
                const selected = chipStatus === status;
                chip.setAttribute('aria-selected', selected ? 'true' : 'false');
                chip.className = CHIP_BASE + (selected ? CHIP_ON[chipStatus] : CHIP_OFF[chipStatus]);
            });
            renderList();
        }

        // =====================================================================
        // DETAIL
        // =====================================================================
        const BTN_READY = '<i class="ph ph-check-circle text-xl"></i><span>Konfirmasi Selesai Refund</span>';
        const BTN_WAITING = '<i class="ph ph-hourglass-medium text-xl"></i><span>Menunggu Pembeli Mengirim Nomor Rekening...</span>';

        const setMobileView = (view) => { if (els.refundRoot) els.refundRoot.dataset.view = view; };

        function showDetail(orderNum) {
            const order = findOrder(orderNum);
            if (!order) return;

            const sameOrder = String(activeOrderNum) === String(order.order_number);
            activeOrderNum = order.order_number;
            markActiveCard();
            setMobileView('detail');

            if (els.detailEmpty) els.detailEmpty.classList.add('hidden');
            if (els.detailContent) {
                els.detailContent.classList.remove('hidden');
                els.detailContent.classList.add('flex');
            }

            setText(els.detOrderId, '#' + order.order_number);
            setText(els.detCancelledAt, order.cancelled_at);
            setText(els.detReason, order.cancellation_reason);
            setText(els.detCustomer, order.recipient_name);
            setText(els.detPhone, order.recipient_whatsapp);
            setText(els.detSourceBadge, (order.order_source || 'web').toUpperCase());
            if (els.detStatusBadge) els.detStatusBadge.innerHTML = DETAIL_BADGE[order.refund_status] || '';

            if (els.detWaLink) {
                const url = String(order.wa_chat_url || '');
                if (/^https?:\/\//i.test(url)) { // tolak javascript:, data:, dll
                    els.detWaLink.href = url;
                    els.detWaLink.classList.remove('hidden');
                } else {
                    els.detWaLink.classList.add('hidden');
                }
            }

            setText(els.detRefundAmount, formatRupiah(order.refund_amount));
            setText(els.detPaidBadge, order.payment_type_label);
            setText(els.detPaidInfo, `Dari total pesanan ${formatRupiah(order.total_amount)} (${order.payment_method} - ${order.payment_type_label})`);

            setText(els.detBankName, order.bank_name || '-');
            setText(els.detAccountNum, order.account_number || '-');
            setText(els.detAccountName, 'A.N ' + (order.account_holder || order.recipient_name));

            if (els.detItemsList) {
                els.detItemsList.innerHTML = Array.isArray(order.items) && order.items.length > 0
                    ? order.items.map((it) => `
                        <div class="py-2.5 flex justify-between items-center gap-2">
                            <span class="font-bold rfd-text-main">${esc(it.name)} <span class="rfd-text-primary font-black">x${esc(it.qty)}</span></span>
                            <span class="font-extrabold rfd-text-muted">${formatRupiah(it.subtotal)}</span>
                        </div>`).join('')
                    : '<p class="text-xs rfd-text-muted py-2">Tidak ada rincian item.</p>';
            }

            const isPending = order.refund_status === 'pending';

            // File yang sudah dipilih baru di-reset kalau pindah pesanan / status berubah.
            // (Dulu tiap refresh data = file terpilih hilang.)
            if (!sameOrder || !isPending) resetFileSelection();

            if (els.areaUpload) els.areaUpload.classList.toggle('hidden', !isPending);
            if (els.areaProof) els.areaProof.classList.toggle('hidden', isPending);

            if (isPending) {
                if (els.btnOpenConfirm) {
                    const ready = Boolean(order.has_valid_account);
                    els.btnOpenConfirm.disabled = !ready;
                    els.btnOpenConfirm.innerHTML = ready ? BTN_READY : BTN_WAITING;
                }
            } else if (order.refund_status === 'refunded' && els.imgProof && order.refund_proof_url) {
                els.imgProof.src = order.refund_proof_url;
            }
        }

        // =====================================================================
        // FILE UPLOAD
        // =====================================================================
        function revokePreviewUrl() {
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }
        }

        function resetFileSelection() {
            revokePreviewUrl();
            if (els.formRefund) els.formRefund.reset();
            if (els.previewImage) {
                els.previewImage.removeAttribute('src');
                els.previewImage.classList.add('hidden');
            }
            if (els.selectedFileBar) {
                els.selectedFileBar.classList.remove('flex');
                els.selectedFileBar.classList.add('hidden');
            }
            if (els.uploadDropzone) els.uploadDropzone.classList.remove('dragover', 'is-error');
        }

        function onFileInputChange(e) {
            const file = e.target.files && e.target.files[0];
            revokePreviewUrl();
            if (els.uploadDropzone) els.uploadDropzone.classList.remove('dragover', 'is-error');

            if (!file) {
                resetFileSelection();
                return;
            }

            if (file.size > MAX_FILE_SIZE) {
                showToast('Ukuran File Terlalu Besar', 'Maksimal ukuran gambar bukti transfer adalah 2MB.', false);
                resetFileSelection();
                return;
            }
            if (!ALLOWED_TYPES.includes(file.type)) {
                showToast('Format Ditolak', 'Hanya boleh upload JPG, JPEG, PNG, atau WebP.', false);
                resetFileSelection();
                return;
            }

            currentObjectUrl = URL.createObjectURL(file);
            if (els.previewImage) {
                els.previewImage.src = currentObjectUrl;
                els.previewImage.classList.remove('hidden');
            }
            setText(els.selectedFileName, `${file.name} (${(file.size / 1024).toFixed(0)} KB)`);
            if (els.selectedFileBar) {
                els.selectedFileBar.classList.remove('hidden');
                els.selectedFileBar.classList.add('flex');
            }
        }

        function onFormSubmit(e) {
            e.preventDefault();
            const hasFile = els.refundFile && els.refundFile.files && els.refundFile.files.length > 0;

            if (!hasFile) {
                showToast('Bukti Belum Diunggah', 'Silakan klik atau seret foto bukti transfer terlebih dahulu.', false);
                if (els.uploadDropzone) {
                    els.uploadDropzone.classList.add('is-error');
                    setTimeout(() => els.uploadDropzone && els.uploadDropzone.classList.remove('is-error'), 2000);
                }
                return;
            }
            openConfirmModal();
        }

        // =====================================================================
        // SUBMIT BUKTI REFUND
        // =====================================================================
        async function executeRefundSubmit() {
            if (isSubmitting) return; // cegah double submit (bukan abort request yang lagi jalan)

            const file = els.refundFile && els.refundFile.files && els.refundFile.files[0];
            if (!file || activeOrderNum === null) {
                hideModal(els.rfdConfirmModal);
                showToast('File Belum Dipilih', 'Silakan pilih foto bukti transfer terlebih dahulu.', false);
                return;
            }

            isSubmitting = true;
            const orderNum = activeOrderNum;
            const btnExec = els.btnExecuteRefund;
            const oriExecHtml = btnExec ? btnExec.innerHTML : '';

            if (btnExec) {
                btnExec.disabled = true;
                btnExec.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-lg"></i><span>Mengunggah...</span>';
            }
            if (els.btnOpenConfirm) els.btnOpenConfirm.disabled = true;

            try {
                const formData = new FormData();
                formData.append('refund_proof', file);

                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const headers = csrfMeta ? { 'X-CSRF-TOKEN': csrfMeta.getAttribute('content') } : {};

                const { ok, status, data } = await requestJson(
                    `/api/admin/batal-refund/${encodeURIComponent(orderNum)}/proses`,
                    { method: 'POST', headers, body: formData }
                );

                isSubmitting = false; // lepas dulu supaya modal bisa ditutup
                hideModal(els.rfdConfirmModal);

                if (ok && data && data.status === 'success') {
                    showToast('Refund Selesai', data.message || 'Bukti refund berhasil disimpan.', true);

                    const idx = refundOrders.findIndex((o) => String(o.order_number) === String(orderNum));
                    if (idx !== -1 && data.data && data.data.order) {
                        refundOrders[idx] = withSearchKey(data.data.order);
                    }
                    if (data.data && typeof data.data.pending_count !== 'undefined') {
                        emitRefundUpdated(data.data.pending_count);
                    }

                    setActiveFilterTab('refunded'); // sekali render
                    showDetail(orderNum);
                } else {
                    showToast('Gagal Menyimpan', data?.message || `Periksa kembali format & ukuran file (HTTP ${status}).`, false);
                }
            } catch (err) {
                isSubmitting = false;
                hideModal(els.rfdConfirmModal);
                showToast('Kesalahan Koneksi', 'Gagal menghubungi server saat mengunggah bukti.', false);
            } finally {
                isSubmitting = false;
                if (btnExec) {
                    btnExec.disabled = false;
                    btnExec.innerHTML = oriExecHtml;
                }
                const o = findOrder(activeOrderNum);
                if (els.btnOpenConfirm) {
                    els.btnOpenConfirm.disabled = !(o && o.refund_status === 'pending' && o.has_valid_account);
                }
            }
        }

        // =====================================================================
        // CLIPBOARD (dengan fallback kalau bukan HTTPS)
        // =====================================================================
        async function copyText(text) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (_) { /* lanjut ke fallback */ }

            const ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0;';
            document.body.appendChild(ta);
            ta.select();
            let ok = false;
            try { ok = document.execCommand('copy'); } catch (_) { ok = false; }
            ta.remove();
            return ok;
        }

        async function onCopyRekeningClick() {
            const rek = (els.detAccountNum ? els.detAccountNum.textContent : '').trim();
            if (!rek || rek === '-') return;

            const ok = await copyText(rek);
            if (!ok) {
                showToast('Gagal Menyalin', 'Browser menolak akses clipboard. Salin manual ya.', false);
                return;
            }
            if (els.iconCopyRekening) {
                els.iconCopyRekening.className = 'ph ph-check text-lg font-bold text-emerald-300';
                setTimeout(() => {
                    if (els.iconCopyRekening) els.iconCopyRekening.className = 'ph ph-copy text-lg font-bold';
                }, 1800);
            }
            showToast('Tersalin', `Nomor rekening ${rek} berhasil disalin.`, true);
        }

        // =====================================================================
        // EVENT HANDLERS
        // =====================================================================
        function onListClick(e) {
            const card = e.target.closest('.order-card');
            if (card && card.dataset.id) showDetail(card.dataset.id);
        }

        function onSearchInput(e) {
            const value = e.target.value;
            clearTimeout(searchTimer);
            // Debounce: render ulang list cuma setelah user berhenti ngetik
            searchTimer = setTimeout(() => {
                searchQuery = (value || '').toLowerCase().trim();
                renderList();
            }, 150);
        }

        function onFilterChipClick(e) {
            const btn = e.target.closest('.chip-filter');
            if (btn) setActiveFilterTab(btn.dataset.status || 'pending');
        }

        function onKeyDown(e) {
            if (e.key !== 'Escape') return;
            closeConfirmModal();
            closeZoomModal();
        }

        function bindBackdrop(modal, closeFn) {
            on(modal, 'click', (e) => { if (e.target === modal) closeFn(); });
        }

        // =====================================================================
        // INIT
        // =====================================================================
        function init() {
            cacheElements();
            teleportToBody();

            on(els.refundList, 'click', onListClick);
            on(els.searchRefund, 'input', onSearchInput);
            on(els.filterChips, 'click', onFilterChipClick);
            on(els.btnBackMobile, 'click', () => setMobileView('list'));
            on(els.btnRefreshRefund, 'click', () => fetchRefundOrders({ silent: true }));
            on(els.btnCopyRekening, 'click', onCopyRekeningClick);

            on(els.refundFile, 'change', onFileInputChange);
            on(els.btnClearFile, 'click', resetFileSelection);
            on(els.formRefund, 'submit', onFormSubmit);
            if (els.uploadDropzone) {
                ['dragenter', 'dragover'].forEach((ev) => on(els.uploadDropzone, ev, () => els.uploadDropzone.classList.add('dragover')));
                ['dragleave', 'drop'].forEach((ev) => on(els.uploadDropzone, ev, () => els.uploadDropzone.classList.remove('dragover')));
            }

            on(els.btnCloseConfirmX, 'click', closeConfirmModal);
            on(els.btnCancelConfirm, 'click', closeConfirmModal);
            on(els.btnExecuteRefund, 'click', executeRefundSubmit);
            bindBackdrop(els.rfdConfirmModal, closeConfirmModal);

            on(els.btnZoomProof, 'click', openZoomModal);
            on(els.btnZoomProofText, 'click', openZoomModal);
            on(els.btnCloseZoomX, 'click', closeZoomModal);
            bindBackdrop(els.rfdZoomModal, closeZoomModal);

            on(document, 'keydown', onKeyDown);

            fetchRefundOrders();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }

        // Back/forward cache: halaman balik dari bfcache = data bisa basi & object URL preview sudah dicabut
        window.addEventListener('pageshow', (e) => {
            if (e.persisted && els.refundList) {
                resetFileSelection();
                fetchRefundOrders({ silent: true });
            }
        });
        window.addEventListener('pagehide', () => {
            if (fetchAbort) fetchAbort.abort();
            revokePreviewUrl();
        });
    })();
</script>
@endpush
