@extends('admin.admin_master')
@section('header_title', 'Profil Perusahaan')
@section('css')
<!-- Leaflet & Geocoder CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
    /* Styling khusus Claymorphism Form Elements */
    .clay-input {
        background: #FDFBFF;
        box-shadow: inset 0 3px 6px rgba(147,104,183,0.1), inset 0 -2px 4px rgba(255,255,255,0.8);
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }
    .clay-input:focus {
        background: #FFFFFF;
        box-shadow: inset 0 2px 4px rgba(147,104,183,0.05), 0 0 0 3px rgba(147,104,183,0.2);
        outline: none;
    }
    .clay-card {
        background: #F5F2F8;
        box-shadow: inset 0 -4px 8px rgba(147,104,183,0.2), inset 0 4px 8px rgba(255,255,255,1), 0 15px 30px rgba(147,104,183,0.15);
        border: 1px solid rgba(255,255,255,0.8);
    }
</style>
@endsection

@section('content')
<div class="max-w-5xl mx-auto pb-10">
    <div id="alert-profil" class="hidden p-4 mb-6 rounded-2xl font-bold text-sm shadow-sm border"></div>

    <form id="form-profil" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Bagean 1: Informasi Dasar -->
        <div class="clay-card p-6 md:p-8 rounded-[2.5rem] relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-admin-sideBg"></div>
            <h3 class="text-xl font-black text-admin-textMain mb-6 flex items-center gap-2">
                <i class="ph ph-buildings text-3xl text-admin-sideBg drop-shadow-sm"></i>
                Informasi Dasar
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Nama Usaha</label>
                    <input type="text" name="company_name" id="company_name" required class="clay-input w-full p-3.5 rounded-2xl text-sm font-bold text-admin-textMain">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Tahun Berdiri</label>
                    <input type="number" name="established_year" id="established_year" required class="clay-input w-full p-3.5 rounded-2xl text-sm font-bold text-admin-textMain">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Foto Sampul / Dapur</label>
                    <div class="relative flex items-center w-full p-2 border-2 border-dashed border-admin-accent rounded-2xl bg-white/50 hover:bg-white transition-colors">
                        <input type="file" name="image_url" id="image_url" accept="image/*" class="w-full text-sm text-admin-textMuted file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-black file:bg-admin-navBg file:text-admin-sideBg hover:file:bg-admin-accent cursor-pointer transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagean 2: Kontak & Sosial Media -->
        <div class="clay-card p-6 md:p-8 rounded-[2.5rem] relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-blue-400"></div>
            <h3 class="text-xl font-black text-admin-textMain mb-6 flex items-center gap-2">
                <i class="ph ph-address-book text-3xl text-blue-500 drop-shadow-sm"></i>
                Kontak & Sosial Media
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Nomor WhatsApp</label>
                    <div class="relative">
                        <i class="ph ph-whatsapp-logo absolute left-4 top-1/2 -translate-y-1/2 text-admin-textMuted text-xl"></i>
                        <input type="text" name="whatsapp_number" id="whatsapp_number" required class="clay-input w-full pl-12 p-3.5 rounded-2xl text-sm font-bold text-admin-textMain">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Email Perusahaan</label>
                    <div class="relative">
                        <i class="ph ph-envelope-simple absolute left-4 top-1/2 -translate-y-1/2 text-admin-textMuted text-xl"></i>
                        <input type="email" name="email_address" id="email_address" required class="clay-input w-full pl-12 p-3.5 rounded-2xl text-sm font-bold text-admin-textMain">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Instagram Handle</label>
                    <div class="relative">
                        <i class="ph ph-instagram-logo absolute left-4 top-1/2 -translate-y-1/2 text-admin-textMuted text-xl"></i>
                        <input type="text" name="instagram_handle" id="instagram_handle" placeholder="@rayakitchen.id" class="clay-input w-full pl-12 p-3.5 rounded-2xl text-sm font-bold text-admin-textMain">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagean 3: Deskripsi & Lokasi (MAPS) -->
        <div class="clay-card p-6 md:p-8 rounded-[2.5rem] relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-pink-400"></div>
            <h3 class="text-xl font-black text-admin-textMain mb-6 flex items-center gap-2">
                <i class="ph ph-map-pin-line text-3xl text-pink-500 drop-shadow-sm"></i>
                Deskripsi & Pengaturan Lokasi
            </h3>

            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Deskripsi Singkat (Header)</label>
                    <textarea name="short_description" id="short_description" rows="2" class="clay-input w-full p-4 rounded-2xl text-sm font-bold text-admin-textMain resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Sejarah / Background History</label>
                    <textarea name="background_history" id="background_history" rows="4" class="clay-input w-full p-4 rounded-2xl text-sm font-bold text-admin-textMain resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Alamat Lengkap (Jalan, RT/RW, Kota)</label>
                    <textarea name="address" id="address" rows="2" class="clay-input w-full p-4 rounded-2xl text-sm font-bold text-admin-textMain resize-none"></textarea>
                </div>

                <!-- GOOGLE MAPS LINK CONVERTER -->
                <div class="p-5 bg-white/50 border border-white rounded-[2rem] shadow-sm">
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Link Google Maps (Share URL)</label>
                    <input type="text" id="map_url_input" placeholder="Paste link Google Maps dari HP (Cth: https://maps.app.goo.gl/...)" class="clay-input w-full p-3.5 rounded-2xl text-sm font-bold text-admin-textMain mb-4">

                    <!-- Hidden inputs untuk dikirim ke backend -->
                    <input type="hidden" name="map_external_url" id="map_external_url">
                    <input type="hidden" name="map_embed_url" id="map_embed_url">

                    <!-- Preview Iframe -->
                    <p class="text-xs font-bold text-admin-textMuted mb-2">Live Preview Widget Footer:</p>
                    <div id="map-preview" class="w-full h-48 rounded-2xl overflow-hidden bg-gray-200 border-2 border-dashed border-gray-300 flex items-center justify-center text-sm font-bold text-gray-400">
                        Preview Peta Belum Tersedia
                    </div>
                </div>

                <!-- LEAFLET MAPS (Penentuan Jarak Ongkir) -->
                <div class="p-5 bg-white/50 border border-white rounded-[2rem] shadow-sm">
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Pusat Acuan Ongkir (Koordinat Dapur)</label>
                    <p class="text-[11px] font-medium text-admin-textMuted mb-3 leading-relaxed">Geser pin merah di peta atau gunakan kotak pencarian untuk menetapkan titik akurat Dapur Raya Kitchen. Titik ini wajib ada agar sistem API kurir bisa menghitung ongkos kirim ke rumah pelanggan.</p>

                    <div id="admin-map" class="w-full h-[350px] rounded-2xl border-2 border-white shadow-[0_4px_15px_rgba(147,104,183,0.15)] z-10 mb-4"></div>

                    <!-- Dynamic Lat Lng Inputs -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5">Latitude</label>
                            <input type="text" name="latitude" id="latitude" required class="clay-input w-full p-3 rounded-xl text-sm font-bold text-admin-textMain text-center" placeholder="-6.1234567">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5">Longitude</label>
                            <input type="text" name="longitude" id="longitude" required class="clay-input w-full p-3 rounded-xl text-sm font-bold text-admin-textMain text-center" placeholder="106.1234567">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="pt-4">
            <button type="submit" id="btn-submit-profil" class="w-full py-4 bg-admin-sideBg text-white font-black rounded-[1.5rem] hover:bg-admin-sideHover shadow-[0_8px_20px_rgba(147,104,183,0.3)] hover:-translate-y-1 active:translate-y-0.5 transition-all duration-300 text-base flex items-center justify-center gap-2">
                <i class="ph ph-floppy-disk text-xl"></i> Simpan Profil Perusahaan
            </button>
        </div>
    </form>
