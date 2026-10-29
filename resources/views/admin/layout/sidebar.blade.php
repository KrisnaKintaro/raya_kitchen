<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-[280px] bg-admin-sideBg text-white border-r border-white/20 transform -translate-x-full lg:translate-x-0 lg:static lg:flex lg:flex-col transition-transform duration-300 ease-in-out shadow-[4px_0_24px_rgba(147,104,183,0.15)] overflow-hidden">

    <!-- Branding Header -->
    <div class="flex items-center justify-between h-[88px] px-8 border-b border-white/20 shrink-0 bg-black/5 relative z-10">
        <a href="/admin/ringkasan_finansial" class="flex items-center gap-4 interactive-element focus-visible:outline-none group">
            <div class="flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl shadow-[inset_0_-2px_4px_rgba(0,0,0,0.1),inset_0_2px_4px_rgba(255,255,255,0.3)] border border-white/30 group-hover:bg-white/30 transition-colors">
                <i class="ph ph-oven text-3xl text-white"></i>
            </div>
            <span class="font-black text-xl tracking-tight text-white drop-shadow-sm">Panel Admin</span>
        </a>
        <button id="btn-close-sidebar" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl text-white/80 hover:bg-white/20 hover:text-white interactive-element focus-visible:outline-none">
            <i class="ph ph-x text-2xl"></i>
        </button>
    </div>

    <!-- Navigation Area -->
    <nav class="flex-1 overflow-y-auto hide-scrollbar py-6 px-4 space-y-4">

        <!-- MODULE: FINANSIAL -->
        <div class="menu-group">
            <button class="btn-toggle-menu w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none text-white/90 hover:bg-white/10" aria-expanded="true">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Finansial</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret rotate-180"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-[500px]">
                <!-- Kapsul Pembungkus Submenu -->
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/ringkasan_finansial" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/ringkasan_finansial') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-chart-line-up text-xl {{ request()->is('admin/ringkasan_finansial') ? 'text-white' : '' }}"></i>
                            Ringkasan Finansial
                        </a>
                    </li>
                    <li>
                        <a href="/admin/riwayat_penjualan" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/riwayat_penjualan') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-receipt text-xl {{ request()->is('admin/riwayat_penjualan') ? 'text-white' : '' }}"></i>
                            Riwayat Penjualan
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: OPERASIONAL -->
        <div class="menu-group">
            <button class="btn-toggle-menu w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none text-white/90 hover:bg-white/10" aria-expanded="true">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Operasional</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret rotate-180"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-[500px]">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/pantauan_dapur" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/pantauan_dapur') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-cooking-pot text-xl {{ request()->is('admin/pantauan_dapur') ? 'text-white' : '' }}"></i>
                            Pantauan Dapur
                        </a>
                    </li>
                    <li>
                        <a href="/admin/daftar_transaksi" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/daftar_transaksi') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-clipboard-text text-xl {{ request()->is('admin/daftar_transaksi') ? 'text-white' : '' }}"></i>
                            Daftar Transaksi
                        </a>
                    </li>
                    <li>
                        <a href="/admin/refund_pembatalan" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/pembatalan_refund') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-arrow-u-up-left text-xl {{ request()->is('admin/pembatalan_refund') ? 'text-white' : '' }}"></i>
                            Batal & Refund
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: PRODUK & RESEP -->
        <div class="menu-group">
            <button class="btn-toggle-menu w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none text-white/90 hover:bg-white/10" aria-expanded="false">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Produk & Resep</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-0">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/katalog_varian" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/katalog_varian*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-package text-xl {{ request()->is('admin/katalog_varian*') ? 'text-white' : '' }}"></i>
                            Produk & Varian
                        </a>
                    </li>
                    <li>
                        <a href="/admin/resep_digital" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/resep_digital*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-book-bookmark text-xl {{ request()->is('admin/resep_digital*') ? 'text-white' : '' }}"></i>
                            Resep Digital
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: SISTEM & TOKO -->
        <div class="menu-group">
            <button class="btn-toggle-menu w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none text-white/90 hover:bg-white/10" aria-expanded="false">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Sistem & Toko</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-0">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/profil_perusahaan" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/profil_perusahaan') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-storefront text-xl {{ request()->is('admin/profil_perusahaan') ? 'text-white' : '' }}"></i>
                            Profil Perusahaan
                        </a>
                    </li>
                    <li>
                        <a href="/admin/operasional_libur" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/operasional_libur') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-calendar-check text-xl {{ request()->is('admin/operasional_libur') ? 'text-white' : '' }}"></i>
                            Operasional & Libur
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: WHATSAPP BOT -->
        <div class="menu-group">
            <button class="btn-toggle-menu w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none text-white/90 hover:bg-white/10" aria-expanded="false">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">WhatsApp Bot</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-0">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/template_pesan" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/template_pesan') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-chat-centered-text text-xl {{ request()->is('admin/template_pesan') ? 'text-white' : '' }}"></i>
                            Template Pesan
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- MODULE: KELOLA AKUN -->
        <div class="menu-group pb-4">
            <button class="btn-toggle-menu w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all interactive-element focus-visible:outline-none text-white/90 hover:bg-white/10" aria-expanded="false">
                <span class="text-xs font-black text-admin-accent uppercase tracking-[0.15em] drop-shadow-sm">Kelola Akun</span>
                <i class="ph ph-caret-down text-sm transition-transform duration-300 icon-caret"></i>
            </button>
            <div class="submenu-container overflow-hidden transition-all duration-300 max-h-0">
                <ul class="mt-1 space-y-1 p-1.5 bg-black/10 rounded-[1.25rem] border border-black/5 shadow-inner">
                    <li>
                        <a href="/admin/kelola-akses" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/kelola-akses*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-shield-check text-xl {{ request()->is('admin/kelola-akses*') ? 'text-white' : '' }}"></i>
                            Kelola Akses
                        </a>
                    </li>
                    <li>
                        <a href="/admin/pelanggan" class="flex items-center gap-4 px-3 min-h-[48px] rounded-xl font-bold text-sm transition-all interactive-element focus-visible:outline-none {{ request()->is('admin/pelanggan*') ? 'bg-admin-sideActive text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.3)] border border-white/20' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-users text-xl {{ request()->is('admin/pelanggan*') ? 'text-white' : '' }}"></i>
                            Daftar Pelanggan
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </nav>
</aside>

