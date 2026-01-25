<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Global Store Settings') }}
        </h2>
    </x-slot>

    @php
        $storeInitial = [
            'province_id' => $store->province_id,
            'city_id' => $store->city_id,
            'subdistrict_id' => $store->subdistrict_id,
            'is_open' => (bool) $store->is_open,
            'operating_hours' => $operatingHours,
            'store_menu_active' => (bool) ($settings['silverchannel_store_menu_active'] ?? false),
            'unique_code_active' => (bool) ($settings['store_payment_unique_code_active'] ?? false),
            'payment_timeout' => (int) ($settings['store_payment_timeout'] ?? 60),
            'bank_details' => $bankDetails ?? [],
            'payment_methods' => $store->payment_methods ?? [],
            'logo_url' => $store->logo_path ? asset('storage/' . $store->logo_path) : null,
        ];
    @endphp

    <script type="application/json" id="store-settings-initial">{!! json_encode($storeInitial, JSON_UNESCAPED_UNICODE) !!}</script>

    <script>
        function storeSettings() {
            let initialData = {};
            try {
                const el = document.getElementById('store-settings-initial');
                if (el) initialData = JSON.parse(el.textContent);
            } catch (e) {
                console.error('Failed to parse initial data', e);
            }

            // Get active tab from server variable
            let activeTabValue = "{{ $tab ?? 'identity' }}";
            
            // Fallback: check URL path if server variable seems default but URL says otherwise
            const pathSegments = window.location.pathname.split('/');
            const lastSegment = pathSegments[pathSegments.length - 1];
            const validTabs = ['identity', 'contact', 'hours', 'payment'];
            
            if (validTabs.includes(lastSegment)) {
                activeTabValue = lastSegment;
            }

            if (!activeTabValue || activeTabValue === '') {
                activeTabValue = 'identity';
            }

            return {
                activeTab: activeTabValue,
                isLoading: false,
                tabs: [
                    { id: 'identity', label: 'Identitas Toko' },
                    { id: 'contact', label: 'Kontak & Alamat' },
                    { id: 'hours', label: 'Jam Operasional' },
                    { id: 'payment', label: 'Pembayaran' }
                ],
                urls: {
                    identity: "{{ route('admin.settings.store.identity') }}",
                    contact: "{{ route('admin.settings.store.contact') }}",
                    hours: "{{ route('admin.settings.store.hours') }}",
                    payment: "{{ route('admin.settings.store.payment') }}",
                    toggle: "{{ route('admin.settings.store.toggle') }}",
                },
                links: {
                    identity: "{{ route('admin.settings.store.tab', ['tab' => 'identity']) }}",
                    contact: "{{ route('admin.settings.store.tab', ['tab' => 'contact']) }}",
                    hours: "{{ route('admin.settings.store.tab', ['tab' => 'hours']) }}",
                    payment: "{{ route('admin.settings.store.tab', ['tab' => 'payment']) }}",
                },
                
                // State
                storeMenuActive: !!initialData.store_menu_active,
                uniqueCodeActive: !!initialData.unique_code_active,
                paymentTimeout: initialData.payment_timeout || 60,
                selectedProvince: initialData.province_id,
                selectedCity: initialData.city_id,
                selectedSubdistrict: initialData.subdistrict_id,
                cities: [],
                subdistricts: [],

                isOpen: initialData.is_open,
                days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                operatingHours: initialData.operating_hours,

                paymentMethods: initialData.payment_methods || [],
                banks: initialData.bank_details || [],

                // Logo Upload State
                logoPreview: initialData.logo_url,
                logoValid: false,
                logoError: null,
                
                validateLogo(event) {
                    const file = event.target.files[0];
                    this.logoError = null;
                    this.logoValid = false;
                    
                    if (!file) return;
                    
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        this.logoError = 'Format file harus JPEG, PNG, atau WEBP.';
                        return;
                    }
                    
                    if (file.size > 2 * 1024 * 1024) { // 2MB limit
                        this.logoError = 'Ukuran file maksimal 2MB.';
                        return;
                    }
                    
                    this.logoValid = true;
                    
                    const reader = new FileReader();
                    reader.onload = (e) => { this.logoPreview = e.target.result; };
                    reader.readAsDataURL(file);
                },

                init() {
                    this.$watch('activeTab', (val) => {
                        if (val === 'contact') {
                            if (this.selectedProvince) {
                                this.fetchCities(false);
                            }
                            if (this.selectedCity) {
                                this.fetchSubdistricts(false);
                            }
                        }
                    });
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
                        const response = await fetch(`/admin/integrations/shipping/cities/${this.selectedProvince}`);
                        const data = await response.json();
                        this.cities = Array.isArray(data) ? data : (data.results || []);
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
                        const response = await fetch(`/admin/integrations/shipping/subdistricts/${this.selectedCity}`);
                        const data = await response.json();
                        this.subdistricts = Array.isArray(data) ? data : (data.results || []);
                    } catch (error) {
                        console.error('Error fetching subdistricts:', error);
                    }
                },

                csrf() {
                    const m = document.querySelector('input[name=_token]');
                    return m ? m.value : '';
                },

                async performSave(url, method, body, successMessage) {
                    this.isLoading = true;
                    try {
                        const headers = { 
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json'
                        };
                        if (!(body instanceof FormData)) {
                            headers['Content-Type'] = 'application/json';
                            body = JSON.stringify(body);
                        }
                        
                        const res = await fetch(url, { method, headers, body });
                        const data = await res.json();
                        
                        if (res.ok && data.success) {
                            Alpine.store('toast').show(successMessage, 'success');
                            return data;
                        } else {
                            Alpine.store('toast').show(data.error || data.message || 'Gagal menyimpan pengaturan', 'error');
                            return null;
                        }
                    } catch (e) {
                        console.error('Save error:', e);
                        Alpine.store('toast').show('Terjadi kesalahan jaringan: ' + (e.message || 'Unknown error'), 'error');
                        return null;
                    } finally {
                        this.isLoading = false;
                    }
                },

                async saveToggle() {
                    const payload = {
                        silverchannel_store_menu_active: this.storeMenuActive ? 1 : 0,
                    };
                    await this.performSave(this.urls.toggle, 'PATCH', payload, 'Pengaturan menu Store Settings berhasil diperbarui');
                },

                async saveIdentity() {
                    const fd = new FormData();
                    const name = document.getElementById('name')?.value || '';
                    const desc = document.getElementById('description')?.value || '';
                    const logo = document.querySelector('input[name=logo]')?.files?.[0] || null;
                    fd.append('name', name);
                    fd.append('description', desc);
                    fd.append('is_open', this.isOpen ? '1' : '0');
                    if (logo) fd.append('logo', logo);
                    
                    const response = await this.performSave(this.urls.identity, 'PATCH', fd, 'Identitas toko berhasil disimpan');
                    if (response && response.logo_url) {
                         this.logoPreview = response.logo_url;
                         // Reset file input
                         const fileInput = document.getElementById('logo_input');
                         if (fileInput) fileInput.value = '';
                    }
                },

                async saveContact() {
                    const payload = {
                        address: document.getElementById('address')?.value || '',
                        province_id: this.selectedProvince || '',
                        city_id: this.selectedCity || '',
                        subdistrict_id: this.selectedSubdistrict || '',
                        postal_code: document.getElementById('postal_code')?.value || '',
                        phone: document.getElementById('phone')?.value || '',
                        whatsapp: document.getElementById('whatsapp')?.value || '',
                        email: document.getElementById('email')?.value || ''
                    };
                    await this.performSave(this.urls.contact, 'PATCH', payload, 'Kontak & Alamat berhasil disimpan');
                },

                async saveHours() {
                    await this.performSave(this.urls.hours, 'PATCH', { 
                        operating_hours: this.operatingHours,
                        is_open: this.isOpen ? 1 : 0
                    }, 'Jam operasional berhasil disimpan');
                },

                addBank() {
                    this.banks.push({ bank: '', number: '', name: '' });
                },

                removeBank(index) {
                    this.banks.splice(index, 1);
                },

                async savePayment() {
                    await this.performSave(this.urls.payment, 'PATCH', {
                        payment_methods: this.paymentMethods,
                        bank_details: this.banks,
                        unique_code_active: this.uniqueCodeActive ? 1 : 0,
                        payment_timeout: this.paymentTimeout
                    }, 'Pengaturan pembayaran berhasil disimpan');
                },

                async uploadLogo(file, index) {
                    if (!file) return;
                    
                    const fd = new FormData();
                    fd.append('logo', file);
                    fd.append('_token', this.csrf());

                    try {
                        const res = await fetch("{{ route('admin.settings.store.bank-logo') }}", {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: fd
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.banks[index].logo = data.url;
                            Alpine.store('toast').show('Logo bank berhasil diupload', 'success');
                        } else {
                            Alpine.store('toast').show(data.message || 'Gagal upload logo', 'error');
                        }
                    } catch (e) {
                        console.error('Upload error:', e);
                        Alpine.store('toast').show('Gagal upload logo', 'error');
                    }
                }
            };
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="py-12" x-data="storeSettings()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.settings.store.update') }}" method="POST" enctype="multipart/form-data" x-ref="mainForm">
                @csrf
                @method('PATCH')

                <!-- 7. Toggle Menu Access (Top Section) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-lg">Aktifkan Menu 'Store Settings' untuk Silverchannel</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Jika diaktifkan, user dengan role Silverchannel dapat mengakses menu Pengaturan Toko di dashboard mereka.</p>
                            </div>
                            
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="silverchannel_store_menu_active" value="1" class="sr-only peer"
                                    x-model="storeMenuActive"
                                    @change="saveToggle()">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Store Details Tabs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <!-- Horizontal Navigation -->
                        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                            @php($navTabs = [
                                ['id' => 'identity', 'label' => 'Identitas Toko'],
                                ['id' => 'contact', 'label' => 'Kontak & Alamat'],
                                ['id' => 'hours', 'label' => 'Jam Operasional'],
                                ['id' => 'payment', 'label' => 'Pembayaran'],
                            ])
                            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs" role="tablist">
                                @foreach($navTabs as $t)
                                    <a href="{{ route('admin.settings.store.tab', ['tab' => $t['id']]) }}"
                                       class="whitespace-nowrap pb-4 px-3 md:px-4 border-b-2 font-medium text-sm md:text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded transition-colors duration-150 ease-in-out {{ ($tab ?? 'identity') === $t['id'] 
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                        {{ $t['label'] }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>

                        <!-- Content Sections -->
                        <div class="mt-6">

                        <div x-show="activeTab === 'identity'" x-transition:enter="transition ease-out duration-300">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Identitas Toko</h3>

                            <div class="grid grid-cols-1 gap-6">
                                <!-- Logo Section (Centered & Circular like Profile) -->
                                <div class="flex flex-col items-center mb-4">
                                    <div class="relative w-32 h-32 mb-4 group">
                                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white dark:border-gray-700 shadow-lg bg-gray-100 dark:bg-gray-700 relative">
                                            <!-- Preview Image -->
                                            <template x-if="logoPreview">
                                                <img :src="logoPreview" class="w-full h-full object-cover">
                                            </template>
                                            
                                            <!-- Fallback Icon -->
                                            <template x-if="!logoPreview">
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                                                </div>
                                            </template>

                                            <!-- Overlay Upload Icon -->
                                            <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 cursor-pointer rounded-full" onclick="document.getElementById('logo_input').click()">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="file" id="logo_input" name="logo" class="hidden" accept="image/png, image/jpeg, image/jpg, image/webp" @change="validateLogo">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Klik gambar untuk mengubah logo</p>
                                    <p class="text-xs text-gray-400">JPG, PNG, WEBP (Max 2MB)</p>
                                    <p x-show="logoError" x-text="logoError" class="text-red-500 text-xs mt-2"></p>
                                </div>

                                <div>
                                    <x-input-label for="name" :value="__('Nama Toko')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $store->name) }}" required />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Deskripsi Singkat')" />
                                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $store->description) }}</textarea>
                                </div>
                            </div>

                            <div class="flex justify-end mt-6 pt-4 border-t dark:border-gray-700">
                                <x-primary-button type="button" @click="saveIdentity()" x-bind:disabled="isLoading" class="min-w-[150px] justify-center">
                                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isLoading ? 'Saving...' : 'Update Identitas'"></span>
                                </x-primary-button>
                            </div>
                        </div>

                        <div x-show="activeTab === 'contact'">
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
                                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" value="{{ old('postal_code', $store->postal_code) }}" required />
                                </div>

                                <div>
                                    <x-input-label for="phone" :value="__('No. Telepon')" />
                                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ old('phone', $store->phone) }}" />
                                </div>

                                <div>
                                    <x-input-label for="whatsapp" :value="__('WhatsApp')" />
                                    <x-text-input id="whatsapp" name="whatsapp" type="text" class="mt-1 block w-full" value="{{ old('whatsapp', $store->whatsapp) }}" placeholder="628123456789" />
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
                            
                            <div class="mt-6 flex justify-end">
                                <x-primary-button type="button" @click="saveContact()" x-bind:disabled="isLoading">
                                    <span x-show="!isLoading">Simpan Kontak</span>
                                    <span x-show="isLoading">Menyimpan...</span>
                                </x-primary-button>
                            </div>
                        </div>

                        <!-- 3. Jam Operasional -->
                        <div x-show="activeTab === 'hours'">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Jam Operasional</h3>
                            
                            <div class="mb-4">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_open" value="0">
                                    <input type="checkbox" name="is_open" value="1" class="sr-only peer" x-model="isOpen">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Status Toko: <span x-text="isOpen ? 'BUKA' : 'TUTUP'"></span></span>
                                </label>
                            </div>

                            <div class="space-y-4">
                                @foreach($days as $day)
                                    <div class="flex items-center space-x-4 border-b pb-2">
                                        <div class="w-24 font-medium capitalize">{{ $day }}</div>
                                        <div class="flex items-center space-x-2">
                                            <input type="time" name="operating_hours[{{ $day }}][open]" 
                                                class="rounded border-gray-300 text-sm" 
                                                x-model="operatingHours['{{ $day }}'].open">
                                            <span>-</span>
                                            <input type="time" name="operating_hours[{{ $day }}][close]" 
                                                class="rounded border-gray-300 text-sm" 
                                                x-model="operatingHours['{{ $day }}'].close">
                                        </div>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="operating_hours[{{ $day }}][is_closed]" 
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                                x-model="operatingHours['{{ $day }}'].is_closed">
                                            <span class="ml-2 text-sm text-gray-600">Tutup</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-end mt-6 pt-4 border-t dark:border-gray-700">
                                 <x-primary-button type="button" @click="saveHours()" x-bind:disabled="isLoading" class="min-w-[150px] justify-center">
                                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isLoading ? 'Saving...' : 'Update Settings'"></span>
                                </x-primary-button>
                            </div>
                        </div>

                        <!-- 4. Pembayaran -->
                        <div x-show="activeTab === 'payment'">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Pengaturan Pembayaran</h3>
                            
                            <div class="mb-6">
                                <h4 class="text-md font-medium mb-2">Metode Pembayaran Aktif</h4>
                                <div class="space-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="payment_methods[]" value="bank_transfer" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="paymentMethods">
                                        <span class="ml-2 dark:text-gray-300">Transfer Bank</span>
                                    </label>
                                    <br>
                                    <label class="inline-flex items-center opacity-50 cursor-not-allowed">
                                        <input type="checkbox" disabled class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 dark:text-gray-300">Payment Gateway (Coming Soon)</span>
                                    </label>
                                    <br>
                                    <label class="inline-flex items-center opacity-50 cursor-not-allowed">
                                        <input type="checkbox" disabled class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 dark:text-gray-300">E-Wallet</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Payment Timeout -->
                            <div class="mb-6 border-t pt-4 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-md font-medium">Batas Waktu Pembayaran (Menit)</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            Pesanan akan otomatis dibatalkan jika belum dibayar dalam waktu yang ditentukan.
                                        </p>
                                    </div>
                                    <div class="w-32">
                                        <input type="number" min="1" class="rounded border-gray-300 text-sm w-full text-center" x-model="paymentTimeout">
                                    </div>
                                </div>
                            </div>

                            <!-- Unique Code Toggle -->
                            <div class="mb-6 border-t pt-4 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-md font-medium">Kode Unik Transaksi</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            Tambahkan 3 digit angka unik pada total pembayaran.
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" x-model="uniqueCodeActive">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-md font-medium">Rekening Bank</h4>
                                    <button type="button" @click="addBank()" class="text-sm text-indigo-600 hover:text-indigo-900">+ Tambah Rekening</button>
                                </div>

                                <div x-show="false">
                                    {{-- Server-side loop removed in favor of Alpine.js --}}
                                </div>

                                <template x-for="(bank, index) in banks" :key="index">
                                    <div class="p-4 border rounded-md bg-gray-50 dark:bg-gray-700 mb-4">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                            <!-- Logo Section -->
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-16 h-16 bg-white rounded border flex items-center justify-center mb-2 overflow-hidden">
                                                    <template x-if="bank.logo">
                                                        <img :src="bank.logo" class="w-full h-full object-contain">
                                                    </template>
                                                    <template x-if="!bank.logo">
                                                        <span class="text-xs text-gray-400">No Logo</span>
                                                    </template>
                                                </div>
                                                <label class="cursor-pointer text-xs text-indigo-600 hover:text-indigo-800">
                                                    Upload Logo
                                                    <input type="file" class="hidden" accept="image/*" @change="uploadLogo($event.target.files[0], index)">
                                                </label>
                                            </div>

                                            <div class="col-span-3 grid grid-cols-1 xl:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Bank</label>
                                                    <input type="text" :name="'bank_details['+index+'][bank]'" x-model="bank.bank" placeholder="Contoh: BCA, Mandiri, BNI" class="w-full rounded border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor Rekening</label>
                                                    <input type="text" :name="'bank_details['+index+'][number]'" x-model="bank.number" placeholder="Contoh: 1234567890" class="w-full rounded border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Atas Nama</label>
                                                    <div class="flex items-center gap-2">
                                                        <input type="text" :name="'bank_details['+index+'][name]'" x-model="bank.name" placeholder="Nama Pemilik Rekening" class="w-full rounded border-gray-300 text-sm">
                                                        <button type="button" @click="removeBank(index)" class="text-red-500 hover:text-red-700 p-2" title="Hapus Rekening">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="flex justify-end mt-6 pt-4 border-t dark:border-gray-700">
                                <x-primary-button type="button" @click="savePayment()" x-bind:disabled="isLoading" class="min-w-[150px] justify-center">
                                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isLoading ? 'Saving...' : 'Update Settings'"></span>
                                </x-primary-button>
                            </div>
                        </div>





                    </div>
                </div>
            </form>
        </div>
    </div>


</x-app-layout>
