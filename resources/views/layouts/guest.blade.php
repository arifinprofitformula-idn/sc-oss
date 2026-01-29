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
        <!-- Background Effects -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -inset-10 opacity-20">
                <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-cyan-500 rounded-full mix-blend-soft-light filter blur-xl animate-pulse"></div>
                <div class="absolute top-3/4 right-1/4 w-72 h-72 bg-amber-500 rounded-full mix-blend-soft-light filter blur-xl animate-pulse animation-delay-2000"></div>
                <div class="absolute bottom-1/4 left-1/2 w-72 h-72 bg-blue-600 rounded-full mix-blend-soft-light filter blur-xl animate-pulse animation-delay-4000"></div>
            </div>
        </div>

        <div class="relative min-h-screen">
            @yield('content')
            
            <!-- Footer -->
            <div class="absolute bottom-4 w-full text-center z-20 pointer-events-none">
                <p class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} PT Emas Perak Indonesia. All rights reserved.
                </p>
            </div>
        </div>

        <style>
            .animation-delay-2000 { animation-delay: 2s; }
            .animation-delay-4000 { animation-delay: 4s; }
            @keyframes pulse {
                0%, 100% { opacity: 0.2; transform: scale(1); }
                50% { opacity: 0.3; transform: scale(1.05); }
            }
            .animate-pulse { animation: pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        </style>
    </body>
</html>
