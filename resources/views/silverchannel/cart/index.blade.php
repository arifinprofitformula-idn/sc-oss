<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Keranjang Belanja') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="cartPage">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart List -->
                <div class="flex-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            
                            <!-- Loading State -->
                            <div x-show="isLoading" class="flex justify-center py-12" style="display: none;">
                                <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <!-- Empty State -->
                            <div x-show="!isLoading && cartItems.length === 0" class="text-center py-12" style="display: none;">
                                <div class="mb-4">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Keranjang Anda Kosong</h3>
                                <p class="mt-1 text-gray-500">Belum ada produk yang ditambahkan.</p>
                                <div class="mt-6">
                                    <a href="{{ route('silverchannel.products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Mulai Belanja
                                    </a>
                                </div>
                            </div>

                            <!-- Cart Items -->
                            <div x-show="!isLoading && cartItems.length > 0">
                                <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="(item, index) in cartItems" :key="item.id">
                                        <li class="flex py-6">
                                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
                                                <img :src="item.product.image_url || 'https://placehold.co/100'" :alt="item.product.name" class="h-full w-full object-cover object-center">
                                            </div>

                                            <div class="ml-4 flex flex-1 flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-gray-900 dark:text-gray-100">
                                                        <h3>
                                                            <a :href="'#'" x-text="item.product.name"></a>
                                                        </h3>
                                                        <p class="ml-4" x-text="formatMoney(item.price_final * item.quantity)"></p>
                                                    </div>
                                                    <p class="mt-1 text-sm text-gray-500" x-text="item.product.sku"></p>
                                                    <p class="mt-1 text-xs text-red-500" x-show="item.error" x-text="item.error"></p>
                                                </div>
                                                <div class="flex flex-1 items-end justify-between text-sm">
                                                    <div class="flex items-center border border-gray-300 rounded dark:border-gray-600">
                                                        <button @click="updateQuantity(item.id, item.quantity - 1)" class="px-2 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50" :disabled="item.quantity <= 1">-</button>
                                                        <input type="text" class="w-12 text-center border-none p-1 text-gray-900 dark:text-gray-100 bg-transparent focus:ring-0" :value="item.quantity" readonly>
                                                        <button @click="updateQuantity(item.id, item.quantity + 1)" class="px-2 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">+</button>
                                                    </div>

                                                    <div class="flex">
                                                        <button @click="removeItem(item.id)" type="button" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">Hapus</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-96" x-show="!isLoading && cartItems.length > 0" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 sticky top-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Ringkasan Pesanan</h2>
                        
                        <div class="flow-root">
                            <dl class="-my-4 divide-y divide-gray-200 dark:divide-gray-700">
                                <div class="flex items-center justify-between py-4">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Total Item</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="totalItems + ' item'"></dd>
                                </div>
                                <div class="flex items-center justify-between py-4">
                                    <dt class="text-base font-medium text-gray-900 dark:text-gray-100">Subtotal</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-gray-100" x-text="formatMoney(subtotal)"></dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-6">
                            <button @click="proceedToCheckout" :disabled="isValidating" class="w-full flex justify-center items-center rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!isValidating">Pembayaran</span>
                                <svg x-show="isValidating" class="animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                            <p class="mt-4 text-center text-sm text-gray-500">
                                atau <a href="{{ route('silverchannel.products.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Lanjut Belanja<span aria-hidden="true"> &rarr;</span></a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @php
        $cartBootstrapData = $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'image_url' => $item->product->image_url,
                    'price_silverchannel' => $item->product->price_silverchannel,
                    'price_final' => $item->product->price_final,
                ],
                'price_final' => $item->price_final,
                'error' => null,
            ];
        })->toArray();
    @endphp
    <template id="cart-bootstrap-data">@json(['items' => $cartBootstrapData])</template>
    <script>
        // Bootstrap data diletakkan di <template> agar directive Blade tidak di-parse sebagai JavaScript.
        const cartBootstrapEl = document.getElementById('cart-bootstrap-data');
        let cartBootstrap = { items: [] };
        try {
            const raw = cartBootstrapEl ? (cartBootstrapEl.innerHTML || '').trim() : '';
            cartBootstrap = raw ? JSON.parse(raw) : cartBootstrap;
        } catch (e) {
            cartBootstrap = { items: [] };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('cartPage', () => ({
                cartItems: cartBootstrap.items || [],
                isLoading: false,
                isValidating: false,
                baseCartUrl: "{{ url('/silverchannel/cart') }}",
                csrfToken: '',

                init() {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    this.csrfToken = csrfMeta ? (csrfMeta.getAttribute('content') || '') : '';
                },

                get totalItems() {
                    return this.cartItems.reduce((acc, item) => acc + item.quantity, 0);
                },

                get subtotal() {
                    return this.cartItems.reduce((acc, item) => acc + (item.price_final * item.quantity), 0);
                },

                formatMoney(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
                },

                async updateQuantity(cartId, newQuantity) {
                    if (newQuantity < 1) return;

                    const itemIndex = this.cartItems.findIndex(i => i.id === cartId);
                    if (itemIndex === -1) return;

                    // Optimistic update
                    const oldQuantity = this.cartItems[itemIndex].quantity;
                    this.cartItems[itemIndex].quantity = newQuantity;
                    this.cartItems[itemIndex].error = null;

                    try {
                        const response = await fetch(`${this.baseCartUrl}/${cartId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                _method: 'PATCH',
                                quantity: newQuantity
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            // Revert on error
                            this.cartItems[itemIndex].quantity = oldQuantity;
                            this.cartItems[itemIndex].error = data.message || 'Gagal update stok';
                            alert(data.message || 'Gagal update stok');
                        }
                    } catch (error) {
                        this.cartItems[itemIndex].quantity = oldQuantity;
                        console.error('Error:', error);
                    }
                },

                async removeItem(cartId) {
                    if (!confirm('Hapus produk ini dari keranjang?')) return;

                    try {
                        const response = await fetch(`${this.baseCartUrl}/${cartId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                _method: 'DELETE'
                            })
                        });

                        if (response.ok) {
                            this.cartItems = this.cartItems.filter(item => item.id !== cartId);
                        } else {
                            alert('Gagal menghapus item');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },

                async proceedToCheckout() {
                    this.isValidating = true;
                    try {
                        const response = await fetch('{{ route("silverchannel.cart.validate") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert(data.message);
                            // Optionally reload to reflect stock changes if any
                            if(data.message.includes('Stok')) {
                                location.reload();
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memproses checkout.');
                    } finally {
                        this.isValidating = false;
                    }
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
