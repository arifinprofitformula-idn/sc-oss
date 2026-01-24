<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Toko') }}
        </h2>
    </x-slot>

@php
    $defaultOperatingHours = [
        'monday' => ['open' => '09:00', 'close' => '17:00', 'is_closed' => false],
        'tuesday' => ['open' => '09:00', 'close' => '17:00', 'is_closed' => false],
        'wednesday' => ['open' => '09:00', 'close' => '17:00', 'is_closed' => false],
        'thursday' => ['open' => '09:00', 'close' => '17:00', 'is_closed' => false],
        'friday' => ['open' => '09:00', 'close' => '17:00', 'is_closed' => false],
        'saturday' => ['open' => '09:00', 'close' => '15:00', 'is_closed' => false],
        'sunday' => ['open' => '09:00', 'close' => '17:00', 'is_closed' => true],
    ];
@endphp

    <div class="py-12" x-data="storeSettings()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('silverchannel.store.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-4 pt-4 sm:px-6">
                        <nav class="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto" aria-label="Tabs">
                            <template x-for="tab in tabs" :key="tab.id">
                                <button type="button"
                                    @click="activeTab = tab.id"
                                    class="whitespace-nowrap pb-3 px-2 sm:px-3 border-b-2 font-medium text-xs sm:text-sm md:text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-t"
                                    :class="activeTab === tab.id 
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                                    <span x-text="tab.label"></span>
                                </button>
                            </template>
                        </nav>
                    </div>

                    <!-- Content -->
                    <div class="p-4 sm:p-6">
                        
                        <div x-show="activeTab === 'identity'" x-transition:enter="transition ease-out duration-300" x-cloak>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Identitas Toko</h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="logo" :value="__('Logo Toko')" />
                                    <div class="mt-2 flex items-center space-x-6">
                                        <div class="shrink-0">
                                            @if($store->logo_path)
                                                <img class="h-16 w-16 object-cover rounded-full" src="{{ asset('storage/' . $store->logo_path) }}" alt="Current Logo" />
                                            @else
                                                <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <span class="text-gray-500 text-xs">No Logo</span>
                                                </div>
                                            @endif
                                        </div>
                                        <label class="block">
                                            <span class="sr-only">Choose logo</span>
                                            <input type="file" name="logo" class="block w-full text-sm text-gray-500
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-full file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-indigo-50 file:text-indigo-700
                                                hover:file:bg-indigo-100
                                            "/>
                                        </label>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">PNG, JPG up to 2MB</p>
                                </div>

                                <div>
                                    <x-input-label for="name" :value="__('Nama Toko')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $store->name)" required />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Deskripsi Singkat')" />
                                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $store->description) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div x-show="activeTab === 'contact'" x-cloak>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Kontak & Alamat</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <x-input-label for="address" :value="__('Alamat Lengkap')" />
                                    <textarea id="address" name="address" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('address', $store->address) }}</textarea>
                                </div>

                                <div>
                                    <x-input-label for="province_id" :value="__('Provinsi')" />
                                    <select id="province_id" name="province_id" x-model="selectedProvince" @change="fetchCities()" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Pilih Provinsi</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province['province_id'] }}">{{ $province['province'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="city_id" :value="__('Kota/Kabupaten')" />
                                    <select id="city_id" name="city_id" x-model="selectedCity" @change="fetchSubdistricts()" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" :disabled="!cities.length">
                                        <option value="">Pilih Kota</option>
                                        <template x-for="city in cities" :key="city.city_id">
                                            <option :value="city.city_id" x-text="city.type + ' ' + city.city_name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="subdistrict_id" :value="__('Kecamatan')" />
                                    <select id="subdistrict_id" name="subdistrict_id" x-model="selectedSubdistrict" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" :disabled="!subdistricts.length">
                                        <option value="">Pilih Kecamatan</option>
                                        <template x-for="sub in subdistricts" :key="sub.subdistrict_id">
                                            <option :value="sub.subdistrict_id" x-text="sub.subdistrict_name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="postal_code" :value="__('Kode Pos')" />
                                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code', $store->postal_code)" required />
                                </div>

                                <div>
                                    <x-input-label for="phone" :value="__('No. Telepon')" />
                                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $store->phone)" />
                                </div>

                                <div>
                                    <x-input-label for="whatsapp" :value="__('WhatsApp')" />
                                    <x-text-input id="whatsapp" name="whatsapp" type="text" class="mt-1 block w-full" :value="old('whatsapp', $store->whatsapp)" placeholder="628123456789" />
                                </div>

                                <div class="col-span-2">
                                    <x-input-label for="email" :value="__('Email Resmi Toko')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $store->email)" />
                                </div>

                                <div class="col-span-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Media Sosial</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <x-input-label for="facebook" value="Facebook URL" />
                                            <x-text-input id="facebook" name="social_links[facebook]" type="url" class="mt-1 block w-full" :value="old('social_links.facebook', $store->social_links['facebook'] ?? '')" placeholder="https://facebook.com/..." />
                                        </div>
                                        <div>
                                            <x-input-label for="instagram" value="Instagram URL" />
                                            <x-text-input id="instagram" name="social_links[instagram]" type="url" class="mt-1 block w-full" :value="old('social_links.instagram', $store->social_links['instagram'] ?? '')" placeholder="https://instagram.com/..." />
                                        </div>
                                        <div>
                                            <x-input-label for="tiktok" value="TikTok URL" />
                                            <x-text-input id="tiktok" name="social_links[tiktok]" type="url" class="mt-1 block w-full" :value="old('social_links.tiktok', $store->social_links['tiktok'] ?? '')" placeholder="https://tiktok.com/@..." />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="activeTab === 'hours'" x-cloak>
                            <div class="space-y-6">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Jam Operasional Toko</h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Atur status buka/tutup toko dan jam layanan untuk setiap hari.</p>
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="hidden" name="is_open" value="0">
                                            <input type="checkbox" name="is_open" value="1" class="sr-only peer" {{ $store->is_open ? 'checked' : '' }}>
                                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Status Toko: <span x-text="$el.previousElementSibling.checked ? 'BUKA' : 'TUTUP'"></span></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                                    <div class="hidden md:grid md:grid-cols-[140px,1fr,120px] gap-4 text-xs font-semibold text-gray-500 dark:text-gray-400 mb-3">
                                        <div>Hari</div>
                                        <div>Jam Operasional</div>
                                        <div class="text-right">Status</div>
                                    </div>

                                    <div class="space-y-3">
                                        <template x-for="(day, key) in days" :key="key">
                                            <div class="border border-gray-100 dark:border-gray-800 rounded-md px-3 py-2 flex flex-col md:grid md:grid-cols-[140px,1fr,120px] md:items-center gap-2">
                                                <div class="flex items-center justify-between md:block">
                                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100 capitalize" x-text="day"></span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <input type="time" :name="'operating_hours['+day+'][open]'" class="w-28 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="operatingHours[day].open">
                                                    <span class="text-sm text-gray-500">sampai</span>
                                                    <input type="time" :name="'operating_hours['+day+'][close]'" class="w-28 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="operatingHours[day].close">
                                                </div>
                                                <div class="flex md:justify-end">
                                                    <label class="inline-flex items-center text-sm">
                                                        <input type="checkbox" :name="'operating_hours['+day+'][is_closed]'" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="operatingHours[day].is_closed">
                                                        <span class="ml-2 text-gray-600 dark:text-gray-300">Tutup</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="activeTab === 'payment'" x-cloak>
                             <!-- Payment tab content removed -->
                        </div>





                        <div class="mt-8 pt-4 border-t flex justify-end">
                            <x-primary-button>
                                {{ __('Update Settings') }}
                            </x-primary-button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    @php($scInitial = [
        'province_id' => $store->province_id,
        'city_id' => $store->city_id,
        'subdistrict_id' => $store->subdistrict_id,
        'is_open' => (bool) $store->is_open,
        'operating_hours' => $store->operating_hours ?? $defaultOperatingHours,
        'bank_details' => $store->bank_details ?? [],
    ])
    <script type="application/json" id="sc-store-settings-initial">{!! json_encode($scInitial, JSON_UNESCAPED_UNICODE) !!}</script>
    <script>
        function storeSettings() {
            return {
                activeTab: 'identity',
                tabs: [
                    { id: 'identity', label: 'Identitas Toko' },
                    { id: 'contact', label: 'Kontak & Alamat' },
                    { id: 'hours', label: 'Jam Operasional' },
                    { id: 'payment', label: 'Pembayaran' }
                ],
                
                // Location Data
                selectedProvince: JSON.parse(document.getElementById('sc-store-settings-initial').textContent).province_id,
                selectedCity: JSON.parse(document.getElementById('sc-store-settings-initial').textContent).city_id,
                selectedSubdistrict: JSON.parse(document.getElementById('sc-store-settings-initial').textContent).subdistrict_id,
                cities: [],
                subdistricts: [],

                // Operating Hours
                isOpen: JSON.parse(document.getElementById('sc-store-settings-initial').textContent).is_open,
                days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                operatingHours: JSON.parse(document.getElementById('sc-store-settings-initial').textContent).operating_hours,

                // Bank Details
                banks: JSON.parse(document.getElementById('sc-store-settings-initial').textContent).bank_details,

                init() {
                    if (this.selectedProvince) {
                        this.fetchCities(false);
                    }
                    if (this.selectedCity) {
                        this.fetchSubdistricts(false);
                    }
                    if (this.banks.length === 0) {
                        this.addBank();
                    }
                },

                async fetchCities(resetCity = true) {
                    if (!this.selectedProvince) {
                        this.cities = [];
                        return;
                    }
                    if (resetCity) {
                        this.selectedCity = '';
                        this.selectedSubdistrict = '';
                        this.subdistricts = [];
                    }
                    
                    try {
                        const response = await fetch(`/store/locations/cities/${this.selectedProvince}`);
                        this.cities = await response.json();
                    } catch (error) {
                        console.error('Error fetching cities:', error);
                    }
                },

                async fetchSubdistricts(resetSub = true) {
                    if (!this.selectedCity) {
                        this.subdistricts = [];
                        return;
                    }
                    if (resetSub) {
                        this.selectedSubdistrict = '';
                    }

                    try {
                        const response = await fetch(`/store/locations/subdistricts/${this.selectedCity}`);
                        this.subdistricts = await response.json();
                    } catch (error) {
                        console.error('Error fetching subdistricts:', error);
                    }
                },

                addBank() {
                    this.banks.push({ bank: '', number: '', name: '' });
                },

                removeBank(index) {
                    this.banks.splice(index, 1);
                }
            }
        }
    </script>
</x-app-layout>
