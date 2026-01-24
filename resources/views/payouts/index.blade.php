<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Wallet & Payouts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Wallet Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Available Balance</h3>
                    <div class="mt-2 text-3xl font-bold text-green-600">
                        Rp {{ number_format($balance, 0, ',', '.') }}
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Ready for withdrawal</p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pending Commission</h3>
                    <div class="mt-2 text-3xl font-bold text-yellow-600">
                        Rp {{ number_format($pendingCommission, 0, ',', '.') }}
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Will be available after holding period</p>
                </div>
            </div>

            <!-- Request Payout Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Request Payout</h3>
                    
                    <!-- Bank Account Info -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Destination Account</h4>
                        @if(auth()->user()->bank_account_no)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Bank Name</p>
                                    <p class="font-medium">{{ auth()->user()->bank_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Account Number</p>
                                    <p class="font-medium font-mono">{{ auth()->user()->bank_account_no }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Account Name</p>
                                    <p class="font-medium">{{ auth()->user()->bank_account_name }}</p>
                                </div>
                            </div>
                            <div class="mt-3 text-right">
                                <a href="{{ route('profile.edit') }}#contact-card" class="text-sm text-blue-600 hover:text-blue-500 hover:underline">Change Account Details &rarr;</a>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <div class="text-amber-600 dark:text-amber-400">
                                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span class="font-medium">No bank account configured.</span>
                                </div>
                                <a href="{{ route('profile.edit') }}#contact-card" class="text-sm font-bold text-blue-600 hover:text-blue-500 hover:underline">Setup Bank Account &rarr;</a>
                            </div>
                        @endif
                    </div>

                    @if($balance < 10000)
                        <div class="bg-yellow-50 text-yellow-800 p-4 rounded-md">
                            Minimum payout amount is Rp 10.000.
                        </div>
                    @elseif(!auth()->user()->bank_account_no)
                        <div class="bg-gray-100 text-gray-500 p-4 rounded-md text-center">
                            Please configure your bank account to request a payout.
                        </div>
                    @else
                        <form action="{{ route('payouts.store') }}" method="POST" class="space-y-4 max-w-xl">
                            @csrf
                            
                            <div>
                                <x-input-label for="amount" value="Amount (Rp)" />
                                <x-text-input id="amount" name="amount" type="number" class="mt-1 block w-full" :value="old('amount', $balance)" min="10000" :max="$balance" required />
                                <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                            </div>

                            <x-primary-button>
                                {{ __('Submit Payout Request') }}
                            </x-primary-button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Payout History -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Payout History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bank</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($payouts as $payout)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payout->payout_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payout->created_at->format('d M Y') }}</td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $payout->bank_details['bank_name'] }} - {{ $payout->bank_details['account_number'] }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No payout history found.</td>
                                </tr>
                                @endforelse
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
