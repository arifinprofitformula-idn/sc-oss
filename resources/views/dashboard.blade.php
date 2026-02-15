<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Custom CSS for 3D Buttons & Shine Effect -->
    <style>
        /* From Product Catalog Page */
        .button-shine { 
            position: relative; 
            transition: all 0.3s ease-in-out; 
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2); 
            padding-block: 0.5rem; 
            padding-inline: 1.25rem; 
            background: linear-gradient(to right, #06b6d4, #2563eb); /* cyan-500 to blue-600 */
            border-radius: 6px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #ffff; 
            gap: 10px; 
            font-weight: bold; 
            border: 3px solid #ffffff4d; 
            outline: none; 
            overflow: hidden; 
            font-size: 15px; 
            cursor: pointer; 
            text-transform: uppercase;
            letter-spacing: 0.05em;
        } 
        .button-shine .icon { 
            width: 24px; 
            height: 24px; 
            transition: all 0.3s ease-in-out; 
        } 
        .button-shine:hover { 
            transform: scale(1.05); 
            border-color: #fff9; 
        } 
        .button-shine:hover .icon { 
            transform: translate(4px); 
        } 
        .button-shine:hover::before { 
            animation: shine 1.5s ease-out infinite; 
        } 
        .button-shine::before { 
            content: ""; 
            position: absolute; 
            width: 100px; 
            height: 100%; 
            background-image: linear-gradient( 
                120deg, 
                rgba(255, 255, 255, 0) 30%, 
                rgba(255, 255, 255, 0.8), 
                rgba(255, 255, 255, 0) 70% 
            ); 
            top: 0; 
            left: -100px; 
            opacity: 0.6; 
        } 
        
        .button-shine.closed {
            background: linear-gradient(to right, #808080, #666666);
            cursor: not-allowed;
            opacity: 0.8;
        }
        
        @keyframes shine { 
            0% { left: -100px; } 
            60% { left: 100%; } 
            to { left: 100%; } 
        }

        .btn-3d {
            /* Custom properties for maintainability */
            --btn-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --btn-shadow-color: rgba(0, 0, 0, 0.2);
            --btn-highlight: rgba(255, 255, 255, 0.2);
            --btn-pulse-color: rgba(5, 186, 218, 0.4); /* Default cyan pulse */
            
            position: relative;
            overflow: hidden;
            transition: var(--btn-transition);
            box-shadow: 
                0 4px 6px -1px var(--btn-shadow-color),
                0 2px 4px -1px var(--btn-shadow-color),
                inset 0 1px 0 var(--btn-highlight);
            z-index: 1;
            background-size: 100% auto;
        }

        /* Gradient Overlay for Lighting Effect */
        .btn-3d::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: -1;
        }

        /* Hover State: Lift up + Deep Shadow + Pulse */
        .btn-3d:hover {
            transform: translateY(-2px);
            /* Combine 3D shadow with Pulse animation logic (handled in keyframes) */
            background-position: right center;
            background-size: 200% auto;
            animation: pulse512 1.5s infinite;
        }
        
        .btn-3d:hover::before {
            opacity: 1;
        }

        /* Active State: Press down */
        .btn-3d:active {
            transform: translateY(1px);
            box-shadow: 
                0 2px 4px -1px var(--btn-shadow-color),
                inset 0 2px 4px rgba(0,0,0,0.1);
            animation: none;
        }

        /* Gold Variant */
        .btn-3d-gold {
            background: linear-gradient(135deg, #EEA727 0%, #D97706 100%);
            --btn-pulse-color: rgba(238, 167, 39, 0.6);
        }

        /* Blue Variant */
        .btn-3d-blue {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
            --btn-pulse-color: rgba(37, 99, 235, 0.6);
        }

        /* Green Variant */
        .btn-3d-green {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            --btn-pulse-color: rgba(22, 163, 74, 0.6);
        }

        /* Red Variant */
        .btn-3d-red {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            --btn-pulse-color: rgba(220, 38, 38, 0.6);
        }

        /* Pulse Animation */
        @keyframes pulse512 {
            0% {
                box-shadow: 
                    0 10px 15px -3px var(--btn-shadow-color),
                    0 4px 6px -2px var(--btn-shadow-color),
                    inset 0 1px 0 var(--btn-highlight),
                    0 0 0 0 var(--btn-pulse-color);
            }
            70% {
                box-shadow: 
                    0 10px 15px -3px var(--btn-shadow-color),
                    0 4px 6px -2px var(--btn-shadow-color),
                    inset 0 1px 0 var(--btn-highlight),
                    0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 
                    0 10px 15px -3px var(--btn-shadow-color),
                    0 4px 6px -2px var(--btn-shadow-color),
                    inset 0 1px 0 var(--btn-highlight),
                    0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        /* Shimmer Animation */
        @keyframes shimmer {
            0% { transform: translateX(-100%) skewX(-15deg); }
            100% { transform: translateX(200%) skewX(-15deg); }
        }
        
        .shimmer {
            position: relative;
            overflow: hidden;
        }
        
        .shimmer::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-15deg);
            animation: shimmer 2s infinite;
            pointer-events: none;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @role('SILVERCHANNEL')
            <!-- Store Status & Shop CTA Card -->
            @if(isset($storeStatus))
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 border-l-4 {{ $storeStatus['is_open'] ? 'border-green-500' : 'border-red-500' }}">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Status Operasional Toko</h3>
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {{ $storeStatus['is_open'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $storeStatus['is_open'] ? 'BUKA' : 'TUTUP' }}
                                </span>
                            </div>
                            
                            @if(!$storeStatus['is_open'])
                                <p class="text-sm text-red-600 dark:text-red-400 font-medium mb-1">
                                    @if($storeStatus['reason'] === 'holiday_mode')
                                        Toko Sedang Libur
                                    @elseif($storeStatus['reason'] === 'store_closed_toggle')
                                        Toko Ditutup Sementara
                                    @elseif($storeStatus['reason'] === 'outside_hours')
                                        Diluar Jam Operasional (Buka kembali jam {{ $storeStatus['open_time'] ?? '-' }})
                                    @elseif($storeStatus['reason'] === 'closed_today')
                                        Tutup Hari Ini
                                    @else
                                        {{ $storeStatus['reason'] }}
                                    @endif
                                </p>
                            @else
                                <p class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">
                                    Kami siap memproses pesanan Anda sekarang.
                                </p>
                            @endif

                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <span class="font-semibold">Info Operasional:</span>
                                @php
                                    $dayMap = [
                                        'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 
                                        'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu', 'sunday' => 'Minggu'
                                    ];
                                    $todayKey = strtolower(\Carbon\Carbon::now()->format('l'));
                                    $todayLabel = $dayMap[$todayKey] ?? $todayKey;
                                    $schedule = $storeStatus['schedule'][$todayKey] ?? null;
                                @endphp
                                Hari ini ({{ $todayLabel }}): 
                                @if($schedule && !($schedule['is_closed'] ?? false))
                                    {{ $schedule['open'] ?? '09:00' }} - {{ $schedule['close'] ?? '17:00' }} WIB
                                @else
                                    Libur / Tutup
                                @endif
                            </p>
                        </div>

                        <div class="flex-shrink-0 w-full md:w-auto">
                            @if($storeStatus['is_open'])
                                <a href="{{ route('silverchannel.products.index') }}" class="button-shine w-full md:w-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    Belanja Sekarang
                                </a>
                            @else
                                <button disabled class="button-shine closed w-full md:w-auto" title="Toko sedang tutup">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    Toko Tutup
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Pricelist Update & Product Table -->
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="pricelistManager()">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <div x-data="lastUpdateStatus()">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Update Pricelist Product</h3>
                            <div class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <span>Last Update: </span>
                                <span class="ml-1 font-semibold" :class="textColor" x-text="text"></span>
                                <span class="ml-2 w-3 h-3 rounded-full animate-pulse" :class="colorClass"></span>
                                <span x-show="loading" class="ml-3 inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memuat...
                                </span>
                                <span x-show="error" class="ml-3 text-red-600" x-text="error"></span>
                            </div>
                        </div>
                        <div class="flex space-x-2 w-full md:w-auto">
                            <a href="{{ route('dashboard.pricelist.export') }}" class="btn-3d btn-3d-green w-full md:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l4 4a1 1 0 01.586 1.414V19a2 2 0 01-2 2z"></path></svg>
                                Export Excel
                            </a>
                            <a href="{{ route('dashboard.pricelist.export-pdf') }}" class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Export PDF
                            </a>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                        <div class="w-full md:w-1/3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" x-model.debounce.500ms="search" placeholder="Cari produk (Nama/SKU)..." class="pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors">
                            </div>
                        </div>
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <span x-show="loading" class="mr-2 flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Updating...
                            </span>
                            <div x-show="!loading && nextRefresh > 0" class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Refresh in <span x-text="nextRefresh" class="font-mono mx-1"></span>s
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="relative min-h-[200px]">
                            <div x-show="loading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center z-10 backdrop-blur-sm transition-all rounded-lg">
                            <div class="flex flex-col items-center">
                                <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="mt-2 text-sm text-gray-600 font-medium">Memuat data...</span>
                            </div>
                            </div>
                            <div x-html="htmlContent" @click.prevent="handlePagination($event)"></div>
                    </div>
                </div>
            </div>

            <script>
            function pricelistManager() {
                return {
                    htmlContent: '',
                    search: '',
                    sortField: 'name',
                    sortDirection: 'asc',
                    loading: false,
                    nextRefresh: 300,
                    refreshTimer: null,
                    countdownTimer: null,

                    async fetchPricelist(url = null) {
                        this.loading = true;
                        let endpoint = url || "{{ route('dashboard.pricelist') }}";
                        
                        const params = new URLSearchParams();
                        if (!url) {
                            if (this.search) params.append('search', this.search);
                            params.append('sort_field', this.sortField);
                            params.append('sort_direction', this.sortDirection);
                            endpoint += '?' + params.toString();
                        }

                        try {
                            const res = await fetch(endpoint, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (!res.ok) throw new Error('Network response was not ok');
                            this.htmlContent = await res.text();
                        } catch (error) {
                            console.error('Error fetching pricelist:', error);
                            this.htmlContent = '<div class="text-center text-red-500 p-8 border-2 border-dashed border-red-300 rounded-lg bg-red-50"><p class="font-bold">Gagal memuat data.</p><p class="text-sm mt-2">Silakan periksa koneksi internet Anda atau coba lagi nanti.</p><button @click="fetchPricelist()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Coba Lagi</button></div>';
                        } finally {
                            this.loading = false;
                            this.resetTimer();
                        }
                    },
                    
                    sortBy(field) {
                        if (this.sortField === field) {
                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortField = field;
                            this.sortDirection = 'asc';
                        }
                        this.fetchPricelist();
                    },

                    handlePagination(e) {
                        const link = e.target.closest('a');
                        // Ensure it's a pagination link (usually has page query param or is inside pagination nav)
                        if (link && link.href && !link.href.includes('#') && !link.target) {
                            this.fetchPricelist(link.href);
                        }
                    },
                    
                    resetTimer() {
                        this.nextRefresh = 300;
                    },

                    init() {
                        this.fetchPricelist();
                        
                        this.$watch('search', () => {
                            this.fetchPricelist();
                        });

                        this.countdownTimer = setInterval(() => {
                            if (this.nextRefresh > 0) {
                                this.nextRefresh--;
                            } else {
                                this.fetchPricelist();
                            }
                        }, 1000);
                    }
                }
            }
            function lastUpdateStatus() {
                return {
                    loading: true,
                    error: '',
                    text: 'Belum ada data',
                    colorClass: 'bg-red-500',
                    textColor: 'text-red-600',
                    async refresh() {
                        this.loading = true;
                        try {
                            const res = await fetch("{{ route('dashboard.pricelist.last-update') }}", { headers: { 'Accept': 'application/json' } });
                            if (!res.ok) throw new Error('Gagal memuat pembaruan');
                            const data = await res.json();
                            const ts = data.last_update ? new Date(data.last_update) : null;
                            if (!ts) {
                                this.text = 'Belum ada data';
                                this.colorClass = 'bg-red-500';
                                this.textColor = 'text-red-600';
                                this.error = '';
                                return;
                            }
                            const formatter = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false });
                            const parts = formatter.formatToParts(ts);
                            const get = (type) => parts.find(p => p.type === type)?.value || '';
                            const weekday = get('weekday');
                            const day = get('day');
                            const month = get('month');
                            const year = get('year');
                            const hour = get('hour');
                            const minute = get('minute');
                            this.text = `${weekday}, ${day} ${month} ${year} - Pkl. ${hour}.${minute} WIB`;
                            const diffMs = Date.now() - ts.getTime();
                            const diffHours = diffMs / (1000 * 60 * 60);
                            if (diffHours < 1) {
                                this.colorClass = 'bg-green-500';
                                this.textColor = 'text-green-600';
                            } else if (diffHours < 24) {
                                this.colorClass = 'bg-yellow-500';
                                this.textColor = 'text-yellow-600';
                            } else {
                                this.colorClass = 'bg-red-500';
                                this.textColor = 'text-red-600';
                            }
                            this.error = '';
                        } catch (e) {
                            this.error = 'Gagal memuat Last Update';
                        } finally {
                            this.loading = false;
                        }
                    },
                    init() {
                        this.refresh();
                        setInterval(() => this.refresh(), 60000);
                    }
                }
            }
            </script>

            <!-- Referral Card -->
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Link Referral Anda</h3>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div x-data="{
                            link: '{{ $referralLink ?? '' }}',
                            state: 'idle', 
                            message: '',
                            
                            copy() {
                                if (this.state === 'loading' || this.state === 'success') return;
                                
                                this.state = 'loading';
                                
                                // Small delay to ensure loading state is visible
                                setTimeout(() => this.executeCopy(), 300);
                            },
                            
                            async executeCopy() {
                                try {
                                    if (navigator.clipboard && navigator.clipboard.writeText) {
                                        await navigator.clipboard.writeText(this.link);
                                        this.handleSuccess();
                                    } else {
                                        throw new Error('Clipboard API unavailable');
                                    }
                                } catch (err) {
                                    this.fallbackCopy();
                                }
                            },
                            
                            fallbackCopy() {
                                try {
                                    const textArea = document.createElement('textarea');
                                    textArea.value = this.link;
                                    
                                    // Ensure it's not visible but part of DOM
                                    textArea.style.position = 'fixed';
                                    textArea.style.left = '-9999px';
                                    textArea.style.top = '0';
                                    document.body.appendChild(textArea);
                                    
                                    textArea.focus();
                                    textArea.select();
                                    
                                    const successful = document.execCommand('copy');
                                    document.body.removeChild(textArea);
                                    
                                    if (successful) {
                                        this.handleSuccess();
                                    } else {
                                        this.handleError();
                                    }
                                } catch (err) {
                                    this.handleError();
                                }
                            },
                            
                            handleSuccess() {
                                this.state = 'success';
                                this.message = 'Link Referral Berhasil Disalin';
                                setTimeout(() => {
                                    this.state = 'idle';
                                    this.message = '';
                                }, 2000);
                            },
                            
                            handleError() {
                                this.state = 'error';
                                this.message = 'Gagal menyalin. Silakan copy manual.';
                                setTimeout(() => {
                                    this.state = 'idle';
                                    this.message = '';
                                }, 3000);
                            }
                        }">
                            <div class="relative">
                                <button 
                                    @click="copy()" 
                                    :disabled="state === 'loading'"
                                    :class="{
                                        'btn-3d-blue': state === 'idle',
                                        'bg-gray-400 cursor-not-allowed': state === 'loading',
                                        'btn-3d-green': state === 'success',
                                        'btn-3d-red': state === 'error'
                                    }"
                                    class="btn-3d shimmer inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 min-w-[120px] justify-center"
                                >
                                    <!-- Idle State -->
                                    <span x-show="state === 'idle'" class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                        Copy Link
                                    </span>
                                    
                                    <!-- Loading State -->
                                    <span x-show="state === 'loading'" x-cloak class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Menyalin...
                                    </span>
                                    
                                    <!-- Success State -->
                                    <span x-show="state === 'success'" x-cloak class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Tersalin!
                                    </span>
                                    
                                    <!-- Error State -->
                                    <span x-show="state === 'error'" x-cloak class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Gagal
                                    </span>
                                </button>
                                
                                <!-- Feedback Message Tooltip/Text -->
                                <div 
                                    x-show="message" 
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2"
                                    class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-1 bg-gray-800 text-white text-xs rounded shadow-lg whitespace-nowrap z-10"
                                    x-text="message"
                                    x-cloak
                                ></div>
                            </div>
                        </div>

                        <code class="text-indigo-600 dark:text-indigo-400 font-mono text-lg break-all select-all">{{ $referralLink ?? '#' }}</code>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Referral Berhasil</div>
                            <div class="mt-1 text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $referralCount ?? 0 }} User</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Total Komisi Didapat</div>
                            <div class="mt-1 text-2xl font-bold text-green-900 dark:text-green-100">Rp {{ number_format($totalCommission ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    <div class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                        <p><strong>Cara menggunakan:</strong> Copy link di atas dan bagikan ke calon distributor. Ketika mereka mendaftar melalui link tersebut, Anda akan otomatis tercatat sebagai referrer mereka.</p>
                    </div>
                </div>
            </div>

            <!-- Recent Orders for Silverchannel -->
            @if(isset($recentOrders) && $recentOrders->count() > 0)
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Pesanan Terakhir Anda</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $order->order_number ?? ('#' . $order->id) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('silverchannel.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @endrole


        </div>
    </div>
</x-app-layout>
