@extends('admin.admin_master')
@section('header_title', 'Profil Perusahaan')

@section('content')
<div class="p-6 bg-white rounded-2xl shadow-sm border border-gray-100 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Profil Perusahaan</h2>
    <div id="alert-profil" class="hidden p-4 mb-4 rounded-xl font-bold text-sm"></div>
    
    <form id="form-profil" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Usaha</label>
                <input type="text" name="company_name" id="company_name" required class="w-full p-3 border rounded-xl bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Berdiri</label>
                <input type="number" name="established_year" id="established_year" required class="w-full p-3 border rounded-xl bg-gray-50">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor WhatsApp</label>
                <input type="text" name="whatsapp_number" id="whatsapp_number" required class="w-full p-3 border rounded-xl bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email Perusahaan</label>
                <input type="email" name="email_address" id="email_address" required class="w-full p-3 border rounded-xl bg-gray-50">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Instagram Handle</label>
                <input type="text" name="instagram_handle" id="instagram_handle" class="w-full p-3 border rounded-xl bg-gray-50" placeholder="@rayakitchen.id">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Sampul / Dapur</label>
                <input type="file" name="image_url" id="image_url" accept="image/*" class="w-full p-2.5 border rounded-xl bg-gray-50">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Singkat (Header)</label>
            <textarea name="short_description" id="short_description" rows="2" class="w-full p-3 border rounded-xl bg-gray-50"></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Sejarah / Background History</label>
            <textarea name="background_history" id="background_history" rows="4" class="w-full p-3 border rounded-xl bg-gray-50"></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap</label>
            <textarea name="address" id="address" rows="2" class="w-full p-3 border rounded-xl bg-gray-50"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Embed URL Google Maps (iframe src)</label>
                <input type="text" name="map_embed_url" id="map_embed_url" class="w-full p-3 border rounded-xl bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Link External Maps (Buka di Maps)</label>
                <input type="text" name="map_external_url" id="map_external_url" class="w-full p-3 border rounded-xl bg-gray-50">
            </div>
        </div>

        <button type="submit" id="btn-submit-profil" class="w-full py-3.5 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 mt-4">Simpan Profil</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
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

    $('#form-profil').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let btn = $('#btn-submit-profil');
        btn.text('Menyimpan...').prop('disabled', true);

        $.ajax({
            url: '/api/admin/profil',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#alert-profil').removeClass('hidden bg-red-100 text-red-700').addClass('bg-green-100 text-green-700').text(res.message);
            },
            error: function() {
                $('#alert-profil').removeClass('hidden bg-green-100 text-green-700').addClass('bg-red-100 text-red-700').text('Gagal menyimpan data.');
            },
            complete: function() {
                btn.text('Simpan Profil').prop('disabled', false);
            }
        });
    });
});
</script>
@endsection