<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Raya Kitchen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { buyer: { primary: '#C099CE', hover: '#9B72AB', bg: '#F8F7FA', textPrimary: '#2D2A32', textSecondary: '#716E77' } } } }
        }
    </script>
</head>
<body class="min-h-screen bg-[#E6D6EB] flex items-center justify-center p-4 md:p-8 font-sans antialiased relative">

    <a href="{{ route('login') }}" class="absolute top-6 left-6 md:top-8 md:left-8 inline-flex items-center gap-2 px-5 py-2.5 bg-buyer-bg text-buyer-textSecondary font-extrabold text-sm rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_10px_rgba(192,153,206,0.2)] border border-white hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 active:translate-y-0 transition-all z-10">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span class="hidden sm:block">Batal</span>
    </a>

    <div class="w-full max-w-4xl bg-buyer-bg rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_20px_40px_rgba(192,153,206,0.3)] border border-white flex flex-col md:flex-row overflow-hidden relative">

        <div class="hidden md:block md:w-5/12 relative bg-buyer-primary">
            <img src="{{ asset('asset/bakery_resetpass.jpg') }}" alt="Bakery Pastry" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-buyer-primary/70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-buyer-primary/90 to-transparent"></div>
            <div class="relative z-10 h-full flex flex-col justify-end p-8 text-white">
                <div class="w-16 h-16 bg-white rounded-2xl p-1.5 mb-6 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_8px_15px_rgba(0,0,0,0.25)] border border-white">
                    <img src="{{ asset('asset/logo_app.png') }}" alt="Logo" class="w-full h-auto">
                </div>
                <h2 class="text-3xl font-black mb-2 drop-shadow-md leading-tight">Bikin Password Baru!</h2>
                <p class="text-white/90 font-medium text-sm leading-relaxed drop-shadow-sm">Buat password yang gampang diinget tapi susah ditebak ya cuy. Jangan pake tanggal lahir mantan.</p>
            </div>
        </div>

        <div class="w-full md:w-7/12 p-8 sm:p-12 lg:p-14 flex flex-col justify-center bg-buyer-bg relative">
            <div class="mb-8 text-center md:text-left">
                <h1 class="text-3xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Reset Password</h1>
                <p class="text-buyer-textSecondary font-medium text-sm">Masukkan kombinasi password baru lo di bawah ini.</p>
            </div>

            <div id="alert-box" class="hidden mb-6 p-3.5 text-sm font-extrabold rounded-2xl border text-center"></div>

            <form id="form-reset" class="space-y-5">
                @csrf

                <!-- Input Password Baru -->
                <div>
                    <label class="block text-xs font-extrabold text-buyer-textPrimary ml-3 mb-1.5 uppercase tracking-wider">Password Baru</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" class="w-full pl-4 pr-12 py-3.5 rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-sm font-bold text-buyer-textPrimary placeholder:font-semibold placeholder:text-buyer-textSecondary/50 transition-all" required minlength="6">
                        <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-4 flex items-center text-buyer-textSecondary hover:text-buyer-primary focus:outline-none transition-colors" data-target="password">
                            <svg class="w-5 h-5 icon-eye" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg class="w-5 h-5 icon-eye-slash hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Input Konfirmasi Password -->
                <div>
                    <label class="block text-xs font-extrabold text-buyer-textPrimary ml-3 mb-1.5 uppercase tracking-wider">Ulangi Password Baru</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password lu" class="w-full pl-4 pr-12 py-3.5 rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-sm font-bold text-buyer-textPrimary placeholder:font-semibold placeholder:text-buyer-textSecondary/50 transition-all" required minlength="6">
                        <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-4 flex items-center text-buyer-textSecondary hover:text-buyer-primary focus:outline-none transition-colors" data-target="password_confirmation">
                            <svg class="w-5 h-5 icon-eye" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg class="w-5 h-5 icon-eye-slash hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="w-full mt-6 py-4 bg-buyer-primary text-white font-black text-sm rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_6px_15px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)] transition-all flex justify-center items-center gap-2">
                    <span id="btn-text">Simpan Password</span>
                    <svg id="btn-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Logic Toggle (Mata) Password
            $('.toggle-password').on('click', function() {
                let targetId = $(this).data('target');
                let inputEl = $('#' + targetId);
                let type = inputEl.attr('type') === 'password' ? 'text' : 'password';

                inputEl.attr('type', type);
                $(this).find('.icon-eye').toggleClass('hidden');
                $(this).find('.icon-eye-slash').toggleClass('hidden');
            });

            // 1. Ambil Parameter WA dan Token dari URL
            const urlParams = new URLSearchParams(window.location.search);
            const waNumber = urlParams.get('wa');
            const token = urlParams.get('token');

            // Kalau user iseng buka halaman ini tanpa token, tendang balik ke forgot password
            if(!waNumber || !token) {
                window.location.href = "{{ url('/forgot-password') }}";
            }

            $('#form-reset').on('submit', function(e) {
                e.preventDefault();

                let password = $('#password').val();
                let passwordConf = $('#password_confirmation').val();

                if (password !== passwordConf) {
                    $('#alert-box').removeClass('hidden bg-green-50 text-green-600 border-green-100')
                        .addClass('bg-red-50 text-red-600 border-red-100 shadow-[inset_0_2px_4px_rgba(239,68,68,0.05)]')
                        .text('Waduh, password lu gak sama bjir! Coba ketik ulang.');
                    return;
                }

                $('#btn-text').text('Menyimpan...');
                $('#btn-icon').addClass('animate-spin').html('<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>');
                $('#btn-submit').prop('disabled', true).addClass('opacity-80 cursor-wait');
                $('#alert-box').addClass('hidden');

                $.ajax({
                    url: "{{ url('api/auth/reset-password') }}",
                    type: "POST",
                    data: {
                        whatsapp_number: waNumber,
                        token: token,
                        password: password,
                        password_confirmation: passwordConf,
                        _token: $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        $('#alert-box').removeClass('hidden bg-red-50 text-red-600 border-red-100 shadow-[inset_0_2px_4px_rgba(239,68,68,0.05)]')
                            .addClass('bg-green-50 text-green-600 border-green-100 shadow-[inset_0_2px_4px_rgba(34,197,94,0.05)]')
                            .text(response.message + ' Otw Login...');

                        setTimeout(() => {
                            window.location.href = "{{ route('login') }}";
                        }, 1500);
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'Gagal mereset password bjir.';
                        $('#alert-box').removeClass('hidden bg-green-50 text-green-600 border-green-100')
                            .addClass('bg-red-50 text-red-600 border-red-100 shadow-[inset_0_2px_4px_rgba(239,68,68,0.05)]')
                            .text(errorMsg);

                        $('#btn-text').text('Simpan Password');
                        $('#btn-icon').removeClass('animate-spin').html('<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>');
                        $('#btn-submit').prop('disabled', false).removeClass('opacity-80 cursor-wait');
                    }
                });
            });
        });
    </script>
</body>
</html>
