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

        <!-- Alert Sukses -->
        <div id="alert-success" class="hidden mb-4 p-3 bg-green-100 text-green-700 rounded-xl text-sm font-bold border border-green-200"></div>

        <!-- Container List Alamat -->
        <div id="list-alamat-container" class="space-y-4 mb-8">
            <p class="text-sm text-gray-500 italic">Memuat alamat...</p>
        </div>

        <!-- Form Kelola Alamat -->
        <div id="form-alamat-section" class="hidden border-t border-purple-100 pt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800" id="form-address-title">Tambah Alamat Baru</h2>
                <button type="button" id="btnBatal" class="text-red-500 font-bold text-xs hover:underline">Batal / Tutup</button>
            </div>

            <form id="alamatForm" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="address_id" id="address_id" value="">

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Label Alamat</label>
                    <input type="text" name="label" required class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Contoh: Rumah / Kantor">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Autocomplete Provinsi -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Provinsi</label>
                        <input type="text" id="provinsi" name="province" required autocomplete="off" list="list-provinsi" 
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Ketik nama provinsi...">
                        <datalist id="list-provinsi"></datalist>
                    </div>
                    
                    <!-- Autocomplete Kota -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kota / Kabupaten</label>
                        <input type="text" id="kota" name="city" required autocomplete="off" list="list-kota" disabled
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 disabled:bg-gray-100" placeholder="Ketik nama kota...">
                        <datalist id="list-kota"></datalist>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Autocomplete Kecamatan -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kecamatan</label>
                        <input type="text" id="kecamatan" name="district" required autocomplete="off" list="list-kecamatan" disabled
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 disabled:bg-gray-100" placeholder="Ketik nama kecamatan...">
                        <datalist id="list-kecamatan"></datalist>
                    </div>
                    
                    <!-- Manual Kode Pos -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Pos</label>
                        <input type="text" name="postal_code" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Masukkan 5 digit angka">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Jalan / Detail Lokasi</label>
                    <textarea name="street_address" rows="3" required class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Masukkan nama jalan, nomor rumah, RT/RW..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Patokan (Opsional)</label>
                    <input type="text" name="landmark" class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400" placeholder="Contoh: Dekat masjid / warna cat rumah">
                </div>

                <!-- Opsi Utama -->
                <div class="flex items-center gap-2 mb-4 bg-gray-50 p-3 rounded-2xl border border-gray-200" id="checkbox-utama-container">
                    <input type="checkbox" name="is_primary" id="is_primary" value="1" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-400">
                    <label for="is_primary" class="text-sm font-semibold text-gray-700 cursor-pointer">Jadikan Alamat Ini sebagai Alamat Utama</label>
                </div>

                <div class="mt-2">
                    <button type="submit" id="btnSubmit" class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-2xl shadow transition duration-200">
                        Simpan Alamat
                    </button>
                </div>

                <!-- Elemen Map Picker dengan Fitur Cari Jalan -->
                <div class="mb-5 mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tandai Lokasi di Peta (Wajib untuk hitung ongkir)</label>
                    <div id="map" class="w-full h-[300px] rounded-xl border-2 border-gray-200 z-10"></div>
                    <p class="text-xs text-gray-500 mt-1">Gunakan kotak pencarian di peta atau geser pin merah untuk menentukan titik pasti lokasi Anda.</p>
                    
                    <input type="hidden" name="latitude" id="latitude" required>
                    <input type="hidden" name="longitude" id="longitude" required>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Load CSS & JS Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Load Leaflet Control Geocoder (Plugin Pencarian Nama Jalan) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

