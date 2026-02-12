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
                        <div class="p-6 text-gray-900 dark:text-gray-100" x-data="{ 
                            provider: '{{ $settings['email_provider'] ?? ($settings['brevo_active'] ? 'brevo' : 'mailketing') }}' 
                        }">
                            <h3 class="text-lg font-medium mb-4">API Email Settings</h3>
                            
                            @if(session('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                    <span class="block sm:inline">{{ session('success') }}</span>
                                </div>
                            @endif

                            <form action="{{ route('admin.integrations.update') }}" method="POST">
                                @csrf
                                
                                <div class="mb-6">
                                    <div class="border-b border-gray-200 dark:border-gray-700">
                                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                            <a href="#" 
                                               @click.prevent="provider = 'brevo'"
                                               :class="{'border-indigo-500 text-indigo-600': provider === 'brevo', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': provider !== 'brevo'}"
                                               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                                Brevo (SMTP/API)
                                            </a>
                                            <a href="#" 
                                               @click.prevent="provider = 'mailketing'"
                                               :class="{'border-indigo-500 text-indigo-600': provider === 'mailketing', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': provider !== 'mailketing'}"
                                               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                                Mailketing (API)
                                            </a>
                                            <a href="#" 
                                               @click.prevent="provider = 'routing'"
                                               :class="{'border-indigo-500 text-indigo-600': provider === 'routing', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': provider !== 'routing'}"
                                               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                                Advanced Routing
                                            </a>
                                        </nav>
                                    </div>
                                    <input type="hidden" name="email_provider" x-bind:value="provider === 'routing' ? 'brevo' : provider">
                                </div>
                                
                                <!-- Brevo Settings -->
                                <div x-show="provider === 'brevo'" class="space-y-4 border-l-4 border-blue-500 pl-4 py-2 bg-blue-50 dark:bg-blue-900/10 rounded-r">
                                    <h4 class="font-semibold text-blue-600 dark:text-blue-400">Brevo Configuration</h4>
                                    <input type="hidden" name="brevo_active" :value="provider === 'brevo' ? 1 : 0">

                                    <div class="mb-4" x-data="{ keyError: false, checkKey(val) { this.keyError = val.trim().startsWith('xsmtp'); } }">
                                        <x-input-label for="brevo_api_key" :value="__('API Key (v3)')" />
                                        <x-text-input id="brevo_api_key" class="block mt-1 w-full" type="password" name="brevo_api_key" 
                                            :value="$settings['brevo_api_key'] ?? ''" 
                                            @input="checkKey($event.target.value)"
                                            x-init="checkKey($el.value)"
                                            />
                                        <p x-show="keyError" x-cloak class="text-red-600 dark:text-red-400 text-sm mt-1 font-bold">
                                            ⚠️ Peringatan: Anda memasukkan SMTP Key (xsmtp-...). Mohon gunakan API Key v3 (xkeysib-...) agar fitur sinkronisasi & tes koneksi berfungsi.
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">Dapatkan API Key v3 dari Brevo Dashboard > SMTP & API > API Keys (Harus berawalan <code>xkeysib-</code>).</p>
                                    </div>

                                    <div class="mb-4">
                                        <x-input-label for="brevo_smtp_login" :value="__('SMTP Login Email')" />
                                        <x-text-input id="brevo_smtp_login" class="block mt-1 w-full" type="email" name="brevo_smtp_login" 
                                            :value="$settings['brevo_smtp_login'] ?? ''" placeholder="Usually your login email" />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <x-input-label for="brevo_sender_email" :value="__('Default Sender Email')" />
                                            <x-text-input id="brevo_sender_email" class="block mt-1 w-full" type="email" name="brevo_sender_email" 
                                                :value="$settings['brevo_sender_email'] ?? ''" />
                                        </div>
                                        <div>
                                            <x-input-label for="brevo_sender_name" :value="__('Default Sender Name')" />
                                            <x-text-input id="brevo_sender_name" class="block mt-1 w-full" type="text" name="brevo_sender_name" 
                                                :value="$settings['brevo_sender_name'] ?? ''" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Mailketing Settings -->
                                <div x-show="provider === 'mailketing'" class="space-y-4 border-l-4 border-green-500 pl-4 py-2 bg-green-50 dark:bg-green-900/10 rounded-r" style="display: none;">
                                    <h4 class="font-semibold text-green-600 dark:text-green-400">Mailketing Configuration</h4>
                                    
                                    <div class="mb-4">
                                        <x-input-label for="mailketing_api_token" :value="__('API Token')" />
                                        <x-text-input id="mailketing_api_token" class="block mt-1 w-full" type="password" name="mailketing_api_token" 
                                            placeholder="Leave blank to keep unchanged" />
                                        <p class="text-sm text-gray-500 mt-1">Dapatkan API Token dari menu Integration di Mailketing.</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <x-input-label for="mailketing_sender_email" :value="__('Default Sender Email')" />
                                            <x-text-input id="mailketing_sender_email" class="block mt-1 w-full" type="email" name="mailketing_sender_email" 
                                                :value="$settings['mailketing_sender_email'] ?? ''" />
                                        </div>
                                        <div>
                                            <x-input-label for="mailketing_sender_name" :value="__('Default Sender Name')" />
                                            <x-text-input id="mailketing_sender_name" class="block mt-1 w-full" type="text" name="mailketing_sender_name" 
                                                :value="$settings['mailketing_sender_name'] ?? ''" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Advanced Routing Settings -->
                                <div x-show="provider === 'routing'" class="space-y-6 border-l-4 border-purple-500 pl-4 py-2 bg-purple-50 dark:bg-purple-900/10 rounded-r">
                                    <div class="mb-4">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Email Routing Rules</h4>
                                        <p class="text-xs text-gray-500 mb-4">Route specific types of emails to different providers.</p>

                                        <div class="grid grid-cols-1 gap-6">
                                            <!-- Authentication Emails -->
                                            <div>
                                                <x-input-label for="email_route_auth" value="Authentication (Reset Password, Verify Email)" />
                                                <select id="email_route_auth" name="email_route_auth" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    <option value="default" {{ ($settings['email_route_auth'] ?? '') == 'default' ? 'selected' : '' }}>Use Default Provider</option>
                                                    <option value="brevo" {{ ($settings['email_route_auth'] ?? '') == 'brevo' ? 'selected' : '' }}>Brevo</option>
                                                    <option value="mailketing" {{ ($settings['email_route_auth'] ?? '') == 'mailketing' ? 'selected' : '' }}>Mailketing</option>
                                                </select>
                                            </div>

                                            <!-- Order Product Emails -->
                                            <div>
                                                <x-input-label for="email_route_order" value="Order Transactions (Product)" />
                                                <select id="email_route_order" name="email_route_order" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    <option value="default" {{ ($settings['email_route_order'] ?? '') == 'default' ? 'selected' : '' }}>Use Default Provider</option>
                                                    <option value="brevo" {{ ($settings['email_route_order'] ?? '') == 'brevo' ? 'selected' : '' }}>Brevo</option>
                                                    <option value="mailketing" {{ ($settings['email_route_order'] ?? '') == 'mailketing' ? 'selected' : '' }}>Mailketing</option>
                                                </select>
                                            </div>

                                            <!-- Order Registration Emails -->
                                            <div>
                                                <x-input-label for="email_route_order_reg" value="Order Transactions (Registration Packet)" />
                                                <select id="email_route_order_reg" name="email_route_order_reg" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    <option value="default" {{ ($settings['email_route_order_reg'] ?? '') == 'default' ? 'selected' : '' }}>Use Default Provider</option>
                                                    <option value="brevo" {{ ($settings['email_route_order_reg'] ?? '') == 'brevo' ? 'selected' : '' }}>Brevo</option>
                                                    <option value="mailketing" {{ ($settings['email_route_order_reg'] ?? '') == 'mailketing' ? 'selected' : '' }}>Mailketing</option>
                                                </select>
                                            </div>

                                            <!-- Marketing Campaigns -->
                                            <div>
                                                <x-input-label for="email_route_marketing" value="Marketing Campaigns (Blast)" />
                                                <select id="email_route_marketing" name="email_route_marketing" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                    <option value="default" {{ ($settings['email_route_marketing'] ?? '') == 'default' ? 'selected' : '' }}>Use Default Provider</option>
                                                    <option value="brevo" {{ ($settings['email_route_marketing'] ?? '') == 'brevo' ? 'selected' : '' }}>Brevo</option>
                                                    <option value="mailketing" {{ ($settings['email_route_marketing'] ?? '') == 'mailketing' ? 'selected' : '' }}>Mailketing</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @include('admin.integrations.partials.email-template-editor')

                                <div class="flex items-center gap-4 mt-6">
                                    <button type="submit" class="btn-3d btn-3d-blue shimmer px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">{{ __('Save Changes') }}</button>
                                    
                                    <button type="button" @click="testConnection(provider)"
                                        class="btn-3d btn-3d-gray shimmer inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                        {{ __('Test Connection') }}
                                    </button>
                                </div>
                            </form>

                            <div id="test-result" class="mt-4 hidden p-4 rounded text-sm"></div>
                        </div>
                    </div>

                    <!-- Email Templates Section -->
                    <div class="mt-6 mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium">Email Templates</h3>
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.integrations.email') }}" method="GET" class="flex gap-2">
                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search templates..." class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <button type="submit" class="btn-3d btn-3d-gray shimmer px-3 py-1.5 rounded-md font-semibold text-xs text-white uppercase tracking-widest">Search</button>
                                    </form>
                                    <a href="{{ route('admin.email-templates.create') }}" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-3 py-1.5 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                        {{ __('Add New') }}
                                    </a>
                                </div>
                            </div>

                            @if (session('success') && !request()->routeIs('admin.integrations.update'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                    <span class="block sm:inline">{{ session('success') }}</span>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                    <span class="block sm:inline">{{ session('error') }}</span>
                                </div>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name / Key</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Brevo Sync</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse ($templates as $template)
                                        <tr>
                                            <td class="px-4 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $template->key }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($template->subject, 25) }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if($template->brevo_id)
                                                    <span class="text-green-600 flex items-center gap-1" title="Synced">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        #{{ $template->brevo_id }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 text-xs">Not Synced</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('admin.email-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900 dark:hover:text-indigo-400">Edit</a>
                                                    
                                                    <form action="{{ route('admin.email-templates.sync', $template) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400" title="Sync to Brevo">
                                                            Sync
                                                        </button>
                                                    </form>

                                                    <div class="relative" x-data="{ open: false }">
                                                        <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                                        </button>
                                                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-700 rounded-md shadow-lg z-50 border dark:border-gray-600" style="display: none;">
                                                            <div class="py-1">
                                                                <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Preview</a>
                                                                <form action="{{ route('admin.email-templates.duplicate', $template) }}" method="POST">
                                                                    @csrf
                                                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Duplicate</button>
                                                                </form>
                                                                <a href="{{ route('admin.email-templates.export', $template) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Export</a>
                                                                <form action="{{ route('admin.email-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No templates found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                {{ $templates->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>

                    <!-- Documentation Card -->
                    <div class="mt-6 mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Panduan Konfigurasi Email
                            </h3>
                            
                            <div class="prose dark:prose-invert max-w-none text-sm" x-data="{ tab: 'brevo' }">
                                <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
                                    <button @click="tab = 'brevo'" :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'brevo', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': tab !== 'brevo' }" class="py-2 px-4 border-b-2 font-medium text-sm focus:outline-none transition-colors">Brevo Guide</button>
                                    <button @click="tab = 'mailketing'" :class="{ 'border-green-500 text-green-600 dark:text-green-400': tab === 'mailketing', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': tab !== 'mailketing' }" class="py-2 px-4 border-b-2 font-medium text-sm focus:outline-none transition-colors">Mailketing Guide</button>
                                </div>

                                <div x-show="tab === 'brevo'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                                    <p class="mb-4">
                                        <strong>Brevo (Sendinblue)</strong> direkomendasikan untuk skalabilitas tinggi dan deliverability internasional.
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-semibold text-blue-600 dark:text-blue-400 mb-2">Setup API Key</h4>
                                            <ol class="list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                                <li>Login ke dashboard <a href="https://brevo.com" target="_blank" class="text-blue-500 hover:underline">Brevo.com</a>.</li>
                                                <li>Masuk ke <strong>SMTP & API</strong> > <strong>API Keys</strong>.</li>
                                                <li>Generate API Key baru (v3) dan copy ke kolom API Key.</li>
                                            </ol>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-blue-600 dark:text-blue-400 mb-2">Setup Sender</h4>
                                            <ol class="list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                                <li>Verifikasi sender email di menu <strong>Senders & IP</strong>.</li>
                                                <li>Pastikan email pengirim sesuai dengan yang didaftarkan.</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="tab === 'mailketing'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                                    <p class="mb-4">
                                        <strong>Mailketing</strong> adalah solusi lokal Indonesia yang hemat biaya untuk email transaksional dengan server lokal.
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-semibold text-green-600 dark:text-green-400 mb-2">Setup API Token</h4>
                                            <ol class="list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                                <li>Login ke dashboard <a href="https://mailketing.co.id" target="_blank" class="text-green-500 hover:underline">Mailketing.co.id</a>.</li>
                                                <li>Masuk ke menu <strong>Integrasi</strong> di sidebar.</li>
                                                <li>Salin <strong>API Token</strong> Anda ke kolom konfigurasi di atas.</li>
                                            </ol>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-green-600 dark:text-green-400 mb-2">Setup Sender</h4>
                                            <ol class="list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                                <li>Pastikan Anda telah mendaftarkan Sender di menu Mailketing.</li>
                                                <li>Sender email harus valid dan terverifikasi di sistem Mailketing.</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-md border border-gray-100 dark:border-gray-700">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-300 text-sm mb-1">Tips:</h4>
                                    <ul class="list-disc list-inside text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                        <li>Gunakan tombol <strong>Test Connection</strong> setelah menyimpan untuk memastikan konfigurasi berhasil.</li>
                                        <li>Pastikan untuk mengklik <strong>Save Changes</strong> sebelum melakukan test connection jika Anda baru saja mengubah pengaturan.</li>
                                    </ul>
                                </div>
                            </div>
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
        function testConnection(provider) {
            const resultDiv = document.getElementById('test-result');
            resultDiv.className = 'mt-4 p-4 rounded text-sm bg-gray-100 text-gray-700 border border-gray-300';
            resultDiv.innerHTML = 'Testing connection to ' + provider + '...';
            resultDiv.classList.remove('hidden');

            const url = provider === 'brevo' ? "{{ route('admin.integrations.test.brevo') }}" : "{{ route('admin.integrations.test.mailketing') }}";

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || (data.status && data.status === 'success')) {
                    resultDiv.className = 'mt-4 p-4 rounded text-sm bg-green-100 text-green-700 border border-green-400';
                    resultDiv.innerHTML = '<strong>Success:</strong> ' + (data.message || 'Connection successful');
                } else {
                    resultDiv.className = 'mt-4 p-4 rounded text-sm bg-red-100 text-red-700 border border-red-400';
                    resultDiv.innerHTML = '<strong>Failed:</strong> ' + (data.message || 'Unknown error');
                }
            })
            .catch(error => {
                resultDiv.className = 'mt-4 p-4 rounded text-sm bg-red-100 text-red-700 border border-red-400';
                resultDiv.innerHTML = '<strong>Error:</strong> ' + error.message;
            });
        }
    </script>
</x-app-layout>
