@extends('pembeli.pembeli_master')
@section('title', 'Kelola Alamat - Raya Kitchen')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto p-6 md:p-8 bg-buyer-bg rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1)] border border-white flex flex-col justify-center relative mb-6">

        <!-- Header & Navigasi -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 border-b border-white pb-6">
            <div>
                <a href="{{ route('profil') }}" class="text-buyer-primary font-black text-xs hover:text-buyer-hover hover:-translate-x-1 transition-transform inline-flex items-center gap-1.5 mb-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Profil
                </a>
                <h1 class="text-buyer-textPrimary drop-shadow-sm text-2xl md:text-3xl font-black mb-1">Daftar Alamat Pengiriman</h1>
                <p class="font-bold text-sm text-buyer-textSecondary">Kelola dan tentukan titik koordinat alamat pengirimanmu.</p>
            </div>
            <button type="button" id="btnTambahAlamat" class="w-full sm:w-auto py-3 px-5 bg-buyer-primary text-white font-black text-sm rounded-2xl hover:-translate-y-1 active:translate-y-0 shadow-[0_4px_10px_rgba(192,153,206,0.3)] transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                Tambah Alamat
            </button>
        </div>

        <!-- Alert Sukses -->
        <div id="alert-success" class="hidden mb-6 p-4 bg-green-50 text-green-600 border border-green-100 rounded-2xl text-sm font-black shadow-sm items-center gap-3 transition-all">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span id="alert-msg"></span>
        </div>

        <!-- Container List Alamat -->
        <div id="list-alamat-container" class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6 mb-8">
            <p class="text-sm text-buyer-textSecondary font-bold italic col-span-full text-center py-10 animate-pulse">Memuat alamat...</p>
        </div>

        <!-- Form Kelola Alamat -->
        <div id="form-alamat-section" class="hidden border-t-2 border-white pt-8 mt-4 origin-top transition-all">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-black text-buyer-textPrimary drop-shadow-sm flex items-center gap-2" id="form-address-title">
                    <svg class="w-6 h-6 text-buyer-primary" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Tambah Alamat Baru
                </h2>
                <button type="button" id="btnBatal" class="text-red-500 font-black text-sm hover:text-red-600 transition flex items-center gap-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Batal
                </button>
            </div>

            <form id="alamatForm" class="flex flex-col gap-6">
                @csrf
                <input type="hidden" name="address_id" id="address_id" value="">

                <!-- GRID MAPS & INPUTS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

                    <!-- Sisi Kiri: Peta -->
                    <div class="w-full flex flex-col gap-3 order-1">
                        <div class="bg-white/50 p-4 rounded-[2rem] border border-white shadow-sm">
                            <label class="block text-xs font-black text-buyer-textMuted uppercase tracking-wider mb-2 text-center">Tandai Lokasi Tujuan</label>
                            <p class="text-[11px] text-buyer-textSecondary font-medium leading-relaxed mb-3 text-center">
                                Geser pin atau gunakan kolom cari peta. Sistem otomatis mencari Provinsi & Kota dari API.
                            </p>
                            <div id="map" class="w-full h-[250px] md:h-[350px] rounded-2xl border-2 border-white shadow-[0_4px_15px_rgba(192,153,206,0.2)] z-10 overflow-hidden"></div>

                            <div id="map-loading-indicator" class="hidden mt-3 items-center justify-center gap-2 text-[11px] font-black text-buyer-primary animate-pulse">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Sedang menarik data Emsifa...
                            </div>
                        </div>

                        <!-- Info Koordinat -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/40 p-2.5 rounded-xl border border-white text-center">
                                <p class="text-[9px] font-black text-buyer-textMuted uppercase">Latitude</p>
                                <input type="text" name="latitude" id="latitude" required readonly class="w-full bg-transparent text-xs font-black text-buyer-textPrimary text-center outline-none pointer-events-none">
                            </div>
                            <div class="bg-white/40 p-2.5 rounded-xl border border-white text-center">
                                <p class="text-[9px] font-black text-buyer-textMuted uppercase">Longitude</p>
                                <input type="text" name="longitude" id="longitude" required readonly class="w-full bg-transparent text-xs font-black text-buyer-textPrimary text-center outline-none pointer-events-none">
                            </div>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Form Input -->
                    <div class="w-full flex flex-col gap-4 order-2">
                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-buyer-textMuted mb-1.5 ml-2">Label Alamat</label>
                            <input type="text" name="label" required class="w-full p-3.5 text-sm font-bold rounded-2xl bg-white border-2 border-white focus:border-buyer-primary focus:ring-4 focus:ring-buyer-primary/10 outline-none transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] text-buyer-textPrimary" placeholder="Cth: Rumah / Kantor / Kosan">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="relative">
                                <label class="block text-[11px] font-black uppercase tracking-wider text-buyer-textMuted mb-1.5 ml-2">Provinsi</label>
                                <input type="text" id="provinsi" name="province" required autocomplete="off" list="list-provinsi" class="w-full p-3.5 text-sm font-bold rounded-2xl bg-white border-2 border-white focus:border-buyer-primary focus:ring-4 focus:ring-buyer-primary/10 outline-none transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] text-buyer-textPrimary" placeholder="Pilih provinsi...">
                                <datalist id="list-provinsi"></datalist>
                            </div>

                            <div class="relative">
                                <label class="block text-[11px] font-black uppercase tracking-wider text-buyer-textMuted mb-1.5 ml-2">Kota / Kabupaten</label>
                                <input type="text" id="kota" name="city" required autocomplete="off" list="list-kota" disabled class="w-full p-3.5 text-sm font-bold rounded-2xl bg-white border-2 border-white focus:border-buyer-primary outline-none transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] text-buyer-textPrimary disabled:opacity-50 disabled:cursor-not-allowed" placeholder="Pilih kota...">
                                <datalist id="list-kota"></datalist>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="relative">
                                <label class="block text-[11px] font-black uppercase tracking-wider text-buyer-textMuted mb-1.5 ml-2">Kecamatan</label>
                                <input type="text" id="kecamatan" name="district" required autocomplete="off" list="list-kecamatan" disabled class="w-full p-3.5 text-sm font-bold rounded-2xl bg-white border-2 border-white focus:border-buyer-primary outline-none transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] text-buyer-textPrimary disabled:opacity-50 disabled:cursor-not-allowed" placeholder="Pilih kecamatan...">
                                <datalist id="list-kecamatan"></datalist>
                            </div>

                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-buyer-textMuted mb-1.5 ml-2">Kode Pos</label>
                                <input type="text" name="postal_code" id="postal_code" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full p-3.5 text-sm font-bold rounded-2xl bg-white border-2 border-white focus:border-buyer-primary focus:ring-4 focus:ring-buyer-primary/10 outline-none transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] text-buyer-textPrimary" placeholder="5 digit angka">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-buyer-textMuted mb-1.5 ml-2">Alamat Jalan / Detail</label>
                            <textarea name="street_address" id="street_address" rows="2" required class="w-full p-3.5 text-sm font-bold rounded-2xl bg-white border-2 border-white focus:border-buyer-primary focus:ring-4 focus:ring-buyer-primary/10 outline-none transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] text-buyer-textPrimary resize-none" placeholder="Cth: Jl. Merdeka No 45, RT 01/RW 02"></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase tracking-wider text-buyer-textMuted mb-1.5 ml-2">Patokan Lokasi (Opsional)</label>
                            <input type="text" name="landmark" class="w-full p-3.5 text-sm font-bold rounded-2xl bg-white border-2 border-white focus:border-buyer-primary focus:ring-4 focus:ring-buyer-primary/10 outline-none transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] text-buyer-textPrimary" placeholder="Cth: Rumah pagar hitam depan masjid">
                        </div>

                        <!-- Opsi Utama -->
                        <div class="flex items-center gap-3 mt-2 bg-white/60 p-4 rounded-2xl border border-white cursor-pointer hover:bg-white transition-colors group" id="checkbox-utama-container" onclick="document.getElementById('is_primary').click()">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="is_primary" id="is_primary" value="1" class="peer w-5 h-5 appearance-none rounded-md border-2 border-buyer-primary bg-white checked:bg-buyer-primary checked:border-buyer-primary transition-all cursor-pointer">
                                <svg class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <label class="text-xs font-black text-buyer-textPrimary cursor-pointer group-hover:text-buyer-primary transition-colors">Jadikan sebagai Alamat Utama</label>
                        </div>

                        <button type="submit" id="btnSubmit" class="w-full py-4 mt-2 bg-buyer-primary text-white font-black text-sm rounded-[1.25rem] hover:-translate-y-1 active:translate-y-0.5 shadow-[0_6px_15px_rgba(192,153,206,0.4)] transition-all flex items-center justify-center gap-2">
                            <i class="ph ph-floppy-disk text-lg"></i> Simpan Alamat
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<!-- SweetAlert2 & Leaflet -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

