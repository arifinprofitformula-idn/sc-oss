@extends('layouts.guest')

@section('title', 'Reset Password - Invalid')
@section('subtitle', 'Link Tidak Valid')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo class="h-20" />
            </a>
        </div>

        <!-- Invalid Token Card -->
        <div class="w-[calc(100%-30px)] sm:w-full sm:max-w-md px-6 py-8 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
            <div class="text-center">
                <!-- Warning Icon -->
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-amber-500/10 mb-4">
                    <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <!-- Warning Message -->
                <h2 class="text-2xl font-bold text-white mb-2">Link Tidak Valid</h2>
                <p class="text-gray-400 text-sm mb-6">
                    Link reset password yang Anda gunakan tidak valid, telah kadaluarsa, atau sudah digunakan.
                </p>

                <!-- Possible Reasons -->
                <div class="bg-gray-800/50 rounded-lg p-4 mb-6 text-left">
                    <h3 class="text-white font-semibold mb-2">Kemungkinan Penyebab:</h3>
                    <ul class="text-gray-400 text-sm space-y-1">
                        <li>• Link telah kadaluarsa (kadaluarsa dalam 1 jam)</li>
                        <li>• Link sudah pernah digunakan</li>
                        <li>• Link tidak lengkap atau rusak</li>
                        <li>• Permintaan reset password dibatalkan</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <a href="{{ route('password.request') }}" class="inline-flex items-center justify-center w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105">
                        Minta Link Baru
                    </a>
                    
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                        Kembali ke Login
                    </a>
                </div>

                <!-- Support -->
                <div class="mt-6 pt-4 border-t border-gray-700/50">
                    <p class="text-xs text-gray-500">
                        Jika masalah berlanjut, 
                        <a href="mailto:{{ config('mail.from.address') }}" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                            hubungi tim support
                        </a>
                        kami.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection