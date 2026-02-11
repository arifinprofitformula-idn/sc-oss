<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mx-[10px] sm:mx-0 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div>
                                <!-- Name -->
                                <div>
                                    <x-input-label for="name" :value="__('Product Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $product->name)" required autofocus />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <!-- SKU -->
                                <div class="mt-4">
                                    <x-input-label for="sku" :value="__('SKU')" />
                                    <x-text-input id="sku" class="block mt-1 w-full" type="text" name="sku" :value="old('sku', $product->sku)" required />
                                    <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                                </div>

                                <!-- Brand -->
                                <div class="mt-4">
                                    <x-input-label for="brand_id" :value="__('Brand')" />
                                    <select id="brand_id" name="brand_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
                                </div>

                                <!-- Category -->
                                <div class="mt-4">
                                    <x-input-label for="category_id" :value="__('Category')" />
                                    <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div>
                                <!-- Price Silverchannel -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <x-input-label for="price_silverchannel" :value="__('Distributor Price (Silverchannel)')" />
                                        <span id="sync-status" class="text-xs text-gray-400 italic hidden">
                                            <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-indigo-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Syncing...
                                        </span>
                                    </div>
                                    <x-text-input id="price_silverchannel" class="block w-full" type="number" name="price_silverchannel" :value="old('price_silverchannel', $product->price_silverchannel)" required min="0" step="0.01" />
                                    <x-input-error :messages="$errors->get('price_silverchannel')" class="mt-2" />
                                </div>

                                <!-- Customer Price (API Integrated) -->
                                <div class="mt-4">
                                    <x-input-label for="price_customer" :value="__('Customer Price')" />
                                    <x-text-input id="price_customer" class="block mt-1 w-full bg-gray-50" type="number" name="price_customer" :value="old('price_customer', $product->price_customer)" min="0" step="0.01" />
                                    <p class="text-xs text-gray-500 mt-1">Automatically synced with EPI APE.</p>
                                    <x-input-error :messages="$errors->get('price_customer')" class="mt-2" />
                                </div>

                                <!-- Weight -->
                                <div class="mt-4">
                                    <x-input-label for="weight" :value="__('Weight (gr)')" />
                                    <x-text-input id="weight" class="block mt-1 w-full" type="number" name="weight" :value="old('weight', $product->weight)" required min="0" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" />
                                    <x-input-error :messages="$errors->get('weight')" class="mt-2" />
                                </div>

                                <!-- Stock -->
                                <div class="mt-4">
                                    <x-input-label for="stock" :value="__('Stock Quantity')" />
                                    <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock', $product->stock)" required min="0" />
                                    <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                                </div>

                                <!-- Image -->
                                <div class="mt-4">
                                    <x-input-label for="image" :value="__('Product Image')" />
                                    @if ($product->image)
                                        <div class="mb-2">
                                            <img src="{{ Storage::url($product->image) }}" alt="Current Image" class="h-20 w-20 object-cover rounded">
                                        </div>
                                    @endif
                                    <input id="image" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" type="file" name="image">
                                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Commission Settings -->
                        <div class="mb-6 border-t pt-4 mt-6" x-data="{ enabled: {{ old('commission_enabled', $product->commission_enabled) ? 'true' : 'false' }} }">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Komisi Referral</h3>
                            
                            <div class="mb-4">
                                <label for="commission_enabled" class="inline-flex items-center">
                                    <input id="commission_enabled" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="commission_enabled" value="1" x-model="enabled">
                                    <span class="ms-2 text-sm text-gray-600">Aktifkan Komisi Referral untuk Produk Ini</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="enabled" x-transition>
                                <div>
                                    <x-input-label for="commission_type" :value="__('Tipe Komisi')" />
                                    <select name="commission_type" id="commission_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="percentage" {{ old('commission_type', $product->commission_type) == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                        <option value="fixed" {{ old('commission_type', $product->commission_type) == 'fixed' ? 'selected' : '' }}>Nilai Tetap (IDR)</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('commission_type')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="commission_value" :value="__('Nilai Komisi')" />
                                    <x-text-input id="commission_value" class="block mt-1 w-full" type="number" name="commission_value" :value="old('commission_value', $product->commission_value)" min="0" step="0.01" />
                                    <p class="text-xs text-gray-500 mt-1">Jika %, masukkan 10 untuk 10%. Jika IDR, masukkan nominal.</p>
                                    <x-input-error :messages="$errors->get('commission_value')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="4">{{ old('description', $product->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Is Active -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Update Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stock History -->
            <div class="mx-[10px] sm:mx-0 bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Stock History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($product->stockLogs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $log->type)) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $log->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $log->quantity > 0 ? '+' : '' }}{{ $log->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->final_stock }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->user ? $log->user->name : 'System' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->note ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No stock history available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const syncUrl = "{{ route('admin.products.sync-price', $product->id) }}";
            const statusEl = document.getElementById('sync-status');
            const silverInput = document.getElementById('price_silverchannel');
            const customerInput = document.getElementById('price_customer');
            
            let isSyncing = false;

            function syncPrices() {
                if (isSyncing) return;
                if (!statusEl || !silverInput || !customerInput) return;

                isSyncing = true;
                
                statusEl.classList.remove('hidden');
                
                fetch(syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Only update if user is NOT currently editing the field
                        if (document.activeElement !== silverInput && data.price_silverchannel !== undefined) {
                            silverInput.value = data.price_silverchannel;
                        }
                        if (document.activeElement !== customerInput && data.price_customer !== undefined) {
                            customerInput.value = data.price_customer;
                        }
                        
                        // Show synced state briefly
                        statusEl.innerHTML = '<span class="text-green-600">Synced</span>';
                        setTimeout(() => {
                            statusEl.classList.add('hidden');
                            // Restore original loading content for next time
                            statusEl.innerHTML = `<svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-indigo-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg> Syncing...`;
                        }, 2000);
                    } else {
                        statusEl.innerHTML = '<span class="text-red-500 text-xs">Sync Failed</span>';
                        console.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error syncing prices:', error);
                    statusEl.innerHTML = '<span class="text-red-500 text-xs">Connection Error</span>';
                })
                .finally(() => {
                    isSyncing = false;
                });
            }

            // Initial sync
            syncPrices();

            // Poll every 10 seconds
            setInterval(syncPrices, 10000);
        });
    </script>
    @endpush
</x-app-layout>
