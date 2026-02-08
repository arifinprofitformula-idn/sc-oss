<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EPI Order & Sales System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-gray-900 via-blue-900 to-gray-900">
        <div class="relative min-h-screen">
            @yield('content')
            
            <!-- Footer -->
            <div class="absolute bottom-4 w-full text-center z-20 pointer-events-none">
                <p class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} PT Emas Perak Indonesia. All rights reserved.
                </p>
            </div>
        </div>
    </body>
</html>
