<x-app-layout>
    <style>
        /* 3D Button Styles */
        .btn-3d {
            transition: all 0.1s;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: 
                0px 0px 0px 0px rgba(0, 0, 0, 0.5),
                0px 0px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.5),
                inset 0px -1px 0px 0px rgba(0, 0, 0, 0.2);
        }

        .btn-3d:active {
            transform: translateY(2px);
            box-shadow: 
                0px 0px 0px 0px rgba(0, 0, 0, 0.5),
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.2);
        }

        /* Blue Variant */
        .btn-3d-blue {
            background: linear-gradient(to bottom, #3b82f6, #2563eb);
            border: 1px solid #1d4ed8;
            color: white;
            text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);
            box-shadow: 
                0px 4px 0px 0px #1e40af,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3);
        }
        .btn-3d-blue:hover {
            background: linear-gradient(to bottom, #60a5fa, #3b82f6);
            --btn-pulse-color: rgba(59, 130, 246, 0.6);
            animation: pulse512 1.5s infinite;
        }
        .btn-3d-blue:active {
            box-shadow: 
                0px 0px 0px 0px #1e40af,
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.3);
        }

        /* Green Variant */
        .btn-3d-green {
            background: linear-gradient(to bottom, #10b981, #059669);
            border: 1px solid #047857;
            color: white;
            text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);
            box-shadow: 
                0px 4px 0px 0px #065f46,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3);
        }
        .btn-3d-green:hover {
            background: linear-gradient(to bottom, #34d399, #10b981);
            --btn-pulse-color: rgba(16, 185, 129, 0.6);
            animation: pulse512 1.5s infinite;
        }
        .btn-3d-green:active {
            box-shadow: 
                0px 0px 0px 0px #065f46,
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.3);
        }

        /* Gold Variant */
        .btn-3d-gold {
            background: linear-gradient(to bottom, #f59e0b, #d97706);
            border: 1px solid #b45309;
            color: white;
            text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);
            box-shadow: 
                0px 4px 0px 0px #92400e,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3);
        }
        .btn-3d-gold:hover {
            background: linear-gradient(to bottom, #fbbf24, #f59e0b);
            --btn-pulse-color: rgba(245, 158, 11, 0.6);
            animation: pulse512 1.5s infinite;
        }
        .btn-3d-gold:active {
            box-shadow: 
                0px 0px 0px 0px #92400e,
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.3);
        }
        
        /* Gray Variant */
        .btn-3d-gray {
            background: linear-gradient(to bottom, #6b7280, #4b5563);
            border: 1px solid #374151;
            color: white;
            text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);
            box-shadow: 
                0px 4px 0px 0px #1f2937,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3);
        }
        .btn-3d-gray:hover {
            background: linear-gradient(to bottom, #9ca3af, #6b7280);
            --btn-pulse-color: rgba(107, 114, 128, 0.6);
            animation: pulse512 1.5s infinite;
        }
        .btn-3d-gray:active {
            box-shadow: 
                0px 0px 0px 0px #1f2937,
                inset 0px 1px 0px 0px rgba(0, 0, 0, 0.3);
        }

        /* Animations */
        @keyframes pulse512 {
            0% { box-shadow: 0 0 0 0 var(--btn-pulse-color); }
            70% { box-shadow: 0 0 0 10px rgba(0, 0, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 0, 0, 0); }
        }

        .shimmer {
            position: relative;
            overflow: hidden;
        }
        .shimmer::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: shimmer 3s infinite;
            pointer-events: none;
        }
        @keyframes shimmer {
            0% { left: -100%; }
            20% { left: 200%; }
            100% { left: 200%; }
        }
    </style>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Order') }} #{{ $order->order_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Order Info -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Items -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Order Items</h3>
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
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Activity Log</h3>
                        <ul class="space-y-4">
                            @foreach ($order->logs as $log)
                                <li class="flex items-start">
                                    <div class="flex-shrink-0 h-2 w-2 mt-2 rounded-full bg-blue-500"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <span class="font-bold">{{ $log->user ? $log->user->name : 'System' }}</span>
                                            changed status to <span class="font-bold">{{ str_replace('_', ' ', $log->to_status) }}</span>
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

                    <!-- Additional Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Silverchannel Info -->
                        <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Silverchannel</h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="block text-gray-500">Name</span>
                                    <span class="block text-gray-900 dark:text-gray-100 font-bold">{{ $order->user->name }}</span>
                                </div>
                                <div>
                                    <span class="block text-gray-500">Email</span>
                                    <span class="block text-gray-900 dark:text-gray-100">{{ $order->user->email }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Info -->
                        <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Shipping Details</h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="block text-gray-500">Address</span>
                                    <span class="block text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $order->shipping_address }}</span>
                                </div>
                                @if($order->shipping_tracking_number)
                                    <div>
                                        <span class="block text-gray-500">Tracking Number</span>
                                        <span class="block text-gray-900 dark:text-gray-100 font-mono font-bold">{{ $order->shipping_tracking_number }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Shipping & Tracking -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Shipping Information</h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500">Courier</p>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ strtoupper($order->shipping_courier ?? '-') }} - {{ strtoupper($order->shipping_service ?? '-') }}</p>
                        </div>

                        <form action="{{ route('admin.orders.update-tracking', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <x-input-label for="tracking_number" :value="__('Tracking Number (Resi)')" />
                                <div class="flex gap-2 mt-1">
                                    <x-text-input id="tracking_number" name="tracking_number" type="text" class="block w-full" :value="old('tracking_number', $order->shipping_tracking_number)" placeholder="Input Resi..." />
                                </div>
                                @error('tracking_number')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <x-input-label for="tracking_note" :value="__('Note (Optional)')" />
                                <textarea id="tracking_note" name="note" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm" rows="2" placeholder="e.g. Resi valid"></textarea>
                            </div>

                            <button type="submit" class="btn-3d btn-3d-blue shimmer w-full justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                {{ __('Update Tracking') }}
                            </button>
                        </form>
                        
                        @if($order->shipping_tracking_number)
                            <div class="mt-4 pt-4 border-t dark:border-gray-700">
                                <a href="https://cekresi.com/?noresi={{ $order->shipping_tracking_number }}" target="_blank" class="btn-3d btn-3d-green shimmer w-full justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Track on CekResi.com
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Chat with Silverchannel -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Communication</h3>
                        <a href="{{ route('admin.orders.chat', $order) }}" class="btn-3d btn-3d-blue shimmer w-full justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            {{ __('Chat with Silverchannel') }}
                        </a>
                    </div>

                    <!-- Update Status -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Update Status</h3>
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <x-input-label for="status" :value="__('Change Status To')" />
                                <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="WAITING_PAYMENT" {{ $order->status == 'WAITING_PAYMENT' ? 'selected' : '' }}>Waiting Payment</option>
                                    <option value="WAITING_VERIFICATION" {{ $order->status == 'WAITING_VERIFICATION' ? 'selected' : '' }}>Waiting Verification</option>
                                    <option value="PAID" {{ $order->status == 'PAID' ? 'selected' : '' }}>Paid</option>
                                    <option value="PACKING" {{ $order->status == 'PACKING' ? 'selected' : '' }}>Packing</option>
                                    <option value="SHIPPED" {{ $order->status == 'SHIPPED' ? 'selected' : '' }}>Shipped</option>
                                    <option value="DELIVERED" {{ $order->status == 'DELIVERED' ? 'selected' : '' }}>Delivered</option>
                                    <option value="CANCELLED" {{ $order->status == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="REFUNDED" {{ $order->status == 'REFUNDED' ? 'selected' : '' }}>Refunded</option>
                                    <option value="RETURNED" {{ $order->status == 'RETURNED' ? 'selected' : '' }}>Returned</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <x-input-label for="note" :value="__('Note (Optional)')" />
                                <textarea id="note" name="note" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="2"></textarea>
                            </div>

                            <button type="submit" class="btn-3d btn-3d-blue shimmer w-full justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                {{ __('Update Status') }}
                            </button>
                        </form>
                    </div>

                    <!-- Internal Note -->
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Internal Note</h3>
                        <form action="{{ route('admin.orders.store-note', $order) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <textarea name="note" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm" rows="3" placeholder="Add internal note..." required></textarea>
                            </div>
                            <button type="submit" class="btn-3d btn-3d-gray shimmer w-full justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                {{ __('Add Note') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>