@extends('layouts.guest')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen py-12">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">EPI</span>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    EPI-OSS
                </span>
            </a>
        </div>

        <!-- Auth Card -->
        <div class="w-full sm:max-w-xl px-6 py-8 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-white mb-2">Silver Partner Registration</h2>
                <p class="text-gray-400 text-sm">
                    {{ __('Register as Silver Channel Partner and start earning') }}
                </p>
            </div>

            <form method="POST" action="{{ route('register.silver.store') }}" x-data="locationSelector()" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Full Name')" class="text-gray-300 font-medium mb-2" />
                    <x-text-input 
                        id="name" 
                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                        type="text" 
                        name="name" 
                        :value="old('name')" 
                        required 
                        autofocus 
                        placeholder="Enter full name"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400" />
                </div>

                <!-- NIK -->
                <div>
                    <x-input-label for="nik" :value="__('NIK (16 Digits)')" class="text-gray-300 font-medium mb-2" />
                    <x-text-input 
                        id="nik" 
                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                        type="number" 
                        name="nik" 
                        :value="old('nik')" 
                        required 
                        maxlength="16" 
                        placeholder="Enter NIK"
                    />
                    <x-input-error :messages="$errors->get('nik')" class="mt-2 text-red-400" />
                </div>

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
                        placeholder="Enter email address"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                </div>

                <!-- WhatsApp -->
                <div>
                    <x-input-label for="whatsapp" :value="__('WhatsApp Number (start with +62)')" class="text-gray-300 font-medium mb-2" />
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

                <!-- Location Selector -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Province -->
                    <div>
                        <x-input-label for="province_id" :value="__('Province')" class="text-gray-300 font-medium mb-2" />
                        <select id="province_id" name="province_id" x-model="selectedProvince" @change="fetchCities()" class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm">
                            <option value="">Select Province</option>
                            <template x-for="province in provinces" :key="province.province_id">
                                <option :value="province.province_id" x-text="province.province"></option>
                            </template>
                        </select>
                        <input type="hidden" name="province_name" x-model="selectedProvinceName">
                        <x-input-error :messages="$errors->get('province_id')" class="mt-2 text-red-400" />
                    </div>

                    <!-- City -->
                    <div>
                        <x-input-label for="city_id" :value="__('City')" class="text-gray-300 font-medium mb-2" />
                        <select id="city_id" name="city_id" x-model="selectedCity" @change="updateCityName()" :disabled="!selectedProvince" class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm">
                            <option value="">Select City</option>
                            <template x-for="city in cities" :key="city.city_id">
                                <option :value="city.city_id" x-text="city.type + ' ' + city.city_name"></option>
                            </template>
                        </select>
                        <input type="hidden" name="city_name" x-model="selectedCityName">
                        <x-input-error :messages="$errors->get('city_id')" class="mt-2 text-red-400" />
                    </div>
                </div>

                <!-- Referral Code -->
                <div>
                    <x-input-label for="referral_code" :value="__('Referral Code (Optional)')" class="text-gray-300 font-medium mb-2" />
                    <x-text-input 
                        id="referral_code" 
                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                        type="text" 
                        name="referral_code" 
                        :value="old('referral_code')" 
                        maxlength="10" 
                        placeholder="Enter referral code"
                    />
                    <x-input-error :messages="$errors->get('referral_code')" class="mt-2 text-red-400" />
                </div>
                
                @if(isset($packages) && $packages->isNotEmpty())
                    @if($packages->count() > 1)
                        <div>
                            <x-input-label for="package_id" :value="__('Select Package')" class="text-gray-300 font-medium mb-2" />
                            <select id="package_id" name="package_id" class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent rounded-md shadow-sm">
                                @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->name }} - Rp {{ number_format($pkg->price) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="package_id" value="{{ $packages->first()->id }}">
                    @endif
                @endif

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
                        placeholder="Create password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-300 font-medium mb-2" />
                    <x-text-input 
                        id="password_confirmation" 
                        class="block w-full bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent" 
                        type="password" 
                        name="password_confirmation" 
                        required 
                        placeholder="Confirm password"
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
                </div>

                <div class="flex items-center justify-between pt-4">
                    <a class="text-sm text-cyan-400 hover:text-cyan-300 transition-colors duration-200" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 focus:ring-offset-gray-900 shadow-lg hover:shadow-cyan-500/25"
                    >
                        {{ __('Register') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationSelector', () => ({
                provinces: [],
                cities: [],
                selectedProvince: '',
                selectedProvinceName: '',
                selectedCity: '',
                selectedCityName: '',

                init() {
                    this.fetchProvinces();
                },

                fetchProvinces() {
                    fetch('{{ route("api.locations.provinces") }}')
                        .then(res => res.json())
                        .then(data => {
                            this.provinces = data;
                            // Check for old value
                            @if(old('province_id'))
                                this.selectedProvince = '{{ old('province_id') }}';
                                this.selectedProvinceName = '{{ old('province_name') }}';
                                this.fetchCities();
                            @endif
                        });
                },

                fetchCities() {
                    if (!this.selectedProvince) {
                        this.cities = [];
                        return;
                    }
                    
                    // Set Province Name
                    let prov = this.provinces.find(p => p.province_id == this.selectedProvince);
                    if(prov) this.selectedProvinceName = prov.province;

                    const url = '{{ route("api.locations.cities", ["province" => "0"]) }}'.replace('/0', '/' + this.selectedProvince);
                     fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            this.cities = data;
                             // Check for old value
                             @if(old('city_id'))
                                if (this.selectedProvince == '{{ old('province_id') }}') {
                                    this.selectedCity = '{{ old('city_id') }}';
                                    this.selectedCityName = '{{ old('city_name') }}';
                                }
                            @endif
                        });
                },

                updateCityName() {
                     let city = this.cities.find(c => c.city_id == this.selectedCity);
                     if(city) this.selectedCityName = city.type + ' ' + city.city_name;
                }
            }));
        });
    </script>
@endsection