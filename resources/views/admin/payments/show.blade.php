<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Payment Details') }} #{{ $payment->payment_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('admin.payments.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Back to Payments</a>
            </div>

            <!-- Payment Info -->
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Payment Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Amount</p>
                            <p class="font-medium text-lg">{{ number_format($payment->amount, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Method</p>
                            <p class="font-medium">{{ ucfirst($payment->method) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="font-medium">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($payment->status === 'PAID') bg-green-100 text-green-800 
                                    @elseif($payment->status === 'PENDING_VERIFICATION') bg-yellow-100 text-yellow-800 
                                    @elseif($payment->status === 'FAILED') bg-red-100 text-red-800 
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $payment->status }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date</p>
                            <p class="font-medium">{{ $payment->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proof of Payment -->
            @if(($payment->method === 'manual' || $payment->method === 'manual_transfer') && $payment->proof_file)
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Payment Proof</h3>
                    <div class="border rounded p-2 bg-gray-50">
                        <img src="{{ asset('storage/' . $payment->proof_file) }}" alt="Payment Proof" class="max-w-full h-auto max-h-[500px] mx-auto">
                    </div>
                    <div class="mt-2 text-center">
                        <a href="{{ asset('storage/' . $payment->proof_file) }}" target="_blank" class="text-blue-600 hover:underline">View Full Size</a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Order Info -->
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Order Reference #{{ $payment->order->order_number }}</h3>
                    <div class="mb-4">
                        <p class="text-sm text-gray-500">Customer</p>
                        <p class="font-medium">{{ $payment->order->user->name }} ({{ $payment->order->user->email }})</p>
                    </div>
                    
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($payment->order->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $item->product_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">{{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            <tr class="font-bold">
                                <td colspan="3" class="px-6 py-4 text-right">Total</td>
                                <td class="px-6 py-4 text-right">{{ number_format($payment->order->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            @if($payment->status === 'PENDING_VERIFICATION')
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Actions</h3>
                    <div class="flex flex-col md:flex-row gap-4">
                        <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" onsubmit="return confirm('Are you sure you want to verify this payment?');">
                            @csrf
                            @method('PATCH')
                            <x-primary-button class="bg-green-600 hover:bg-green-700">
                                Verify Payment & Mark Order as Paid
                            </x-primary-button>
                        </form>

                        <div x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reject Payment
                            </button>

                            <div x-show="open" class="mt-4 p-4 border border-red-200 rounded-lg bg-red-50 dark:bg-red-900/20">
                                <form action="{{ route('admin.payments.reject', $payment) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-4">
                                        <x-input-label for="reason" value="Rejection Reason" />
                                        <textarea name="reason" id="reason" rows="3" class="block w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required></textarea>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button @click="open = false" type="button" class="text-gray-600 dark:text-gray-400 hover:text-gray-900">Cancel</button>
                                        <x-primary-button class="bg-red-600 hover:bg-red-700">Confirm Reject</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>