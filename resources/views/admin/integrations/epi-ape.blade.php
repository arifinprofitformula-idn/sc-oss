<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Integration System') }}
        </h2>
        <style>
            /* Custom Checkbox Style - Adapted to Indigo Theme */
            .epi-checkbox-container input {
                display: flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                opacity: 0;
                cursor: pointer;
                height: 0;
                width: 0;
            }

            .epi-checkbox-container {
                display: block;
                position: relative;
                cursor: pointer;
                font-size: 20px;
                user-select: none;
                border: 3px solid #c7d2fe; /* Indigo 200 */
                border-radius: 10px;
                overflow: hidden;
                width: 1.3em;
                height: 1.3em;
                /* Background handled by Tailwind classes */
            }

            /* Create a custom checkbox */
            .epi-checkmark {
                position: relative;
                top: 0;
                left: 0;
                height: 1.3em;
                width: 1.3em;
                background-color: #4f46e5; /* Indigo 600 */
                border-bottom: 1.5px solid #4f46e5;
                box-shadow: 0 0 1px #e0e7ff, inset 0 -2.5px 3px #818cf8,
                    inset 0 3px 3px rgba(0, 0, 0, 0.34);
                border-radius: 8px;
                transition: transform 0.3s ease-in-out;
            }

            /* Checked state: Show checkmark (Slide Up to 0) */
            .epi-checkbox-container input:checked ~ .epi-checkmark {
                animation: wipeIn 0.6s ease-in-out forwards;
            }

            /* Unchecked state: Hide checkmark (Slide Down to 40px) */
            .epi-checkbox-container input:not(:checked) ~ .epi-checkmark {
                animation: wipeOut 0.6s ease-in-out forwards;
            }

            @keyframes wipeIn {
                0% { transform: translateY(40px); }
                100% { transform: translateY(0); }
            }

            @keyframes wipeOut {
                0% { transform: translateY(0); }
                100% { transform: translateY(40px); }
            }

            /* Icon styling */
            .epi-checkmark:before {
                content: "";
                position: absolute;
                left: 10px;
                top: 4px;
                width: 5px;
                height: 10px;
                border: solid white;
                border-width: 0 2px 2px 0;
                transform: rotate(45deg);
                box-shadow: 0 4px 2px rgba(0, 0, 0, 0.34);
            }

            /* Price Preview Modal Style */
            .card {
                width: 320px;
                height: auto;
                min-height: 220px;
                background-color: rgb(255, 255, 255);
                border-radius: 8px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px 30px;
                gap: 13px;
                position: relative;
                overflow: hidden;
                box-shadow: 2px 2px 20px rgba(0, 0, 0, 0.062);
            }

            #cookieSvg {
                width: 50px;
            }

            #cookieSvg g path {
                fill: rgb(97, 81, 81);
            }

            .cookieHeading {
                font-size: 1.2em;
                font-weight: 800;
                color: rgb(26, 26, 26);
                text-align: center;
            }

            .cookieDescription {
                text-align: center;
                font-size: 0.9em;
                font-weight: 600;
                color: rgb(99, 99, 99);
                width: 100%;
            }

            .cookieDescription p {
                margin-bottom: 4px;
            }

            .buttonContainer {
                display: flex;
                gap: 20px;
                flex-direction: row;
                margin-top: 10px;
            }

            .acceptButton {
                width: 100px;
                height: 35px;
                background-color: #7b57ff;
                transition-duration: .2s;
                border: none;
                color: rgb(241, 241, 241);
                cursor: pointer;
                font-weight: 600;
                border-radius: 20px;
                box-shadow: 0 4px 6px -1px #977ef3, 0 2px 4px -1px #977ef3;
                transition: all .6s ease;
            }

            .acceptButton:hover {
                background-color: #9173ff;
                box-shadow: 0 10px 15px -3px #977ef3, 0 4px 6px -2px #977ef3;
                transition-duration: .2s;
            }
        </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Settings Form -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">EPI APE Configuration</h3>
                            
                            <form action="{{ route('admin.integrations.update') }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <!-- Enable Integration -->
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="epi_ape_active" name="epi_ape_active" type="checkbox" value="1" {{ $settings['active'] ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="epi_ape_active" class="font-medium text-gray-700 dark:text-gray-300">Enable EPI Auto Price Engine</label>
                                            <p class="text-gray-500 dark:text-gray-400">Enable automatic price synchronization.</p>
                                        </div>
                                    </div>

                                    <!-- API Key -->
                                    <div>
                                        <label for="epi_ape_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="password" name="epi_ape_api_key" id="epi_ape_api_key" value="{{ $settings['api_key'] ? '********' : '' }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Leave blank to keep current key.</p>
                                    </div>

                                    <!-- Base URL -->
                                    <div>
                                        <label for="epi_ape_base_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Base URL</label>
                                        <input type="url" name="epi_ape_base_url" id="epi_ape_base_url" value="{{ $settings['base_url'] }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                    </div>

                                    <!-- Update Interval -->
                                    <div>
                                        <label for="epi_ape_update_interval" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Update Interval (Minutes)</label>
                                        <input type="number" name="epi_ape_update_interval" id="epi_ape_update_interval" value="{{ $settings['update_interval'] }}" min="5" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                    </div>

                                    <!-- Notify Email -->
                                    <div>
                                        <label for="epi_ape_notify_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notification Email</label>
                                        <input type="email" name="epi_ape_notify_email" id="epi_ape_notify_email" value="{{ $settings['notify_email'] }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                        <p class="mt-1 text-xs text-gray-500">Receive alerts on sync errors.</p>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-between items-center">
                                    <div x-data="{ loading: false, message: '', success: false }" class="flex items-center">
                                        <button type="button" 
                                            @click="loading = true; message = ''; 
                                                fetch('{{ route('admin.integrations.test.epi-ape') }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    }
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    loading = false;
                                                    success = data.success;
                                                    message = data.message;
                                                })
                                                .catch(error => {
                                                    loading = false;
                                                    success = false;
                                                    message = 'Connection error';
                                                })"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                            <span x-show="!loading">Test Connection</span>
                                            <span x-show="loading" class="animate-spin mr-2">
                                                <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                        <div x-show="message" class="ml-3 text-xs" :class="success ? 'text-green-600' : 'text-red-600'" x-text="message"></div>
                                    </div>

                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Save Config
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sync Status & Manual Sync -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Sync Status</h3>
                            
                            <div x-data="{ syncing: false, showModal: false, result: {} }">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-500">Manual trigger for price synchronization.</p>
                                    </div>
                                    <button @click="syncing = true; 
                                            fetch('{{ route('admin.integrations.epi-ape.sync') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                }
                                            })
                                            .then(r => r.json())
                                            .then(data => {
                                                syncing = false;
                                                result = data;
                                                if (data.error_count > 0) {
                                                    showModal = true;
                                                } else {
                                                    window.location.reload();
                                                }
                                            })
                                            .catch(e => {
                                                syncing = false;
                                                alert('Sync failed: ' + e);
                                            })" 
                                        :disabled="syncing"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50">
                                        <span x-show="!syncing">Sync Now</span>
                                        <span x-show="syncing">Syncing...</span>
                                    </button>
                                </div>

                                <!-- Error Popup Modal -->
                                <div x-show="showModal" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                        </div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Sync Completed with Errors</h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500">
                                                                Sync finished with <span class="font-bold" x-text="result.updated_count"></span> updates and <span class="font-bold text-red-600" x-text="result.error_count"></span> errors.
                                                                Please review the error log below.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <button @click="window.location.reload()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                    View Error Log
                                                </button>
                                                <button @click="showModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Log -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Recent Activity
                            </h3>
                        </div>
                        <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            @forelse($logs as $log)
                                <li class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 ease-in-out text-sm">
                                    <div class="flex space-x-3">
                                        <div class="flex-1 space-y-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $log->method }} {{ $log->status_code }}
                                                    </span>
                                                    <span class="ml-2 font-mono text-xs text-gray-500">{{ Str::limit($log->endpoint, 30) }}</span>
                                                </h3>
                                                <p class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Duration: {{ $log->duration_ms }}ms
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">No logs found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Product Mapping -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Product Mapping Guide -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center mb-4">
                                <svg class="h-6 w-6 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Panduan Mapping Produk
                                </h3>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">
                                Berikut adalah referensi parameter yang telah divalidasi dari integrasi API EPI Auto Price Engine.
                                Gunakan ID ini untuk melakukan mapping produk.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Brand Table -->
                                <div class="overflow-hidden border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                        <h4 class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Brand Identity (epi_brand_id)</h4>
                                    </div>
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama Brand</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">1</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Goldgram</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">2</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Silvergram</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">3</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Meezan Gold</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">5</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">NEW BRAND</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Level Table -->
                                <div class="overflow-hidden border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                        <h4 class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price Level (epi_level_id)</h4>
                                    </div>
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Level / Tipe Harga</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">4</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Buyback</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">5</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Silverchannel <span class="text-xs text-gray-400 ml-1">(Khusus Silvergram)</span></td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">7</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Konsumen</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">8</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Epi-store</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">9</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Epi-channel</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-mono text-indigo-600 dark:text-indigo-400">10</td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Harga-standar-perak</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Product Mapping</h3>
                            <p class="text-sm text-gray-500 mb-4">Map your local products to EPI APE Brand ID and Level ID to enable auto-pricing.</p>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SKU</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">EPI Mapping (Brand | Silver | Cust | Gram)</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Sync</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse($products as $product)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $product->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $product->sku }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    <div class="flex space-x-2 items-center">
                                                        <input form="mapping-form-{{ $product->id }}" type="number" name="epi_brand_id" placeholder="Brand" title="Brand ID" value="{{ $product->epiMapping?->epi_brand_id }}" class="w-14 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required min="1">
                                                        <input form="mapping-form-{{ $product->id }}" type="number" name="epi_level_id" placeholder="Silver" title="Silverchannel Level ID" value="{{ $product->epiMapping?->epi_level_id }}" class="w-14 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required min="1">
                                                        <input form="mapping-form-{{ $product->id }}" type="number" name="epi_level_id_customer" placeholder="Cust" title="Customer Level ID" value="{{ $product->epiMapping?->epi_level_id_customer }}" class="w-14 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" min="1">
                                                        <input form="mapping-form-{{ $product->id }}" type="number" step="0.001" name="epi_gramasi" placeholder="Gram" title="Gramasi" value="{{ $product->epiMapping?->epi_gramasi ?? 1 }}" class="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required min="0.001">
                                                        <button type="button" onclick="previewPrice(this)" data-product-id="{{ $product->id }}" class="text-xs text-blue-600 hover:text-blue-800" title="Check Price">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="mt-1 flex items-center gap-2">
                                                        <label class="epi-checkbox-container bg-white dark:bg-gray-700">
                                                            <input form="mapping-form-{{ $product->id }}" type="checkbox" name="is_active" value="1" {{ ($product->epiMapping?->is_active ?? true) ? 'checked' : '' }}>
                                                            <div class="epi-checkmark"></div>
                                                        </label>
                                                        <span class="text-xs text-gray-600 dark:text-gray-400">Active</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    @if($product->epiMapping?->last_synced_at)
                                                        <div>{{ $product->epiMapping->last_synced_price ? 'IDR '.number_format($product->epiMapping->last_synced_price) : '-' }}</div>
                                                        <div class="text-xs text-gray-400">{{ $product->epiMapping->last_synced_at->diffForHumans() }}</div>
                                                    @else
                                                        <span class="text-xs text-gray-400">Never</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <form id="mapping-form-{{ $product->id }}" action="{{ route('admin.integrations.epi-ape.mapping.update') }}" method="POST" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">Save</button>
                                                    </form>
                                                    
                                                    @if($product->epiMapping)
                                                        <form id="delete-mapping-{{ $product->epiMapping->id }}" action="{{ route('admin.integrations.epi-ape.mapping.delete', $product->epiMapping->id) }}" method="POST" class="inline ml-2">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" 
                                                                onclick="if(confirm('Delete mapping?')) this.closest('form').submit()" 
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600">
                                                                Unlink
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                                    No active products found. Configure products at <a href="{{ route('admin.products.index') }}" class="text-indigo-600 hover:text-indigo-900">Product Management</a>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Error Log Section -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6" id="error-log-section">
                        <div class="p-6 text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Integration Error Log</h3>
                                <p class="text-sm text-gray-500">Detailed log of integration issues.</p>
                            </div>
                            <div class="flex space-x-2">
                                <form method="GET" class="flex items-center space-x-2">
                                    <select name="error_status" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">All Status</option>
                                        <option value="new" {{ request('error_status') == 'new' ? 'selected' : '' }}>New</option>
                                        <option value="resolved" {{ request('error_status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="ignored" {{ request('error_status') == 'ignored' ? 'selected' : '' }}>Ignored</option>
                                    </select>
                                </form>
                                <a href="{{ route('admin.integrations.epi-ape.errors.export') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                    Export CSV
                                </a>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Message</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($integrationErrors as $error)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                                {{ $error->created_at->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-gray-100">
                                                {{ $error->error_code }}
                                            </td>
                                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ Str::limit($error->message, 50) }}</div>
                                                <div class="text-xs text-gray-400">{{ Str::limit($error->recommended_action, 50) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $error->status == 'new' ? 'bg-red-100 text-red-800' : ($error->status == 'resolved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($error->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if($error->status == 'new')
                                                    <form action="{{ route('admin.integrations.epi-ape.errors.resolve', $error->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600 text-xs">Resolve</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                                No errors found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                            {{ $integrationErrors->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Price Preview Modal -->
    <div id="price-preview-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" style="backdrop-filter: blur(2px);">
        <div class="card">
            <!-- Icon -->
            <svg id="cookieSvg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier"> 
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.31-8.86c-1.77-.45-2.34-.94-2.34-1.67 0-.84.79-1.43 2.1-1.43 1.38 0 1.9.66 1.94 1.64h1.71c-.05-1.34-.87-2.57-2.49-2.97V5H10.9v1.69c-1.51.32-2.72 1.3-2.72 2.81 0 1.79 1.49 2.69 3.66 3.21 1.95.46 2.34 1.15 2.34 1.87 0 .53-.39 1.39-2.1 1.39-1.6 0-2.23-.72-2.32-1.64H8.04c.1 1.7 1.36 2.66 2.86 2.97V19h2.34v-1.67c1.52-.29 2.72-1.16 2.73-2.77-.01-2.2-1.9-2.96-3.66-3.42z" fill="rgb(97, 81, 81)"></path> 
                </g>
            </svg>
            <p class="cookieHeading" id="preview-title">Price Preview</p>
            <div class="cookieDescription" id="preview-content">
                Loading...
            </div>
            <div class="buttonContainer">
                <button class="acceptButton" onclick="document.getElementById('price-preview-modal').classList.add('hidden')">Close</button>
            </div>
        </div>
    </div>

    <script>
        function previewPrice(btn) {
            const tr = btn.closest('tr');
            const productName = tr.querySelector('td:first-child').innerText.trim().split('\n')[0];
            
            const productId = btn.getAttribute('data-product-id');
            
            // Helper to get input value by name for this specific product
            const getInputValue = (name) => {
                const el = document.querySelector(`input[form="mapping-form-${productId}"][name="${name}"]`);
                return el ? el.value : '';
            };
            
            const brandId = getInputValue('epi_brand_id');
            const levelId = getInputValue('epi_level_id');
            const custLevelId = getInputValue('epi_level_id_customer');
            const gramasi = getInputValue('epi_gramasi');
            
            const modal = document.getElementById('price-preview-modal');
            const title = document.getElementById('preview-title');
            const content = document.getElementById('preview-content');

            // Show modal with loading state
            title.innerText = productName;
            content.innerHTML = '<p class="text-gray-400">Fetching prices...</p>';
            modal.classList.remove('hidden');
            
            if(!brandId || !gramasi) {
                content.innerHTML = '<p class="text-red-500">Brand and Gramasi are required.</p>';
                return;
            }

            const fetchPrice = (lvlId, label) => {
                if(!lvlId) return Promise.resolve(null);
                
                return fetch("{{ route('admin.integrations.epi-ape.preview-price') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        product_id: productId,
                        brand_id: brandId, 
                        level_id: lvlId, 
                        gramasi: gramasi,
                        price_type: label // Silverchannel or Customer
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        return { label: label, price: data.price, formatted: new Intl.NumberFormat('id-ID').format(data.price) };
                    } else {
                        return { label: label, error: data.message };
                    }
                })
                .catch(err => ({ label: label, error: 'System Error' }));
            };

            const p1 = fetchPrice(levelId, 'Silverchannel');
            const p2 = fetchPrice(custLevelId, 'Customer');

            Promise.all([p1, p2]).then(results => {
                let html = '';

                results.forEach(res => {
                    if(!res) return; // skipped
                    if(res.error) {
                        html += `<p class="text-red-500 text-xs">${res.label}: ${res.error}</p>`;
                    } else {
                        html += `<p class="text-gray-800">${res.label}: <span class="font-bold text-indigo-600">IDR ${res.formatted}</span></p>`;
                    }
                });

                if (html === '') {
                     html = '<p class="text-gray-500">No levels selected.</p>';
                }

                content.innerHTML = html;
            });
        }
    </script>
</x-app-layout>
