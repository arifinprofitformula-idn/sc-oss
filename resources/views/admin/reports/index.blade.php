<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Reports & Analytics') }}
            </h2>
            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                Last Update: {{ $stats['integrity']['last_update'] }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Data Integrity Warnings -->
            @if($stats['integrity']['orders_vs_payments']['status'] !== 'OK' || $stats['integrity']['payouts_vs_ledger']['status'] !== 'OK')
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                    <div class="flex">
                        <div class="py-1"><svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                        <div>
                            <p class="font-bold">Data Integrity Warning - Please Check Logs</p>
                            @if($stats['integrity']['orders_vs_payments']['status'] !== 'OK')
                                <p class="text-sm">Orders vs Payments discrepancy: {{ number_format($stats['integrity']['orders_vs_payments']['diff'], 2) }}</p>
                            @endif
                            @if($stats['integrity']['payouts_vs_ledger']['status'] !== 'OK')
                                <p class="text-sm">Payouts vs Ledger discrepancy: {{ number_format($stats['integrity']['payouts_vs_ledger']['diff'], 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Filter
                        </button>
                        @if($startDate || $endDate)
                            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Sales -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Sales (Paid)</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</div>
                </div>

                <!-- Total Orders -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Orders</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_orders']['total']) }}</div>
                    <div class="mt-3 grid grid-cols-2 gap-1 text-xs text-gray-500 dark:text-gray-400">
                        @foreach($stats['total_orders']['breakdown'] as $status => $count)
                            @if($count > 0)
                                <div class="flex justify-between">
                                    <span>{{ str_replace('_', ' ', $status) }}</span>
                                    <span class="font-semibold">{{ $count }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Commissions Paid -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Commissions Paid</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($stats['total_commission_paid'], 0, ',', '.') }}</div>
                </div>

                <!-- Pending Liability -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pending Liability</div>
                    <div class="text-2xl font-bold text-yellow-600 mt-1">Rp {{ number_format($stats['pending_payouts']['total_pending_liability'], 0, ',', '.') }}</div>
                    <div class="mt-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex justify-between">
                            <span>Payouts Requested</span>
                            <span class="font-semibold">{{ number_format($stats['pending_payouts']['payouts_requested'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Wallet Available</span>
                            <span class="font-semibold">{{ number_format($stats['pending_payouts']['ledger_available'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Wallet Pending</span>
                            <span class="font-semibold">{{ number_format($stats['pending_payouts']['ledger_pending'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Export Reports</h3>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('admin.reports.export', ['type' => 'orders']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Export Orders (CSV)
                    </a>
                    <a href="{{ route('admin.reports.export', ['type' => 'commissions']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Export Commissions (CSV)
                    </a>
                </div>
            </div>

            <!-- Monthly Sales Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Monthly Sales Performance</h3>
                
                @if($monthlySales->isEmpty())
                    <p class="text-gray-500">No sales data available for the last 12 months.</p>
                @else
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Month</th>
                                    <th scope="col" class="px-6 py-3">Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlySales as $sale)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $sale->month }}
                                    </td>
                                    <td class="px-6 py-4">
                                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>