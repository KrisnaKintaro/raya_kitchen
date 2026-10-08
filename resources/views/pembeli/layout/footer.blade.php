<footer class="bg-buyer-primary/30 border-t-4 shadow-[0_-15px_40px_rgba(192,153,206,0.15)] mt-auto pt-10 pb-8 relative z-10 rounded-t-[3rem] overflow-hidden">

    <!-- Dekorasi Background Biar Gak Kosong -->
    <div class="absolute top-0 left-0 w-full h-full -z-10 pointer-events-none">
        <div class="absolute -top-24 -left-24 w-64 h-64 bg-buyer-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-buyer-hover/10 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-6 md:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-12 mb-10">

            <!-- Kolom Kiri: Brand & Deskripsi (Span 4) -->
            <div class="md:col-span-4 flex flex-col space-y-5">
                <!-- Logo Clay Effect -->
                <a href="/" class="flex items-center gap-4 group w-fit">
                    <div class="relative bg-white rounded-2xl p-2 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_6px_12px_rgba(192,153,206,0.2)] border-2 border-gray-50 transform group-hover:-translate-y-1 transition-all duration-300">
                         <img src="{{ asset('asset/logo_app.png') }}" alt="Logo Raya Kitchen" class="h-10 w-auto">
                    </div>
                    <span class="font-black text-3xl tracking-tight text-buyer-primary drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)] group-hover:text-buyer-hover transition-colors">
                        Raya
                        <span class="block -mt-1.5 text-2xl text-buyer-textPrimary">Kitchen.</span>
                    </span>
                </a>

                <p class="text-buyer-textSecondary leading-relaxed text-sm font-bold pr-4">
                    Menghadirkan roti dan kue kering fresh dari oven setiap harinya. Pesan lebih mudah, cepat, dan otomatis lewat integrasi sistem WhatsApp Bot kami.
                </p>

                <!-- Sosmed Buttons (Cuma WA) -->
                <div class="flex gap-3 pt-2">
                    <a href="#" class="p-3 bg-white border-2 border-white rounded-2xl shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_4px_8px_rgba(192,153,206,0.15)] hover:-translate-y-1 hover:shadow-[0_8px_15px_rgba(192,153,206,0.2)] transition-all text-[#25D366] group">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Kolom Tengah: Navigasi Cepat (Span 3) -->
            <div class="md:col-span-3 flex flex-col pt-2 lg:pl-10">
                <h3 class="font-black text-buyer-textPrimary mb-6 text-xl drop-shadow-sm">Menu Cepat</h3>
                <div class="flex flex-col space-y-4 text-sm">
                    <a href="/" class="text-buyer-textSecondary hover:text-buyer-primary font-extrabold flex items-center group transition-colors">
                        <span class="w-2.5 h-2.5 rounded-full bg-buyer-primary opacity-0 -translate-x-3 group-hover:opacity-100 group-hover:translate-x-0 transition-all mr-3 shadow-[0_0_8px_rgba(192,153,206,0.6)]"></span>
                        Katalog Produk
                    </a>

                    @auth
                        <a href="/pesanan" class="text-buyer-textSecondary hover:text-buyer-primary font-extrabold flex items-center group transition-colors">
                            <span class="w-2.5 h-2.5 rounded-full bg-buyer-primary opacity-0 -translate-x-3 group-hover:opacity-100 group-hover:translate-x-0 transition-all mr-3 shadow-[0_0_8px_rgba(192,153,206,0.6)]"></span>
                            Cek Pesanan Saya
                        </a>
                    @endauth

                    <a href="/tentang-kami" class="text-buyer-textSecondary hover:text-buyer-primary font-extrabold flex items-center group transition-colors">
                        <span class="w-2.5 h-2.5 rounded-full bg-buyer-primary opacity-0 -translate-x-3 group-hover:opacity-100 group-hover:translate-x-0 transition-all mr-3 shadow-[0_0_8px_rgba(192,153,206,0.6)]"></span>
                        Tentang Kami
                    </a>
                    <a href="#" class="text-buyer-textSecondary hover:text-buyer-primary font-extrabold flex items-center group transition-colors">
                        <span class="w-2.5 h-2.5 rounded-full bg-buyer-primary opacity-0 -translate-x-3 group-hover:opacity-100 group-hover:translate-x-0 transition-all mr-3 shadow-[0_0_8px_rgba(192,153,206,0.6)]"></span>
                        Syarat & Ketentuan
                    </a>
                </div>
            </div>

            <!-- Kolom Kanan: Lokasi Maps yang Dirapihkan (Span 5) -->
            <div class="md:col-span-5 flex flex-col pt-2">
                <h3 class="font-black text-buyer-textPrimary mb-6 text-xl drop-shadow-sm">Lokasi Kami</h3>

                <!-- Claymorphism Container buat Maps -->
                <!-- Pake pb-3 aja biar ga offside bawahnya -->
                <div class="relative bg-buyer-bg p-2.5 rounded-[2rem] shadow-[inset_0_-4px_8px_rgba(192,153,206,0.2),inset_0_4px_8px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border-2 border-white group pb-3">

                    <!-- Overlay Tombol "Buka di Maps" -->
                    <a href="https://maps.google.com/?q=Perum+BDP,+Jl.+Husein+Sastranegara+Blok+BE+No.+26,+Jatisari,+Kec.+Jatiasih,+Kota+Bks" target="_blank" class="absolute top-2 left-3 z-10 px-2 py-2 bg-white/95 backdrop-blur-md text-blue-600 font-extrabold text-xs rounded-xl shadow-[0_4px_10px_rgba(0,0,0,0.1)] flex items-center gap-2 hover:scale-105 active:scale-95 transition-all border border-gray-100">
                        Buka di Maps
                        <svg class="w-8 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>

                    <!-- Map Iframe Container (Lebih tinggi dikit biar proporsional) -->
                    <div class="relative w-full h-52 rounded-[1.5rem] overflow-hidden bg-gray-200 shadow-[inset_0_2px_6px_rgba(0,0,0,0.1)]">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.753896561214!2d106.9419131!3d-6.3262657!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTknMzQuNiJTIDEwNsKwNTYnMzAuOSJF!5e0!3m2!1sen!2sid!4v1690000000000!5m2!1sen!2sid"
                            width="100%"
                            height="100%"
                            style="border:0; filter: contrast(1.1) saturate(1.1);"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>

                        <!-- Card Alamat (Sekarang 100% aman di dalam box abu-abu map) -->
                        <div class="absolute bottom-1 left-3 right-3 bg-white/95 backdrop-blur-xl p-3.5 rounded-2xl shadow-[0_4px_15px_rgba(0,0,0,0.15)] border border-white flex items-center gap-3">
                            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-buyer-textPrimary text-sm line-clamp-1">Dapur Raya Kitchen</h4>
                                <p class="text-[11px] text-buyer-textSecondary leading-tight mt-1 font-bold line-clamp-2">
                                    Perum BDP, Jl. Husein Sastranegara Blok BE No. 26, Bekasi
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Bagian Bawah: Copyright -->
        <div class="pt-6 mt-4 border-t-2 border-white flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-buyer-textSecondary text-sm font-extrabold">
                &copy; {{ date('Y') }} Raya Kitchen. All rights reserved.
            </p>
            <div class="flex items-center gap-1.5 text-sm font-extrabold text-buyer-textSecondary bg-white/50 px-4 py-2 rounded-full border border-white">
                Dibuat dengan <svg class="w-4 h-4 text-red-500 animate-pulse" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg> untuk Kepuasan Pelanggan
            </div>
        </div>
    </div>
</footer>
