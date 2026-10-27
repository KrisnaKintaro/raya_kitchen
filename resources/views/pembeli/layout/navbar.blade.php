<div class="sticky top-2 sm:top-3 z-50 px-2 sm:px-4 lg:px-3 w-full transition-all duration-300">
    <nav class="mx-auto bg-[#E6D6EB] rounded-[2.1rem] md:rounded-[2rem] border-[1px] shadow-[inset_0_-4px_8px_rgba(192,153,206,0.3),inset_0_4px_8px_rgba(255,255,255,1),0_20px_40px_-10px_rgba(192,153,206,0.4)] px-5 py-3.5 md:px-8 flex justify-between items-center">

        <a href="/" class="flex items-center gap-3 sm:gap-4 group">
            <div class="relative bg-white rounded-[1.25rem] p-2 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_4px_10px_rgba(192,153,206,0.25)] border-2 border-gray-50 transform group-hover:-translate-y-1 group-hover:rotate-3 transition-all duration-300">
                 <img src="{{ asset('asset/logo_app.png') }}" alt="Logo Raya Kitchen" class="h-9 md:h-11 w-auto">
            </div>
            <span class="font-black text-2xl md:text-3xl tracking-tight text-buyer-textPrimary drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)] group-hover:text-buyer-primary transition-colors duration-300">
                Raya Kitchen
            </span>
        </a>

        <div class="hidden lg:flex items-center gap-x-2 p-1.5 bg-buyer-bg/90 rounded-full shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,1)] border border-white backdrop-blur-sm">
            <a href="/" class="relative px-7 py-3 font-black text-base rounded-full transition-all duration-300 {{ request()->is('/') || request()->is('katalog*') ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_6px_12px_rgba(192,153,206,0.4)]' : 'text-buyer-textSecondary hover:text-buyer-primary hover:bg-white hover:shadow-[0_4px_10px_rgba(192,153,206,0.15)]' }}">Katalog</a>
            @auth
                <a href="/riwayat-pesanan" class="relative px-7 py-3 font-black text-base rounded-full transition-all duration-300 {{ request()->is('pesanan*') || request()->is('riwayat-pesanan*') ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_6px_12px_rgba(192,153,206,0.4)]' : 'text-buyer-textSecondary hover:text-buyer-primary hover:bg-white hover:shadow-[0_4px_10px_rgba(192,153,206,0.15)]' }}">Pesanan Saya</a>
            @endauth
            <a href="/tentang-kami" class="relative px-7 py-3 font-black text-base rounded-full transition-all duration-300 {{ request()->is('tentang-kami*') ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_6px_12px_rgba(192,153,206,0.4)]' : 'text-buyer-textSecondary hover:text-buyer-primary hover:bg-white hover:shadow-[0_4px_10px_rgba(192,153,206,0.15)]' }}">Tentang Kami</a>
        </div>

        <div class="flex items-center space-x-4">
            @guest
                <a href="/login" class="hidden sm:block font-black text-base text-buyer-textSecondary hover:text-buyer-primary px-4 transition-colors">Masuk</a>
                <a href="/register" class="px-8 py-3.5 bg-buyer-primary text-white font-black text-base rounded-full shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_6px_15px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all duration-200">Daftar</a>
            @endguest

            @auth
                @php
                    $cartCount = \App\Models\Cart::where('user_id', auth()->id())->sum('quantity');
                @endphp
                <a href="/keranjang" class="relative p-3.5 bg-white border-2 border-gray-50 text-buyer-primary rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_4px_10px_rgba(192,153,206,0.2)] hover:-translate-y-1 active:translate-y-0 transition-all duration-300 group">
                    <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    @if($cartCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-black px-2.5 py-0.5 rounded-full shadow-[inset_0_-2px_2px_rgba(0,0,0,0.2),0_2px_4px_rgba(239,68,68,0.4)] border-2 border-[#E6D6EB]">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                <div class="relative group hidden sm:block">
                    <button class="relative p-3.5 bg-white border-2 border-gray-50 text-buyer-primary rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_4px_10px_rgba(192,153,206,0.2)] hover:-translate-y-1 transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span id="notif-badge" class="hidden absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-black px-2.5 py-0.5 rounded-full shadow-[inset_0_-2px_2px_rgba(0,0,0,0.2),0_2px_4px_rgba(239,68,68,0.4)] border-2 border-[#E6D6EB]">
                            0
                        </span>
                    </button>

                    <div class="absolute right-0 mt-3 w-80 bg-white rounded-[2rem] shadow-[0_20px_40px_-5px_rgba(192,153,206,0.3)] py-4 invisible opacity-0 translate-y-3 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 border-2 border-gray-50 origin-top-right z-[100]">
                        <div class="px-5 pb-3 border-b border-gray-100 flex justify-between items-center">
                            <h4 class="font-black text-buyer-textPrimary">Notifikasi</h4>
                            <button id="btn-read-all" class="text-xs text-buyer-primary font-bold hover:underline">Tandai dibaca</button>
                        </div>
                        <div id="notif-list" class="max-h-[340px] overflow-y-auto flex flex-col p-2 scroll-smooth">
                        </div>
                    </div>
                </div>

                <div class="relative group hidden sm:block">
                    <button class="flex items-center gap-2 p-1.5 pl-1.5 pr-5 bg-white border-2 border-gray-50 rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_4px_10px_rgba(192,153,206,0.2)] hover:-translate-y-1 transition-all duration-300">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=C099CE&color=fff&bold=true" alt="User" class="w-10 h-10 rounded-full shadow-sm">
                        <span class="font-extrabold text-base text-buyer-textPrimary">{{ auth()->user()->name ?? 'Profil' }}</span>
                        <svg class="w-5 h-5 text-buyer-textSecondary" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="absolute right-0 mt-3 w-56 bg-white rounded-[2rem] shadow-[0_20px_40px_-5px_rgba(192,153,206,0.3)] py-3 invisible opacity-0 translate-y-3 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 border-2 border-gray-50 origin-top-right">
                        <a href="/profil" class="flex items-center gap-3 px-5 py-3 text-base font-black text-buyer-textSecondary hover:bg-buyer-primary/10 hover:text-buyer-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Edit Profil
                        </a>
                        <hr class="my-2 border-gray-100 mx-4">
                        <form action="{{ url('api/auth/logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-5 py-3 text-base font-black text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            <button id="btnHamburger" class="lg:hidden p-3 bg-white text-buyer-primary border-2 border-gray-50 rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_4px_10px_rgba(192,153,206,0.2)] hover:scale-105 active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </nav>
</div>

<!-- ================= MOBILE SIDEBAR MENU ================= -->
<div id="mobileOverlay" class="fixed inset-0 bg-buyer-textPrimary/40 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300"></div>

<div id="mobileSidebar" class="fixed top-0 right-0 h-full w-80 bg-buyer-bg shadow-[-20px_0_50px_rgba(192,153,206,0.3)] z-[70] transform translate-x-full transition-transform duration-300 ease-out border-l-4 border-white rounded-l-[3rem]">
    <div class="p-8 flex flex-col h-full overflow-y-auto hide-scrollbar">

        <div class="flex justify-between items-center mb-10 pb-5 border-b-2 border-white/60">
            <span class="font-black text-3xl text-buyer-primary drop-shadow-sm">Menu</span>
            <button id="btnCloseMenu" class="p-3 bg-white border-2 border-gray-50 rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_4px_10px_rgba(192,153,206,0.2)] text-buyer-textSecondary hover:text-red-500 hover:scale-105 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex flex-col space-y-4 font-black text-base">
            <a href="/" class="flex items-center gap-4 p-4 rounded-[1.5rem] transition-all duration-300 {{ request()->is('/') || request()->is('katalog*') ? 'bg-buyer-primary text-white shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)]' : 'bg-buyer-bg text-buyer-textSecondary border-2 border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white hover:text-buyer-primary hover:-translate-y-1' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Katalog
            </a>

            @auth
                <a href="/riwayat-pesanan" class="flex items-center gap-4 p-4 rounded-[1.5rem] transition-all duration-300 {{ request()->is('pesanan*') || request()->is('riwayat-pesanan*') ? 'bg-buyer-primary text-white shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)]' : 'bg-buyer-bg text-buyer-textSecondary border-2 border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white hover:text-buyer-primary hover:-translate-y-1' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Pesanan Saya
                </a>
                <a href="/profil" class="flex items-center gap-4 p-4 rounded-[1.5rem] transition-all duration-300 {{ request()->is('profil*') ? 'bg-buyer-primary text-white shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)]' : 'bg-buyer-bg text-buyer-textSecondary border-2 border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white hover:text-buyer-primary hover:-translate-y-1' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Edit Profil
                </a>
            @endauth

            <a href="/tentang-kami" class="flex items-center gap-4 p-4 rounded-[1.5rem] transition-all duration-300 {{ request()->is('tentang-kami*') ? 'bg-buyer-primary text-white shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)]' : 'bg-buyer-bg text-buyer-textSecondary border-2 border-white shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white hover:text-buyer-primary hover:-translate-y-1' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Tentang Kami
            </a>
        </div>

        <div class="mt-auto pt-10">
            @guest
                <div class="flex flex-col space-y-4">
                    <a href="/login" class="flex items-center justify-center gap-2 p-4 font-black text-buyer-textPrimary bg-white rounded-[1.5rem] shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_4px_10px_rgba(192,153,206,0.15)] border-2 border-gray-50 hover:-translate-y-1 transition-all">
                        Masuk
                    </a>
                    <a href="/register" class="flex items-center justify-center gap-2 p-4 font-black text-white bg-buyer-primary rounded-[1.5rem] shadow-[inset_0_-4px_6px_rgba(0,0,0,0.15),inset_0_4px_6px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all">
                        Daftar Sekarang
                    </a>
                </div>
            @endguest

            @auth
                <form action="{{ url('api/auth/logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-3 p-4 font-black text-red-500 bg-white rounded-[1.5rem] shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_4px_10px_rgba(192,153,206,0.15)] border-2 border-gray-50 hover:-translate-y-1 hover:text-red-600 transition-all group">
                        <svg class="w-6 h-6 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </button>
                </form>
            @endauth
        </div>
    </div>
</div>

@push('script')
<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnHamburger = document.getElementById('btnHamburger');
        const btnCloseMenu = document.getElementById('btnCloseMenu');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const mobileSidebar = document.getElementById('mobileSidebar');

        const toggleMobileMenu = () => {
            const isHidden = mobileOverlay.classList.contains('hidden');
            if (isHidden) {
                mobileOverlay.classList.remove('hidden');
                setTimeout(() => {
                    mobileOverlay.classList.remove('opacity-0');
                    mobileSidebar.classList.remove('translate-x-full');
                }, 10);
            } else {
                mobileOverlay.classList.add('opacity-0');
                mobileSidebar.classList.add('translate-x-full');
                setTimeout(() => {
                    mobileOverlay.classList.add('hidden');
                }, 300);
            }
        };

        if(btnHamburger) btnHamburger.addEventListener('click', toggleMobileMenu);
        if(btnCloseMenu) btnCloseMenu.addEventListener('click', toggleMobileMenu);
        if(mobileOverlay) mobileOverlay.addEventListener('click', toggleMobileMenu);
    });

</script>
@endpush
