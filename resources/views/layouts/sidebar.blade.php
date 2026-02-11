<div x-cloak :class="sidebarOpen ? 'block' : 'hidden'" @click="sidebarOpen = false" class="fixed inset-0 z-20 transition-opacity bg-gray-900 bg-opacity-50 lg:hidden"></div>

<div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" class="fixed inset-y-0 left-0 top-0 z-50 w-64 overflow-y-auto transition duration-300 transform bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col h-screen">

    <!-- Logo -->
    <div class="flex items-center justify-center h-16 border-b border-gray-200 dark:border-gray-700 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <x-application-logo class="block h-9 w-auto fill-current text-indigo-600 dark:text-indigo-400" />
        </a>
    </div>

    <nav class="mt-5 px-2 space-y-1 flex-1">
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            {{ __('Dashboard') }}
        </x-sidebar-link>

        @if(Auth::user()->hasRole('SUPER_ADMIN'))
            <x-sidebar-link :href="route('admin.silverchannels.index')" :active="request()->routeIs('admin.silverchannels.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                {{ __('Silverchannels') }}
            </x-sidebar-link>
            
            <x-sidebar-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                {{ __('Products') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('admin.packages.index')" :active="request()->routeIs('admin.packages.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                {{ __('Packages') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                {{ __('Orders') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('admin.payments.index')" :active="request()->routeIs('admin.payments.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                {{ __('Payments') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('admin.payouts.index')" :active="request()->routeIs('admin.payouts.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                {{ __('Payouts') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                {{ __('Reports') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('admin.chats.index')" :active="request()->routeIs('admin.chats.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                {{ __('Pusat Pesan') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('admin.integrations.index')" :active="request()->routeIs('admin.integrations.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                {{ __('Integration System') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('admin.settings.store')" :active="request()->routeIs('admin.settings.store')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                {{ __('Store Settings') }}
            </x-sidebar-link>
        @endif

        @if(Auth::user()->hasRole('SILVERCHANNEL'))
            <x-sidebar-link :href="route('silverchannel.products.index')" :active="request()->routeIs('silverchannel.products.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                {{ __('Catalog') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('silverchannel.orders.index')" :active="request()->routeIs('silverchannel.orders.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                {{ __('My Orders') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('payouts.index')" :active="request()->routeIs('payouts.index')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                {{ __('Wallet') }}
            </x-sidebar-link>

            <x-sidebar-link :href="route('silverchannel.referrals.index')" :active="request()->routeIs('silverchannel.referrals.*')">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2a3 3 0 015.356-1.857M12 7a3 3 0 110-6 3 3 0 010 6zm5 4a3 3 0 100-6 3 3 0 000 6zm-10 0a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                {{ __('My Referrals') }}
            </x-sidebar-link>

            @php
                $integrationService = app(\App\Services\IntegrationService::class);
                $showStoreSettings = $integrationService->get('silverchannel_store_menu_active');
            @endphp

            @if($showStoreSettings)
                <x-sidebar-link :href="route('silverchannel.store.settings')" :active="request()->routeIs('silverchannel.store.settings')">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    {{ __('Store Settings') }}
                </x-sidebar-link>
            @endif
        @endif
    </nav>

    <!-- User Profile & Logout -->
    <div class="border-t border-gray-200 dark:border-gray-700 p-4">
        @php
            $user = Auth::user();
            $photoUrl = $user->profile_picture ? asset('storage/' . $user->profile_picture) : '';
            $initials = substr($user->name, 0, 1);
        @endphp

        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <div x-data="{ 
                        photoUrl: '{{ $photoUrl }}', 
                        initials: '{{ $initials }}',
                        hasError: false
                    }" 
                    x-on:profile-photo-updated.window="photoUrl = $event.detail.url; hasError = false"
                    class="relative h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-300 font-bold overflow-hidden group border border-gray-200 dark:border-gray-600">
                    
                    <template x-if="photoUrl && !hasError">
                        <img :src="photoUrl" 
                             alt="{{ $user->name }}" 
                             class="h-full w-full object-cover transition-opacity duration-300 group-hover:opacity-80"
                             loading="lazy"
                             @@error="hasError = true">
                    </template>
                
                    <template x-if="!photoUrl || hasError">
                        <span x-text="initials" class="select-none"></span>
                    </template>
                </div>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-gray-900">
                    {{ $user->name }}
                </p>
                <a href="{{ route('profile.edit') }}" class="text-xs font-medium text-gray-500 group-hover:text-gray-700 hover:underline">
                    {{ __('View Profile') }}
                </a>
            </div>
        </div>

        <div class="flex items-center justify-between gap-2 w-full mt-4">
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <style>
                    @keyframes shimmer {
                        0% { transform: translateX(-100%) skewX(-15deg); }
                        100% { transform: translateX(200%) skewX(-15deg); }
                    }
                    .shimmer {
                        position: relative;
                        overflow: hidden;
                    }
                    .shimmer::after {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 50%;
                        height: 100%;
                        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
                        transform: skewX(-15deg);
                        animation: shimmer 2s infinite;
                        pointer-events: none;
                    }
                    /* 3D Button Effect */
                    .btn-3d {
                        transition: all 0.1s;
                        position: relative;
                        top: 0;
                    }
                    .btn-3d:active {
                        top: 2px;
                        box-shadow: 0 0px 0 0 var(--btn-shadow-color) !important;
                    }
                    .btn-3d-indigo {
                        background-color: #4f46e5;
                        box-shadow: 0 4px 0 0 #312e81;
                        --btn-shadow-color: #312e81;
                        color: white;
                    }
                    .btn-3d-indigo:hover {
                        background-color: #4338ca;
                    }
                </style>
                <button type="submit" class="w-full flex items-center justify-center px-4 py-3 rounded-full font-bold btn-3d btn-3d-indigo shimmer group">
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="ml-3">{{ __('Log Out') }}</span>
                </button>
            </form>
        </div>
    </div>
</div>
