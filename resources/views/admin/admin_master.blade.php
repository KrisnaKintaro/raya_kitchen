<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="app-url" content="{{ url('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin - Raya Kitchen')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Phosphor Icons (Vektor Profesional) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Interaksi Dasar */
        .interactive-element {
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease, box-shadow 0.2s ease;
        }
        .interactive-element:active {
            transform: scale(0.96) translateY(2px);
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            sideBg: '#9368B7',
                            sideHover: '#A57BCB',
                            sideActive: '#B38CDA',

                            // Palet Navbar & Background
                            navBg: '#E8D5F6',
                            bg: '#F5F2F8',    

                            // Aksen & Teks
                            accent: '#E4CCFF',
                            textMain: '#2D2A32',
                            textMuted: '#716E77',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    @yield('css')
</head>
<body class="bg-admin-bg text-admin-textMain font-sans antialiased overflow-hidden selection:bg-admin-sideHover selection:text-white">

    <div class="flex h-screen overflow-hidden">

        <!-- Escape Hatch: Mobile Backdrop -->
        <div id="sidebar-backdrop" class="fixed inset-0 bg-admin-sideBg/50 backdrop-blur-sm z-40 lg:hidden opacity-0 pointer-events-none transition-opacity duration-300 ease-in-out"></div>

        <!-- Sidebar Component -->
        @include('admin.layout.sidebar')

        <!-- Main Workspace (Scrollable Area) -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden hide-scrollbar" id="main-content">

            <!-- Navbar Component -->
            @include('admin.layout.navbar')

            <!-- Global AJAX Loader -->
            <div id="global-loader" class="absolute top-0 left-0 w-full h-1.5 z-50 opacity-0 pointer-events-none transition-opacity duration-200">
                <div class="h-full bg-admin-sideActive w-full origin-left scale-x-0 transition-transform duration-500 ease-out shadow-[0_0_12px_#B38CDA]" id="loader-bar"></div>
            </div>

            <!-- Injected Content -->
            <main class="w-full px-4 lg:px-6 py-6 lg:py-8 max-w-[1440px] mx-auto opacity-0 translate-y-4 animate-[fadeInUp_0.4s_ease-out_forwards]">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            const sidebar = $('#sidebar');
            const backdrop = $('#sidebar-backdrop');
            let isSidebarOpen = false;

            function toggleSidebar(forceState = null) {
                isSidebarOpen = forceState !== null ? forceState : !isSidebarOpen;
                if (isSidebarOpen) {
                    sidebar.removeClass('-translate-x-full');
                    backdrop.removeClass('opacity-0 pointer-events-none');
                } else {
                    sidebar.addClass('-translate-x-full');
                    backdrop.addClass('opacity-0 pointer-events-none');
                }
            }

            $(document).on('click', '#btn-hamburger', () => toggleSidebar(true));
            $(document).on('click', '#btn-close-sidebar, #sidebar-backdrop', () => toggleSidebar(false));

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (isSidebarOpen) toggleSidebar(false);
                }
            });

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            $(document).ajaxStart(function() {
                $('#global-loader').removeClass('opacity-0 pointer-events-none');
                $('#loader-bar').css('transform', 'scaleX(0.7)');
            }).ajaxStop(function() {
                $('#loader-bar').css('transform', 'scaleX(1)');
                setTimeout(() => {
                    $('#global-loader').addClass('opacity-0 pointer-events-none');
                    setTimeout(() => $('#loader-bar').css('transform', 'scaleX(0)'), 200);
                }, 300);
            });
        });
    </script>

    <style>
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    @stack('script')
</body>
</html>
