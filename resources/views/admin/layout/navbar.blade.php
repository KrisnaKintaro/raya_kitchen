<header class="sticky top-0 lg:top-4 z-30 mx-0 lg:ml-6 lg:mr-6 bg-admin-navBg lg:rounded-[2rem] shadow-[0_6px_15px_rgba(147,104,183,0.1),inset_0_-4px_8px_rgba(147,104,183,0.2),inset_0_4px_8px_rgba(255,255,255,0.9)] border-b-2 lg:border-2 border-white h-[100px] lg:h-[110px] flex items-center justify-between px-6 lg:px-8 mb-0 lg:mb-6 transition-all">
    <!-- Bagian Kiri: Control & Konteks Halaman -->
    <div class="flex items-center gap-5">
        <!-- Toggle Sidebar (Mobile) -->
        <button id="btn-hamburger" class="lg:hidden w-12 h-12 flex items-center justify-center text-admin-sideBg bg-white rounded-2xl shadow-[inset_0_-2px_4px_rgba(147,104,183,0.15),0_4px_10px_rgba(147,104,183,0.15)] border border-white interactive-element focus-visible:outline-none -ml-2">
            <i class="ph ph-list text-2xl"></i>
        </button>

        <!-- Dinamis Title -->
        <h1 class="py-5 font-black text-2xl lg:text-3xl text-admin-textMain tracking-tight drop-shadow-sm">
            @yield('header_title', 'Dashboard')
        </h1>
    </div>

    <!-- Bagian Kanan: Global Action & Profil -->
    <div class="flex items-center gap-4 sm:gap-6 relative z-[100]">

        <!-- Quick Action: Ke Toko -->
        <a href="/" target="_blank" class="hidden sm:flex items-center gap-3 h-12 lg:h-14 px-6 rounded-2xl text-sm font-black text-admin-sideBg bg-white shadow-[inset_0_-3px_4px_rgba(147,104,183,0.15),inset_0_3px_4px_rgba(255,255,255,0.6),0_6px_12px_rgba(147,104,183,0.15)] hover:-translate-y-1 active:translate-y-0.5 transition-all border border-white/60">
            <i class="ph ph-storefront text-2xl"></i>
            Lihat Web Toko
        </a>

        <!-- Pembatas Vertikal (Warna dibikin lebih blend) -->
        <div class="h-8 w-1 bg-white rounded-full hidden sm:block mx-1 shadow-sm"></div>

        <!-- Profil Dropdown (Murni Hover CSS) -->
        <div class="relative group">
            <!-- Pemicu Hover -->
            <button class="flex items-center gap-4 h-17 lg:h-21 p-2.5 pr-5 bg-white/70 border-2 border-white rounded-[1.75rem] shadow-[inset_0_-2px_4px_rgba(147,104,183,0.1),0_4px_10px_rgba(147,104,183,0.1)] hover:-translate-y-1 transition-all duration-300 focus-visible:outline-none cursor-default">
                <div class="w-10 h-10 lg:w-11 lg:h-11 rounded-[14px] bg-admin-sideBg text-white flex items-center justify-center font-black shadow-inner border border-admin-accent/30 text-lg">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
                <div class="hidden md:flex flex-col items-start text-left">
                    <span class="text-base font-black text-admin-textMain leading-none">{{ auth()->user()->name ?? 'Administrator' }}</span>
                    <span class="text-[11px] font-bold text-admin-textMuted mt-1.5 uppercase tracking-[0.1em]">Superadmin</span>
                </div>
                <i class="ph ph-caret-down text-admin-sideBg text-lg ml-2 hidden md:block group-hover:rotate-180 transition-transform duration-300"></i>
            </button>

            <!-- Dropdown Konten -->
            <div class="absolute right-0 mt-3 w-64 bg-white rounded-[2rem] shadow-[0_20px_40px_-5px_rgba(147,104,183,0.25)] py-4 invisible opacity-0 translate-y-3 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 border-2 border-white origin-top-right">

                <!-- Info User (Tampil hanya di mobile) -->
                <div class="px-6 pb-4 border-b border-gray-100 mb-3 md:hidden">
                    <span class="block text-base font-black text-admin-textMain">{{ auth()->user()->name ?? 'Administrator' }}</span>
                    <span class="block text-xs font-bold text-admin-textMuted mt-1 uppercase tracking-wider">Superadmin</span>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="m-0 px-3">
                    @csrf
                    <button type="submit" class="w-full text-left px-5 py-3.5 rounded-2xl text-base font-black text-red-500 hover:text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors border border-transparent hover:border-red-100 shadow-sm">
                        <i class="ph ph-sign-out text-xl"></i>
                        Keluar Sistem
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
