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
<body class="min-h-screen bg-[#E6D6EB] flex items-center justify-center p-4 font-sans antialiased relative">

    <!-- Container Utama: Claymorphism -->
    <div class="w-full max-w-md bg-buyer-bg rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_20px_40px_rgba(192,153,206,0.3)] border border-white p-8 sm:p-10 flex flex-col relative text-center">

        <div class="flex justify-center mb-6">
            <div class="w-20 h-20 bg-white rounded-2xl p-2 shadow-[inset_0_-2px_4px_rgba(192,153,206,0.2),0_8px_15px_rgba(192,153,206,0.2)] border border-white flex items-center justify-center">
                <svg class="w-10 h-10 text-buyer-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
        </div>

        <h1 class="text-2xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Verifikasi WhatsApp</h1>
        <p class="text-buyer-textSecondary font-medium text-sm mb-8">
            Masukkan 6 digit kode OTP yang baru aja dikirim ke nomor <br>
            <span id="display-wa" class="font-bold text-buyer-primary"></span>
        </p>

        <!-- Notifikasi Error AJAX -->
        <div id="alert-box" class="hidden mb-6 p-3.5 text-sm font-extrabold rounded-2xl border text-center"></div>

        <form id="form-otp" class="space-y-6">
            @csrf

            <!-- Kotak Input OTP (6 Digit) -->
            <div class="flex justify-between gap-2 sm:gap-3" id="otp-container">
                <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-buyer-textPrimary transition-all" required>
                <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-buyer-textPrimary transition-all" required>
                <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-buyer-textPrimary transition-all" required>
                <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-buyer-textPrimary transition-all" required>
                <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-buyer-textPrimary transition-all" required>
                <input type="text" maxlength="1" class="otp-input w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black rounded-2xl bg-buyer-bg shadow-[inset_0_3px_6px_rgba(192,153,206,0.2),inset_0_-2px_4px_rgba(255,255,255,0.8)] border border-transparent focus:outline-none focus:ring-2 focus:ring-buyer-primary/50 focus:bg-white text-buyer-textPrimary transition-all" required>
            </div>

            <button type="submit" id="btn-submit" class="w-full mt-4 py-4 bg-buyer-primary text-white font-black text-sm rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_6px_15px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all flex justify-center items-center gap-2">
                <span id="btn-text">Verifikasi OTP</span>
            </button>
        </form>

        <p class="text-center text-sm font-bold text-buyer-textSecondary mt-8">
            Belum terima kode?
            <button id="resend-btn" class="text-buyer-primary hover:text-buyer-hover hover:underline underline-offset-4 transition-all">Kirim Ulang</button>
        </p>

    </div>

    <!-- SCRIPT LOGIC OTP -->
    <script>
        $(document).ready(function() {
            // Ambil nomor WA dari URL parameter (?wa=628xxx)
            const urlParams = new URLSearchParams(window.location.search);
            const waNumber = urlParams.get('wa');

            if(waNumber) {
                // Sensor sedikit nomornya biar aman (62812****890)
                let maskedWa = waNumber.substring(0, 5) + '****' + waNumber.substring(waNumber.length - 3);
                $('#display-wa').text('+' + maskedWa);
            } else {
                window.location.href = "{{ route('register') }}"; // Tendang ke register kalau gaada param
            }

            // Auto-next kotak input pas ngetik angka
            $('.otp-input').on('keyup', function(e) {
                let key = e.which;
                if(key >= 48 && key <= 57 || key >= 96 && key <= 105) {
                    // Kalau angka, pindah ke kotak kanan
                    $(this).next('.otp-input').focus();
                } else if(key === 8) {
                    // Kalau backspace, hapus lalu pindah ke kotak kiri
                    $(this).prev('.otp-input').focus();
                }
            });

            // Submit Verifikasi OTP
            $('#form-otp').on('submit', function(e) {
                e.preventDefault();

                // Gabungin 6 value input jadi 1 string
                let otpCode = '';
                $('.otp-input').each(function() {
                    otpCode += $(this).val();
                });

                if(otpCode.length < 6) {
                    showError('Kode OTP harus 6 digit cuy!');
                    return;
                }

                // Loading state
                $('#btn-text').text('Mengecek...');
                $('#btn-submit').prop('disabled', true).addClass('opacity-80 cursor-wait');
                $('#alert-box').addClass('hidden');

                $.ajax({
                    url: "{{ url('api/auth/verify-otp') }}", 
                    type: "POST",
                    data: {
                        whatsapp_number: waNumber,
                        otp: otpCode,
                        _token: $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            $('#alert-box').removeClass('hidden bg-red-50 text-red-600 border-red-100')
                                .addClass('bg-green-50 text-green-600 border-green-100')
                                .text('Verifikasi Sukses! Mengalihkan...');

                            // Arahin ke halaman Home/Dashboard
                            setTimeout(() => {
                                window.location.href = "{{ url('/') }}";
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        $('#btn-text').text('Verifikasi OTP');
                        $('#btn-submit').prop('disabled', false).removeClass('opacity-80 cursor-wait');

                        let errorMsg = xhr.responseJSON?.message || 'Kode OTP salah atau kadaluarsa!';
                        showError(errorMsg);
                    }
                });
            });

            function showError(msg) {
                $('#alert-box').removeClass('hidden bg-green-50 text-green-600 border-green-100')
                    .addClass('bg-red-50 text-red-600 border-red-100 shadow-[inset_0_2px_4px_rgba(239,68,68,0.05)]')
                    .text(msg);
            }
        });
    </script>
</body>
</html>
