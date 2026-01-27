<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile Silverchannel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('status') === 'profile-details-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Sukses!</strong>
                    <span class="block sm:inline">{{ session('message') ?? 'Data profil berhasil diperbarui.' }}</span>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Akses Dibatasi!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Profile Completeness Progress Bar -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 mb-6" 
                 x-data="{ 
                    progress: {{ $user->profile_completeness }},
                    saveToStorage() {
                        localStorage.setItem('profile_completeness', this.progress);
                    }
                 }"
                 x-init="saveToStorage()"
                 @profile-updated.window="progress = $event.detail.completeness; saveToStorage()">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Kelengkapan Profil</h3>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300" x-text="progress + '%'"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700 overflow-hidden">
                    <div class="h-4 rounded-full transition-all duration-1000 ease-out"
                         :class="{
                             'bg-red-500': progress < 40,
                             'bg-yellow-400': progress >= 40 && progress < 70,
                             'bg-green-500': progress >= 70
                         }"
                         :style="'width: ' + progress + '%'"></div>
                </div>
                <template x-if="progress < 70">
                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-medium text-red-500">Profil Anda belum lengkap!</p>
                        <p>Silakan lengkapi minimal 70% data pribadi, kontak, alamat, dan rekening bank untuk mengakses menu lainnya.</p>
                    </div>
                </template>
                <template x-if="progress >= 70">
                    <div class="mt-3 text-sm text-green-600 dark:text-green-400 font-medium">
                        Profil Anda sudah cukup lengkap. Anda dapat mengakses semua fitur.
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- COLUMN 1: PHOTO & IDENTITY (Standalone - No Form) -->
                <div class="space-y-6 w-full">
                    <!-- Photo & ID Card -->
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-center"
                         x-data="photoProfile()">
                        
                        <header class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4 flex items-center justify-center gap-2">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">FOTO PROFIL</h2>
                        </header>

                        <!-- Photo Upload Section -->
                        <div class="flex flex-col items-center">
                            <div class="relative w-40 h-40 mb-4 group mx-auto">
                                <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-white dark:border-gray-700 shadow-lg bg-gray-100 dark:bg-gray-700 relative">
                                    <!-- Preview Image -->
                                    <img x-show="photoPreview" :src="photoPreview" class="w-full h-full object-cover" style="display: none;">
                                    
                                    <!-- Fallback Icon -->
                                    <div x-show="!photoPreview" class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>

                                    <!-- Overlay Upload Icon -->
                                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 cursor-pointer rounded-full" onclick="document.getElementById('photo').click()">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                </div>
                                <input id="photo" type="file" class="hidden" accept="image/png, image/jpeg, image/jpg, image/webp" @change="validateFile($event)">
                            </div>
                            <p class="text-xs text-gray-500 mb-4">
                                Format: JPG, PNG, WEBP. Max 10MB. Rasio 1:1.
                            </p>
                            
                            <!-- Progress Bar -->
                            <div x-show="uploading" class="w-full max-w-xs mb-2" style="display: none;">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                                </div>
                                <p class="text-xs text-center mt-1 text-gray-600 dark:text-gray-400" x-text="statusText + ' (' + progress + '%)'"></p>
                            </div>
                        </div>

                        <!-- Update Photo Button -->
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" 
                                    :disabled="!photoValid || uploading"
                                    @click="uploadPhoto"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                                
                                <span x-show="!uploading" class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    Update Foto Profil
                                </span>
                                
                                <span x-show="uploading" class="flex items-center gap-2" style="display: none;">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Mengupload...
                                </span>
                            </button>
                            <p x-show="photoError" x-text="photoError" class="text-red-500 text-xs mt-2" style="display: none;"></p>
                            <p x-show="successMessage" x-text="successMessage" class="text-green-500 text-xs mt-2" style="display: none;"></p>
                        </div>

                    <!-- Name & Badge -->
                    <div class="text-center">
                        <h3 class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100 break-words">{{ $user->name }}</h3>
                        <div class="mt-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                {{ $user->silver_channel_id ?? 'Silver Member' }}
                            </span>
                        </div>
                    </div>

                    <!-- Referral Code (Copyable) -->
                    <div class="mt-6 bg-green-50 dark:bg-green-900/20 p-3 rounded-lg border border-green-100 dark:border-green-800 relative group cursor-pointer text-left" onclick="navigator.clipboard.writeText('{{ $user->referral_code }}'); alert('Kode referral disalin!');">
                        <span class="text-xs text-green-600 dark:text-green-400 font-bold uppercase tracking-wider">Kode Referral Saya</span>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-200 mt-1 flex items-center justify-between gap-2">
                            {{ $user->referral_code ?? '-' }}
                            <svg class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>

                    <!-- Referred By -->
                    <div class="mt-4 bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-200 dark:border-gray-600 text-left">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Promoted By</span>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2 truncate" title="{{ $user->referrer ? $user->referrer->name : '-' }}">
                            {{ $user->referrer ? $user->referrer->name : '-' }}
                        </div>
                    </div>

                    <!-- Link Referral SilverChannel -->
                    <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800 text-left"
                         x-data="{
                            link: '{{ route('register.silver', ['ref' => $user->referral_code]) }}',
                            copied: false,
                            copyToClipboard() {
                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(this.link).then(() => {
                                        this.copied = true;
                                        setTimeout(() => this.copied = false, 2000);
                                    }).catch(() => {
                                        this.fallbackCopy();
                                    });
                                } else {
                                    this.fallbackCopy();
                                }
                            },
                            fallbackCopy() {
                                const input = this.$refs.referralInput;
                                input.select();
                                input.setSelectionRange(0, 99999); // Untuk mobile
                                try {
                                    document.execCommand('copy');
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 2000);
                                } catch (err) {
                                    console.error('Gagal menyalin', err);
                                    alert('Gagal menyalin link secara otomatis. Silakan salin manual.');
                                }
                            }
                         }">
                        <label class="block text-xs text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider mb-2">
                            Link Referral SilverChannel
                        </label>
                        
                        <div class="flex rounded-md shadow-sm">
                            <div class="relative flex-grow focus-within:z-10">
                                <input type="text" 
                                       x-ref="referralInput"
                                       x-model="link" 
                                       readonly 
                                       class="focus:ring-blue-500 focus:border-blue-500 block w-full rounded-none rounded-l-md text-xs sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 text-gray-600">
                            </div>
                            <button @click="copyToClipboard()" 
                                    type="button" 
                                    class="-ml-px relative inline-flex items-center space-x-2 px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-r-md text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-600 hover:bg-gray-100 dark:hover:bg-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                
                                <!-- Copy Icon -->
                                <svg x-show="!copied" class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                
                                <!-- Check Icon -->
                                <svg x-show="copied" class="h-4 w-4 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                
                                <span x-text="copied ? 'Tersalin!' : 'Salin'" class="hidden sm:inline">Salin</span>
                            </button>
                        </div>
                    </div>
                    </div>

                    <!-- Password Update -->
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <header class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">UPDATE PASSWORD</h2>
                        </header>
                         @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- COLUMN 2: Personal Data -->
                <div class="space-y-6 w-full">
                    <form method="post" action="{{ route('profile.details.update') }}" class="h-full" x-data="personalDataForm">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="section" value="personal">
                        
                        <!-- Personal Data -->
                        <div class="space-y-6 h-full">
                            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg h-full flex flex-col">
                                <header class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">DATA PRIBADI</h2>
                                    <span x-show="saveStatus === 'saving'" class="ml-auto text-xs text-blue-500 flex items-center">
                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Menyimpan...
                                    </span>
                                    <span x-show="saveStatus === 'saved'" x-transition.leave.duration.2000ms class="ml-auto text-xs text-green-500 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Tersimpan
                                    </span>
                                    <span x-show="saveStatus === 'error'" class="ml-auto text-xs text-red-500">Gagal menyimpan</span>
                                </header>

                                <!-- Provider Change Alert -->
                                <div x-show="providerChanged" style="display: none;" class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 dark:bg-yellow-900/20 dark:border-yellow-600">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400 dark:text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                                <span class="font-bold">Pembaruan Sistem Alamat:</span> Konfigurasi sistem alamat telah berubah. Silakan lengkapi ulang alamat Anda (Provinsi, Kota, Kecamatan, Kelurahan).
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="name" :value="__('Nama Lengkap (Sesuai KTP)')" required />
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            </div>
                                            <x-text-input id="name" name="name" type="text" class="pl-10 block w-full py-3" :value="old('name', $user->name)" required @input.debounce.1000ms="autoSave('name', $event.target.value)" />
                                        </div>
                                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                    </div>
                                    <div>
                                        <x-input-label for="nik" :value="__('NIK')" required />
                                        <div class="relative mt-1" x-data="{ nik: '{{ old('nik', $user->nik) }}' }">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.95 2-2.167 2.25M12 20h0"></path></svg>
                                            </div>
                                            <x-text-input id="nik" name="nik" type="text" 
                                                class="pl-10 block w-full py-3" 
                                                x-model="nik"
                                                @input="nik = nik.replace(/[^0-9]/g, ''); autoSave('nik', nik)"
                                                required />
                                            
                                            <!-- Realtime Validation Feedback -->
                                            <div class="mt-1 absolute right-0 top-3 pr-3 pointer-events-none">
                                                <svg x-show="nik.length === 16" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <svg x-show="nik.length > 0 && nik.length !== 16" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </div>
                                        
                                        <!-- Validation Messages -->
                                        <div x-data="{ nik: '{{ old('nik', $user->nik) }}' }" x-effect="nik = document.getElementById('nik').value">
                                            <p x-show="document.getElementById('nik').value.length > 0 && document.getElementById('nik').value.length < 16" class="text-xs text-yellow-600 mt-1 font-medium">
                                                Angka kurang (Harus 16 digit)
                                            </p>
                                            <p x-show="document.getElementById('nik').value.length > 16" class="text-xs text-red-600 mt-1 font-medium">
                                                Angka lebih (Maksimal 16 digit)
                                            </p>
                                        </div>

                                        <x-input-error class="mt-2" :messages="$errors->get('nik')" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="birth_place" :value="__('Tempat Lahir')" />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                </div>
                                                <x-text-input id="birth_place" name="birth_place" type="text" class="pl-10 block w-full py-3" :value="old('birth_place', $user->birth_place ?? '')" @input.debounce.1000ms="autoSave('birth_place', $event.target.value)" />
                                            </div>
                                        </div>
                                        <div>
                                            <x-input-label for="birth_date" :value="__('Tanggal Lahir')" />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                                <x-text-input id="birth_date" name="birth_date" type="date" class="pl-10 block w-full py-3" :value="old('birth_date', $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '')" @change="autoSave('birth_date', $event.target.value)" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="gender" :value="__('Jenis Kelamin')" required />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                </div>
                                                <select id="gender" name="gender" class="pl-10 mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3" @change="autoSave('gender', $event.target.value)">
                                                    <option value="">Pilih...</option>
                                                    <option value="Laki-laki" {{ old('gender', $user->gender ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                                    <option value="Perempuan" {{ old('gender', $user->gender ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                                </select>
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                                        </div>
                                        <div>
                                            <x-input-label for="religion" :value="__('Agama')" required />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                </div>
                                                <select id="religion" name="religion" class="pl-10 mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3" @change="autoSave('religion', $event.target.value)">
                                                    <option value="">Pilih...</option>
                                                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $rel)
                                                        <option value="{{ $rel }}" {{ old('religion', $user->religion ?? '') == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label for="marital_status" :value="__('Status Perkawinan')" />
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                            </div>
                                            <select id="marital_status" name="marital_status" class="pl-10 mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3" @change="autoSave('marital_status', $event.target.value)">
                                                <option value="">Pilih...</option>
                                                @foreach(['Belum Menikah', 'Menikah', 'Cerai Hidup', 'Cerai Mati'] as $status)
                                                    <option value="{{ $status }}" {{ old('marital_status', $user->marital_status ?? '') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label for="job" :value="__('Pekerjaan')" required />
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <x-text-input id="job" name="job" type="text" class="pl-10 mt-1 block w-full py-3" :value="old('job', $user->job ?? '')" required @input.debounce.1000ms="autoSave('job', $event.target.value)" />
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                    <div class="mb-4">
                                            <x-input-label for="address" :value="__('Alamat Lengkap (Sesuai KTP)')" required />
                                            <div class="relative mt-1">
                                                <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                                </div>
                                                <textarea id="address" name="address" 
                                                    class="pl-10 mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3" 
                                                    rows="3" 
                                                    required
                                                    @input.debounce.1000ms="autoSave('address', $event.target.value)"
                                                >{{ old('address', $user->address ?? '') }}</textarea>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1 h-5">
                                                <span x-show="saveStatus === 'saving'" class="text-xs text-blue-500 flex items-center">
                                                    <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    Menyimpan...
                                                </span>
                                                <span x-show="saveStatus === 'saved'" x-transition.leave.duration.2000ms class="text-xs text-green-500 flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Tersimpan
                                                </span>
                                                <span x-show="saveStatus === 'error'" class="text-xs text-red-500">Gagal menyimpan</span>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <x-input-label for="province_id" :value="__('Provinsi')" required />
                                            <div class="relative mt-1"
                                                x-data="searchableSelect({
                                                    options: provinces,
                                                    value: selectedProvince,
                                                    valueKey: 'province_id',
                                                    labelKey: 'province',
                                                    placeholder: 'Pilih Provinsi',
                                                    loading: loadingProvinces,
                                                    disabled: false,
                                                    onChange: (val) => { selectedProvince = val; onProvinceChange(); }
                                                })" 
                                                x-effect="updateOptions(provinces); loading = loadingProvinces; value = selectedProvince">
                                                
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </div>

                                                <button type="button" @click="toggle()" :disabled="disabled" 
                                                        class="relative w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3 pl-10 pr-10 text-left cursor-default sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <div x-show="isOpen" @click.away="close()" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                        <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Cari Provinsi...">
                                                    </div>
                                                    <ul class="max-h-48 overflow-y-auto">
                                                        <template x-for="option in filteredOptions" :key="option[valueKey]">
                                                            <li @click="select(option)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 dark:text-white hover:bg-indigo-600 hover:text-white">
                                                                <span :class="{'font-semibold': value == option[valueKey], 'font-normal': value != option[valueKey]}" class="block truncate" x-text="option[labelKey]"></span>
                                                                <span x-show="value == option[valueKey]" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white">
                                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                        </template>
                                                        <li x-show="filteredOptions.length === 0" class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-sm">
                                                            <span x-show="!loading">Tidak ditemukan</span>
                                                            <span x-show="loading">Memuat data...</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <input type="hidden" name="province_id" :value="value">
                                                <input type="hidden" name="province_name" :value="selectedProvinceName">
                                            </div>
                                            <div x-show="loadingProvinces" class="text-xs text-indigo-500 mt-1 flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Memuat provinsi...
                                            </div>
                                            <p x-show="errorProvinces" x-text="errorProvinces" class="text-xs text-red-500 mt-1"></p>
                                        </div>

                                        <div class="mb-4">
                                            <x-input-label for="city_id" :value="__('Kota/Kabupaten')" required />
                                            <div class="relative mt-1"
                                                x-data="searchableSelect({
                                                    options: cities,
                                                    value: selectedCity,
                                                    valueKey: 'city_id',
                                                    labelKey: 'city_name',
                                                    placeholder: 'Pilih Kota/Kabupaten',
                                                    loading: loadingCities,
                                                    disabled: !selectedProvince,
                                                    onChange: (val) => { selectedCity = val; onCityChange(); }
                                                })" 
                                                x-effect="updateOptions(cities); loading = loadingCities; value = selectedCity; disabled = !selectedProvince">
                                                
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                </div>

                                                <button type="button" @click="toggle()" :disabled="disabled" 
                                                        class="relative w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3 pl-10 pr-10 text-left cursor-default sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <div x-show="isOpen" @click.away="close()" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                        <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Cari Kota/Kabupaten...">
                                                    </div>
                                                    <ul class="max-h-48 overflow-y-auto">
                                                        <template x-for="option in filteredOptions" :key="option[valueKey]">
                                                            <li @click="select(option)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 dark:text-white hover:bg-indigo-600 hover:text-white">
                                                                <span :class="{'font-semibold': value == option[valueKey], 'font-normal': value != option[valueKey]}" class="block truncate" x-text="`${option.type ? option.type + ' ' : ''}${option[labelKey]}`"></span>
                                                                <span x-show="value == option[valueKey]" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white">
                                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                        </template>
                                                        <li x-show="filteredOptions.length === 0" class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-sm">
                                                            <span x-show="!loading">Tidak ditemukan</span>
                                                            <span x-show="loading">Memuat data...</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <input type="hidden" name="city_id" :value="value">
                                            </div>
                                            <input type="hidden" name="city_name" :value="selectedCityName">
                                            <div x-show="loadingCities" class="text-xs text-indigo-500 mt-1 flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Memuat kota...
                                            </div>
                                            <p x-show="errorCities" x-text="errorCities" class="text-xs text-red-500 mt-1"></p>
                                        </div>

                                        <div class="mb-4">
                                            <x-input-label for="subdistrict_id" :value="__('Kecamatan')" />
                                            <div class="relative mt-1"
                                                x-data="searchableSelect({
                                                    options: subdistricts,
                                                    value: selectedSubdistrict,
                                                    valueKey: 'subdistrict_id',
                                                    labelKey: 'subdistrict_name',
                                                    placeholder: 'Pilih Kecamatan',
                                                    loading: loadingSubdistricts,
                                                    disabled: !selectedCity,
                                                    onChange: (val) => { selectedSubdistrict = val; onSubdistrictChange(); }
                                                })" 
                                                x-effect="updateOptions(subdistricts); loading = loadingSubdistricts; value = selectedSubdistrict; disabled = !selectedCity">
                                                
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                </div>

                                                <button type="button" @click="toggle()" :disabled="disabled" 
                                                        class="relative w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3 pl-10 pr-10 text-left cursor-default sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <div x-show="isOpen" @click.away="close()" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                        <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Cari Kecamatan...">
                                                    </div>
                                                    <ul class="max-h-48 overflow-y-auto">
                                                        <template x-for="option in filteredOptions" :key="option[valueKey]">
                                                            <li @click="select(option)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 dark:text-white hover:bg-indigo-600 hover:text-white">
                                                                <span :class="{'font-semibold': value == option[valueKey], 'font-normal': value != option[valueKey]}" class="block truncate" x-text="option[labelKey]"></span>
                                                                <span x-show="value == option[valueKey]" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white">
                                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                        </template>
                                                        <li x-show="filteredOptions.length === 0" class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-sm">
                                                            <span x-show="!loading">Tidak ditemukan</span>
                                                            <span x-show="loading">Memuat data...</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <input type="hidden" name="subdistrict_id" :value="value">
                                                <input type="hidden" name="subdistrict_name" :value="selectedSubdistrictName">
                                            </div>
                                            <div x-show="loadingSubdistricts" class="text-xs text-indigo-500 mt-1 flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Memuat kecamatan...
                                            </div>
                                            <p x-show="errorSubdistricts" x-text="errorSubdistricts" class="text-xs text-red-500 mt-1"></p>
                                        </div>

                                        <!-- Village / Kelurahan (Only for API ID) -->
                                        <div class="mb-4" x-show="addressProvider === 'api_id'" style="display: none;">
                                            <x-input-label for="village_id" :value="__('Kelurahan / Desa')" />
                                            <div class="relative mt-1"
                                                x-data="searchableSelect({
                                                    options: villages,
                                                    value: selectedVillage,
                                                    valueKey: 'village_id',
                                                    labelKey: 'village_name',
                                                    placeholder: 'Pilih Kelurahan',
                                                    loading: loadingVillages,
                                                    disabled: !selectedSubdistrict,
                                                    onChange: (val) => { selectedVillage = val; updateVillageName(); autoSave('village_id', selectedVillage); }
                                                })" 
                                                x-effect="updateOptions(villages); loading = loadingVillages; value = selectedVillage; disabled = !selectedSubdistrict">
                                                
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                </div>

                                                <button type="button" @click="toggle()" :disabled="disabled" 
                                                        class="relative w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3 pl-10 pr-10 text-left cursor-default sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <div x-show="isOpen" @click.away="close()" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                        <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Cari Kelurahan...">
                                                    </div>
                                                    <ul class="max-h-48 overflow-y-auto">
                                                        <template x-for="option in filteredOptions" :key="option[valueKey]">
                                                            <li @click="select(option)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 dark:text-white hover:bg-indigo-600 hover:text-white">
                                                                <span :class="{'font-semibold': value == option[valueKey], 'font-normal': value != option[valueKey]}" class="block truncate" x-text="option[labelKey]"></span>
                                                                <span x-show="value == option[valueKey]" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white">
                                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                        </template>
                                                        <li x-show="filteredOptions.length === 0" class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-sm">
                                                            <span x-show="!loading">Tidak ditemukan</span>
                                                            <span x-show="loading">Memuat data...</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <input type="hidden" name="village_id" :value="value">
                                                <input type="hidden" name="village_name" :value="selectedVillageName">
                                            </div>
                                            <div x-show="loadingVillages" class="text-xs text-indigo-500 mt-1 flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Memuat kelurahan...
                                            </div>
                                            <p x-show="errorVillages" x-text="errorVillages" class="text-xs text-red-500 mt-1"></p>
                                        </div>

                                        <div class="mb-4">
                                            <x-input-label for="postal_code" :value="__('Kode Pos')" required />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                </div>
                                                <x-text-input id="postal_code" name="postal_code" type="text" 
                                                    class="pl-10 mt-1 block w-full py-3" 
                                                    :value="old('postal_code', $user->postal_code ?? '')" 
                                                    required 
                                                    @input.debounce.1000ms="autoSave('postal_code', $event.target.value)"
                                                />
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                                            <div class="flex items-center gap-2 mt-1 h-5">
                                                <span x-show="saveStatus === 'saving'" class="text-xs text-blue-500 flex items-center">
                                                    <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    Menyimpan...
                                                </span>
                                                <span x-show="saveStatus === 'saved'" x-transition.leave.duration.2000ms class="text-xs text-green-500 flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Tersimpan
                                                </span>
                                                <span x-show="saveStatus === 'error'" class="text-xs text-red-500">Gagal menyimpan</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Submit Button Personal -->
                                    <div class="pt-6 mt-auto border-t border-gray-100 dark:border-gray-700">
                                        <x-primary-button class="w-full justify-center py-3 text-base flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            {{ __('Simpan Data Pribadi') }}
                                        </x-primary-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- COLUMN 3: Contact -->
                <div class="space-y-6 w-full">
                    <form method="post" action="{{ route('profile.details.update') }}" class="h-full" x-data="contactForm">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="section" value="contact">

                        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg h-full flex flex-col">
                            <header class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">KONTAK</h2>
                                <!-- Global Save Status Indicator for this section -->
                                <div class="ml-auto flex items-center h-5">
                                    <span x-show="saveStatus === 'saving'" class="text-xs text-blue-500 flex items-center">
                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Menyimpan...
                                    </span>
                                    <span x-show="saveStatus === 'saved'" x-transition.leave.duration.2000ms class="text-xs text-green-500 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Tersimpan
                                    </span>
                                    <span x-show="saveStatus === 'error'" class="text-xs text-red-500" x-text="errorMessage || 'Gagal menyimpan'"></span>
                                </div>
                            </header>

                            <div class="space-y-6">
                                <!-- Contact Info -->
                                <div class="space-y-4">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">Kontak Utama</h3>
                                    <div>
                                            <x-input-label for="email" :value="__('Email')" required />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                </div>
                                                <x-text-input id="email" name="email" type="email" 
                                                    class="pl-10 block w-full py-3" 
                                                    :value="old('email', $user->email)" 
                                                    required 
                                                    @input.debounce.1500ms="autoSave('email', $event.target.value)" />
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                        </div>

                                        <div>
                                            <x-input-label for="phone" :value="__('Nomor WhatsApp')" required />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                                </div>
                                                <x-text-input id="phone" name="phone" type="text" class="pl-10 block w-full py-3" :value="old('phone', $user->phone)" required @input.debounce.1000ms="autoSave('phone', $event.target.value)" />
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Social Media Section -->
                                <div class="space-y-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">Media Sosial (Username)</h3>
                                    
                                    <div class="grid grid-cols-1 gap-4">
                                        <!-- Facebook -->
                                        <div x-data="{ username: {{ Js::from(old('social_facebook', $user->social_facebook)) }} }">
                                            <x-input-label for="social_facebook" :value="__('Facebook')" />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                                </div>
                                                <x-text-input id="social_facebook" name="social_facebook" type="text" class="pl-10 block w-full py-3" x-model="username" placeholder="Username Facebook" @input.debounce.1000ms="autoSave('social_facebook', $event.target.value)" />
                                            </div>
                                            <div x-show="username" class="mt-1 text-sm pl-10" x-cloak>
                                                <a :href="'https://facebook.com/' + username" target="_blank" class="text-blue-600 hover:text-blue-500 hover:underline flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                    <span>facebook.com/<span x-text="username"></span></span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Instagram -->
                                        <div x-data="{ username: {{ Js::from(old('social_instagram', $user->social_instagram)) }} }">
                                            <x-input-label for="social_instagram" :value="__('Instagram')" />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                                </div>
                                                <x-text-input id="social_instagram" name="social_instagram" type="text" class="pl-10 block w-full py-3" x-model="username" placeholder="Username Instagram" @input.debounce.1000ms="autoSave('social_instagram', $event.target.value)" />
                                            </div>
                                            <div x-show="username" class="mt-1 text-sm pl-10" x-cloak>
                                                <a :href="'https://instagram.com/' + username" target="_blank" class="text-pink-600 hover:text-pink-500 hover:underline flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                    <span>instagram.com/<span x-text="username"></span></span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Tiktok -->
                                        <div x-data="{ username: {{ Js::from(old('social_tiktok', $user->social_tiktok)) }} }">
                                            <x-input-label for="social_tiktok" :value="__('Tiktok')" />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-black dark:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.35-1.08 1.08-1.15 1.84-.06.52.09 1.04.39 1.46.63.86 1.87 1.13 2.87.82 1.08-.32 1.92-1.32 1.92-2.48.05-5.13.04-10.26.04-15.38z"/></svg>
                                                </div>
                                                <x-text-input id="social_tiktok" name="social_tiktok" type="text" class="pl-10 block w-full py-3" x-model="username" placeholder="Username Tiktok" @input.debounce.1000ms="autoSave('social_tiktok', $event.target.value)" />
                                            </div>
                                            <div x-show="username" class="mt-1 text-sm pl-10" x-cloak>
                                                <a :href="'https://tiktok.com/@' + username.replace(/^@/, '')" target="_blank" class="text-black dark:text-gray-300 hover:text-gray-700 hover:underline flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                    <span>tiktok.com/@<span x-text="username.replace(/^@/, '')"></span></span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Thread -->
                                        <div x-data="{ username: {{ Js::from(old('social_thread', $user->social_thread)) }} }">
                                            <x-input-label for="social_thread" :value="__('Threads')" />
                                            <div class="relative mt-1">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="w-5 h-5 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300">@</span>
                                                </div>
                                                <x-text-input id="social_thread" name="social_thread" type="text" class="pl-10 block w-full py-3" x-model="username" placeholder="Username Threads" @input.debounce.1000ms="autoSave('social_thread', $event.target.value)" />
                                            </div>
                                            <div x-show="username" class="mt-1 text-sm pl-10" x-cloak>
                                                <a :href="'https://www.threads.net/@' + username.replace(/^@/, '')" target="_blank" class="text-gray-900 dark:text-gray-100 hover:text-gray-600 hover:underline flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                    <span>threads.net/@<span x-text="username.replace(/^@/, '')"></span></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Account Section -->
                                <div class="space-y-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">Rekening Bank</h3>
                                    
                                    <div>
                                        <x-input-label for="bank_name" :value="__('Nama Bank')" />
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            </div>
                                            <x-text-input id="bank_name" name="bank_name" type="text" class="pl-10 block w-full py-3" :value="old('bank_name', $user->bank_name)" placeholder="Contoh: BCA, Mandiri, BRI" @input.debounce.1000ms="autoSave('bank_name', $event.target.value)" />
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label for="bank_account_no" :value="__('Nomor Rekening')" />
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                            </div>
                                            <x-text-input id="bank_account_no" name="bank_account_no" type="text" class="pl-10 block w-full py-3" :value="old('bank_account_no', $user->bank_account_no)" placeholder="Nomor Rekening" @input.debounce.1000ms="autoSave('bank_account_no', $event.target.value)" />
                                        </div>
                                        <x-input-error class="mt-2" :messages="$errors->get('bank_account_no')" />
                                    </div>

                                    <div>
                                        <x-input-label for="bank_account_name" :value="__('Atas Nama')" />
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            </div>
                                            <x-text-input id="bank_account_name" name="bank_account_name" type="text" class="pl-10 block w-full py-3" :value="old('bank_account_name', $user->bank_account_name)" placeholder="Nama Pemilik Rekening" @input.debounce.1000ms="autoSave('bank_account_name', $event.target.value)" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button Contact -->
                                <div class="pt-6 mt-auto border-t border-gray-100 dark:border-gray-700">
                                    <x-primary-button class="w-full justify-center py-3 text-base flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ __('UPDATE PROFIL') }}
                                    </x-primary-button>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @php
        $personalDataConfig = [
            "addressProvider" => $addressProvider,
            "currentProvider" => $user->address_provider ?? "",
            "selectedProvince" => $user->province_id ?? "",
            "selectedCity" => $user->city_id ?? "",
            "selectedSubdistrict" => $user->subdistrict_id ?? "",
            "selectedVillage" => $user->village_id ?? "",
            "selectedProvinceName" => $user->province_name ?? "",
            "selectedCityName" => $user->city_name ?? "",
            "selectedSubdistrictName" => $user->subdistrict_name ?? "",
            "selectedVillageName" => $user->village_name ?? "",
        ];
        $personalDataJson = addslashes(json_encode($personalDataConfig));

        $photoConfigData = [
            "photoPreview" => $user->profile_picture ? asset('storage/' . $user->profile_picture) : '',
            "uploadUrl" => route('profile.photo.update'),
            "csrfToken" => csrf_token(),
        ];
        $photoConfigJson = addslashes(json_encode($photoConfigData));
    @endphp
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('searchableSelect', ({
                options: initialOptions,
                value,
                valueKey,
                labelKey,
                placeholder,
                loading,
                disabled,
                onChange
            }) => ({
                options: [],
                value: value,
                valueKey: valueKey,
                labelKey: labelKey,
                placeholder: placeholder,
                loading: loading,
                disabled: disabled,
                onChange: onChange,
                
                isOpen: false,
                search: '',
                
                init() {
                    console.log('searchableSelect init', { placeholder, initialOptions: initialOptions ? initialOptions.length : 0 });
                    this.options = initialOptions || [];
                    this.$watch('options', (val) => {
                         console.log('options watcher triggered', val ? val.length : 0);
                         if (!Array.isArray(val)) this.options = [];
                    });
                    this.$watch('isOpen', (val) => {
                        if (val) {
                            this.$nextTick(() => {
                                if (this.$refs.searchInput) this.$refs.searchInput.focus();
                            });
                        } else {
                            this.search = '';
                        }
                    });
                },

                updateOptions(newOptions) {
                    console.log('updateOptions called', newOptions ? newOptions.length : 0);
                    this.options = newOptions || [];
                },
                
                get filteredOptions() {
                    const opts = this.options || [];
                    if (this.search === '') return opts;
                    const searchLower = this.search.toLowerCase();
                    return opts.filter(option => {
                        const label = option[this.labelKey] || '';
                        const type = option.type ? option.type + ' ' : '';
                        const text = (type + label).toLowerCase();
                        return text.includes(searchLower);
                    });
                },
                
                get selectedLabel() {
                    if (!this.value) return '';
                    const opts = this.options || [];
                    const option = opts.find(o => o[this.valueKey] == this.value);
                    if (!option) return '';
                    const type = option.type ? option.type + ' ' : '';
                    return type + (option[this.labelKey] || '');
                },
                
                toggle() {
                    if (this.disabled) return;
                    this.isOpen = !this.isOpen;
                },
                
                close() {
                    this.isOpen = false;
                },
                
                select(option) {
                    this.value = option[this.valueKey];
                    this.close();
                    if (this.onChange) {
                        this.onChange(this.value);
                    }
                },
                
                clear() {
                    this.value = '';
                    this.onChange('');
                    this.close();
                },

                setOptions(opts) {
                    this.options = opts || [];
                }
            }));

            const personalDataConfig = JSON.parse('{!! $personalDataJson !!}');

            Alpine.data('personalDataForm', () => ({
                addressProvider: personalDataConfig.addressProvider,
                currentProvider: personalDataConfig.currentProvider,
                providerChanged: false,

                provinces: [],
                cities: [],
                subdistricts: [],
                villages: [],
                
                selectedProvince: personalDataConfig.selectedProvince,
                selectedCity: personalDataConfig.selectedCity,
                selectedSubdistrict: personalDataConfig.selectedSubdistrict,
                selectedVillage: personalDataConfig.selectedVillage,
                
                selectedProvinceName: personalDataConfig.selectedProvinceName,
                selectedCityName: personalDataConfig.selectedCityName,
                selectedSubdistrictName: personalDataConfig.selectedSubdistrictName,
                selectedVillageName: personalDataConfig.selectedVillageName,
                
                errorProvinces: '',
                errorCities: '',
                errorSubdistricts: '',
                errorVillages: '',
                
                loadingProvinces: false,
                loadingCities: false,
                loadingSubdistricts: false,
                loadingVillages: false,
                
                saveStatus: '',
                
                autoSave: async function(field, value) {
                    this.saveStatus = 'saving';
                    const form = this.$root.closest('form');
                    if (!form) return;
                    
                    const formData = new FormData(form);
                    
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (res.ok) {
                            const data = await res.json();
                            this.saveStatus = 'saved';
                            if (data.profile_completeness !== undefined) {
                                window.dispatchEvent(new CustomEvent('profile-updated', { 
                                    detail: { completeness: data.profile_completeness } 
                                }));
                            }
                            setTimeout(() => { if(this.saveStatus === 'saved') this.saveStatus = ''; }, 3000);
                        } else {
                            this.saveStatus = 'error';
                            setTimeout(() => { if(this.saveStatus === 'error') this.saveStatus = ''; }, 3000);
                        }
                    } catch (e) {
                        this.saveStatus = 'error';
                        setTimeout(() => { if(this.saveStatus === 'error') this.saveStatus = ''; }, 3000);
                    }
                },
                
                init: async function() {
                    // Check provider mismatch
                    if (this.currentProvider && this.currentProvider !== this.addressProvider) {
                        this.providerChanged = true;
                        this.clearAddressFields();
                    }

                    await this.fetchProvinces();
                    
                    this.updateProvinceName();
                    if (this.selectedProvince) {
                        await this.fetchCities();
                    }
                    if (this.selectedCity) {
                        await this.fetchSubdistricts();
                    }
                    if (this.selectedSubdistrict && this.addressProvider === 'api_id') {
                        await this.fetchVillages();
                    }
                },

                clearAddressFields: function() {
                    this.selectedProvince = '';
                    this.selectedCity = '';
                    this.selectedSubdistrict = '';
                    this.selectedVillage = '';
                    this.selectedProvinceName = '';
                    this.selectedCityName = '';
                    this.selectedSubdistrictName = '';
                    this.selectedVillageName = '';
                    
                    // Reset options
                    this.cities = [];
                    this.subdistricts = [];
                    this.villages = [];
                },
                
                fetchProvinces: async function() {
                    this.loadingProvinces = true;
                    this.errorProvinces = '';
                    try {
                        const res = await fetch("{{ route('profile.locations.provinces') }}");
                        if (!res.ok) throw new Error('Gagal memuat provinsi');
                        this.provinces = await res.json();
                    } catch(e) { 
                        console.error('Failed to load provinces', e);
                        this.errorProvinces = 'Gagal memuat data provinsi.';
                    }
                    this.loadingProvinces = false;
                },
                
                onProvinceChange: async function() {
                    this.selectedCity = '';
                    this.selectedSubdistrict = '';
                    this.selectedVillage = '';
                    this.selectedCityName = '';
                    this.cities = [];
                    this.subdistricts = [];
                    this.villages = [];
                    this.errorCities = '';
                    
                    if (this.selectedProvince) {
                        this.updateProvinceName();
                        await this.autoSave('province_id', this.selectedProvince);
                        await this.fetchCities();
                    }
                },
                
                fetchCities: async function() {
                    this.loadingCities = true;
                    this.errorCities = '';
                    try {
                        const url = "{{ route('profile.locations.cities', ['province' => 'PROVINCE_ID']) }}".replace('PROVINCE_ID', this.selectedProvince);
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('Gagal memuat kota');
                        this.cities = await res.json();
                        this.updateCityName();
                    } catch(e) { 
                        console.error('Failed to load cities', e);
                        this.errorCities = 'Gagal memuat data kota.';
                    }
                    this.loadingCities = false;
                },

                onCityChange: async function() {
                    this.selectedSubdistrict = '';
                    this.selectedVillage = '';
                    this.subdistricts = [];
                    this.villages = [];
                    this.errorSubdistricts = '';
                    this.updateCityName();
                    
                    if (this.selectedCity) {
                        await this.autoSave('city_id', this.selectedCity);
                        await this.fetchSubdistricts();
                    }
                },
                
                updateProvinceName: function() {
                    const prov = this.provinces.find(p => p.province_id == this.selectedProvince);
                    if (prov) {
                        this.selectedProvinceName = prov.province;
                    } else {
                        this.selectedProvinceName = '';
                    }
                },

                updateCityName: function() {
                    const city = this.cities.find(c => c.city_id == this.selectedCity);
                    if (city) {
                        this.selectedCityName = `${city.type} ${city.city_name}`;
                    } else {
                        this.selectedCityName = '';
                    }
                },

                updateSubdistrictName: function() {
                    const sub = this.subdistricts.find(s => s.subdistrict_id == this.selectedSubdistrict);
                    if (sub) {
                        this.selectedSubdistrictName = sub.subdistrict_name;
                    } else {
                        this.selectedSubdistrictName = '';
                    }
                },

                fetchSubdistricts: async function() {
                    this.loadingSubdistricts = true;
                    this.errorSubdistricts = '';
                    try {
                        const url = "{{ route('profile.locations.subdistricts', ['city' => 'CITY_ID']) }}".replace('CITY_ID', this.selectedCity);
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('Gagal memuat kecamatan');
                        this.subdistricts = await res.json();
                    } catch(e) {
                        console.error('Failed to load subdistricts', e);
                        this.errorSubdistricts = 'Gagal memuat data kecamatan.';
                    }
                    this.loadingSubdistricts = false;
                },

                onSubdistrictChange: async function() {
                    this.selectedVillage = '';
                    this.villages = [];
                    this.errorVillages = '';
                    this.updateSubdistrictName();
                    
                    if (this.selectedSubdistrict) {
                        await this.autoSave('subdistrict_id', this.selectedSubdistrict);
                        if (this.addressProvider === 'api_id') {
                            await this.fetchVillages();
                        }
                    }
                },

                updateVillageName: function() {
                    const village = this.villages.find(v => v.village_id == this.selectedVillage);
                    if (village) {
                        this.selectedVillageName = village.village_name;
                    } else {
                        this.selectedVillageName = '';
                    }
                },

                fetchVillages: async function() {
                    this.loadingVillages = true;
                    this.errorVillages = '';
                    try {
                        const url = "{{ route('profile.locations.villages', ['subdistrict' => 'SUBDISTRICT_ID']) }}".replace('SUBDISTRICT_ID', this.selectedSubdistrict);
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('Gagal memuat kelurahan');
                        this.villages = await res.json();
                    } catch(e) {
                        console.error('Failed to load villages', e);
                        this.errorVillages = 'Gagal memuat data kelurahan.';
                    }
                    this.loadingVillages = false;
                }
            }));

            const photoConfig = JSON.parse('{!! $photoConfigJson !!}');

            Alpine.data('photoProfile', () => ({
                photoPreview: photoConfig.photoPreview,
                photoValid: false,
                photoError: null,
                uploading: false,
                progress: 0,
                statusText: '',
                successMessage: null,
                uploadUrl: photoConfig.uploadUrl,
                csrfToken: photoConfig.csrfToken,

                validateFile: function(event) {
                    const file = event.target.files[0];
                    this.photoError = null;
                    this.successMessage = null;
                    this.photoValid = false;
                    
                    if (!file) return;
                    
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        this.photoError = 'Format file harus JPEG, PNG, atau WEBP.';
                        return;
                    }
                    
                    if (file.size > 10 * 1024 * 1024) {
                        this.photoError = 'Ukuran file maksimal 10MB.';
                        return;
                    }
                    
                    this.photoValid = true;
                    
                    const reader = new FileReader();
                    reader.onload = (e) => { this.photoPreview = e.target.result; };
                    reader.readAsDataURL(file);
                },

                uploadPhoto: function() {
                    if (!this.photoValid) return;
                    this.uploading = true;
                    this.progress = 0;
                    this.statusText = 'Mengupload...';
                    this.photoError = null;
                    this.successMessage = null;

                    const fileInput = document.getElementById('photo');
                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('photo', file);
                    formData.append('_token', this.csrfToken);

                    const xhr = new XMLHttpRequest();
                    
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            this.progress = Math.round((e.loaded / e.total) * 100);
                            if (this.progress >= 100) {
                                this.statusText = 'Memproses & Kompresi...';
                            }
                        }
                    });

                    xhr.onreadystatechange = () => {
                        if (xhr.readyState === 4) {
                            this.uploading = false;
                            if (xhr.status >= 200 && xhr.status < 300) {
                                try {
                                    const result = JSON.parse(xhr.responseText);
                                    this.successMessage = result.message;
                                    this.photoPreview = result.url;
                                    window.dispatchEvent(new CustomEvent('profile-photo-updated', { detail: { url: result.url } }));
                                    this.photoValid = false;
                                    fileInput.value = '';
                                    setTimeout(() => this.successMessage = null, 3000);
                                } catch (e) {
                                    this.photoError = 'Respon server tidak valid.';
                                }
                            } else {
                                try {
                                    const result = JSON.parse(xhr.responseText);
                                    this.photoError = result.message || 'Gagal mengupload foto.';
                                } catch (e) {
                                    this.photoError = 'Gagal mengupload foto. Error: ' + xhr.statusText;
                                }
                            }
                        }
                    };

                    xhr.open('POST', this.uploadUrl, true);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                }
            }));
            Alpine.data('contactForm', () => ({
                saveStatus: '',
                errorMessage: '',
                
                autoSave: async function(field, value) {
                    this.saveStatus = 'saving';
                    this.errorMessage = '';
                    const form = this.$root.closest('form');
                    if (!form) return;
                    
                    const formData = new FormData(form);
                    
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (res.ok) {
                            const data = await res.json();
                            this.saveStatus = 'saved';
                            
                            // Dispatch event to update profile completeness progress bar
                            if (data.profile_completeness !== undefined) {
                                window.dispatchEvent(new CustomEvent('profile-updated', { 
                                    detail: { completeness: data.profile_completeness } 
                                }));
                            }

                            setTimeout(() => { if(this.saveStatus === 'saved') this.saveStatus = ''; }, 3000);
                        } else {
                            this.saveStatus = 'error';
                            try {
                                const errorData = await res.json();
                                this.errorMessage = errorData.message || 'Gagal menyimpan.';
                                if (errorData.errors) {
                                    const firstKey = Object.keys(errorData.errors)[0];
                                    this.errorMessage = errorData.errors[firstKey][0];
                                }
                            } catch (e) {
                                this.errorMessage = 'Gagal menyimpan.';
                            }
                            setTimeout(() => { 
                                if(this.saveStatus === 'error') {
                                    this.saveStatus = ''; 
                                    this.errorMessage = '';
                                }
                            }, 5000);
                        }
                    } catch (e) {
                        this.saveStatus = 'error';
                        this.errorMessage = 'Terjadi kesalahan jaringan.';
                        setTimeout(() => { 
                            if(this.saveStatus === 'error') {
                                this.saveStatus = ''; 
                                this.errorMessage = '';
                            }
                        }, 5000);
                    }
                }
            }));
        });
    </script>
</x-app-layout>
