<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Paket') }}: {{ $package->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                        <!-- Alert Messages -->
                        <div x-show="errorMessage" x-transition class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" style="display: none;">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline" x-text="errorMessage"></span>
                        </div>
                        <div x-show="successMessage" x-transition class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" style="display: none;">
                            <strong class="font-bold">Sukses!</strong>
                            <span class="block sm:inline" x-text="successMessage"></span>
                        </div>

                        <form method="POST" action="{{ route('admin.packages.update', $package) }}" x-data="packageForm()" @submit.prevent="submitForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Image Upload -->
                        <div class="mb-4" x-data="imageUpload()">
                            <label class="block text-sm font-medium text-gray-700">Foto Paket (1:1 atau 3:4) <span class="text-gray-400 text-xs">(Max 2MB, JPG/PNG/WEBP)</span></label>
                            
                            @if($package->image)
                                <div class="mb-2">
                                    <p class="text-xs text-gray-500 mb-1">Foto Saat Ini:</p>
                                    <img src="{{ Storage::url($package->image) }}" class="h-20 object-cover rounded-md border border-gray-200">
                                </div>
                            @endif

                            <input type="file" name="image" accept="image/png, image/jpeg, image/webp" @change="validateAndPreview($event)" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p x-show="error" x-text="error" class="text-red-500 text-sm mt-1" style="display: none;"></p>
                            <template x-if="preview">
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 mb-1">Preview Baru:</p>
                                    <img :src="preview" class="h-40 object-cover rounded-md border border-gray-200">
                                </div>
                            </template>
                            @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Paket <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required value="{{ old('name', $package->name) }}">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $package->description) }}</textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Original Price -->
                            <div>
                                <label for="original_price" class="block text-sm font-medium text-gray-700">Harga Normal (Rp) <span class="text-gray-400 text-xs">(Opsional)</span></label>
                                <input type="number" name="original_price" id="original_price" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ old('original_price', $package->original_price) }}">
                                <p class="text-xs text-gray-500 mt-1">Harga sebelum diskon (dicoret).</p>
                                @error('original_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Harga Jual / Promo (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" name="price" id="price" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required value="{{ old('price', $package->price) }}">
                                <p class="text-xs text-gray-500 mt-1">Harga yang harus dibayar pengguna.</p>
                                @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Weight -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700">Weight/Berat (Gram) <span class="text-red-500">*</span></label>
                                <input type="number" name="weight" id="weight" min="1" max="30000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required value="{{ old('weight', $package->weight) }}">
                                <p class="text-xs text-gray-500 mt-1">Berat paket untuk hitung ongkir (Min 1g).</p>
                                @error('weight') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Commission Settings -->
                        <div class="mb-4 border-t pt-4 mt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Komisi Referral</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="commission_type" class="block text-sm font-medium text-gray-700">Tipe Komisi <span class="text-red-500">*</span></label>
                                    <select name="commission_type" id="commission_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="fixed" {{ old('commission_type', $package->commission_type) == 'fixed' ? 'selected' : '' }}>Nilai Tetap (IDR)</option>
                                        <option value="percentage" {{ old('commission_type', $package->commission_type) == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                    </select>
                                    @error('commission_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="commission_value" class="block text-sm font-medium text-gray-700">Nilai Komisi <span class="text-red-500">*</span></label>
                                    <input type="number" name="commission_value" id="commission_value" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required value="{{ old('commission_value', $package->commission_value) }}">
                                    <p class="text-xs text-gray-500 mt-1">Jika %, masukkan 10 untuk 10%. Jika IDR, masukkan nominal.</p>
                                    @error('commission_value') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="mb-4">
                            <label for="duration_days" class="block text-sm font-medium text-gray-700">Durasi (Hari) <span class="text-red-500">*</span></label>
                            <input type="number" name="duration_days" id="duration_days" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required value="{{ old('duration_days', $package->duration_days) }}">
                            @error('duration_days') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Benefits -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fitur / Keuntungan</label>
                            <template x-for="(benefit, index) in benefits" :key="index">
                                <div class="flex items-center mb-2">
                                    <input type="text" :name="'benefits[' + index + ']'" x-model="benefits[index]" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: Akses Video Premium">
                                    <button type="button" @click="removeBenefit(index)" class="ml-2 text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="addBenefit()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Tambah Fitur
                            </button>
                            @error('benefits') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Additional Products -->
                        <div class="mb-6 border-t pt-4 mt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Produk Tambahan (Bundling)</h3>
                            <p class="text-sm text-gray-500 mb-4">Pilih produk yang akan disertakan dalam paket ini. Harga total paket akan otomatis bertambah sesuai harga produk.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto p-2 border rounded-md bg-gray-50">
                                <template x-for="(product, index) in availableProducts" :key="product.id">
                                    <div class="border rounded-lg p-4 bg-white shadow-sm transition-all duration-200 hover:shadow-md"
                                         :class="product.selected ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0 pt-1">
                                                    <input type="checkbox" :id="'prod_' + product.id" x-model="product.selected"
                                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 h-5 w-5">
                                                </div>
                                                <label :for="'prod_' + product.id" class="cursor-pointer select-none">
                                                    <p class="font-semibold text-gray-900 text-sm" x-text="product.name"></p>
                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                        SKU: <span x-text="product.sku"></span>
                                                    </p>
                                                    <p class="text-xs font-medium text-blue-600 mt-1">
                                                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(product.price_silverchannel)"></span>
                                                    </p>
                                                </label>
                                            </div>
                                            <div class="text-xs text-gray-400 font-mono bg-gray-100 px-2 py-1 rounded">
                                                Stok: <span x-text="product.stock"></span>
                                            </div>
                                        </div>
                                        
                                        <div x-show="product.selected" x-transition class="pt-3 border-t border-gray-100 mt-2">
                                            <div class="flex items-center justify-between">
                                                <label class="text-xs font-medium text-gray-700">Jumlah (Qty):</label>
                                                <input type="number" x-model="product.quantity" min="1"
                                                       class="w-20 text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1 px-2 text-right">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="availableProducts.length === 0" class="text-center py-8 text-gray-500">
                                Tidak ada produk aktif yang tersedia.
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="mb-6">
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktifkan Paket Ini</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('admin.packages.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" :disabled="isLoading" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="benefits-data">
        {!! json_encode(old('benefits', $package->benefits ?? [''])) !!}
    </script>

    <script type="application/json" id="products-data">
        {!! json_encode($products->map(function($p) use ($package) {
            $existing = $package->products->find($p->id);
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'price_silverchannel' => $p->price_silverchannel,
                'stock' => $p->stock,
                'selected' => $existing ? true : false,
                'quantity' => $existing ? $existing->pivot->quantity : 1
            ];
        })) !!}
    </script>

    <script>
        function imageUpload() {
            return {
                preview: null,
                error: null,
                validateAndPreview(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Size validation (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        this.error = 'Ukuran file maksimal 2MB.';
                        event.target.value = '';
                        this.preview = null;
                        return;
                    }

                    const img = new Image();
                    img.onload = () => {
                        const ratio = img.width / img.height;
                        // 1:1 = 1.0, 3:4 = 0.75
                        const is1x1 = Math.abs(ratio - 1) < 0.05;
                        const is3x4 = Math.abs(ratio - 0.75) < 0.05;

                        if (!is1x1 && !is3x4) {
                            this.error = 'Rasio aspek gambar harus 1:1 atau 3:4.';
                            event.target.value = '';
                            this.preview = null;
                        } else {
                            this.error = null;
                            this.preview = img.src;
                        }
                    };
                    img.onerror = () => {
                        this.error = 'File bukan gambar yang valid.';
                        event.target.value = '';
                        this.preview = null;
                    };
                    img.src = URL.createObjectURL(file);
                }
            }
        }

        function packageForm() {
            return {
                benefits: JSON.parse(document.getElementById('benefits-data').textContent),
                availableProducts: JSON.parse(document.getElementById('products-data').textContent),
                isLoading: false,
                errorMessage: '',
                successMessage: '',
                
                addBenefit() {
                    this.benefits.push('');
                },
                removeBenefit(index) {
                    this.benefits.splice(index, 1);
                },
                submitForm(event) {
                    this.isLoading = true;
                    this.errorMessage = '';
                    this.successMessage = '';

                    const formData = new FormData(event.target);
                    
                    // Manually append selected products
                    let pIndex = 0;
                    this.availableProducts.forEach(p => {
                        if (p.selected) {
                            formData.append(`products[${pIndex}][id]`, p.id);
                            formData.append(`products[${pIndex}][quantity]`, p.quantity);
                            pIndex++;
                        }
                    });

                    fetch(event.target.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');
                        
                        if (response.redirected) {
                            window.location.href = response.url;
                            return;
                        }

                        if (contentType && contentType.includes('application/json')) {
                            const data = await response.json();
                            if (!response.ok) {
                                throw data;
                            }

                            if (data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            }

                            this.successMessage = data.message || 'Perubahan berhasil disimpan.';
                            window.scrollTo(0, 0);
                        } else {
                            // Assume error if HTML returned without redirect
                            throw new Error('Terjadi kesalahan pada server (Respon tidak valid).');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.isLoading = false;
                        if (error.errors) {
                             this.errorMessage = Object.values(error.errors).flat().join(', ');
                        } else {
                             this.errorMessage = error.message || 'Gagal menyimpan perubahan.';
                        }
                        window.scrollTo(0, 0);
                    });
                }
            }
        }
    </script>
</x-app-layout>