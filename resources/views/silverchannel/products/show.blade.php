<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/placeholder.png') }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-auto rounded-lg shadow-md">
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold mb-4">{{ $product->name }}</h1>
                            <div class="text-sm text-gray-500 mb-4">SKU: {{ $product->sku }}</div>
                            
                            <div class="mb-6">
                                <div class="text-gray-600 dark:text-gray-400">Harga Silverchannel:</div>
                                <div class="text-3xl font-bold text-blue-600">Rp {{ number_format($product->price_silverchannel, 0, ',', '.') }}</div>
                            </div>
                            
                            <div class="mb-6">
                                <div class="text-gray-600 dark:text-gray-400">Harga Konsumen (MSRP):</div>
                                <div class="text-xl font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($product->price_customer, 0, ',', '.') }}</div>
                            </div>
                            
                            <div class="prose dark:prose-invert mb-8">
                                {{ $product->description }}
                            </div>
                            
                            <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg w-full md:w-auto transition duration-300">
                                Add to Cart (Coming Soon)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
