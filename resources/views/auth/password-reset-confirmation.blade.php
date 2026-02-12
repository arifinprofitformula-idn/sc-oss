@extends('layouts.guest')

@section('title', 'Forgot Password - Processing')
@section('subtitle', 'Permintaan sedang diproses')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo class="h-20" />
            </a>
        </div>

        <!-- Processing Card -->
        <div class="w-[calc(100%-30px)] sm:w-full sm:max-w-md px-6 py-8 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
            <div class="text-center">
                <!-- Status Icon -->
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-500/10 mb-4">
                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>

                <!-- Success Message -->
                <h2 class="text-2xl font-bold text-white mb-2">Permintaan Diproses</h2>
                <p class="text-gray-400 text-sm mb-6">
                    @if(session('status'))
                        {{ session('status') }}
                    @else
                        Jika email tersebut terdaftar di sistem kami, kami akan mengirimkan link reset password.
                    @endif
                </p>

                <!-- Instructions -->
                <div class="bg-gray-800/50 rounded-lg p-4 mb-6">
                    <h3 class="text-white font-semibold mb-2">Langkah Selanjutnya:</h3>
                    <ul class="text-gray-400 text-sm space-y-1 text-left">
                        <li>• Cek inbox email Anda (termasuk folder spam)</li>
                        <li>• Klik link reset password dalam email</li>
                        <li>• Link akan kadaluarsa dalam 1 jam</li>
                        <li>• Jika tidak menerima email, coba lagi dalam 1 jam</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105">
                        Kembali ke Login
                    </a>
                    
                    <a href="{{ route('password.request') }}" class="inline-flex items-center justify-center w-full bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                        Kirim Ulang Email
                    </a>
                </div>

                <!-- Support -->
                <div class="mt-6 pt-4 border-t border-gray-700/50">
                    <p class="text-xs text-gray-500">
                        Masih mengalami masalah? 
                        <a href="mailto:{{ config('mail.from.address') }}" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                            Hubungi Support
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection