<nav class="bg-buyer-primary/40 border-b border-white sticky top-0 z-50 shadow-[0_10px_30px_-10px_rgba(192,153,206,0.15)] transition-all duration-300">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">

        <!-- Kiri: Logo & Nama Brand (Clay Effect) -->
        <a href="/" class="flex items-center gap-3 group">
            <div class="relative bg-white rounded-xl p-1 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_8px_rgba(192,153,206,0.15)] transform group-hover:-translate-y-1 transition-all duration-300">
                 <img src="{{ asset('asset/logo_app.png') }}" alt="Logo Raya Kitchen" class="h-9 w-auto">
            </div>
            <span class="font-extrabold text-2xl tracking-tight text-buyer-primary drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)] transition-all duration-300">
                Raya Kitchen
            </span>
        </a>

        <!-- Tengah: Menu Navigasi Desktop -->
        <div class="hidden md:flex items-center gap-x-2 p-1.5 bg-buyer-bg rounded-full shadow-[inset_0_2px_5px_rgba(192,153,206,0.2),inset_0_-2px_5px_rgba(255,255,255,0.9)] border border-white">
            <a href="/" class="relative px-6 py-2 font-bold text-sm rounded-full transition-all duration-300 {{ request()->is('/') || request()->is('katalog*') ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]' : 'text-buyer-textSecondary hover:text-buyer-primary hover:bg-white hover:shadow-[0_2px_8px_rgba(192,153,206,0.15)]' }}">Katalog</a>
            @auth
                <a href="/pesanan" class="relative px-6 py-2 font-bold text-sm rounded-full transition-all duration-300 {{ request()->is('pesanan*') ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]' : 'text-buyer-textSecondary hover:text-buyer-primary hover:bg-white hover:shadow-[0_2px_8px_rgba(192,153,206,0.15)]' }}">Pesanan Saya</a>
            @endauth
            <a href="/tentang-kami" class="relative px-6 py-2 font-bold text-sm rounded-full transition-all duration-300 {{ request()->is('tentang-kami*') ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]' : 'text-buyer-textSecondary hover:text-buyer-primary hover:bg-white hover:shadow-[0_2px_8px_rgba(192,153,206,0.15)]' }}">Tentang Kami</a>
        </div>

        <!-- Kanan: Aksi -->
        <div class="flex items-center space-x-4">
            @guest
                <a href="/login" class="hidden md:block font-bold text-buyer-textSecondary hover:text-buyer-primary transition-colors">Masuk</a>
                <a href="/register" class="px-6 py-2.5 bg-buyer-primary text-white font-bold rounded-full shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)] hover:-translate-y-[2px] transition-all duration-200">Daftar</a>
            @endguest

            @auth
                <a href="/keranjang" class="relative p-2.5 bg-buyer-bg border border-white text-buyer-textSecondary hover:text-buyer-primary hover:bg-white rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_8px_rgba(192,153,206,0.15)] hover:-translate-y-0.5 transition-all duration-300 group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-[inset_0_-2px_3px_rgba(0,0,0,0.3),inset_0_2px_3px_rgba(255,255,255,0.4),0_2px_4px_rgba(239,68,68,0.4)]">3</span>
                </a>

                <div class="relative group hidden md:block">
                    <button class="flex items-center gap-2 p-1.5 pl-1.5 pr-4 bg-buyer-bg border border-white rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_8px_rgba(192,153,206,0.15)] hover:-translate-y-0.5 hover:bg-white transition-all duration-300">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=C099CE&color=fff" alt="User" class="w-8 h-8 rounded-full shadow-sm">
                        <span class="font-bold text-sm text-buyer-textPrimary">{{ auth()->user()->name ?? 'Profil' }}</span>
                        <svg class="w-4 h-4 text-buyer-textSecondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="absolute right-0 mt-3 w-48 bg-white rounded-2xl shadow-[0_15px_35px_-5px_rgba(192,153,206,0.25)] py-2 invisible opacity-0 translate-y-2 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 border border-white">
                        <a href="/profil" class="block px-4 py-2.5 text-sm font-semibold text-buyer-textPrimary hover:bg-buyer-bg hover:text-buyer-primary">Edit Profil</a>
                        <hr class="my-1 border-buyer-primary/10">
                        <form action="/logout" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-semibold text-red-500 hover:bg-red-50">Logout</button>
                        </form>
                    </div>
                </div>
            @endauth

            <!-- Mobile Hamburger Button -->
            <!-- Dikasih ID btnHamburger buat dipanggil di JS -->
            <button id="btnHamburger" class="md:hidden p-2 text-buyer-textPrimary bg-buyer-bg border border-white rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_8px_rgba(192,153,206,0.15)] hover:bg-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>
</nav>

<!-- ================= MOBILE SIDEBAR MENU ================= -->
<!-- Overlay Gelap (Backdrop) -->
<div id="mobileOverlay" class="fixed inset-0 bg-buyer-textPrimary/40 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300"></div>

