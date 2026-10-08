@extends('pembeli.pembeli_master')

@section('title', 'Katalog Produk - Raya Kitchen')

@section('css')
<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up {
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Custom Scrollbar buat menu kategori (kalau di HP) */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection

@section('content')
<div class="mb-8 text-center md:text-left">
    <h2 class="text-3xl md:text-4xl font-black text-buyer-primary drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)] mb-3">
        Katalog Roti & Kue
    </h2>
    <p class="text-buyer-textSecondary font-semibold text-sm md:text-base">
        Fresh dari oven setiap harinya. Silakan pilih menu favoritmu cuy!
    </p>
</div>

<!-- ================= FILTER KATEGORI (Data dari tabel Products) ================= -->
<div class="mb-8">
    <div class="flex overflow-x-auto hide-scrollbar gap-3 pb-4" id="category-container">
        <!-- Tombol Filter Kategori bakal di-inject via JS -->
    </div>
</div>

<!-- ================= LOADING SPINNER ================= -->
<div id="loading-spinner" class="flex flex-col items-center justify-center my-16 hidden">
    <div class="relative w-16 h-16 mb-6">
        <div class="absolute inset-0 border-4 border-buyer-primary/20 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-buyer-primary rounded-full border-t-transparent animate-spin"></div>
    </div>
    <p class="text-buyer-primary font-bold animate-pulse text-lg tracking-wide drop-shadow-sm">
        Menyiapkan pesanan...
    </p>
</div>

<!--  LIST PRODUK VARIAN  -->
<!-- Grid: 1 di HP, 2 di Tablet, 3-4 di Laptop -->
<div id="produk-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
    <!-- Card produk bakal nongol di sini via JS -->
</div>

<!--  PAGINATION BAR  -->
<div class="mt-12 flex justify-center items-center gap-2" id="pagination-container">
    <!-- Tombol Pagination bakal di-inject via JS -->
</div>

@endsection

@push('script')
<script>
$(document).ready(function() {

    // Variabel penampung data dari database
    let productsMaster = [];
    let productVariants = [];

    // Konfigurasi Pagination & Filter
    let activeCategory = 'all';
    let currentPage = 1;
    let itemsPerPage = 8;
    let filteredData = [];

    fetchKatalogData();

    function fetchKatalogData() {
        $.ajax({
            url: "{{ url('/api/pembeli/katalog') }}",
            type: "GET",
            dataType: "JSON",
            beforeSend: function() {
                $('#loading-spinner').removeClass('hidden').show();
                $('#produk-container').hide().empty();
                $('#category-container').empty();
                $('#pagination-container').empty();
            },
            success: function(response) {
                if(response.status === 'success') {
                    // Masukin data "Semua Menu" di urutan pertama
                    productsMaster = [{ id: 'all', name: 'Semua Menu' }].concat(response.data.categories);
                    productVariants = response.data.variants;

                    // Jalankan engine UI
                    renderCategories();
                    applyFilterAndRender();
                } else {
                    $('#loading-spinner').hide();
                    Toast.show('error', 'Gagal memuat data katalog.');
                }
            },
            error: function(xhr) {
                $('#loading-spinner').hide();
                console.error(xhr.responseText);
                alert('Server error bjir, cek console.');
            }
        });
    }

    // ==========================================
    // FUNGSI RENDER TOMBOL KATEGORI
    // ==========================================
    function renderCategories() {
        let html = '';
        $.each(productsMaster, function(index, cat) {
            let isActive = cat.id === activeCategory;
            let btnClass = isActive
                ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]'
                : 'bg-buyer-bg text-buyer-textSecondary shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_2px_5px_rgba(192,153,206,0.15)] hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5';

            html += `
                <button class="filter-btn px-6 py-2.5 rounded-full font-extrabold text-sm transition-all duration-300 whitespace-nowrap ${btnClass}" data-id="${cat.id}">
                    ${cat.name}
                </button>
            `;
        });
        $('#category-container').html(html);

        $('.filter-btn').on('click', function() {
            activeCategory = $(this).data('id');
            // Konversi tipe data ID (karena dari DB ID-nya integer, dari tombol bisa jadi string)
            if(activeCategory !== 'all') activeCategory = parseInt(activeCategory);

            currentPage = 1;
            renderCategories();
            applyFilterAndRender();
        });
    }

    // ==========================================
    // FUNGSI FILTER & PAGINATION LOGIC
    // ==========================================
    function applyFilterAndRender() {
        $('#produk-container').hide().empty();
        $('#pagination-container').empty();
        $('#loading-spinner').removeClass('hidden').show();

        setTimeout(function() {
            $('#loading-spinner').hide();

            if (activeCategory === 'all') {
                filteredData = productVariants;
            } else {
                filteredData = productVariants.filter(v => v.product_id === activeCategory);
            }

            let startIndex = (currentPage - 1) * itemsPerPage;
            let endIndex = startIndex + itemsPerPage;
            let paginatedItems = filteredData.slice(startIndex, endIndex);

            renderVariants(paginatedItems);
            renderPagination();
            $('#produk-container').fadeIn(400);
        }, 300); // Simulasi delay pendek biar transisi mulus
    }

    // ==========================================
    // FUNGSI RENDER CARD VARIAN
    // ==========================================
    function renderVariants(items) {
        if(items.length === 0) {
            $('#produk-container').html(`
                <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                    <p class="text-buyer-textSecondary text-lg font-extrabold">Waduh, belum ada varian di kategori ini bjir.</p>
                </div>
            `);
            return;
        }

        let html = '';
        $.each(items, function(index, item) {
            let hargaFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.price);
            let animationDelay = index * 50;

            // 🔥 LOGIC BARU: Mengatur Path Gambar Storage / URL External
            let finalImageUrl = item.image_url;
            if (finalImageUrl && !finalImageUrl.startsWith('http')) {
                // Menghilangkan slash '/' di awal biar nggak nabrak sama fungsi asset()
                finalImageUrl = "{{ asset('') }}" + finalImageUrl.replace(/^\//, '');
            }

            html += `
                <div class="opacity-0 animate-fade-up bg-buyer-bg p-3.5 rounded-[2rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_10px_20px_rgba(192,153,206,0.15)] border border-white flex flex-col group hover:-translate-y-2 hover:shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_15px_30px_rgba(192,153,206,0.25)] transition-all duration-300" style="animation-delay: ${animationDelay}ms;">
                    <div class="relative w-full h-48 sm:h-56 rounded-2xl overflow-hidden shadow-[inset_0_2px_8px_rgba(0,0,0,0.1)] mb-4 bg-gray-100">
                        <img src="${finalImageUrl}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-out" alt="${item.variant_name}">
                        <div class="absolute top-3 right-3 px-3 py-1.5 bg-white/90 backdrop-blur-md text-buyer-primary text-[10px] font-extrabold rounded-full shadow-[0_4px_10px_rgba(192,153,206,0.2)] border border-white/50">
                            ${item.category_name}
                        </div>
                    </div>
                    <div class="flex flex-col flex-grow px-2">
                        <h5 class="font-extrabold text-buyer-textPrimary text-base lg:text-lg leading-snug mb-1 line-clamp-2" title="${item.variant_name}">
                            ${item.variant_name}
                        </h5>
                        <p class="font-black text-buyer-primary text-lg lg:text-xl mt-auto mb-4 drop-shadow-sm">
                            ${hargaFormatted}
                        </p>
                        <a href="{{ url('/produk') }}/${item.id}" class="w-full py-2.5 bg-buyer-primary text-white font-extrabold text-sm rounded-xl shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)] hover:-translate-y-[2px] active:translate-y-0.5 active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)] transition-all text-center flex items-center justify-center gap-2">
                            <span>Lihat Detail</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            `;
        });
        $('#produk-container').html(html);
    }

    // ==========================================
    // FUNGSI RENDER PAGINATION BAR
    // ==========================================
    function renderPagination() {
        let totalPages = Math.ceil(filteredData.length / itemsPerPage);
        if(totalPages <= 1) return;

        let html = '';
        let prevDisabled = currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white hover:-translate-y-0.5 hover:text-buyer-primary';

        html += `
            <button class="page-btn p-2.5 rounded-xl bg-buyer-bg text-buyer-textSecondary shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_2px_5px_rgba(192,153,206,0.15)] transition-all ${prevDisabled}" data-action="prev">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </button>
        `;

        for(let i = 1; i <= totalPages; i++) {
            let activeClass = i === currentPage
                ? 'bg-buyer-primary text-white shadow-[inset_0_-3px_4px_rgba(0,0,0,0.15),inset_0_3px_4px_rgba(255,255,255,0.4),0_4px_10px_rgba(192,153,206,0.4)]'
                : 'bg-buyer-bg text-buyer-textSecondary shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_2px_5px_rgba(192,153,206,0.15)] hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5';

            html += `
                <button class="page-btn w-10 h-10 flex items-center justify-center rounded-xl font-extrabold text-sm transition-all ${activeClass}" data-page="${i}">
                    ${i}
                </button>
            `;
        }

        let nextDisabled = currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white hover:-translate-y-0.5 hover:text-buyer-primary';
        html += `
            <button class="page-btn p-2.5 rounded-xl bg-buyer-bg text-buyer-textSecondary shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_2px_5px_rgba(192,153,206,0.15)] transition-all ${nextDisabled}" data-action="next">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
            </button>
        `;

        $('#pagination-container').html(html);

        $('.page-btn').on('click', function() {
            let action = $(this).data('action');
            let targetPage = $(this).data('page');

            if (action === 'prev' && currentPage > 1) {
                currentPage--;
                applyFilterAndRender();
            } else if (action === 'next' && currentPage < totalPages) {
                currentPage++;
                applyFilterAndRender();
            } else if (targetPage && targetPage !== currentPage) {
                currentPage = targetPage;
                applyFilterAndRender();
            }

            if (action || targetPage) $('html, body').animate({ scrollTop: 0 }, 'smooth');
        });
    }

});
</script>
@endpush
