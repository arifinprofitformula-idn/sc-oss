<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12" x-data="checkoutPage">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
                <!-- Left Column: Billing & Shipping Details -->
                <div class="flex-1 space-y-6">
                    
                    <!-- Billing Details -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Detail Tagihan</h3>
                        
                        <!-- Readonly Form -->
                        <div class="space-y-4">
                            <!-- ID & Name -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label value="ID Silverchannel" />
                                    <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                        value="{{ $user->silver_channel_id ?? '-' }}" disabled readonly />
                                </div>
                                <div>
                                    <x-input-label value="Nama Lengkap" />
                                    <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                        value="{{ $user->name }}" disabled readonly />
                                </div>
                            </div>

                            <!-- Contact -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label value="Email" />
                                    <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                        value="{{ $user->email }}" disabled readonly />
                                </div>
                                <div>
                                    <x-input-label value="Telepon / WA" />
                                    <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                        value="{{ $user->phone ?? '-' }}" disabled readonly />
                                </div>
                            </div>

                            <!-- Address (Only shown if NOT shipping different) -->
                            <template x-if="!shipDifferent">
                                <div class="space-y-4 transition-all duration-300">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <x-input-label value="Provinsi" />
                                            <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                                value="{{ $user->province_name ?? '-' }}" disabled readonly />
                                        </div>
                                        <div>
                                            <x-input-label value="Kota/Kabupaten" />
                                            <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                                value="{{ $user->city_name ?? '-' }}" disabled readonly />
                                        </div>
                                        <div>
                                            <x-input-label value="Kecamatan" />
                                            <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                                value="{{ $user->subdistrict_name ?? $user->subdistrict_id ?? '-' }}" disabled readonly />
                                        </div>
                                        <div>
                                            <x-input-label value="Kelurahan / Desa" />
                                            <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                                value="{{ $user->village_name ?? '-' }}" disabled readonly />
                                        </div>
                                        <div>
                                            <x-input-label value="Kode Pos" />
                                            <x-text-input class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                                value="{{ $user->postal_code ?? '-' }}" disabled readonly />
                                        </div>
                                    </div>
                                    <div>
                                        <x-input-label value="Alamat Lengkap" />
                                        <textarea class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed" 
                                            rows="3" disabled readonly>{{ $user->address ?? '-' }}</textarea>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Ship to Different Address Toggle -->
                        <div class="mt-6 flex items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                            <label class="inline-flex items-center cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" x-model="shipDifferent" class="sr-only">
                                    <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition-colors" :class="{ 'bg-yellow-500': shipDifferent }"></div>
                                    <div class="dot absolute w-4 h-4 bg-white rounded-full shadow left-1 top-1 transition-transform" :class="{ 'transform translate-x-full': shipDifferent }"></div>
                                </div>
                                <span class="ml-3 text-sm font-bold text-gray-900 dark:text-gray-100">Kirim ke alamat yang berbeda?</span>
                            </label>
                        </div>
                    </div>

                    <!-- Shipping Address Form (Only visible if shipDifferent) -->
                    <div x-show="shipDifferent" x-transition class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-yellow-500">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Alamat Pengiriman Baru</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Name -->
                            <div>
                                <x-input-label for="shipping_name" value="Nama Penerima *" />
                                <x-text-input id="shipping_name" class="block mt-1 w-full" type="text" x-model="shippingForm.name" />
                                <span class="text-red-500 text-sm" x-text="errors['shipping_address.name']"></span>
                            </div>

                            <!-- Contact Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="shipping_email" value="Email *" />
                                    <x-text-input id="shipping_email" class="block mt-1 w-full" type="email" x-model="shippingForm.email" />
                                    <span class="text-red-500 text-sm" x-text="errors['shipping_address.email']"></span>
                                </div>
                                <div>
                                    <x-input-label for="shipping_phone" value="Telepon / WA *" />
                                    <x-text-input id="shipping_phone" class="block mt-1 w-full" type="text" x-model="shippingForm.phone" />
                                    <span class="text-red-500 text-sm" x-text="errors['shipping_address.phone']"></span>
                                </div>
                            </div>

                            <!-- Address Selects -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="shipping_province" value="Provinsi *" />
                                    <select id="shipping_province" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        x-model="shippingForm.province_id" @change="fetchCities('shipping')">
                                        <option value="">Pilih Provinsi</option>
                                        <template x-for="prov in provinces" :key="prov.province_id">
                                            <option :value="prov.province_id" x-text="prov.province"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="shipping_city" value="Kota/Kab *" />
                                    <select id="shipping_city" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        x-model="shippingForm.city_id" @change="fetchSubdistricts('shipping')" :disabled="!shippingForm.province_id">
                                        <option value="">Pilih Kota</option>
                                        <template x-for="city in shippingCities" :key="city.city_id">
                                            <option :value="city.city_id" x-text="city.city_name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="shipping_subdistrict" value="Kecamatan *" />
                                    <select id="shipping_subdistrict" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        x-model="shippingForm.subdistrict_id" @change="onSubdistrictChange('shipping')" :disabled="!shippingForm.city_id">
                                        <option value="">Pilih Kecamatan</option>
                                        <template x-for="sub in shippingSubdistricts" :key="sub.subdistrict_id">
                                            <option :value="sub.subdistrict_id" x-text="sub.subdistrict_name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <!-- Village & Postal Code -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Village Select (Only for API ID) -->
                                <div x-show="addressProvider === 'api_id'">
                                    <x-input-label for="shipping_village" value="Kelurahan *" />
                                    <select id="shipping_village" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        x-model="shippingForm.village_id" @change="fetchShippingCosts()" :disabled="!shippingForm.subdistrict_id">
                                        <option value="">Pilih Kelurahan</option>
                                        <template x-for="village in shippingVillages" :key="village.village_id">
                                            <option :value="village.village_id" x-text="village.village_name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Village Text (For Non-API ID) -->
                                <div x-show="addressProvider !== 'api_id'">
                                    <x-input-label for="shipping_village_text" value="Kelurahan / Desa" />
                                    <x-text-input id="shipping_village_text" class="block mt-1 w-full" type="text" x-model="shippingForm.village_name" placeholder="Nama Kelurahan / Desa" />
                                    <span class="text-red-500 text-sm" x-text="errors['shipping_address.village_name']"></span>
                                </div>

                                <!-- Postal Code -->
                                <div>
                                    <x-input-label for="shipping_postal_code" value="Kode Pos *" />
                                    <x-text-input id="shipping_postal_code" class="block mt-1 w-full" type="text" x-model="shippingForm.postal_code" placeholder="Kode Pos" />
                                    <span class="text-red-500 text-sm" x-text="errors['shipping_address.postal_code']"></span>
                                </div>
                            </div>

                            <!-- Address Text -->
                            <div>
                                <x-input-label for="shipping_address" value="Alamat Lengkap Tujuan *" />
                                <textarea id="shipping_address" rows="3" 
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                                    x-model="shippingForm.address" 
                                    placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan..."
                                    required minlength="10"></textarea>
                                <p class="mt-1 text-xs text-gray-500">Mohon tuliskan alamat selengkap mungkin untuk memudahkan kurir.</p>
                                <span class="text-red-500 text-sm" x-text="errors['shipping_address.address']"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <x-input-label for="notes" value="Catatan Pesanan (opsional)" />
                        <textarea id="notes" rows="2" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                            x-model="notes" placeholder="Catatan khusus untuk pengiriman..."></textarea>
                    </div>

                </div>

                <!-- Right Column: Order Summary -->
                <div class="w-full lg:w-96 xl:w-[420px] flex-shrink-0 order-first lg:order-last">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 lg:sticky top-6 overflow-hidden">
                        <div class="px-4 sm:px-6 pt-4 sm:pt-6 pb-3 sm:pb-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/40 flex items-center justify-center text-blue-600">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-2 13H5L3 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Pesanan Anda</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="cartItems.length + ' item produk'" ></p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Pesanan & Pembayaran (Minimalist Redesign) -->
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            
                            <!-- 1. Daftar Produk (Compact) -->
                            <div class="px-5 py-4 max-h-[300px] overflow-y-auto custom-scrollbar">
                                <template x-if="cartItems.length === 0">
                                    <div class="text-center py-4 text-gray-500 text-sm">Keranjang kosong</div>
                                </template>
                                <template x-for="item in cartItems" :key="item.id">
                                    <div class="flex gap-3 py-2 group">
                                        <!-- Thumbnail -->
                                        <div class="w-12 h-12 flex-shrink-0 rounded-md bg-gray-100 overflow-hidden border border-gray-200">
                                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" loading="lazy" @@error="item.image = '/images/placeholder-product.jpg'">
                                        </div>
                                        <!-- Info -->
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-1" x-text="item.name"></h4>
                                            <p class="text-[11px] text-gray-500 truncate" x-text="item.variant_label || 'SKU: ' + (item.sku || '-')"></p>
                                        </div>
                                        <!-- Price -->
                                        <div class="text-right flex flex-col justify-center">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="formatMoney(item.quantity * item.price)"></span>
                                            <span class="text-[10px] text-gray-500" x-text="item.quantity + ' x ' + formatMoney(item.price)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        <!-- Section 2: Perhitungan Biaya (Minimalist) -->
                        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 space-y-5">
                            
                            <!-- Courier Selection -->
                            <div class="space-y-3">
                                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pengiriman</label>
                                
                                @php
                                    $courierOptions = [];
                                    if (isset($checkoutCouriers) && is_array($checkoutCouriers)) {
                                        $courierOptions = $checkoutCouriers;
                                    } else {
                                        $courierOptions = [];
                                    }
                                @endphp

                                <div x-show="availableCouriers.length === 0" class="p-3 bg-amber-50 text-amber-700 rounded-md text-sm border border-amber-200" style="display: none;">
                                    <p class="font-medium">Belum ada kurir aktif.</p>
                                </div>

                                <div class="grid grid-cols-1 gap-3">
                                    <!-- Courier Dropdown -->
                                    <select x-model="selectedCourier" @change="fetchShippingCosts()" 
                                        :disabled="shippingLoading || availableCouriers.length === 0"
                                        class="block w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        <option value="">Pilih Kurir Pengiriman</option>
                                        @foreach($courierOptions as $courier)
                                            <option value="{{ $courier }}">{{ strtoupper($courier) }}</option>
                                        @endforeach
                                    </select>

                                    <!-- Service Selection (Simplified List) -->
                                    <div x-show="shippingLoading" class="text-center py-4">
                                        <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-xs text-gray-500 mt-2 block">Cek ongkir...</span>
                                    </div>

                                    <div x-show="!shippingLoading && shippingError" class="text-sm text-red-600 bg-red-50 p-3 rounded-md border border-red-100">
                                        <p x-text="shippingError"></p>
                                    </div>

                                    <div x-show="!shippingLoading && !shippingError && shippingOptions.length > 0" class="space-y-2">
                                        <template x-for="(option, index) in shippingOptions" :key="index">
                                            <div @click="selectedShippingIndex = index"
                                                class="cursor-pointer p-3 rounded-lg border transition-all duration-200 flex justify-between items-center group"
                                                :class="selectedShippingIndex === index ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-500' : 'border-gray-200 dark:border-gray-700 hover:border-blue-300'">
                                                
                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center justify-center w-5 h-5 rounded-full border border-gray-300" 
                                                         :class="selectedShippingIndex === index ? 'border-blue-600 bg-blue-600' : 'bg-white'">
                                                        <div class="w-2 h-2 rounded-full bg-white" x-show="selectedShippingIndex === index"></div>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="option.service"></p>
                                                        <p class="text-[11px] text-gray-500" x-text="'Est. ' + option.cost[0].etd + ' hari'"></p>
                                                    </div>
                                                </div>
                                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100" x-text="formatMoney(option.cost[0].value)"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Cost Breakdown (Grouped) -->
                            <div class="space-y-3 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rincian Biaya</label>
                                
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>Subtotal Produk</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100" x-text="formatMoney(subtotal)"></span>
                                    </div>
                                    
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>Ongkos Kirim</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100" x-text="shippingCost > 0 ? formatMoney(shippingCost) : '-'"></span>
                                    </div>

                                    <!-- Mandatory Insurance -->
                                    <div x-show="insuranceSettings.active" class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <div class="flex items-center gap-1">
                                            <span>Biaya Asuransi Pengiriman (LM)</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-gray-100" x-text="insuranceCost > 0 ? formatMoney(insuranceCost) : '-'"></span>
                                    </div>

                                    <div class="flex justify-between text-gray-600 dark:text-gray-400" x-show="uniqueCode > 0">
                                        <span>Kode Unik</span>
                                        <span class="font-medium text-blue-600" x-text="formatMoney(uniqueCode)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-base font-bold text-gray-900 dark:text-gray-100">Total Pembayaran</span>
                                <span class="text-2xl font-bold text-blue-600" x-text="formatMoney(grandTotal)"></span>
                            </div>
                        </div>

                        <!-- Section 3: Metode Pembayaran (Minimalist - Bank Only) -->
                        <div class="px-5 py-5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 block">Metode Pembayaran</label>
                            
                            <!-- Hidden Input for Form Submission -->
                            <input type="hidden" name="payment_method" x-model="paymentMethod">

                            <!-- Single Option: Bank Transfer -->
                            <div class="relative overflow-hidden rounded-xl border border-blue-500 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-blue-500 transition-all">
                                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-blue-500/10 to-transparent -mr-4 -mt-4 rounded-full"></div>
                                
                                <div class="p-4 flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 flex-shrink-0">
                                        <!-- Bank Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Bank Transfer</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">BCA, Mandiri, BNI, BRI</p>
                                    </div>
                                    <div class="text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Pay Now Button -->
                            <button @click="submitOrder" 
                                :disabled="processing || cartItems.length === 0 || selectedShippingIndex === ''"
                                class="mt-6 w-full flex items-center justify-center rounded-md border border-transparent px-6 py-3 text-base font-bold text-white shadow-sm focus:outline-none transition-all duration-200 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                
                                <span x-show="!processing" class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    BAYAR SEKARANG
                                </span>
                                
                                <span x-show="processing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memproses...
                                </span>
                            </button>
                            
                            <p class="text-center text-[10px] text-gray-400 mt-3">
                                Dengan mengklik tombol di atas, Anda menyetujui <a href="#" @click.prevent="showTermsModal = true" class="underline hover:text-gray-500 cursor-pointer">Syarat & Ketentuan</a> kami.
                            </p>
                        </div>

                        <div x-show="selectedShippingIndex === '' && cartItems.length > 0" class="px-5 pb-5 text-center text-xs text-red-500">
                            Silakan pilih layanan pengiriman terlebih dahulu
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Terms & Conditions Modal -->
        <template x-teleport="body">
            <div x-show="showTermsModal" class="relative z-[10000]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <!-- Background backdrop -->
                <div x-show="showTermsModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <!-- Modal Panel -->
                        <div x-show="showTermsModal"
                             @click.outside="showTermsModal = false"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="relative w-[90%] max-w-[960px] transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all flex flex-col max-h-[90vh] border border-gray-200 dark:border-gray-700">
                            
                            <!-- Header -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 sm:p-10 flex justify-between items-center border-b border-gray-200 dark:border-gray-600 shrink-0">
                                <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100" id="modal-title">Syarat & Ketentuan Transaksi</h3>
                                <button @click="showTermsModal = false" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="p-6 sm:p-10 overflow-y-auto custom-scrollbar bg-white dark:bg-gray-800">
                                <div class="prose dark:prose-invert max-w-none text-sm sm:text-base text-gray-600 dark:text-gray-300 space-y-6">
                                    
                                    <section>
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-lg mb-2">1. Ketentuan Umum</h4>
                                        <p class="leading-relaxed">Selamat datang di EPI Order & Sales System (EPI-OSS). Dengan mengakses dan menggunakan platform ini, serta melanjutkan proses checkout, Anda menyetujui untuk tunduk pada seluruh Syarat dan Ketentuan yang berlaku. Kami berhak mengubah ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya.</p>
                                    </section>

                                    <section>
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-lg mb-2">2. Pemesanan & Pembayaran</h4>
                                        <ul class="list-disc pl-5 space-y-2">
                                            <li>Pemesanan produk dilakukan melalui sistem checkout resmi EPI-OSS.</li>
                                            <li>Harga yang tertera pada ringkasan pesanan adalah harga final, termasuk biaya produk dan biaya tambahan lainnya (jika ada), namun belum termasuk ongkos kirim kecuali dinyatakan lain.</li>
                                            <li>Pembayaran wajib dilakukan melalui metode <strong>Transfer Bank</strong> ke rekening resmi yang tertera pada halaman konfirmasi pembayaran.</li>
                                            <li>Pesanan akan diproses (packing & kirim) hanya setelah bukti pembayaran diunggah dan diverifikasi valid oleh admin kami.</li>
                                            <li>Pembayaran yang tidak dikonfirmasi dalam waktu 1x24 jam berpotensi menyebabkan pembatalan pesanan otomatis.</li>
                                        </ul>
                                    </section>

                                    <section>
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-lg mb-2">3. Pengiriman & Logistik</h4>
                                        <ul class="list-disc pl-5 space-y-2">
                                            <li>Pengiriman dilakukan menggunakan jasa ekspedisi pihak ketiga yang dipilih pembeli saat checkout (JNE, J&T, SiCepat, dll).</li>
                                            <li>Estimasi waktu pengiriman yang tertera adalah perkiraan dari pihak ekspedisi dan dapat berubah tergantung kondisi lapangan.</li>
                                            <li>Nomor resi (Airway Bill) akan diinformasikan melalui dashboard sistem segera setelah pesanan diserahkan ke kurir.</li>
                                            <li>Segala bentuk keterlambatan, kehilangan, atau kerusakan yang terjadi selama proses pengiriman oleh pihak ekspedisi berada di luar kendali dan tanggung jawab langsung kami, namun kami akan membantu proses pelacakan dan klaim asuransi (jika diasuransikan).</li>
                                        </ul>
                                    </section>

                                    <section>
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-lg mb-2">4. Kebijakan Pengembalian (Retur) & Refund</h4>
                                        <ul class="list-disc pl-5 space-y-2">
                                            <li>Kami menjamin kualitas produk yang dikirim. Namun, jika terjadi cacat produksi atau kesalahan pengiriman barang, Anda berhak mengajukan klaim.</li>
                                            <li><strong>WAJIB VIDEO UNBOXING:</strong> Segala bentuk komplain (barang kurang/rusak/salah) WAJIB menyertakan bukti video unboxing paket sejak segel utuh sampai dibuka, tanpa jeda (no cut) dan tanpa edit.</li>
                                            <li>Batas waktu pelaporan komplain adalah maksimal <strong>1x24 jam</strong> setelah status resi dinyatakan "Diterima/Delivered".</li>
                                            <li>Jika klaim disetujui, solusi dapat berupa penggantian barang atau pengembalian dana (refund) ke Saldo Dompet (Wallet) akun Anda.</li>
                                        </ul>
                                    </section>

                                    <section>
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-lg mb-2">5. Privasi & Data Pengguna</h4>
                                        <p class="leading-relaxed">Kami menjaga kerahasiaan data pribadi Anda (Nama, Alamat, No HP, dll) dan hanya menggunakannya untuk keperluan pemrosesan pesanan dan pengiriman. Data tidak akan diperjualbelikan kepada pihak ketiga manapun.</p>
                                    </section>

                                    <section>
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-lg mb-2">6. Hukum yang Berlaku</h4>
                                        <p class="leading-relaxed">Syarat dan ketentuan ini diatur, ditafsirkan, dan dilaksanakan berdasarkan hukum yang berlaku di Negara Republik Indonesia. Segala perselisihan yang timbul akan diselesaikan secara musyawarah untuk mufakat.</p>
                                    </section>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 sm:p-10 flex flex-row-reverse border-t border-gray-200 dark:border-gray-600 shrink-0">
                                <button type="button" 
                                        @click="showTermsModal = false"
                                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-3 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    Saya Mengerti
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <style>
        /* Custom Scrollbar for Product List */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f3f4f6;
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        .dark .custom-scrollbar {
            scrollbar-color: #4b5563 #374151;
        }
        
        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: #374151;
        }
        
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
        }
        
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        
        /* Smooth transitions for image loading */
        .image-loading {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Enhanced hover effects */
        .product-card-hover {
            transition: all 0.2s ease-in-out;
        }
        
        .product-card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        
        /* Responsive image container */
        .image-container {
            aspect-ratio: 1 / 1;
        }
        
        /* Lightbox animation */
        .lightbox-enter {
            animation: lightboxEnter 0.3s ease-out;
        }
        
        @keyframes lightboxEnter {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>

    @push('scripts')
    @php
        $integrationService = app(\App\Services\IntegrationService::class);
        $currentProvider = $integrationService->get('shipping_provider', 'rajaongkir');
        
        $checkoutBootstrap = array(
            'cartItems' => $cartItems,
            'addressProvider' => $currentProvider,
            'userProfile' => array(
                'province_id' => $user->province_id,
                'city_id' => $user->city_id,
                'subdistrict_id' => $user->subdistrict_id,
                'village_id' => $user->village_id,
            ),
            'billingAddress' => array(
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'province_id' => $user->province_id,
                'city_id' => $user->city_id,
                'subdistrict_id' => $user->subdistrict_id,
                'village_id' => $user->village_id,
                'village_name' => $user->village_name ?? '',
                'postal_code' => $user->postal_code ?? '',
                'address' => $user->address ?? '',
            ),
            'uniqueCode' => $uniqueCode,
            'insuranceSettings' => $insuranceSettings,
            'couriers' => $checkoutCouriers ?? array(),
            'routes' => array(
                'locations' => array(
                    'provinces' => route('profile.locations.provinces'),
                    'cities' => route('profile.locations.cities', array('province' => ':id')),
                    'subdistricts' => route('profile.locations.subdistricts', array('city' => ':id')),
                    'villages' => route('profile.locations.villages', array('subdistrict' => ':id')),
                ),
                'shippingCost' => route('silverchannel.checkout.shipping-cost'),
                'processOrder' => route('silverchannel.checkout.process'),
            ),
        );
    @endphp

    <input type="hidden" id="checkout-bootstrap-data" value='@json($checkoutBootstrap)'>

    <script>
        (function () {
            var el = document.getElementById('checkout-bootstrap-data');
            var data = {};
            if (el && el.value) {
                try {
                    data = JSON.parse(el.value);
                } catch (e) {
                    data = {};
                }
            }
            window.checkoutBootstrap = data;
        })();
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutPage', () => ({
                shipDifferent: false,
                showTermsModal: false,
                cartItems: [],
                uniqueCode: 0,
                notes: '',
                paymentMethod: 'transfer',
                
                // Insurance
                insuranceSettings: { active: false, percentage: 0, description: '' },

                // Address Provider
                addressProvider: '',
                
                // Weight Data
                totalWeight: 0,
                
                // Shipping Form Data
                shippingForm: {
                    name: '',
                    email: '',
                    phone: '',
                    province_id: '',
                    city_id: '',
                    subdistrict_id: '',
                    village_id: '',
                    village_name: '',
                    postal_code: '',
                    address: ''
                },
                
                // Data Sources
                provinces: [],
                shippingCities: [],
                shippingSubdistricts: [],
                shippingVillages: [],

                // Caches
                provinceCache: null,
                cityCache: {},
                subdistrictCache: {},
                villageCache: {},
                
                // Shipping Logic
                availableCouriers: [],
                selectedCourier: '',
                shippingOptions: [],
                selectedShippingIndex: '',
                sortMode: 'price',
                shippingLoading: false,
                shippingError: '',
                shippingCostCache: {},
                
                processing: false,
                errors: {},

                async init() {
                    const bs = window.checkoutBootstrap;
                    this.uniqueCode = bs.uniqueCode;
                    this.insuranceSettings = bs.insuranceSettings || { active: false, percentage: 0, description: '' };
                    this.addressProvider = bs.addressProvider || 'rajaongkir';

                    this.availableCouriers = Array.isArray(bs.couriers) ? bs.couriers : [];
                    if (this.availableCouriers.length > 0) {
                        this.selectedCourier = this.availableCouriers[0];
                    }
                    
                    // Map Cart Items with enhanced image properties
                    this.cartItems = (bs.cartItems || []).map(item => ({
                        id: item.product_id,
                        name: item.product.name,
                        sku: item.product.sku,
                        price: item.price_final,
                        quantity: item.quantity,
                        image: item.product.image_url,
                        weight: item.product.weight || 1000,
                        imageLoaded: false, // For lazy loading state
                        variant_label: item.variant_label || null
                    }));

                    await this.fetchProvinces();

                    if (this.selectedCourier) {
                        await this.fetchShippingCosts();
                    }

                    this.$watch('shipDifferent', (value) => {
                        if (this.selectedCourier) {
                            if (!value) {
                                this.fetchShippingCosts();
                            } else {
                                if (this.shippingForm.subdistrict_id) {
                                    if (this.addressProvider === 'api_id' && !this.shippingForm.village_id) {
                                        this.shippingOptions = [];
                                        this.selectedShippingIndex = '';
                                        return;
                                    }
                                    this.fetchShippingCosts();
                                } else {
                                    this.shippingOptions = [];
                                    this.selectedShippingIndex = '';
                                }
                            }
                        }
                    });
                },
                
                get subtotal() {
                    return this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                
                get shippingCost() {
                    if (this.selectedShippingIndex === '') return 0;
                    const option = this.shippingOptions[this.selectedShippingIndex];
                    return option ? option.cost[0].value : 0;
                },

                get selectedShippingOption() {
                    if (this.selectedShippingIndex === '') return null;
                    return this.shippingOptions[this.selectedShippingIndex] || null;
                },

                get shippingEta() {
                    if (!this.selectedShippingOption || !this.selectedShippingOption.cost || !this.selectedShippingOption.cost[0]) {
                        return null;
                    }
                    return this.selectedShippingOption.cost[0].etd;
                },
                
                get insuranceCost() {
                    if (!this.insuranceSettings.active) return 0;
                    return this.subtotal * (this.insuranceSettings.percentage / 100);
                },

                get grandTotal() {
                    return this.subtotal + this.shippingCost + this.uniqueCode + this.insuranceCost;
                },
                
                formatMoney(amount) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                },

                formatWeight(grams) {
                    if (grams < 1000) {
                        return new Intl.NumberFormat('id-ID').format(grams) + ' gr';
                    }
                    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 }).format(grams / 1000) + ' kg';
                },

                applySorting() {
                    if (!Array.isArray(this.shippingOptions) || this.shippingOptions.length === 0) {
                        return;
                    }

                    if (this.sortMode === 'price') {
                        this.shippingOptions.sort((a, b) => {
                            const av = a.cost && a.cost[0] ? a.cost[0].value : 0;
                            const bv = b.cost && b.cost[0] ? b.cost[0].value : 0;
                            return av - bv;
                        });
                    } else if (this.sortMode === 'etd') {
                        const parseEtd = (value) => {
                            if (!value) return 999;
                            const num = parseInt(String(value).split('-')[0], 10);
                            return Number.isNaN(num) ? 999 : num;
                        };

                        this.shippingOptions.sort((a, b) => {
                            const ae = a.cost && a.cost[0] ? parseEtd(a.cost[0].etd) : 999;
                            const be = b.cost && b.cost[0] ? parseEtd(b.cost[0].etd) : 999;
                            return ae - be;
                        });
                    }

                    this.selectedShippingIndex = '';
                },

                // Location Fetchers
                async fetchProvinces() {
                    if (this.provinceCache) {
                        this.provinces = this.provinceCache;
                        return;
                    }
                    try {
                        const res = await fetch(window.checkoutBootstrap.routes.locations.provinces);
                        this.provinces = await res.json();
                        this.provinceCache = this.provinces;
                    } catch (e) { console.error(e); }
                },

                async fetchCities(type) {
                    // Only for shipping form as billing is readonly
                    if (type !== 'shipping') return;
                    
                    this.shippingCities = [];
                    this.shippingSubdistricts = [];
                    this.shippingVillages = [];
                    this.shippingForm.city_id = '';
                    this.shippingForm.subdistrict_id = '';
                    this.shippingForm.village_id = '';
                    
                    if (!this.shippingForm.province_id) return;

                    if (this.cityCache[this.shippingForm.province_id]) {
                        this.shippingCities = this.cityCache[this.shippingForm.province_id];
                        return;
                    }
                    
                    const url = window.checkoutBootstrap.routes.locations.cities.replace(':id', this.shippingForm.province_id);
                    try {
                        const res = await fetch(url);
                        this.shippingCities = await res.json();
                        this.cityCache[this.shippingForm.province_id] = this.shippingCities;
                    } catch (e) { console.error(e); }
                },

                async fetchSubdistricts(type) {
                    if (type !== 'shipping') return;
                    
                    this.shippingSubdistricts = [];
                    this.shippingVillages = [];
                    this.shippingForm.subdistrict_id = '';
                    this.shippingForm.village_id = '';
                    
                    if (!this.shippingForm.city_id) return;

                    if (this.subdistrictCache[this.shippingForm.city_id]) {
                        this.shippingSubdistricts = this.subdistrictCache[this.shippingForm.city_id];
                        return;
                    }
                    
                    const url = window.checkoutBootstrap.routes.locations.subdistricts.replace(':id', this.shippingForm.city_id);
                    try {
                        const res = await fetch(url);
                        this.shippingSubdistricts = await res.json();
                        this.subdistrictCache[this.shippingForm.city_id] = this.shippingSubdistricts;
                    } catch (e) { console.error(e); }
                },

                async onSubdistrictChange(type) {
                    if (type !== 'shipping') return;
                    this.shippingVillages = [];
                    this.shippingForm.village_id = '';
                    
                    if (this.addressProvider === 'api_id') {
                        await this.fetchVillages(type);
                    } else {
                        // RajaOngkir stops at subdistrict
                        this.fetchShippingCosts();
                    }
                },

                async fetchVillages(type) {
                     if (type !== 'shipping') return;
                     if (!this.shippingForm.subdistrict_id) return;
                     
                     if (this.villageCache[this.shippingForm.subdistrict_id]) {
                         this.shippingVillages = this.villageCache[this.shippingForm.subdistrict_id];
                         return;
                     }
                     
                     const url = window.checkoutBootstrap.routes.locations.villages.replace(':id', this.shippingForm.subdistrict_id);
                     try {
                         const res = await fetch(url);
                         this.shippingVillages = await res.json();
                         this.villageCache[this.shippingForm.subdistrict_id] = this.shippingVillages;
                     } catch (e) { console.error(e); }
                },

                async fetchShippingCosts() {
                    // Determine which subdistrict to use
                    let locationId = null;
                    let useVillage = (this.addressProvider === 'api_id');
                    
                    if (this.shipDifferent) {
                        if (useVillage) {
                            locationId = this.shippingForm.village_id;
                        } else {
                            locationId = this.shippingForm.subdistrict_id;
                        }
                    } else {
                        if (useVillage) {
                            locationId = window.checkoutBootstrap.billingAddress.village_id;
                        } else {
                            locationId = window.checkoutBootstrap.billingAddress.subdistrict_id;
                        }
                    }

                    if (!locationId) {
                        this.shippingOptions = [];
                        this.selectedShippingIndex = '';
                        if (this.selectedCourier) {
                            if (useVillage) {
                                this.shippingError = 'Mohon lengkapi alamat (Kelurahan) untuk menghitung ongkir.';
                            } else {
                                this.shippingError = 'Mohon lengkapi alamat (Kecamatan) untuk menghitung ongkir.';
                            }
                        }
                        return;
                    }

                    this.shippingError = '';

                    if (!this.selectedCourier) {
                        this.shippingOptions = [];
                        this.selectedShippingIndex = '';
                        this.shippingError = 'Silakan pilih kurir pengiriman terlebih dahulu.';
                        return;
                    }

                    const cacheKey = `${locationId}_${this.selectedCourier}`;

                    if (this.shippingCostCache[cacheKey]) {
                        this.shippingOptions = this.shippingCostCache[cacheKey];
                        this.selectedShippingIndex = '';
                        return;
                    }

                    this.shippingLoading = true;
                    this.shippingOptions = [];
                    this.selectedShippingIndex = '';

                    try {
                        const res = await fetch(window.checkoutBootstrap.routes.shippingCost, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                destination_subdistrict_id: locationId,
                                courier: this.selectedCourier
                            })
                        });
                        
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            this.shippingOptions = [];
                            this.selectedShippingIndex = '';
                            this.shippingError = data.message || 'Maaf, layanan pengiriman tidak tersedia untuk lokasi ini. Silakan hubungi CS.';
                            return;
                        }

                        if (data.total_weight) {
                            this.totalWeight = data.total_weight;
                        }

                        if (data.data && data.data[0] && data.data[0].costs && data.data[0].costs.length > 0) {
                            this.shippingOptions = data.data[0].costs;
                            this.applySorting();
                            this.shippingCostCache[cacheKey] = this.shippingOptions;
                        } else {
                            this.shippingOptions = [];
                            this.shippingError = 'Layanan pengiriman tidak ditemukan untuk rute ini. Coba kurir lain atau hubungi CS.';
                        }
                    } catch (e) {
                        console.error(e);
                        this.shippingOptions = [];
                        this.selectedShippingIndex = '';
                        this.shippingError = 'Gagal terhubung ke server ongkir. Periksa koneksi internet Anda.';
                    } finally {
                        this.shippingLoading = false;
                    }
                },
                
                async submitOrder() {
                    this.processing = true;
                    this.errors = {};
                    
                    // Prepare Payload
                    // If shipDifferent is false, we need to send billing address as shipping address
                    // But backend expects 'shipping_address' array.
                    // We need to construct it carefully.
                    
                    let shippingPayload = {};
                    
                    if (this.shipDifferent) {
                        shippingPayload = { ...this.shippingForm };
                    } else {
                        // Use billing data (from server-side rendered values ideally, but we have limited access here)
                        // Actually, backend should probably handle "use_billing" flag, but current controller expects full address.
                        // We can reconstruct it from blade variables passed to JS or DOM.
                        // Ideally we pass full user profile to JS bootstrap.
                        // For now, let's assume the backend controller handles it OR we pass what we have.
                        // Since billing fields are readonly, user can't change them.
                        // We need to pass them.
                        // Let's add full user profile to bootstrap.
                    }
                    
                    // Wait, we need the billing address values to send if shipDifferent is false.
                    // I'll update bootstrap to include them.
                    
                    // Payload construction
                    const payload = {
                        ship_different: this.shipDifferent,
                        shipping_address: this.shipDifferent ? this.shippingForm : window.checkoutBootstrap.billingAddress,
                        shipping_service: {
                            courier: this.selectedCourier,
                            service: this.shippingOptions[this.selectedShippingIndex].service,
                            cost: this.shippingCost
                        },
                        payment_method: this.paymentMethod,
                        notes: this.notes,
                        use_insurance: this.insuranceSettings.active // Mandatory if active
                    };

                    try {
                        const res = await fetch(window.checkoutBootstrap.routes.processOrder, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        const data = await res.json();
                        
                        if (data.success) {
                            window.location.href = data.redirect_url;
                        } else {
                            if (res.status === 422) {
                                this.errors = data.errors || {};
                                alert('Mohon periksa kembali inputan Anda.');
                            } else {
                                alert(data.message || 'Terjadi kesalahan saat memproses pesanan.');
                            }
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan jaringan.');
                    } finally {
                        this.processing = false;
                    }
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