<!-- Panel Sidebar (Clay Style & Rounded) -->
<!-- Gw kasih rounded-l-3xl biar pinggiran kirinya melengkung halus -->
<div id="mobileSidebar" class="fixed top-0 right-0 h-full w-72 bg-buyer-bg shadow-[-20px_0_40px_rgba(192,153,206,0.2)] z-[70] transform translate-x-full transition-transform duration-300 ease-out border-l border-white rounded-l-3xl">
    <div class="p-6 flex flex-col h-full overflow-y-auto">
        <!-- Header Sidebar -->
        <div class="flex justify-between items-center mb-8 pb-4 border-b border-white/60">
            <span class="font-extrabold text-2xl text-buyer-primary drop-shadow-[0_1px_1px_rgba(255,255,255,0.8)]">Menu</span>

            <!-- Tombol Close (Clay Effect) -->
            <button id="btnCloseMenu" class="p-2 bg-buyer-bg border border-white rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_2px_5px_rgba(192,153,206,0.15)] text-buyer-textSecondary hover:text-red-500 hover:bg-white hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Link Navigasi Mobile (Pakai Icon & Clay Logic) -->
        <div class="flex flex-col space-y-3 font-bold text-sm">

            <!-- Katalog -->
            <a href="/" class="flex items-center gap-3 p-3.5 rounded-2xl transition-all duration-300
                {{ request()->is('/') || request()->is('katalog*')
                    ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]'
                    : 'bg-buyer-bg text-buyer-textSecondary border border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(192,153,206,0.15)]' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Katalog
            </a>

            @auth
                <!-- Pesanan Saya -->
                <a href="/pesanan" class="flex items-center gap-3 p-3.5 rounded-2xl transition-all duration-300
                    {{ request()->is('pesanan*')
                        ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]'
                        : 'bg-buyer-bg text-buyer-textSecondary border border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(192,153,206,0.15)]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Pesanan Saya
                </a>

                <!-- Profil -->
                <a href="/profil" class="flex items-center gap-3 p-3.5 rounded-2xl transition-all duration-300
                    {{ request()->is('profil*')
                        ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]'
                        : 'bg-buyer-bg text-buyer-textSecondary border border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(192,153,206,0.15)]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Edit Profil
                </a>
            @endauth

            <!-- Tentang Kami -->
            <a href="/tentang-kami" class="flex items-center gap-3 p-3.5 rounded-2xl transition-all duration-300
                {{ request()->is('tentang-kami*')
                    ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]'
                    : 'bg-buyer-bg text-buyer-textSecondary border border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(192,153,206,0.15)]' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Tentang Kami
            </a>
        </div>

        <!-- Bagian Bawah (Call to Action / Logout) -->
        <div class="mt-auto pt-8">
            @guest
                <div class="flex flex-col space-y-3">
                    <a href="/login" class="flex items-center justify-center gap-2 p-3.5 font-bold text-buyer-textSecondary bg-buyer-bg border border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] rounded-2xl hover:bg-white hover:text-buyer-primary transition-all">
                        Masuk
                    </a>
                    <!-- Tombol Daftar pakai efek Clay penuh -->
                    <a href="/register" class="flex items-center justify-center gap-2 p-3.5 font-bold text-white bg-buyer-primary rounded-2xl shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)] hover:-translate-y-[2px] transition-all">
                        Daftar Sekarang
                    </a>
                </div>
            @endguest

            @auth
                <form action="/logout" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 p-3.5 font-bold text-red-500 bg-buyer-bg border border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] rounded-2xl hover:bg-white hover:-translate-y-[2px] transition-all group">
                        <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </button>
                </form>
            @endauth
        </div>
    </div>
</div>

<!-- ================= SCRIPT LOGIC ================= -->
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnHamburger = document.getElementById('btnHamburger');
        const btnCloseMenu = document.getElementById('btnCloseMenu');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const mobileSidebar = document.getElementById('mobileSidebar');

        // Fungsi buat buka/tutup menu
        const toggleMobileMenu = () => {
            const isHidden = mobileOverlay.classList.contains('hidden');

            if (isHidden) {
                // Proses Buka: Ilangin display:none dulu, baru jalani animasi geser
                mobileOverlay.classList.remove('hidden');
                setTimeout(() => {
                    mobileOverlay.classList.remove('opacity-0');
                    mobileSidebar.classList.remove('translate-x-full');
                }, 10); // Jeda bentar biar browser ngerender
            } else {
                // Proses Tutup: Geser dulu, baru display:none
                mobileOverlay.classList.add('opacity-0');
                mobileSidebar.classList.add('translate-x-full');
                setTimeout(() => {
                    mobileOverlay.classList.add('hidden');
                }, 300); // Nunggu animasi Tailwind (duration-300) kelar
            }
        };

        // Pasang event listener ke tombol hamburger, close, dan overlay
        if(btnHamburger) btnHamburger.addEventListener('click', toggleMobileMenu);
        if(btnCloseMenu) btnCloseMenu.addEventListener('click', toggleMobileMenu);
        if(mobileOverlay) mobileOverlay.addEventListener('click', toggleMobileMenu);
    });
</script>
@endpush
