<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('API Details') }} : {{ $externalApi->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Details & Test Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- API Info -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4">Configuration</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Endpoint URL</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-2 rounded">{{ $externalApi->endpoint_url }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Method</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $externalApi->method }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Auth Type</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($externalApi->auth_type) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Rate Limit</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $externalApi->rate_limit_requests }} req / {{ $externalApi->rate_limit_period }}s</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    @if($externalApi->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Default Parameters</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-2 rounded overflow-auto max-h-32">
                                    {{ json_encode($externalApi->parameters, JSON_PRETTY_PRINT) }}
                                </dd>
                            </div>
                        </dl>
                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('admin.external-apis.edit', $externalApi) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500">Edit Configuration</a>
                        </div>
                    </div>
                </div>

                <!-- Test Console -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-data="testConsole()">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4">Test Console</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Override Parameters (JSON)</label>
                            <textarea x-model="params" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono" rows="3" placeholder="{}"></textarea>
                        </div>

                        <button @click="runTest" :disabled="loading" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <span x-show="loading" class="mr-2">Testing...</span>
                            <span x-show="!loading">Run Test Request</span>
                        </button>

                        <!-- Results -->
                        <div x-show="result" class="mt-4 border-t pt-4 dark:border-gray-700" style="display: none;">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold">Status: <span x-text="result?.status" :class="result?.success ? 'text-green-600' : 'text-red-600'"></span></span>
                                <span class="text-sm text-gray-500">Time: <span x-text="result?.duration"></span> ms</span>
                            </div>
                            <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded text-xs overflow-auto max-h-64 font-mono text-gray-800 dark:text-gray-200" x-text="JSON.stringify(result?.data || result?.error, null, 2)"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Recent Activity Logs</h3>
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Time</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Duration</th>
                                    <th scope="col" class="px-6 py-3">Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $log->status_code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $log->response_time }} ms</td>
                                    <td class="px-6 py-4 text-red-500 truncate max-w-xs" title="{{ $log->error_message }}">{{ $log->error_message ?: '-' }}</td>
                                </tr>
                                @empty
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No logs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function testConsole() {
            return {
                params: '{}',
                loading: false,
                result: null,
                runTest() {
                    this.loading = true;
                    this.result = null;
                    
                    let payload = {};
                    try {
                        payload = JSON.parse(this.params);
                    } catch (e) {
                        alert('Invalid JSON parameters');
                        this.loading = false;
                        return;
                    }

                    fetch('{{ route("admin.external-apis.test", $externalApi) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ params: payload })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.result = data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.result = { error: 'Network Error' };
                        this.loading = false;
                    });
                }
            }
        }
    </script>
</x-app-layout>
