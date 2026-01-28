<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Integration System') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <!-- Success Notification -->
            @if(session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200 shadow-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Changes Saved Successfully</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" @click="show = false" class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="space-y-6" x-data="{ activeProvider: '{{ $activeProvider }}' }">
                <!-- Settings Form -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Shipping Configuration</h3>
                        
                        <form action="{{ route('admin.integrations.update') }}" method="POST">
                            @csrf
                            
                            <!-- Provider Selector -->
                            <div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-6">
                                <label for="shipping_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Active Provider</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <select id="shipping_provider" name="shipping_provider" x-model="activeProvider" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="rajaongkir">RajaOngkir</option>
                                        <option value="api_id">API Ongkir (API ID)</option>
                                    </select>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Select which provider to use for calculating shipping costs.</p>
                            </div>

                            <!-- Global Shipping Configuration (Insurance) -->
                            <div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-6">
                                <h4 class="text-md font-semibold mb-4 text-gray-900 dark:text-gray-100">Global Shipping Configuration (Insurance)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Active Toggle -->
                                    <div class="flex items-center md:col-span-2">
                                        <input type="checkbox" name="shipping_insurance_active" id="shipping_insurance_active" value="1" 
                                            {{ $insuranceSettings['active'] ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="shipping_insurance_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                            Enable Shipping Insurance Service
                                        </label>
                                    </div>

                                    <!-- Percentage -->
                                    <div>
                                        <label for="shipping_insurance_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Insurance Percentage (%)</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" min="0" max="100" name="shipping_insurance_percentage" id="shipping_insurance_percentage" 
                                                value="{{ $insuranceSettings['percentage'] }}" 
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md" placeholder="0.00">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Percentage of total item price.</p>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label for="shipping_insurance_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Description (Frontend)</label>
                                        <input type="text" name="shipping_insurance_description" id="shipping_insurance_description" 
                                            value="{{ $insuranceSettings['description'] }}" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Text displayed to customer during checkout.</p>
                                    </div>

                                    <!-- Packing Fee -->
                                    <div class="md:col-span-2">
                                        <label for="shipping_packing_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Biaya Packing (Packing Fee)</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input type="number" step="1" min="0" name="shipping_packing_fee" id="shipping_packing_fee" 
                                                value="{{ $insuranceSettings['packing_fee'] ?? 0 }}" 
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md" placeholder="0">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Fixed cost added to shipping total for packing.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- RajaOngkir Settings -->
                            <div x-show="activeProvider === 'rajaongkir'" class="space-y-6">
                                <div class="flex items-center space-x-2 mb-4">
                                    <!-- RajaOngkir Icon Placeholder -->
                                    <span class="p-2 bg-indigo-100 rounded-lg">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </span>
                                    <h4 class="text-md font-semibold">RajaOngkir Settings</h4>
                                </div>

                                <div class="grid grid-cols-1 gap-6">
                                    <!-- API Key -->
                                    <div>
                                        <label for="rajaongkir_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="password" name="rajaongkir_api_key" id="rajaongkir_api_key" value="{{ $rajaOngkirSettings['api_key'] }}" 
                                                class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                    </div>

                                    <!-- Account Type -->
                                    <div>
                                        <label for="rajaongkir_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Type</label>
                                        <select id="rajaongkir_type" name="rajaongkir_type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="starter" {{ $rajaOngkirSettings['type'] === 'starter' ? 'selected' : '' }}>Starter</option>
                                            <option value="basic" {{ $rajaOngkirSettings['type'] === 'basic' ? 'selected' : '' }}>Basic</option>
                                            <option value="pro" {{ $rajaOngkirSettings['type'] === 'pro' ? 'selected' : '' }}>Pro</option>
                                        </select>
                                    </div>

                                    <!-- Base URL -->
                                    <div>
                                        <label for="rajaongkir_base_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Base URL</label>
                                        <input type="text" name="rajaongkir_base_url" id="rajaongkir_base_url" value="{{ $rajaOngkirSettings['base_url'] }}" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                    </div>

                                    <!-- Store Origin (Dropdowns) - Reusing existing logic -->
                                    <div x-data="locationSelector('{{ $rajaOngkirSettings['origin_id'] }}', '{{ $rajaOngkirSettings['origin_label'] }}')" class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Store Origin (Location)</label>
                                        
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md border border-gray-200 dark:border-gray-600 mb-2">
                                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                                Current Location: 
                                                <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="originLabel || 'Not Set'"></span>
                                            </p>
                                            <input type="hidden" name="rajaongkir_origin_id" :value="originId">
                                            <input type="hidden" name="rajaongkir_origin_label" :value="originLabel">
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <!-- Province -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Province</label>
                                                <select x-model="selectedProvince" @change="onProvinceChange()" class="block w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select Province</option>
                                                    <template x-for="p in provinces" :key="p.province_id">
                                                        <option :value="p.province_id" x-text="p.province"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <!-- City -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">City</label>
                                                <select x-model="selectedCity" @change="onCityChange()" :disabled="!selectedProvince" class="block w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select City</option>
                                                    <template x-for="c in cities" :key="c.city_id">
                                                        <option :value="c.city_id" x-text="c.type + ' ' + c.city_name"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <!-- Subdistrict -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Subdistrict</label>
                                                <select x-model="selectedSubdistrict" @change="onSubdistrictChange()" :disabled="!selectedCity" class="block w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select Subdistrict</option>
                                                    <template x-for="s in subdistricts" :key="s.subdistrict_id">
                                                        <option :value="s.subdistrict_id" x-text="s.subdistrict_name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Couriers -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Enabled Couriers</label>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            @php
                                                $couriers = ['jne', 'pos', 'tiki', 'sicepat', 'jnt', 'anteraja', 'lion', 'ninja'];
                                                $selectedCouriers = is_string($rajaOngkirSettings['couriers']) 
                                                    ? explode(',', $rajaOngkirSettings['couriers']) 
                                                    : ($rajaOngkirSettings['couriers'] ?? []);
                                            @endphp
                                            @foreach($couriers as $courier)
                                                <div class="flex items-center">
                                                    <input id="courier_{{ $courier }}" name="rajaongkir_couriers[]" type="checkbox" value="{{ $courier }}" 
                                                        {{ in_array($courier, $selectedCouriers) ? 'checked' : '' }}
                                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                    <label for="courier_{{ $courier }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-300 uppercase">
                                                        {{ $courier }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- API ID Settings -->
                            <div x-show="activeProvider === 'api_id'" class="space-y-6" style="display: none;">
                                <div class="flex items-center space-x-2 mb-4">
                                    <!-- API ID Icon Placeholder -->
                                    <span class="p-2 bg-green-100 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    </span>
                                    <h4 class="text-md font-semibold">API Ongkir (API ID) Settings</h4>
                                </div>

                                <div class="grid grid-cols-1 gap-6">
                                    <!-- API Key -->
                                    <div>
                                        <label for="api_id_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key (x-api-co-id)</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="password" name="api_id_key" id="api_id_key" value="{{ $apiIdSettings['api_key'] }}" 
                                                class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Your API ID Key.</p>
                                    </div>

                                    <!-- Base URL -->
                                    <div>
                                        <label for="api_id_base_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Base URL</label>
                                        <input type="text" name="api_id_base_url" id="api_id_base_url" value="{{ $apiIdSettings['base_url'] ?? 'https://api.api.co.id' }}" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                    </div>
                                    
                                    <!-- API ID Store Origin -->
                                    <div x-data="apiIdLocationSelector('{{ $apiIdSettings['origin_id'] ?? '' }}', '{{ $apiIdSettings['origin_label'] ?? '' }}')" class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Store Origin (Location)</label>
                                        
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md border border-gray-200 dark:border-gray-600 mb-2">
                                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                                Current Location: 
                                                <span class="font-bold text-green-600 dark:text-green-400" x-text="originLabel || 'Not Set'"></span>
                                            </p>
                                            <input type="hidden" name="api_id_origin_id" :value="originId">
                                            <input type="hidden" name="api_id_origin_label" :value="originLabel">
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Province -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Province</label>
                                                <select x-model="selectedProvince" @change="onProvinceChange()" class="block w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select Province</option>
                                                    <template x-for="p in provinces" :key="p.id">
                                                        <option :value="p.id" x-text="p.name"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <!-- City -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">City</label>
                                                <select x-model="selectedCity" @change="onCityChange()" :disabled="!selectedProvince" class="block w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select City</option>
                                                    <template x-for="c in cities" :key="c.id">
                                                        <option :value="c.id" x-text="c.name"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <!-- Subdistrict -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Subdistrict</label>
                                                <select x-model="selectedSubdistrict" @change="onSubdistrictChange()" :disabled="!selectedCity" class="block w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select Subdistrict</option>
                                                    <template x-for="s in subdistricts" :key="s.id">
                                                        <option :value="s.id" x-text="s.name"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <!-- Village -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Village</label>
                                                <select x-model="selectedVillage" @change="onVillageChange()" :disabled="!selectedSubdistrict" class="block w-full text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select Village</option>
                                                    <template x-for="v in villages" :key="v.id">
                                                        <option :value="v.id" x-text="v.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-blue-50 p-4 rounded-md">
                                        <p class="text-sm text-blue-700">
                                            <strong>Note:</strong> You must configure the Store Origin separately for API ID. This system uses the same location database as RajaOngkir (Provinces/Cities/Subdistricts).
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-between items-center">
                                <button type="button" @click="testConnection(activeProvider)" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Test Connection
                                </button>
                                
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Global Shipping Configuration -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Global Shipping Configuration</h3>
                        <form action="{{ route('admin.integrations.update') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Active Couriers (System Wide)</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @foreach($availableCouriers as $courier)
                                    <div class="flex items-center">
                                        <input type="checkbox" name="shipping_active_couriers[]" value="{{ $courier }}" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            {{ in_array($courier, $activeCouriers) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 uppercase">{{ $courier }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Select couriers that are supported by your shipping provider API.</p>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save Global Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Silverchannel Configuration -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="{ showModal: false, selectedStore: null, storeCouriers: [] }">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Silverchannel Configuration</h3>
                        <p class="text-sm text-gray-500 mb-4">Configure specific shipping couriers for each Silverchannel store.</p>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Store</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Owner</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Active Couriers</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($stores as $store)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $store->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $store->user->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            @if($store->shipping_couriers)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($store->shipping_couriers as $sc)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 uppercase">{{ $sc }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">Global Default</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" @click="showModal = true; selectedStore = {{ $store->toJson() }}; storeCouriers = {{ json_encode($store->shipping_couriers ?? []) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">Edit</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div x-show="showModal" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="showModal" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showModal = false">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div x-show="showModal" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form action="{{ route('admin.integrations.shipping.store-update') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="store_id" :value="selectedStore?.id">
                                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                                    Edit Couriers for <span x-text="selectedStore?.name"></span>
                                                </h3>
                                                <div class="mt-4">
                                                    <p class="text-sm text-gray-500 mb-2">Select active couriers for this store. Only globally active couriers are shown.</p>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        @foreach($activeCouriers as $courier)
                                                        <div class="flex items-center">
                                                            <input type="checkbox" name="couriers[]" value="{{ $courier }}" 
                                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                                :checked="storeCouriers && storeCouriers.includes('{{ $courier }}')">
                                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 uppercase">{{ $courier }}</span>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Save Changes
                                        </button>
                                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check Shipping Cost -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 relative z-20" x-data="shippingTest">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Check Shipping Cost</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Inputs -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Origin</label>
                                    <div class="mt-1 p-2 bg-gray-100 dark:bg-gray-700 rounded-md text-sm">
                                        <template x-if="activeProvider === 'rajaongkir'">
                                            <span>{{ $rajaOngkirSettings['origin_label'] ?: 'Not Configured' }}</span>
                                        </template>
                                        <template x-if="activeProvider === 'api_id'">
                                            <span>{{ $apiIdSettings['origin_label'] ?? 'Not Configured' }}</span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Origin is taken from Store Origin settings.</p>
                                </div>

                                <div class="relative" @click.outside="destinationResults = []">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Destination</label>
                                    <div class="relative">
                                        <input type="text" x-model="destinationQuery" 
                                            @input.debounce.300ms="searchDestination()" 
                                            @keydown.arrow-down.prevent="focusNext()"
                                            @keydown.arrow-up.prevent="focusPrev()"
                                            @keydown.enter.prevent="selectCurrent()"
                                            @keydown.escape="destinationResults = []"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-10"
                                            placeholder="Search city, subdistrict, or village..."
                                            role="combobox"
                                            aria-autocomplete="list"
                                            :aria-expanded="destinationResults.length > 0"
                                            aria-haspopup="listbox">
                                        
                                        <!-- Loading Spinner -->
                                        <div x-show="searchLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Error Message -->
                                    <p x-show="searchError" x-text="searchError" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                    
                                    <!-- Dropdown Results -->
                                    <div x-show="destinationResults.length > 0" 
                                        class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg min-h-[200px] max-h-[400px] rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" 
                                        role="listbox">
                                        <template x-for="(item, index) in destinationResults" :key="item.subdistrict_id || index">
                                            <div @click="selectDestination(item)" 
                                                :class="{ 'bg-indigo-600 text-white': focusedIndex === index, 'text-gray-900 dark:text-gray-200': focusedIndex !== index }"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white group">
                                                <span class="block truncate" x-text="formatLocation(item)"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="destinationId" class="mt-1 text-sm text-green-600">
                                        Selected: <span x-text="destinationName"></span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Weight (grams)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" x-model="weight" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. 1000">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">gram</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">1000 gram = 1 kg</p>
                            </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Courier</label>
                                        <select x-model="courier" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="all">All Couriers</option>
                                            <option value="jne">JNE</option>
                                            <option value="pos">POS Indonesia</option>
                                            <option value="tiki">TIKI</option>
                                            <option value="sicepat">SiCepat</option>
                                            <option value="jnt">J&T Express</option>
                                            <option value="lion">Lion Parcel</option>
                                            <option value="ninja">Ninja Xpress</option>
                                            <option value="anteraja">AnterAja</option>
                                            <option value="ide">ID Express</option>
                                            <option value="sap">SAP Express</option>
                                            <option value="rpx">RPX</option>
                                            <option value="wahana">Wahana</option>
                                            <option value="pahala">Pahala</option>
                                        </select>
                                    </div>
                                </div>

                                <button @click="checkCost" :disabled="loading || !destinationId" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                    <span x-show="!loading">Check Cost</span>
                                    <span x-show="loading">Checking...</span>
                                </button>
                            </div>

                            <!-- Results -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4 min-h-[400px] flex flex-col">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Results</h4>
                                    <div class="flex space-x-2">
                                        <select x-model="sortBy" class="text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="price_asc">Cheapest</option>
                                            <option value="price_desc">Most Expensive</option>
                                            <option value="fastest">Fastest</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Filters -->
                                <div class="mb-4 flex flex-wrap gap-2" x-show="results">
                                    <input type="number" x-model="filters.maxPrice" placeholder="Max Price" class="text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md w-24 focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" x-model="filters.service" placeholder="Service (e.g. REG)" class="text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md w-32 focus:border-indigo-500 focus:ring-indigo-500" @focus="filters.service === 'all' ? filters.service = '' : null">
                                </div>

                                <template x-if="!results && !loading">
                                    <div class="flex-1 flex items-center justify-center text-gray-500 text-sm">
                                        Enter details and click Check Cost to see results.
                                    </div>
                                </template>

                                <template x-if="loading">
                                    <div class="flex-1 flex items-center justify-center">
                                        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </template>

                                <template x-if="results && !loading">
                                    <div class="space-y-3 overflow-y-auto max-h-[500px] pr-1">
                                        <template x-for="item in sortedResults" :key="item.courier_code + item.service + item.price">
                                            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm border border-gray-100 dark:border-gray-600 flex items-center justify-between hover:shadow-md transition-shadow">
                                                <div class="flex items-center space-x-3">
                                                    <!-- Logo Badge -->
                                                    <div :class="`w-10 h-10 rounded-full flex items-center justify-center text-white text-[10px] font-bold shadow-sm ${courierLogos[item.courier_code] || 'bg-gray-500'}`">
                                                        <span x-text="item.courier_code.toUpperCase()"></span>
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-gray-900 dark:text-gray-100 text-sm" x-text="item.courier_name"></div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            <span x-text="item.service"></span>
                                                            <span x-show="item.description && item.description !== item.service" x-text="` - ${item.description}`"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="font-bold text-indigo-600 dark:text-indigo-400 text-sm" x-text="formatRupiah(item.price)"></div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-end">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        <span x-text="item.etd.replace('HARI', '').replace('Days', '').trim() + ' Days'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <div x-show="sortedResults.length === 0" class="text-center text-sm text-gray-500 py-4">
                                            No results match your filters.
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Recent Checks History -->
                            <div class="mt-4" x-show="history.length > 0">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Recent Checks</h4>
                                    <button @click="history = []; localStorage.removeItem('shipping_cost_history')" class="text-xs text-red-500 hover:text-red-700">Clear</button>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(h, i) in history" :key="i">
                                        <div class="bg-white dark:bg-gray-700 p-2 rounded border border-gray-200 dark:border-gray-600 text-xs flex justify-between items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors" @click="restoreHistory(h)">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-gray-100" x-text="h.destination"></div>
                                                <div class="text-gray-500 dark:text-gray-400" x-text="`${h.weight}g • ${h.courier === 'all' ? 'All Couriers' : h.courier.toUpperCase()}`"></div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-gray-400" x-text="h.time"></div>
                                                <div class="font-semibold text-indigo-600 dark:text-indigo-400" x-text="h.cost ? formatRupiah(h.cost) : '-'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Integration Logs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Integration Logs</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Provider</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Endpoint</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($logs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $log->created_at->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 uppercase">
                                            {{ $log->integration }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $log->method }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                            {{ Str::limit($log->endpoint, 30) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($log->status_code >= 200 && $log->status_code < 300)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $log->status_code }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ $log->status_code }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $log->duration_ms }}ms
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No logs available.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('apiIdLocationSelector', (initialId = '', initialLabel = '') => ({
                provinces: [],
                cities: [],
                subdistricts: [],
                villages: [],
                
                selectedProvince: null,
                selectedCity: null,
                selectedSubdistrict: null,
                selectedVillage: null,
                
                originId: initialId,
                originLabel: initialLabel,
                
                init() {
                    this.fetchProvinces();
                },
                
                async fetchProvinces() {
                    try {
                        let res = await fetch(`{{ route('admin.integrations.shipping.provinces') }}?provider=api_id`);
                        this.provinces = await res.json();
                    } catch (e) { console.error(e); }
                },
                
                async onProvinceChange() {
                    this.selectedCity = null;
                    this.selectedSubdistrict = null;
                    this.selectedVillage = null;
                    this.cities = [];
                    this.subdistricts = [];
                    this.villages = [];
                    
                    if (!this.selectedProvince) return;
                    
                    try {
                        let res = await fetch(`{{ url('admin/integrations/shipping/cities') }}/${this.selectedProvince}?provider=api_id`);
                        this.cities = await res.json();
                    } catch (e) { console.error(e); }
                },
                
                async onCityChange() {
                    this.selectedSubdistrict = null;
                    this.selectedVillage = null;
                    this.subdistricts = [];
                    this.villages = [];
                    
                    if (!this.selectedCity) return;
                    
                    try {
                        let res = await fetch(`{{ url('admin/integrations/shipping/subdistricts') }}/${this.selectedCity}?provider=api_id`);
                        this.subdistricts = await res.json();
                    } catch (e) { console.error(e); }
                },
                
                async onSubdistrictChange() {
                    this.selectedVillage = null;
                    this.villages = [];
                    
                    if (!this.selectedSubdistrict) return;
                    
                    try {
                        let res = await fetch(`{{ url('admin/integrations/shipping/villages') }}/${this.selectedSubdistrict}?provider=api_id`);
                        this.villages = await res.json();
                    } catch (e) { console.error(e); }
                },
                
                onVillageChange() {
                    if (this.selectedVillage) {
                        let vil = this.villages.find(v => v.id == this.selectedVillage);
                        let sub = this.subdistricts.find(s => s.id == this.selectedSubdistrict);
                        let city = this.cities.find(c => c.id == this.selectedCity);
                        let prov = this.provinces.find(p => p.id == this.selectedProvince);
                        
                        if (vil && sub && city && prov) {
                            this.originId = vil.id; // API ID needs village code (id)
                            this.originLabel = `${vil.name}, ${sub.name}, ${city.name}, ${prov.name}`;
                        }
                    }
                }
            }));

            Alpine.data('locationSelector', (initialId = '', initialLabel = '') => ({
                provinces: [],
                cities: [],
                subdistricts: [],
                
                selectedProvince: null,
                selectedCity: null,
                selectedSubdistrict: null,
                
                originId: initialId,
                originLabel: initialLabel,
                
                init() {
                    this.fetchProvinces();
                },
                
                async fetchProvinces() {
                    try {
                        let res = await fetch("{{ route('admin.integrations.shipping.provinces') }}");
                        this.provinces = await res.json();
                    } catch (e) {
                        console.error(e);
                    }
                },
                
                async onProvinceChange() {
                    this.selectedCity = null;
                    this.selectedSubdistrict = null;
                    this.cities = [];
                    this.subdistricts = [];
                    
                    if (!this.selectedProvince) return;
                    
                    try {
                        let res = await fetch(`{{ url('admin/integrations/shipping/cities') }}/${this.selectedProvince}`);
                        this.cities = await res.json();
                    } catch (e) { console.error(e); }
                },
                
                async onCityChange() {
                    this.selectedSubdistrict = null;
                    this.subdistricts = [];
                    
                    if (!this.selectedCity) return;
                    
                    try {
                        let res = await fetch(`{{ url('admin/integrations/shipping/subdistricts') }}/${this.selectedCity}`);
                        this.subdistricts = await res.json();
                    } catch (e) { console.error(e); }
                },
                
                onSubdistrictChange() {
                    if (this.selectedSubdistrict) {
                        // Find the object
                        let sub = this.subdistricts.find(s => s.subdistrict_id == this.selectedSubdistrict);
                        let city = this.cities.find(c => c.city_id == this.selectedCity);
                        let prov = this.provinces.find(p => p.province_id == this.selectedProvince);
                        
                        if (sub && city && prov) {
                            this.originId = sub.subdistrict_id;
                            this.originLabel = `${sub.subdistrict_name}, ${city.type} ${city.city_name}, ${prov.province}`;
                        }
                    }
                }
            }));

            Alpine.data('shippingTest', () => ({
                destinationQuery: '',
                destinationId: '',
                destinationName: '',
                weight: 1000,
                courier: 'all',
                results: null,
                loading: false,
                destinationResults: [],
                searchLoading: false,
                searchError: null,
                focusedIndex: -1,
                history: [],
                isNewResult: false,
                sortBy: 'price_asc', // price_asc, price_desc, fastest
                filters: {
                    maxPrice: null,
                    maxDays: null,
                    service: 'all' // all, reg, express, etc. (simplified to text match if needed)
                },
                
                init() {
                    // Load history from localStorage
                    const saved = localStorage.getItem('shipping_cost_history');
                    if (saved) {
                        try {
                            this.history = JSON.parse(saved);
                        } catch (e) {
                            console.error('Failed to parse history', e);
                        }
                    }
                },

                get sortedResults() {
                    if (!this.results) return [];
                    
                    let flatResults = [];
                    // Flatten the structure: Courier -> Costs -> Cost Item
                    this.results.forEach(courier => {
                        courier.costs.forEach(service => {
                            service.cost.forEach(costItem => {
                                flatResults.push({
                                    courier_code: courier.code,
                                    courier_name: courier.name,
                                    service: service.service,
                                    description: service.description,
                                    price: costItem.value,
                                    etd: costItem.etd,
                                    note: costItem.note
                                });
                            });
                        });
                    });

                    // Filter
                    let filtered = flatResults.filter(item => {
                        if (this.filters.maxPrice && item.price > this.filters.maxPrice) return false;
                        if (this.filters.service && this.filters.service !== 'all') {
                            if (!item.service.toLowerCase().includes(this.filters.service.toLowerCase())) return false;
                        }
                        return true;
                    });

                    // Sort
                    filtered.sort((a, b) => {
                        if (this.sortBy === 'price_asc') return a.price - b.price;
                        if (this.sortBy === 'price_desc') return b.price - a.price;
                        if (this.sortBy === 'fastest') {
                            // Simple heuristic: parse first number in ETD string
                            let getDays = (str) => {
                                let m = str.match(/\d+/);
                                return m ? parseInt(m[0]) : 999;
                            };
                            return getDays(a.etd) - getDays(b.etd);
                        }
                        return 0;
                    });

                    return filtered;
                },

                get courierLogos() {
                    // Simple color mapping or placeholder
                    return {
                        'jne': 'bg-red-600',
                        'pos': 'bg-orange-500',
                        'tiki': 'bg-blue-600',
                        'sicepat': 'bg-red-500',
                        'jnt': 'bg-red-700',
                        'lion': 'bg-black',
                        'ninja': 'bg-red-800',
                        'anteraja': 'bg-purple-600',
                        'ide': 'bg-orange-600',
                        'sap': 'bg-blue-500',
                        'rpx': 'bg-orange-400',
                        'wahana': 'bg-yellow-500',
                        'pahala': 'bg-green-600'
                    };
                },
                
                async searchDestination() {
                    if (this.destinationQuery.length < 3) {
                        this.destinationResults = [];
                        this.searchError = null;
                        return;
                    }
                    
                    this.searchLoading = true;
                    this.searchError = null;
                    this.destinationResults = [];
                    this.focusedIndex = -1;

                    try {
                        let provider = document.getElementById('shipping_provider').value;
                        let res = await fetch(`{{ route('admin.integrations.shipping.search') }}?q=${this.destinationQuery}&provider=${provider}`);
                        
                        if (!res.ok) throw new Error('Failed to fetch results');
                        
                        let data = await res.json();
                        this.destinationResults = data;
                        
                        if (this.destinationResults.length === 0) {
                             this.searchError = 'No results found.';
                        }
                    } catch (e) {
                        console.error(e);
                        this.searchError = 'Error loading results. Please try again.';
                        this.destinationResults = [];
                    } finally {
                        this.searchLoading = false;
                    }
                },
                
                selectDestination(item) {
                    this.destinationId = item.subdistrict_id;
                    this.destinationName = this.formatLocation(item);
                    this.destinationQuery = '';
                    this.destinationResults = [];
                    this.searchError = null;
                    this.focusedIndex = -1;
                    
                    // Auto check cost when destination selected
                    this.autoCheckCost();
                },
                
                formatLocation(item) {
                    // Filter out null/undefined/empty values
                    return [item.subdistrict_name, item.city_name, item.province_name]
                        .filter(Boolean)
                        .join(', ');
                },

                focusNext() {
                    if (this.destinationResults.length === 0) return;
                    this.focusedIndex = (this.focusedIndex + 1) % this.destinationResults.length;
                },

                focusPrev() {
                    if (this.destinationResults.length === 0) return;
                    this.focusedIndex = (this.focusedIndex - 1 + this.destinationResults.length) % this.destinationResults.length;
                },

                selectCurrent() {
                    if (this.focusedIndex >= 0 && this.focusedIndex < this.destinationResults.length) {
                        this.selectDestination(this.destinationResults[this.focusedIndex]);
                    }
                },
                
                autoCheckCost() {
                    if (this.destinationId && this.weight > 0) {
                        this.checkCost(true);
                    }
                },
                
                async checkCost(isAuto = false) {
                    if (this.loading) return; // Prevent double submit
                    
                    this.loading = true;
                    this.isNewResult = false;
                    // Don't clear results immediately for better UX (no flicker) if it's auto
                    if (!isAuto) this.results = null;
                    
                    try {
                        let provider = document.getElementById('shipping_provider').value;
                        let res = await fetch("{{ route('admin.integrations.shipping.test-cost') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                destination_id: this.destinationId,
                                weight: this.weight,
                                courier: this.courier,
                                provider: provider
                            })
                        });
                        let data = await res.json();
                        if (data.success) {
                            this.results = data.data;
                            this.isNewResult = true;
                            
                            // Add to history
                            let firstCost = 0;
                            if (this.results.length > 0 && this.results[0].costs.length > 0) {
                                firstCost = this.results[0].costs[0].cost[0].value;
                            }
                            
                            this.history.unshift({
                                time: new Date().toLocaleTimeString(),
                                destination: this.destinationName,
                                weight: this.weight,
                                courier: this.courier,
                                cost: firstCost,
                                results: this.results
                            });
                            
                            // Limit history to 5 items
                            if (this.history.length > 5) this.history.pop();
                            
                            // Save to localStorage
                            localStorage.setItem('shipping_cost_history', JSON.stringify(this.history));
                            
                            // Reset "New Result" badge after 2 seconds
                            setTimeout(() => { this.isNewResult = false; }, 2000);
                            
                        } else {
                            if (!isAuto) alert('Error: ' + (data.message || 'Failed to fetch costs'));
                        }
                    } catch (e) {
                        console.error(e);
                        if (!isAuto) alert('System Error');
                    } finally {
                        this.loading = false;
                    }
                },
                
                restoreHistory(item) {
                    this.destinationName = item.destination;
                    this.weight = item.weight;
                    this.courier = item.courier;
                    this.results = item.results;
                    // Note: destinationId is not stored in history for display, 
                    // and we might not need it unless we want to re-fetch.
                    // Assuming restore is just for viewing past result.
                },
                
                formatRupiah(value) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                }
            }));
        });

        function testConnection(provider) {
            let url = provider === 'api_id' ? "{{ route('admin.integrations.test.api_id') }}" : "{{ route('admin.integrations.test.rajaongkir') }}";
            
            // Gather data from form
            let data = {};
            if (provider === 'rajaongkir') {
                data.api_key = document.getElementById('rajaongkir_api_key').value;
                data.base_url = document.getElementById('rajaongkir_base_url').value;
            } else {
                data.api_key = document.getElementById('api_id_key').value;
                data.base_url = document.getElementById('api_id_base_url').value;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Connection Successful: ' + (data.message || 'OK'));
                    } else {
                        alert('Connection Failed: ' + (data.message || 'Unknown Error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while testing connection.');
                });
        }
    </script>
    @endpush
</x-app-layout>
