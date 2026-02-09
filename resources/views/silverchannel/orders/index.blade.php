<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
                @if($orders->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">{{ __('No orders found.') }}</p>
                        <a href="{{ route('silverchannel.products.index') }}" class="text-blue-600 hover:underline">{{ __('Start Shopping') }}</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-blue-50 dark:bg-gray-700 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Order #</th>
                                    <th class="px-6 py-3 bg-blue-50 dark:bg-gray-700 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Date</th>
                                    <th class="px-6 py-3 bg-blue-50 dark:bg-gray-700 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Total</th>
                                    <th class="px-6 py-3 bg-blue-50 dark:bg-gray-700 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Status</th>
                                    <th class="px-6 py-3 bg-blue-50 dark:bg-gray-700 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($orders as $order)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $order->order_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            {{ $order->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                            IDR {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full 
                                                @if($order->status == 'DRAFT') bg-gray-100 text-gray-800 border border-gray-200
                                                @elseif($order->status == 'PAID') bg-green-100 text-green-800 border border-green-200
                                                @elseif($order->status == 'CANCELLED') bg-red-100 text-red-800 border border-red-200
                                                @else bg-blue-100 text-blue-800 border border-blue-200 @endif">
                                                {{ str_replace('_', ' ', $order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('silverchannel.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 transition-colors duration-150">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
