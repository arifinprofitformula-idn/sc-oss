<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Integration System') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Settings Form -->
                <div class="md:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium mb-4">Brevo Email Marketing</h3>
                            
                            @if(session('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                    <span class="block sm:inline">{{ session('success') }}</span>
                                </div>
                            @endif

                            <form action="{{ route('admin.integrations.update') }}" method="POST">
                                @csrf
                                
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="brevo_active" value="1" 
                                            {{ isset($settings['brevo_active']) && $settings['brevo_active'] ? 'checked' : '' }}
                                            class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Enable Brevo Integration</span>
                                    </label>
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="brevo_api_key" :value="__('API Key')" />
                                    <x-text-input id="brevo_api_key" class="block mt-1 w-full" type="password" name="brevo_api_key" 
                                        :value="$settings['brevo_api_key'] ?? ''" />
                                    <p class="text-sm text-gray-500 mt-1">Get your API key from Brevo Dashboard > SMTP & API > Keys.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <x-input-label for="brevo_sender_email" :value="__('Default Sender Email')" />
                                        <x-text-input id="brevo_sender_email" class="block mt-1 w-full" type="email" name="brevo_sender_email" 
                                            :value="$settings['brevo_sender_email'] ?? ''" required />
                                    </div>
                                    <div>
                                        <x-input-label for="brevo_sender_name" :value="__('Default Sender Name')" />
                                        <x-text-input id="brevo_sender_name" class="block mt-1 w-full" type="text" name="brevo_sender_name" 
                                            :value="$settings['brevo_sender_name'] ?? ''" required />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 mt-6">
                                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                                    
                                    <button type="button" onclick="testBrevoConnection()" 
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        {{ __('Test Connection') }}
                                    </button>
                                </div>
                            </form>

                            <div id="test-result" class="mt-4 hidden p-4 rounded text-sm"></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Logs -->
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium mb-4">Recent Logs</h3>
                            <div class="space-y-3">
                                @forelse($logs as $log)
                                    <div class="text-sm border-b border-gray-200 dark:border-gray-700 pb-2">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ $log->method }}</span>
                                            <span class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="font-medium truncate" title="{{ $log->endpoint }}">{{ Str::limit($log->endpoint, 25) }}</div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="px-2 py-0.5 text-xs rounded {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $log->status_code }}
                                            </span>
                                            <span class="text-xs text-gray-400">{{ $log->duration_ms }}ms</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No logs available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testBrevoConnection() {
            const resultDiv = document.getElementById('test-result');
            resultDiv.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
            resultDiv.classList.add('bg-gray-100', 'text-gray-700');
            resultDiv.innerHTML = 'Testing connection...';
            resultDiv.classList.remove('hidden');

            fetch("{{ route('admin.integrations.test.brevo') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.classList.remove('bg-gray-100', 'text-gray-700');
                if (data.success) {
                    resultDiv.classList.add('bg-green-100', 'text-green-700');
                    resultDiv.innerHTML = `<strong>Connected!</strong><br>${data.message}`;
                } else {
                    resultDiv.classList.add('bg-red-100', 'text-red-700');
                    resultDiv.innerHTML = `<strong>Failed!</strong><br>${data.message}`;
                }
            })
            .catch(error => {
                resultDiv.classList.remove('bg-gray-100', 'text-gray-700');
                resultDiv.classList.add('bg-red-100', 'text-red-700');
                resultDiv.innerHTML = 'An unexpected error occurred.';
                console.error(error);
            });
        }
    </script>
</x-app-layout>
