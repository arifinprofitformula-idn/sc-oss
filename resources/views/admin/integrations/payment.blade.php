<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Integration System') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Settings Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Payment Gateway Configuration</h3>
                            
                            <form action="{{ route('admin.integrations.update') }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 gap-6">
                                    <!-- Provider -->
                                    <div>
                                        <label for="payment_gateway_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provider</label>
                                        <select id="payment_gateway_provider" name="payment_gateway_provider" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="midtrans" {{ $settings['provider'] === 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                                            <option value="xendit" {{ $settings['provider'] === 'xendit' ? 'selected' : '' }}>Xendit (Coming Soon)</option>
                                        </select>
                                    </div>

                                    <!-- Merchant ID -->
                                    <div>
                                        <label for="midtrans_merchant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Merchant ID</label>
                                        <input type="text" name="midtrans_merchant_id" id="midtrans_merchant_id" value="{{ $settings['merchant_id'] }}" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                    </div>

                                    <!-- Client Key -->
                                    <div>
                                        <label for="midtrans_client_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Key</label>
                                        <input type="text" name="midtrans_client_key" id="midtrans_client_key" value="{{ $settings['client_key'] }}" 
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md">
                                    </div>

                                    <!-- Server Key -->
                                    <div>
                                        <label for="midtrans_server_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Server Key</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="password" name="midtrans_server_key" id="midtrans_server_key" value="{{ $settings['server_key'] }}" 
                                                class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Encrypted in database.</p>
                                    </div>

                                    <!-- Production Mode -->
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="midtrans_is_production" name="midtrans_is_production" type="checkbox" value="1" {{ $settings['is_production'] ? 'checked' : '' }} 
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="midtrans_is_production" class="font-medium text-gray-700 dark:text-gray-300">Production Mode</label>
                                            <p class="text-gray-500 dark:text-gray-400">Uncheck for Sandbox mode.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end items-center">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Logs -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Recent Logs</h3>
                            
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    @forelse($logs as $log)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800 {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-500' : 'bg-red-500' }}">
                                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $log->method }} <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log->endpoint }}</span></p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                        <time datetime="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ml-11 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $log->status_code }} ({{ $log->duration_ms }}ms)
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    @empty
                                    <li class="text-sm text-gray-500 dark:text-gray-400">No logs found.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
