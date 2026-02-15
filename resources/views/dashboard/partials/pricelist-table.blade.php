<div class="overflow-x-auto relative shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
        <thead class="text-xs uppercase bg-gradient-to-r from-cyan-500 to-blue-600 text-white">
            <tr>
                <th scope="col" class="py-2 px-3 sm:py-3 sm:px-6">Gambar Produk</th>
                <th scope="col" class="py-2 px-3 sm:py-3 sm:px-6 cursor-pointer hover:bg-blue-700/30 transition-colors" @click="sortBy('name')">
                    <div class="flex items-center">
                        Nama Produk
                        <span x-show="sortField === 'name'" class="ml-1">
                            <template x-if="sortDirection === 'asc'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </template>
                            <template x-if="sortDirection === 'desc'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </template>
                        </span>
                    </div>
                </th>
                <th scope="col" class="py-2 px-3 sm:py-3 sm:px-6 cursor-pointer hover:bg-blue-700/30 transition-colors" @click="sortBy('price_silverchannel')">
                    <div class="flex items-center">
                        Harga Silverchannel
                        <span x-show="sortField === 'price_silverchannel'" class="ml-1">
                            <template x-if="sortDirection === 'asc'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </template>
                            <template x-if="sortDirection === 'desc'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </template>
                        </span>
                    </div>
                </th>
                <th scope="col" class="py-2 px-3 sm:py-3 sm:px-6 cursor-pointer hover:bg-blue-700/30 transition-colors" @click="sortBy('price_customer')">
                    <div class="flex items-center">
                        Harga Konsumen
                        <span x-show="sortField === 'price_customer'" class="ml-1">
                            <template x-if="sortDirection === 'asc'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </template>
                            <template x-if="sortDirection === 'desc'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </template>
                        </span>
                    </div>
                </th>
                <th scope="col" class="py-2 px-3 sm:py-3 sm:px-6">Tombol Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors border-l-4 border-blue-500">
                    <td class="py-2 px-3 sm:py-4 sm:px-6">
                        <div class="w-12 h-12 bg-white rounded flex items-center justify-center overflow-hidden ring-1 ring-blue-100">
                            <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/placeholder.png') }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-contain"
                                 loading="lazy"
                                data-placeholder="{{ asset('images/placeholder.png') }}"
                                onerror="this.src=this.dataset.placeholder;">
                        </div>
                    </td>
                    <td class="py-2 px-3 sm:py-4 sm:px-6 font-medium text-gray-900 dark:text-white">
                        <div class="line-clamp-2" title="{{ $product->name }}">{{ $product->name }}</div>
                        <div class="text-xs mt-1 inline-flex items-center px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">{{ $product->sku }}</div>
                    </td>
                    <td class="py-2 px-3 sm:py-4 sm:px-6">
                        <div class="flex items-center">
                            <span class="font-bold text-blue-600">Rp {{ number_format($product->price_silverchannel, 0, ',', '.') }}</span>
                            @if($product->price_trend === 'up')
                                <svg class="w-4 h-4 text-red-500 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            @elseif($product->price_trend === 'down')
                                <svg class="w-4 h-4 text-green-500 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                            @endif
                        </div>
                    </td>
                    <td class="py-2 px-3 sm:py-4 sm:px-6">
                        <div class="text-gray-900">Rp {{ number_format($product->price_customer, 0, ',', '.') }}</div>
                    </td>
                    <td class="py-2 px-3 sm:py-4 sm:px-6">
                        <div x-data="{
                            qty: 1,
                            maxStock: {{ (int) $product->stock }},
                            productId: {{ $product->id }},
                            csrfToken: '',
                            loading: false,
                            added: false,
                            error: '',
                            init() {
                                const m = document.querySelector('meta[name=csrf-token]');
                                this.csrfToken = m ? (m.getAttribute('content') || '') : '';
                            },
                            validate() {
                                if (!this.qty || this.qty < 1) this.qty = 1;
                                if (this.qty > this.maxStock) this.qty = this.maxStock;
                            },
                            async handleAddToCart() {
                                this.validate();
                                if (this.maxStock <= 0) {
                                    this.error = 'Stok tidak tersedia';
                                    return;
                                }
                                if (this.loading) return;
                                this.loading = true;
                                const payload = { product_id: this.productId, quantity: this.qty };
                                try {
                                    const res = await fetch('{{ route('silverchannel.cart.store') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.csrfToken,
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify(payload)
                                    });
                                    const ok = res.ok;
                                    const data = await res.json().catch(() => ({}));
                                    if (!ok) {
                                        this.error = data.message || 'Gagal menambahkan ke keranjang';
                                        return;
                                    }
                                    this.added = true;
                                    this.error = '';
                                    if (data.item && Alpine.store('cart')) {
                                        Alpine.store('cart').add(data.item);
                                    }
                                    if (Alpine.store('toast')) {
                                        Alpine.store('toast').show('Produk berhasil ditambahkan ke keranjang', 'success', { duration: 2000 });
                                    }
                                } catch (e) {
                                    this.error = 'Terjadi kesalahan jaringan';
                                } finally {
                                    this.loading = false;
                                }
                            }
                        }" class="flex items-center gap-2">
                            <input type="number" min="1" :max="maxStock" x-model.number="qty" @change="validate()" class="w-16 sm:w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-xs sm:text-sm">
                            <button type="button"
                                    @click="handleAddToCart()"
                                    :disabled="loading"
                                    :class="{
                                        'btn-3d-blue': !added && !loading,
                                        'btn-3d-green': added && !loading,
                                        'bg-gray-400 cursor-not-allowed': loading
                                    }"
                                    class="btn-3d shimmer flex-1 inline-flex justify-center items-center px-2 py-1 text-xs sm:text-sm font-semibold text-white rounded-md border border-transparent focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 gap-1"
                                    aria-label="Tambah ke keranjang">
                                <svg x-show="!added && !loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-show="!added && !loading" class="hidden lg:inline">Add to Cart</span>
                                <span x-show="loading" class="hidden lg:inline">Menambahkan...</span>
                                <svg x-show="added && !loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span x-show="added && !loading" class="hidden lg:inline">Ditambahkan</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 px-6 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414a1 1 0 00-.707-.293H4"></path></svg>
                            <p>Data produk tidak ditemukan.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="p-4 bg-white dark:bg-gray-800 border-t dark:border-gray-700">
        {{ $products->links() }}
    </div>
</div>
