<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center shadow-lg transition duration-150 ease-in-out">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Product
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand / Category</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (SC)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($product->image)
                                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-10 w-10 object-cover rounded">
                                            @else
                                                <span class="text-gray-400">No Image</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $product->brand->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->category->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Rp {{ number_format($product->price_silverchannel, 0, ',', '.') }}</div>
                                            @if($product->price_msrp)
                                                <div class="text-xs text-gray-500 line-through">Rp {{ number_format($product->price_msrp, 0, ',', '.') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($product->weight, 0, ',', '.') }} gr
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $product->stock }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" x-data="{
                                            active: {{ $product->is_active ? 'true' : 'false' }},
                                            loading: false,
                                            error: null,
                                            async toggle() {
                                                this.error = null;
                                                const prev = this.active;
                                                this.active = !this.active;
                                                this.loading = true;
                                                try {
                                                    const res = await fetch('{{ route('admin.products.update-active', $product) }}', {
                                                        method: 'PATCH',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'Accept': 'application/json',
                                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                                        },
                                                        body: JSON.stringify({ is_active: this.active ? 1 : 0 })
                                                    });
                                                    if (!res.ok) { throw new Error('Request failed'); }
                                                    const data = await res.json();
                                                    this.active = !!data.is_active;
                                                } catch (e) {
                                                    this.active = prev;
                                                    this.error = 'Gagal memperbarui status. Coba lagi.';
                                                } finally {
                                                    this.loading = false;
                                                }
                                            }
                                        }">
                                            <button type="button" @click="toggle" :aria-pressed="active"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                                                :class="active ? 'bg-green-500' : 'bg-red-500'">
                                                <span class="sr-only">Toggle Active</span>
                                                <span class="inline-block h-5 w-5 transform bg-white rounded-full shadow transition duration-200 ease-in-out" 
                                                    :style="active ? 'transform: translateX(1.25rem);' : 'transform: translateX(0.25rem);'"></span>
                                            </button>
                                            <span class="ml-2 text-xs font-semibold" :class="active ? 'text-green-700' : 'text-red-700'" x-text="active ? 'Active' : 'Inactive'"></span>
                                            <template x-if="loading">
                                                <span class="ml-2 text-xs text-gray-500">Updating...</span>
                                            </template>
                                            <template x-if="error">
                                                <span class="ml-2 text-xs text-red-600" x-text="error"></span>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
