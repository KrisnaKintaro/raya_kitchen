@extends('pembeli.pembeli_master')
@section('title', 'Tentang Kami - Raya Kitchen')

@section('content')
<div class="mb-8 text-center md:text-left">
    <h2 class="text-3xl md:text-4xl font-black text-buyer-primary drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)] mb-3">
        Tentang <span id="tk-title-name">Kami</span>
    </h2>
    <p class="text-buyer-textSecondary font-semibold text-sm md:text-base">
        Kenalan lebih dekat dengan dapur di balik kelezatan roti favoritmu.
    </p>
</div>

<!-- ================= LOADING SPINNER ================= -->
<div id="loading-spinner" class="flex flex-col items-center justify-center py-20">
    <div class="relative w-16 h-16 mb-6">
        <div class="absolute inset-0 border-4 border-buyer-primary/20 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-buyer-primary rounded-full border-t-transparent animate-spin"></div>
    </div>
    <p class="text-buyer-primary font-bold animate-pulse text-lg drop-shadow-sm">Memuat informasi...</p>
</div>

<!-- ================= KONTEN UTAMA ================= -->
<div id="content-container" class="hidden opacity-0 transition-opacity duration-500 bg-buyer-bg p-6 md:p-10 rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_15px_40px_rgba(192,153,206,0.15)] border border-white">
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
        <!-- Kolom Kiri: Gambar & Statistik -->
        <div class="space-y-6 w-full">
            <div class="relative w-full aspect-[4/3] rounded-[2rem] overflow-hidden shadow-[inset_0_4px_10px_rgba(0,0,0,0.08)] bg-white border-4 border-white">
                <img id="tk-image" src="" alt="Dapur Raya Kitchen" class="w-full h-full object-cover">
                
                <!-- Floating Stats -->
                <div class="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-[0_4px_10px_rgba(192,153,206,0.2)] border border-white flex justify-around text-center">
                    <div>
                        <h4 id="tk-year" class="font-black text-buyer-primary text-2xl">-</h4>
                        <p class="text-[10px] font-extrabold text-buyer-textSecondary uppercase tracking-wider">Berdiri</p>
                    </div>
                    <div class="w-px bg-buyer-primary/20"></div>
                    <div>
                        <h4 id="tk-products" class="font-black text-buyer-primary text-2xl">-</h4>
                        <p class="text-[10px] font-extrabold text-buyer-textSecondary uppercase tracking-wider">Total Produk</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Teks Sejarah & Kontak -->
        <div class="flex flex-col space-y-6 w-full">
            <div>
                <h3 class="font-extrabold text-buyer-textPrimary mb-2 text-sm uppercase tracking-wider">Awal Mula <span id="tk-name">Kami</span></h3>
                <p id="tk-history" class="text-buyer-textSecondary font-medium text-sm leading-relaxed">
                    <!-- History text bakal masuk ke sini via JS -->
                </p>
            </div>

            <!-- Box Kontak -->
            <div class="bg-buyer-primary/5 p-5 rounded-2xl shadow-[inset_0_2px_5px_rgba(192,153,206,0.15),0_1px_1px_rgba(255,255,255,1)] border border-white mt-auto">
                <h3 class="font-extrabold text-buyer-textPrimary mb-4 text-sm uppercase tracking-wider">Hubungi Kami</h3>
                
                <div class="space-y-4">
                    <a id="tk-wa-link" href="#" target="_blank" class="flex items-center gap-3 text-buyer-textSecondary hover:text-buyer-primary transition-colors group">
                        <div class="p-2 bg-white rounded-xl shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_2px_5px_rgba(192,153,206,0.15)] group-hover:-translate-y-0.5 transition-all">
                            <svg class="w-5 h-5 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>
                        <span id="tk-wa-text" class="font-bold">-</span>
                    </a>

                    <a id="tk-ig-link" href="#" target="_blank" class="hidden items-center gap-3 text-buyer-textSecondary hover:text-buyer-primary transition-colors group">
                        <div class="p-2 bg-white rounded-xl shadow-[inset_0_-2px_4px_rgba(192,153,206,0.1),0_2px_5px_rgba(192,153,206,0.15)] group-hover:-translate-y-0.5 transition-all">
                            <svg class="w-5 h-5 text-[#E1306C]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.149 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </div>
                        <span id="tk-ig-text" class="font-bold">-</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    $.ajax({
        url: "{{ url('/api/pembeli/tentang-kami') }}",
        type: "GET",
        dataType: "JSON",
        success: function(response) {
            if(response.status === 'success') {
                let data = response.data;
                
                $('#tk-title-name').text(data.company_name);
                $('#tk-name').text(data.company_name);
                $('#tk-history').text(data.background_history);
                $('#tk-year').text(data.established_year);
                $('#tk-products').text(data.total_products_made + '+');
                
                let imageUrl = data.image_url;
                if (imageUrl && !imageUrl.startsWith('http')) {
                    imageUrl = "{{ asset('storage/') }}/" + imageUrl;
                }
                $('#tk-image').attr('src', imageUrl);

                let waClean = data.whatsapp_number.replace(/[^0-9]/g, ""); 
                $('#tk-wa-link').attr('href', 'https://wa.me/' + waClean);
                $('#tk-wa-text').text('+' + data.whatsapp_number);

                if(data.instagram_handle) {
                    let igUsername = data.instagram_handle.replace('@', ''); 
                    $('#tk-ig-link').attr('href', 'https://instagram.com/' + igUsername);
                    $('#tk-ig-text').text(data.instagram_handle);
                    $('#tk-ig-link').removeClass('hidden').addClass('flex');
                }

                $('#loading-spinner').hide();
                $('#content-container').removeClass('hidden');
                
                setTimeout(() => {
                    $('#content-container').addClass('opacity-100');
                }, 50);
            }
        },
        error: function() {
            $('#loading-spinner').html('<p class="text-red-500 font-bold">Gagal memuat data perusahaan.</p>');
        }
    });
});
</script>
@endpush