@push('script')
<script>
$(document).ready(function() {
    let globalAddresses = [];
    let provincesData = [];
    let citiesData = [];
    let districtsData = [];

    // --- INISIALISASI MAP & MARKER ---
    let defaultLat = -6.3473242;
    let defaultLng = 106.9481003;
    let map = L.map('map', { scrollWheelZoom: false }).setView([defaultLat, defaultLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    let marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);

    function updateLokasi(lat, lng) {
        $('#latitude').val(lat.toFixed(7));
        $('#longitude').val(lng.toFixed(7));
        reverseGeocode(lat, lng);
    }

    marker.on('dragend', function () {
        let pos = marker.getLatLng();
        updateLokasi(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateLokasi(e.latlng.lat, e.latlng.lng);
    });

    L.Control.geocoder({
        defaultMarkGeocoder: false,
        placeholder: 'Ketik nama jalan/daerah...',
        geocoder: L.Control.Geocoder.nominatim({
            geocodingQueryParams: { countrycodes: 'id', addressdetails: 1, limit: 5 }
        })
    }).on('markgeocode', function(e) {
        let center = e.geocode.center;
        map.setView(center, 16);
        marker.setLatLng(center);
        updateLokasi(center.lat, center.lng);
    }).addTo(map);

    // --- LOGIKA FETCH DATA EMSIFA ---
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

    // --- LOGIKA REVERSE GEOCODING TERINTEGRASI EMSIFA ---
    async function reverseGeocode(lat, lng) {
        $('#map-loading-indicator').removeClass('hidden').addClass('flex');

        let url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1`;

        try {
            let response = await fetch(url);
            let data = await response.json();

            if(data && data.address) {
                let addr = data.address;
                let rawProv = addr.state || addr.region || '';
                let rawCity = addr.city || addr.town || addr.county || addr.municipality || '';
                let rawDist = addr.suburb || addr.village || addr.neighbourhood || addr.residential || '';
                let postCode = addr.postcode || '';
                let street = addr.road || '';
                let houseNumber = addr.house_number ? ' No. ' + addr.house_number : '';

                if(postCode) $('#postal_code').val(postCode);
                if(street) $('#street_address').val(street + houseNumber);

                await syncWithEmsifa(rawProv, rawCity, rawDist);
            }
        } catch (err) {
            console.warn('Gagal Reverse Geocoding', err);
        } finally {
            $('#map-loading-indicator').removeClass('flex').addClass('hidden');
        }
    }

    // Fungsi canggih nyocokin data string OSM ke ID API Emsifa
    async function syncWithEmsifa(rawProv, rawCity, rawDist) {
        await loadProvinces();
        if(!rawProv) return;

        // 1. Cocokkan Provinsi
        let cleanProv = rawProv.toLowerCase().replace(/(provinsi|daerah khusus|ibukota)/g, '').trim();
        let matchProv = provincesData.find(p => p.name.toLowerCase().includes(cleanProv) || cleanProv.includes(p.name.toLowerCase()));

        if(matchProv) {
            $('#provinsi').val(matchProv.name);
            $('#kota').prop('disabled', false).attr('placeholder', 'Memuat kota...');
            $('#kecamatan').val('').prop('disabled', true).attr('placeholder', 'Pilih kecamatan...');

            // 2. Fetch & Cocokkan Kota
            try {
                let resCity = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${matchProv.id}.json`);
                let cities = await resCity.json();
                citiesData = cities;

                let optCity = '';
                cities.forEach(c => optCity += `<option value="${c.name}">`);
                $('#list-kota').html(optCity);

                if(!rawCity) { $('#kota').val('').attr('placeholder', 'Pilih kota...'); return; }

                let cleanCity = rawCity.toLowerCase().replace(/(kota|kabupaten|kab\.|kab )/g, '').trim();
                let matchCity = cities.find(c => c.name.toLowerCase().replace(/(kota|kabupaten)/g, '').trim() === cleanCity || c.name.toLowerCase().includes(cleanCity));

                if(matchCity) {
                    $('#kota').val(matchCity.name);
                    $('#kecamatan').prop('disabled', false).attr('placeholder', 'Memuat kecamatan...');

                    // 3. Fetch & Cocokkan Kecamatan
                    let resDist = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${matchCity.id}.json`);
                    let districts = await resDist.json();
                    districtsData = districts;

                    let optDist = '';
                    districts.forEach(d => optDist += `<option value="${d.name}">`);
                    $('#list-kecamatan').html(optDist);

                    if(!rawDist) { $('#kecamatan').val('').attr('placeholder', 'Pilih kecamatan...'); return; }

                    let cleanDist = rawDist.toLowerCase().replace(/(kecamatan|kec\.)/g, '').trim();
                    let matchDist = districts.find(d => d.name.toLowerCase().replace(/(kecamatan|kec)/g, '').trim() === cleanDist || d.name.toLowerCase().includes(cleanDist));

                    if(matchDist) $('#kecamatan').val(matchDist.name);
                    else $('#kecamatan').val('').attr('placeholder', 'Pilih kecamatan...');

                } else {
                    $('#kota').val('').attr('placeholder', 'Pilih kota...');
                }
            } catch (err) {
                console.error("Gagal sinkronisasi wilayah", err);
            }
        }
    }

    // --- KONTROL MANUAL INPUT WILAYAH (Fallback) ---
    $('#provinsi').on('change input', function() {
        let selectedProv = provincesData.find(p => p.name === $(this).val());
        $('#kota, #kecamatan').val('').prop('disabled', true);
        if (selectedProv) {
            $('#kota').attr('placeholder', 'Memuat kota...').prop('disabled', false);
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProv.id}.json`)
                .then(res => res.json()).then(data => {
                    citiesData = data; let opts = ''; data.forEach(c => opts += `<option value="${c.name}">`);
                    $('#list-kota').html(opts); $('#kota').attr('placeholder', 'Pilih kota...');
                });
        }
    });

    $('#kota').on('change input', function() {
        let selectedCity = citiesData.find(c => c.name === $(this).val());
        $('#kecamatan').val('').prop('disabled', true);
        if (selectedCity) {
            $('#kecamatan').attr('placeholder', 'Memuat kecamatan...').prop('disabled', false);
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedCity.id}.json`)
                .then(res => res.json()).then(data => {
                    districtsData = data; let opts = ''; data.forEach(d => opts += `<option value="${d.name}">`);
                    $('#list-kecamatan').html(opts); $('#kecamatan').attr('placeholder', 'Pilih kecamatan...');
                });
        }
    });

    // --- CRUD ALAMAT LIST ---
    function loadAlamatData() {
        $.ajax({
            url: '/api/pembeli/profil-data',
            type: 'GET',
            success: function(res) {
                if(res.status === 'success') {
                    globalAddresses = res.data.all_addresses || [];
                    renderListAlamat(globalAddresses);
                }
            }
        });
    }

    function renderListAlamat(addresses) {
        let container = $('#list-alamat-container');
        container.empty();
        if(addresses.length === 0) {
            container.append(`
                <div class="col-span-full p-10 text-center bg-white/50 border-2 border-dashed border-white rounded-[2rem]">
                    <p class="text-sm text-buyer-textMuted font-black uppercase">Belum ada alamat tersimpan.</p>
                </div>
            `); return;
        }

        addresses.forEach(addr => {
            let isUtama = addr.is_primary == 1;
            let badge = isUtama ? '<span class="bg-buyer-primary text-white text-[10px] px-2.5 py-1 rounded-full font-black uppercase shadow-sm">Utama</span>' : '';
            let btnUtama = !isUtama ? `<button type="button" class="btn-jadikan-utama flex-1 md:flex-none px-3 py-1.5 text-xs font-black text-gray-400 bg-white border border-gray-100 rounded-xl hover:text-buyer-primary transition-all" data-id="${addr.id}">Jadikan Utama</button>` : '';

            container.append(`
                <div class="p-5 ${isUtama ? 'bg-white shadow-[0_8px_20px_rgba(192,153,206,0.15)] border-2 border-white scale-[1.02]' : 'bg-white/50 border border-white hover:bg-white shadow-sm'} rounded-[2rem] flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="font-black text-buyer-textPrimary uppercase">${addr.label || 'Alamat'}</h3>
                            ${badge}
                        </div>
                        <div class="text-xs text-buyer-textSecondary space-y-2 mb-4">
                            <p class="font-black text-buyer-textPrimary">${addr.recipient_name} <span class="font-medium">(${addr.recipient_whatsapp})</span></p>
                            <p class="font-bold line-clamp-2">${addr.street_address}</p>
                            <p class="font-medium">${addr.district}, ${addr.city}, ${addr.province}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-auto pt-4 border-t border-gray-100">
                        ${btnUtama}
                        <button type="button" class="btn-edit-alamat flex-1 px-4 py-2 bg-blue-50 text-blue-600 font-black text-xs rounded-xl hover:bg-blue-600 hover:text-white transition-colors" data-id="${addr.id}">Edit</button>
                        <button type="button" class="btn-hapus-alamat flex-1 px-4 py-2 bg-red-50 text-red-600 font-black text-xs rounded-xl hover:bg-red-600 hover:text-white transition-colors" data-id="${addr.id}">Hapus</button>
                    </div>
                </div>
            `);
        });
    }

    // FORM SUBMIT DAN TOMBOL AKSI CRUD LAINNYA TETAP SAMA SEPERTI SEBELUMNYA
    $('#btnTambahAlamat').on('click', function() {
        $('#alamatForm')[0].reset();
        $('#address_id').val('');
        $('#kota, #kecamatan').prop('disabled', true);
        loadProvinces();

        $('#form-address-title').html('Tambah Alamat Baru');
        $(this).hide();
        $('#form-alamat-section').slideDown(400);

        marker.setLatLng([defaultLat, defaultLng]);
        map.setView([defaultLat, defaultLng], 14);
        $('#latitude').val(defaultLat);
        $('#longitude').val(defaultLng);

        setTimeout(() => { map.invalidateSize(); }, 400);
        $('html, body').animate({ scrollTop: $("#form-alamat-section").offset().top - 80 }, 500);
    });

    $('#btnBatal').on('click', function() {
        $('#form-alamat-section').slideUp(300);
        $('#btnTambahAlamat').fadeIn(300);
    });

    $('#alamatForm').on('submit', function(e) {
        e.preventDefault();
        if(!provincesData.find(p => p.name === $('#provinsi').val())) {
            Swal.fire({ title: "Provinsi Belum Tepat", text: "Pilih nama provinsi dari opsi otomatis.", icon: "info" });
            return;
        }

        let btn = $('#btnSubmit'); let og = btn.html();
        btn.html('Menyimpan...').prop('disabled', true);

        $.ajax({
            url: '/api/pembeli/profil-data', type: 'POST',
            data: JSON.stringify({
                address_id: $('#address_id').val(), label: $('input[name="label"]').val(), province: $('#provinsi').val(),
                city: $('#kota').val(), district: $('#kecamatan').val(), postal_code: $('#postal_code').val(),
                street_address: $('#street_address').val(), landmark: $('input[name="landmark"]').val(),
                latitude: $('#latitude').val(), longitude: $('#longitude').val(), is_primary: $('#is_primary').is(':checked') ? 1 : 0
            }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                $('#alert-msg').text(res.message); $('#alert-success').removeClass('hidden').hide().fadeIn();
                setTimeout(() => $('#alert-success').fadeOut(), 4000);
                $('#form-alamat-section').slideUp(300); $('#btnTambahAlamat').show();
                loadAlamatData();
            },
            complete: function() { btn.html(og).prop('disabled', false); }
        });
    });

    // Edit & Hapus & Set Utama Binding (Menggunakan Swal2)
    $(document).on('click', '.btn-hapus-alamat', function() {
        let id = $(this).data('id');
        Swal.fire({ title: "Hapus Alamat?", text: "Akan dihapus permanen.", icon: "warning", showCancelButton: true, confirmButtonText: "Ya, Hapus" }).then((res) => {
            if(res.isConfirmed) {
                $.ajax({
                    url: `/api/pembeli/alamat/${id}`, type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() { loadAlamatData(); }
                });
            }
        });
    });

    $(document).on('click', '.btn-jadikan-utama', function() {
        let addr = globalAddresses.find(item => item.id == $(this).data('id'));
        if(addr) {
            $.ajax({
                url: '/api/pembeli/profil-data', type: 'POST',
                data: JSON.stringify({...addr, address_id: addr.id, is_primary: 1}),
                contentType: 'application/json', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function() { loadAlamatData(); }
            });
        }
    });

    $(document).on('click', '.btn-edit-alamat', function() {
        let addr = globalAddresses.find(item => item.id == $(this).data('id'));
        if(addr) {
            $('#address_id').val(addr.id); $('input[name="label"]').val(addr.label);
            $('#postal_code').val(addr.postal_code); $('#street_address').val(addr.street_address);
            $('input[name="landmark"]').val(addr.landmark);
            $('#is_primary').prop('checked', addr.is_primary == 1);

            $('#btnTambahAlamat').hide(); $('#form-alamat-section').slideDown(400);

            loadProvinces().then(() => {
                $('#provinsi').val(addr.province);
                let p = provincesData.find(p => p.name === addr.province);
                if (p) {
                    $('#kota').prop('disabled', false);
                    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${p.id}.json`).then(r=>r.json()).then(cities => {
                        citiesData = cities; let opts = ''; cities.forEach(c => opts += `<option value="${c.name}">`);
                        $('#list-kota').html(opts); $('#kota').val(addr.city);

                        let c = cities.find(c => c.name === addr.city);
                        if (c) {
                            $('#kecamatan').prop('disabled', false);
                            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${c.id}.json`).then(r=>r.json()).then(districts => {
                                districtsData = districts; let dOpts = ''; districts.forEach(d => dOpts += `<option value="${d.name}">`);
                                $('#list-kecamatan').html(dOpts); $('#kecamatan').val(addr.district);
                            });
                        }
                    });
                }
            });

            if(addr.latitude && addr.longitude) {
                marker.setLatLng([addr.latitude, addr.longitude]);
                map.setView([addr.latitude, addr.longitude], 15);
                $('#latitude').val(addr.latitude); $('#longitude').val(addr.longitude);
            }
            setTimeout(() => { map.invalidateSize(); }, 400);
            $('html, body').animate({ scrollTop: $("#form-alamat-section").offset().top - 80 }, 500);
        }
    });

    loadAlamatData();
});
</script>
@endpush
