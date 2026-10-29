@extends('admin.admin_master')
@section('header_title', 'Profil Perusahaan')

@section('content')
<div class="max-w-5xl mx-auto pb-10">
    <div id="alert-profil" class="hidden p-4 mb-6 rounded-2xl font-bold text-sm shadow-sm border"></div>

    <form id="form-profil" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <!-- Bagean 1: Informasi Dhasar -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-admin-sideBg"></div>
            <h3 class="text-lg font-black text-admin-textMain mb-6 flex items-center gap-2">
                <i class="ph ph-buildings text-2xl text-admin-sideBg"></i> 
                Informasi Dasar
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Nama Usaha</label>
                    <input type="text" name="company_name" id="company_name" required class="w-full p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Tahun Berdiri</label>
                    <input type="number" name="established_year" id="established_year" required class="w-full p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Foto Sampul / Dapur</label>
                    <div class="relative flex items-center w-full p-2 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                        <input type="file" name="image_url" id="image_url" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-admin-navBg file:text-admin-sideBg hover:file:bg-admin-accent cursor-pointer">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagean 2: Kontak & Sosial Media -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-blue-400"></div>
            <h3 class="text-lg font-black text-admin-textMain mb-6 flex items-center gap-2">
                <i class="ph ph-address-book text-2xl text-blue-500"></i> 
                Kontak & Sosial Media
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Nomor WhatsApp</label>
                    <div class="relative">
                        <i class="ph ph-whatsapp-logo absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input type="text" name="whatsapp_number" id="whatsapp_number" required class="w-full pl-11 p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Email Perusahaan</label>
                    <div class="relative">
                        <i class="ph ph-envelope-simple absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input type="email" name="email_address" id="email_address" required class="w-full pl-11 p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Instagram Handle</label>
                    <div class="relative">
                        <i class="ph ph-instagram-logo absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input type="text" name="instagram_handle" id="instagram_handle" placeholder="@rayakitchen.id" class="w-full pl-11 p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagean 3: Deskripsi & Lokasi -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-pink-400"></div>
            <h3 class="text-lg font-black text-admin-textMain mb-6 flex items-center gap-2">
                <i class="ph ph-map-pin-line text-2xl text-pink-500"></i> 
                Deskripsi & Lokasi
            </h3>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Deskripsi Singkat (Header)</label>
                    <textarea name="short_description" id="short_description" rows="2" class="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm leading-relaxed"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Sejarah / Background History</label>
                    <textarea name="background_history" id="background_history" rows="4" class="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm leading-relaxed"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Alamat Lengkap</label>
                    <textarea name="address" id="address" rows="2" class="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm leading-relaxed"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Embed URL Google Maps (iframe src)</label>
                        <input type="text" name="map_embed_url" id="map_embed_url" class="w-full p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Link External Maps</label>
                        <input type="text" name="map_external_url" id="map_external_url" class="w-full p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-admin-sideBg focus:ring-2 focus:ring-admin-sideBg/20 outline-none transition-all text-sm">
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" id="btn-submit-profil" class="w-full py-4 bg-admin-sideBg text-white font-black rounded-2xl hover:bg-admin-sideHover shadow-[0_8px_20px_rgba(147,104,183,0.3)] hover:-translate-y-1 transition-all duration-300 text-lg flex items-center justify-center gap-2">
                <i class="ph ph-floppy-disk"></i> Simpan Profil Perusahaan
            </button>
        </div>
    </form>
</div>

@endsection

@push('script')
<script>
$(document).ready(function() {
    // Njupuk data seka API
    $.get('/api/admin/profil', function(res) {
        if(res.status === 'success' && res.data) {
            $('#company_name').val(res.data.company_name);
            $('#established_year').val(res.data.established_year);
            $('#whatsapp_number').val(res.data.whatsapp_number);
            $('#email_address').val(res.data.email_address);
            $('#instagram_handle').val(res.data.instagram_handle);
            $('#short_description').val(res.data.short_description);
            $('#background_history').val(res.data.background_history);
            $('#address').val(res.data.address);
            $('#map_embed_url').val(res.data.map_embed_url);
            $('#map_external_url').val(res.data.map_external_url);
        }
    });

    // Proses Submit AJAX
    $('#form-profil').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let btn = $('#btn-submit-profil');
        
        btn.html('<i class="ph ph-spinner animate-spin"></i> Menyimpan...').prop('disabled', true);

        $.ajax({
            url: '/api/admin/profil',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#alert-profil').removeClass('hidden bg-red-50 border-red-200 text-red-600')
                    .addClass('bg-green-50 border-green-200 text-green-600')
                    .html('<i class="ph ph-check-circle mr-1"></i> ' + res.message);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            error: function() {
                $('#alert-profil').removeClass('hidden bg-green-50 border-green-200 text-green-600')
                    .addClass('bg-red-50 border-red-200 text-red-600')
                    .html('<i class="ph ph-warning-circle mr-1"></i> Gagal menyimpan data. Coba lagi.');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            complete: function() {
                btn.html('<i class="ph ph-floppy-disk"></i> Simpan Profil Perusahaan').prop('disabled', false);
            }
        });
    });
});
</script>
@endpush