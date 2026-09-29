<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>@yield('title', 'Raya Kitchen - Bakery Fresh & Yummy')</title>

    <script src="https://cdn.tailwindcss.com"></script>

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

    @stack('scripts')
</body>
</html>
