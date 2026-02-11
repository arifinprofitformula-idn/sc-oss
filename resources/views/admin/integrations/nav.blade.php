@php
    $navItems = [
        [
            'name' => 'API Ongkir',
            'route' => 'admin.integrations.shipping',
            'active' => request()->routeIs('admin.integrations.shipping'),
        ],
        [
            'name' => 'Payment Gateway',
            'route' => 'admin.integrations.payment',
            'active' => request()->routeIs('admin.integrations.payment'),
        ],
        [
            'name' => 'Brevo (Email)',
            'route' => 'admin.integrations.brevo',
            'active' => request()->routeIs('admin.integrations.brevo'),
        ],
        [
            'name' => 'EPI APE',
            'route' => 'admin.integrations.epi-ape',
            'active' => request()->routeIs('admin.integrations.epi-ape'),
        ],
        [
            'name' => 'Email Templates',
            'route' => 'admin.email-templates.index',
            'active' => request()->routeIs('admin.email-templates.*'),
        ],
        [
            'name' => 'Documentation',
            'route' => 'admin.integrations.docs',
            'active' => request()->routeIs('admin.integrations.docs'),
        ],
    ];

    $activeItem = collect($navItems)->firstWhere('active', true) ?? ['name' => 'Select Integration', 'route' => '#'];
@endphp

<div class="mb-6">
    <!-- Mobile Dropdown Navigation (<= 768px) -->
    <div class="md:hidden" x-data="{ open: false }">
        <label for="tabs" class="sr-only">Select a tab</label>
        
        <!-- Main Button (Accordion Header) -->
        <div class="relative">
            <button 
                @click="open = !open" 
                type="button" 
                class="relative z-30 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-[10px] shadow-sm pl-4 pr-10 py-3 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-200"
                aria-haspopup="listbox" 
                :aria-expanded="open" 
                aria-labelledby="listbox-label"
            >
                <span class="flex items-center">
                    <span class="ml-1 block truncate font-medium text-gray-700 dark:text-gray-200">
                        {{ $activeItem['name'] }}
                    </span>
                </span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                    <!-- Chevron Icon -->
                    <svg 
                        class="h-5 w-5 text-gray-400 transition-transform duration-300" 
                        :class="{'rotate-180': open}" 
                        xmlns="http://www.w3.org/2000/svg" 
                        viewBox="0 0 20 20" 
                        fill="currentColor" 
                        aria-hidden="true"
                    >
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <!-- Overlay -->
            <div 
                x-show="open" 
                x-transition:enter="transition-opacity ease-linear duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="transition-opacity ease-linear duration-300" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0"
                @click="open = false"
                class="fixed inset-0 z-29 bg-gray-900/50 backdrop-blur-sm"
                style="display: none;"
            ></div>

            <!-- Dropdown List -->
            <ul 
                x-show="open" 
                x-transition:enter="transition ease-out duration-200" 
                x-transition:enter-start="opacity-0 translate-y-1" 
                x-transition:enter-end="opacity-100 translate-y-0" 
                x-transition:leave="transition ease-in duration-150" 
                x-transition:leave-start="opacity-100 translate-y-0" 
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute z-30 mt-2 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-[10px] py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm scroll-smooth" 
                tabindex="-1" 
                role="listbox" 
                style="display: none;"
            >
                @foreach($navItems as $item)
                    <li 
                        class="text-gray-900 dark:text-gray-200 cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors duration-150 group" 
                        role="option"
                        @click="window.location.href = '{{ route($item['route']) }}'"
                    >
                        <div class="flex items-center">
                            <span class="font-normal block truncate {{ $item['active'] ? 'font-semibold text-indigo-600 dark:text-indigo-400' : '' }}">
                                {{ $item['name'] }}
                            </span>
                        </div>

                        @if($item['active'])
                            <span class="text-indigo-600 dark:text-indigo-400 absolute inset-y-0 right-0 flex items-center pr-4">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Desktop Tabs (Hidden on Mobile) -->
    <div class="hidden md:block border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="{{ $item['active']
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}
                        whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors duration-150">
                    {{ $item['name'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