<!-- SCRIPT ACCORDION SIDEBAR (Tanpa jQuery, murni Vanilla JS biar kenceng & No Memory Leak) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Ambil URL path saat ini biar sidebar kebuka otomatis di posisi menu yang lagi diklik
        const currentPath = window.location.pathname;

        document.querySelectorAll('.menu-group').forEach(group => {
            const btn = group.querySelector('.btn-toggle-menu');
            const container = group.querySelector('.submenu-container');
            const caret = group.querySelector('.icon-caret');

            // Cek apakah ada submenu aktif di dalam grup ini
            const hasActiveLink = container.querySelector('a[class*="bg-admin-sideActive"]');

            // Buka default jika grup ini mengandung link aktif
            if (hasActiveLink) {
                btn.setAttribute('aria-expanded', 'true');
                container.style.maxHeight = container.scrollHeight + "px";
                caret.classList.add('rotate-180');
            } else {
                btn.setAttribute('aria-expanded', 'false');
                container.style.maxHeight = '0px';
                caret.classList.remove('rotate-180');
            }

            // Event listener klik
            btn.addEventListener('click', () => {
                const isExpanded = btn.getAttribute('aria-expanded') === 'true';

                // Opsional: Tutup semua menu lain saat satu menu dibuka (Accordion Mode)
                document.querySelectorAll('.menu-group').forEach(otherGroup => {
                    if (otherGroup !== group) {
                        const otherBtn = otherGroup.querySelector('.btn-toggle-menu');
                        const otherContainer = otherGroup.querySelector('.submenu-container');
                        const otherCaret = otherGroup.querySelector('.icon-caret');

                        otherBtn.setAttribute('aria-expanded', 'false');
                        otherContainer.style.maxHeight = '0px';
                        otherCaret.classList.remove('rotate-180');
                    }
                });

                // Toggle menu yg diklik
                if (!isExpanded) {
                    btn.setAttribute('aria-expanded', 'true');
                    container.style.maxHeight = container.scrollHeight + "px";
                    caret.classList.add('rotate-180');
                } else {
                    btn.setAttribute('aria-expanded', 'false');
                    container.style.maxHeight = '0px';
                    caret.classList.remove('rotate-180');
                }
            });
        });
    });
</script>
