@extends('pembeli.pembeli_master')
@section('title', 'Profil Saya - Raya Kitchen')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto p-6 bg-white rounded-2xl shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1)] flex flex-col justify-center relative mb-6">
        
        <h1 class="text-buyer-textPrimary drop-shadow-sm text-xl font-bold mb-2">Profil Saya</h1>
        <p class="font-medium text-sm text-gray-600 mb-6">Kelola informasi akun Anda di sini.</p>

        <!-- Tempat notifikasi -->
        <div id="alert-message" class="hidden mb-4 p-3 rounded-xl text-sm font-bold border"></div>

        <div class="flex flex-col gap-6">
            
            <!-- FORM TERPADU -->
            <div class="border-b border-gray-100 pb-6">
                <form id="formProfil" class="flex flex-col gap-5">
                    
                    <!-- 1. Nama Lengkap -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" id="user_name" name="name" value="{{ Auth::user()->name ?? '' }}" required
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400">
                    </div>

                    <!-- 2. Keamanan (Password Saat Ini & Password Baru) -->
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl">
                        <p class="text-xs font-bold text-gray-500 mb-3 uppercase tracking-wider">Keamanan Akun</p>
                        
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Password Saat Ini</label>
                            <input type="text" value="••••••••••••" readonly
                                class="w-full p-3 text-sm font-medium rounded-xl border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                                <input type="password" id="password" name="password" placeholder="Kosongkan jika tak diubah"
                                    class="w-full p-3 text-sm font-medium rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password"
                                    class="w-full p-3 text-sm font-medium rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400">
                                <p id="password-error" class="text-xs text-red-500 mt-1 hidden font-semibold">Password tidak cocok!</p>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Nomor WhatsApp -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor WhatsApp</label>
                        @php
                            $rawWa = Auth::user()->whatsapp_number ?? '';
                            if (str_starts_with($rawWa, '62')) {
                                $rawWa = '0' . substr($rawWa, 2);
                            }
                        @endphp
                        <input type="text" id="user_wa" name="whatsapp_number" value="{{ $rawWa }}" required placeholder="08xxxxxxxxxx"
                            class="w-full p-3.5 text-sm font-medium rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400">
                    </div>

                    <!-- 4. Tombol Simpan (Disembunyikan secara default, muncul jika ada perubahan) -->
                    <div id="container-btn-simpan" class="hidden mt-2 transition-all duration-300">
                        <button type="submit" id="btnUpdateProfil" class="w-full py-3.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-2xl shadow transition duration-200">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tombol Kelola Alamat -->
            <div>
                <a href="{{ route('alamat') }}" class="w-full flex items-center justify-center gap-2 py-3.5 px-4 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-sm rounded-2xl transition duration-200 border border-purple-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Kelola Alamat Pengiriman Saya
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    let alertBox = $('#alert-message');

    // Simpan nilai awal form untuk membandingkan perubahan
    let initialValues = {
        name: $('#user_name').val(),
        wa: $('#user_wa').val(),
        pass: '',
        passConf: ''
    };

    function checkChanges() {
        let currentName = $('#user_name').val();
        let currentWa = $('#user_wa').val();
        let currentPass = $('#password').val();
        let currentPassConf = $('#password_confirmation').val();

        if (currentName !== initialValues.name || 
            currentWa !== initialValues.wa || 
            currentPass !== '' || 
            currentPassConf !== '') {
            $('#container-btn-simpan').slideDown(); // Munculkan tombol
        } else {
            $('#container-btn-simpan').slideUp(); // Sembunyikan tombol jika kembali seperti semula
        }
    }

    // Pantau setiap kali user mengetik/mengubah isi input
    $('#user_name, #user_wa, #password, #password_confirmation').on('input', function() {
        checkChanges();
    });

    function showAlert(message, isSuccess) {
        alertBox.text(message).removeClass('hidden bg-red-100 text-red-700 border-red-200 bg-green-100 text-green-700 border-green-200');
        if(isSuccess) {
            alertBox.addClass('bg-green-100 text-green-700 border-green-200');
        } else {
            alertBox.addClass('bg-red-100 text-red-700 border-red-200');
        }
        setTimeout(() => alertBox.addClass('hidden'), 4000);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Submit Form
    $('#formProfil').on('submit', function(e) {
        e.preventDefault();
        
        let pass = $('#password').val();
        let passConf = $('#password_confirmation').val();
        let errorMsg = $('#password-error');
        
        if (pass || passConf) {
            if (pass !== passConf) {
                errorMsg.removeClass('hidden');
                return;
            }
            if (pass.length < 8) {
                alert('Password minimal harus 8 karakter!');
                return;
            }
        }
        errorMsg.addClass('hidden');

        let btn = $('#btnUpdateProfil');
        let originalText = btn.text();
        btn.text('Menyimpan...').prop('disabled', true);

        let formData = {
            name: $('#user_name').val(),
            whatsapp_number: $('#user_wa').val(),
            password: pass,
            password_confirmation: passConf
        };

        $.ajax({
            url: '/api/pembeli/update-profil-pribadi', 
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            xhrFields: { withCredentials: true },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(res) {
                if(res.status === 'success') {
                    showAlert(res.message, true);
                    
                    // Perbarui nilai awal dengan data terbaru setelah disimpan
                    initialValues.name = $('#user_name').val();
                    initialValues.wa = $('#user_wa').val();
                    $('#password').val('');
                    $('#password_confirmation').val('');
                    
                    // Sembunyikan kembali tombol simpan
                    $('#container-btn-simpan').slideUp();
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal memperbarui profil.';
                showAlert(msg, false);
            },
            complete: function() {
                btn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush