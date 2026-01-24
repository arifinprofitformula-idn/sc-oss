<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Product Catalog') }}
        </h2>
    </x-slot>

    @php
        $status = $operationalStatus ?? null;
    @endphp

    <script type="application/json" id="store-operational-status">{!! json_encode($status, JSON_UNESCAPED_UNICODE) !!}</script>

    <div class="py-12" x-data>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search & Filter -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form action="{{ route('silverchannel.products.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <x-text-input name="search" class="w-full" placeholder="Search product..." value="{{ request('search') }}" />
                    </div>
                    <div>
                        <x-primary-button>{{ __('Search') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <!-- Operational Status -->
            <div class="mb-6 space-y-4" x-data>
                <template x-if="$store.storeStatus.error">
                    <div class="bg-red-50 border border-red-200 text-red-800 text-xs px-3 py-2 rounded" x-text="$store.storeStatus.error"></div>
                </template>

                <template x-if="$store.storeStatus.loaded && !$store.storeStatus.isOpen && !$store.storeStatus.error">
                    <div>
                        <div class="mt-3 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-gray-200 dark:border-gray-700"
                             x-show="true" x-transition>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    Jam Operasional Toko
                                </h3>
                                <span class="text-xs px-2 py-1 rounded-full"
                                      :class="$store.storeStatus.isOpen
                                        ? 'bg-green-100 text-green-800 border border-green-200'
                                        : 'bg-red-100 text-red-800 border border-red-200'">
                                    <span x-text="$store.storeStatus.isOpen ? 'BUKA' : 'TUTUP'"></span>
                                </span>
                            </div>

                            <div class="mb-2 text-xs font-semibold text-red-700">TOKO TUTUP HARI MINGGU/LIBUR NASIONAL</div>

                            <div class="space-y-1 text-xs text-gray-800 dark:text-gray-200">
                                <template x-for="line in $store.storeStatus.simplifiedScheduleLines()" :key="line">
                                    <div x-text="line"></div>
                                </template>
                            </div>

                        </div>
                    </div>
                </template>

                <template x-if="!$store.storeStatus.loaded && !$store.storeStatus.error">
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs px-3 py-2 rounded">
                        Jadwal operasional belum tersedia. Silakan coba beberapa saat lagi.
                    </div>
                </template>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex flex-col">
                        <div class="aspect-w-16 aspect-h-9 bg-gray-200 dark:bg-gray-700">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="object-cover w-full h-48">
                            @else
                                <div class="flex items-center justify-center h-48 text-gray-400">
                                    No Image
                                </div>
                            @endif
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $product->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $product->brand->name }} - {{ $product->category->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-2">{{ $product->description }}</p>
                            
                            <div class="mt-auto">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-lg font-bold text-blue-600">Rp {{ number_format($product->price_silverchannel, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-500 line-through">Rp {{ number_format($product->price_msrp, 0, ',', '.') }}</span>
                                </div>
                                
                                <div x-data="productCard({ productId: {{ $product->id }} })">
                                    <div class="flex gap-2">
                                        <x-text-input type="number" x-model.number="qty" min="1" class="w-20" />
                                        <!-- Warna tombol dikendalikan oleh state toko: saat TUTUP gunakan gradasi GREY -->
                                        <x-primary-button 
                                            type="button"
                                            @click="handleAddToCart()"
                                            class="flex-1 justify-center transition-all duration-200"
                                            x-bind:disabled="!$store.storeStatus.canAddToCart"
                                            x-bind:title="!$store.storeStatus.canAddToCart ? 'Toko sedang tutup. Transaksi tidak bisa dilakukan.' : ''"
                                            x-bind:class="$store.storeStatus.canAddToCart 
                                                ? 'bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white'
                                                : 'text-white cursor-not-allowed opacity-80'"
                                            x-bind:style="!$store.storeStatus.canAddToCart 
                                                ? 'background-image: linear-gradient(to right, #808080, #666666);' 
                                                : ''"
                                        >
                                            <span x-show="$store.storeStatus.canAddToCart">{{ __('Add to Cart') }}</span>
                                            <span x-show="!$store.storeStatus.canAddToCart">TOKO TUTUP</span>
                                        </x-primary-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            let initial = null;
            try {
                const el = document.getElementById('store-operational-status');
                if (el && el.textContent.trim() !== '') {
                    initial = JSON.parse(el.textContent);
                }
            } catch (e) {
                console.error('Failed to parse operational status', e);
            }

            Alpine.store('storeStatus', {
                loaded: !!initial,
                status: initial && initial.status ? initial.status : 'OPEN',
                isOpen: initial && initial.status ? (initial.status === 'OPEN') : true,
                canAddToCart: initial ? !!initial.can_add_to_cart : true,
                schedule: initial && initial.schedule ? initial.schedule : {},
                error: '',

                formatDay(key) {
                    const map = {
                        monday: 'Senin',
                        tuesday: 'Selasa',
                        wednesday: 'Rabu',
                        thursday: 'Kamis',
                        friday: 'Jumat',
                        saturday: 'Sabtu',
                        sunday: 'Minggu',
                    };
                    return map[key] || key;
                },

                formatTime(t) {
                    if (!t || typeof t !== 'string') return '';
                    return t.replace(':', '.');
                },

                simplifiedScheduleLines() {
                    const days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                    const s = this.schedule || {};

                    const isOpenDay = (d) => {
                        const cfg = s[d];
                        return cfg && !cfg.is_closed && cfg.open && cfg.close;
                    };
                    const timeKey = (d) => {
                        const cfg = s[d];
                        if (!cfg) return null;
                        return `${cfg.open}|${cfg.close}`;
                    };

                    const groups = [];
                    let current = null;
                    for (let i = 0; i < days.length; i++) {
                        const d = days[i];
                        if (isOpenDay(d)) {
                            const tk = timeKey(d);
                            if (!current) {
                                current = { start: i, end: i, tk };
                            } else if (current.tk === tk && current.end === i - 1) {
                                current.end = i;
                            } else {
                                groups.push(current);
                                current = { start: i, end: i, tk };
                            }
                        } else {
                            if (current) {
                                groups.push(current);
                                current = null;
                            }
                        }
                    }
                    if (current) groups.push(current);

                    const lines = [];
                    for (const g of groups) {
                        const startDay = days[g.start];
                        const endDay = days[g.end];
                        const cfg = s[startDay];
                        const open = this.formatTime(cfg.open);
                        const close = this.formatTime(cfg.close);
                        if (g.start === g.end) {
                            lines.push(`${this.formatDay(startDay)} : ${open} - ${close} WIB`);
                        } else {
                            lines.push(`${this.formatDay(startDay)} s.d. ${this.formatDay(endDay)} : ${open} - ${close} WIB`);
                        }
                    }
                    return lines;
                },

                async refresh() {
                    try {
                        const res = await fetch('{{ route('silverchannel.store.operational-status') }}', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (!res.ok) {
                            this.error = 'Gagal mengambil status toko. Silakan cek kembali beberapa saat lagi.';
                            return;
                        }
                        const data = await res.json();
                        if (!data || typeof data !== 'object') {
                            this.error = 'Data status toko tidak valid. Silakan coba beberapa saat lagi.';
                            return;
                        }

                        this.loaded = true;
                        this.status = data.status || 'OPEN';
                        this.isOpen = this.status === 'OPEN';
                        this.canAddToCart = !!data.can_add_to_cart;
                        this.schedule = data.schedule || {};
                        this.error = '';
                    } catch (e) {
                        console.error('Failed to refresh store status', e);
                        this.error = 'Terjadi kesalahan saat memperbarui status toko. Silakan coba lagi.';
                    }
                }
            });

            // Initial refresh and polling
            const store = Alpine.store('storeStatus');
            // We can call refresh to ensure fresh data, but we also have initial data injected
            // store.refresh(); 
            
            setInterval(() => {
                Alpine.store('storeStatus').refresh();
            }, 60000);
        });

        function productCard(props) {
            return {
                qty: 1,
                csrfToken: '',
                productId: props.productId,
                init() {
                    const m = document.querySelector('meta[name=csrf-token]');
                    this.csrfToken = m ? (m.getAttribute('content') || '') : '';
                },
                async handleAddToCart() {
                    if (!Alpine.store('storeStatus').canAddToCart) {
                        alert('Toko sedang tutup. Anda tidak dapat menambahkan produk ke keranjang saat ini.');
                        return;
                    }

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
                            alert(data.message || 'Gagal menambahkan ke keranjang');
                            return;
                        }

                        if (data.item && Alpine.store('cart')) {
                            Alpine.store('cart').add(data.item);
                        }
                        this.qty = 1;
                        Alpine.store('toast').show('Produk berhasil ditambahkan ke keranjang', 'success', {
                            duration: 2000,
                            onHide: () => {
                                if (Alpine.store('cart')) {
                                    Alpine.store('cart').open();
                                }
                            }
                        });
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan');
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
