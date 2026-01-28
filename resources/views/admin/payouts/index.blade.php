<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Payouts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Filters -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route('admin.payouts.index') }}" class="flex gap-4">
                            <select name="status" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="REQUESTED" {{ request('status') === 'REQUESTED' ? 'selected' : '' }}>Requested</option>
                                <option value="PROCESSED" {{ request('status') === 'PROCESSED' ? 'selected' : '' }}>Processed</option>
                                <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User / Referrer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($payouts as $payout)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payout->payout_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $payout->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $payout->user->email }}</div>
                                        <div class="text-xs text-indigo-600 dark:text-indigo-400 font-mono mt-1">{{ $payout->user->silver_channel_id ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rp {{ number_format($payout->amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($payout->status === 'PROCESSED') bg-green-100 text-green-800 
                                            @elseif($payout->status === 'REQUESTED') bg-yellow-100 text-yellow-800 
                                            @elseif($payout->status === 'REJECTED') bg-red-100 text-red-800 
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $payout->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payout->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.payouts.show', $payout) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $payouts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
