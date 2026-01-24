<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans antialiased" x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') ? localStorage.getItem('sidebarOpen') === 'true' : window.innerWidth >= 1024 }" x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val))">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.sidebar')

            <div class="flex-1 flex flex-col min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-0'">
                <!-- Topbar & Page Heading -->
                @include('layouts.topbar', ['header' => $header ?? null])

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-toast-popup />
        <x-cart-sidebar />
        @stack('scripts')
        <script>
            (function(){
                var onReady=function(){
                    document.querySelectorAll('[x-cloak]').forEach(function(el){
                        el.removeAttribute('x-cloak');
                    });
                };
                if(document.readyState==='loading'){
                    document.addEventListener('DOMContentLoaded', onReady);
                } else {
                    onReady();
                }
            })();
        </script>
    </body>
</html>
