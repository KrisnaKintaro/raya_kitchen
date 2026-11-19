<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Sandi Baru Admin - Raya Kitchen</title>
    <!-- Tailwind CSS & Phosphor Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            sideBg: '#9368B7',
                            sideHover: '#A57BCB',
                            sideActive: '#B38CDA',
                            navBg: '#E8D5F6',
                            bg: '#F5F2F8',
                            accent: '#E4CCFF',
                            textMain: '#2D2A32',
                            textMuted: '#716E77'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-admin-bg font-sans antialiased flex items-center justify-center p-4 relative overflow-x-hidden">

    <!-- Tombol Kembali ke Login -->
    <div class="absolute top-6 left-6">
        <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white hover:bg-admin-navBg text-admin-textMain font-bold text-xs shadow-sm border border-admin-accent/50 transition-all">
            <i class="ph ph-arrow-left text-base"></i> Batal, ke Login
        </a>
    </div>

    <!-- Container Toast Notifikasi Kustom di ATAS TENGAH -->
    <div id="toast-container" class="fixed top-6 left-1/2 -translate-x-1/2 z-[200] flex flex-col gap-3 pointer-events-none w-full max-w-sm px-4"></div>

    <!-- Card Utama (Single Centered Card) -->
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-admin-accent/50">

        <!-- Header Banner dengan Gambar Suasana Dapur -->
        <div class="relative bg-slate-900 p-8 text-center text-white overflow-hidden">
            <div class="absolute inset-0">
                <img src="https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=800" alt="Keamanan Admin" class="w-full h-full object-cover opacity-40">
                <div class="absolute inset-0 bg-gradient-to-b from-[#9368B7]/80 via-[#9368B7]/90 to-[#734A97]"></div>
            </div>

            <div class="relative z-10">
                <div class="w-14 h-14 bg-white/15 backdrop-blur-md rounded-2xl mx-auto flex items-center justify-center mb-3 border border-white/25 shadow-inner text-white">
                    <i class="ph ph-lock-open text-2xl"></i>
                </div>
                <h1 class="text-xl font-black text-white tracking-tight">Buat Sandi Baru</h1>
                <p class="text-xs text-purple-100 mt-1">Masukkan kata sandi baru yang aman untuk akun internal.</p>
            </div>
        </div>

        <!-- Form Reset Password -->
        <div class="p-8">
            <div id="invalid-link-alert" class="hidden mb-4 p-4 rounded-2xl bg-red-50 border border-red-200 text-xs text-red-700 font-semibold">
                Tautan reset password tidak lengkap atau tidak valid. Silakan ajukan ulang di halaman Lupa Password.
            </div>

            <form id="admin-reset-form" class="space-y-4">
                <div>
                    <label for="password" class="block text-xs font-black uppercase tracking-wider text-admin-textMain mb-1.5">Kata Sandi Baru</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter" class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-purple-100 bg-admin-bg text-sm font-semibold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg focus:bg-white transition-all">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted hover:text-admin-textMain">
                            <i class="ph ph-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-black uppercase tracking-wider text-admin-textMain mb-1.5">Konfirmasi Sandi Baru</label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6" autocomplete="new-password" placeholder="Ulangi kata sandi baru" class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-purple-100 bg-admin-bg text-sm font-semibold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg focus:bg-white transition-all">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted hover:text-admin-textMain">
                            <i class="ph ph-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <button id="btn-submit" type="submit" class="w-full py-4 rounded-2xl bg-admin-sideBg hover:bg-admin-sideHover disabled:opacity-60 disabled:cursor-not-allowed text-white font-black text-sm shadow-lg shadow-purple-900/15 transition-all flex items-center justify-center gap-2 mt-2">
                    <i class="ph ph-check-circle text-base"></i>
                    <span>Simpan Sandi Baru</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const waParam = urlParams.get('wa') || '';
        const tokenParam = urlParams.get('token') || '';

        if (!waParam || !tokenParam) {
            document.getElementById('invalid-link-alert').classList.remove('hidden');
            document.getElementById('btn-submit').disabled = true;
        }

        function togglePassword(btn) {
            const input = btn.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i class="ph ph-eye-slash text-lg"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i class="ph ph-eye text-lg"></i>';
            }
        }

        function showNotification(title, message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const isError = type === 'error';

            const borderClass = isError ? 'border-red-200' : 'border-admin-accent';
            const iconBoxClass = isError ? 'bg-red-50 text-red-600' : 'bg-admin-navBg text-admin-sideBg';
            const iconClass = isError ? 'ph-warning-circle' : 'ph-check-circle';

            toast.className = `pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border ${borderClass} shadow-2xl bg-white transition-all transform -translate-y-5 opacity-0 duration-300`;
            toast.innerHTML = `
                <div class="w-10 h-10 rounded-xl ${iconBoxClass} flex items-center justify-center shrink-0">
                    <i class="ph ${iconClass} text-xl"></i>
                </div>
                <div>
                    <h4 class="font-black text-sm text-admin-textMain">${title}</h4>
                    <p class="text-xs text-admin-textMuted mt-0.5">${message}</p>
                </div>
            `;

            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('-translate-y-5', 'opacity-0'), 50);

            setTimeout(() => {
                toast.classList.add('-translate-y-5', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        document.getElementById('admin-reset-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!waParam || !tokenParam) {
                showNotification('Tautan Tidak Valid', 'Parameter WA atau token tidak ditemukan di URL.', 'error');
                return;
            }

            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;

            if (password.length < 6) {
                showNotification('Sandi Terlalu Pendek', 'Kata sandi baru minimal harus 6 karakter.', 'error');
                return;
            }

            if (password !== passwordConfirmation) {
                showNotification('Sandi Tidak Cocok', 'Konfirmasi kata sandi tidak sama dengan kata sandi baru.', 'error');
                return;
            }

            const btn = document.getElementById('btn-submit');
            const originalHtml = btn.innerHTML;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-base"></i><span>Menyimpan Sandi...</span>';

            try {
                const response = await fetch('/api/auth/admin/reset-password', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        whatsapp_number: waParam,
                        token: tokenParam,
                        password: password,
                        password_confirmation: passwordConfirmation
                    })
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    const errorMsg = result.message || (result.errors ? Object.values(result.errors)[0][0] : 'Gagal mengubah kata sandi.');
                    showNotification('Reset Gagal', errorMsg, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    return;
                }

                showNotification('Sandi Berhasil Diubah!', result.message || 'Silakan masuk menggunakan kata sandi baru.', 'success');
                setTimeout(() => {
                    window.location.href = result.redirect || "{{ route('admin.login') }}";
                }, 1500);
            } catch (err) {
                showNotification('Koneksi Bermasalah', 'Tidak dapat terhubung ke server. Coba lagi.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    </script>
</body>
</html>
