<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Raya Kitchen</title>
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
    <a href="{{ route('login') }}" class="absolute top-6 left-6 md:top-8 md:left-8 inline-flex items-center gap-2 px-5 py-2.5 bg-buyer-bg text-buyer-textSecondary font-extrabold text-sm rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_10px_rgba(192,153,206,0.2)] border border-white hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 active:translate-y-0 transition-all z-10">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span class="hidden sm:block">Kembali</span>
    </a>

    <!-- Container Utama: Claymorphism -->
    <div class="w-full max-w-4xl bg-buyer-bg rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_20px_40px_rgba(192,153,206,0.3)] border border-white flex flex-col md:flex-row overflow-hidden relative">

        <!-- Kolom Kiri: Foto Roti + Overlay Ungu -->
        <div class="hidden md:block md:w-5/12 relative bg-buyer-primary">
            <!-- Foto Bakery buat Forget Password -->
            <img src="{{ asset('asset/bakery_forgotpass.jpg') }}" alt="Bakery Display" class="absolute inset-0 w-full h-full object-cover">

            <!-- Overlay warna primer -->
            <div class="absolute inset-0 bg-buyer-primary/70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-buyer-primary/90 to-transparent"></div>

            <div class="relative z-10 h-full flex flex-col justify-end p-8 text-white">
                <div class="w-16 h-16 bg-white rounded-2xl p-1.5 mb-6 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_8px_15px_rgba(0,0,0,0.25)] border border-white">
                    <img src="{{ asset('asset/logo_app.png') }}" alt="Logo" class="w-full h-auto">
                </div>
                <h2 class="text-3xl font-black mb-2 drop-shadow-md leading-tight">Lupa Password?</h2>
                <p class="text-white/90 font-medium text-sm leading-relaxed drop-shadow-sm">
                    Tenang aja cuy, masukin nomor WA lo dan kita bakal bantu reset passwordnya.
                </p>
            </div>
        </div>

        <!-- Kolom Kanan: Form Forget Password -->
        <div class="w-full md:w-7/12 p-8 sm:p-12 lg:p-14 flex flex-col justify-center bg-buyer-bg relative">

            <!-- Header Mobile Logo -->
            <div class="md:hidden flex justify-center mb-6">
                <div class="w-16 h-16 bg-white rounded-2xl p-1.5 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_8px_15px_rgba(192,153,206,0.2)] border border-white">
                    <img src="{{ asset('asset/logo_app.png') }}" alt="Logo" class="w-full h-auto">
                </div>
            </div>

            <div class="mb-8 text-center md:text-left">
                <h1 class="text-3xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Reset Password</h1>
                <p class="text-buyer-textSecondary font-medium text-sm">Masukin nomor WhatsApp yang terdaftar di akun Raya Kitchen lo.</p>
            </div>

            <!-- Notifikasi Error/Success AJAX -->
            <div id="alert-box" class="hidden mb-6 p-3.5 text-sm font-extrabold rounded-2xl border text-center"></div>

            <form id="form-forget" class="space-y-5">
                @csrf
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

                <!-- Tombol Submit (Efek Timbul Empuk) -->
                <button type="submit" id="btn-submit" class="w-full mt-6 py-4 bg-buyer-primary text-white font-black text-sm rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_6px_15px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)] transition-all flex justify-center items-center gap-2">
                    <span id="btn-text">Kirim Link Reset</span>
                    <svg id="btn-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>

            <p class="text-center text-sm font-bold text-buyer-textSecondary mt-8">
                Inget passwordnya?
                <a href="{{ route('login') }}" class="text-buyer-primary hover:text-buyer-hover hover:underline underline-offset-4 transition-all">Balik ke Login</a>
            </p>
        </div>
    </div>

    <!-- SCRIPT LOGIC AJAX -->
    <script>
        $(document).ready(function() {
            $('#form-forget').on('submit', function(e) {
                e.preventDefault();

                // Loading State
                $('#btn-text').text('Mengirim...');
                $('#btn-icon').addClass('animate-pulse').html('<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>');
                $('#btn-submit').prop('disabled', true).addClass('opacity-80 cursor-wait');
                $('#alert-box').addClass('hidden');

                // Simulasi AJAX (Nanti lu ganti pake request beneran ke API Forget Password pas masuk Sprint 2)
                setTimeout(() => {
                    $('#alert-box').removeClass('hidden bg-red-50 text-red-600 border-red-100')
                        .addClass('bg-green-50 text-green-600 border-green-100 shadow-[inset_0_2px_4px_rgba(34,197,94,0.05)]')
                        .text('Link reset password berhasil dikirim cuy! Cek WA lo ya.');

                    $('#btn-text').text('Kirim Link Reset');
                    $('#btn-icon').removeClass('animate-pulse').html('<path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>');
                    $('#btn-submit').prop('disabled', false).removeClass('opacity-80 cursor-wait');
                    $('#wa_number').val('');
                }, 1500);
            });
        });
    </script>
</body>
</html>
