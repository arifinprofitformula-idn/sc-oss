@extends('layouts.guest')

@section('content')
    <style>
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .animate-shimmer {
            animation: shimmer 1s ease-in-out infinite;
        }
    </style>
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        <div class="mb-8 flex justify-center">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo />
            </a>
        </div>

        <div class="max-w-7xl mx-auto">
            @if(!$package)
                <!-- Empty State -->
                <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-8 text-center max-w-2xl mx-auto">
                    <div class="mb-6">
                        <svg class="w-20 h-20 text-gray-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-4">{{ __('Pendaftaran Ditutup') }}</h2>
                    <p class="text-gray-300 text-lg mb-8">
                        {{ __('Pendaftaran SilverChannel sedang tidak tersedia. Silakan hubungi admin untuk informasi lebih lanjut.') }}
                    </p>
                    <a href="/" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-gray-900 bg-cyan-400 hover:bg-cyan-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transition-colors">
                        {{ __('Kembali ke Beranda') }}
                    </a>
                </div>
            @else
            <form method="POST" action="{{ route('register.silver.store') }}" x-data="locationSelector({
                oldName: {{ json_encode(old('name')) }},
                oldNik: {{ json_encode(old('nik')) }},
                oldEmail: {{ json_encode(old('email')) }},
                oldWhatsapp: {{ json_encode(old('whatsapp')) }},
                oldAddress: {{ json_encode(old('address')) }},
                oldProvinceId: {{ json_encode(old('province_id')) }},
                oldProvinceName: {{ json_encode(old('province_name')) }},
                oldCityId: {{ json_encode(old('city_id')) }},
                oldCityName: {{ json_encode(old('city_name')) }},
                oldSubdistrictId: {{ json_encode(old('subdistrict_id')) }},
                oldSubdistrictName: {{ json_encode(old('subdistrict_name')) }},
                oldVillageId: {{ json_encode(old('village_id')) }},
                oldVillageName: {{ json_encode(old('village_name')) }},
                oldPostalCode: {{ json_encode(old('postal_code')) }},
                oldShippingService: {{ json_encode(old('shipping_service')) }},
                oldShippingCost: {{ json_encode(old('shipping_cost')) }},
                oldShippingCourier: {{ json_encode(old('shipping_courier')) }},
                oldShippingEtd: {{ json_encode(old('shipping_etd')) }},
                packageWeight: {{ $package->total_weight ?? 1000 }},
                packingFee: {{ $packingFee ?? 0 }}
            })">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package->id }}">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Registration Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6 sm:p-8">
                            <div class="mb-6">
                                <h2 class="text-2xl font-bold text-white mb-2">{{ __('Form Registrasi') }}</h2>
                                <p class="text-gray-300 text-sm">
                                    {{ __('Lengkapi data diri Anda untuk mendaftar sebagai Silver Channel Partner.') }}
                                </p>
                            </div>

                            <div class="space-y-6">
                                <!-- Name -->
                                <div>
                                    <x-input-label for="name" :value="__('Nama Lengkap (Sesuai KTP)')" class="text-white font-medium mb-2" />
                                    <x-text-input 
                                        id="name" 
                                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                        type="text" 
                                        name="name" 
                                        x-model="name"
                                        required 
                                        autofocus 
                                        placeholder="Masukkan nama lengkap"
                                    />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400" />
                                </div>

                                <!-- NIK -->
                                <div>
                                    <x-input-label for="nik" :value="__('NIK (16 Digit)')" class="text-white font-medium mb-2" />
                                    <x-text-input 
                                        id="nik" 
                                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                        type="number" 
                                        name="nik" 
                                        x-model="nik"
                                        required 
                                        maxlength="16" 
                                        placeholder="Masukkan NIK"
                                    />
                                    <x-input-error :messages="$errors->get('nik')" class="mt-2 text-red-400" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Email -->
                                    <div>
                                        <x-input-label for="email" :value="__('Email')" class="text-white font-medium mb-2" />
                                        <x-text-input 
                                            id="email" 
                                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                            type="email" 
                                            name="email" 
                                            x-model="email"
                                            required 
                                            placeholder="contoh@email.com"
                                        />
                                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                                    </div>

                                    <!-- WhatsApp -->
                                    <div>
                                        <x-input-label for="whatsapp" :value="__('Nomor WhatsApp (awali 62)')" class="text-white font-medium mb-2" />
                                        <x-text-input 
                                            id="whatsapp" 
                                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                            type="text" 
                                            name="whatsapp" 
                                            x-model="whatsapp"
                                            @input="whatsapp = whatsapp.replace(/[^0-9]/g, '')"
                                            required 
                                            placeholder="628..." 
                                        />
                                        <p class="text-xs text-gray-300 mt-1">Format: 6281234567890 (tanpa tanda +)</p>
                                        <x-input-error :messages="$errors->get('whatsapp')" class="mt-2 text-red-400" />
                                    </div>
                                </div>

                                <!-- Location Selector -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Province -->
                                    <div>
                                        <x-input-label for="province_id" :value="__('Provinsi')" class="text-white font-medium mb-2" />
                                        <select id="province_id" name="province_id" x-model="selectedProvince" @change="fetchCities()" class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm p-2.5">
                                            <option value="">Pilih Provinsi</option>
                                            <template x-for="province in provinces" :key="province.province_id">
                                                <option :value="province.province_id" x-text="province.province"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" name="province_name" x-model="selectedProvinceName">
                                        <x-input-error :messages="$errors->get('province_id')" class="mt-2 text-red-400" />
                                    </div>

                                    <!-- City -->
                                    <div>
                                        <x-input-label for="city_id" :value="__('Kota/Kabupaten')" class="text-white font-medium mb-2" />
                                        <select id="city_id" name="city_id" x-model="selectedCity" @change="updateCityName(); fetchSubdistricts()" :disabled="!selectedProvince" class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm p-2.5">
                                            <option value="">Pilih Kota</option>
                                            <template x-for="city in cities" :key="city.city_id">
                                                <option :value="city.city_id" x-text="city.type + ' ' + city.city_name"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" name="city_name" x-model="selectedCityName">
                                        <x-input-error :messages="$errors->get('city_id')" class="mt-2 text-red-400" />
                                    </div>

                                    <!-- Kecamatan -->
                                    <div>
                                        <x-input-label for="subdistrict_id" :value="__('Kecamatan')" class="text-white font-medium mb-2" />
                                        <select id="subdistrict_id" name="subdistrict_id" x-model="selectedSubdistrict" @change="updateSubdistrictName(); fetchVillages()" :disabled="!selectedCity" class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm p-2.5">
                                            <option value="">Pilih Kecamatan</option>
                                            <template x-for="subdistrict in subdistricts" :key="subdistrict.subdistrict_id">
                                                <option :value="subdistrict.subdistrict_id" x-text="subdistrict.subdistrict_name"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" name="subdistrict_name" x-model="selectedSubdistrictName">
                                        <x-input-error :messages="$errors->get('subdistrict_id')" class="mt-2 text-red-400" />
                                    </div>

                                    <!-- Kelurahan -->
                                    <div>
                                        <x-input-label for="village_id" :value="__('Kelurahan')" class="text-white font-medium mb-2" />
                                        <select id="village_id" name="village_id" x-model="selectedVillage" @change="updateVillageName(); fetchShippingServices()" :disabled="!selectedSubdistrict" class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm p-2.5">
                                            <option value="">Pilih Kelurahan</option>
                                            <template x-for="village in villages" :key="village.village_id">
                                                <option :value="village.village_id" x-text="village.village_name"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" name="village_name" x-model="selectedVillageName">
                                        <x-input-error :messages="$errors->get('village_id')" class="mt-2 text-red-400" />
                                    </div>

                                    <!-- Kode Pos -->
                                    <div>
                                        <x-input-label for="postal_code" :value="__('Kode Pos')" class="text-white font-medium mb-2" />
                                        <x-text-input 
                                            id="postal_code" 
                                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                            type="text" 
                                            name="postal_code" 
                                            x-model="postalCode"
                                            maxlength="10"
                                            placeholder="xxxxx"
                                        />
                                        <x-input-error :messages="$errors->get('postal_code')" class="mt-2 text-red-400" />
                                    </div>
                                </div>
                                
                                <!-- Address -->
                                <div>
                                    <x-input-label for="address" :value="__('Alamat Lengkap')" class="text-white font-medium mb-2" />
                                    <textarea 
                                        id="address" 
                                        name="address" 
                                        x-model="address"
                                        rows="2"
                                        required
                                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm"
                                        placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                                    ></textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mt-2 text-red-400" />
                                </div>

                                <!-- Shipping Service Selection -->
                                <div class="border-t border-gray-700 pt-6">
                                    <h3 class="text-lg font-medium text-white mb-4">{{ __('Pengiriman') }}</h3>
                                    
                                    <div x-show="isLoadingShipping" class="flex items-center justify-center py-4">
                                        <svg class="animate-spin h-6 w-6 text-cyan-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="ml-2 text-gray-300 text-sm">Memuat layanan pengiriman...</span>
                                    </div>

                                    <div x-show="!isLoadingShipping && shippingServices.length === 0 && selectedCity" class="text-gray-300 text-sm py-2">
                                        <p>Tidak ada layanan pengiriman yang tersedia untuk lokasi ini.</p>
                                    </div>

                                    <div x-show="!selectedCity" class="text-gray-300 text-sm italic py-2">
                                        Silakan pilih lokasi (Provinsi & Kota) terlebih dahulu.
                                    </div>

                                    <div class="grid grid-cols-1 gap-4" x-show="!isLoadingShipping && shippingServices.length > 0">
                                        <template x-for="courier in shippingServices" :key="courier.code">
                                            <div class="space-y-3">
                                                <h4 class="text-white text-sm font-semibold uppercase tracking-wider" x-text="courier.name"></h4>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <template x-for="cost in courier.costs" :key="cost.service">
                                                        <div 
                                                            @click="selectShipping(cost.service, cost.cost[0].value, cost.cost[0].etd, courier.code)"
                                                            class="relative rounded-lg border p-4 cursor-pointer hover:border-cyan-500 transition-all duration-200"
                                                            :class="selectedShippingService === cost.service && selectedShippingCourier === courier.code ? 'bg-cyan-900/20 border-cyan-500 ring-1 ring-cyan-500' : 'bg-gray-800/30 border-gray-700'"
                                                        >
                                                            <div class="flex justify-between items-center">
                                                                <div class="flex-1">
                                                                    <p class="text-white font-bold" x-text="cost.service"></p>
                                                                    <p class="text-gray-300 text-xs mt-1" x-text="cost.description"></p>
                                                                    <p class="text-gray-300 text-xs mt-1" x-text="'Estimasi: ' + (cost.cost[0].etd ? cost.cost[0].etd + ' hari' : '-')"></p>
                                                                </div>
                                                                <div class="flex items-center space-x-3">
                                                                    <div class="text-right">
                                                                        <p class="text-cyan-400 font-bold" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(cost.cost[0].value)"></p>
                                                                    </div>
                                                                    <!-- Selected Indicator -->
                                                                    <div class="w-6 h-6 flex items-center justify-center">
                                                                        <div x-show="selectedShippingService === cost.service && selectedShippingCourier === courier.code" 
                                                                             x-transition:enter="transition ease-out duration-200"
                                                                             x-transition:enter-start="opacity-0 scale-50"
                                                                             x-transition:enter-end="opacity-100 scale-100">
                                                                            <svg class="w-6 h-6 text-cyan-500" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                            </svg>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <!-- Hidden Inputs for Shipping -->
                                    <input type="hidden" name="shipping_service" x-model="selectedShippingService">
                                    <input type="hidden" name="shipping_cost" x-model="selectedShippingCost">
                                    <input type="hidden" name="shipping_courier" x-model="selectedShippingCourier">
                                    <input type="hidden" name="shipping_etd" x-model="selectedShippingEtd">
                                    <x-input-error :messages="$errors->get('shipping_service')" class="mt-2 text-red-400" />
                                </div>

                                <!-- Referral Code -->
                                <div>
                                    <x-input-label for="referral_code" :value="__('Kode Referral')" class="text-white font-medium mb-2" required />
                                    <x-text-input 
                                        id="referral_code" 
                                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent {{ $referralCode ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                        type="text" 
                                        name="referral_code" 
                                        :value="old('referral_code', $referralCode)" 
                                        maxlength="20" 
                                        placeholder="Wajib Masukkan Kode Referral Anda"
                                        :readonly="$referralCode ? true : false"
                                        required
                                    />
                                    @if($referralCode)
                                        <p class="text-xs text-gray-300 mt-1">* Kode referral otomatis terisi dari link afiliasi.</p>
                                    @endif
                                    <x-input-error :messages="$errors->get('referral_code')" class="mt-2 text-red-400" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Password -->
                                    <div>
                                        <x-input-label for="password" :value="__('Password')" class="text-white font-medium mb-2" />
                                        <x-text-input 
                                            id="password" 
                                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                            type="password" 
                                            name="password" 
                                            required 
                                            autocomplete="new-password" 
                                            placeholder="Buat password"
                                        />
                                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                                    </div>

                                    <!-- Confirm Password -->
                                    <div>
                                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-white font-medium mb-2" />
                                        <x-text-input 
                                            id="password_confirmation" 
                                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                            type="password" 
                                            name="password_confirmation" 
                                            required 
                                            placeholder="Ulangi password"
                                        />
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
                                    </div>
                                </div>

                                <!-- Terms & Conditions Checkbox -->
                                <div class="flex items-center mt-8 gap-3">
                                    <label class="cyber-checkbox flex-shrink-0">
                                        <input type="checkbox" name="terms_accepted" x-model="termsAccepted" required>
                                        <span class="cyber-checkbox__mark">
                                            <span class="cyber-checkbox__box"></span>
                                            <svg class="cyber-checkbox__check" viewBox="0 0 24 24">
                                                <path d="M20 6L9 17l-5-5"></path>
                                            </svg>
                                            <span class="cyber-checkbox__particles">
                                                <div class="particle-1"></div>
                                                <div class="particle-2"></div>
                                                <div class="particle-3"></div>
                                                <div class="particle-4"></div>
                                                <div class="particle-5"></div>
                                                <div class="particle-6"></div>
                                                <div class="particle-7"></div>
                                                <div class="particle-8"></div>
                                            </span>
                                        </span>
                                    </label>
                                    <span class="text-gray-300 text-sm flex-1 leading-tight">
                                        Saya telah membaca dan menyetujui <a href="#" @click.prevent="showTermsModal = true" class="text-cyan-400 hover:text-cyan-300 underline font-medium transition-colors">Syarat & Ketentuan</a> menjadi Silverchannel
                                    </span>
                                </div>
                                <style>
                                    /* From Uiverse.io by 00Kubi - Adapted for Silvergram Theme */ 
                                    .cyber-checkbox { 
                                        --checkbox-size: 24px; 
                                        --checkbox-color: #06b6d4; /* cyan-500 */
                                        --checkbox-check-color: #ffffff; 
                                        --checkbox-hover-color: #2563eb; /* blue-600 */
                                        --checkbox-spark-offset: -20px; 

                                        position: relative; 
                                        display: inline-block; 
                                        cursor: pointer; 
                                        user-select: none; 
                                    } 

                                    .cyber-checkbox input { 
                                        display: none; 
                                    } 

                                    .cyber-checkbox__mark { 
                                        position: relative; 
                                        display: inline-block; 
                                        width: var(--checkbox-size); 
                                        height: var(--checkbox-size); 
                                    } 

                                    .cyber-checkbox__box { 
                                        position: absolute; 
                                        inset: 0; 
                                        border: 2px solid var(--checkbox-color); 
                                        border-radius: 4px; 
                                        background: transparent; 
                                        transition: all 0.2s ease; 
                                    } 

                                    .cyber-checkbox__check { 
                                        position: absolute; 
                                        inset: 0; 
                                        padding: 2px; 
                                        stroke: var(--checkbox-check-color); 
                                        stroke-width: 2px; 
                                        stroke-linecap: round; 
                                        stroke-linejoin: round; 
                                        fill: none; 
                                        transform: scale(0); 
                                        transition: transform 0.2s ease; 
                                    } 

                                    .cyber-checkbox__effects { 
                                        position: absolute; 
                                        inset: var(--checkbox-spark-offset); 
                                        pointer-events: none; 
                                    } 

                                    .cyber-checkbox__spark { 
                                        position: absolute; 
                                        top: 50%; 
                                        left: 50%; 
                                        width: 2px; 
                                        height: 2px; 
                                        background: var(--checkbox-color); 
                                        border-radius: 50%; 
                                        opacity: 0; 
                                        transform-origin: center center; 
                                    } 

                                    /* Hover */ 
                                    .cyber-checkbox:hover .cyber-checkbox__box { 
                                        border-color: var(--checkbox-hover-color); 
                                        box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.2); 
                                    } 

                                    /* Checked */ 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .cyber-checkbox__box { 
                                        background: var(--checkbox-color); 
                                        border-color: var(--checkbox-color); 
                                    } 

                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .cyber-checkbox__check { 
                                        transform: scale(1); 
                                    } 

                                    /* Spark Animation */ 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .cyber-checkbox__spark { 
                                        animation: spark 0.4s ease-out; 
                                    } 

                                    .cyber-checkbox__spark:nth-child(1) { 
                                        transform: rotate(0deg) translateX(var(--checkbox-spark-offset)); 
                                    } 
                                    .cyber-checkbox__spark:nth-child(2) { 
                                        transform: rotate(90deg) translateX(var(--checkbox-spark-offset)); 
                                    } 
                                    .cyber-checkbox__spark:nth-child(3) { 
                                        transform: rotate(180deg) translateX(var(--checkbox-spark-offset)); 
                                    } 
                                    .cyber-checkbox__spark:nth-child(4) { 
                                        transform: rotate(270deg) translateX(var(--checkbox-spark-offset)); 
                                    } 

                                    @keyframes spark { 
                                        0% { 
                                            opacity: 0; 
                                            transform: scale(0) rotate(0deg) translateX(var(--checkbox-spark-offset)); 
                                        } 
                                        50% { 
                                            opacity: 1; 
                                        } 
                                        100% { 
                                            opacity: 0; 
                                            transform: scale(1) rotate(0deg) translateX(calc(var(--checkbox-spark-offset) * 1.5)); 
                                        } 
                                    } 

                                    /* Active */ 
                                    .cyber-checkbox:active .cyber-checkbox__box { 
                                        transform: scale(0.9); 
                                    } 

                                    /* Focus */ 
                                    .cyber-checkbox input:focus + .cyber-checkbox__mark .cyber-checkbox__box { 
                                        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.2); 
                                    } 

                                    .cyber-checkbox__particles { 
                                        position: absolute; 
                                        inset: -50%; 
                                        pointer-events: none; 
                                    } 

                                    .cyber-checkbox__particles div { 
                                        position: absolute; 
                                        top: 50%; 
                                        left: 50%; 
                                        width: 3px; 
                                        height: 3px; 
                                        border-radius: 50%; 
                                        background: var(--checkbox-color); 
                                        opacity: 0; 
                                    } 

                                    /* Particle animations for check */ 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .particle-1 { animation: particle-1 0.4s ease-out forwards; } 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .particle-2 { animation: particle-2 0.4s ease-out forwards 0.1s; } 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .particle-3 { animation: particle-3 0.4s ease-out forwards 0.15s; } 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .particle-4 { animation: particle-4 0.4s ease-out forwards 0.05s; } 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .particle-5 { animation: particle-5 0.4s ease-out forwards 0.12s; } 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .particle-6 { animation: particle-6 0.4s ease-out forwards 0.08s; } 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .particle-7 { animation: particle-7 0.4s ease-out forwards 0.18s; } 
                                    .cyber-checkbox input:checked + .cyber-checkbox__mark .particle-8 { animation: particle-8 0.4s ease-out forwards 0.15s; } 

                                    /* Particle animations for uncheck */ 
                                    .cyber-checkbox input:not(:checked) + .cyber-checkbox__mark .particle-1 { animation: particle-out-1 0.4s ease-out forwards; } 
                                    .cyber-checkbox input:not(:checked) + .cyber-checkbox__mark .particle-2 { animation: particle-out-2 0.4s ease-out forwards 0.1s; } 
                                    .cyber-checkbox input:not(:checked) + .cyber-checkbox__mark .particle-3 { animation: particle-out-3 0.4s ease-out forwards 0.15s; } 
                                    .cyber-checkbox input:not(:checked) + .cyber-checkbox__mark .particle-4 { animation: particle-out-4 0.4s ease-out forwards 0.05s; } 
                                    .cyber-checkbox input:not(:checked) + .cyber-checkbox__mark .particle-5 { animation: particle-out-5 0.4s ease-out forwards 0.12s; } 
                                    .cyber-checkbox input:not(:checked) + .cyber-checkbox__mark .particle-6 { animation: particle-out-6 0.4s ease-out forwards 0.08s; } 
                                    .cyber-checkbox input:not(:checked) + .cyber-checkbox__mark .particle-7 { animation: particle-out-7 0.4s ease-out forwards 0.18s; } 
                                    .cyber-checkbox input:not(:checked) + .cyber-checkbox__mark .particle-8 { animation: particle-out-8 0.4s ease-out forwards 0.15s; } 

                                    /* Particle keyframes */ 
                                    @keyframes particle-1 { 0% { transform: translate(0, 0) scale(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translate(-20px, -20px) scale(1); opacity: 0; } } 
                                    @keyframes particle-2 { 0% { transform: translate(0, 0) scale(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translate(20px, -20px) scale(1); opacity: 0; } } 
                                    @keyframes particle-3 { 0% { transform: translate(0, 0) scale(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translate(20px, 20px) scale(1); opacity: 0; } } 
                                    @keyframes particle-4 { 0% { transform: translate(0, 0) scale(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translate(-20px, 20px) scale(1); opacity: 0; } } 
                                    @keyframes particle-5 { 0% { transform: translate(0, 0) scale(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translate(0, -30px) scale(1); opacity: 0; } } 
                                    @keyframes particle-6 { 0% { transform: translate(0, 0) scale(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translate(30px, 0) scale(1); opacity: 0; } } 
                                    @keyframes particle-7 { 0% { transform: translate(0, 0) scale(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translate(0, 30px) scale(1); opacity: 0; } } 
                                    @keyframes particle-8 { 0% { transform: translate(0, 0) scale(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translate(-30px, 0) scale(1); opacity: 0; } } 

                                    /* Particle Out Keyframes (Simple Fade) */
                                    @keyframes particle-out-1 { 0% { opacity: 1; transform: translate(-20px, -20px) scale(1); } 100% { opacity: 0; transform: translate(0, 0) scale(0); } }
                                    @keyframes particle-out-2 { 0% { opacity: 1; transform: translate(20px, -20px) scale(1); } 100% { opacity: 0; transform: translate(0, 0) scale(0); } }
                                    @keyframes particle-out-3 { 0% { opacity: 1; transform: translate(20px, 20px) scale(1); } 100% { opacity: 0; transform: translate(0, 0) scale(0); } }
                                    @keyframes particle-out-4 { 0% { opacity: 1; transform: translate(-20px, 20px) scale(1); } 100% { opacity: 0; transform: translate(0, 0) scale(0); } }
                                    @keyframes particle-out-5 { 0% { opacity: 1; transform: translate(0, -30px) scale(1); } 100% { opacity: 0; transform: translate(0, 0) scale(0); } }
                                    @keyframes particle-out-6 { 0% { opacity: 1; transform: translate(30px, 0) scale(1); } 100% { opacity: 0; transform: translate(0, 0) scale(0); } }
                                    @keyframes particle-out-7 { 0% { opacity: 1; transform: translate(0, 30px) scale(1); } 100% { opacity: 0; transform: translate(0, 0) scale(0); } }
                                    @keyframes particle-out-8 { 0% { opacity: 1; transform: translate(-30px, 0) scale(1); } 100% { opacity: 0; transform: translate(0, 0) scale(0); } }
                                </style>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Package Info -->
                    <div class="lg:col-span-1">
                        <div class="bg-gradient-to-br from-blue-900/80 to-gray-900/80 backdrop-blur-xl border border-blue-700/50 rounded-2xl shadow-2xl p-6 sm:p-8 sticky top-8">
                            
                            <!-- Package Image -->
                            <div class="mb-6 -mx-6 -mt-6 sm:-mx-8 sm:-mt-8 rounded-t-2xl overflow-hidden relative group">
                                @if($package->image)
                                    <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-105 rounded-2xl shadow-lg hover:shadow-cyan-400/50 hover:shadow-2xl">
                                @else
                                    <!-- Fallback Placeholder -->
                                    <div class="w-full aspect-[4/3] bg-gray-800 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                @if($package->original_price && $package->original_price > $package->price)
                                    <div class="absolute top-4 left-6 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                        PROMO
                                    </div>
                                @endif
                            </div>

                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-white mb-2">{{ $package->name }}</h3>
                                
                                @if($package->original_price && $package->original_price > $package->price)
                                    <div class="flex flex-col items-center justify-center">
                                         <span class="text-gray-400 line-through text-sm">Rp {{ number_format($package->original_price, 0, ',', '.') }}</span>
                                        <div class="text-3xl font-bold text-cyan-400">
                                           Rp {{ number_format($package->base_total, 0, ',', '.') }}
                                        </div>
                                         <span class="bg-red-500/20 text-white text-xs px-2 py-0.5 rounded-full mt-1 border border-red-500/30">
                                            Hemat Rp {{ number_format($package->original_price - $package->price, 0, ',', '.') }}
                                         </span>
                                         @if($package->promo_start_date || $package->promo_end_date)
                                            <div class="mt-2 text-xs text-yellow-400 font-medium">
                                                Promo: 
                                                {{ $package->promo_start_date ? \Carbon\Carbon::parse($package->promo_start_date)->translatedFormat('d M') : 'Sekarang' }} 
                                                - 
                                                {{ $package->promo_end_date ? \Carbon\Carbon::parse($package->promo_end_date)->translatedFormat('d M Y') : 'Seterusnya' }}
                                            </div>
                                         @endif
                                    </div>
                                @else
                                    <div class="text-3xl font-extrabold text-cyan-400">
                                        Rp {{ number_format($package->base_total, 0, ',', '.') }}
                                    </div>
                                @endif
                                <p class="text-gray-300 text-sm mt-2">Biaya Paket + Produk</p>
                            </div>

                            <div class="space-y-4 mb-8">
                                @if(is_array($package->benefits))
                                    @foreach($package->benefits as $benefit)
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-green-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-gray-300 text-sm">{{ $benefit }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Bundled Products List --}}
                                @if($package->products->count() > 0)
                                    <div class="pt-4 border-t border-gray-700">
                                        <h4 class="text-xs font-semibold text-white uppercase mb-3">{{ __('Produk Termasuk') }}</h4>
                                        <div class="space-y-3">
                                            @foreach($package->products as $product)
                                                <div class="flex items-center bg-gray-800/50 p-2 rounded-lg border border-gray-700">
                                                    @if($product->image)
                                                        <img src="{{ asset('storage/' . $product->image) }}" class="w-10 h-10 rounded-md object-cover mr-3">
                                                    @else
                                                        <div class="w-10 h-10 rounded-md bg-gray-700 flex items-center justify-center mr-3 text-gray-500 text-xs">IMG</div>
                                                    @endif
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-white truncate">{{ $product->name }}</p>
                                                        <p class="text-xs text-gray-300">{{ $product->pivot->quantity }} x Rp {{ number_format($product->price_silverchannel, 0, ',', '.') }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Cost Summary --}}
                                <div class="pt-4 border-t border-gray-700">
                                    <h4 class="text-xs font-semibold text-white uppercase mb-3">{{ __('Ringkasan Biaya') }}</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between text-gray-300">
                                            <span>Paket Pendaftaran</span>
                                            <span>Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                        </div>
                                        @if($package->products_total > 0)
                                            <div class="flex justify-between text-gray-300">
                                                <span>Produk Tambahan</span>
                                                <span>Rp {{ number_format($package->products_total, 0, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        <div class="flex justify-between text-gray-300" x-show="selectedShippingCost > 0" x-transition>
                                            <span>Ongkos Kirim</span>
                                            <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedShippingCost)"></span>
                                        </div>
                                        @if($package->insurance_cost > 0)
                                            <div class="flex justify-between text-gray-300">
                                                <span>Asuransi Pengiriman (LM)</span>
                                                <span>Rp {{ number_format($package->insurance_cost, 0, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        
                                        <!-- Packing Fee -->
                                        <div class="flex justify-between text-gray-300">
                                            <span>Biaya Packing</span>
                                            <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(packingFee)"></span>
                                        </div>

                                        <div class="pt-2 border-t border-gray-700 flex justify-between font-bold text-white text-base">
                                            <span>Total Bayar</span>
                                            <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format({{ $package->base_total + $package->insurance_cost }} + parseInt(selectedShippingCost || 0) + parseInt(packingFee || 0))"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($package->description)
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300 text-sm italic">
                                            {{ $package->description }}
                                        </p>
                                    </div>
                                @endif

                                @if($package->terms)
                                    <div class="pt-4 border-t border-gray-700">
                                        <h4 class="text-xs font-semibold text-white uppercase mb-2">{{ __('Syarat & Ketentuan') }}</h4>
                                        <p class="text-gray-300 text-xs">
                                            {{ $package->terms }}
                                        </p>
                                    </div>
                                @endif
                            </div>

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
                            <button type="submit" 
                                    class="button-shine w-full py-4 disabled:opacity-50 disabled:cursor-not-allowed disabled:grayscale"
                                    :disabled="!termsAccepted"
                            >
                                <span class="relative text-lg tracking-wide uppercase">{{ __('Lanjut ke Pembayaran') }}</span>
                                <svg class="icon ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                            
                            <div class="mt-6 text-center">
                                <a class="text-sm text-gray-300 hover:text-white transition-colors duration-200" href="{{ route('login') }}">
                                    {{ __('Sudah punya akun? Masuk disini') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Terms Modal -->
                <div x-show="showTermsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="showTermsModal = false" x-transition.opacity></div>
                    <div class="relative bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl" x-transition.scale.origin.center>
                        <!-- Header -->
                        <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                            <h3 class="text-xl font-bold text-white">Syarat & Ketentuan Silverchannel</h3>
                            <button type="button" @click="showTermsModal = false" class="text-gray-400 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 overflow-y-auto text-gray-300 space-y-4 custom-scrollbar">
                            <h4 class="text-lg font-semibold text-white">1. Pendaftaran & Keanggotaan</h4>
                            <p>Dengan mendaftar sebagai Silverchannel, Anda setuju untuk memberikan data yang valid dan akurat. Keanggotaan bersifat mengikat setelah disetujui oleh admin.</p>
                            
                            <h4 class="text-lg font-semibold text-white">2. Hak & Kewajiban</h4>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Anda berhak mendapatkan harga khusus Silverchannel dan komisi sesuai ketentuan.</li>
                                <li>Anda wajib menjaga nama baik brand dan tidak melakukan penipuan.</li>
                                <li>Dilarang menjual produk di bawah harga yang telah ditetapkan (Central Price List).</li>
                            </ul>
                            
                            <h4 class="text-lg font-semibold text-white">3. Pemesanan & Pembayaran</h4>
                            <p>Semua pemesanan harus melalui sistem EPI-OSS. Pembayaran wajib dilakukan sesuai nominal tagihan beserta kode unik jika ada.</p>
                            
                            <h4 class="text-lg font-semibold text-white">4. Kebijakan Retur & Refund</h4>
                            <p>Retur hanya diterima jika kesalahan dari pihak pengirim atau barang cacat produksi, wajib menyertakan video unboxing tanpa jeda.</p>
                            
                            <h4 class="text-lg font-semibold text-white">5. Pembatalan Keanggotaan</h4>
                            <p>Admin berhak menonaktifkan akun Silverchannel jika ditemukan pelanggaran terhadap syarat & ketentuan ini.</p>
                        </div>
                        
                        <!-- Footer -->
                        <div class="p-6 border-t border-gray-700 bg-gray-900/50 rounded-b-2xl flex justify-end">
                            <button type="button" @click="showTermsModal = false" class="px-6 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-medium transition-colors">
                                Saya Mengerti
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationSelector', (initialData = {}) => ({
                showTermsModal: false,
                termsAccepted: false,
                // Non-location fields for persistence
                name: initialData.oldName || '',
                nik: initialData.oldNik || '',
                email: initialData.oldEmail || '',
                whatsapp: initialData.oldWhatsapp || '',
                address: initialData.oldAddress || '',

                provinces: [],
                cities: [],
                subdistricts: [],
                villages: [],
                selectedProvince: initialData.oldProvinceId || '',
                selectedProvinceName: initialData.oldProvinceName || '',
                selectedCity: initialData.oldCityId || '',
                selectedCityName: initialData.oldCityName || '',
                selectedSubdistrict: initialData.oldSubdistrictId || '',
                selectedSubdistrictName: initialData.oldSubdistrictName || '',
                selectedVillage: initialData.oldVillageId || '',
                selectedVillageName: initialData.oldVillageName || '',
                postalCode: initialData.oldPostalCode || '',
                packageWeight: initialData.packageWeight || 1000,
                packingFee: initialData.packingFee || 0,
                
                selectedShippingService: initialData.oldShippingService || '',
                selectedShippingCost: initialData.oldShippingCost || 0,
                selectedShippingCourier: initialData.oldShippingCourier || 'jne',
                selectedShippingEtd: initialData.oldShippingEtd || '',
                shippingServices: [],
                
                isLoadingProvinces: false,
                isLoadingCities: false,
                isLoadingSubdistricts: false,
                isLoadingVillages: false,
                isLoadingShipping: false,
                
                apiRoutes: {
                    provinces: "{{ route('api.provinces') }}",
                    cities: "{{ url('api/cities') }}",
                    subdistricts: "{{ url('api/subdistricts') }}",
                    villages: "{{ url('api/villages') }}",
                    shipping: "{{ route('register.silver.shipping-services') }}"
                },

                init() {
                    // Load from session if no old input
                    this.loadFromSession();

                    this.fetchProvinces();
                    
                    // Chain the dependent fetches if data exists (e.g. from validation error or session)
                    if (this.selectedProvince) {
                        this.fetchCities().then(() => {
                            if (this.selectedCity) {
                                this.fetchSubdistricts().then(() => {
                                    if (this.selectedSubdistrict) {
                                        this.fetchVillages().then(() => {
                                            if (this.selectedVillage) {
                                                this.fetchShippingServices();
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }

                    // Watchers for auto-save
                    this.$watch('name', () => this.saveToSession());
                    this.$watch('nik', () => this.saveToSession());
                    this.$watch('email', () => this.saveToSession());
                    this.$watch('whatsapp', () => this.saveToSession());
                    this.$watch('address', () => this.saveToSession());
                    this.$watch('selectedProvince', () => this.saveToSession());
                    this.$watch('selectedCity', () => this.saveToSession());
                    this.$watch('selectedSubdistrict', () => this.saveToSession());
                    this.$watch('selectedVillage', () => this.saveToSession());
                    this.$watch('postalCode', () => this.saveToSession());

                    // Auto-focus on first error if exists
                    this.$nextTick(() => {
                        const firstError = document.querySelector('.text-red-400');
                        if (firstError) {
                            // Try to find the input associated with this error
                            // Assuming structure: Label -> Input -> Error
                            const parent = firstError.closest('div');
                            if (parent) {
                                const input = parent.querySelector('input, select, textarea');
                                if (input) {
                                    input.focus();
                                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            }
                        }
                    });
                },

                saveToSession() {
                    const state = {
                        name: this.name,
                        nik: this.nik,
                        email: this.email,
                        whatsapp: this.whatsapp,
                        address: this.address,
                        selectedProvince: this.selectedProvince,
                        selectedProvinceName: this.selectedProvinceName,
                        selectedCity: this.selectedCity,
                        selectedCityName: this.selectedCityName,
                        selectedSubdistrict: this.selectedSubdistrict,
                        selectedSubdistrictName: this.selectedSubdistrictName,
                        selectedVillage: this.selectedVillage,
                        selectedVillageName: this.selectedVillageName,
                        postalCode: this.postalCode
                    };
                    sessionStorage.setItem('register_silver_form', JSON.stringify(state));
                },

                loadFromSession() {
                    // If we have server-side old input (validation error), DO NOT load from session, 
                    // because old input is the most recent attempt.
                    const hasOldInput = initialData.oldName || initialData.oldNik || initialData.oldEmail || initialData.oldProvinceId;
                    
                    if (hasOldInput) {
                        return;
                    }
            
                    const saved = sessionStorage.getItem('register_silver_form');
                    if (saved) {
                        try {
                            const state = JSON.parse(saved);
                            this.name = state.name || '';
                            this.nik = state.nik || '';
                            this.email = state.email || '';
                            this.whatsapp = state.whatsapp || '';
                            this.address = state.address || '';
                            this.selectedProvince = state.selectedProvince || '';
                            this.selectedProvinceName = state.selectedProvinceName || '';
                            this.selectedCity = state.selectedCity || '';
                            this.selectedCityName = state.selectedCityName || '';
                            this.selectedSubdistrict = state.selectedSubdistrict || '';
                            this.selectedSubdistrictName = state.selectedSubdistrictName || '';
                            this.selectedVillage = state.selectedVillage || '';
                            this.selectedVillageName = state.selectedVillageName || '';
                            this.postalCode = state.postalCode || '';
                        } catch (e) {
                            console.error('Error parsing session data', e);
                        }
                    }
                },

                async fetchProvinces() {
                    this.isLoadingProvinces = true;
                    try {
                        const response = await fetch(this.apiRoutes.provinces);
                        const data = await response.json();
                        this.provinces = data;
                    } catch (error) {
                        console.error('Error fetching provinces:', error);
                        alert('Gagal mengambil data provinsi. Silakan refresh halaman.');
                    } finally {
                        this.isLoadingProvinces = false;
                    }
                },

                async fetchCities() {
                    if (!this.selectedProvince) {
                        this.cities = [];
                        this.subdistricts = [];
                        this.villages = [];
                        return;
                    }
                    
                    this.isLoadingCities = true;
                    
                    // Update Name
                    const province = this.provinces.find(p => p.province_id == this.selectedProvince);
                    if (province) this.selectedProvinceName = province.province;

                    try {
                        const response = await fetch(`${this.apiRoutes.cities}/${this.selectedProvince}`);
                        const data = await response.json();
                        this.cities = data;
                        
                        // Reset city if not matching
                        if (this.selectedCity && !this.cities.find(c => c.city_id == this.selectedCity)) {
                            this.selectedCity = '';
                            this.selectedCityName = '';
                            this.selectedSubdistrict = '';
                            this.selectedSubdistrictName = '';
                            this.selectedVillage = '';
                            this.selectedVillageName = '';
                        }
                    } catch (error) {
                        console.error('Error fetching cities:', error);
                    } finally {
                        this.isLoadingCities = false;
                    }
                },
                
                updateCityName() {
                    const city = this.cities.find(c => c.city_id == this.selectedCity);
                    if (city) {
                        this.selectedCityName = city.type + ' ' + city.city_name;
                        this.postalCode = city.postal_code || '';
                        // Do not fetch shipping immediately, wait for village
                        this.shippingServices = [];
                    }
                },

                async fetchSubdistricts() {
                    if (!this.selectedCity) {
                        this.subdistricts = [];
                        this.villages = [];
                        return;
                    }

                    this.isLoadingSubdistricts = true;

                    try {
                        const response = await fetch(`${this.apiRoutes.subdistricts}/${this.selectedCity}`);
                        const data = await response.json();
                        this.subdistricts = data;

                        // Reset subdistrict if not matching
                        if (this.selectedSubdistrict && !this.subdistricts.find(s => s.subdistrict_id == this.selectedSubdistrict)) {
                            this.selectedSubdistrict = '';
                            this.selectedSubdistrictName = '';
                            this.selectedVillage = '';
                            this.selectedVillageName = '';
                        }
                    } catch (error) {
                        console.error('Error fetching subdistricts:', error);
                    } finally {
                        this.isLoadingSubdistricts = false;
                    }
                },

                updateSubdistrictName() {
                    const subdistrict = this.subdistricts.find(s => s.subdistrict_id == this.selectedSubdistrict);
                    if (subdistrict) {
                        this.selectedSubdistrictName = subdistrict.subdistrict_name;
                        // Do not fetch shipping yet
                        this.shippingServices = [];
                    }
                },

                async fetchVillages() {
                    if (!this.selectedSubdistrict) {
                        this.villages = [];
                        return;
                    }

                    this.isLoadingVillages = true;

                    try {
                        const response = await fetch(`${this.apiRoutes.villages}/${this.selectedSubdistrict}`);
                        const data = await response.json();
                        this.villages = data;

                        // Reset village if not matching
                        if (this.selectedVillage && !this.villages.find(v => v.village_id == this.selectedVillage)) {
                            this.selectedVillage = '';
                            this.selectedVillageName = '';
                        }
                    } catch (error) {
                        console.error('Error fetching villages:', error);
                    } finally {
                        this.isLoadingVillages = false;
                    }
                },

                updateVillageName() {
                    const village = this.villages.find(v => v.village_id == this.selectedVillage);
                    if (village) {
                        this.selectedVillageName = village.village_name;
                        // Fetch shipping now
                    }
                },

                async fetchShippingServices() {
                    if (!this.selectedVillage) return; 

                    // Use Village ID for API ID Shipping Cost
                    let destination = this.selectedVillage;
                    let destinationType = 'village';

                    this.isLoadingShipping = true;
                    this.shippingServices = [];
                    this.selectedShippingService = '';
                    this.selectedShippingCost = 0;

                    try {
                        const response = await fetch(this.apiRoutes.shipping, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                destination: destination,
                                destination_type: destinationType,
                                weight: this.packageWeight, 
                                courier: 'jne'
                            })
                        });
                        
                        const data = await response.json();
                        if (data.error) {
                             console.error(data.error);
                             // Optional: alert(data.error);
                        } else {
                            this.shippingServices = data;
                        }
                    } catch (error) {
                        console.error('Error fetching shipping:', error);
                    } finally {
                        this.isLoadingShipping = false;
                    }
                },

                selectShipping(service, cost, etd, courier) {
                    this.selectedShippingService = service;
                    this.selectedShippingCost = cost;
                    this.selectedShippingEtd = etd;
                    this.selectedShippingCourier = courier;
                }
            }));
        });
    </script>

    <style>
        @keyframes shimmer {
            0% { transform: translateX(-100%) skewX(-15deg); }
            100% { transform: translateX(200%) skewX(-15deg); }
        }
    </style>
@endsection
