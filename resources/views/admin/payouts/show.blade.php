<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Payout Details') }} #{{ $payout->payout_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.payouts.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Back to Payouts</a>
            </div>

            <!-- Payout Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Request Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">User</p>
                            <p class="font-medium">{{ $payout->user->name }} ({{ $payout->user->email }})</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Amount</p>
                            <p class="font-medium text-lg">Rp {{ number_format($payout->amount, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="font-medium">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($payout->status === 'PROCESSED') bg-green-100 text-green-800 
                                    @elseif($payout->status === 'REQUESTED') bg-yellow-100 text-yellow-800 
                                    @elseif($payout->status === 'REJECTED') bg-red-100 text-red-800 
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $payout->status }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Requested At</p>
                            <p class="font-medium">{{ $payout->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-200 dark:border-gray-700">
                    
                    @if($payout->proof_file)
                    <div class="mt-4">
                        <h4 class="font-bold mb-2">Transfer Proof</h4>
                        <div class="border rounded p-2 bg-gray-50">
                            <img src="{{ asset('storage/' . $payout->proof_file) }}" alt="Transfer Proof" class="max-w-full h-auto max-h-[300px]">
                        </div>
                        <a href="{{ asset('storage/' . $payout->proof_file) }}" target="_blank" class="text-blue-600 hover:underline text-sm mt-1 inline-block">View Full Size</a>
                    </div>
                    @endif

                    @if($payout->rejection_reason)
                    <div class="mt-4 p-4 bg-red-50 text-red-800 rounded-lg">
                        <strong>Rejection Reason:</strong> {{ $payout->rejection_reason }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($payout->status === 'REQUESTED')
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Process Request</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Approve Form -->
                        <div class="border-r border-gray-200 dark:border-gray-700 pr-4">
                            <h4 class="font-bold text-green-600 mb-2">Approve & Mark as Processed</h4>
                            <form action="{{ route('admin.payouts.approve', $payout) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                
                                <div class="mb-4">
                                    <x-input-label for="proof_file" value="Upload Transfer Proof" />
                                    <input type="file" name="proof_file" id="proof_file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" required>
                                    <p class="mt-1 text-sm text-gray-500">Upload screenshot/receipt of transfer.</p>
                                </div>

                                <x-primary-button class="bg-green-600 hover:bg-green-700">
                                    {{ __('Confirm Transfer') }}
                                </x-primary-button>
                            </form>
                        </div>

                        <!-- Reject Form -->
                        <div>
                            <h4 class="font-bold text-red-600 mb-2">Reject Request</h4>
                            <form action="{{ route('admin.payouts.reject', $payout) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this payout? The amount will be returned to user wallet.');">
                                @csrf
                                @method('PATCH')
                                
                                <div class="mb-4">
                                    <x-input-label for="reason" value="Reason for Rejection" />
                                    <textarea name="reason" id="reason" rows="3" class="block w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required></textarea>
                                </div>

                                <x-primary-button class="bg-red-600 hover:bg-red-700">
                                    {{ __('Reject Request') }}
                                </x-primary-button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
