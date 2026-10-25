<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="app-url" content="{{ url('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Raya Kitchen - Bakery Fresh & Yummy')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        html::-webkit-scrollbar, body::-webkit-scrollbar {
            display: none;
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        buyer: {
                            primary: '#C099CE',
                            hover: '#9B72AB',
                            bg: '#F8F7FA',
                            surface: '#FFFFFF',
                            textPrimary: '#2D2A32',
                            textSecondary: '#716E77'
                        }
                    }
                }
            }
        }
    </script>

    @yield('css')
</head>
<body class="flex flex-col min-h-screen bg-buyer-bg text-buyer-textPrimary font-sans antialiased">

    @include('pembeli.layout.navbar')

    <main class="flex-grow container mx-auto px-4 py-8">
        @yield('content')
    </main>

    @include('pembeli.layout.footer')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tangkap status login dari PHP dan ubah jadi boolean JS murni
            const isLoggedIn = "{{ auth()->check() }}" === "1";

            // Gunakan logika IF murni pengganti auth blade (simbol at dihapus biar ga error)
            if (isLoggedIn) {
                fetchNotifications();

                function fetchNotifications() {
                    $.ajax({
                        url: '/api/pembeli/notifications',
                        type: 'GET',
                        success: function(res) {
                            if(res.status === 'success') {
                                let count = res.data.unread_count;
                                let notifs = res.data.notifications;

                                if(count > 0) {
                                    $('#notif-badge').text(count).removeClass('hidden');
                                } else {
                                    $('#notif-badge').addClass('hidden');
                                }

                                let html = '';
                                if(notifs.length === 0) {
                                    html = '<div class="p-4 text-center text-gray-400 text-xs font-bold">Belum ada notifikasi</div>';
                                } else {
                                    notifs.forEach(notif => {
                                        let readClass = notif.is_read ? 'bg-white' : 'bg-purple-50';
                                        html += `
                                            <div class="notif-item p-3 mb-1 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors ${readClass}" data-id="${notif.id}" data-order="${notif.order_number}">
                                                <p class="text-sm font-black text-gray-800 mb-0.5">${notif.title}</p>
                                                <p class="text-xs text-gray-500 font-medium leading-tight">${notif.message}</p>
                                            </div>
                                        `;
                                    });
                                }
                                $('#notif-list').html(html);
                            }
                        }
                    });
                }

                // Klik 1 notif = Redirect ke Nota & Tandai dibaca
                $(document).on('click', '.notif-item', function() {
                    let id = $(this).data('id');
                    let orderNum = $(this).data('order');

                    $.post('/api/pembeli/notifications/read', { id: id, _token: $('meta[name="csrf-token"]').attr('content') }, function() {
                        window.location.href = '/nota?order_number=' + orderNum;
                    });
                });

                // Tandai semua dibaca
                $('#btn-read-all').click(function(e) {
                    e.stopPropagation();
                    $.post('/api/pembeli/notifications/read', { _token: $('meta[name="csrf-token"]').attr('content') }, function() {
                        fetchNotifications();
                    });
                });
            }
        });
    </script>
    @stack('script')
</body>
</html>
