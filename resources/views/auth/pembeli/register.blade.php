<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Raya Kitchen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        buyer: {
                            primary: '#C099CE',
                            hover: '#9B72AB',
                            bg: '#F8F7FA',
                            textPrimary: '#2D2A32',
                            textSecondary: '#716E77'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-[#E6D6EB] flex items-center justify-center p-4 md:p-8 font-sans antialiased relative">

    <!-- Tombol Kembali Claymorphism -->
    <a href="{{ url('/') }}" class="absolute top-6 left-6 md:top-8 md:left-8 inline-flex items-center gap-2 px-5 py-2.5 bg-buyer-bg text-buyer-textSecondary font-extrabold text-sm rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_10px_rgba(192,153,206,0.2)] border border-white hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 active:translate-y-0 transition-all z-10">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span class="hidden sm:block">Kembali</span>
    </a>

    <!-- Container Utama: Claymorphism -->
    <div class="w-full max-w-4xl bg-buyer-bg rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_20px_40px_rgba(192,153,206,0.3)] border border-white flex flex-col md:flex-row overflow-hidden relative">

        <!-- Kolom Kiri: Foto Roti + Overlay Ungu -->
        <div class="hidden md:block md:w-5/12 relative bg-buyer-primary">
            <!-- Foto Bakery buat Register -->
            <img src="{{ asset('asset/bakery_register.jpg') }}" alt="Bakery" class="absolute inset-0 w-full h-full object-cover">
            <!-- Overlay warna primer -->
            <div class="absolute inset-0 bg-buyer-primary/70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-buyer-primary/90 to-transparent"></div>

            <div class="relative z-10 h-full flex flex-col justify-end p-8 text-white">
                <div class="w-16 h-16 bg-white rounded-2xl p-1.5 mb-6 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_8px_15px_rgba(0,0,0,0.25)] border border-white">
                    <img src="{{ asset('asset/logo_app.png') }}" alt="Logo" class="w-full h-auto">
                </div>
                <h2 class="text-3xl font-black mb-2 drop-shadow-md leading-tight">Gabung Sekarang!</h2>
                <p class="text-white/90 font-medium text-sm leading-relaxed drop-shadow-sm">
                    Buat akun barumu dan mulai pesan roti kesukaanmu tanpa ribet antri.
                </p>
            </div>
        </div>

        <!-- Kolom Kanan: Form Register -->
        <div class="w-full md:w-7/12 p-8 sm:p-12 flex flex-col justify-center bg-buyer-bg relative">

            <!-- Header Mobile Logo -->
            <div class="md:hidden flex justify-center mb-6">
                <div class="w-16 h-16 bg-white rounded-2xl p-1.5 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_8px_15px_rgba(192,153,206,0.2)] border border-white">
                    <img src="{{ asset('asset/logo_app.png') }}" alt="Logo" class="w-full h-auto">
                </div>
            </div>

            <div class="mb-6 text-center md:text-left">
                <h1 class="text-3xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Daftar Akun Baru Anda</h1>
                <p class="text-buyer-textSecondary font-medium text-sm">Lengkapi data di bawah untuk membuat akun baru.</p>
            </div>

            <!-- Notifikasi Error/Success AJAX -->
            <div id="alert-box" class="hidden mb-6 p-3.5 text-sm font-extrabold rounded-2xl border text-center">
                <!-- Pesan dinamis -->
            </div>

            <form id="form-register" class="space-y-4">
                @csrf

                <!-- Input Nama Lengkap -->
                <div>
                    <label class="block text-xs font-extrabold text-buyer-textPrimary ml-3 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
                    <input type="text" id="name" name="name" placeholder="Siapa namamu?" class="w-full px-4 py-3.5 rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-sm font-bold text-buyer-textPrimary placeholder:font-semibold placeholder:text-buyer-textSecondary/50 transition-all" required>
                </div>

                <!-- Input Nomor WA -->
                <div>
                    <label class="block text-xs font-extrabold text-buyer-textPrimary ml-3 mb-1.5 uppercase tracking-wider">Nomor WhatsApp</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-buyer-textSecondary font-bold text-sm">+62</span>
                        </div>
                        <input type="number" id="wa_number" name="whatsapp_number" placeholder="8123456789" class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-sm font-bold text-buyer-textPrimary placeholder:font-semibold placeholder:text-buyer-textSecondary/50 transition-all appearance-none" required>
                    </div>
                </div>

                <!-- Input Password & Konfirmasi (Bersebelahan di desktop) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-buyer-textPrimary ml-3 mb-1.5 uppercase tracking-wider">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" class="w-full px-4 py-3.5 rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-sm font-bold text-buyer-textPrimary placeholder:font-semibold placeholder:text-buyer-textSecondary/50 transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-buyer-textPrimary ml-3 mb-1.5 uppercase tracking-wider">Ulangi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" class="w-full px-4 py-3.5 rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-sm font-bold text-buyer-textPrimary placeholder:font-semibold placeholder:text-buyer-textSecondary/50 transition-all" required>
                    </div>
                </div>

                <!-- Tombol Register (Efek Timbul Empuk) -->
                <button type="submit" id="btn-submit" class="w-full mt-6 py-4 bg-buyer-primary text-white font-black text-sm rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_6px_15px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)] transition-all flex justify-center items-center gap-2">
                    <span id="btn-text">Daftar Sekarang</span>
                    <svg id="btn-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </button>
            </form>

            <p class="text-center text-sm font-bold text-buyer-textSecondary mt-8">
                Udah punya akun?
                <a href="{{ route('login') }}" class="text-buyer-primary hover:text-buyer-hover hover:underline underline-offset-4 transition-all">Masuk di sini</a>
            </p>

        </div>
    </div>

    <!-- SCRIPT AJAX REGISTER -->
    <script>
        $(document).ready(function() {
            $('#form-register').on('submit', function(e) {
                e.preventDefault();

                let name = $('#name').val();
                let wa_number = $('#wa_number').val();
                let password = $('#password').val();
                let password_confirmation = $('#password_confirmation').val();
                let _token = $('input[name="_token"]').val();

                // Validasi Frontend Sederhana
                if(password !== password_confirmation) {
                    $('#alert-box').removeClass('hidden bg-green-50 text-green-600 border-green-100').addClass('bg-red-50 text-red-600 border-red-100 shadow-[inset_0_2px_4px_rgba(239,68,68,0.05)]').text('Password dan Konfirmasi Password tidak sama bjir!');
                    return;
                }

                // Loading State
                $('#btn-text').text('Memproses...');
                $('#btn-icon').addClass('animate-spin').html('<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>');
                $('#btn-submit').prop('disabled', true).addClass('opacity-80 cursor-wait');
                $('#alert-box').addClass('hidden');

                $.ajax({
                    url: "{{ url('/api/pembeli/register') }}",
                    type: "POST",
                    data: {
                        name: name,
                        whatsapp_number: wa_number,
                        password: password,
                        password_confirmation: password_confirmation,
                        _token: _token
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            $('#alert-box').removeClass('hidden bg-red-50 text-red-600 border-red-100 shadow-[inset_0_2px_4px_rgba(239,68,68,0.05)]')
                                .addClass('bg-green-50 text-green-600 border-green-100 shadow-[inset_0_2px_4px_rgba(34,197,94,0.05)]')
                                .text('Daftar berhasil cuy! Redirecting...');

                            // Auto login & lempar ke home
                            setTimeout(() => {
                                window.location.href = "{{ url('/') }}";
                            }, 1500);
                        } else {
                            resetBtn();
                            showError(response.message);
                        }
                    },
                    error: function(xhr) {
                        resetBtn();
                        let errorMsg = 'Gagal terhubung ke server. Coba lagi cuy.';

                        // Nge-handle validasi error dari Laravel (misal WA udah terdaftar)
                        if(xhr.responseJSON && xhr.responseJSON.errors) {
                            let firstError = Object.values(xhr.responseJSON.errors)[0][0];
                            errorMsg = firstError;
                        } else if(xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        showError(errorMsg);
                    }
                });
            });

            function resetBtn() {
                $('#btn-text').text('Daftar Sekarang');
                $('#btn-icon').removeClass('animate-spin').html('<path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>');
                $('#btn-submit').prop('disabled', false).removeClass('opacity-80 cursor-wait');
            }

            function showError(msg) {
                $('#alert-box').removeClass('hidden bg-green-50 text-green-600 border-green-100')
                    .addClass('bg-red-50 text-red-600 border-red-100 shadow-[inset_0_2px_4px_rgba(239,68,68,0.05)]')
                    .text(msg);
            }
        });
    </script>
</body>
</html>
