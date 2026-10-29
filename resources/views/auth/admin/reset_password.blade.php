<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <form id="admin-reset-form" class="space-y-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMain mb-1.5">Kata Sandi Baru</label>
                    <div class="relative">
                        <input type="password" required placeholder="••••••••" class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-purple-100 bg-admin-bg text-sm font-semibold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg focus:bg-white transition-all">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted hover:text-admin-textMain">
                            <i class="ph ph-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-admin-textMain mb-1.5">Konfirmasi Sandi Baru</label>
                    <div class="relative">
                        <input type="password" required placeholder="••••••••" class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-purple-100 bg-admin-bg text-sm font-semibold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideBg focus:bg-white transition-all">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted hover:text-admin-textMain">
                            <i class="ph ph-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 rounded-2xl bg-admin-sideBg hover:bg-admin-sideHover text-white font-black text-sm shadow-lg shadow-purple-900/15 transition-all flex items-center justify-center gap-2 mt-2">
                    <i class="ph ph-check-circle text-base"></i>
                    <span>Simpan Sandi Baru</span>
                </button>
            </form>
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

        function showNotification(title, message) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border border-admin-accent shadow-2xl bg-white transition-all transform -translate-y-5 opacity-0 duration-300';
            
            toast.innerHTML = `
                <div class="w-10 h-10 rounded-xl bg-admin-navBg text-admin-sideBg flex items-center justify-center shrink-0">
                    <i class="ph ph-check-circle text-xl"></i>
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
            }, 1800);
        }

        document.getElementById('admin-reset-form').addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('Sandi Berhasil Diubah!', 'Silakan masuk menggunakan kata sandi baru.');
            setTimeout(() => {
                window.location.href = "{{ route('admin.login') }}";
            }, 1800);
        });
    </script>
</body>
</html>