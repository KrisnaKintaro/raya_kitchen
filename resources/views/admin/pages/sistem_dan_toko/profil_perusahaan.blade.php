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

        <!-- Bagian 1: Informasi Dasar -->
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
                    <div id="preview-cover-wrapper" class="hidden mb-3">
                        <img id="preview-cover-img" src="" alt="Foto Sampul Dapur" class="h-40 w-full md:w-72 object-cover rounded-2xl border-2 border-white shadow-sm">
                    </div>
                    <div class="relative flex items-center w-full p-2 border-2 border-dashed border-admin-accent rounded-2xl bg-white/50 hover:bg-white transition-colors">
                        <input type="file" name="image_url" id="image_url" accept="image/*" class="w-full text-sm text-admin-textMuted file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-black file:bg-admin-navBg file:text-admin-sideBg hover:file:bg-admin-accent cursor-pointer transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Kontak & Sosial Media -->
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

        <!-- Bagian 3: Deskripsi & Lokasi (LEAFLET MAPS) -->
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
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Alamat Lengkap (Jalan, RT/RW, Kecamatan, Kota)</label>
                    <textarea name="address" id="address" rows="2" required class="clay-input w-full p-4 rounded-2xl text-sm font-bold text-admin-textMain resize-none"></textarea>
                </div>

                <!-- Wilayah Asal RajaOngkir -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Kecamatan / Kota Asal Ongkir</label>
                        <input type="text" name="rajaongkir_keyword" id="rajaongkir_keyword" placeholder="Cth: Mojoroto / Jatiasih" class="clay-input w-full p-3.5 rounded-2xl text-sm font-bold text-admin-textMain">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">ID Destinasi RajaOngkir (Otomatis)</label>
                        <input type="text" name="rajaongkir_destination_id" id="rajaongkir_destination_id" readonly class="clay-input w-full p-3.5 rounded-2xl text-sm font-bold text-admin-textMuted bg-gray-100" placeholder="Terisi otomatis saat disimpan">
                    </div>
                </div>

                <!-- Hidden inputs otomatis terisi dari titik koordinat Leaflet -->
                <input type="hidden" name="map_external_url" id="map_external_url">
                <input type="hidden" name="map_embed_url" id="map_embed_url">

                <!-- LEAFLET MAPS (Satu-satunya Peta Lokasi & Pusat Acuan Ongkir) -->
                <div class="p-5 bg-white/50 border border-white rounded-[2rem] shadow-sm">
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMuted mb-2">Lokasi Dapur & Pusat Acuan Ongkir</label>
                    <p class="text-[11px] font-medium text-admin-textMuted mb-3 leading-relaxed">Geser pin biru di peta atau gunakan kotak pencarian di pojok kanan peta untuk menetapkan titik akurat Dapur Raya Kitchen. Koordinat dan link peta untuk tampilan toko akan otomatis diperbarui.</p>

                    <div id="admin-map" class="w-full h-[380px] rounded-2xl border-2 border-white shadow-[0_4px_15px_rgba(147,104,183,0.15)] z-10 mb-4"></div>

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
    let adminMap, adminMarker;
    let defaultLat = -7.816600;
    let defaultLng = 112.011400;

    // Sinkronkan input koordinat & URL peta otomatis dari titik Leaflet
    function syncLocationFields(lat, lng) {
        const latFixed = parseFloat(lat).toFixed(7);
        const lngFixed = parseFloat(lng).toFixed(7);
        $('#latitude').val(latFixed);
        $('#longitude').val(lngFixed);
        $('#map_external_url').val(`https://www.google.com/maps?q=${latFixed},${lngFixed}`);
        $('#map_embed_url').val(`https://maps.google.com/maps?q=${latFixed},${lngFixed}&z=15&output=embed`);
    }

    function initAdminMap(lat, lng) {
        if (!adminMap) {
            adminMap = L.map('admin-map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(adminMap);
            adminMarker = L.marker([lat, lng], {draggable: true}).addTo(adminMap);

            adminMarker.on('dragend', function () {
                let pos = adminMarker.getLatLng();
                syncLocationFields(pos.lat, pos.lng);
            });

            adminMap.on('click', function(e) {
                adminMarker.setLatLng(e.latlng);
                syncLocationFields(e.latlng.lat, e.latlng.lng);
            });

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
                syncLocationFields(center.lat, center.lng);
            }).addTo(adminMap);
        } else {
            adminMarker.setLatLng([lat, lng]);
            adminMap.setView([lat, lng], 15);
        }
        syncLocationFields(lat, lng);
    }

    // --- 1. LOAD DATA ASLI DARI API ---
    $.ajax({
        url: '/api/admin/profil-perusahaan',
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        success: function(res) {
            if (res.status === 'success' && res.data) {
                const d = res.data;
                $('#company_name').val(d.company_name || '');
                $('#established_year').val(d.established_year || '');
                $('#whatsapp_number').val(d.whatsapp_number || '');
                $('#email_address').val(d.email_address || '');
                $('#instagram_handle').val(d.instagram_handle || '');
                $('#short_description').val(d.short_description || '');
                $('#background_history').val(d.background_history || '');
                $('#address').val(d.address || '');
                $('#rajaongkir_destination_id').val(d.rajaongkir_destination_id || '');

                if (d.image_full_url) {
                    $('#preview-cover-img').attr('src', d.image_full_url);
                    $('#preview-cover-wrapper').removeClass('hidden');
                }

                let lat = parseFloat(d.latitude) || defaultLat;
                let lng = parseFloat(d.longitude) || defaultLng;
                initAdminMap(lat, lng);

                if (d.map_external_url) $('#map_external_url').val(d.map_external_url);
                if (d.map_embed_url) $('#map_embed_url').val(d.map_embed_url);
            } else {
                initAdminMap(defaultLat, defaultLng);
            }
        },
        error: function() {
            initAdminMap(defaultLat, defaultLng);
        }
    });

    // Preview gambar lokal sebelum diupload
    $('#image_url').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-cover-img').attr('src', e.target.result);
                $('#preview-cover-wrapper').removeClass('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Update peta pas admin ngetik koordinat manual di Input
    $('#latitude, #longitude').on('input', function() {
        let lat = parseFloat($('#latitude').val());
        let lng = parseFloat($('#longitude').val());
        if (!isNaN(lat) && !isNaN(lng) && adminMarker) {
            adminMarker.setLatLng([lat, lng]);
            adminMap.setView([lat, lng], adminMap.getZoom());
            $('#map_external_url').val(`https://www.google.com/maps?q=${lat.toFixed(7)},${lng.toFixed(7)}`);
            $('#map_embed_url').val(`https://maps.google.com/maps?q=${lat.toFixed(7)},${lng.toFixed(7)}&z=15&output=embed`);
        }
    });

    // --- 2. SUBMIT FORM KE API ASLI ---
    $('#form-profil').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let btn = $('#btn-submit-profil');

        btn.html('<i class="ph ph-spinner animate-spin text-xl"></i> Menyimpan...').prop('disabled', true);

        $.ajax({
            url: '/api/admin/profil-perusahaan',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
            },
            success: function(res) {
                if (res.data && res.data.rajaongkir_destination_id) {
                    $('#rajaongkir_destination_id').val(res.data.rajaongkir_destination_id);
                }
                if (res.data && res.data.image_full_url) {
                    $('#preview-cover-img').attr('src', res.data.image_full_url);
                    $('#preview-cover-wrapper').removeClass('hidden');
                }

                $('#alert-profil').removeClass('hidden bg-red-50 border-red-200 text-red-600')
                    .addClass('bg-green-50 border-green-200 text-green-600')
                    .html(`<i class="ph ph-check-circle mr-1"></i> ${res.message}`);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan saat menyimpan profil.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors)[0][0];
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                $('#alert-profil').removeClass('hidden bg-green-50 border-green-200 text-green-600')
                    .addClass('bg-red-50 border-red-200 text-red-600')
                    .html(`<i class="ph ph-warning-circle mr-1"></i> ${msg}`);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            complete: function() {
                btn.html('<i class="ph ph-floppy-disk text-xl"></i> Simpan Profil Perusahaan').prop('disabled', false);
            }
        });
    });
});
</script>
@endpush