@extends('layouts.guest')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </div>

        <!-- Auth Card -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-500 via-orange-500 to-yellow-500"></div>
            
            <div class="mb-6 text-center">
                <div class="mx-auto w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-white mb-2">Akun Sedang Diverifikasi</h2>
                
                <div class="text-gray-300 text-sm mb-6 leading-relaxed">
                    Terima kasih telah mendaftar di EPI Order & Sales System. Saat ini akun Anda sedang dalam proses peninjauan oleh tim Admin kami.
                </div>

                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700/50 mb-6 text-left">
                    <h3 class="text-sm font-semibold text-gray-200 mb-2">Informasi Penting:</h3>
                    <ul class="text-xs text-gray-400 space-y-2 list-disc list-inside">
                        <li>Proses verifikasi biasanya memakan waktu 1x24 jam kerja.</li>
                        <li>Pastikan data profil dan bukti pembayaran Anda sudah benar.</li>
                        <li>Anda akan menerima notifikasi email setelah akun diaktifkan.</li>
                    </ul>
                </div>

                <div class="text-xs text-gray-500 mb-6">
                    Butuh bantuan mendesak? Hubungi kami di <br>
                    <a href="https://wa.me/6281234567890" target="_blank" class="text-blue-400 hover:text-blue-300 transition-colors">WhatsApp Support</a>
                </div>
            </div>

            <div class="mt-6 flex flex-col space-y-4">
                <form method="POST" action="{{ route('logout') }}" class="text-center">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-700 rounded-md font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        {{ __('Keluar (Logout)') }}
                    </button>
                </form>
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} PT Emas Perak Indonesia. All rights reserved.
        </div>
    </div>
@endsection
