<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @role('SILVERCHANNEL')
            <!-- Referral Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="md:flex md:items-center md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Link Referral Anda</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Bagikan link ini untuk mendapatkan komisi dari pendaftaran distributor baru.</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg flex flex-col sm:flex-row items-center justify-between gap-4">
                        <code class="text-indigo-600 dark:text-indigo-400 font-mono text-lg break-all select-all">{{ $referralLink ?? '#' }}</code>
                        
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
                                        'bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900': state === 'idle',
                                        'bg-gray-400 cursor-not-allowed': state === 'loading',
                                        'bg-green-600 hover:bg-green-700': state === 'success',
                                        'bg-red-600 hover:bg-red-700': state === 'error'
                                    }"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 min-w-[120px] justify-center"
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
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">#{{ $order->unique_code ?? $order->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('silverchannel.store.operational-status') }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
