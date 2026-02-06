<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Order Received') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="orderReceivedPage({{ $expiryTime->timestamp * 1000 }}, '{{ $order->status }}')">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                    
                    <!-- Expired Message -->
                    <div class="mb-6 p-4 bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-200 rounded-lg" x-show="isCancelled" x-cloak>
                        <h4 class="text-lg font-bold">Waktu Pembayaran Habis</h4>
                        <p>Pesanan ini telah dibatalkan otomatis karena melewati batas waktu pembayaran.</p>
                    </div>

                    <!-- Success Icon -->
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900 mb-6" x-show="!isCancelled">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900 mb-6" x-show="isCancelled" x-cloak>
                         <svg class="h-8 w-8 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>

                    <h3 class="text-2xl font-bold mb-2" x-text="isCancelled ? 'Pesanan Dibatalkan' : 'Terima Kasih! Pesanan Anda Telah Diterima.'"></h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-8" x-show="!isCancelled">Silakan selesaikan pembayaran agar pesanan dapat segera diproses.</p>

                    <!-- Countdown Timer -->
                    <div class="mb-6 p-4 rounded-lg text-center transition-colors duration-300" 
                         :class="{
                            'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 animate-pulse border border-red-200': timeLeftSeconds < 600,
                            'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400': timeLeftSeconds >= 600
                         }"
                         x-show="showTimer" x-cloak>
                         <p class="text-sm font-medium uppercase tracking-wider mb-1">Sisa Waktu Pembayaran</p>
                         <div class="text-3xl font-bold font-mono" x-text="formattedTime"></div>
                    </div>

                    <!-- Order Details Horizontal Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-b border-gray-200 dark:border-gray-700 py-6 mb-8">
                        <div class="text-center md:border-r border-gray-100 dark:border-gray-700 last:border-0">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Nomor Pesanan</p>
                            <p class="font-bold text-lg whitespace-nowrap">{{ $order->order_number }}</p>
                        </div>
                        <div class="text-center md:border-r border-gray-100 dark:border-gray-700 last:border-0">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Tanggal</p>
                            <p class="font-bold text-lg whitespace-nowrap">{{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Metode Pembayaran</p>
                            <p class="font-bold text-lg capitalize whitespace-nowrap">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                        </div>
                    </div>

                    <!-- Bank Details (If Transfer) -->
                    @if($order->payment_method === 'transfer')
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8 text-left max-w-3xl mx-auto shadow-sm" x-show="!isCancelled">
                        <h4 class="font-bold text-lg mb-6 text-center border-b border-gray-200 dark:border-gray-600 pb-3">Instruksi Pembayaran</h4>
                        
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8 px-4">
                            <!-- Bank Info -->
                            <div class="w-full md:w-1/2 text-center md:text-left">
                                <p class="text-sm text-gray-500 mb-3">Bank Tujuan</p>
                                
                                @php
                                    $bankInfo = $store->bank_details[0] ?? [];
                                    // Handle both new and old key formats for backward compatibility
                                    $bankName = $bankInfo['bank'] ?? $bankInfo['bank_name'] ?? 'Bank';
                                    $accountNumber = $bankInfo['number'] ?? $bankInfo['account_number'] ?? '-';
                                    $accountName = $bankInfo['name'] ?? $bankInfo['account_name'] ?? '-';
                                    $bankLogo = $bankInfo['logo'] ?? null;
                                @endphp
                                
                                <div class="flex flex-col items-center md:items-start">
                                    @if($bankLogo)
                                        <img src="{{ $bankLogo }}" alt="{{ $bankName }}" class="h-10 mb-3 object-contain">
                                    @else
                                        <p class="font-bold text-xl mb-2">{{ $bankName }}</p>
                                    @endif
                                    
                                    <div class="flex items-center gap-2 group cursor-pointer bg-white dark:bg-gray-800 px-4 py-2 rounded border border-gray-200 dark:border-gray-600 hover:border-blue-400 transition-all w-full md:w-auto justify-center md:justify-start" 
                                         @click="copyToClipboard('{{ $accountNumber }}', $el)">
                                        <p class="text-lg font-mono font-bold tracking-wide select-all">{{ $accountNumber }}</p>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">a.n {{ $accountName }}</p>
                                </div>
                            </div>

                            <!-- Total Amount -->
                            <div class="w-full md:w-1/2 text-center md:text-right">
                                <p class="text-sm text-gray-500 mb-3">Total Tagihan</p>
                                <div class="flex flex-col items-center md:items-end">
                                    <div class="flex items-center gap-2 cursor-pointer group transition-all px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                         @click="copyToClipboard('{{ $order->total_amount }}', $el)">
                                        <p class="font-bold text-[24px] text-blue-600 dark:text-blue-400 leading-none tracking-tight">
                                            IDR {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </p>
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </div>
                                    @if($order->unique_code > 0)
                                        <p class="text-xs text-red-500 font-semibold mt-2 max-w-xs ml-auto">*Nominal termasuk kode unik. Mohon transfer tepat hingga 3 digit terakhir.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <style>
                        /* From Login Page & Products Page */
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
                            text-decoration: none; /* For anchor tags */
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
                        @keyframes shine { 
                            0% { left: -100px; } 
                            60% { left: 100%; } 
                            to { left: 100%; } 
                        } 
                    </style>
                    <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8" x-show="!isCancelled">
                        <a href="{{ route('payment.checkout', $order) }}" 
                           class="button-shine w-full sm:w-auto">
                            Konfirmasi Pembayaran
                            <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                        
                        <a href="{{ route('silverchannel.orders.show', $order) }}" 
                           class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm w-full sm:w-auto">
                            Lihat Detail Pesanan
                        </a>
                    </div>
                    
                    <!-- Back to Home Button (Shown when expired) -->
                    <div class="flex justify-center mt-8" x-show="isCancelled" x-cloak>
                         <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                            Kembali ke Dashboard
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div x-show="showToast" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white px-6 py-3 rounded-lg shadow-xl z-50 flex items-center gap-3">
            <div class="rounded-full bg-green-500 p-1">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="text-sm font-semibold">Teks Berhasil Disalin</span>
        </div>

    </div>

    <script>
        function orderReceivedPage(expiryTimestamp, initialStatus) {
            return {
                expiryTime: expiryTimestamp,
                status: initialStatus,
                now: new Date().getTime(),
                timeLeftSeconds: 0,
                showToast: false,
                isCancelled: initialStatus === 'CANCELLED',
                
                get showTimer() {
                    return this.timeLeftSeconds > 0 && !this.isCancelled && this.status === 'WAITING_PAYMENT';
                },

                init() {
                    if (this.status === 'WAITING_PAYMENT') {
                        this.updateTimer();
                        this.timer = setInterval(() => {
                            this.updateTimer();
                        }, 1000);
                    }
                },
                
                updateTimer() {
                    this.now = new Date().getTime();
                    const diff = this.expiryTime - this.now;
                    this.timeLeftSeconds = Math.max(0, Math.floor(diff / 1000));
                    
                    if (this.timeLeftSeconds <= 0) {
                        if (!this.isCancelled && this.status === 'WAITING_PAYMENT') {
                            this.isCancelled = true;
                            this.status = 'CANCELLED';
                            clearInterval(this.timer);
                            // Reload to ensure backend state is consistent if user refreshes
                            setTimeout(() => window.location.reload(), 2000);
                        }
                    }
                },
                
                get formattedTime() {
                    const hours = Math.floor(this.timeLeftSeconds / 3600);
                    const minutes = Math.floor((this.timeLeftSeconds % 3600) / 60);
                    const seconds = this.timeLeftSeconds % 60;
                    
                    const pad = (num) => num.toString().padStart(2, '0');
                    if (hours > 0) {
                         return `${pad(hours)} : ${pad(minutes)} : ${pad(seconds)}`;
                    }
                    return `${pad(minutes)} : ${pad(seconds)}`;
                },

                copyToClipboard(text, element = null) {
                    if (!text) return;
                    text = text.toString();
                    
                    const showSuccess = () => {
                        this.showToast = true;
                        if (element) {
                            // Find text element inside the clicked container or use the element itself
                            const textEl = element.querySelector('p.font-bold, p.font-mono') || element;
                            const originalText = textEl.textContent;
                            
                            // Visual feedback
                            element.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                            textEl.textContent = 'BERHASIL DISALIN!';
                            textEl.classList.add('animate-pulse');
                            
                            setTimeout(() => {
                                element.classList.remove('bg-green-50', 'text-green-700', 'border-green-200');
                                textEl.textContent = originalText;
                                textEl.classList.remove('animate-pulse');
                            }, 2000);
                        }
                        setTimeout(() => this.showToast = false, 3000);
                    };

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(showSuccess).catch(() => this.copyFallback(text, showSuccess));
                    } else {
                        this.copyFallback(text, showSuccess);
                    }
                },
                
                copyFallback(text, callback) {
                    const textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-9999px";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        const successful = document.execCommand('copy');
                        if (successful) callback();
                        else alert('Gagal menyalin. Silakan salin manual.');
                    } catch (err) {
                        alert('Gagal menyalin. Silakan salin manual.');
                    }
                    document.body.removeChild(textArea);
                }
            }
        }
    </script>
</x-app-layout>
