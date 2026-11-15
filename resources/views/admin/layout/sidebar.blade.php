@php
    $initialPendingRefundCount = \App\Models\Order::where('status', 'dibatalkan')
        ->where(function ($q) {
            $q->where('refund_status', 'pending')
              ->orWhere(function ($sq) {
                  $sq->whereNotNull('refund_account_number')
                     ->where('refund_account_number', '!=', '')
                     ->where('refund_status', 'none');
              });
        })
        ->count();
@endphp

<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-[280px] bg-admin-sideBg text-white border-r border-white/20 transform -translate-x-full lg:translate-x-0 lg:static lg:flex lg:flex-col transition-transform duration-300 ease-in-out shadow-[4px_0_24px_rgba(147,104,183,0.15)] overflow-hidden">

    <!-- Branding Header -->
    <div class="flex items-center justify-between h-[88px] px-8 border-b border-white/20 shrink-0 bg-black/5 relative z-10">
        <a href="/admin/dashboard_finansial" class="flex items-center gap-4 interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white rounded-2xl group">
            <div class="flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl shadow-[inset_0_-2px_4px_rgba(0,0,0,0.1),inset_0_2px_4px_rgba(255,255,255,0.3)] border border-white/30 group-hover:bg-white/30 transition-colors">
                <i class="ph ph-oven text-3xl text-white"></i>
            </div>
            <span class="font-black text-xl tracking-tight text-white drop-shadow-sm">Panel Admin</span>
        </a>
        <button id="btn-close-sidebar" type="button" aria-label="Tutup Sidebar" class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-white/80 hover:bg-white/20 hover:text-white interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
            <i class="ph ph-x text-2xl"></i>
        </button>
    </div>

    <!-- Navigation Area -->
    <nav class="flex-1 overflow-y-auto hide-scrollbar py-6 px-4 space-y-4">

        <!-- CORE MENU: DASHBOARD FINANSIAL -->
        <div>
            <a href="/admin/dashboard_finansial" class="flex items-center gap-4 px-4 min-h-[54px] rounded-2xl font-black text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/dashboard_finansial*') || request()->is('admin/ringkasan_finansial*') || request()->is('admin/riwayat_penjualan*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3),0_8px_20px_rgba(0,0,0,0.15)] border border-white/30' : 'bg-black/10 text-white/85 hover:bg-white/15 hover:text-white border border-white/10' }}">
                <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                    <i class="ph ph-chart-line-up text-xl text-white"></i>
                </div>
                <span>Dashboard Finansial</span>
            </a>
        </div>

        <!-- MODULE: OPERASIONAL -->
        <div class="menu-group">
            <button type="button" class="btn-toggle-menu w-full min-h-[48px] flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white text-white/90 hover:bg-white/10" aria-expanded="true">
                <span class="flex items-center gap-2">
                    <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Operasional</span>
                    <span id="sidebar-operasional-dot" class="{{ $initialPendingRefundCount > 0 ? 'inline-flex' : 'hidden' }} w-2 h-2 rounded-full bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.9)]"></span>
                </span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret rotate-180"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-[500px]">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/pantauan_dapur" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/pantauan_dapur') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-cooking-pot text-xl {{ request()->is('admin/pantauan_dapur') ? 'text-white' : '' }}"></i>
                            <span>Pantauan Dapur</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/daftar_transaksi" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/daftar_transaksi') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-clipboard-text text-xl {{ request()->is('admin/daftar_transaksi') ? 'text-white' : '' }}"></i>
                            <span>Daftar Transaksi</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/kelola_pengiriman" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/kelola_pengiriman') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-package text-xl {{ request()->is('admin/kelola_pengiriman') ? 'text-white' : '' }}"></i>
                            <span>Kelola Pengiriman</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/refund_pembatalan" class="flex items-center justify-between gap-2 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/refund_pembatalan*') || request()->is('admin/pembatalan_refund*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <span class="flex items-center gap-4 min-w-0">
                                <i class="ph ph-arrow-u-up-left text-xl shrink-0 {{ request()->is('admin/refund_pembatalan*') ? 'text-white' : '' }}"></i>
                                <span class="truncate">Batal & Refund</span>
                            </span>
                            <span id="sidebar-refund-badge"
                                  title="{{ $initialPendingRefundCount }} pesanan menunggu pengembalian dana"
                                  class="{{ $initialPendingRefundCount > 0 ? 'inline-flex' : 'hidden' }} items-center justify-center min-w-[24px] h-6 px-2 rounded-full text-[11px] font-black bg-amber-400 text-slate-900 shadow-[inset_0_-2px_3px_rgba(0,0,0,0.2),inset_0_2px_3px_rgba(255,255,255,0.6),0_2px_6px_rgba(0,0,0,0.2)] border border-white/50 shrink-0">
                                {{ $initialPendingRefundCount }}
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: PRODUK & RESEP -->
        <div class="menu-group">
            <button type="button" class="btn-toggle-menu w-full min-h-[48px] flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white text-white/90 hover:bg-white/10" aria-expanded="false">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Produk & Resep</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-0">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/katalog_varian" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/katalog_varian*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-package text-xl {{ request()->is('admin/katalog_varian*') ? 'text-white' : '' }}"></i>
                            <span>Produk & Varian</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/resep_digital" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/resep_digital*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-book-bookmark text-xl {{ request()->is('admin/resep_digital*') ? 'text-white' : '' }}"></i>
                            <span>Resep Digital</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: SISTEM & TOKO -->
        <div class="menu-group">
            <button type="button" class="btn-toggle-menu w-full min-h-[48px] flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white text-white/90 hover:bg-white/10" aria-expanded="false">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Sistem & Toko</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-0">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/profil_perusahaan" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/profil_perusahaan') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-storefront text-xl {{ request()->is('admin/profil_perusahaan') ? 'text-white' : '' }}"></i>
                            <span>Profil Perusahaan</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/operasional_libur" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/operasional_libur') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-calendar-check text-xl {{ request()->is('admin/operasional_libur') ? 'text-white' : '' }}"></i>
                            <span>Operasional & Libur</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: WHATSAPP BOT -->
        <div class="menu-group">
            <button type="button" class="btn-toggle-menu w-full min-h-[48px] flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white text-white/90 hover:bg-white/10" aria-expanded="false">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">WhatsApp Bot</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-0">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/template_pesan" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/template_pesan') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-chat-centered-text text-xl {{ request()->is('admin/template_pesan') ? 'text-white' : '' }}"></i>
                            <span>Template Pesan</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: KELOLA AKUN -->
        <div class="menu-group pb-4">
            <button type="button" class="btn-toggle-menu w-full min-h-[48px] flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white text-white/90 hover:bg-white/10" aria-expanded="false">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Kelola Akun</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-0">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/kelola-akses" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/kelola-akses*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-shield-check text-xl {{ request()->is('admin/kelola-akses*') ? 'text-white' : '' }}"></i>
                            <span>Kelola Akses</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/pelanggan" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white {{ request()->is('admin/pelanggan*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-users text-xl {{ request()->is('admin/pelanggan*') ? 'text-white' : '' }}"></i>
                            <span>Daftar Pelanggan</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </nav>
