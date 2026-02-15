<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Order Details') }} #{{ $order->order_number }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showUploadModal: false, showViewModal: false, previewUrl: null, viewType: 'image', viewError: false, uploading: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Order Info -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Items -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Items</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 bg-blue-50 dark:bg-gray-700 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Product</th>
                                        <th class="px-4 py-2 bg-blue-50 dark:bg-gray-700 text-right text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Price</th>
                                        <th class="px-4 py-2 bg-blue-50 dark:bg-gray-700 text-center text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Qty</th>
                                        <th class="px-4 py-2 bg-blue-50 dark:bg-gray-700 text-right text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($order->items as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item->product_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->product?->sku ?? '-' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                IDR {{ number_format($item->price, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                IDR {{ number_format($item->total, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-900 dark:text-gray-100">Total Amount</td>
                                        <td class="px-4 py-3 text-right font-bold text-blue-600 whitespace-nowrap">
                                            IDR {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Order History -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Order History</h3>
                        <ul class="space-y-4">
                            @foreach ($order->logs as $log)
                                <li class="flex items-start">
                                    <div class="flex-shrink-0 h-2 w-2 mt-2 rounded-full bg-blue-500"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            Status changed to <span class="font-bold">{{ str_replace('_', ' ', $log->to_status) }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $log->created_at->format('d M Y H:i') }}</p>
                                        @if($log->note)
                                            <p class="text-sm text-gray-600 mt-1 italic">"{{ $log->note }}"</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Sidebar -->
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
                        text-decoration: none;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                    } 
                    .button-shine:hover { 
                        transform: scale(1.05); 
                        border-color: #fff9; 
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

                    /* Color Variants */
                    .button-shine.red {
                        background: linear-gradient(to right, #ef4444, #b91c1c); /* red-500 to red-700 */
                    }
                    .button-shine.green {
                        background: linear-gradient(to right, #22c55e, #15803d); /* green-500 to green-700 */
                    }
                    .button-shine.gray {
                        background: linear-gradient(to right, #6b7280, #374151); /* gray-500 to gray-700 */
                    }

                    @keyframes shine { 
                        0% { left: -100px; } 
                        60% { left: 100%; } 
                        to { left: 100%; } 
                    }
                    
                    /* Status Animations */
                    @keyframes drive-across {
                        0% { transform: translateX(-50px); opacity: 0; }
                        10% { opacity: 1; }
                        90% { opacity: 1; }
                        100% { transform: translateX(50px); opacity: 0; }
                    }
                    @keyframes float {
                        0%, 100% { transform: translateY(0); }
                        50% { transform: translateY(-5px); }
                    }
                    @keyframes pulse-ring {
                        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
                        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
                        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
                    }
                    @keyframes spin-slow {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                    @keyframes shake {
                        0%, 100% { transform: rotate(0deg); }
                        25% { transform: rotate(-5deg); }
                        75% { transform: rotate(5deg); }
                    }
                    @keyframes scale-in {
                        0% { transform: scale(0); opacity: 0; }
                        100% { transform: scale(1); opacity: 1; }
                    }
                    
                    .status-icon-container {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 80px;
                        margin-bottom: 1rem;
                    }
                    .animate-drive { animation: drive-across 2s linear infinite; }
                    .animate-float { animation: float 3s ease-in-out infinite; }
                    .animate-pulse-custom { animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
                    .animate-spin-slow { animation: spin-slow 3s linear infinite; }
                    .animate-shake { animation: shake 0.5s ease-in-out infinite; }
                    .animate-scale-in { animation: scale-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
                </style>
                <div class="space-y-6">
                    <!-- Status Card -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Status</h3>
                        <div class="flex flex-col items-center justify-center py-4">
                            <!-- Animated Icon -->
                            <div class="status-icon-container">
                                @switch($order->status)
                                    @case('SHIPPED')
                                        <div class="relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-blue-600 animate-drive" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" class="hidden" /> <!-- Fallback -->
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 012-2v0a2 2 0 012 2m9 0a2 2 0 012-2v0a2 2 0 012 2" />
                                            </svg>
                                            <div class="absolute -bottom-2 left-0 right-0 h-1 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-400 w-1/2 animate-drive" style="animation-duration: 1s;"></div>
                                            </div>
                                        </div>
                                        @break

                                    @case('PACKING')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-yellow-600 animate-shake" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        @break

                                    @case('PAID')
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-green-100 rounded-full animate-pulse-custom"></div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-600 relative z-10 animate-scale-in" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        @break

                                    @case('WAITING_PAYMENT')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-orange-500 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        @break

                                    @case('WAITING_VERIFICATION')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-blue-500 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        @break

                                    @case('DELIVERED')
                                        <div class="relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-600 animate-scale-in" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                            </svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500 absolute -bottom-1 -right-1 bg-white rounded-full border-2 border-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        @break
                                    
                                    @case('CANCELLED')
                                    @case('REFUNDED')
                                    @case('RETURNED')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500 animate-shake" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        @break

                                    @default
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 animate-float" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                @endswitch
                            </div>
                            
                            <span class="px-6 py-2 mt-2 inline-flex text-base leading-5 font-bold rounded-full shadow-sm
                                @if($order->status == 'DRAFT') bg-gray-100 text-gray-800 border border-gray-200
                                @elseif($order->status == 'PAID') bg-green-100 text-green-800 border border-green-200
                                @elseif($order->status == 'SHIPPED') bg-blue-100 text-blue-800 border border-blue-200
                                @elseif($order->status == 'PACKING') bg-yellow-100 text-yellow-800 border border-yellow-200
                                @elseif($order->status == 'CANCELLED') bg-red-100 text-red-800 border border-red-200
                                @else bg-indigo-100 text-indigo-800 border border-indigo-200 @endif">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                            
                            <p class="text-xs text-gray-500 mt-2">
                                @if($order->status == 'WAITING_PAYMENT')
                                    Menunggu pembayaran Anda
                                @elseif($order->status == 'PAID')
                                    Pembayaran berhasil diverifikasi
                                @elseif($order->status == 'PACKING')
                                    Pesanan sedang dikemas
                                @elseif($order->status == 'SHIPPED')
                                    Pesanan dalam pengiriman
                                @elseif($order->status == 'DELIVERED')
                                    Pesanan telah diterima
                                @endif
                            </p>

                            @php
                                $hasProof = $order->proof_of_delivery && \Illuminate\Support\Facades\Storage::disk('delivered')->exists($order->proof_of_delivery);
                                $proofExt = $hasProof ? strtolower(pathinfo($order->proof_of_delivery, PATHINFO_EXTENSION)) : null;
                                $proofType = $proofExt === 'pdf' ? 'pdf' : 'image';
                            @endphp
                            @if($hasProof)
                                <div class="mt-4 w-full px-4">
                                    <button @click="showViewModal = true; previewUrl = '{{ Storage::disk('delivered')->url($order->proof_of_delivery) }}'; viewType = '{{ $proofType }}'; viewError = false" 
                                            class="button-shine green w-full">
                                        Lihat Bukti Terima
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        @if($order->status === 'WAITING_PAYMENT' || $order->status === 'DRAFT')
                            <div class="mt-6 border-t pt-4 space-y-3">
                                <a href="{{ route('payment.checkout', $order) }}" class="button-shine w-full">
                                    Proceed to Payment
                                </a>
                                
                                <form action="{{ route('silverchannel.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    @csrf
                                    <button type="submit" class="button-shine red w-full">
                                        Cancel Order
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="mt-6 border-t pt-4 space-y-3">
                                @if($order->status === 'SHIPPED')
                                    <button @click="showUploadModal = true" class="button-shine green w-full">
                                        Order Diterima
                                    </button>
                                @endif

                                <a href="{{ route('silverchannel.orders.chat', $order) }}" class="button-shine w-full">
                                    Butuh Bantuan CS
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Shipping Info -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Shipping Details</h3>
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="block text-gray-500 font-medium">Courier Service</span>
                                <span class="block text-gray-900 dark:text-gray-100 font-semibold mt-1">
                                    {{ strtoupper($order->shipping_courier ?? '-') }} 
                                    @if($order->shipping_service)
                                        - {{ strtoupper($order->shipping_service) }}
                                    @endif
                                </span>
                            </div>
                            
                            <div>
                                <span class="block text-gray-500 font-medium">Shipping Address</span>
                                <span class="block text-gray-900 dark:text-gray-100 whitespace-pre-line mt-1">{{ $order->shipping_address }}</span>
                            </div>

                            @if($order->shipping_tracking_number)
                                <div class="pt-4 border-t dark:border-gray-700">
                                    <span class="block text-gray-500 font-medium">Tracking Number (Resi)</span>
                                    <div class="flex flex-col mt-1" x-data="{
                                        copied: false,
                                        copyToClipboard() {
                                            const text = '{{ $order->shipping_tracking_number }}';
                                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                                navigator.clipboard.writeText(text).then(() => {
                                                    this.copied = true;
                                                    setTimeout(() => this.copied = false, 2000);
                                                }).catch(() => {
                                                    this.fallbackCopy(text);
                                                });
                                            } else {
                                                this.fallbackCopy(text);
                                            }
                                        },
                                        fallbackCopy(text) {
                                            const textArea = document.createElement('textarea');
                                            textArea.value = text;
                                            textArea.style.position = 'fixed';
                                            textArea.style.left = '-9999px';
                                            document.body.appendChild(textArea);
                                            textArea.focus();
                                            textArea.select();
                                            try {
                                                document.execCommand('copy');
                                                this.copied = true;
                                                setTimeout(() => this.copied = false, 2000);
                                            } catch (err) {
                                                console.error('Fallback: Unable to copy', err);
                                            }
                                            document.body.removeChild(textArea);
                                        }
                                    }">
                                        <div class="flex items-center gap-2">
                                            <span class="block text-gray-900 dark:text-gray-100 font-mono font-bold text-lg bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                                {{ $order->shipping_tracking_number }}
                                            </span>
                                            <button @click="copyToClipboard()" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200" :class="{'text-green-500 hover:text-green-600': copied}" :title="copied ? 'Copied!' : 'Copy'">
                                                <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <svg x-show="copied" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </div>
                                        <span x-show="copied" x-cloak 
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 transform translate-y-[-10px]"
                                            x-transition:enter-end="opacity-100 transform translate-y-0"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 transform translate-y-0"
                                            x-transition:leave-end="opacity-0 transform translate-y-[-10px]"
                                            class="flex items-center gap-1 text-green-600 dark:text-green-400 text-sm font-medium mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Resi Berhasil Disalin
                                        </span>
                                    </div>
                                    
                                    <a href="https://cekresi.com/?noresi={{ $order->shipping_tracking_number }}" target="_blank" class="button-shine mt-3 w-full">
                                        Track Order
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Confirmation Modal -->
        <div x-show="showUploadModal" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showUploadModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     aria-hidden="true"
                     @click="showUploadModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showUploadModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    <form action="{{ route('silverchannel.orders.mark-delivered', $order) }}" method="POST" enctype="multipart/form-data" @submit="uploading = true">
                        @csrf
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                        Konfirmasi Penerimaan Order
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Pastikan barang telah diterima dengan baik. Unggah foto bukti penerimaan (paket/barang) untuk menyelesaikan pesanan ini.
                                        </p>
                                        
                                        <!-- File Upload -->
                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Bukti Foto Penerimaan (Max 5MB)
                                            </label>
                                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md relative hover:border-blue-500 transition-colors">
                                                <div class="space-y-1 text-center" x-show="!previewUrl">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                                        <label for="proof-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                            <span>Upload a file</span>
                                                            <input id="proof-upload" name="proof_of_delivery" type="file" class="sr-only" accept="image/*" required
                                                                @change="
                                                                    const file = $event.target.files[0];
                                                                    if(file) {
                                                                        if(file.size > 5242880) {
                                                                            alert('File size exceeds 5MB limit');
                                                                            $event.target.value = '';
                                                                            return;
                                                                        }
                                                                        const reader = new FileReader();
                                                                        reader.onload = (e) => { previewUrl = e.target.result; };
                                                                        reader.readAsDataURL(file);
                                                                    }
                                                                ">
                                                        </label>
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        PNG, JPG, GIF up to 5MB
                                                    </p>
                                                </div>
                                                
                                                <!-- Preview -->
                                                <div x-show="previewUrl" class="relative">
                                                    <img :src="previewUrl" class="max-h-48 rounded mx-auto" />
                                                    <button type="button" @click="previewUrl = null; document.getElementById('proof-upload').value = ''" class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600 transform translate-x-1/2 -translate-y-1/2">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Bar (Fake simulation for UX) -->
                            <div x-show="uploading" class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
                                </div>
                                <p class="text-xs text-center text-gray-500 mt-1">Uploading...</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    :disabled="uploading"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                Konfirmasi Diterima
                            </button>
                            <button type="button" 
                                    @click="showUploadModal = false"
                                    :disabled="uploading"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Delivery Proof Modal -->
    <div x-show="showViewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showViewModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showViewModal = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showViewModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                Bukti Terima
                            </h3>
                            <div class="mt-4 flex justify-center">
                                <template x-if="viewType === 'image'">
                                    <img :src="previewUrl" alt="Bukti Terima" class="max-h-[70vh] w-auto rounded-lg shadow-md"
                                         x-on:error="viewError = true">
                                </template>
                                <template x-if="viewType === 'pdf'">
                                    <iframe :src="previewUrl" class="w-full h-[70vh] bg-white shadow-md rounded border-none"></iframe>
                                </template>
                            </div>
                            <div x-show="viewError" class="mt-3 text-center">
                                <p class="text-sm text-red-600">File bukti tidak ditemukan atau gagal dimuat.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <a :href="previewUrl" target="_blank" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Buka di Tab Baru
                    </a>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showViewModal = false">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
