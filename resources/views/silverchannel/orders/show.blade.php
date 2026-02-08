<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Order Details') }} #{{ $order->order_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Order Info -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Items -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Items</h3>
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item->product_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product->sku }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                            IDR {{ number_format($item->price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-gray-900 dark:text-gray-100">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                            IDR {{ number_format($item->total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-900 dark:text-gray-100">Total Amount</td>
                                    <td class="px-4 py-3 text-right font-bold text-blue-600">
                                        IDR {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Order History -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
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
                    @keyframes shine { 
                        0% { left: -100px; } 
                        60% { left: 100%; } 
                        to { left: 100%; } 
                    } 
                </style>
                <div class="space-y-6">
                    <!-- Status Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Status</h3>
                        <div class="text-center">
                            <span class="px-4 py-2 inline-flex text-sm leading-5 font-bold rounded-full 
                                @if($order->status == 'DRAFT') bg-gray-100 text-gray-800 
                                @elseif($order->status == 'PAID') bg-green-100 text-green-800 
                                @elseif($order->status == 'CANCELLED') bg-red-100 text-red-800 
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </div>
                        
                        @if($order->status === 'WAITING_PAYMENT' || $order->status === 'DRAFT')
                            <div class="mt-6 border-t pt-4 space-y-3">
                                <a href="{{ route('payment.checkout', $order) }}" class="button-shine w-full">
                                    Proceed to Payment
                                </a>
                                
                                <form action="{{ route('silverchannel.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    @csrf
                                    <button type="submit" class="block w-full text-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Cancel Order
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Shipping Info -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
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
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="block text-gray-900 dark:text-gray-100 font-mono font-bold text-lg bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                            {{ $order->shipping_tracking_number }}
                                        </span>
                                        <button onclick="navigator.clipboard.writeText('{{ $order->shipping_tracking_number }}')" class="text-gray-400 hover:text-gray-600" title="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
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
    </div>
</x-app-layout>