</aside>

<script>
    (() => {
        const boundListeners = [];
        let badgeFetchController = null;

        function updateSidebarRefundBadge(count) {
            const parsedCount = Math.max(0, parseInt(count, 10) || 0);
            const badge = document.getElementById('sidebar-refund-badge');
            const dot = document.getElementById('sidebar-operasional-dot');

            if (badge) {
                badge.textContent = String(parsedCount);
                badge.title = `${parsedCount} pesanan menunggu pengembalian dana`;
                if (parsedCount > 0) {
                    badge.classList.remove('hidden');
                    badge.classList.add('inline-flex');
                } else {
                    badge.classList.remove('inline-flex');
                    badge.classList.add('hidden');
                }
            }

            if (dot) {
                if (parsedCount > 0) {
                    dot.classList.remove('hidden');
                    dot.classList.add('inline-flex');
                } else {
                    dot.classList.remove('inline-flex');
                    dot.classList.add('hidden');
                }
            }
        }

        function handleRefundUpdatedEvent(e) {
            if (e && e.detail && typeof e.detail.pending_count !== 'undefined') {
                updateSidebarRefundBadge(e.detail.pending_count);
            }
        }

        async function syncRefundCountFromApi() {
            if (badgeFetchController) {
                badgeFetchController.abort();
            }
            badgeFetchController = new AbortController();

            try {
                const res = await fetch('/api/admin/batal-refund/count', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: badgeFetchController.signal
                });
                if (!res.ok) return;
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    updateSidebarRefundBadge(json.data.pending_count);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    // Silent fail untuk background badge check
                }
            }
        }

        function initSidebar() {
            const groups = document.querySelectorAll('.menu-group');

            groups.forEach(group => {
                const btn = group.querySelector('.btn-toggle-menu');
                const container = group.querySelector('.submenu-container');
                const caret = group.querySelector('.icon-caret');
                if (!btn || !container || !caret) return;

                const hasActiveLink = container.querySelector('a[class*="bg-admin-sideActive"]');

                if (hasActiveLink) {
                    btn.setAttribute('aria-expanded', 'true');
                    container.style.maxHeight = container.scrollHeight + 'px';
                    caret.classList.add('rotate-180');
                } else {
                    btn.setAttribute('aria-expanded', 'false');
                    container.style.maxHeight = '0px';
                    caret.classList.remove('rotate-180');
                }

                const onToggleClick = () => {
                    const isExpanded = btn.getAttribute('aria-expanded') === 'true';

                    groups.forEach(otherGroup => {
                        if (otherGroup !== group) {
                            const otherBtn = otherGroup.querySelector('.btn-toggle-menu');
                            const otherContainer = otherGroup.querySelector('.submenu-container');
                            const otherCaret = otherGroup.querySelector('.icon-caret');
                            if (otherBtn && otherContainer && otherCaret) {
                                otherBtn.setAttribute('aria-expanded', 'false');
                                otherContainer.style.maxHeight = '0px';
                                otherCaret.classList.remove('rotate-180');
                            }
                        }
                    });

                    if (!isExpanded) {
                        btn.setAttribute('aria-expanded', 'true');
                        container.style.maxHeight = container.scrollHeight + 'px';
                        caret.classList.add('rotate-180');
                    } else {
                        btn.setAttribute('aria-expanded', 'false');
                        container.style.maxHeight = '0px';
                        caret.classList.remove('rotate-180');
                    }
                };

                btn.addEventListener('click', onToggleClick);
                boundListeners.push({ el: btn, type: 'click', fn: onToggleClick });
            });

            window.addEventListener('refund:updated', handleRefundUpdatedEvent);
            boundListeners.push({ el: window, type: 'refund:updated', fn: handleRefundUpdatedEvent });

            syncRefundCountFromApi();
        }

        function cleanupSidebar() {
            if (badgeFetchController) {
                badgeFetchController.abort();
                badgeFetchController = null;
            }
            while (boundListeners.length > 0) {
                const item = boundListeners.pop();
                item.el.removeEventListener(item.type, item.fn);
            }
        }

        document.addEventListener('DOMContentLoaded', initSidebar, { once: true });
        window.addEventListener('pagehide', cleanupSidebar, { once: true });
    })();
</script>