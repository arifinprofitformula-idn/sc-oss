<header class="sticky top-0 z-40 flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm h-16">
    <div class="flex items-center">
        <!-- Mobile Toggle -->
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none mr-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Page Heading -->
        @if(isset($header))
            <div class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $header }}
            </div>
        @endif
    </div>

    <div class="flex items-center space-x-4">
        @if(auth()->user() && auth()->user()->hasRole('SILVERCHANNEL') && !request()->routeIs('silverchannel.checkout.*'))
            <!-- Cart Icon -->
            <a 
                href="{{ route('silverchannel.products.index', ['open_cart' => 1]) }}"
                @if(request()->routeIs('silverchannel.products.index'))
                    @click.prevent="$store.cart.toggle()"
                @endif
                class="relative p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors focus:outline-none"
                aria-label="Keranjang Belanja"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                
                <!-- Badge -->
                <span 
                    x-show="$store.cart.items.length > 0" 
                    x-text="$store.cart.items.reduce((acc, item) => acc + item.quantity, 0)"
                    class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full"
                    style="display: none;"
                ></span>
            </a>
        @endif

        <!-- Optional: Right side content like notifications can go here -->
    </div>
</header>