@push('script')
<script>
$(document).ready(function() {
    let globalAddresses = [];
    let provincesData = [];
    let citiesData = [];
    let districtsData = [];

    // --- INISIALISASI MAP & MARKER TUNGGAL ---
    let defaultLat = -7.983908; 
    let defaultLng = 112.621391;
    let map = L.map('map').setView([defaultLat, defaultLng], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let marker = null;

    function setMarker(lat, lng) {
        map.eachLayer(function (layer) {
            if (layer instanceof L.Marker) {
                map.removeLayer(layer);
            }
        });

        marker = L.marker([lat, lng], {draggable: true}).addTo(map);
        
        marker.on('dragend', function (e) {
            updateLokasi(marker.getLatLng().lat, marker.getLatLng().lng);
        });

        updateLokasi(lat, lng);
    }

    function updateLokasi(lat, lng) {
        $('#latitude').val(lat);
        $('#longitude').val(lng);
    }

    setMarker(defaultLat, defaultLng);

    map.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng);
    });

    // --- PENCARIAN JALAN SELURUH INDONESIA ---
    L.Control.geocoder({
        defaultMarkGeocoder: false, 
        placeholder: 'Cari jalan atau lokasi di Indonesia...',
        errorMessage: 'Lokasi tidak ditemukan.',
        geocoder: L.Control.Geocoder.nominatim({
            geocodingQueryParams: {
                countrycodes: 'id',
                addressdetails: 1,
                limit: 5
            }
        })
    }).on('markgeocode', function(e) {
        let center = e.geocode.center;
        map.setView(center, 16); 
        setMarker(center.lat, center.lng);
    }).addTo(map);

    // --- LOGIKA AUTOCOMPLETE WILAYAH ---
    function loadProvinces() {
        if(provincesData.length > 0) return Promise.resolve();
        return fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
            .then(res => res.json())
            .then(data => {
                provincesData = data;
                let options = '';
                data.forEach(p => options += `<option value="${p.name}">`);
                $('#list-provinsi').html(options);
            });
    }

    $('#provinsi').on('input', function() {
        let selectedVal = $(this).val();
        let selectedProv = provincesData.find(p => p.name === selectedVal);
        
        $('#kota').val('').prop('disabled', true);
        $('#list-kota').empty();
        $('#kecamatan').val('').prop('disabled', true);
        $('#list-kecamatan').empty();

        if (selectedProv) {
            $('#kota').attr('placeholder', 'Memuat data kota...').prop('disabled', false);
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProv.id}.json`)
                .then(res => res.json())
                .then(data => {
                    citiesData = data;
                    let options = '';
                    data.forEach(c => options += `<option value="${c.name}">`);
                    $('#list-kota').html(options);
                    $('#kota').attr('placeholder', 'Ketik nama kota/kabupaten...');
                });
        }
    });

    $('#kota').on('input', function() {
        let selectedVal = $(this).val();
        let selectedCity = citiesData.find(c => c.name === selectedVal);
        
        $('#kecamatan').val('').prop('disabled', true);
        $('#list-kecamatan').empty();

        if (selectedCity) {
            $('#kecamatan').attr('placeholder', 'Memuat data kecamatan...').prop('disabled', false);
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedCity.id}.json`)
                .then(res => res.json())
                .then(data => {
                    districtsData = data;
                    let options = '';
                    data.forEach(d => options += `<option value="${d.name}">`);
                    $('#list-kecamatan').html(options);
                    $('#kecamatan').attr('placeholder', 'Ketik nama kecamatan...');
                });
        }
    });

    // --- LOGIKA CRUD ALAMAT ---
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
            error: function() {
                $('#list-alamat-container').html('<p class="text-sm text-red-500 italic">Gagal memuat data alamat.</p>');
            }
        });
    }

    function renderListAlamat(addresses) {
        let container = $('#list-alamat-container');
        container.empty();
        if(addresses.length === 0) {
            container.append('<div class="p-6 text-center bg-gray-50 border border-dashed border-gray-300 rounded-2xl"><p class="text-sm text-gray-500 font-medium">Anda belum memiliki alamat tersimpan.</p></div>');
            return;
        }

        addresses.forEach(function(addr) {
            let isUtama = addr.is_primary == 1;
            let badgeUtama = isUtama ? '<span class="bg-purple-100 text-purple-700 text-[10px] uppercase px-2.5 py-1 rounded-full font-extrabold tracking-wide">Utama</span>' : '';
            let btnJadikanUtama = !isUtama ? `<button type="button" class="btn-jadikan-utama text-xs font-bold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 hover:text-green-600 px-3 py-1.5 rounded-lg transition" data-id="${addr.id}">Jadikan Utama</button>` : '';
            let textJarak = addr.distance_to_store ? `<span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg ml-2">Jarak: ${addr.distance_to_store} KM</span>` : '';

            container.append(`
                <div class="p-4 ${isUtama ? 'bg-purple-50/30 border-purple-300' : 'bg-white border-gray-200'} border rounded-2xl transition hover:shadow-sm">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-800 text-base">${addr.label || 'Alamat'}</h3>
                            ${badgeUtama}
                        </div>
                        <div class="flex flex-wrap gap-2">
                            ${btnJadikanUtama}
                            <button type="button" class="btn-edit-alamat text-xs font-bold text-purple-700 bg-purple-100 hover:bg-purple-200 px-3 py-1.5 rounded-lg transition" data-id="${addr.id}">Edit</button>
                            <button type="button" class="btn-hapus-alamat text-xs font-bold text-red-700 bg-red-100 hover:bg-red-200 px-3 py-1.5 rounded-lg transition" data-id="${addr.id}">Hapus</button>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 mt-2 space-y-1">
                        <p class="font-medium text-gray-700">${addr.recipient_name || ''} ${addr.recipient_whatsapp || ''} ${textJarak}</p>
                        <p>${addr.street_address}</p>
                        <p>${addr.district}, ${addr.city}, ${addr.province} ${addr.postal_code}</p>
                        ${addr.landmark ? `<p class="text-xs text-gray-500 italic mt-1">Patokan: ${addr.landmark}</p>` : ''}
                    </div>
                </div>
            `);
        });
    }

    function submitAjax(formData, loadingText = 'Menyimpan...') {
        let btnSubmit = $('#btnSubmit');
        let alertSuccess = $('#alert-success');
        let originalText = btnSubmit.text();
        btnSubmit.text(loadingText).prop('disabled', true);

        $.ajax({
            url: '/api/pembeli/profil-data',
            type: 'POST', 
            data: JSON.stringify(formData),
            contentType: 'application/json',
            xhrFields: { withCredentials: true },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if(res.status === 'success') {
                    alertSuccess.text(res.message).removeClass('hidden');
                    setTimeout(() => alertSuccess.addClass('hidden'), 3000);
                    
                    $('#alamatForm')[0].reset();
                    $('#form-alamat-section').slideUp();
                    $('#kota, #kecamatan').prop('disabled', true);
                    loadAlamatData(); 
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            error: function() { alert('Terjadi kesalahan saat memproses data.'); },
            complete: function() { btnSubmit.text(originalText).prop('disabled', false); }
        });
    }

    $(document).on('click', '.btn-hapus-alamat', function() {
        let id = $(this).data('id');
        let btn = $(this);
        
        Swal.fire({
            title: "Yakin ingin menghapus?",
            text: "Alamat ini akan dihapus dari daftar Anda!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#9333ea", 
            cancelButtonColor: "#ef4444", 
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                btn.text('Menghapus...').prop('disabled', true);
                
                $.ajax({
                    url: `/api/pembeli/alamat/${id}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        if(res.status === 'success') {
                            Swal.fire({
                                title: "Terhapus!",
                                text: res.message,
                                icon: "success",
                                confirmButtonColor: "#9333ea"
                            });
                            loadAlamatData(); 
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: "Gagal!",
                            text: xhr.responseJSON?.message || 'Gagal menghapus alamat.',
                            icon: "error",
                            confirmButtonColor: "#9333ea"
                        });
                        btn.text('Hapus').prop('disabled', false);
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-jadikan-utama', function() {
        let addr = globalAddresses.find(item => item.id == $(this).data('id'));
        if(addr) submitAjax({...addr, address_id: addr.id, is_primary: 1}, 'Mengatur utama...');
    });

    // --- SINKRONISASI OTOMATIS WILAYAH SAAT EDIT ALAMAT ---
    $(document).on('click', '.btn-edit-alamat', function() {
        let addr = globalAddresses.find(item => item.id == $(this).data('id'));
        if(addr) {
            $('#address_id').val(addr.id);
            $('input[name="label"]').val(addr.label || '');
            $('input[name="postal_code"]').val(addr.postal_code || '');
            $('textarea[name="street_address"]').val(addr.street_address || '');
            $('input[name="landmark"]').val(addr.landmark || '');

            $('#checkbox-utama-container').toggle(addr.is_primary != 1);
            $('#is_primary').prop('checked', addr.is_primary == 1);
            $('#form-address-title').text('Edit Alamat: ' + (addr.label || ''));
            $('#form-alamat-section').slideDown();

            // Load Provinsi, Kota, dan Kecamatan secara berurutan agar datalist-nya valid
            loadProvinces().then(() => {
                $('#provinsi').val(addr.province);
                let selectedProv = provincesData.find(p => p.name === addr.province);

                if (selectedProv) {
                    $('#kota').prop('disabled', false).attr('placeholder', 'Memuat kota...');
                    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProv.id}.json`)
                        .then(res => res.json())
                        .then(cities => {
                            citiesData = cities;
                            let options = '';
                            cities.forEach(c => options += `<option value="${c.name}">`);
                            $('#list-kota').html(options);
                            $('#kota').val(addr.city).prop('disabled', false);

                            let selectedCity = cities.find(c => c.name === addr.city);
                            if (selectedCity) {
                                $('#kecamatan').prop('disabled', false).attr('placeholder', 'Memuat kecamatan...');
                                fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedCity.id}.json`)
                                    .then(res => res.json())
                                    .then(districts => {
                                        districtsData = districts;
                                        let distOptions = '';
                                        districts.forEach(d => distOptions += `<option value="${d.name}">`);
                                        $('#list-kecamatan').html(distOptions);
                                        $('#kecamatan').val(addr.district).prop('disabled', false);
                                    });
                            }
                        });
                }
            });
            
            if(addr.latitude && addr.longitude) {
                setMarker(addr.latitude, addr.longitude);
                map.setView(new L.LatLng(addr.latitude, addr.longitude), 15);
            }

            setTimeout(() => { map.invalidateSize(); }, 500);
            $('html, body').animate({ scrollTop: $("#form-alamat-section").offset().top - 100 }, 500);
        }
    });

    $('#btnTambahAlamat').on('click', function() {
        $('#alamatForm')[0].reset(); 
        $('#address_id').val(''); 
        $('#kota, #kecamatan').prop('disabled', true);
        
        loadProvinces(); 
        
        $('#checkbox-utama-container').show();
        $('#is_primary').prop('checked', false);
        $('#form-address-title').text('Tambah Alamat Baru');
        $('#form-alamat-section').slideDown();
        
        setMarker(defaultLat, defaultLng);
        map.setView([defaultLat, defaultLng], 14);
        
        setTimeout(() => { map.invalidateSize(); }, 500);
        $('html, body').animate({ scrollTop: $("#form-alamat-section").offset().top - 100 }, 500);
    });

    $('#btnBatal').on('click', function() { $('#form-alamat-section').slideUp(); });

    $('#alamatForm').on('submit', function(e) {
        e.preventDefault(); 
        
        if(!provincesData.find(p => p.name === $('#provinsi').val())) {
            alert('Silakan pilih Provinsi dari daftar yang muncul saat mengetik.'); return;
        }
        
        submitAjax({
            address_id: $('#address_id').val(),
            label: $('input[name="label"]').val(),
            province: $('#provinsi').val(),
            city: $('#kota').val(),
            district: $('#kecamatan').val(),
            postal_code: $('input[name="postal_code"]').val(),
            street_address: $('textarea[name="street_address"]').val(),
            landmark: $('input[name="landmark"]').val(),
            latitude: $('#latitude').val(),
            longitude: $('#longitude').val(),
            is_primary: $('#is_primary').is(':checked') ? 1 : 0
        });
    });

    loadAlamatData();
});
</script>
@endpush