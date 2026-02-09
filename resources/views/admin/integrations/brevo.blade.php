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
            {{ __('Integration System') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('admin.integrations.nav')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Settings Form -->
                <div class="md:col-span-2">
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                                    <button type="submit" class="btn-3d btn-3d-blue shimmer px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">{{ __('Save Changes') }}</button>
                                    
                                    <button type="button" onclick="testBrevoConnection()" 
                                        class="btn-3d btn-3d-gray shimmer inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
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
                    <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
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