</div>
@endsection

@push('script')
<!-- Leaflet & Geocoder JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
$(document).ready(function() {
    // --- 1. DUMMY DATA INISIALISASI ---
    // Dipakai buat nge-render halaman karena API GET belum ada controllernya
    const dummyProfilData = {
        company_name: "Raya Kitchen",
        established_year: 2020,
        whatsapp_number: "6281234567890",
        email_address: "hello@rayakitchen.com",
        instagram_handle: "@rayakitchen.id",
        short_description: "Menghadirkan roti dan kue kering fresh dari oven setiap harinya.",
        background_history: "Berawal dari kecintaan terhadap pastry rumahan...",
        address: "Perum BDP, Jl. Husein Sastranegara Blok BE No. 26",
        map_external_url: "https://maps.app.goo.gl/dummy",
        map_embed_url: "<iframe src='https://maps.google.com/maps?q=Raya+Kitchen+Bekasi&output=embed' width='100%' height='100%' style='border:0;' loading='lazy'></iframe>",
        latitude: -6.3262657,
        longitude: 106.9419131
    };

    // --- 2. LOAD DATA KE DALAM FORM ---
    $('#company_name').val(dummyProfilData.company_name);
    $('#established_year').val(dummyProfilData.established_year);
    $('#whatsapp_number').val(dummyProfilData.whatsapp_number);
    $('#email_address').val(dummyProfilData.email_address);
    $('#instagram_handle').val(dummyProfilData.instagram_handle);
    $('#short_description').val(dummyProfilData.short_description);
    $('#background_history').val(dummyProfilData.background_history);
    $('#address').val(dummyProfilData.address);
    $('#map_external_url').val(dummyProfilData.map_external_url);
    $('#map_embed_url').val(dummyProfilData.map_embed_url);

    // Tampilkan preview awal jika ada
    if(dummyProfilData.map_embed_url) {
        $('#map_url_input').val(dummyProfilData.map_external_url);
        $('#map-preview').html(dummyProfilData.map_embed_url);
    }

    // --- 3. INISIALISASI LEAFLET MAP ---
    let adminMap, adminMarker;
    let defaultLat = dummyProfilData.latitude || -6.200000;
    let defaultLng = dummyProfilData.longitude || 106.816666;

    function initAdminMap(lat, lng) {
        if (!adminMap) {
            adminMap = L.map('admin-map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(adminMap);
            adminMarker = L.marker([lat, lng], {draggable: true}).addTo(adminMap);

            // Update input pas pin digeser
            adminMarker.on('dragend', function (e) {
                let pos = adminMarker.getLatLng();
                $('#latitude').val(pos.lat.toFixed(7));
                $('#longitude').val(pos.lng.toFixed(7));
            });

            // Update pin pas peta di-klik
            adminMap.on('click', function(e) {
                adminMarker.setLatLng(e.latlng);
                $('#latitude').val(e.latlng.lat.toFixed(7));
                $('#longitude').val(e.latlng.lng.toFixed(7));
            });

            // Fitur Pencarian (Geocoder)
            L.Control.geocoder({
                defaultMarkGeocoder: false,
                placeholder: 'Cari wilayah dapur...',
                geocoder: L.Control.Geocoder.nominatim({
                    geocodingQueryParams: { countrycodes: 'id', addressdetails: 1, limit: 5 }
                })
            }).on('markgeocode', function(e) {
                let center = e.geocode.center;
                adminMap.setView(center, 16);
                adminMarker.setLatLng(center);
                $('#latitude').val(center.lat.toFixed(7));
                $('#longitude').val(center.lng.toFixed(7));
            }).addTo(adminMap);
        } else {
            adminMarker.setLatLng([lat, lng]);
            adminMap.setView([lat, lng], 15);
        }
        $('#latitude').val(lat);
        $('#longitude').val(lng);
    }

    // Panggil peta
    initAdminMap(defaultLat, defaultLng);

    // Update peta pas admin ngetik koordinat manual di Input
    $('#latitude, #longitude').on('input', function() {
        let lat = parseFloat($('#latitude').val());
        let lng = parseFloat($('#longitude').val());
        if(!isNaN(lat) && !isNaN(lng)) {
            adminMarker.setLatLng([lat, lng]);
            adminMap.setView([lat, lng], adminMap.getZoom());
        }
    });

    // --- 4. CONVERTER GOOGLE MAPS LINK KE IFRAME ---
    $('#map_url_input').on('input', function() {
        let val = $(this).val();
        $('#map_external_url').val(val); // Simpan link asli ke DB

        if(val.trim() !== '') {
            // Logika Converter URL ke Embed
            let embedSrc = `https://maps.google.com/maps?q=${encodeURIComponent(val)}&t=&z=15&ie=UTF8&iwloc=&output=embed`;

            // Ekstrak kalau linknya mengandung koordinat (@lat,lng)
            let coordMatch = val.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
            if (coordMatch) {
                embedSrc = `https://maps.google.com/maps?q=${coordMatch[1]},${coordMatch[2]}&z=15&output=embed`;
            }

            let iframeTag = `<iframe src="${embedSrc}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>`;

            $('#map_embed_url').val(iframeTag);
            $('#map-preview').html(iframeTag).removeClass('border-dashed border-gray-300 text-gray-400 bg-gray-200');
        } else {
            $('#map_embed_url').val('');
            $('#map-preview').html('Preview Peta Belum Tersedia').addClass('border-dashed border-gray-300 text-gray-400 bg-gray-200');
        }
    });

    // --- 5. DUMMY FORM SUBMISSION ---
    $('#form-profil').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let btn = $('#btn-submit-profil');

        btn.html('<i class="ph ph-spinner animate-spin text-xl"></i> Menyimpan...').prop('disabled', true);

        // Simulasi Loading 1 Detik
        setTimeout(function() {
            // Ngeskrip hasil FormData ke console buat diintip backend
            console.log("=== Data Siap Kirim ke API ===");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $('#alert-profil').removeClass('hidden bg-red-50 border-red-200 text-red-600')
                .addClass('bg-green-50 border-green-200 text-green-600')
                .html('<i class="ph ph-check-circle mr-1"></i> Data profil, lokasi, dan setting peta berhasil di-generate secara DUMMY. Cek Console F12.');

            window.scrollTo({ top: 0, behavior: 'smooth' });
            btn.html('<i class="ph ph-floppy-disk text-xl"></i> Simpan Profil Perusahaan').prop('disabled', false);
        }, 1000);
    });
});
</script>
@endpush
