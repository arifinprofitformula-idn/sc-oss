<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Order Details') }} #{{ $order->order_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ showDeliveryModal: false, previewUrl: null, uploading: false }">
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
                                            <div class="text-xs text-gray-500">{{ $item->product?->sku ?? '-' }}</div>
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
                        @else
                            <div class="mt-6 border-t pt-4 space-y-3">
                                @if($order->status === 'SHIPPED')
                                    <button @click="showDeliveryModal = true" class="block w-full text-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Order Diterima
                                    </button>
                                @endif

                                <a href="{{ route('silverchannel.orders.chat', $order) }}" class="block w-full text-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Pengaduan
                                </a>
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

        <!-- Delivery Confirmation Modal -->
        <div x-show="showDeliveryModal" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDeliveryModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     aria-hidden="true"
                     @click="showDeliveryModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDeliveryModal" 
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
                                    @click="showDeliveryModal = false"
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
</x-app-layout>
