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

                    <!-- Recipient Information -->
                    <div x-data="{ 
                        showToast: false,
                        copyToClipboard(text) {
                            if (!text || text === '-' || text === '') {
                                console.warn('Nothing to copy');
                                return;
                            }
                            
                            // Modern API
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText(text)
                                    .then(() => {
                                        this.showToast = true;
                                        setTimeout(() => this.showToast = false, 2000);
                                    })
                                    .catch(err => {
                                        console.error('Failed to copy: ', err);
                                        // Fallback using textarea hack
                                        this.fallbackCopy(text);
                                    });
                            } else {
                                this.fallbackCopy(text);
                            }
                        },
                        fallbackCopy(text) {
                            const textArea = document.createElement('textarea');
                            textArea.value = text;
                            textArea.style.position = 'fixed'; // Avoid scrolling to bottom
                            textArea.style.left = '-9999px';
                            textArea.style.top = '0';
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();
                            
                            try {
                                const successful = document.execCommand('copy');
                                if (successful) {
                                    this.showToast = true;
                                    setTimeout(() => this.showToast = false, 2000);
                                } else {
                                    console.error('Fallback copy failed');
                                    alert('Gagal menyalin nomor rekening. Silakan salin secara manual.');
                                }
                            } catch (err) {
                                console.error('Fallback copy error', err);
                                alert('Gagal menyalin nomor rekening.');
                            }
                            
                            document.body.removeChild(textArea);
                        }
                    }">
                        <h3 class="text-lg font-bold mb-4">Recipient Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <!-- Bank Name -->
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Bank Name</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $payout->user->bank_name ?? '-' }}</p>
                            </div>

                            <!-- Account Number -->
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Account Number</p>
                                <div class="flex items-center gap-2 group cursor-pointer" 
                                     @click="copyToClipboard('{{ $payout->user->bank_account_no ?? '' }}')"
                                     title="Salin nomor rekening">
                                    <p class="font-medium font-mono text-gray-900 dark:text-gray-100">{{ $payout->user->bank_account_no ?? '-' }}</p>
                                    @if(!empty($payout->user->bank_account_no))
                                    <button type="button" 
                                            class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors relative"
                                            @click.stop="copyToClipboard('{{ $payout->user->bank_account_no ?? '' }}')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                        
                                        <!-- Custom Tooltip -->
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                            Salin nomor rekening
                                        </div>
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Account Name -->
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Account Name</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $payout->user->bank_account_name ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Toast Notification -->
                        <div x-show="showToast" style="display: none;"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-2"
                             class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white px-6 py-3 rounded-lg shadow-xl z-50 flex items-center gap-3">
                            <div class="rounded-full bg-green-500 p-1">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-sm font-semibold">Rekening Berhasil Disalin</span>
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

                                <button type="submit" class="btn-3d btn-3d-green shimmer w-full justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                    {{ __('Confirm Transfer') }}
                                </button>
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

                                <button type="submit" class="btn-3d btn-3d-red shimmer w-full justify-center inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                    {{ __('Reject Request') }}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
