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
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 md:gap-0">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Reports & Analytics') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex justify-end">
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 ring-1 ring-white/20">
                    <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Last Update: {{ $stats['integrity']['last_update'] }}
                </span>
            </div>

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
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-[10px] p-6">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col md:flex-row gap-4 md:items-end">
                    <div class="w-full md:flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="w-full md:flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="w-full md:w-auto flex flex-col md:flex-row gap-2">
                        <button type="submit" class="btn-3d btn-3d-gray shimmer w-full md:w-auto justify-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">
                            Filter
                        </button>
                        @if($startDate || $endDate)
                            <a href="{{ route('admin.reports.index') }}" class="btn-3d btn-3d-gray shimmer w-full md:w-auto justify-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Sales -->
                <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-[10px] p-6 border-l-4 border-blue-500">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Sales (Paid)</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</div>
                </div>

                <!-- Total Orders -->
                <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-[10px] p-6 border-l-4 border-indigo-500">
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
                <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-[10px] p-6 border-l-4 border-green-500">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Commissions Paid</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($stats['total_commission_paid'], 0, ',', '.') }}</div>
                </div>

                <!-- Pending Liability -->
                <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-[10px] p-6 border-l-4 border-yellow-500">
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
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-[10px] p-6">
                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Export Reports</h3>
                <div class="flex flex-col md:flex-row gap-4">
                    <a href="{{ route('admin.reports.export', ['type' => 'orders']) }}" class="btn-3d btn-3d-blue shimmer w-full md:w-auto justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">
                        Export Orders (CSV)
                    </a>
                    <a href="{{ route('admin.reports.export', ['type' => 'commissions']) }}" class="btn-3d btn-3d-green shimmer w-full md:w-auto justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">
                        Export Commissions (CSV)
                    </a>
                </div>
            </div>

            <!-- Monthly Sales Chart -->
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-[10px] p-6 border border-blue-200">
                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Monthly Sales Performance</h3>
                
                @if($monthlySales->isEmpty())
                    <p class="text-gray-500">No sales data available for the last 12 months.</p>
                @else
                    <div class="overflow-x-auto md:overflow-visible">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
                                <tr>
                                    <th scope="col" class="px-3 py-3 md:px-6 md:py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Month</th>
                                    <th scope="col" class="px-3 py-3 md:px-6 md:py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Total Sales</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($monthlySales as $sale)
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="px-3 py-3 md:px-6 md:py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $sale->month }}
                                    </td>
                                    <td class="px-3 py-3 md:px-6 md:py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
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