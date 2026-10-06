@extends('pembeli.pembeli_master')
@section('title', 'Detail Produk - Raya Kitchen')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Tombol Kembali -->
    <div class="mb-6">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-buyer-bg text-buyer-textSecondary font-extrabold text-sm rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_10px_rgba(192,153,206,0.15)] hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Katalog
        </a>
    </div>

    <!-- ================= LOADING SPINNER ================= -->
    <div id="loading-spinner" class="flex flex-col items-center justify-center py-32">
        <div class="relative w-16 h-16 mb-6">
            <div class="absolute inset-0 border-4 border-buyer-primary/20 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-buyer-primary rounded-full border-t-transparent animate-spin"></div>
        </div>
        <p class="text-buyer-primary font-bold animate-pulse text-lg drop-shadow-sm">Membuka detail roti...</p>
    </div>

    <!-- ================= DETAIL PRODUK CONTAINER ================= -->
    <!-- Dibungkus Card Claymorphism Gede biar rapi dan nyatu -->
    <div id="detail-container" class="bg-buyer-bg p-6 md:p-8 lg:p-10 rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.2),inset_0_4px_6px_rgba(255,255,255,1),0_15px_40px_rgba(192,153,206,0.15)] border border-white hidden opacity-0 transition-opacity duration-500">

        <div class="flex flex-col md:flex-row gap-8 lg:gap-12">
            <!-- Kolom Kiri: Gambar Produk -->
            <!-- Dibatasi lebarnya md:w-5/12 biar proporsional dan ga menuhin layar -->
            <div class="w-full md:w-5/12 shrink-0">
                <div class="relative w-full aspect-square rounded-[2rem] overflow-hidden shadow-[inset_0_4px_10px_rgba(0,0,0,0.08)] bg-white border-4 border-white group">
                    <img id="pd-gambar" src="" alt="Gambar Produk" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">

                    <!-- Badge Kategori -->
                    <div id="pd-kategori" class="absolute top-4 left-4 px-4 py-1.5 bg-white/95 backdrop-blur-sm text-buyer-primary text-xs font-black rounded-full shadow-[0_4px_10px_rgba(192,153,206,0.2)] border border-gray-100 uppercase tracking-wider">
                        <!-- Kategori -->
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Detail & Form Add to Cart -->
            <!-- Lebar md:w-7/12 -->
            <div class="w-full md:w-7/12 flex flex-col py-2">

                <h1 id="pd-nama" class="text-3xl lg:text-4xl font-black text-buyer-textPrimary drop-shadow-sm mb-3 leading-tight">
                    <!-- Nama Roti -->
                </h1>

                <p id="pd-harga" class="text-3xl font-black text-buyer-primary drop-shadow-[0_1px_1px_rgba(255,255,255,0.8)] mb-6">
                    <!-- Harga -->
                </p>

                <!-- Box Deskripsi (Efek Tenggelam/Inset) -->
                <div class="bg-buyer-primary/5 p-5 rounded-2xl shadow-[inset_0_2px_5px_rgba(192,153,206,0.15),0_1px_1px_rgba(255,255,255,1)] border border-white mb-8">
                    <h3 class="font-extrabold text-buyer-textPrimary mb-2 text-sm uppercase tracking-wider">Deskripsi Produk</h3>
                    <p id="pd-deskripsi" class="text-buyer-textSecondary font-medium text-sm leading-relaxed">
                        <!-- Deskripsi -->
                    </p>
                </div>

                <!-- Spacer biar tombol selalu di bawah kalau teks deskripsi pendek -->
                <div class="mt-auto"></div>

                <!-- Kontrol Kuantitas & Tombol Beli -->
                <h3 class="font-extrabold text-buyer-textPrimary mb-3 text-sm">Jumlah Pembelian</h3>
                <div class="flex flex-col sm:flex-row gap-4 items-center">

                    <!-- Kontrol Jumlah (Padat & Proporsional) -->
                    <div class="flex items-center justify-between bg-white rounded-2xl p-1.5 shadow-[inset_0_2px_5px_rgba(192,153,206,0.15)] border border-gray-50 w-full sm:w-36 shrink-0 h-14">
                        <button id="btn-min" class="w-10 h-10 flex items-center justify-center bg-buyer-bg text-buyer-primary font-black rounded-xl shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white active:scale-95 transition-all text-lg">
                            −
                        </button>
                        <input type="number" id="input-qty" value="1" min="1" class="w-12 text-center font-black text-buyer-textPrimary bg-transparent border-none outline-none text-lg pointer-events-none appearance-none">
                        <button id="btn-plus" class="w-10 h-10 flex items-center justify-center bg-buyer-bg text-buyer-primary font-black rounded-xl shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1)] hover:bg-white active:scale-95 transition-all text-lg">
                            +
                        </button>
                    </div>

                    <!-- Tombol Keranjang -->
                    <button id="btn-add-cart" class="flex-grow w-full flex items-center justify-center gap-2 h-14 bg-buyer-primary text-white font-black text-base rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_6px_15px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all group">
                        <svg class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>Masukin Keranjang</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    // Tangkap ID dari variabel yang dilempar controller web.php
    const productId = "{{ $id }}";

    fetchDetailData();

    function fetchDetailData() {
        $.ajax({
            url: `/api/pembeli/katalog/${productId}`,
            type: "GET",
            dataType: "JSON",
            beforeSend: function() {
                $('#loading-spinner').show();
                $('#detail-container').addClass('hidden').removeClass('opacity-100');
            },
            success: function(response) {
                $('#loading-spinner').hide();

                if(response.status === 'success') {
                    let data = response.data;

                    let hargaFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
                    }).format(data.harga);

                    // Isi data ke elemen HTML
                    $('#pd-gambar').attr('src', data.gambar);
                    $('#pd-kategori').text(data.kategori);
                    $('#pd-nama').text(data.nama);
                    $('#pd-harga').text(hargaFormatted);
                    $('#pd-deskripsi').text(data.deskripsi);

                    // Tampilkan container detail dengan efek fade-in
                    $('#detail-container').removeClass('hidden');
                    // Pakai setTimeout kecil biar transisi CSS opacity jalannya mulus
                    setTimeout(() => {
                        $('#detail-container').addClass('opacity-100');
                    }, 50);
                }
            },
            error: function(xhr) {
                $('#loading-spinner').hide();
                alert('Gagal mengambil detail produk. Pastiin ID-nya bener cuy!');
                window.location.href = '/'; // Balikin ke katalog kalau error
            }
        });
    }

    // ==========================================
    // LOGIC KONTROL JUMLAH (PLUS / MINUS)
    // ==========================================
    const inputQty = $('#input-qty');

    $('#btn-plus').click(function() {
        let currentVal = parseInt(inputQty.val());
        inputQty.val(currentVal + 1);
    });

    $('#btn-min').click(function() {
        let currentVal = parseInt(inputQty.val());
        if(currentVal > 1) {
            inputQty.val(currentVal - 1);
        }
    });

    // Simulasi klik masuk keranjang
    $('#btn-add-cart').click(function() {
        let qty = inputQty.val();
        let namaRoti = $('#pd-nama').text().trim();

        // Nanti diganti sama AJAX POST ke cart
        alert(`Siaapp! ${qty} porsi ${namaRoti} berhasil dimasukin ke keranjang bjir! 🛒`);
    });
});
</script>
@endpush
