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
            {{ __('Payment Verification') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="paymentManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter -->
            <div class="mb-6 mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form action="{{ route('admin.payments.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <select name="status" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">All Statuses</option>
                            <option value="PENDING_VERIFICATION" {{ request('status') == 'PENDING_VERIFICATION' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Paid</option>
                            <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>Failed</option>
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

            <!-- Notifications -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg border border-blue-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Payment #</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Proof</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-end text-xs font-bold text-white uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($payments as $payment)
                                <tr class="hover:bg-blue-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $payment->payment_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-blue-600 hover:underline">
                                        {{ $payment->order->order_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    IDR {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 uppercase">
                                    {{ str_replace('_', ' ', $payment->method) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($payment->proof_file)
                                        <a href="{{ asset('storage/' . $payment->proof_file) }}" target="_blank" class="btn-3d btn-3d-blue shimmer px-3 py-1 rounded-md text-xs">View Proof</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($payment->status == 'PENDING_VERIFICATION') bg-yellow-100 text-yellow-800 
                                        @elseif($payment->status == 'PAID') bg-green-100 text-green-800 
                                        @elseif($payment->status == 'FAILED') bg-red-100 text-red-800 
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ str_replace('_', ' ', $payment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                    @if($payment->status === 'PENDING_VERIFICATION')
                                        <div class="flex justify-end space-x-3">
                                            <!-- Approve -->
                                            <div class="relative group">
                                                <button @click="openConfirmModal('approve', '{{ route('admin.payments.verify', $payment) }}', 'Approve Payment', 'Are you sure you want to verify this payment?', 'Yes, Approve', 'btn-3d-green', 'PATCH')" 
                                                        class="p-2 rounded hover:shadow hover:bg-green-50 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 transition">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 pointer-events-none transition">Approve</span>
                                            </div>

                                            <!-- Reject -->
                                            <div class="relative group">
                                                <button @click="openConfirmModal('reject', '{{ route('admin.payments.reject', $payment->id) }}', 'Reject Payment', 'Please provide a reason for rejection:', 'Reject Payment', 'btn-3d-red', 'PATCH', true)" 
                                                        class="p-2 rounded hover:shadow hover:bg-red-50 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 pointer-events-none transition">Reject</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $payments->links() }}
            </div>
        </div>
        </div>

        <!-- Confirmation Modal -->
        <div x-show="confirmModal.show" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="if(!confirmModal.loading) confirmModal.show = false">
            
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="if(!confirmModal.loading) confirmModal.show = false">
                    <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full"
                     x-transition:enter="transform transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transform transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10"
                                 :class="{
                                    'bg-red-100 text-red-600': confirmModal.type === 'delete' || confirmModal.type === 'reject',
                                    'bg-yellow-100 text-yellow-600': confirmModal.type === 'warning',
                                    'bg-green-100 text-green-600': confirmModal.type === 'approve' || confirmModal.type === 'success',
                                    'bg-blue-100 text-blue-600': confirmModal.type === 'info'
                                 }">
                                <!-- Heroicon based on type -->
                                <template x-if="confirmModal.type === 'delete' || confirmModal.type === 'reject'">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </template>
                                <template x-if="confirmModal.type === 'approve' || confirmModal.type === 'success'">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="confirmModal.type === 'info'">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title" x-text="confirmModal.title">
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="confirmModal.message">
                                    </p>
                                    
                                    <!-- Input for Reject Reason -->
                                    <template x-if="confirmModal.hasInput">
                                        <div class="mt-3">
                                            <textarea 
                                                x-model="confirmModal.inputValue" 
                                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                rows="3"
                                                placeholder="Enter reason here..."
                                                required></textarea>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                class="btn-3d shimmer w-full inline-flex justify-center rounded-md px-4 py-2 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="confirmModal.buttonClass"
                                :disabled="confirmModal.loading || (confirmModal.hasInput && !confirmModal.inputValue)"
                                @click="submitConfirm()">
                            <svg x-show="confirmModal.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="confirmModal.loading ? 'Processing...' : confirmModal.buttonText"></span>
                        </button>
                        <button type="button" 
                                class="btn-3d btn-3d-gray shimmer mt-3 w-full inline-flex justify-center rounded-md px-4 py-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="confirmModal.loading"
                                @click="confirmModal.show = false">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('paymentManager', () => ({
                confirmModal: {
                    show: false,
                    type: 'info',
                    title: '',
                    message: '',
                    actionUrl: '',
                    actionMethod: 'POST',
                    loading: false,
                    buttonText: 'Confirm',
                    buttonClass: 'btn-3d-blue',
                    hasInput: false,
                    inputValue: ''
                },

                openConfirmModal(type, url, title, message, buttonText, buttonClass, method = 'POST', hasInput = false) {
                    this.confirmModal = {
                        show: true,
                        type: type,
                        title: title,
                        message: message,
                        actionUrl: url,
                        actionMethod: method,
                        loading: false,
                        buttonText: buttonText,
                        buttonClass: buttonClass,
                        hasInput: hasInput,
                        inputValue: ''
                    };
                },

                submitConfirm() {
                    this.confirmModal.loading = true;
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.confirmModal.actionUrl;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = this.confirmModal.actionMethod;
                    form.appendChild(methodInput);

                    if (this.confirmModal.hasInput) {
                        const reasonInput = document.createElement('input');
                        reasonInput.type = 'hidden';
                        reasonInput.name = 'reason';
                        reasonInput.value = this.confirmModal.inputValue;
                        form.appendChild(reasonInput);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            }));
        });
    </script>
</x-app-layout>