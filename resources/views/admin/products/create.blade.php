<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Product') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="productForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div>
                                <!-- Name -->
                                <div>
                                    <x-input-label for="name" :value="__('Product Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <!-- SKU -->
                                <div class="mt-4">
                                    <x-input-label for="sku" :value="__('SKU')" />
                                    <x-text-input id="sku" class="block mt-1 w-full" type="text" name="sku" :value="old('sku')" required />
                                    <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                                </div>

                                <!-- Brand -->
                                <div class="mt-4">
                                    <x-input-label for="brand_id" :value="__('Brand')" />
                                    <select id="brand_id" name="brand_id" x-model="selectedBrand" @change="checkNew($event.target.value, 'brand')" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                        <option value="new" class="font-bold text-indigo-600">+ Add New Brand</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
                                </div>

                                <!-- Category -->
                                <div class="mt-4">
                                    <x-input-label for="category_id" :value="__('Category')" />
                                    <select id="category_id" name="category_id" x-model="selectedCategory" @change="checkNew($event.target.value, 'category')" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                        <option value="new" class="font-bold text-indigo-600">+ Add New Category</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div>
                                <!-- Price Silverchannel -->
                                <div>
                                    <x-input-label for="price_silverchannel" :value="__('Distributor Price (Silverchannel)')" />
                                    <x-text-input id="price_silverchannel" class="block mt-1 w-full" type="number" name="price_silverchannel" :value="old('price_silverchannel')" required min="0" step="0.01" />
                                    <x-input-error :messages="$errors->get('price_silverchannel')" class="mt-2" />
                                </div>

                                <!-- Price MSRP (Renamed to Customer Price) -->
                                <div class="mt-4">
                                    <label class="block font-medium text-sm text-gray-700" for="price_msrp">
                                        {{ __('Customer Price') }}
                                    </label>
                                    <x-text-input id="price_msrp" class="block mt-1 w-full" type="number" name="price_msrp" :value="old('price_msrp')" min="0" step="0.01" />
                                    <x-input-error :messages="$errors->get('price_msrp')" class="mt-2" />
                                </div>

                                <!-- Weight -->
                                <div class="mt-4">
                                    <x-input-label for="weight" :value="__('Weight (gr)')" />
                                    <x-text-input id="weight" class="block mt-1 w-full" type="number" name="weight" :value="old('weight', 0)" required min="0" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" />
                                    <x-input-error :messages="$errors->get('weight')" class="mt-2" />
                                </div>

                                <!-- Stock -->
                                <div class="mt-4">
                                    <x-input-label for="stock" :value="__('Stock Quantity')" />
                                    <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock', 0)" required min="0" />
                                    <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                                </div>

                                <!-- Image -->
                                <div class="mt-4">
                                    <x-input-label for="image" :value="__('Product Image')" />
                                    <input id="image" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" type="file" name="image">
                                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Commission Settings -->
                        <div class="mb-6 border-t pt-4 mt-6" x-data="{ enabled: {{ old('commission_enabled', 0) ? 'true' : 'false' }} }">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Komisi Referral</h3>
                            
                            <div class="mb-4">
                                <label for="commission_enabled" class="inline-flex items-center">
                                    <input id="commission_enabled" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="commission_enabled" value="1" x-model="enabled">
                                    <span class="ms-2 text-sm text-gray-600">Aktifkan Komisi Referral untuk Produk Ini</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="enabled" x-transition>
                                <div>
                                    <x-input-label for="commission_type" :value="__('Tipe Komisi')" />
                                    <select name="commission_type" id="commission_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="percentage" {{ old('commission_type') == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                        <option value="fixed" {{ old('commission_type') == 'fixed' ? 'selected' : '' }}>Nilai Tetap (IDR)</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('commission_type')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="commission_value" :value="__('Nilai Komisi')" />
                                    <x-text-input id="commission_value" class="block mt-1 w-full" type="number" name="commission_value" :value="old('commission_value', 0)" min="0" step="0.01" />
                                    <p class="text-xs text-gray-500 mt-1">Jika %, masukkan 10 untuk 10%. Jika IDR, masukkan nominal.</p>
                                    <x-input-error :messages="$errors->get('commission_value')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="4">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Is Active -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Create Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Brand Modal -->
        <div x-show="showBrandModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showBrandModal" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="closeBrandModal()">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div x-show="showBrandModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Add New Brand
                        </h3>
                        <div class="mt-2">
                            <input type="text" x-model="newBrandName" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Brand Name">
                            <p x-show="brandError" class="mt-2 text-sm text-red-600" x-text="brandError"></p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="saveBrand()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Brand
                        </button>
                        <button type="button" @click="closeBrandModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Category Modal -->
        <div x-show="showCategoryModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCategoryModal" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="closeCategoryModal()">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div x-show="showCategoryModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Add New Category
                        </h3>
                        <div class="mt-2">
                            <input type="text" x-model="newCategoryName" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Category Name">
                            <p x-show="categoryError" class="mt-2 text-sm text-red-600" x-text="categoryError"></p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="saveCategory()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Category
                        </button>
                        <button type="button" @click="closeCategoryModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productForm', () => ({
                selectedBrand: "{{ old('brand_id') }}",
                selectedCategory: "{{ old('category_id') }}",
                showBrandModal: false,
                showCategoryModal: false,
                newBrandName: '',
                newCategoryName: '',
                brandError: null,
                categoryError: null,

                checkNew(value, type) {
                    if (value === 'new') {
                        if (type === 'brand') {
                            this.showBrandModal = true;
                            this.selectedBrand = ''; // Reset select to empty until saved
                        } else {
                            this.showCategoryModal = true;
                            this.selectedCategory = '';
                        }
                    }
                },

                closeBrandModal() {
                    this.showBrandModal = false;
                    this.newBrandName = '';
                    this.brandError = null;
                },

                closeCategoryModal() {
                    this.showCategoryModal = false;
                    this.newCategoryName = '';
                    this.categoryError = null;
                },

                async saveBrand() {
                    this.brandError = null;
                    if (!this.newBrandName) {
                        this.brandError = 'Brand name is required.';
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('admin.brands.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: this.newBrandName,
                                is_active: 1
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Add new option to select
                            const select = document.getElementById('brand_id');
                            const option = new Option(data.brand.name, data.brand.id);
                            // Insert before the last option (+ Add New)
                            select.add(option, select.options[select.options.length - 1]);
                            
                            this.selectedBrand = data.brand.id;
                            this.closeBrandModal();
                        } else {
                            this.brandError = data.message || 'Error saving brand.';
                            if (data.errors && data.errors.name) {
                                this.brandError = data.errors.name[0];
                            }
                        }
                    } catch (error) {
                        this.brandError = 'Network error occurred.';
                        console.error(error);
                    }
                },

                async saveCategory() {
                    this.categoryError = null;
                    if (!this.newCategoryName) {
                        this.categoryError = 'Category name is required.';
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('admin.categories.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: this.newCategoryName,
                                is_active: 1
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Add new option to select
                            const select = document.getElementById('category_id');
                            const option = new Option(data.category.name, data.category.id);
                            // Insert before the last option (+ Add New)
                            select.add(option, select.options[select.options.length - 1]);
                            
                            this.selectedCategory = data.category.id;
                            this.closeCategoryModal();
                        } else {
                            this.categoryError = data.message || 'Error saving category.';
                            if (data.errors && data.errors.name) {
                                this.categoryError = data.errors.name[0];
                            }
                        }
                    } catch (error) {
                        this.categoryError = 'Network error occurred.';
                        console.error(error);
                    }
                }
            }));
        });
    </script>
</x-app-layout>
