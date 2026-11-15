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
        --clay-shadow-interactive:
            inset 0 -3px 4px rgba(0, 0, 0, 0.12),
            inset 0 3px 4px rgba(255, 255, 255, 0.35),
            0 6px 14px rgba(147, 104, 183, 0.22);
        --clay-shadow-pressed:
            inset 0 3px 6px rgba(147, 104, 183, 0.18),
            inset 0 -2px 4px rgba(255, 255, 255, 0.85);

        /* Fluid Typography Tokens */
        --fs-title: clamp(1.125rem, 1.2vw + 0.85rem, 1.5rem);
        --fs-amount: clamp(1.25rem, 1.5vw + 0.9rem, 1.75rem);
        --fs-account: clamp(1.15rem, 1.4vw + 0.8rem, 1.5rem);
        --fs-body: clamp(0.8125rem, 0.3vw + 0.75rem, 0.9375rem);
    }

    .clay-panel {
        background-color: var(--clay-surface);
        box-shadow: var(--clay-shadow-card);
        border: 2px solid #FFFFFF;
    }

    .clay-inset {
        background-color: var(--clay-bg-base);
        box-shadow: var(--clay-shadow-pressed);
        border: 1px solid rgba(255, 255, 255, 0.8);
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
        background-color: var(--clay-surface-Alt);
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

    .upload-box {
        border: 2px dashed #CBD5E1;
        transition: transform 0.18s ease, opacity 0.18s ease;
    }
    .upload-box:hover, .upload-box.dragover {
        border-color: var(--clay-primary);
        background-color: var(--clay-surface-Alt);
    }

    .fluid-title { font-size: var(--fs-title); }
    .fluid-amount { font-size: var(--fs-amount); }
    .fluid-account { font-size: var(--fs-account); }
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
<div id="refund-toast" role="status" aria-live="polite" class="fixed top-6 right-6 z-[210] hidden items-center gap-3 px-5 py-3.5 rounded-2xl bg-white border-2 border-purple-100 shadow-2xl transition-all duration-200 opacity-0 translate-y-[-10px] max-w-sm">
    <div id="refund-toast-icon" class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
        <i class="ph ph-check-circle text-xl"></i>
    </div>
    <div class="min-w-0 flex-1">
        <p id="refund-toast-title" class="text-xs font-black text-gray-900">Informasi</p>
        <p id="refund-toast-msg" class="text-xs font-semibold text-gray-600 truncate">Berhasil diproses.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 lg:h-[calc(100dvh-140px)] min-h-[640px] max-w-full overflow-x-hidden">

    <!-- LEFT PANEL: DAFTAR ANTREAN & RIWAYAT REFUND -->
    <section id="left-panel" aria-label="Daftar Antrean Refund" class="lg:col-span-5 xl:col-span-4 clay-panel rounded-[2rem] flex flex-col overflow-hidden h-[calc(100dvh-150px)] lg:h-full">

        <!-- Search & Filter Area -->
        <div class="p-4 sm:p-5 border-b border-purple-100/80 bg-[#FAF8FC] shrink-0 space-y-3.5">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="font-black text-admin-textMain text-sm uppercase tracking-wider">Antrean Refund</h2>
                    <p class="text-[11px] font-semibold text-admin-textMuted">Pesanan batal yang memiliki dana masuk</p>
                </div>
                <button type="button" id="btn-refresh-refund" title="Muat ulang data" aria-label="Muat ulang daftar refund" class="clay-btn w-11 h-11 rounded-xl bg-white border border-purple-100 text-admin-sideBg flex items-center justify-center shadow-sm">
                    <i id="icon-refresh-refund" class="ph ph-arrows-clockwise text-lg font-bold"></i>
                </button>
            </div>

            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-textMuted pointer-events-none"></i>
                <input type="search" id="search-refund" autocomplete="off" placeholder="Cari No. Pesanan, Nama, atau Bank..." class="w-full min-h-[44px] pl-11 pr-4 py-2.5 clay-inset rounded-xl text-xs sm:text-sm font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg">
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
    <section id="right-panel" aria-label="Detail Resolusi Refund" class="hidden lg:flex lg:col-span-7 xl:col-span-8 clay-panel rounded-[2rem] flex-col relative overflow-hidden h-[calc(100dvh-150px)] lg:h-full">

        <!-- Empty State (Saat belum ada item yang dipilih) -->
        <div id="detail-empty" class="absolute inset-0 bg-white z-20 flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 clay-inset rounded-3xl flex items-center justify-center mb-5">
                <i class="ph ph-bank text-4xl text-admin-sideBg"></i>
            </div>
            <h3 class="fluid-title font-black text-admin-textMain mb-2">Pusat Resolusi Refund</h3>
            <p class="fluid-body font-medium text-admin-textMuted max-w-md leading-relaxed">
                Pilih salah satu pesanan batal di panel kiri untuk memeriksa rincian rekening tujuan pelanggan dan mengunggah foto bukti pengembalian dana.
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
                            <h2 class="fluid-title font-black text-rose-600 tracking-tight truncate" id="det-order-id">#RK-0000</h2>
                            <div id="det-status-badge"></div>
                            <span id="det-source-badge" class="px-2.5 py-0.5 text-[10px] font-black rounded-lg bg-purple-50 text-admin-sideBg border border-purple-200 uppercase">WEB</span>
                        </div>
                        <p class="text-xs font-bold text-admin-textMuted flex items-center gap-1.5 truncate">
                            <i class="ph ph-clock text-admin-sideBg shrink-0"></i>
                            <span>Dibatalkan: <span id="det-cancelled-at" class="text-admin-textMain">-</span></span>
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
                            <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                                <i class="ph ph-user-circle text-base text-admin-sideBg"></i>
                                <span>Data Pelanggan & Tagihan</span>
                            </h4>
                            <p class="font-black text-admin-textMain text-sm sm:text-base" id="det-customer">-</p>
                            <p class="text-xs font-bold text-admin-textMuted mt-0.5" id="det-phone">-</p>
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
                    <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                        <i class="ph ph-shopping-bag text-base text-admin-sideBg"></i>
                        <span>Ringkasan Item Pesanan</span>
                    </h4>
                    <div id="det-items-list" class="divide-y divide-gray-100 text-xs sm:text-sm"></div>
                </div>

                <!-- Form Upload / Bukti Refund -->
                <div class="clay-panel rounded-2xl overflow-hidden p-5">
                    <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-4 border-b border-gray-100 pb-2 flex items-center gap-1.5">
                        <i class="ph ph-receipt text-base text-admin-sideBg"></i>
                        <span>Bukti Transfer Pengembalian Dana</span>
                    </h4>

                    <!-- Area Upload (Jika status = pending) -->
                    <div id="area-upload" class="hidden">
                        <form id="form-refund" class="space-y-4" novalidate>
                            <div id="upload-dropzone" class="upload-box relative w-full min-h-[180px] rounded-2xl flex flex-col items-center justify-center bg-[#FAF8FC] p-4 text-center cursor-pointer overflow-hidden">
                                <input type="file" id="refund_file" name="refund_proof" accept="image/jpeg,image/png,image/jpg,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required aria-label="Unggah foto bukti transfer">
                                <div id="upload-placeholder" class="flex flex-col items-center pointer-events-none">
                                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-admin-sideBg flex items-center justify-center mb-2.5 shadow-inner">
                                        <i class="ph ph-upload-simple text-2xl font-bold"></i>
                                    </div>
                                    <p class="text-xs sm:text-sm font-black text-admin-textMain mb-1">Klik atau seret foto bukti transfer ke sini</p>
                                    <p class="text-[11px] font-semibold text-admin-textMuted">Format JPG, PNG, WEBP (Maksimal 2MB)</p>
                                </div>
                                <img id="preview-image" class="absolute inset-0 w-full h-full object-contain bg-slate-900/90 p-2 rounded-2xl hidden z-10 pointer-events-none" src="" alt="Preview Bukti Refund">
                            </div>

                            <div id="selected-file-bar" class="hidden items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-purple-50 border border-purple-200 text-xs">
                                <span id="selected-file-name" class="font-bold text-admin-sideBg truncate">-</span>
                                <button type="button" id="btn-clear-file" class="clay-btn px-3 py-1.5 rounded-lg bg-white text-rose-600 font-black text-[11px] border border-rose-200 shrink-0">
                                    Ganti Foto
                                </button>
                            </div>

                            <!-- Sticky / Ergonomic Thumb-Zone Action -->
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
                                <img id="img-proof" src="" alt="Bukti Refund Tersimpan" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-900/45 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-white">
                                    <i class="ph ph-magnifying-glass-plus text-3xl mb-1"></i>
                                    <span class="text-[11px] font-black">Perbesar Bukti</span>
                                </div>
                            </button>
                            <div class="flex-1 bg-emerald-50 p-5 rounded-2xl border border-emerald-200 w-full space-y-2">
                                <div class="flex items-center gap-2 text-emerald-800 font-black text-sm sm:text-base">
                                    <i class="ph ph-SealCheck text-2xl text-emerald-600"></i>
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

<!-- MODAL KONFIRMASI SELESAIKAN REFUND (Escape Hatches: X, Backdrop, ESC) -->
<div id="confirmRefundModal" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title" class="fixed inset-0 z-[190] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-200">
    <div id="confirmRefundDialog" class="clay-panel rounded-[2rem] max-w-md w-full p-6 sm:p-7 relative transform scale-95 transition-transform duration-200">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="ph ph-shield-check text-2xl"></i>
                </div>
                <h3 id="confirm-modal-title" class="font-black text-base text-admin-textMain">Konfirmasi Penyelesaian Refund</h3>
            </div>
            <button type="button" id="btn-close-confirm-x" aria-label="Tutup modal konfirmasi" class="clay-btn w-11 h-11 rounded-xl bg-gray-100 text-gray-500 hover:text-rose-600 flex items-center justify-center">
                <i class="ph ph-x text-xl font-bold"></i>
            </button>
        </div>

        <p class="text-xs sm:text-sm font-semibold text-admin-textMuted leading-relaxed mb-4">
            Pastikan dana sebesar <strong id="confirm-modal-amount" class="text-rose-600 font-black">Rp 0</strong> telah ditransfer ke rekening <strong id="confirm-modal-bank" class="text-admin-textMain font-black">-</strong> untuk pesanan <strong id="confirm-modal-order" class="text-admin-sideBg font-black">#RK-000</strong>.
        </p>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 pt-2">
            <button type="button" id="btn-cancel-confirm" class="clay-btn px-5 py-3 rounded-xl bg-gray-100 text-gray-700 font-black text-xs sm:text-sm">
                Periksa Kembali
            </button>
            <button type="button" id="btn-execute-refund" class="clay-btn px-6 py-3 rounded-xl bg-emerald-600 text-white font-black text-xs sm:text-sm shadow-md flex items-center justify-center gap-2">
                <i class="ph ph-paper-plane-tilt text-lg"></i>
                <span>Ya, Simpan & Kirim Bukti</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW GAMBAR BUKTI TRANSFER (Escape Hatches: X, Backdrop, ESC) -->
<div id="proofZoomModal" role="dialog" aria-modal="true" aria-label="Preview Bukti Transfer" class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-200">
    <div id="proofZoomDialog" class="bg-white rounded-[2rem] p-4 max-w-2xl w-full max-h-[90dvh] flex flex-col shadow-2xl transform scale-95 transition-transform duration-200">
        <div class="flex items-center justify-between pb-3 mb-2 border-b border-gray-100">
            <span id="zoom-modal-title" class="text-xs font-black uppercase tracking-wider text-admin-textMain">Bukti Transfer Pengembalian Dana</span>
            <button type="button" id="btn-close-zoom-x" aria-label="Tutup preview gambar" class="clay-btn w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i class="ph ph-x text-xl font-bold"></i>
            </button>
        </div>
        <div class="flex-1 overflow-auto flex items-center justify-center bg-slate-950/90 rounded-2xl p-2 min-h-[260px]">
            <img id="zoom-modal-image" src="" alt="Bukti Refund Ukuran Penuh" class="max-h-[72dvh] w-auto object-contain rounded-xl">
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
        let refundOrders = [];
        let activeFilter = 'pending';
        let searchQuery = '';
        let activeOrderNum = null;

        let fetchAbortController = null;
        let submitAbortController = null;
        let currentObjectUrl = null;
        const activeTimers = new Set();
        const boundListeners = [];

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

        function revokePreviewUrl() {
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }
        }

        // =========================================================================
        // CONTEXTUAL TOAST ENGINE
        // =========================================================================
        function showToast(title, message, isSuccess = true) {
            const toast = document.getElementById('refund-toast');
            const iconBox = document.getElementById('refund-toast-icon');
            const titleEl = document.getElementById('refund-toast-title');
            const msgEl = document.getElementById('refund-toast-msg');
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
        // FETCH DATA DARI API CONTROLLER (DENGAN ABORTCONTROLLER)
        // =========================================================================
        async function fetchRefundOrders(preserveSelection = true) {
            if (fetchAbortController) {
                fetchAbortController.abort();
            }
            fetchAbortController = new AbortController();

            const skeleton = document.getElementById('list-skeleton');
            const listEl = document.getElementById('refund-list');
            const refreshIcon = document.getElementById('icon-refresh-refund');

            if (refreshIcon) refreshIcon.classList.add('animate-spin');
            if (!preserveSelection && skeleton && listEl) {
                skeleton.classList.remove('hidden');
                listEl.classList.add('hidden');
            }

            try {
                const response = await fetch('/api/admin/batal-refund', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: fetchAbortController.signal
                });

                const res = await response.json();
                if (res.status === 'success' && res.data) {
                    refundOrders = Array.isArray(res.data.orders) ? res.data.orders : [];
                    const pendingCount = res.data.summary?.pending_count ?? 0;

                    // Sinkronkan badge di Sidebar Admin secara real-time
                    window.dispatchEvent(new CustomEvent('refund:updated', {
                        detail: { pending_count: pendingCount }
                    }));

                    renderList();

                    if (preserveSelection && activeOrderNum) {
                        const stillExists = refundOrders.some(o => o.order_number === activeOrderNum);
                        if (stillExists) {
                            showDetail(activeOrderNum);
                        }
                    }
                } else {
                    showToast('Gagal Memuat', res.message || 'Tidak dapat memuat data refund.', false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showToast('Gangguan Jaringan', 'Gagal terhubung ke server.', false);
                }
            } finally {
                if (skeleton) skeleton.classList.add('hidden');
                if (listEl) listEl.classList.remove('hidden');
                if (refreshIcon) refreshIcon.classList.remove('animate-spin');
            }
        }

        // =========================================================================
        // RENDER LIST & FILTER
        // =========================================================================
        function renderList() {
            const listEl = document.getElementById('refund-list');
            const countPendingEl = document.getElementById('count-pending');
            const countRefundedEl = document.getElementById('count-refunded');
            if (!listEl) return;

            let counts = { pending: 0, refunded: 0 };

            const filtered = refundOrders.filter(o => {
                const haystack = `${o.order_number} ${o.recipient_name} ${o.bank_name} ${o.account_number}`.toLowerCase();
                const matchSearch = haystack.includes(searchQuery);
                if (matchSearch && (o.refund_status === 'pending' || o.refund_status === 'refunded')) {
                    counts[o.refund_status]++;
                }
                return matchSearch && o.refund_status === activeFilter;
            });

            if (countPendingEl) countPendingEl.textContent = String(counts.pending);
            if (countRefundedEl) countRefundedEl.textContent = String(counts.refunded);

            if (filtered.length === 0) {
                const emptyTitle = activeFilter === 'pending'
                    ? 'Tidak Ada Antrean Refund'
                    : 'Belum Ada Riwayat Refund Selesai';
                const emptyDesc = activeFilter === 'pending'
                    ? 'Seluruh pengembalian dana pelanggan sudah diselesaikan.'
                    : 'Pesanan yang telah direfund akan tercatat di tab ini.';

                listEl.innerHTML = `
                    <div class="py-14 px-4 text-center flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-2xl clay-inset flex items-center justify-center text-admin-sideBg mb-3">
                            <i class="ph ph-check-square-offset text-3xl"></i>
                        </div>
                        <p class="font-black text-admin-textMain text-sm mb-1">${emptyTitle}</p>
                        <p class="text-xs font-medium text-admin-textMuted max-w-[240px]">${emptyDesc}</p>
                    </div>
                `;
                return;
            }

            listEl.innerHTML = filtered.map(o => {
                const isActive = activeOrderNum === o.order_number ? 'active' : '';
                const badge = o.refund_status === 'pending'
                    ? `<span class="px-2.5 py-0.5 text-[10px] rounded-lg font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">Menunggu</span>`
                    : `<span class="px-2.5 py-0.5 text-[10px] rounded-lg font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">Selesai</span>`;

                return `
                    <button type="button" class="order-card w-full text-left p-4 rounded-2xl focus:outline-none ${isActive}" data-id="${o.order_number}">
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <span class="font-black text-admin-textMain text-sm truncate">#${o.order_number}</span>
                            ${badge}
                        </div>
                        <div class="flex justify-between items-end gap-2">
                            <div class="min-w-0">
                                <p class="font-bold text-admin-textMain text-xs truncate flex items-center gap-1.5">
                                    <i class="ph ph-user text-admin-sideBg"></i>
                                    <span class="truncate">${o.recipient_name}</span>
                                </p>
                                <p class="text-[11px] font-semibold text-admin-textMuted truncate mt-0.5 flex items-center gap-1">
                                    <i class="ph ph-bank"></i>
                                    <span class="truncate">${o.bank_name} • ${o.payment_type_label}</span>
                                </p>
                            </div>
                            <span class="font-black text-rose-600 text-xs sm:text-sm shrink-0">${formatRupiah(o.refund_amount)}</span>
                        </div>
                    </button>
                `;
            }).join('');
        }

        // =========================================================================
        // TAMPILKAN DETAIL PESANAN DI PANEL KANAN
        // =========================================================================
        function showDetail(orderNum) {
            activeOrderNum = orderNum;
            renderList();

            const order = refundOrders.find(o => o.order_number === orderNum);
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

            document.getElementById('det-order-id').textContent = '#' + order.order_number;
            document.getElementById('det-cancelled-at').textContent = order.cancelled_at;
            document.getElementById('det-reason').textContent = order.cancellation_reason;
            document.getElementById('det-customer').textContent = order.recipient_name;
            document.getElementById('det-phone').textContent = order.recipient_whatsapp;
            document.getElementById('det-source-badge').textContent = (order.order_source || 'web').toUpperCase();

            const statusBadgeEl = document.getElementById('det-status-badge');
            if (statusBadgeEl) {
                statusBadgeEl.innerHTML = order.refund_status === 'pending'
                    ? `<span class="px-2.5 py-1 text-[10px] rounded-lg font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1"><i class="ph ph-clock-countdown"></i> Belum Refund</span>`
                    : `<span class="px-2.5 py-1 text-[10px] rounded-lg font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1"><i class="ph ph-check-circle"></i> Selesai Refund</span>`;
            }

            const waLink = document.getElementById('det-wa-link');
            if (waLink) {
                if (order.wa_chat_url) {
                    waLink.href = order.wa_chat_url;
                    waLink.classList.remove('hidden');
                } else {
                    waLink.classList.add('hidden');
                }
            }

            document.getElementById('det-refund-amount').textContent = formatRupiah(order.refund_amount);
            document.getElementById('det-paid-badge').textContent = order.payment_type_label;
            document.getElementById('det-paid-info').textContent = `Dari total pesanan ${formatRupiah(order.total_amount)} (${order.payment_method} - ${order.payment_type_label})`;

            document.getElementById('det-bank-name').textContent = order.bank_name || '-';
            document.getElementById('det-account-num').textContent = order.account_number || '-';
            document.getElementById('det-account-name').textContent = 'A.N ' + (order.account_holder || order.recipient_name);

            // Render daftar item pesanan
            const itemsContainer = document.getElementById('det-items-list');
            if (itemsContainer) {
                if (Array.isArray(order.items) && order.items.length > 0) {
                    itemsContainer.innerHTML = order.items.map(it => `
                        <div class="py-2.5 flex justify-between items-center gap-2">
                            <span class="font-bold text-admin-textMain">${it.name} <span class="text-admin-sideBg font-black">x${it.qty}</span></span>
                            <span class="font-extrabold text-admin-textMuted">${formatRupiah(it.subtotal)}</span>
                        </div>
                    `).join('');
                } else {
                    itemsContainer.innerHTML = '<p class="text-xs text-admin-textMuted py-2">Tidak ada rincian item.</p>';
                }
            }

            // Toggle Area Upload vs Area Bukti
            const areaUpload = document.getElementById('area-upload');
            const areaProof = document.getElementById('area-proof');

            resetFileSelection();

            if (order.refund_status === 'pending') {
                if (areaUpload) areaUpload.classList.remove('hidden');
                if (areaProof) areaProof.classList.add('hidden');
            } else {
                if (areaUpload) areaUpload.classList.add('hidden');
                if (areaProof) areaProof.classList.remove('hidden');
                const imgProof = document.getElementById('img-proof');
                if (imgProof && order.refund_proof_url) {
                    imgProof.src = order.refund_proof_url;
                }
            }
        }

        function resetFileSelection() {
            revokePreviewUrl();
            const form = document.getElementById('form-refund');
            const previewImg = document.getElementById('preview-image');
            const fileBar = document.getElementById('selected-file-bar');
            if (form) form.reset();
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
            }
            if (fileBar) {
                fileBar.classList.remove('flex');
                fileBar.classList.add('hidden');
            }
        }

        // =========================================================================
        // MODAL HANDLERS (DENGAN ESCAPE HATCHES: X, BACKDROP, ESC)
        // =========================================================================
        function openConfirmModal() {
            const order = refundOrders.find(o => o.order_number === activeOrderNum);
            if (!order) return;

            document.getElementById('confirm-modal-amount').textContent = formatRupiah(order.refund_amount);
            document.getElementById('confirm-modal-bank').textContent = `${order.bank_name} (${order.account_number})`;
            document.getElementById('confirm-modal-order').textContent = '#' + order.order_number;

            const modal = document.getElementById('confirmRefundModal');
            const dialog = document.getElementById('confirmRefundDialog');
            if (!modal || !dialog) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            registerTimer(() => {
                modal.classList.remove('opacity-0');
                dialog.classList.remove('scale-95');
            }, 15);
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmRefundModal');
            const dialog = document.getElementById('confirmRefundDialog');
            if (!modal || !dialog || modal.classList.contains('hidden')) return;

            modal.classList.add('opacity-0');
            dialog.classList.add('scale-95');
            registerTimer(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }

        function openZoomModal() {
            const order = refundOrders.find(o => o.order_number === activeOrderNum);
            if (!order || !order.refund_proof_url) return;

            const zoomImg = document.getElementById('zoom-modal-image');
            const zoomTitle = document.getElementById('zoom-modal-title');
            const modal = document.getElementById('proofZoomModal');
            const dialog = document.getElementById('proofZoomDialog');
            if (!modal || !dialog || !zoomImg) return;

            zoomImg.src = order.refund_proof_url;
            if (zoomTitle) zoomTitle.textContent = `Bukti Refund #${order.order_number}`;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            registerTimer(() => {
                modal.classList.remove('opacity-0');
                dialog.classList.remove('scale-95');
            }, 15);
        }

        function closeZoomModal() {
            const modal = document.getElementById('proofZoomModal');
            const dialog = document.getElementById('proofZoomDialog');
            if (!modal || !dialog || modal.classList.contains('hidden')) return;

            modal.classList.add('opacity-0');
            dialog.classList.add('scale-95');
            registerTimer(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }

        // =========================================================================
        // SUBMIT BUKTI REFUND VIA FETCH API + FORMDATA
        // =========================================================================
        async function executeRefundSubmit() {
            const fileInput = document.getElementById('refund_file');
            if (!fileInput || !fileInput.files || !fileInput.files[0] || !activeOrderNum) {
                closeConfirmModal();
                showToast('File Belum Dipilih', 'Silakan pilih foto bukti transfer terlebih dahulu.', false);
                return;
            }

            if (submitAbortController) {
                submitAbortController.abort();
            }
            submitAbortController = new AbortController();

            const btnExec = document.getElementById('btn-execute-refund');
            const btnOpen = document.getElementById('btn-open-confirm');
            const oriExecHtml = btnExec ? btnExec.innerHTML : '';

            if (btnExec) {
                btnExec.disabled = true;
                btnExec.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-lg"></i><span>Mengunggah...</span>';
            }
            if (btnOpen) btnOpen.disabled = true;

            const formData = new FormData();
            formData.append('refund_proof', fileInput.files[0]);

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const headers = { 'Accept': 'application/json' };
            if (csrfMeta) {
                headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
            }

            try {
                const response = await fetch(`/api/admin/batal-refund/${encodeURIComponent(activeOrderNum)}/proses`, {
                    method: 'POST',
                    headers,
                    body: formData,
                    signal: submitAbortController.signal
                });

                const res = await response.json();

                if (response.ok && res.status === 'success') {
                    closeConfirmModal();
                    showToast('Refund Selesai', res.message, true);

                    const idx = refundOrders.findIndex(o => o.order_number === activeOrderNum);
                    if (idx !== -1 && res.data?.order) {
                        refundOrders[idx] = res.data.order;
                    }

                    // Update badge sidebar secara instan
                    if (typeof res.data?.pending_count !== 'undefined') {
                        window.dispatchEvent(new CustomEvent('refund:updated', {
                            detail: { pending_count: res.data.pending_count }
                        }));
                    }

                    // Pindah ke tab Selesai agar Admin langsung melihat bukti yang tersimpan
                    setActiveFilterTab('refunded');
                    showDetail(activeOrderNum);
                } else {
                    closeConfirmModal();
                    showToast('Gagal Menyimpan', res.message || 'Periksa kembali format dan ukuran file gambar.', false);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    closeConfirmModal();
                    showToast('Kesalahan Koneksi', 'Gagal menghubungi server saat mengunggah bukti.', false);
                }
            } finally {
                if (btnExec) {
                    btnExec.disabled = false;
                    btnExec.innerHTML = oriExecHtml;
                }
                if (btnOpen) btnOpen.disabled = false;
            }
        }

        function setActiveFilterTab(status) {
            activeFilter = status;
            const chips = document.querySelectorAll('.chip-filter');
            chips.forEach(chip => {
                const chipStatus = chip.getAttribute('data-status');
                const isSelected = chipStatus === status;
                chip.setAttribute('aria-selected', isSelected ? 'true' : 'false');

                chip.className = 'chip-filter clay-btn px-3 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 ';
                if (isSelected && status === 'pending') {
                    chip.className += 'bg-amber-500 text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.35)]';
                } else if (isSelected && status === 'refunded') {
                    chip.className += 'bg-emerald-600 text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.35)]';
                } else if (chipStatus === 'pending') {
                    chip.className += 'bg-white border border-purple-100 text-amber-700';
                } else {
                    chip.className += 'bg-white border border-purple-100 text-emerald-700';
                }
            });
            renderList();
        }

        // =========================================================================
        // NAMED EVENT HANDLERS
        // =========================================================================
        function onListClick(e) {
            const card = e.target.closest('.order-card');
            if (!card) return;
            const orderId = card.getAttribute('data-id');
            if (orderId) showDetail(orderId);
        }

        function onSearchInput(e) {
            searchQuery = (e.target.value || '').toLowerCase().trim();
            renderList();
        }

        function onFilterChipClick(e) {
            const btn = e.target.closest('.chip-filter');
            if (!btn) return;
            setActiveFilterTab(btn.getAttribute('data-status') || 'pending');
        }

        function onMobileBackClick() {
            const leftPanel = document.getElementById('left-panel');
            const rightPanel = document.getElementById('right-panel');
            if (rightPanel && leftPanel) {
                rightPanel.classList.remove('flex');
                rightPanel.classList.add('hidden');
                leftPanel.classList.remove('hidden');
            }
        }

        function onCopyRekeningClick() {
            const rek = (document.getElementById('det-account-num')?.textContent || '').trim();
            if (!rek || rek === '-') return;

            navigator.clipboard.writeText(rek).then(() => {
                const icon = document.getElementById('icon-copy-rekening');
                if (icon) {
                    icon.className = 'ph ph-check text-lg font-bold text-emerald-300';
                    registerTimer(() => {
                        icon.className = 'ph ph-copy text-lg font-bold';
                    }, 1800);
                }
                showToast('Tersalin', `Nomor rekening ${rek} berhasil disalin.`, true);
            });
        }

        function onFileInputChange(e) {
            const file = e.target.files && e.target.files[0];
            revokePreviewUrl();

            const previewImg = document.getElementById('preview-image');
            const fileBar = document.getElementById('selected-file-bar');
            const fileNameEl = document.getElementById('selected-file-name');

            if (!file) {
                if (previewImg) previewImg.classList.add('hidden');
                if (fileBar) fileBar.classList.add('hidden');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                showToast('Ukuran File Terlalu Besar', 'Maksimal ukuran gambar bukti transfer adalah 2MB.', false);
                resetFileSelection();
                return;
            }

            currentObjectUrl = URL.createObjectURL(file);
            if (previewImg) {
                previewImg.src = currentObjectUrl;
                previewImg.classList.remove('hidden');
            }
            if (fileNameEl) fileNameEl.textContent = `${file.name} (${(file.size / 1024).toFixed(0)} KB)`;
            if (fileBar) {
                fileBar.classList.remove('hidden');
                fileBar.classList.add('flex');
            }
        }

        function onFormSubmit(e) {
            e.preventDefault();
            const fileInput = document.getElementById('refund_file');
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                showToast('Bukti Belum Diunggah', 'Silakan pilih foto bukti transfer terlebih dahulu.', false);
                return;
            }
            openConfirmModal();
        }

        function onKeyDown(e) {
            if (e.key === 'Escape') {
                closeConfirmModal();
                closeZoomModal();
            }
        }

        function onConfirmBackdropClick(e) {
            if (e.target.id === 'confirmRefundModal') {
                closeConfirmModal();
            }
        }

        function onZoomBackdropClick(e) {
            if (e.target.id === 'proofZoomModal') {
                closeZoomModal();
            }
        }

        function onRefreshClick() {
            fetchRefundOrders(true);
        }

        // =========================================================================
        // INIT & TEARDOWN (ZERO MEMORY LEAK)
        // =========================================================================
        function initPage() {
            bindEvent(document.getElementById('refund-list'), 'click', onListClick);
            bindEvent(document.getElementById('search-refund'), 'input', onSearchInput);
            bindEvent(document.getElementById('filter-chips'), 'click', onFilterChipClick);
            bindEvent(document.getElementById('btn-back-mobile'), 'click', onMobileBackClick);
            bindEvent(document.getElementById('btn-copy-rekening'), 'click', onCopyRekeningClick);
            bindEvent(document.getElementById('refund_file'), 'change', onFileInputChange);
            bindEvent(document.getElementById('btn-clear-file'), 'click', resetFileSelection);
            bindEvent(document.getElementById('form-refund'), 'submit', onFormSubmit);
            bindEvent(document.getElementById('btn-refresh-refund'), 'click', onRefreshClick);

            // Modal Confirm Listeners
            bindEvent(document.getElementById('btn-close-confirm-x'), 'click', closeConfirmModal);
            bindEvent(document.getElementById('btn-cancel-confirm'), 'click', closeConfirmModal);
            bindEvent(document.getElementById('btn-execute-refund'), 'click', executeRefundSubmit);
            bindEvent(document.getElementById('confirmRefundModal'), 'click', onConfirmBackdropClick);

            // Modal Zoom Listeners
            bindEvent(document.getElementById('btn-zoom-proof'), 'click', openZoomModal);
            bindEvent(document.getElementById('btn-zoom-proof-text'), 'click', openZoomModal);
            bindEvent(document.getElementById('btn-close-zoom-x'), 'click', closeZoomModal);
            bindEvent(document.getElementById('proofZoomModal'), 'click', onZoomBackdropClick);

            // Global ESC key
            bindEvent(document, 'keydown', onKeyDown);

            fetchRefundOrders(false);
        }

        function cleanupPage() {
            if (fetchAbortController) {
                fetchAbortController.abort();
                fetchAbortController = null;
            }
            if (submitAbortController) {
                submitAbortController.abort();
                submitAbortController = null;
            }
            activeTimers.forEach(id => window.clearTimeout(id));
            activeTimers.clear();
            revokePreviewUrl();

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