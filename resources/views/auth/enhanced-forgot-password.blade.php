@extends('layouts.guest')

@section('title', 'Lupa Password')
@section('subtitle', 'Reset password Anda')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo class="h-20" />
            </a>
        </div>

        <!-- Auth Card -->
        <div class="w-[calc(100%-30px)] sm:w-full sm:max-w-md px-6 py-8 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-white mb-2">Lupa Password?</h2>
                <p class="text-gray-400 text-sm">
                    Masukkan email Anda dan kami akan mengirimkan link untuk mereset password Anda.
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg text-blue-400" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Alamat Email')" class="text-gray-300 font-medium mb-2" />
                    <x-text-input 
                        id="email" 
                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required 
                        autofocus 
                        placeholder="Masukkan email Anda"
                        ::readonly="loading"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                    
                    <!-- Email Validation Info -->
                    <div class="mt-2 text-xs text-gray-400">
                        <p>Pastikan email Anda valid dan dapat menerima email.</p>
                    </div>
                </div>

                <!-- Rate Limit Warning -->
                @if(session('rate_limit_warning'))
                    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3">
                        <p class="text-yellow-400 text-sm">
                            <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            {{ session('rate_limit_warning') }}
                        </p>
                    </div>
                @endif

                <div class="flex items-center justify-between mt-8">
                    <a class="text-sm text-cyan-400 hover:text-cyan-300 transition-colors duration-200" href="{{ route('login') }}">
                        {{ __('Kembali ke Login') }}
                    </a>

                    <style>
                        /* From Uiverse.io by satyamchaudharydev */ 
                        .button-shine { 
                            position: relative; 
                            transition: all 0.3s ease-in-out; 
                            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2); 
                            padding-block: 0.5rem; 
                            padding-inline: 1.25rem; 
                            background: linear-gradient(to right, #06b6d4, #2563eb); /* cyan-500 to blue-600 */
                            border-radius: 6px; 
                            display: flex; 
                            align-items: center; 
                            justify-content: center; 
                            color: #ffff; 
                            gap: 10px; 
                            font-weight: bold; 
                            border: 3px solid #ffffff4d; 
                            outline: none; 
                            overflow: hidden; 
                            font-size: 15px; 
                            cursor: pointer; 
                        } 
                        
                        .button-shine .icon { 
                            width: 24px; 
                            height: 24px; 
                            transition: all 0.3s ease-in-out; 
                        } 
                        
                        .button-shine:hover { 
                            transform: scale(1.05); 
                            border-color: #fff9; 
                        } 
                        
                        .button-shine:hover .icon { 
                            transform: translate(4px); 
                        } 
                        
                        .button-shine:hover::before { 
                            animation: shine 1.5s ease-out infinite; 
                        } 
                        
                        .button-shine::before { 
                            content: ""; 
                            position: absolute; 
                            width: 100px; 
                            height: 100%; 
                            background-image: linear-gradient( 
                                120deg, 
                                rgba(255, 255, 255, 0) 30%, 
                                rgba(255, 255, 255, 0.8), 
                                rgba(255, 255, 255, 0) 70% 
                            ); 
                            top: 0; 
                            left: -100px; 
                            opacity: 0.6; 
                        } 
                        
                        @keyframes shine { 
                            0% { left: -100px; } 
                            60% { left: 100%; } 
                            100% { left: 100%; } 
                        }

                        /* Disabled state */
                        .button-shine:disabled {
                            opacity: 0.7;
                            cursor: not-allowed;
                            transform: none;
                        }
                    </style>

                    <button class="button-shine" type="submit" :disabled="loading">
                        <span x-show="!loading">{{ __('Kirim Link Reset') }}</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengirim...
                        </span>
                        <div class="icon" x-show="!loading">
                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z" fill="currentColor"></path>
                            </svg>
                        </div>
                    </button>
                </div>
            </form>

            <div class="mt-6 border-t border-gray-700/50 pt-4 text-center">
                <p class="text-xs text-gray-500">
                    {{ __('Masih mengalami masalah?') }} 
                    <a href="mailto:{{ config('mail.from.address') }}" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                        {{ __('Hubungi Support') }}
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection