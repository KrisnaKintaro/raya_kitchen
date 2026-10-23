@extends('pembeli.pembeli_master')
@section('title', 'Kelola Alamat - Raya Kitchen')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-2xl shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1)] flex flex-col justify-center relative mb-6">
        
        <!-- Header & Navigasi -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('profil') }}" class="text-purple-600 font-bold text-xs hover:underline flex items-center gap-1 mb-2">
                    &larr; Kembali ke Profil
                </a>
                <h1 class="text-buyer-textPrimary drop-shadow-sm text-xl font-bold mb-1">Daftar Alamat Pengiriman</h1>
                <p class="font-medium text-sm text-gray-600">Kelola daftar alamat pengiriman Anda.</p>
            </div>
            <button type="button" id="btnTambahAlamat" class="py-2 px-4 bg-purple-600 text-white font-bold text-xs rounded-xl hover:bg-purple-700 transition shadow-sm">
                + Tambah Alamat Baru
            </button>
        </div>

        <!-- Tempat notifikasi sukses -->
        <div id="alert-success" class="hidden mb-4 p-3 bg-green-100 text-green-700 rounded-xl text-sm font-bold border border-green-200"></div>

        <!-- Daftar Alamat Tersimpan (Ditampilkan via JS) -->
        <div id="list-alamat-container" class="space-y-4 mb-8">
            <!-- Loading state -->
            <p class="text-sm text-gray-500 italic">Memuat alamat...</p>
        </div>

        <!-- Form untuk mengelola/tambah alamat (Disembunyikan secara default) -->
        <div id="form-alamat-section" class="hidden border-t border-purple-100 pt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800" id="form-address-title">Tambah Alamat Baru</h2>
                <button type="button" id="btnBatal" class="text-red-500 font-bold text-xs hover:underline">Batal / Tutup</button>
            </div>

            <form id="alamatForm" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="address_id" id="address_id" value="">

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Label Alamat (Contoh: Rumah / Kantor)</label>
                    <input type="text" name="label" required
                        class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Rumah">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Provinsi</label>
                        <input type="text" name="province" required
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kota / Kabupaten</label>
                        <input type="text" name="city" required
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kecamatan</label>
                        <input type="text" name="district" required
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Pos</label>
                        <input type="text" name="postal_code" required
                            inputmode="numeric" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Jalan / Detail Lokasi</label>
                    <textarea name="street_address" rows="3" required
                        class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Masukkan nama jalan, nomor rumah, RT/RW..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Patokan (Opsional)</label>
                    <input type="text" name="landmark" 
                        class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Contoh: Dekat masjid / warna cat rumah">
                </div>

                <!-- Opsi Jadikan Alamat Utama (Disembunyikan jika sedang edit alamat utama) -->
                <div class="flex items-center gap-2 mb-4 bg-gray-50 p-3 rounded-2xl border border-gray-200" id="checkbox-utama-container">
                    <input type="checkbox" name="is_primary" id="is_primary" value="1" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-400">
                    <label for="is_primary" class="text-sm font-semibold text-gray-700 cursor-pointer">Jadikan Alamat Ini sebagai Alamat Utama</label>
                </div>

                <!-- Tombol Simpan -->
                <div class="mt-2">
                    <button type="submit" id="btnSubmit" class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-2xl shadow transition duration-200">
                        Simpan Alamat
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    let globalAddresses = [];

    // Muat data alamat
    function loadAlamatData() {
        $.ajax({
            url: '/api/pembeli/profil-data',
            type: 'GET',
            dataType: 'json',
            xhrFields: { withCredentials: true },
            success: function(res) {
                if(res.status === 'success') {
                    globalAddresses = res.data.all_addresses || [];
                    renderListAlamat(globalAddresses);
                }
            },
            error: function(xhr) {
                console.error('Gagal memuat alamat:', xhr.responseText);
                $('#list-alamat-container').html('<p class="text-sm text-red-500 italic">Gagal memuat data alamat.</p>');
            }
        });
    }

    // Render daftar alamat dalam bentuk List/Card
    function renderListAlamat(addresses) {
        let container = $('#list-alamat-container');
        container.empty();

        if(addresses.length === 0) {
            container.append('<div class="p-6 text-center bg-gray-50 border border-dashed border-gray-300 rounded-2xl"><p class="text-sm text-gray-500 font-medium">Anda belum memiliki alamat tersimpan.</p></div>');
            return;
        }

        addresses.forEach(function(addr) {
            let isUtama = addr.is_primary == 1;
            
            // Badge & Tombol
            let badgeUtama = isUtama 
                ? '<span class="bg-purple-100 text-purple-700 text-[10px] uppercase px-2.5 py-1 rounded-full font-extrabold tracking-wide">Utama</span>' 
                : '';
                
            let btnJadikanUtama = !isUtama 
                ? `<button type="button" class="btn-jadikan-utama text-xs font-bold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 hover:text-green-600 px-3 py-1.5 rounded-lg transition" data-id="${addr.id}">Jadikan Utama</button>` 
                : '';

            let card = `
                <div class="p-4 ${isUtama ? 'bg-purple-50/30 border-purple-300' : 'bg-white border-gray-200'} border rounded-2xl transition hover:shadow-sm">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-800 text-base">${addr.label || 'Alamat'}</h3>
                            ${badgeUtama}
                        </div>
                        <div class="flex flex-wrap gap-2">
                            ${btnJadikanUtama}
                            <button type="button" class="btn-edit-alamat text-xs font-bold text-purple-700 bg-purple-100 hover:bg-purple-200 px-3 py-1.5 rounded-lg transition" data-id="${addr.id}">Edit</button>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 mt-2 space-y-1">
                        <p class="font-medium text-gray-700">${addr.recipient_name || ''} - ${addr.recipient_whatsapp || ''}</p>
                        <p>${addr.street_address}</p>
                        <p>${addr.district}, ${addr.city}, ${addr.province} ${addr.postal_code}</p>
                        ${addr.landmark ? `<p class="text-xs text-gray-500 italic mt-1">Patokan: ${addr.landmark}</p>` : ''}
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    // Fungsi pengiriman AJAX untuk Simpan / Set Utama
    function submitAjax(formData, loadingText = 'Menyimpan...') {
        let btnSubmit = $('#btnSubmit');
        let alertSuccess = $('#alert-success');
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        let originalText = btnSubmit.text();
        btnSubmit.text(loadingText).prop('disabled', true);

        $.ajax({
            url: '/api/pembeli/profil-data',
            type: 'POST', 
            data: JSON.stringify(formData),
            contentType: 'application/json',
            xhrFields: { withCredentials: true },
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(res) {
                if(res.status === 'success') {
                    alertSuccess.text(res.message).removeClass('hidden');
                    setTimeout(() => alertSuccess.addClass('hidden'), 3000);
                    
                    // Reset dan tutup form
                    $('#alamatForm')[0].reset();
                    $('#address_id').val('');
                    $('#form-alamat-section').slideUp();
                    
                    // Muat ulang daftar
                    loadAlamatData(); 
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            error: function(xhr) {
                console.error('Error saat menyimpan:', xhr.responseText);
                alert('Terjadi kesalahan saat memproses data.');
            },
            complete: function() {
                btnSubmit.text(originalText).prop('disabled', false);
            }
        });
    }

    // Klik Jadikan Utama
    $(document).on('click', '.btn-jadikan-utama', function() {
        let id = $(this).data('id');
        let selected = globalAddresses.find(item => item.id == id);
        
        if(selected) {
            // Ambil semua data utuh milik alamat ini, tapi timpa is_primary jadi 1
            let formData = {
                address_id: selected.id,
                label: selected.label,
                province: selected.province,
                city: selected.city,
                district: selected.district,
                postal_code: selected.postal_code,
                street_address: selected.street_address,
                landmark: selected.landmark,
                is_primary: 1
            };
            submitAjax(formData, 'Mengatur utama...');
        }
    });

    // Klik Edit Alamat
    $(document).on('click', '.btn-edit-alamat', function() {
        let id = $(this).data('id');
        let addr = globalAddresses.find(item => item.id == id);
        
        if(addr) {
            $('#address_id').val(addr.id);
            $('input[name="label"]').val(addr.label || '');
            $('input[name="province"]').val(addr.province || '');
            $('input[name="city"]').val(addr.city || '');
            $('input[name="district"]').val(addr.district || '');
            $('input[name="postal_code"]').val(addr.postal_code || '');
            $('textarea[name="street_address"]').val(addr.street_address || '');
            $('input[name="landmark"]').val(addr.landmark || '');
            
            if(addr.is_primary == 1) {
                $('#checkbox-utama-container').hide();
                $('#is_primary').prop('checked', true);
            } else {
                $('#checkbox-utama-container').show();
                $('#is_primary').prop('checked', false);
            }

            $('#form-address-title').text('Edit Alamat: ' + (addr.label || ''));
            $('#form-alamat-section').slideDown();
            
            // Scroll sedikit ke form
            $('html, body').animate({
                scrollTop: $("#form-alamat-section").offset().top - 100
            }, 500);
        }
    });

    // Klik Tambah Alamat Baru
    $('#btnTambahAlamat').on('click', function() {
        $('#alamatForm')[0].reset(); // Bersihkan form
        $('#address_id').val(''); 
        
        $('#checkbox-utama-container').show();
        $('#is_primary').prop('checked', false);
        $('#form-address-title').text('Tambah Alamat Baru');
        
        $('#form-alamat-section').slideDown();
        $('html, body').animate({
            scrollTop: $("#form-alamat-section").offset().top - 100
        }, 500);
    });

    // Klik Tutup / Batal Form
    $('#btnBatal').on('click', function() {
        $('#form-alamat-section').slideUp();
    });

    // Submit Form
    $('#alamatForm').on('submit', function(e) {
        e.preventDefault(); 
        
        let formData = {
            address_id: $('#address_id').val(),
            label: $('input[name="label"]').val(),
            province: $('input[name="province"]').val(),
            city: $('input[name="city"]').val(),
            district: $('input[name="district"]').val(),
            postal_code: $('input[name="postal_code"]').val(),
            street_address: $('textarea[name="street_address"]').val(),
            landmark: $('input[name="landmark"]').val(),
            is_primary: $('#is_primary').is(':checked') ? 1 : 0
        };

        submitAjax(formData, 'Menyimpan...');
    });

    // Inisialisasi awal
    loadAlamatData();
});
</script>
@endpush