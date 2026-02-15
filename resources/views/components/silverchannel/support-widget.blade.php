<div x-data="supportWidget()" 
     x-cloak
     class="fixed bottom-6 right-6 z-50 flex flex-col items-end space-y-4"
     style="display: none;"
     x-show="visible"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

    <!-- Chat Window (Balloon Popup) -->
    <div x-show="isOpen" 
         @click.away="isOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 origin-bottom-right"
         x-transition:enter-end="opacity-100 scale-100 origin-bottom-right"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 origin-bottom-right"
         x-transition:leave-end="opacity-0 scale-95 origin-bottom-right"
         class="bg-white dark:bg-gray-800 w-80 md:w-96 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col mb-4">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 p-4 flex justify-between items-center text-white">
            @php
                $storeAdmin = \App\Models\User::role('SUPER_ADMIN')->orderBy('id')->first();
                $adminPhoto = $storeAdmin && !empty($storeAdmin->profile_picture) ? asset('storage/' . $storeAdmin->profile_picture) : null;
            @endphp
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-white/30 bg-white/20 flex items-center justify-center">
                        @if($adminPhoto)
                            <img src="{{ $adminPhoto }}" alt="Customer Service" class="w-full h-full object-cover">
                        @else
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        @endif
                    </div>
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-blue-600 rounded-full"></span>
                </div>
                <div>
                    <h4 class="font-bold text-sm">Customer Service</h4>
                    <p class="text-xs text-blue-100 opacity-90">Online • Membalas cepat</p>
                </div>
            </div>
            <button @click="isOpen = false" class="text-white/80 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-4 bg-gray-50 dark:bg-gray-900 min-h-[200px] max-h-[300px] overflow-y-auto">
            
            <!-- Loading State -->
            <div x-show="loading" class="flex justify-center py-4">
                <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>

            <!-- Content -->
            <div x-show="!loading">
                <template x-if="latest">
                    <div class="space-y-3">
                        <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300" x-text="'Order #' + latest.order_number"></span>
                                <span class="text-[10px] text-gray-400" x-text="latest.last_message_at"></span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2" x-text="latest.last_message || 'Lampiran'"></p>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('silverchannel.support.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua percakapan</a>
                        </div>
                    </div>
                </template>

                <template x-if="!latest">
                    <div class="text-center py-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Halo! Ada yang bisa kami bantu dengan pesanan Anda?</p>
                        <a href="{{ route('silverchannel.support.index') }}" class="inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition shadow-sm">
                            Mulai Chat
                        </a>
                    </div>
                </template>

                <!-- WhatsApp CS -->
                @php
                    // Ambil nomor dari profil store admin (SUPER_ADMIN)
                    $storeAdmin = \App\Models\User::role('SUPER_ADMIN')->orderBy('id')->first();
                    $waNumber = $storeAdmin && !empty($storeAdmin->phone) ? $storeAdmin->phone : env('WHATSAPP_CS_NUMBER', '+6281234567890');
                    $waHours  = app(\App\Services\IntegrationService::class)->get('whatsapp_cs_hours', 'Senin–Jumat 08:00–17:00');
                    $waMessage = urlencode('Halo, saya butuh bantuan mengenai [produk/layanan]');
                    $waDeeplink = "https://wa.me/" . preg_replace('/\\D/', '', ltrim($waNumber, '+')) . "?text=" . $waMessage;
                @endphp
                <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-3">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold" style="background-color:#E8F8EE;color:#0B7A43;">
                            Customer Service WhatsApp
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Jam Operasional: {{ $waHours }}</span>
                    </div>
                    <button type="button"
                            @click="openWhatsApp('{{ $waNumber }}', '{{ $waDeeplink }}')"
                            aria-label="Hubungi via WhatsApp"
                            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                            style="background-color:#25D366;"
                            @mouseenter="trackWhatsAppHover()"
                            @focus="trackWhatsAppHover()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        Hubungi via WhatsApp
                    </button>
                    <div x-show="waFallbackVisible" class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                        WhatsApp tidak tersedia. Silakan hubungi nomor: 
                        <span class="font-mono font-semibold">{{ $waNumber }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer / Input Mockup -->
        <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('silverchannel.support.index') }}" class="block w-full text-left text-sm text-gray-400 bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                Tulis pesan...
            </a>
        </div>
    </div>

    <!-- Toggle Button (Bubble) -->
    <button @click="isOpen = !isOpen" 
            class="group relative w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center focus:outline-none focus:ring-4 focus:ring-blue-300">
        
        <!-- Icon: Chat -->
        <svg x-show="!isOpen" class="w-7 h-7 transform transition duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        
        <!-- Icon: Close -->
        <svg x-show="isOpen" class="w-6 h-6 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>

        <!-- Notification Badge -->
        <template x-if="unreadCount > 0 && !isOpen">
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white dark:border-gray-800 animate-bounce" x-text="unreadCount"></span>
        </template>
    </button>
