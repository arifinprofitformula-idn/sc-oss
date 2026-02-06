<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Integration System') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Settings Form -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Sync Status</h3>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-500">Manual trigger for price synchronization.</p>
                                </div>
                                <form action="{{ route('admin.integrations.epi-ape.sync') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Sync Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Log -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Product Mapping</h3>
                            <p class="text-sm text-gray-500 mb-4">Map your local products to EPI APE Brand ID and Level ID to enable auto-pricing.</p>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SKU</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">EPI Mapping (Brand | Level | Gram)</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Sync</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($products as $product)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $product->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $product->sku }}
                                                </td>
                                                <form action="{{ route('admin.integrations.epi-ape.mapping.update') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        <div class="flex space-x-2">
                                                            <input type="number" name="epi_brand_id" placeholder="Brand ID" value="{{ $product->epiMapping?->epi_brand_id }}" class="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                                            <input type="number" name="epi_level_id" placeholder="Level ID" value="{{ $product->epiMapping?->epi_level_id }}" class="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                                            <input type="number" step="0.001" name="epi_gramasi" placeholder="Gram" value="{{ $product->epiMapping?->epi_gramasi ?? 1 }}" class="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                                        </div>
                                                        <div class="mt-1">
                                                            <label class="inline-flex items-center">
                                                                <input type="checkbox" name="is_active" value="1" {{ ($product->epiMapping?->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-4 w-4">
                                                                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Active</span>
                                                            </label>
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
                                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">Save</button>
                                                        
                                                        @if($product->epiMapping)
                                                            <button type="button" 
                                                                onclick="if(confirm('Delete mapping?')) document.getElementById('delete-mapping-{{ $product->epiMapping->id }}').submit()" 
                                                                class="ml-2 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600">
                                                                Unlink
                                                            </button>
                                                        @endif
                                                    </td>
                                                </form>
                                                @if($product->epiMapping)
                                                    <form id="delete-mapping-{{ $product->epiMapping->id }}" action="{{ route('admin.integrations.epi-ape.mapping.delete', $product->epiMapping->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
