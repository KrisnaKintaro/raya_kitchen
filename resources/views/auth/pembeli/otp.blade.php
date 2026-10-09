<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Raya Kitchen</title>
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
    <a href="{{ url('/register') }}" class="absolute top-6 left-6 md:top-8 md:left-8 inline-flex items-center gap-2 px-5 py-2.5 bg-buyer-bg text-buyer-textSecondary font-extrabold text-sm rounded-full shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),inset_0_2px_4px_rgba(255,255,255,1),0_4px_10px_rgba(192,153,206,0.2)] border border-white hover:bg-white hover:text-buyer-primary hover:-translate-y-0.5 active:translate-y-0 transition-all z-10">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span class="hidden sm:block">Kembali</span>
    </a>

    <!-- Container Utama: Claymorphism -->
    <div class="w-full max-w-4xl bg-buyer-bg rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_20px_40px_rgba(192,153,206,0.3)] border border-white flex flex-col md:flex-row overflow-hidden relative">

        <!-- Kolom Kiri: Foto Roti + Overlay Ungu -->
        <div class="hidden md:block md:w-5/12 relative bg-buyer-primary">
            <!-- Foto Bakery buat OTP -->
            <img src="{{ asset('asset/bakery_otp.jpg') }}" alt="Bakery Otp" class="absolute inset-0 w-full h-full object-cover">

            <!-- Overlay warna primer -->
            <div class="absolute inset-0 bg-buyer-primary/70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-buyer-primary/90 to-transparent"></div>

            <div class="relative z-10 h-full flex flex-col justify-end p-8 text-white">
                <div class="w-16 h-16 bg-white rounded-2xl p-1.5 mb-6 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_8px_15px_rgba(0,0,0,0.25)] border border-white">
                    <img src="{{ asset('asset/logo_app.png') }}" alt="Logo" class="w-full h-auto">
                </div>
                <h2 class="text-3xl font-black mb-2 drop-shadow-md leading-tight">Satu Langkah Lagi!</h2>
                <p class="text-white/90 font-medium text-sm leading-relaxed drop-shadow-sm">
                    Keamanan akun anda adalah prioritas kami. Cek WhatsApp mu sekarang buat ngambil kode rahasianya.
                </p>
            </div>
        </div>

        <!-- Kolom Kanan: Form OTP -->
        <div class="w-full md:w-7/12 p-8 sm:p-12 flex flex-col justify-center bg-buyer-bg relative">

            <!-- Header Mobile Logo -->
            <div class="md:hidden flex justify-center mb-6">
                <div class="w-16 h-16 bg-white rounded-2xl p-1.5 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_8px_15px_rgba(192,153,206,0.2)] border border-white">
                    <img src="{{ asset('asset/logo_app.png') }}" alt="Logo" class="w-full h-auto">
                </div>
            </div>

            <div class="mb-8 text-center md:text-left">
                <h1 class="text-3xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Verifikasi Nomor WA</h1>
                <p class="text-buyer-textSecondary font-medium text-sm">
                    Kami udah ngirim 6 digit kode OTP ke nomor <br>
                    <span class="font-bold text-buyer-primary">+62 812-XXXX-XXXX</span>
                </p>
            </div>

            <!-- Notifikasi Error/Success AJAX -->
            <div id="alert-box" class="hidden mb-6 p-3.5 text-sm font-extrabold rounded-2xl border text-center">
                <!-- Pesan dinamis -->
            </div>

            <form id="form-otp" class="space-y-6">
                @csrf

                <!-- Input OTP 6 Kotak (Efek Tenggelam/Mendelep) -->
                <div>
                    <label class="block text-xs font-extrabold text-buyer-textPrimary ml-1 mb-3 uppercase tracking-wider text-center md:text-left">Masukkan Kode OTP</label>
                    <div class="flex gap-2 justify-center md:justify-start" id="otp-container">
                        <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black text-buyer-textPrimary rounded-2xl bg-white shadow-[inset_0_2px_5px_rgba(192,153,206,0.2)] border-2 border-buyer-primary/30 focus:outline-none focus:ring-4 focus:ring-buyer-primary/30 focus:border-buyer-primary transition-all">
                        <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black text-buyer-textPrimary rounded-2xl bg-white shadow-[inset_0_2px_5px_rgba(192,153,206,0.2)] border-2 border-buyer-primary/30 focus:outline-none focus:ring-4 focus:ring-buyer-primary/30 focus:border-buyer-primary transition-all">
                        <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black text-buyer-textPrimary rounded-2xl bg-white shadow-[inset_0_2px_5px_rgba(192,153,206,0.2)] border-2 border-buyer-primary/30 focus:outline-none focus:ring-4 focus:ring-buyer-primary/30 focus:border-buyer-primary transition-all">
                        <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black text-buyer-textPrimary rounded-2xl bg-white shadow-[inset_0_2px_5px_rgba(192,153,206,0.2)] border-2 border-buyer-primary/30 focus:outline-none focus:ring-4 focus:ring-buyer-primary/30 focus:border-buyer-primary transition-all">
                        <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black text-buyer-textPrimary rounded-2xl bg-white shadow-[inset_0_2px_5px_rgba(192,153,206,0.2)] border-2 border-buyer-primary/30 focus:outline-none focus:ring-4 focus:ring-buyer-primary/30 focus:border-buyer-primary transition-all">
                        <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black text-buyer-textPrimary rounded-2xl bg-white shadow-[inset_0_2px_5px_rgba(192,153,206,0.2)] border-2 border-buyer-primary/30 focus:outline-none focus:ring-4 focus:ring-buyer-primary/30 focus:border-buyer-primary transition-all">
                    </div>
                </div>

                <!-- Tombol Verifikasi (Efek Timbul Empuk) -->
                <button type="submit" id="btn-submit" class="w-full mt-4 py-4 bg-buyer-primary text-white font-black text-sm rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_6px_15px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)] transition-all flex justify-center items-center gap-2">
                    <span id="btn-text">Verifikasi Sekarang</span>
                    <svg id="btn-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>
            </form>

            <!-- Kirim Ulang OTP -->
            <p class="text-center text-sm font-bold text-buyer-textSecondary mt-8">
                Belum dapet pesan WA? <br class="sm:hidden">
                <span id="timer-text" class="text-buyer-textSecondary">Tunggu <span id="countdown" class="text-buyer-primary">60</span> detik</span>
                <a href="#" id="resend-link" class="hidden text-buyer-primary hover:text-buyer-hover hover:underline underline-offset-4 transition-all">Kirim Ulang OTP</a>
            </p>

        </div>
    </div>

    <!-- SCRIPT LOGIC UI OTP -->
    <script>
        $(document).ready(function() {
            // 1. Logic pindah kolom otomatis pas ngetik OTP
            const inputs = $('.otp-input');

            inputs.on('input', function(e) {
                const target = $(this);
                const val = target.val();

                // Cuma bolehin angka
                target.val(val.replace(/[^0-9]/g, ''));

                if (target.val() !== '') {
                    target.next('.otp-input').focus();
                }
            });

            inputs.on('keydown', function(e) {
                const target = $(this);
                // Kalau pencet backspace dan kotaknya kosong, pindah ke kiri
                if (e.key === 'Backspace' && target.val() === '') {
                    target.prev('.otp-input').focus();
                }
            });

            // Pastiin input pertama auto-focus pas halaman dibuka
            inputs.first().focus();


            // 2. Logic Countdown Timer buat Kirim Ulang
            let timeLeft = 60;
            const countdownEl = $('#countdown');
            const timerTextEl = $('#timer-text');
            const resendLinkEl = $('#resend-link');

            const timer = setInterval(() => {
                timeLeft--;
                countdownEl.text(timeLeft);

                if (timeLeft <= 0) {
                    clearInterval(timer);
                    timerTextEl.addClass('hidden');
                    resendLinkEl.removeClass('hidden');
                }
            }, 1000);

            // 3. Simulasi Submit (Nanti lo sambungin ke controller)
            $('#form-otp').on('submit', function(e) {
                e.preventDefault();

                // Gabungin 6 input jadi 1 string
                let otpValue = '';
                inputs.each(function() {
                    otpValue += $(this).val();
                });

                if(otpValue.length < 6) {
                    $('#alert-box').removeClass('hidden bg-green-50 text-green-600 border-green-100')
                        .addClass('bg-red-50 text-red-600 border-red-100 shadow-[inset_0_2px_4px_rgba(239,68,68,0.05)]')
                        .text('Isi kode OTP-nya sampai lengkap 6 digit cuy!');
                    return;
                }

                // Efek loading
                $('#btn-text').text('Memverifikasi...');
                $('#btn-icon').addClass('animate-spin').html('<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>');
                $('#btn-submit').prop('disabled', true).addClass('opacity-80 cursor-wait');
                $('#alert-box').addClass('hidden');

                // Nanti lo ganti pake AJAX beneran ke route API OTP lo
                setTimeout(() => {
                    $('#alert-box').removeClass('hidden bg-red-50 text-red-600 border-red-100')
                        .addClass('bg-green-50 text-green-600 border-green-100 shadow-[inset_0_2px_4px_rgba(34,197,94,0.05)]')
                        .text('Verifikasi sukses njir! Redirecting...');

                    setTimeout(() => {
                        window.location.href = "{{ url('/') }}";
                    }, 1500);
                }, 1500);
            });
        });
    </script>
</body>
</html>
