@extends('pembeli.pembeli_master')
@section('title', 'Katalog Produk - Raya Kitchen')
@section('css')
@endsection

@section('content')
<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Katalog Produk</h2>
            <p class="text-muted">Temukan produk impianmu di sini.</p>
        </div>
    </div>

    <!-- Container Loading -->
    <div id="loading-spinner" class="text-center my-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Memuat data produk...</p>
    </div>

    <!-- Container List Produk (Diisi lewat AJAX) -->
    <div class="row" id="produk-container" style="display: none;">
        <!-- Card produk injection disini -->
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        loadKatalog();

        function loadKatalog() {
            $.ajax({
                url: "{{ url('/api/pembeli/katalog') }}",
                type: "GET",
                dataType: "JSON",
                beforeSend: function() {
                    $('#loading-spinner').show();
                    $('#produk-container').hide().empty();
                },
                success: function(response) {
                    $('#loading-spinner').hide();

                    if(response.status === 'success' && response.data.length > 0) {
                        let html = '';

                        $.each(response.data, function(index, item) {
                            // Format rupiah sederhana
                            let hargaFormatted = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(item.harga);

                            html += `
                                <div class="col-md-3 col-sm-6 mb-4">
                                    <div class="card h-100 shadow-sm border-0">
                                        <img src="${item.gambar}" class="card-img-top" alt="${item.nama}">
                                        <div class="card-body d-flex flex-column">
                                            <span class="badge bg-secondary mb-2 align-self-start">${item.kategori}</span>
                                            <h5 class="card-title text-truncate">${item.nama}</h5>
                                            <p class="card-text fw-bold text-primary fs-5 mt-auto">${hargaFormatted}</p>
                                            <a href="{{ url('/produk') }}/${item.slug}" class="btn btn-outline-primary btn-sm w-100 mt-2">Detail Produk</a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        $('#produk-container').html(html).fadeIn();
                    } else {
                        $('#produk-container').html(`
                            <div class="col-12 text-center my-5">
                                <p class="text-muted">Belum ada produk yang tersedia.</p>
                            </div>
                        `).show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#loading-spinner').hide();
                    console.error('Error fetching data:', error);
                    alert('Gagal mengambil data produk dari server bjir!');
                }
            });
        }
    });
</script>
@endpush
