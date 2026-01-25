@extends('layouts.guest')

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

                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 focus:ring-offset-gray-900 shadow-lg hover:shadow-cyan-500/25"
                    >
                        {{ __('Email Reset Link') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