</div>

<script>
    function supportWidget() {
        return {
            visible: false,
            isOpen: false,
            loading: false,
            latest: null,
            unreadCount: 0,
            waFallbackVisible: false,
            
            init() {
                // Hide balloon if on support page
                if (window.location.pathname.startsWith('/silverchannel/support')) {
                    this.visible = false;
                    return;
                }

                // Show balloon after 5 seconds
                setTimeout(() => {
                    this.visible = true;
                }, 5000);

                // Fetch data
                this.fetchSummary();

                // Poll occasionally
                setInterval(() => this.fetchSummary(), 30000);
            },

            fetchSummary() {
                // We use the conversations endpoint with page=1 to get summary
                // Or create a specific endpoint. Using existing one for efficiency.
                fetch("{{ route('silverchannel.support.conversations') }}")
                    .then(res => res.json())
                    .then(data => {
                        if (data.data && data.data.length > 0) {
                            this.latest = data.data[0];
                            // Calculate total unread
                            this.unreadCount = data.data.reduce((sum, item) => sum + item.unread_count, 0);
                        } else {
                            this.latest = null;
                            this.unreadCount = 0;
                        }
                    })
                    .catch(() => {});
            },

            isMobile() {
                return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
            },

            trackWhatsAppClick(payload = {}) {
                try {
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        event: 'whatsapp_cs_click',
                        device: this.isMobile() ? 'mobile' : 'desktop',
                        path: window.location.pathname,
                        ...payload
                    });
                } catch (_) {}
                try {
                    if (typeof fbq === 'function') {
                        fbq('trackCustom', 'WhatsAppCSClick', {
                            device: this.isMobile() ? 'mobile' : 'desktop',
                            path: window.location.pathname,
                            ...payload
                        });
                    }
                } catch (_) {}
            },

            trackWhatsAppHover() {
                // Lightweight hover tracking; do not spam analytics
            },

            openWhatsApp(number, deeplink) {
                this.waFallbackVisible = false;
                if (!navigator.onLine) {
                    this.waFallbackVisible = true;
                    return;
                }
                this.trackWhatsAppClick({ number });
                if (this.isMobile()) {
                    const appLink = 'whatsapp://send?phone=' + number.replace(/\D/g, '') + '&text=' + encodeURIComponent('Halo, saya butuh bantuan mengenai [produk/layanan]');
                    let opened = false;
                    const timer = setTimeout(() => {
                        if (!opened) {
                            window.open(deeplink, '_blank');
                        }
                    }, 500);
                    try {
                        const win = window.open(appLink, '_blank');
                        opened = !!win;
                        clearTimeout(timer);
                    } catch (_) {
                        clearTimeout(timer);
                        window.open(deeplink, '_blank');
                    }
                } else {
                    window.open(deeplink, '_blank');
                }
            }
        }
    }
</script>
