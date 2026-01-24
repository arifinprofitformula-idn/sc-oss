<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit External API') }} : {{ $externalApi->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form action="{{ route('admin.external-apis.update', $externalApi) }}" method="POST" x-data="{ authType: '{{ old('auth_type', $externalApi->auth_type) }}' }">
                        @csrf
                        @method('PUT')

                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="name" :value="__('API Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $externalApi->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="endpoint_url" :value="__('Endpoint URL')" />
                                <x-text-input id="endpoint_url" class="block mt-1 w-full" type="url" name="endpoint_url" :value="old('endpoint_url', $externalApi->endpoint_url)" required />
                                <x-input-error :messages="$errors->get('endpoint_url')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="method" :value="__('HTTP Method')" />
                                <select id="method" name="method" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="GET" {{ old('method', $externalApi->method) == 'GET' ? 'selected' : '' }}>GET</option>
                                    <option value="POST" {{ old('method', $externalApi->method) == 'POST' ? 'selected' : '' }}>POST</option>
                                    <option value="PUT" {{ old('method', $externalApi->method) == 'PUT' ? 'selected' : '' }}>PUT</option>
                                    <option value="DELETE" {{ old('method', $externalApi->method) == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                                </select>
                                <x-input-error :messages="$errors->get('method')" class="mt-2" />
                            </div>

                            <div class="flex items-center mt-8">
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600" name="is_active" value="1" {{ old('is_active', $externalApi->is_active) ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Parameters -->
                        <div class="mb-6">
                            <x-input-label for="parameters" :value="__('Default Parameters (JSON)')" />
                            <textarea id="parameters" name="parameters" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3">{{ old('parameters', json_encode($externalApi->parameters)) }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Example: {"key": "value"}</p>
                            <x-input-error :messages="$errors->get('parameters')" class="mt-2" />
                        </div>

                        <!-- Rate Limiting -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="rate_limit_requests" :value="__('Rate Limit (Requests)')" />
                                <x-text-input id="rate_limit_requests" class="block mt-1 w-full" type="number" name="rate_limit_requests" :value="old('rate_limit_requests', $externalApi->rate_limit_requests)" required />
                            </div>
                            <div>
                                <x-input-label for="rate_limit_period" :value="__('Period (Seconds)')" />
                                <x-text-input id="rate_limit_period" class="block mt-1 w-full" type="number" name="rate_limit_period" :value="old('rate_limit_period', $externalApi->rate_limit_period)" required />
                            </div>
                        </div>

                        <!-- Authentication -->
                        <div class="mb-6 border-t pt-4 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Authentication</h3>
                            
                            <div class="mb-4">
                                <x-input-label for="auth_type" :value="__('Auth Type')" />
                                <select id="auth_type" name="auth_type" x-model="authType" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="none">None</option>
                                    <option value="api_key">API Key</option>
                                    <option value="bearer">Bearer Token</option>
                                    <option value="basic">Basic Auth</option>
                                </select>
                            </div>

                            @php $creds = $externalApi->auth_credentials ?? []; @endphp

                            <!-- API Key Fields -->
                            <div x-show="authType === 'api_key'" class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <div>
                                    <x-input-label for="auth_key" :value="__('Key Name')" />
                                    <x-text-input id="auth_key" class="block mt-1 w-full" type="text" name="auth_credentials[key]" placeholder="X-API-KEY" :value="old('auth_credentials.key', $creds['key'] ?? '')" />
                                </div>
                                <div>
                                    <x-input-label for="auth_value" :value="__('Key Value')" />
                                    <x-text-input id="auth_value" class="block mt-1 w-full" type="text" name="auth_credentials[value]" :value="old('auth_credentials.value', $creds['value'] ?? '')" />
                                </div>
                                <div>
                                    <x-input-label for="auth_in" :value="__('Location')" />
                                    <select name="auth_credentials[in]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="header" {{ ($creds['in'] ?? '') == 'header' ? 'selected' : '' }}>Header</option>
                                        <option value="query" {{ ($creds['in'] ?? '') == 'query' ? 'selected' : '' }}>Query Param</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Bearer Token Fields -->
                            <div x-show="authType === 'bearer'" class="p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <x-input-label for="auth_token" :value="__('Token')" />
                                <x-text-input id="auth_token" class="block mt-1 w-full" type="text" name="auth_credentials[token]" :value="old('auth_credentials.token', $creds['token'] ?? '')" />
                            </div>

                            <!-- Basic Auth Fields -->
                            <div x-show="authType === 'basic'" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <div>
                                    <x-input-label for="auth_username" :value="__('Username')" />
                                    <x-text-input id="auth_username" class="block mt-1 w-full" type="text" name="auth_credentials[username]" :value="old('auth_credentials.username', $creds['username'] ?? '')" />
                                </div>
                                <div>
                                    <x-input-label for="auth_password" :value="__('Password')" />
                                    <x-text-input id="auth_password" class="block mt-1 w-full" type="password" name="auth_credentials[password]" :value="old('auth_credentials.password', $creds['password'] ?? '')" />
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3">{{ old('description', $externalApi->description) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.external-apis.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update API') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
