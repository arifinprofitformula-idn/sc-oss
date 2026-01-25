@extends('layouts.guest')

@section('title', 'Reset Password')

@section('subtitle', 'Create a new password')

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
                    {{ $title ?? 'Reset Password' }}
                </h2>
                <p class="text-gray-400 text-sm">
                    {{ $subtitle ?? 'Create a new password' }}
                </p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-gray-300" />
            <x-text-input 
                id="email" 
                class="block mt-2 w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                type="email" 
                name="email" 
                :value="old('email', $request->email)" 
                required 
                autofocus 
                autocomplete="username" 
                placeholder="Enter your email"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400" />
        </div>

        <!-- Password -->
        <div class="mt-6">
            <x-input-label for="password" :value="__('New Password')" class="text-gray-300" />
            <x-text-input 
                id="password" 
                class="block mt-2 w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                type="password" 
                name="password" 
                required 
                autocomplete="new-password" 
                placeholder="Enter new password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-6">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-300" />
            <x-text-input 
                id="password_confirmation" 
                class="block mt-2 w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="Confirm your password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-rose-400" />
        </div>

        <!-- Reset Button -->
        <div class="mt-8">
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 focus:ring-offset-gray-900 shadow-lg hover:shadow-cyan-500/25"
            >
                {{ __('Reset Password') }}
            </button>
        </div>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300 transition-colors duration-200 font-semibold text-sm underline">
                {{ __('Back to login') }}
            </a>
        </div>
    </form>
        </div>
    </div>
@endsection
