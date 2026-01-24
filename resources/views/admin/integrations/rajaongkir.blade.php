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

            <div class="space-y-6">
                <!-- Settings Form -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">RajaOngkir Configuration</h3>
                        
                        <form action="{{ route('admin.integrations.update') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 gap-6">
                                <!-- API Key -->
                                <div>
                                    <label for="rajaongkir_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <input type="password" name="rajaongkir_api_key" id="rajaongkir_api_key" value="{{ $settings['api_key'] }}" 
                                            class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Encrypted in database.</p>
                                </div>

                                <!-- Account Type -->
                                <div>
                                    <label for="rajaongkir_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Type</label>
                                    <select id="rajaongkir_type" name="rajaongkir_type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="starter" {{ $settings['type'] === 'starter' ? 'selected' : '' }}>Starter</option>
                                        <option value="basic" {{ $settings['type'] === 'basic' ? 'selected' : '' }}>Basic</option>
                                        <option value="pro" {{ $settings['type'] === 'pro' ? 'selected' : '' }}>Pro</option>
                                    </select>
                                </div>

                                <!-- Base URL -->
                                <div>
                                    <label for="rajaongkir_base_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Base URL</label>
                                    <input type="text" name="rajaongkir_base_url" id="rajaongkir_base_url" value="{{ $settings['base_url'] }}" 
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: https://rajaongkir.komerce.id/api/v1 (V2)</p>
                                </div>

                                <!-- Store Origin (Dropdowns) -->
                                <div x-data="locationSelector" class="space-y-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Store Origin (Location)</label>
                                    
                                    <!-- Current Selection Display -->
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
                                            <div x-data="searchableSelect({
                                                    options: provinces,
                                                    value: selectedProvince,
                                                    valueKey: 'province_id',
                                                    labelKey: 'province',
                                                    placeholder: 'Select Province',
                                                    loading: loadingProvinces,
                                                    disabled: false,
                                                    onChange: (val) => { selectedProvince = val; onProvinceChange(); }
                                                })" 
                                                x-effect="options = provinces; loading = loadingProvinces; value = selectedProvince"
                                                class="relative">
                                                
                                                <!-- Trigger -->
                                                <button type="button" @click="toggle()" :disabled="disabled" class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <!-- Dropdown -->
                                                <div x-show="isOpen" @click.away="close()" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                        <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Search...">
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
                                                            <span x-show="!loading">No results found</span>
                                                            <span x-show="loading">Loading data...</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div x-show="loadingProvinces" class="text-xs text-indigo-500 mt-1 flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Loading provinces...
                                            </div>
                                            <p x-show="errorProvinces" x-text="errorProvinces" class="text-xs text-red-500 mt-1"></p>
                                        </div>

                                        <!-- City -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">City</label>
                                            <div x-data="searchableSelect({
                                                    options: cities,
                                                    value: selectedCity,
                                                    valueKey: 'city_id',
                                                    labelKey: 'city_name',
                                                    placeholder: 'Select City',
                                                    loading: loadingCities,
                                                    disabled: !selectedProvince,
                                                    onChange: (val) => { selectedCity = val; onCityChange(); }
                                                })" 
                                                x-effect="options = cities; loading = loadingCities; value = selectedCity; disabled = !selectedProvince"
                                                class="relative">
                                                
                                                <button type="button" @click="toggle()" :disabled="disabled" class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <div x-show="isOpen" @click.away="close()" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                        <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Search...">
                                                    </div>
                                                    <ul class="max-h-48 overflow-y-auto">
                                                        <template x-for="option in filteredOptions" :key="option[valueKey]">
                                                            <li @click="select(option)" class="cursor-pointer select-none relative py-2 px-4 text-gray-900 dark:text-white hover:bg-indigo-600 hover:text-white">
                                                                <span :class="{'font-semibold': value == option[valueKey], 'font-normal': value != option[valueKey]}" class="block truncate" x-text="`${option.type ? option.type + ' ' : ''}${option[labelKey]}`"></span>
                                                                <span x-show="value == option[valueKey]" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white">
                                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                        </template>
                                                        <li x-show="filteredOptions.length === 0" class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">No results found</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div x-show="loadingCities" class="text-xs text-indigo-500 mt-1 flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Loading cities...
                                            </div>
                                            <p x-show="errorCities" x-text="errorCities" class="text-xs text-red-500 mt-1"></p>
                                        </div>

                                        <!-- Subdistrict -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Subdistrict</label>
                                            <div x-data="searchableSelect({
                                                    options: subdistricts,
                                                    value: selectedSubdistrict,
                                                    valueKey: 'subdistrict_id',
                                                    labelKey: 'subdistrict_name',
                                                    placeholder: 'Select Subdistrict',
                                                    loading: loadingSubdistricts,
                                                    disabled: !selectedCity,
                                                    onChange: (val) => { selectedSubdistrict = val; updateSelection(); }
                                                })" 
                                                x-effect="options = subdistricts; loading = loadingSubdistricts; value = selectedSubdistrict; disabled = !selectedCity"
                                                class="relative">
                                                
                                                <button type="button" @click="toggle()" :disabled="disabled" class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </button>

                                                <div x-show="isOpen" @click.away="close()" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                        <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Search...">
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
                                                        <li x-show="filteredOptions.length === 0" class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-sm">No results found</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div x-show="loadingSubdistricts" class="text-xs text-indigo-500 mt-1 flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Loading subdistricts...
                                            </div>
                                            <p x-show="errorSubdistricts" x-text="errorSubdistricts" class="text-xs text-red-500 mt-1"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Couriers -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Active Couriers</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        @foreach(['jne', 'pos', 'tiki', 'rpx', 'pandu', 'wahana', 'sicepat', 'jnt', 'pahala', 'sap', 'jet', 'indah', 'dse', 'slis', 'first', 'ncs', 'star', 'ninja', 'lion', 'idl', 'rex', 'ide', 'sentral'] as $courier)
                                            <div class="flex items-center p-2 border border-gray-200 dark:border-gray-700 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                                                <input id="courier_{{ $courier }}" name="rajaongkir_couriers[]" value="{{ $courier }}" type="checkbox"
                                                    {{ in_array($courier, explode(',', $settings['couriers'] ?? '')) ? 'checked' : '' }}
                                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded cursor-pointer">
                                                <label for="courier_{{ $courier }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-300 uppercase cursor-pointer w-full">
                                                    {{ $courier }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <!-- Hidden fallback to ensure array is sent if empty (optional, usually handled by validation) -->
                                </div>

                                <!-- Status -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="rajaongkir_active" name="rajaongkir_active" type="checkbox" value="1" {{ $settings['active'] ? 'checked' : '' }} 
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="rajaongkir_active" class="font-medium text-gray-700 dark:text-gray-300">Active</label>
                                        <p class="text-gray-500 dark:text-gray-400">Enable or disable shipping cost calculation via RajaOngkir.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-between items-center">
                                <button type="button" onclick="testConnection()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Test Connection
                                </button>

                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Test Result -->
                <div id="test-result" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">Connection Test Result</h4>
                        <div id="test-result-content" class="text-sm text-gray-600 dark:text-gray-400 font-mono bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-auto max-h-60">
                            <!-- AJAX Content -->
                        </div>
                    </div>
                </div>

                <!-- Shipping Calculator Tester -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Test Shipping Cost</h3>
                        
                        <div x-data="testLocationSelector" class="space-y-6">
                            <!-- Row 1: Destination Address -->
                            <div class="relative z-20">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Destination</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Province -->
                                    <div class="relative z-30">
                                        <div x-data="searchableSelect({
                                                options: provinces,
                                                value: selectedProvince,
                                                valueKey: 'province_id',
                                                labelKey: 'province',
                                                placeholder: 'Select Province',
                                                loading: loadingProvinces,
                                                disabled: false,
                                                onChange: (val) => { selectedProvince = val; onProvinceChange(); }
                                            })" 
                                            x-effect="options = provinces; loading = loadingProvinces; value = selectedProvince"
                                            class="relative">
                                            
                                            <button type="button" @click="toggle()" :disabled="disabled" class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                </span>
                                            </button>

                                            <div x-show="isOpen" @click.away="close()" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                    <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Search...">
                                                </div>
                                                <ul class="max-h-48 overflow-y-auto">
                                                    <template x-for="option in filteredOptions" :key="option[valueKey]">
                                                        <li @click="select(option)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 dark:text-white hover:bg-indigo-600 hover:text-white">
                                                            <span :class="{'font-semibold': value == option[valueKey], 'font-normal': value != option[valueKey]}" class="block truncate" x-text="option[labelKey]"></span>
                                                            <span x-show="value == option[valueKey]" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span>
                                                        </li>
                                                    </template>
                                                    <li x-show="filteredOptions.length === 0" class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-sm">
                                                        <span x-show="!loading">No results found</span>
                                                        <span x-show="loading">Loading...</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- City -->
                                    <div class="relative z-20">
                                        <div x-data="searchableSelect({
                                                options: cities,
                                                value: selectedCity,
                                                valueKey: 'city_id',
                                                labelKey: 'city_name',
                                                placeholder: 'Select City',
                                                loading: loadingCities,
                                                disabled: !selectedProvince,
                                                onChange: (val) => { selectedCity = val; onCityChange(); }
                                            })" 
                                            x-effect="options = cities; loading = loadingCities; value = selectedCity; disabled = !selectedProvince"
                                            class="relative">
                                            
                                            <button type="button" @click="toggle()" :disabled="disabled" class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                </span>
                                            </button>

                                            <div x-show="isOpen" @click.away="close()" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                    <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Search...">
                                                </div>
                                                <ul class="max-h-48 overflow-y-auto">
                                                    <template x-for="option in filteredOptions" :key="option[valueKey]">
                                                        <li @click="select(option)" class="cursor-pointer select-none relative py-2 px-4 text-gray-900 dark:text-white hover:bg-indigo-600 hover:text-white">
                                                            <span :class="{'font-semibold': value == option[valueKey], 'font-normal': value != option[valueKey]}" class="block truncate" x-text="`${option.type ? option.type + ' ' : ''}${option[labelKey]}`"></span>
                                                            <span x-show="value == option[valueKey]" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span>
                                                        </li>
                                                    </template>
                                                    <li x-show="filteredOptions.length === 0" class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">No results found</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Subdistrict -->
                                    <div class="relative z-10">
                                        <div x-data="searchableSelect({
                                                options: subdistricts,
                                                value: selectedSubdistrict,
                                                valueKey: 'subdistrict_id',
                                                labelKey: 'subdistrict_name',
                                                placeholder: 'Select Subdistrict',
                                                loading: loadingSubdistricts,
                                                disabled: !selectedCity,
                                                onChange: (val) => { selectedSubdistrict = val; updateSelection(); }
                                            })" 
                                            x-effect="options = subdistricts; loading = loadingSubdistricts; value = selectedSubdistrict; disabled = !selectedCity"
                                            class="relative">
                                            
                                            <button type="button" @click="toggle()" :disabled="disabled" class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="block truncate" x-text="selectedLabel || placeholder"></span>
                                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                </span>
                                            </button>

                                            <div x-show="isOpen" @click.away="close()" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                <div class="sticky top-0 z-10 bg-white dark:bg-gray-700 p-2 border-b border-gray-200 dark:border-gray-600">
                                                    <input type="text" x-model="search" x-ref="searchInput" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs" placeholder="Search...">
                                                </div>
                                                <ul class="max-h-48 overflow-y-auto">
                                                    <template x-for="option in filteredOptions" :key="option[valueKey]">
                                                        <li @click="select(option)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 dark:text-white hover:bg-indigo-600 hover:text-white">
                                                            <span :class="{'font-semibold': value == option[valueKey], 'font-normal': value != option[valueKey]}" class="block truncate" x-text="option[labelKey]"></span>
                                                            <span x-show="value == option[valueKey]" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span>
                                                        </li>
                                                    </template>
                                                    <li x-show="filteredOptions.length === 0" class="py-2 pl-3 pr-9 text-gray-500 dark:text-gray-400 text-sm">No results found</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="test_destination_id" :value="selectedSubdistrict">
                            </div>

                            <!-- Row 2: Weight & Courier -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative z-0">
                                <!-- Weight -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Weight (grams)</label>
                                    <input type="number" id="test_weight" value="1000" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm sm:text-sm py-2 px-3">
                                </div>

                                <!-- Courier -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Courier</label>
                                    <select id="test_courier" class="block w-full border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 px-3">
                                        @foreach(['jne', 'pos', 'tiki', 'sicepat', 'jnt'] as $courier)
                                            <option value="{{ $courier }}">{{ strtoupper($courier) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" onclick="testCost()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Calculate Cost
                            </button>
                        </div>

                        <div id="cost-result" class="hidden mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-md font-mono text-sm overflow-auto">
                        </div>
                    </div>
                </div>

                <!-- Logs (Moved Below) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Recent Logs</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Endpoint</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($logs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ $log->status_code }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $log->method }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $log->endpoint }}">
                                            {{ \Illuminate\Support\Str::limit($log->endpoint, 50) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $log->duration_ms }}ms
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $log->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                            No logs found.
                                        </td>
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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('searchableSelect', ({ options, value, valueKey, labelKey, placeholder, loading, disabled, onChange }) => ({
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
                    this.options = options || [];
                    
                    // Watch for external changes to props if passed via x-effect
                    this.$watch('options', (val) => {
                         // Ensure options is always an array
                         if (!Array.isArray(val)) this.options = [];
                    });

                    this.$watch('isOpen', (val) => {
                        if (val) {
                            this.$nextTick(() => {
                                if (this.$refs.searchInput) {
                                    this.$refs.searchInput.focus();
                                }
                            });
                        } else {
                            this.search = '';
                        }
                    });
                },
                
                get filteredOptions() {
                    const opts = this.options || [];
                    if (this.search === '') {
                        return opts;
                    }
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
                }
            }));

            Alpine.data('locationSelector', () => ({
                provinces: [],
                cities: [],
                subdistricts: [],
                
                selectedProvince: '',
                selectedCity: '',
                selectedSubdistrict: '',
                
                errorProvinces: '',
                errorCities: '',
                errorSubdistricts: '',
                
                originId: '{{ $settings["origin_id"] }}',
                originLabel: '{{ $settings["origin_label"] }}',
                
                loadingProvinces: false,
                loadingCities: false,
                loadingSubdistricts: false,
                
                async init() {
                    await this.fetchProvinces();
                },
                
                async fetchProvinces() {
                    this.loadingProvinces = true;
                    this.errorProvinces = '';
                    try {
                        const res = await fetch("{{ route('admin.integrations.rajaongkir.provinces') }}");
                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            throw new Error(err.error || 'Failed to fetch provinces');
                        }
                        this.provinces = await res.json();
                        // If we have a saved province but it's not in the list (e.g. switch account type), clear selection
                        if (this.selectedProvince && !this.provinces.find(p => p.province_id == this.selectedProvince)) {
                            this.selectedProvince = '';
                            this.cities = [];
                            this.subdistricts = [];
                        } else if (this.selectedProvince) {
                            // If we have a selection, trigger cascade load
                            this.onProvinceChange();
                        }
                    } catch(e) { 
                        console.error(e);
                        this.errorProvinces = e.message || 'Failed to load provinces.';
                    }
                    this.loadingProvinces = false;
                },
                
                async onProvinceChange() {
                    // Only clear downstream if this is a user change (checked via value comparison if needed, but safe to reset if just fetching)
                    // But here we are called by watcher or manually.
                    // If called by fetchProvinces (restore state), we don't want to clear if we already have a city selected that matches.
                    // However, for simplicity, let's just fetch cities.
                    
                    const oldCity = this.selectedCity;
                    this.cities = [];
                    this.errorCities = '';
                    
                    if(!this.selectedProvince) {
                        this.selectedCity = '';
                        this.subdistricts = [];
                        return;
                    }
                    
                    this.loadingCities = true;
                    try {
                        const url = "{{ route('admin.integrations.rajaongkir.cities', ['province' => 'PROVINCE_ID']) }}".replace('PROVINCE_ID', this.selectedProvince);
                        const res = await fetch(url);
                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            throw new Error(err.error || 'Failed to fetch cities');
                        }
                        this.cities = await res.json();
                        
                        // Restore city if valid
                        if (oldCity && this.cities.find(c => c.city_id == oldCity)) {
                            this.selectedCity = oldCity;
                            this.onCityChange(); // Cascade
                        } else {
                            this.selectedCity = '';
                            this.subdistricts = [];
                        }
                    } catch(e) { 
                        console.error(e);
                        this.errorCities = e.message || 'Failed to load cities.';
                    }
                    this.loadingCities = false;
                },
                
                async onCityChange() {
                    const oldSub = this.selectedSubdistrict;
                    this.subdistricts = [];
                    this.errorSubdistricts = '';
                    
                    if(!this.selectedCity) {
                        this.selectedSubdistrict = '';
                        return;
                    }
                    
                    this.loadingSubdistricts = true;
                    try {
                        const url = "{{ route('admin.integrations.rajaongkir.subdistricts', ['city' => 'CITY_ID']) }}".replace('CITY_ID', this.selectedCity);
                        const res = await fetch(url);
                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            throw new Error(err.error || 'Failed to fetch subdistricts');
                        }
                        this.subdistricts = await res.json();
                        
                        // Restore subdistrict if valid
                        if (oldSub && this.subdistricts.find(s => s.subdistrict_id == oldSub)) {
                            this.selectedSubdistrict = oldSub;
                            this.updateSelection();
                        } else {
                            this.selectedSubdistrict = '';
                        }
                    } catch(e) { 
                        console.error(e);
                        this.errorSubdistricts = e.message || 'Failed to load subdistricts.';
                    }
                    this.loadingSubdistricts = false;
                },
                
                updateSelection() {
                    const sub = this.subdistricts.find(s => s.subdistrict_id == this.selectedSubdistrict);
                    const city = this.cities.find(c => c.city_id == this.selectedCity);
                    const prov = this.provinces.find(p => p.province_id == this.selectedProvince);
                    
                    if(sub && city && prov) {
                        this.originId = sub.subdistrict_id;
                        this.originLabel = `${sub.subdistrict_name}, ${city.city_name}, ${prov.province}`;
                        
                        // Also update hidden input
                        const input = document.getElementById('rajaongkir_origin_id');
                        if(input) input.value = sub.subdistrict_id;
                    }
                }
            }));

            Alpine.data('testLocationSelector', () => ({
                provinces: [],
                cities: [],
                subdistricts: [],
                
                selectedProvince: '',
                selectedCity: '',
                selectedSubdistrict: '',
                
                loadingProvinces: false,
                loadingCities: false,
                loadingSubdistricts: false,
                
                async init() {
                    await this.fetchProvinces();
                },
                
                async fetchProvinces() {
                    this.loadingProvinces = true;
                    try {
                        const res = await fetch("{{ route('admin.integrations.rajaongkir.provinces') }}");
                        if (!res.ok) throw new Error('Failed to fetch provinces');
                        this.provinces = await res.json();
                    } catch(e) { 
                        console.error(e);
                    }
                    this.loadingProvinces = false;
                },
                
                async onProvinceChange() {
                    this.cities = [];
                    this.selectedCity = '';
                    this.subdistricts = [];
                    this.selectedSubdistrict = '';
                    this.updateSelection();
                    
                    if(!this.selectedProvince) return;
                    
                    this.loadingCities = true;
                    try {
                        const url = "{{ route('admin.integrations.rajaongkir.cities', ['province' => 'PROVINCE_ID']) }}".replace('PROVINCE_ID', this.selectedProvince);
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('Failed to fetch cities');
                        this.cities = await res.json();
                    } catch(e) { 
                        console.error(e);
                    }
                    this.loadingCities = false;
                },
                
                async onCityChange() {
                    this.subdistricts = [];
                    this.selectedSubdistrict = '';
                    this.updateSelection();
                    
                    if(!this.selectedCity) return;
                    
                    this.loadingSubdistricts = true;
                    try {
                        const url = "{{ route('admin.integrations.rajaongkir.subdistricts', ['city' => 'CITY_ID']) }}".replace('CITY_ID', this.selectedCity);
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('Failed to fetch subdistricts');
                        this.subdistricts = await res.json();
                    } catch(e) { 
                        console.error(e);
                    }
                    this.loadingSubdistricts = false;
                },
                
                updateSelection() {
                    const input = document.getElementById('test_destination_id');
                    if(input) {
                        input.value = this.selectedSubdistrict;
                    }
                }
            }));
        });

        function testConnection() {
            const resultDiv = document.getElementById('test-result');
            const contentDiv = document.getElementById('test-result-content');
            
            resultDiv.classList.remove('hidden');
            contentDiv.innerHTML = 'Testing connection...';
            
            fetch('{{ route("admin.integrations.test.rajaongkir") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                let colorClass = data.success ? 'text-green-600' : 'text-red-600';
                let html = `<div class="font-bold ${colorClass}">${data.message}</div>`;
                html += `<div>Status Code: ${data.status_code}</div>`;
                html += `<div>Duration: ${data.duration}ms</div>`;
                if (data.data) {
                    html += `<pre class="mt-2 text-xs">${JSON.stringify(data.data, null, 2)}</pre>`;
                }
                contentDiv.innerHTML = html;
            })
            .catch(error => {
                contentDiv.innerHTML = `<div class="text-red-600">Error: ${error.message}</div>`;
            });
        }

        function testCost() {
            const destId = document.getElementById('test_destination_id').value;
            const weight = document.getElementById('test_weight').value;
            const courier = document.getElementById('test_courier').value;
            const resultDiv = document.getElementById('cost-result');
            
            // Validation
            if (!destId) {
                alert('Please select a destination first.');
                return;
            }
            if (!weight || weight < 1) {
                alert('Please enter a valid weight (min 1 gram).');
                return;
            }
            if (!courier) {
                alert('Please select a courier.');
                return;
            }

            resultDiv.classList.remove('hidden');
            // Improved Loading UI
            resultDiv.innerHTML = `
                <div class="flex items-center justify-center p-4">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600 dark:text-gray-400">Calculating shipping costs...</span>
                </div>
            `;

            fetch('{{ route("admin.integrations.rajaongkir.test-cost") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    destination_id: destId,
                    weight: weight,
                    courier: courier
                })
            })
            .then(response => {
                 if (!response.ok) {
                     return response.json().then(err => { throw new Error(err.message || 'Network response was not ok'); });
                 }
                 return response.json();
            })
            .then(data => {
                if (data.success) {
                    let html = `
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Courier</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Service</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cost</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ETD</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    `;
                    
                    data.data.forEach(courier => {
                        let courierName = (courier.name || courier.code || 'Unknown').toUpperCase();
                        
                        if (courier.costs && Array.isArray(courier.costs)) {
                            courier.costs.forEach(cost => {
                                let costVal = cost.cost[0].value;
                                let etd = cost.cost[0].etd || '-';
                                let service = cost.service;
                                let desc = cost.description;
                                
                                html += `
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${courierName}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div class="font-medium text-gray-900 dark:text-gray-200">${service}</div>
                                            <div class="text-xs text-gray-500">${desc}</div>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                            Rp ${new Intl.NumberFormat('id-ID').format(costVal)}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${etd} days</td>
                                    </tr>
                                `;
                            });
                        } 
                        else if (courier.service) {
                             html += `
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${courierName}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div class="font-medium text-gray-900 dark:text-gray-200">${courier.service}</div>
                                            <div class="text-xs text-gray-500">${courier.description}</div>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white font-bold">
                                            Rp ${new Intl.NumberFormat('id-ID').format(courier.cost)}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${courier.etd} days</td>
                                    </tr>
                                `;
                        }
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    resultDiv.innerHTML = html;
                } else {
                     resultDiv.innerHTML = `
                        <div class="rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Calculation Failed</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>${data.message}</p>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Error</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>${error.message}</p>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
        }
    </script>
</x-app-layout>