<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Wallet & Payouts') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showWithdrawModal: false, withdrawAmount: {{ $balance }} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Wallet Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Available Balance</h3>
                        <div class="mt-2 text-3xl font-bold text-green-600">
                            Rp {{ number_format($balance, 0, ',', '.') }}
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Ready for withdrawal</p>
                    </div>
                    <div>
                        <button @click="showWithdrawModal = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Withdraw
                        </button>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pending Commission</h3>
                    <div class="mt-2 text-3xl font-bold text-yellow-600">
                        Rp {{ number_format($pendingCommission, 0, ',', '.') }}
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Will be available after holding period</p>
                </div>
            </div>

            <!-- Withdraw Modal -->
            <div x-show="showWithdrawModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showWithdrawModal" 
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showWithdrawModal = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showWithdrawModal" 
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                        
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                        Withdraw Funds
                                    </h3>
                                    <div class="mt-2">
                                        @if(auth()->user()->status !== 'ACTIVE')
                                            <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 mb-4">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm text-red-700 dark:text-red-200">
                                                            Your account is not active. Please contact support.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif(!auth()->user()->bank_account_no)
                                            <div class="bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 p-4 mb-4">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                                            You haven't set up your bank account details yet. Please update your profile to proceed.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($balance < 10000)
                                            <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 mb-4">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm text-red-700 dark:text-red-200">
                                                            Insufficient balance. Minimum withdrawal amount is <strong>Rp 10.000</strong>.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                                Please review your bank details and enter the amount you wish to withdraw.
                                            </p>

                                            <!-- Bank Details Summary -->
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-4 text-sm">
                                                <div class="grid grid-cols-3 gap-2">
                                                    <span class="text-gray-500 dark:text-gray-400">Bank:</span>
                                                    <span class="col-span-2 font-medium text-gray-900 dark:text-gray-200">{{ auth()->user()->bank_name }}</span>
                                                    
                                                    <span class="text-gray-500 dark:text-gray-400">Number:</span>
                                                    <span class="col-span-2 font-medium font-mono text-gray-900 dark:text-gray-200">{{ auth()->user()->bank_account_no }}</span>
                                                    
                                                    <span class="text-gray-500 dark:text-gray-400">Name:</span>
                                                    <span class="col-span-2 font-medium text-gray-900 dark:text-gray-200">{{ auth()->user()->bank_account_name }}</span>
                                                </div>
                                            </div>

                                            <form id="withdrawForm" action="{{ route('payouts.store') }}" method="POST">
                                                @csrf
                                                <div>
                                                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount to Withdraw</label>
                                                    <div class="mt-1 relative rounded-md shadow-sm">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                                        </div>
                                                        <input type="number" name="amount" id="amount" 
                                                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md" 
                                                            placeholder="0" 
                                                            min="10000" 
                                                            max="{{ $balance }}" 
                                                            x-model="withdrawAmount"
                                                            required>
                                                    </div>
                                                    <p class="mt-2 text-xs text-gray-500">Max available: Rp {{ number_format($balance, 0, ',', '.') }}</p>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            @if(auth()->user()->status !== 'ACTIVE')
                                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" @click="showWithdrawModal = false">
                                    Close
                                </button>
                            @elseif(!auth()->user()->bank_account_no)
                                <a href="{{ route('profile.edit') }}#contact-card" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Go to Settings
                                </a>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showWithdrawModal = false">
                                    Close
                                </button>
                            @elseif($balance < 10000)
                                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" @click="showWithdrawModal = false">
                                    Understood
                                </button>
                            @else
                                <button type="button" onclick="document.getElementById('withdrawForm').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Confirm Withdraw
                                </button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showWithdrawModal = false">
                                    Cancel
                                </button>
                            @endif
                        </div>
                    </div>
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

            <!-- Commission History -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Commission History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Available At</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($commissions as $commission)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $commission->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-sm max-w-xs truncate" title="{{ $commission->description }}">
                                        {{ $commission->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($commission->type === 'REGISTRATION') bg-blue-100 text-blue-800 
                                            @elseif($commission->type === 'TRANSACTION') bg-green-100 text-green-800 
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $commission->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $commission->amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        Rp {{ number_format($commission->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($commission->status === 'PAID') bg-green-100 text-green-800 
                                            @elseif($commission->status === 'AVAILABLE') bg-emerald-100 text-emerald-800 
                                            @elseif($commission->status === 'PENDING') bg-yellow-100 text-yellow-800 
                                            @elseif($commission->status === 'CANCELLED') bg-red-100 text-red-800 
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $commission->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $commission->available_at ? $commission->available_at->format('d M Y') : '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No commission history found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $commissions->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('toast').show(@json(session('success')), 'success');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('toast').show(@json(session('error')), 'error');
            });
        </script>
    @endif
</x-app-layout>
