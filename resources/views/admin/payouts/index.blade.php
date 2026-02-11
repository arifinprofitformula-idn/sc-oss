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

        /* Red Variant */
        .btn-3d-red {
            background: linear-gradient(to bottom, #ef4444, #dc2626);
            border: 1px solid #b91c1c;
            color: white;
            text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);
            box-shadow: 
                0px 4px 0px 0px #991b1b,
                0px 5px 5px 0px rgba(0, 0, 0, 0.2),
                inset 0px 1px 0px 0px rgba(255, 255, 255, 0.3);
        }
        .btn-3d-red:hover {
            background: linear-gradient(to bottom, #f87171, #ef4444);
            --btn-pulse-color: rgba(239, 68, 68, 0.6);
            animation: pulse512 1.5s infinite;
        }
        .btn-3d-red:active {
            box-shadow: 
                0px 0px 0px 0px #991b1b,
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
            {{ __('Manage Payouts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="mb-6 mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('admin.payouts.index') }}" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <select name="status" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">All Statuses</option>
                            <option value="REQUESTED" {{ request('status') === 'REQUESTED' ? 'selected' : '' }}>Requested</option>
                            <option value="PROCESSED" {{ request('status') === 'PROCESSED' ? 'selected' : '' }}>Processed</option>
                            <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <button type="submit" class="btn-3d btn-3d-blue shimmer w-full md:w-auto justify-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">{{ __('Filter') }}</button>
                    </div>
                    <div class="w-full md:w-auto">
                        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn-3d btn-3d-green shimmer w-full md:w-auto justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">
                            {{ __('Export CSV') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Payouts Table -->
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg border border-blue-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Number</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">User / Referrer</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($payouts as $payout)
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $payout->payout_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $payout->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $payout->user->email }}</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-mono mt-1">{{ $payout->user->silver_channel_id ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($payout->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($payout->status === 'PROCESSED') bg-green-100 text-green-800 
                                        @elseif($payout->status === 'REQUESTED') bg-yellow-100 text-yellow-800 
                                        @elseif($payout->status === 'REJECTED') bg-red-100 text-red-800 
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $payout->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $payout->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.payouts.show', $payout) }}" class="btn-3d btn-3d-blue shimmer px-3 py-1 rounded-md text-xs">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $payouts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
