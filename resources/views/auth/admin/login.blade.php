<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin - Raya Kitchen</title>
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

    <!-- Tombol Kembali di Pojok Kiri Atas -->
    <div class="absolute top-6 left-6">
        <a href="/" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white hover:bg-admin-navBg text-admin-textMain font-bold text-xs shadow-sm border border-admin-accent/50 transition-all">
            <i class="ph ph-arrow-left text-base"></i> Kembali ke Toko
        </a>
    </div>

    <!-- Container Toast Notifikasi Kustom di ATAS TENGAH -->
    <div id="toast-container" class="fixed top-6 left-1/2 -translate-x-1/2 z-[200] flex flex-col gap-3 pointer-events-none w-full max-w-sm px-4"></div>

    <!-- Card Utama (Single Centered Card) -->
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-admin-accent/50 relative">

        <!-- Header Banner dengan Gambar Suasana Dapur -->
        <div class="relative bg-slate-900 p-8 text-center text-white overflow-hidden">
            <div class="absolute inset-0">
                <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800" alt="Dapur Raya Kitchen" class="w-full h-full object-cover opacity-45">
                <div class="absolute inset-0 bg-gradient-to-b from-[#9368B7]/80 via-[#9368B7]/90 to-[#734A97]"></div>
            </div>

            <div class="absolute top-4 right-4 z-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 backdrop-blur-md text-white border border-white/25">
                    <i class="ph ph-shield-check text-xs"></i> Restricted Area
                </span>
            </div>

            <div class="relative z-10">
                <div class="w-16 h-16 bg-white/15 backdrop-blur-md rounded-2xl mx-auto flex items-center justify-center mb-3 border border-white/25 shadow-inner">
                    <i class="ph ph-cooking-pot text-3xl text-white"></i>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">Admin Raya Kitchen</h1>
                <p class="text-xs text-purple-100 mt-1">Portal khusus manajemen operasional internal.</p>
            </div>
        </div>

        <!-- Form Login -->
        <div class="p-8">
            <form id="admin-login-form" class="space-y-4">
                <div>
                    <label for="whatsapp_number" class="block text-xs font-black uppercase tracking-wider text-admin-textMain mb-1.5">Nomor WhatsApp</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-admin-sideBg font-bold text-sm">+62</span>
                        <input id="whatsapp_number" name="whatsapp_number" type="tel" required autocomplete="tel" placeholder="81234567890" class="w-full pl-14 pr-4 py-3.5 rounded-2xl border border-purple-100 bg-admin-bg text-sm font-semibold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg focus:bg-white transition-all">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-black uppercase tracking-wider text-admin-textMain">Password</label>
                        <a href="{{ route('admin.password.request') }}" class="text-xs font-bold text-admin-sideBg hover:underline">Lupa Password?</a>
                    </div>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••" class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-purple-100 bg-admin-bg text-sm font-semibold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg focus:bg-white transition-all">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted hover:text-admin-textMain">
                            <i class="ph ph-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center text-xs text-admin-textMuted pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input id="remember" name="remember" type="checkbox" class="rounded border-purple-300 text-admin-sideBg focus:ring-admin-sideBg w-4 h-4">
                        <span>Ingat perangkat ini</span>
                    </label>
                </div>

                <button id="btn-submit" type="submit" class="w-full py-4 rounded-2xl bg-admin-sideBg hover:bg-admin-sideHover disabled:opacity-60 disabled:cursor-not-allowed text-white font-black text-sm shadow-lg shadow-purple-900/15 transition-all flex items-center justify-center gap-2 mt-2">
                    <span>Masuk Dashboard</span>
                    <i class="ph ph-arrow-right text-base"></i>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-admin-bg text-center">
                <p class="text-xs text-admin-textMuted">Bukan staf admin? <a href="/" class="font-bold text-admin-sideBg hover:underline">Beranda Toko</a></p>
            </div>
        </div>

    </div>

    <script>
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
            }, 3000);
        }

        document.getElementById('admin-login-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('btn-submit');
            const originalHtml = btn.innerHTML;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const payload = {
                whatsapp_number: document.getElementById('whatsapp_number').value.trim(),
                password: document.getElementById('password').value,
                remember: document.getElementById('remember').checked,
                device_name: 'web_admin_blade'
            };

            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-base"></i><span>Memverifikasi...</span>';

            try {
                const response = await fetch('/api/auth/admin/login', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    const errorMsg = result.message || (result.errors ? Object.values(result.errors)[0][0] : 'Gagal masuk ke dashboard.');
                    showNotification('Login Gagal', errorMsg, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    return;
                }

                if (result.token) {
                    localStorage.setItem('admin_token', result.token);
                }

                showNotification('Login Berhasil!', 'Mengarahkan ke Dashboard Finansial...', 'success');
                setTimeout(() => {
                    window.location.href = result.redirect || "{{ route('admin.dashboard-finansial') }}";
                }, 900);
            } catch (err) {
                showNotification('Koneksi Bermasalah', 'Tidak dapat terhubung ke server. Coba lagi.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    </script>
</body>
</html>
