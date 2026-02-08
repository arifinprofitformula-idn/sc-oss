@extends('layouts.guest')

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
                <h2 class="text-2xl font-bold text-white mb-2">Forgot Password?</h2>
                <p class="text-gray-400 text-sm">
                    {{ __('No problem. Just let us know your email address and we will email you a password reset link.') }}
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg text-blue-400" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email Address')" class="text-gray-300 font-medium mb-2" />
                    <x-text-input 
                        id="email" 
                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required 
                        autofocus 
                        placeholder="Enter your email address"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                </div>

                <div class="flex items-center justify-between mt-8">
                    <a class="text-sm text-cyan-400 hover:text-cyan-300 transition-colors duration-200" href="{{ route('login') }}">
                        {{ __('Back to Login') }}
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
                            0% { 
                                left: -100px; 
                            } 
                        
                            60% { 
                                left: 100%; 
                            } 
                        
                            to { 
                                left: 100%; 
                            } 
                        } 
                    </style>
                    <button type="submit" class="button-shine">
                        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ __('Email Reset Link') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
