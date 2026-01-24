<div
    x-data
    x-show="$store.cart.isOpen"
    class="relative z-50"
    aria-labelledby="slide-over-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
    x-cloak
>
    <!-- Background backdrop -->
    <div
        x-show="$store.cart.isOpen"
        x-transition:enter="ease-in-out duration-500"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"
        @click="$store.cart.close()"
    ></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <!-- Slide-over panel -->
                <div
                    x-show="$store.cart.isOpen"
                    x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="pointer-events-auto w-screen max-w-md"
                >
                    <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                        <!-- Header -->
                        <div class="flex items-start justify-between px-4 py-6 sm:px-6 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide" id="slide-over-title">KERANJANG BELANJA</h2>
                            <div class="ml-3 flex h-7 items-center">
                                <button type="button" class="relative -m-2 p-2 text-gray-400 hover:text-gray-500 transition-colors" @click="$store.cart.close()">
                                    <span class="absolute -inset-0.5"></span>
                                    <span class="sr-only">Close panel</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 relative">
                            <!-- Loading Spinner -->
                            <div x-show="$store.cart.loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10" style="display: none;">
                                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <template x-if="!$store.cart.loading && $store.cart.items.length === 0">
                                <div class="flex flex-col items-center justify-center h-full text-center">
                                    <div class="bg-gray-100 rounded-full p-6 mb-4">
                                        <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">Keranjang Kosong</h3>
                                    <p class="mt-2 text-sm text-gray-500 max-w-xs mx-auto">Sepertinya Anda belum menambahkan produk apapun ke keranjang belanja Anda.</p>
                                    <button @click="$store.cart.close()" class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Mulai Belanja
                                    </button>
                                </div>
                            </template>

                            <ul role="list" class="-my-6 divide-y divide-gray-200" x-show="$store.cart.items.length > 0">
                                <template x-for="item in $store.cart.items" :key="item.id">
                                    <li class="flex py-6">
                                        <!-- Image & Remove Button -->
                                        <div class="relative h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                            <img :src="item.image" :alt="item.name" class="h-full w-full object-cover object-center">
                                            <button 
                                                @click="$store.cart.remove(item.id)"
                                                class="absolute top-1 left-1 bg-white bg-opacity-80 text-red-500 rounded-full p-0.5 hover:bg-red-50 hover:text-red-600 transition-colors shadow-sm"
                                                title="Hapus item"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="ml-4 flex flex-1 flex-col">
                                            <div>
                                                <div class="flex justify-between items-start">
                                                    <h3 class="text-base font-semibold text-gray-900 line-clamp-2" x-text="item.name"></h3>
                                                    
                                                    <!-- Quantity Controls -->
                                                    <div class="flex items-center border border-gray-300 rounded-md ml-2 shadow-sm bg-white">
                                                        <button @click="$store.cart.updateQuantity(item.id, -1)" class="px-2 py-1 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors rounded-l-md">
                                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                            </svg>
                                                        </button>
                                                        <span x-text="item.quantity" class="px-2 py-1 text-gray-900 text-sm font-medium border-l border-r border-gray-300 min-w-[2rem] text-center bg-gray-50"></span>
                                                        <button @click="$store.cart.updateQuantity(item.id, 1)" class="px-2 py-1 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors rounded-r-md">
                                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-1 flex items-center text-sm text-gray-500">
                                                    <span x-text="item.quantity"></span>
                                                    <span class="mx-1">×</span>
                                                    <span x-text="$store.cart.formatPrice(item.price)"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Footer -->
                        <div class="border-t border-gray-200 px-4 py-6 sm:px-6 bg-gray-50" x-show="$store.cart.items.length > 0">
                            <div class="flex justify-between text-base font-bold text-gray-900 mb-4">
                                <p>Subtotal:</p>
                                <p x-text="$store.cart.formatPrice($store.cart.subtotal)"></p>
                            </div>
                            <div class="mt-6">
                                <!-- Saat toko TUTUP, tombol menggunakan gradasi GREY dan dinonaktifkan -->
                                <button 
                                    type="button"
                                    @click="
                                        if ($store.storeStatus && !$store.storeStatus.canAddToCart) {
                                            if (Alpine.store('toast')) {
                                                Alpine.store('toast').show('Toko sedang tutup. Transaksi tidak bisa dilakukan.', 'error', { duration: 3000 });
                                            } else {
                                                alert('Toko sedang tutup. Transaksi tidak bisa dilakukan.');
                                            }
                                            return;
                                        }

                                        if ($store.cart.items.length === 0) {
                                            alert('Keranjang belanja Anda kosong.');
                                            return;
                                        }

                                        try {
                                            $store.cart.loading = true;

                                            if (typeof gtag === 'function') {
                                                gtag('event', 'begin_checkout', {
                                                    'items': $store.cart.items,
                                                    'value': $store.cart.subtotal,
                                                    'currency': 'IDR'
                                                });
                                            } else {
                                                console.log('Track: begin_checkout', {
                                                    items: $store.cart.items,
                                                    value: $store.cart.subtotal
                                                });
                                            }

                                            window.location.href = '{{ route('silverchannel.checkout.index') }}';
                                        } catch (e) {
                                            console.error(e);
                                            alert('Gagal mengalihkan ke halaman pembayaran. Silakan coba lagi.');
                                            $store.cart.loading = false;
                                        }
                                    "
                                    class="flex w-full items-center justify-center rounded-md border border-transparent px-6 py-3 text-base font-bold text-white shadow-sm focus:outline-none transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="($store.storeStatus && !$store.storeStatus.canAddToCart) 
                                        ? 'cursor-not-allowed opacity-80' 
                                        : 'bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2'"
                                    :style="($store.storeStatus && !$store.storeStatus.canAddToCart) 
                                        ? 'background-image: linear-gradient(to right, #808080, #666666);' 
                                        : ''"
                                    :title="($store.storeStatus && !$store.storeStatus.canAddToCart) ? 'Toko sedang tutup. Transaksi tidak bisa dilakukan.' : ''"
                                    :disabled="$store.cart.loading || $store.cart.items.length === 0 || ($store.storeStatus && !$store.storeStatus.canAddToCart)"
                                >
                                    <span x-show="!$store.cart.loading && !($store.storeStatus && !$store.storeStatus.canAddToCart)" class="flex items-center">
                                        LANJUTKAN ORDER
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                        </svg>
                                    </span>
                                    <span x-show="!$store.cart.loading && ($store.storeStatus && !$store.storeStatus.canAddToCart)" class="flex items-center">
                                        TOKO TUTUP
                                    </span>
                                    <span x-show="$store.cart.loading" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memuat...
                                    </span>
                                </button>
                            </div>
                            <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
                                <p>
                                    atau
                                    <button type="button" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors" @click="$store.cart.close()">
                                        Lanjut Belanja
                                        <span aria-hidden="true"> &rarr;</span>
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
