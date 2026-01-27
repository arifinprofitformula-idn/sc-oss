@extends('layouts.guest')

@section('content')
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        <div class="mb-8 flex justify-center">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo />
            </a>
        </div>

        <div class="max-w-7xl mx-auto">
            <form method="POST" action="{{ route('register.silver.store') }}" x-data="locationSelector({
                oldProvinceId: {{ json_encode(old('province_id')) }},
                oldProvinceName: {{ json_encode(old('province_name')) }},
                oldCityId: {{ json_encode(old('city_id')) }},
                oldCityName: {{ json_encode(old('city_name')) }},
                packageWeight: {{ $package->weight ?? 1000 }}
            })">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package->id }}">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Registration Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6 sm:p-8">
                            <div class="mb-6">
                                <h2 class="text-2xl font-bold text-white mb-2">{{ __('Form Registrasi') }}</h2>
                                <p class="text-gray-400 text-sm">
                                    {{ __('Lengkapi data diri Anda untuk mendaftar sebagai Silver Channel Partner.') }}
                                </p>
                            </div>

                            <div class="space-y-6">
                                <!-- Name -->
                                <div>
                                    <x-input-label for="name" :value="__('Nama Lengkap (Sesuai KTP)')" class="text-gray-300 font-medium mb-2" />
                                    <x-text-input 
                                        id="name" 
                                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                        type="text" 
                                        name="name" 
                                        :value="old('name')" 
                                        required 
                                        autofocus 
                                        placeholder="Masukkan nama lengkap"
                                    />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400" />
                                </div>

                                <!-- NIK -->
                                <div>
                                    <x-input-label for="nik" :value="__('NIK (16 Digit)')" class="text-gray-300 font-medium mb-2" />
                                    <x-text-input 
                                        id="nik" 
                                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                        type="number" 
                                        name="nik" 
                                        :value="old('nik')" 
                                        required 
                                        maxlength="16" 
                                        placeholder="Masukkan NIK"
                                    />
                                    <x-input-error :messages="$errors->get('nik')" class="mt-2 text-red-400" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Email -->
                                    <div>
                                        <x-input-label for="email" :value="__('Email')" class="text-gray-300 font-medium mb-2" />
                                        <x-text-input 
                                            id="email" 
                                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                            type="email" 
                                            name="email" 
                                            :value="old('email')" 
                                            required 
                                            placeholder="contoh@email.com"
                                        />
                                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                                    </div>

                                    <!-- WhatsApp -->
                                    <div>
                                        <x-input-label for="whatsapp" :value="__('Nomor WhatsApp (awali +62)')" class="text-gray-300 font-medium mb-2" />
                                        <x-text-input 
                                            id="whatsapp" 
                                            class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                                            type="text" 
                                            name="whatsapp" 
                                            :value="old('whatsapp')" 
                                            required 
                                            placeholder="+628..." 
                                        />
                                        <x-input-error :messages="$errors->get('whatsapp')" class="mt-2 text-red-400" />
                                    </div>
                                </div>

                                <!-- Location Selector -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Province -->
                                    <div>
                                        <x-input-label for="province_id" :value="__('Provinsi')" class="text-gray-300 font-medium mb-2" />
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
                                        <x-input-label for="city_id" :value="__('Kota/Kabupaten')" class="text-gray-300 font-medium mb-2" />
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
                                        <x-input-label for="subdistrict_id" :value="__('Kecamatan')" class="text-gray-300 font-medium mb-2" />
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
                                        <x-input-label for="village_id" :value="__('Kelurahan')" class="text-gray-300 font-medium mb-2" />
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
                                        <x-input-label for="postal_code" :value="__('Kode Pos')" class="text-gray-300 font-medium mb-2" />
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
                                    <x-input-label for="address" :value="__('Alamat Lengkap (Opsional)')" class="text-gray-300 font-medium mb-2" />
                                    <textarea 
                                        id="address" 
                                        name="address" 
                                        rows="2"
                                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm"
                                        placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                                    >{{ old('address') }}</textarea>
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
                                        <span class="ml-2 text-gray-400 text-sm">Memuat layanan pengiriman...</span>
                                    </div>

                                    <div x-show="!isLoadingShipping && shippingServices.length === 0 && selectedCity" class="text-gray-400 text-sm py-2">
                                        <p>Tidak ada layanan pengiriman yang tersedia untuk lokasi ini.</p>
                                    </div>

                                    <div x-show="!selectedCity" class="text-gray-500 text-sm italic py-2">
                                        Silakan pilih lokasi (Provinsi & Kota) terlebih dahulu.
                                    </div>

                                    <div class="grid grid-cols-1 gap-4" x-show="!isLoadingShipping && shippingServices.length > 0">
                                        <template x-for="courier in shippingServices" :key="courier.code">
                                            <div class="space-y-3">
                                                <h4 class="text-gray-300 text-sm font-semibold uppercase tracking-wider" x-text="courier.name"></h4>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <template x-for="cost in courier.costs" :key="cost.service">
                                                        <div 
                                                            @click="selectShipping(cost.service, cost.cost[0].value, cost.cost[0].etd, courier.code)"
                                                            class="relative rounded-lg border p-4 cursor-pointer hover:border-cyan-500 transition-all duration-200"
                                                            :class="selectedShippingService === cost.service && selectedShippingCourier === courier.code ? 'bg-cyan-900/20 border-cyan-500 ring-1 ring-cyan-500' : 'bg-gray-800/30 border-gray-700'"
                                                        >
                                                            <div class="flex justify-between items-start">
                                                                <div>
                                                                    <p class="text-white font-bold" x-text="cost.service"></p>
                                                                    <p class="text-gray-400 text-xs mt-1" x-text="cost.description"></p>
                                                                    <p class="text-gray-500 text-xs mt-1" x-text="'Estimasi: ' + (cost.cost[0].etd ? cost.cost[0].etd + ' hari' : '-')"></p>
                                                                </div>
                                                                <div class="text-right">
                                                                    <p class="text-cyan-400 font-bold" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(cost.cost[0].value)"></p>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Selected Indicator -->
                                                            <div x-show="selectedShippingService === cost.service && selectedShippingCourier === courier.code" class="absolute top-2 right-2">
                                                                <svg class="w-5 h-5 text-cyan-500" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                </svg>
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
                                    <x-input-label for="referral_code" :value="__('Kode Referral')" class="text-gray-300 font-medium mb-2" />
                                    <x-text-input 
                                        id="referral_code" 
                                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent {{ $referralCode ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                        type="text" 
                                        name="referral_code" 
                                        :value="old('referral_code', $referralCode)" 
                                        maxlength="20" 
                                        placeholder="Masukkan kode referral jika ada"
                                        :readonly="$referralCode ? true : false"
                                    />
                                    @if($referralCode)
                                        <p class="text-xs text-cyan-400 mt-1">* Kode referral otomatis terisi dari link afiliasi.</p>
                                    @endif
                                    <x-input-error :messages="$errors->get('referral_code')" class="mt-2 text-red-400" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Password -->
                                    <div>
                                        <x-input-label for="password" :value="__('Password')" class="text-gray-300 font-medium mb-2" />
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
                                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-gray-300 font-medium mb-2" />
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
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Package Info -->
                    <div class="lg:col-span-1">
                        <div class="bg-gradient-to-br from-blue-900/80 to-gray-900/80 backdrop-blur-xl border border-blue-700/50 rounded-2xl shadow-2xl p-6 sm:p-8 sticky top-8">
                            
                            <!-- Package Image -->
                            <div class="mb-6 -mx-6 -mt-6 sm:-mx-8 sm:-mt-8 rounded-t-2xl overflow-hidden relative group">
                                @if($package->image)
                                    <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <!-- Fallback Placeholder -->
                                    <div class="w-full aspect-[4/3] bg-gray-800 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                @if($package->original_price && $package->original_price > $package->price)
                                    <div class="absolute top-4 right-4 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                        PROMO
                                    </div>
                                @endif
                            </div>

                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-white mb-2">{{ $package->name }}</h3>
                                
                                @if($package->original_price && $package->original_price > $package->price)
                                    <div class="flex flex-col items-center justify-center">
                                         <span class="text-gray-400 line-through text-sm">Rp {{ number_format($package->original_price, 0, ',', '.') }}</span>
                                         <div class="text-3xl font-extrabold text-cyan-400">
                                            Rp {{ number_format($package->price, 0, ',', '.') }}
                                        </div>
                                         <span class="bg-red-500/20 text-red-300 text-xs px-2 py-0.5 rounded-full mt-1 border border-red-500/30">
                                            Hemat Rp {{ number_format($package->original_price - $package->price, 0, ',', '.') }}
                                         </span>
                                    </div>
                                @else
                                    <div class="text-3xl font-extrabold text-cyan-400">
                                        Rp {{ number_format($package->price, 0, ',', '.') }}
                                    </div>
                                @endif
                                <p class="text-gray-400 text-sm mt-2">Biaya Pendaftaran</p>
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
                                
                                @if($package->description)
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-400 text-sm italic">
                                            {{ $package->description }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <button 
                                type="submit" 
                                class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 focus:ring-offset-gray-900 shadow-lg hover:shadow-cyan-500/25 flex items-center justify-center"
                            >
                                <span>{{ __('Lanjut ke Pembayaran') }}</span>
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                            
                            <div class="mt-6 text-center">
                                <a class="text-sm text-gray-400 hover:text-white transition-colors duration-200" href="{{ route('login') }}">
                                    {{ __('Sudah punya akun? Masuk disini') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationSelector', (initialData = {}) => ({
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
                    this.fetchProvinces();
                    if (this.selectedProvince) {
                        this.fetchCities().then(() => {
                            if (this.selectedCity) {
                                this.fetchSubdistricts().then(() => {
                                    if (this.selectedSubdistrict) {
                                        this.fetchVillages();
                                    }
                                });
                            }
                        });
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
@endsection
