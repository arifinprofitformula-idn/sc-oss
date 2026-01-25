@extends('layouts.guest')

@section('title', 'Login')

@section('subtitle', 'Sign in to your account')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo />
            </a>
        </div>

        <!-- Auth Card -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
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

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
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

            @if (Route::has('password.request'))
                <a class="text-sm text-cyan-400 hover:text-cyan-300 transition-colors duration-200 underline" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <div class="mt-8">
            <x-primary-button class="w-full justify-center">
                {{ __('Sign In') }}
            </x-primary-button>
        </div>
    </form>
        </div>
    </div>
@endsection
