@extends('layouts.guest')

@section('title', 'Login')

@section('subtitle', 'Sign in to your account')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen relative z-20" x-data="{ loading: false }">
        <!-- Loader Animation -->
        <x-loader-animation />

        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo class="h-20" />
            </a>
        </div>

        <!-- Auth Card -->
        <div class="w-[calc(100%-30px)] sm:w-full sm:max-w-md px-6 py-8 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-white mb-2">
                    {{ $title ?? 'Welcome Back' }}
                </h2>
                <p class="text-gray-400 text-sm">
                    {{ $subtitle ?? 'Sign in to continue to your account' }}
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg text-blue-400" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6" @submit="loading = true">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-gray-300" />
            <x-text-input 
                id="email" 
                class="block mt-2 w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                autocomplete="username" 
                placeholder="Enter your email"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400" />
        </div>

        <!-- Password -->
        <div class="mt-6">
            <x-input-label for="password" :value="__('Password')" class="text-gray-300" />
            <x-text-input 
                id="password" 
                class="block mt-2 w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                type="password"
                name="password"
                required 
                autocomplete="current-password" 
                placeholder="Enter your password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-6">
            <label for="remember_me" class="inline-flex items-center">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    class="rounded bg-gray-800 border-gray-700 text-cyan-500 shadow-sm focus:ring-cyan-500 focus:ring-offset-gray-900" 
                    name="remember"
                >
                <span class="ms-2 text-sm text-gray-400 hover:text-gray-300 cursor-pointer">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request.enhanced'))
                <a class="text-sm text-cyan-400 hover:text-cyan-300 transition-colors duration-200 underline" href="{{ route('password.request.enhanced') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <div class="mt-8">
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
            <button type="submit" class="button-shine w-full">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                {{ __('Sign In') }}
            </button>
        </div>

        <!-- Register Silverchannel Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-400">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register.silver') }}" class="text-cyan-400 hover:text-cyan-300 transition-colors duration-200 underline font-medium">
                    {{ __('Register as Silverchannel') }}
                </a>
            </p>
        </div>
    </form>
        </div>
    </div>
@endsection
