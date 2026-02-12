@extends('layouts.guest')

@section('title', 'Reset Password')
@section('subtitle', 'Buat Password Baru')

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
                <h2 class="text-2xl font-bold text-white mb-2">Reset Password</h2>
                <p class="text-gray-400 text-sm">
                    Buat password baru yang kuat untuk akun Anda.
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg text-blue-400" :status="session('status')" />

            <form method="POST" action="{{ route('password.store.enhanced') }}" class="space-y-6" x-data="{ 
                password: '', 
                passwordConfirmation: '', 
                get passwordStrength() {
                    if (!this.password) return 0;
                    let strength = 0;
                    if (this.password.length >= 8) strength += 25;
                    if (this.password.length >= 12) strength += 10;
                    if (/[a-z]/.test(this.password)) strength += 15;
                    if (/[A-Z]/.test(this.password)) strength += 15;
                    if (/\d/.test(this.password)) strength += 15;
                    if (/[@$!%*?&]/.test(this.password)) strength += 20;
                    return Math.min(strength, 100);
                },
                get passwordStrengthColor() {
                    if (this.passwordStrength === 0) return 'bg-gray-600';
                    if (this.passwordStrength < 50) return 'bg-red-500';
                    if (this.passwordStrength < 80) return 'bg-yellow-500';
                    return 'bg-green-500';
                },
                get passwordStrengthText() {
                    if (this.passwordStrength === 0) return '';
                    if (this.passwordStrength < 50) return 'Lemah';
                    if (this.passwordStrength < 80) return 'Sedang';
                    return 'Kuat';
                },
                get passwordsMatch() {
                    return this.password && this.passwordConfirmation && this.password === this.passwordConfirmation;
                },
                get canSubmit() {
                    return this.password && this.passwordConfirmation && this.passwordsMatch && this.passwordStrength >= 80;
                }
            }" @submit="if (!canSubmit) { event.preventDefault(); }">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <!-- New Password -->
                <div>
                    <x-input-label for="password" :value="__('Password Baru')" class="text-gray-300 font-medium mb-2" />
                    <div class="relative">
                        <x-text-input 
                            id="password" 
                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent pr-10" 
                            type="password" 
                            name="password" 
                            required 
                            autocomplete="new-password" 
                            placeholder="Masukkan password baru"
                            x-model="password"
                        />
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" @click="let input = document.getElementById('password'); input.type = input.type === 'password' ? 'text' : 'password'">
                            <svg x-show="document.getElementById('password').type === 'password'" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="document.getElementById('password').type !== 'password'" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                    
                    <!-- Password Strength Indicator -->
                    <div class="mt-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-gray-400">Kekuatan Password</span>
                            <span class="text-xs font-medium" 
                                  :class="passwordStrength === 0 ? 'text-gray-400' : 
                                         passwordStrength < 50 ? 'text-red-400' : 
                                         passwordStrength < 80 ? 'text-yellow-400' : 'text-green-400'"
                                  x-text="passwordStrengthText"></span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-300" 
                                 :class="passwordStrengthColor"
                                 :style="`width: ${passwordStrength}%`"></div>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="mt-3 text-xs text-gray-400 space-y-1">
                        <div class="flex items-center" :class="password.length >= 8 ? 'text-green-400' : 'text-gray-400'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="password.length >= 8">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="password.length < 8">
                                <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                            </svg>
                            Minimal 8 karakter
                        </div>
                        <div class="flex items-center" :class="/[a-z]/.test(password) ? 'text-green-400' : 'text-gray-400'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="/[a-z]/.test(password)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!/[a-z]/.test(password)">
                                <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                            </svg>
                            Mengandung huruf kecil
                        </div>
                        <div class="flex items-center" :class="/[A-Z]/.test(password) ? 'text-green-400' : 'text-gray-400'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="/[A-Z]/.test(password)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!/[A-Z]/.test(password)">
                                <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                            </svg>
                            Mengandung huruf besar
                        </div>
                        <div class="flex items-center" :class="/\d/.test(password) ? 'text-green-400' : 'text-gray-400'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="/\d/.test(password)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!/\d/.test(password)">
                                <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                            </svg>
                            Mengandung angka
                        </div>
                        <div class="flex items-center" :class="/[@$!%*?&]/.test(password) ? 'text-green-400' : 'text-gray-400'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="/[@$!%*?&]/.test(password)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!/[@$!%*?&]/.test(password)">
                                <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                            </svg>
                            Mengandung karakter spesial
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" class="text-gray-300 font-medium mb-2" />
                    <div class="relative">
                        <x-text-input 
                            id="password_confirmation" 
                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent pr-10" 
                            type="password" 
                            name="password_confirmation" 
                            required 
                            autocomplete="new-password" 
                            placeholder="Konfirmasi password baru"
                            x-model="passwordConfirmation"
                        />
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" @click="let input = document.getElementById('password_confirmation'); input.type = input.type === 'password' ? 'text' : 'password'">
                            <svg x-show="document.getElementById('password_confirmation').type === 'password'" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="document.getElementById('password_confirmation').type !== 'password'" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
                    
                    <!-- Match Indicator -->
                    <div class="mt-2 flex items-center" :class="passwordsMatch ? 'text-green-400' : 'text-gray-400'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="passwordsMatch">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!passwordsMatch">
                            <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                        </svg>
                        <span class="text-xs" x-text="passwordsMatch ? 'Password cocok' : 'Password belum cocok'"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8">
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 focus:ring-offset-gray-900 shadow-lg hover:shadow-cyan-500/25 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        :disabled="!canSubmit"
                    >
                        Reset Password
                    </button>
                </div>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300 transition-colors duration-200 font-semibold text-sm underline">
                        Kembali ke login
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection