<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Global Store Settings') }}
        </h2>
    </x-slot>

    <style>
        /* Reactor Switch CSS */
        .reactor-widget {
            display: inline-block;
            vertical-align: middle;
            transform: scale(0.4);
            transform-origin: left center;
            margin-right: -70px; /* Compensation for scale spacing */
        }

        /* Hide input */
        .reactor-widget .reactor-switch input {
          display: none;
        }

        /* Track / Shell */
        .reactor-widget .reactor-switch label {
          width: 120px;
          height: 55px;
          position: relative;
          display: block;
          cursor: pointer;
          border-radius: 999px;
          overflow: hidden;
          isolation: isolate;
          background: linear-gradient(180deg, #2d0a0a 0%, #1a0505 100%);
          border: 1px solid #ffffff10;
          box-shadow:
            inset 0 8px 20px #000a,
            inset 0 1px 0 #ffffff08,
            0 10px 28px #0007;
          transition: 600ms cubic-bezier(0.15, 0.95, 0.18, 1);
        }

        /* Outer Rim */
        .reactor-widget .reactor-switch label::before {
          content: "";
          position: absolute;
          inset: 2px;
          border-radius: 999px;
          box-shadow:
            inset 0 0 0 1px #ffffff08,
            inset 0 -4px 10px #000b,
            inset 0 4px 10px #ffffff05;
          z-index: 0;
        }

        /* Inner Core - Inactive (Red) */
        .reactor-widget .reactor-switch__core {
          position: absolute;
          inset: 6px;
          border-radius: 999px;
          /* Dark Red Gradient */
          background: radial-gradient(110px 70px at 18% 50%, #ffffff0c, transparent 60%),
            radial-gradient(120px 80px at 82% 50%, #00000080, transparent 65%),
            linear-gradient(90deg, #451a1a, #2b0b0b);
          box-shadow:
            inset 0 0 0 1px #ffffff06,
            inset 0 0 18px #000b;
          transition: 600ms cubic-bezier(0.15, 0.95, 0.18, 1);
          z-index: 1;
        }

        /* Energy Beam */
        .reactor-widget .reactor-switch__beam {
          position: absolute;
          inset: -60% -30%;
          background: conic-gradient(
            from 110deg,
            transparent 0 18%,
            #ff000018 26%, 
            transparent 40% 55%, 
            #ff4d4d18 66%, 
            transparent 78% 100% 
          );
          filter: blur(20px);
          opacity: 0.22;
          animation: reactorBeam 6s linear infinite;
          z-index: 0;
        }

        @keyframes reactorBeam {
          to {
            transform: translateX(18%) rotate(360deg);
          }
        }

        /* Thumb */
        .reactor-widget .reactor-switch__thumb {
          position: absolute;
          top: 5px;
          left: 5px;
          width: 45px;
          height: 45px;
          border-radius: 50%;
          z-index: 3;

          background: radial-gradient(
            circle at 25% 20%,
            #ffffff 0%,
            #ffeaf6 45%, 
            #eab4b4 100% 
          );

          box-shadow:
            0 8px 16px #0009,
            inset 0 3px 8px #ffffff,
            inset -6px -9px 14px #c6909066;

          transition: 600ms cubic-bezier(0.15, 0.95, 0.18, 1);
        }

        /* Inner Lens */
        .reactor-widget .reactor-switch__thumb::before {
          content: "";
          position: absolute;
          inset: 5px;
          border-radius: 50%;
          background: radial-gradient(circle at 30% 25%, #ffffff66, transparent 55%),
            radial-gradient(circle at 75% 80%, #00000066, transparent 60%);
          box-shadow: inset 0 0 0 1px #ffffff20;
        }

        /* Glow Tail */
        .reactor-widget .reactor-switch__thumb::after {
          content: "";
          position: absolute;
          top: 50%;
          left: 50%;
          width: 80px;
          height: 80px;
          transform: translate(-50%, -50%);
          background: radial-gradient(circle, #ff000022 0%, transparent 60%),
            radial-gradient(circle, #ff4d4d22 0%, transparent 65%);
          filter: blur(12px);
          opacity: 0;
          transition: 600ms ease;
        }

        /* OFF / ON Text */
        .reactor-widget .reactor-switch__state {
          position: absolute;
          top: 50%;
          transform: translateY(-50%);
          font-weight: 800;
          letter-spacing: 0.2em;
          font-size: 10px;
          user-select: none;
          z-index: 2;
          transition: 600ms ease;
        }

        .reactor-widget .reactor-switch__state--off {
          right: 16px;
          color: #e0c7c7b5;
        }
        .reactor-widget .reactor-switch__state--on {
          left: 16px;
          color: #001611;
          opacity: 0;
        }

        /* === ON STATE (Green) === */
        .reactor-widget .reactor-switch input:checked + label {
          border-color: #ffffff20;
          box-shadow:
            0 0 14px #00ff8833,
            0 0 30px #7bffb533,
            inset 0 8px 18px #ffffff18,
            0 10px 28px #0009;
        }

        .reactor-widget .reactor-switch input:checked + label .reactor-switch__core {
          /* Green Gradient */
          background: linear-gradient(90deg, #00ff88, #7bffb5, #22c55e);
          box-shadow:
            inset 0 0 0 1px #ffffff18,
            inset 0 0 20px #00ff8844;
        }

        .reactor-widget .reactor-switch input:checked + label .reactor-switch__beam {
           background: conic-gradient(
             from 110deg,
             transparent 0 18%,
             #00ff8818 26%, 
             transparent 40% 55%, 
             #7bffb518 66%, 
             transparent 78% 100% 
           );
        }

        /* Move Thumb */
        .reactor-widget .reactor-switch input:checked + label .reactor-switch__thumb {
          left: 70px;
          background: radial-gradient(
            circle at 30% 25%,
            #06331a 0%,
            #031c0d 55%,
            #001205 100%
          );
          box-shadow:
            0 8px 18px #000c,
            0 0 14px #00ff8855,
            inset 0 0 12px #00ff8866,
            inset -6px -9px 16px #00ff8855;
        }

        .reactor-widget .reactor-switch input:checked + label .reactor-switch__thumb::after {
          opacity: 1;
          background: radial-gradient(circle, #00ff8822 0%, transparent 60%),
            radial-gradient(circle, #7bffb522 0%, transparent 65%);
        }

        /* Switch Text Visibility */
        .reactor-widget .reactor-switch input:checked + label .reactor-switch__state--off {
          opacity: 0;
        }
        .reactor-widget .reactor-switch input:checked + label .reactor-switch__state--on {
          opacity: 1;
        }

        /* Focus */
        .reactor-widget .reactor-switch input:focus-visible + label {
          outline: 2px solid #ffffff44;
          outline-offset: 4px;
        }

        /* 3D Button Styles */
        .btn-3d {
            transition: all 0.1s;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: 
                0px 0px 0px 0px rgba(0, 0, 0, 0.5),
                0px 0px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px -1px 0px 0px rgba(0, 0, 0, 0.2);
        }

        .btn-3d:active {
            transform: translateY(2px);
            box-shadow: 
                0px 0px 0px 0px rgba(0, 0, 0, 0.5),
                0px 0px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.2),
                inset 0px -1px 0px 0px rgba(255, 255, 255, 0.5);
        }

        /* Blue Variant */
        .btn-3d-blue {
            background: linear-gradient(to bottom, #3b82f6, #2563eb);
            border: 1px solid #1d4ed8;
            box-shadow: 
                0px 4px 0px 0px #1e40af,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3),
                inset 0px -1px 0px 0px rgba(0, 0, 0, 0.2);
            --btn-pulse-color: rgba(59, 130, 246, 0.5);
        }

        .btn-3d-blue:hover {
            background: linear-gradient(to bottom, #60a5fa, #3b82f6);
            transform: translateY(-1px);
            animation: pulse512 1.5s infinite;
        }

        .btn-3d-blue:active {
            background: linear-gradient(to bottom, #2563eb, #3b82f6);
            box-shadow: 
                0px 0px 0px 0px #1e40af,
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.2),
                inset 0px -1px 0px 0px rgba(255, 255, 255, 0.3);
        }

        /* Pulse Animation */
        @keyframes pulse512 {
            0% { box-shadow: 0 0 0 0 var(--btn-pulse-color); }
            70% { box-shadow: 0 0 0 10px rgba(0, 0, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 0, 0, 0); }
        }

        /* Shimmer Effect */
        .shimmer {
            position: relative;
            overflow: hidden;
        }

        .shimmer::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.2) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }
    </style>

    @php
        $storeInitial = [
            'province_id' => $store->province_id,
            'city_id' => $store->city_id,
            'subdistrict_id' => $store->subdistrict_id,
            'is_open' => (bool) $store->is_open,
            'holiday_mode' => (bool) $store->holiday_mode,
            'holiday_note' => $store->holiday_note,
            'operating_hours' => $operatingHours,
            'store_menu_active' => (bool) ($settings['silverchannel_store_menu_active'] ?? false),
            'unique_code_active' => (bool) ($settings['store_payment_unique_code_active'] ?? false),
            'payment_timeout' => (int) ($settings['store_payment_timeout'] ?? 60),
            'holding_period' => (int) ($settings['commission_holding_period'] ?? 7),
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
                    contact: null,
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
                holdingPeriod: initialData.holding_period || 7,
                selectedProvince: initialData.province_id,
                selectedCity: initialData.city_id,
                selectedSubdistrict: initialData.subdistrict_id,
                cities: [],
                subdistricts: [],

                isOpen: initialData.is_open,
                holidayMode: initialData.holiday_mode,
                holidayNote: initialData.holiday_note || '',
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
                    // Listen for profile updates from other tabs
                    window.addEventListener('storage', (event) => {
                        if (event.key === 'profile_last_updated') {
                            window.location.reload();
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
                    fd.append('_method', 'PATCH'); // Method spoofing for FormData
                    if (logo) fd.append('logo', logo);
                    
                    const response = await this.performSave(this.urls.identity, 'POST', fd, 'Identitas toko berhasil disimpan');
                    if (response && response.logo_url) {
                         this.logoPreview = response.logo_url;
                         // Reset file input
                         const fileInput = document.getElementById('logo_input');
                         if (fileInput) fileInput.value = '';
                    }
                },

                async saveContact() {
                    // Contact is now managed via Profile
                },

                async saveHours() {
                    await this.performSave(this.urls.hours, 'PATCH', { 
                        operating_hours: this.operatingHours,
                        is_open: this.isOpen ? 1 : 0,
                        holiday_mode: this.holidayMode ? 1 : 0,
                        holiday_note: this.holidayNote
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
                        payment_timeout: this.paymentTimeout,
                        holding_period: this.holdingPeriod
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
                <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-lg">Aktifkan Menu 'Store Settings' untuk Silverchannel</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Jika diaktifkan, user dengan role Silverchannel dapat mengakses menu Pengaturan Toko di dashboard mereka.</p>
                            </div>
                            
                            <div class="reactor-widget scale-[0.6] origin-left">
                                <div class="reactor-switch">
                                    <input type="checkbox" id="toggle-store-menu" name="silverchannel_store_menu_active" value="1" 
                                        x-model="storeMenuActive" 
                                        @change="saveToggle()" hidden>
                                    <label for="toggle-store-menu">
                                        <div class="reactor-switch__core"></div>
                                        <div class="reactor-switch__beam"></div>
                                        <div class="reactor-switch__thumb"></div>
                                        <span class="reactor-switch__state reactor-switch__state--off">OFF</span>
                                        <span class="reactor-switch__state reactor-switch__state--on">ON</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Store Details Tabs -->
                <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                                <button type="button" @click="saveIdentity()" x-bind:disabled="isLoading" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest min-w-[150px] justify-center disabled:opacity-50">
                                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isLoading ? 'Saving...' : 'Update Identitas'"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="activeTab === 'contact'">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Kontak & Alamat</h3>

                            <!-- Info Alert -->
                            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 dark:bg-gray-700 dark:border-blue-500">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700 dark:text-blue-200">
                                            Informasi ini diambil secara otomatis dari Profil Anda.
                                            Perubahan hanya dapat dilakukan melalui menu <a href="{{ route('profile.edit') }}" class="font-bold underline hover:text-blue-600">Profile</a>.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <x-input-label for="address" :value="__('Alamat Lengkap')" />
                                    <textarea id="address" rows="2" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-gray-500" disabled>{{ $user->address }}</textarea>
                                </div>

                                <div>
                                    <x-input-label for="province_name" :value="__('Provinsi')" />
                                    <x-text-input type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->province_name }}" disabled />
                                </div>

                                <div>
                                    <x-input-label for="city_name" :value="__('Kota/Kabupaten')" />
                                    <x-text-input type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->city_name }}" disabled />
                                </div>

                                <div>
                                    <x-input-label for="subdistrict_name" :value="__('Kecamatan')" />
                                    <x-text-input type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->subdistrict_name }}" disabled />
                                </div>

                                <div>
                                    <x-input-label for="postal_code" :value="__('Kode Pos')" />
                                    <x-text-input type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->postal_code }}" disabled />
                                </div>

                                <div>
                                    <x-input-label for="phone" :value="__('No. Telepon / WhatsApp')" />
                                    <x-text-input type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->phone }}" disabled />
                                </div>

                                <div>
                                    <x-input-label for="email" :value="__('Email Resmi Toko')" />
                                    <x-text-input type="email" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->email }}" disabled />
                                </div>

                                <div class="col-span-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Media Sosial</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <x-input-label for="facebook" value="Facebook URL" />
                                            <x-text-input type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->social_facebook }}" disabled />
                                        </div>
                                        <div>
                                            <x-input-label for="instagram" value="Instagram URL" />
                                            <x-text-input type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->social_instagram }}" disabled />
                                        </div>
                                        <div>
                                            <x-input-label for="tiktok" value="TikTok URL" />
                                            <x-text-input type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 text-gray-500" value="{{ $user->social_tiktok }}" disabled />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <a href="{{ route('profile.edit') }}" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                    <span>Ubah di Profile</span>
                                </a>
                            </div>
                        </div>

                        <!-- 3. Jam Operasional -->
                        <div x-show="activeTab === 'hours'">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Jam Operasional</h3>
                            
                            <div class="mb-4 space-y-4">
                                <div>
                                    <div class="flex items-center">
                                        <div class="reactor-widget scale-[0.6] origin-left">
                                            <div class="reactor-switch">
                                                <input type="hidden" name="is_open" value="0">
                                                <input type="checkbox" id="toggle-is-open" name="is_open" value="1" x-model="isOpen" hidden>
                                                <label for="toggle-is-open">
                                                    <div class="reactor-switch__core"></div>
                                                    <div class="reactor-switch__beam"></div>
                                                    <div class="reactor-switch__thumb"></div>
                                                    <span class="reactor-switch__state reactor-switch__state--off">OFF</span>
                                                    <span class="reactor-switch__state reactor-switch__state--on">ON</span>
                                                </label>
                                            </div>
                                        </div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Status Toko (Manual): <span x-text="isOpen ? 'BUKA' : 'TUTUP'"></span></span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 ml-14">Override manual untuk menutup/buka toko sementara.</p>
                                </div>

                                <div>
                                    <div class="flex items-center">
                                        <div class="reactor-widget scale-[0.6] origin-left">
                                            <div class="reactor-switch">
                                                <input type="checkbox" id="toggle-holiday-mode" name="holiday_mode" value="1" x-model="holidayMode" hidden>
                                                <label for="toggle-holiday-mode">
                                                    <div class="reactor-switch__core"></div>
                                                    <div class="reactor-switch__beam"></div>
                                                    <div class="reactor-switch__thumb"></div>
                                                    <span class="reactor-switch__state reactor-switch__state--off">OFF</span>
                                                    <span class="reactor-switch__state reactor-switch__state--on">ON</span>
                                                </label>
                                            </div>
                                        </div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Mode Libur (Holiday): <span x-text="holidayMode ? 'AKTIF' : 'NON-AKTIF'"></span></span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 ml-14">Jika aktif, toko akan dianggap TUTUP seharian penuh (libur nasional/event).</p>
                                    
                                    <div class="ml-14 mt-3" x-show="holidayMode" x-transition>
                                        <x-input-label for="holiday_note" value="Keterangan Penutupan (Optional)" />
                                        <x-text-input id="holiday_note" type="text" class="mt-1 block w-full max-w-md" 
                                            x-model="holidayNote" 
                                            placeholder="Contoh: HARI RAYA IDUL FITRI" 
                                            maxlength="100" />
                                        <p class="text-xs text-gray-500 mt-1">Maksimal 100 karakter. Akan ditampilkan di halaman produk.</p>
                                    </div>
                                </div>
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
                                        <div class="flex items-center">
                                            <div class="reactor-widget scale-[0.5] origin-left">
                                                <div class="reactor-switch">
                                                    <!-- Hidden input for form submission -->
                                                    <input type="hidden" name="operating_hours[{{ $day }}][is_closed]" :value="operatingHours['{{ $day }}'].is_closed ? 1 : 0">
                                                    
                                                    <!-- UI Toggle: Checked = Open (Green), Unchecked = Closed (Red) -->
                                                    <input type="checkbox" id="toggle-closed-{{ $day }}" 
                                                           :checked="!operatingHours['{{ $day }}'].is_closed" 
                                                           @change="operatingHours['{{ $day }}'].is_closed = !$event.target.checked" 
                                                           hidden>
                                                           
                                                    <label for="toggle-closed-{{ $day }}">
                                                        <div class="reactor-switch__core"></div>
                                                        <div class="reactor-switch__beam"></div>
                                                        <div class="reactor-switch__thumb"></div>
                                                        <span class="reactor-switch__state reactor-switch__state--off">TUTUP</span>
                                                        <span class="reactor-switch__state reactor-switch__state--on">BUKA</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-end mt-6 pt-4 border-t dark:border-gray-700">
                                 <button type="button" @click="saveHours()" x-bind:disabled="isLoading" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest min-w-[150px] justify-center disabled:opacity-50">
                                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isLoading ? 'Saving...' : 'Update Settings'"></span>
                                </button>
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

                            <!-- Holding Period -->
                            <div class="mb-6 border-t pt-4 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-md font-medium">Periode Holding (hari)</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            Jangka waktu penahanan dana komisi sebelum dapat dicairkan oleh Silverchannel.
                                        </p>
                                    </div>
                                    <div class="w-32">
                                        <input type="number" min="0" max="90" class="rounded border-gray-300 text-sm w-full text-center" x-model="holdingPeriod">
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
                                    <div class="reactor-widget scale-[0.6] origin-left">
                                        <div class="reactor-switch">
                                            <input type="checkbox" id="toggle-unique-code" x-model="uniqueCodeActive" hidden>
                                            <label for="toggle-unique-code">
                                                <div class="reactor-switch__core"></div>
                                                <div class="reactor-switch__beam"></div>
                                                <div class="reactor-switch__thumb"></div>
                                                <span class="reactor-switch__state reactor-switch__state--off">OFF</span>
                                                <span class="reactor-switch__state reactor-switch__state--on">ON</span>
                                            </label>
                                        </div>
                                    </div>
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
                                <button type="button" @click="savePayment()" x-bind:disabled="isLoading" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest min-w-[150px] justify-center disabled:opacity-50">
                                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isLoading ? 'Saving...' : 'Update Settings'"></span>
                                </button>
                            </div>
                        </div>





                    </div>
                </div>
            </form>
        </div>
    </div>


</x-app-layout>
