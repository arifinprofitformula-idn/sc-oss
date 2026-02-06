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
            {{ __('Import Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Header & Back Button -->
                    <div class="mb-6 flex justify-between items-center">
                        <h3 class="text-lg font-medium">
                            @if(isset($hasPending) && $hasPending)
                                Preview Data Import
                            @else
                                Upload File Import
                            @endif
                        </h3>
                        <a href="{{ route('admin.products.index') }}" class="btn-3d btn-3d-gray shimmer px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">
                            Kembali
                        </a>
                    </div>

                    <!-- Flash Messages / Results -->
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                            <strong class="font-bold">Terjadi Kesalahan!</strong>
                            <ul class="list-disc list-inside mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('import_result'))
                        <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <h4 class="font-bold text-lg mb-2">Hasil Import</h4>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="p-3 bg-green-100 dark:bg-green-900 rounded border border-green-200 dark:border-green-800">
                                    <span class="block text-sm text-green-800 dark:text-green-200">Berhasil</span>
                                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ session('import_result')['count'] ?? 0 }}</span>
                                </div>
                                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded border border-blue-200 dark:border-blue-800">
                                    <span class="block text-sm text-blue-800 dark:text-blue-200">Total Baris</span>
                                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ session('import_result')['total_rows'] ?? '-' }}</span>
                                </div>
                            </div>
                             @if(session('import_result')['message'])
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ session('import_result')['message'] }}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(isset($hasPending) && $hasPending)
                        <!-- PREVIEW STATE -->
                        <div class="space-y-6">
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-md border border-yellow-200 dark:border-yellow-800">
                                <p class="text-yellow-800 dark:text-yellow-200 text-sm">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Ini adalah preview 5 baris pertama dari file Anda. Pastikan kolom terbaca dengan benar sebelum memproses import.
                                </p>
                            </div>

                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            @foreach($headers as $header)
                                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                    {{ $header }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($preview_data as $row)
                                            <tr>
                                                @foreach($headers as $header)
                                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                                        {{ is_array($row) ? ($row[$header] ?? '-') : '-' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <form action="{{ route('admin.products.import.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="update_existing" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Update data jika SKU sudah ada</span>
                                        </label>
                                    </div>
                                    <button type="submit" class="btn-3d btn-3d-blue shimmer px-6 py-2 rounded-md font-semibold">
                                        Proses Import
                                    </button>
                                </form>

                                <a href="{{ route('admin.products.import.cancel') }}" class="btn-3d btn-3d-gray shimmer px-6 py-2 rounded-md font-semibold">
                                    Batal
                                </a>
                            </div>
                        </div>

                    @else
                        <!-- UPLOAD STATE -->
                        <div class="space-y-6">
                            
                            <!-- Download Template Section -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg border border-blue-200 dark:border-blue-800">
                                <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-300 mb-2">Langkah 1: Siapkan Data</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Unduh template XLSX berikut untuk memastikan format data Anda sesuai dengan sistem.
                                </p>
                                <a href="{{ route('admin.products.import.template') }}" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 text-sm font-medium rounded-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download Template XLSX
                                </a>

                                <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-800">
                                    <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-2">Kolom Utama:</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600 dark:text-gray-400">
                                        <div>
                                            <ul class="list-disc pl-4 mt-1 space-y-1">
                                                <li>SKU (Unik)</li>
                                                <li>Nama Produk</li>
                                                <li>Brand</li>
                                                <li>Kategori</li>
                                                <li>Harga (Silverchannel)</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <ul class="list-disc pl-4 mt-1 space-y-1">
                                                <li>MSRP (Harga Pasaran)</li>
                                                <li>Berat (gram)</li>
                                                <li>Stok</li>
                                                <li>Deskripsi</li>
                                                <li>URL Gambar</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Form -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-lg border border-gray-200 dark:border-gray-700"
                                x-data="{
                                    fileName: null,
                                    fileSize: null,
                                    isValid: false,
                                    errorMsg: null,
                                    isDragging: false,
                                    
                                    handleFile(event) {
                                        const file = event.target.files[0];
                                        this.processFile(file);
                                    },
                                    
                                    handleDrop(event) {
                                        this.isDragging = false;
                                        const file = event.dataTransfer.files[0];
                                        
                                        // Update input file manually for form submission
                                        const dataTransfer = new DataTransfer();
                                        dataTransfer.items.add(file);
                                        document.getElementById('file').files = dataTransfer.files;
                                        
                                        this.processFile(file);
                                    },
                                    
                                    processFile(file) {
                                        if (!file) {
                                            this.reset();
                                            return;
                                        }
                                        
                                        this.fileName = file.name;
                                        
                                        // Basic validation
                                        const validTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain'];
                                        const validExts = ['.xlsx', '.xls', '.csv'];
                                        const fileExt = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
                                        
                                        if (!validExts.includes(fileExt)) {
                                            this.errorMsg = 'Format file harus XLSX, XLS, atau CSV.';
                                            this.isValid = false;
                                        } else if (file.size > 10 * 1024 * 1024) { // 10MB
                                            this.errorMsg = 'Ukuran file maksimal 10MB.';
                                            this.isValid = false;
                                        } else {
                                            this.errorMsg = null;
                                            this.isValid = true;
                                            this.fileSize = (file.size / 1024).toFixed(2) + ' KB';
                                        }
                                    },
                                    
                                    reset() {
                                        this.fileName = null;
                                        this.fileSize = null;
                                        this.isValid = false;
                                        this.errorMsg = null;
                                        document.getElementById('file').value = '';
                                    }
                                }">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Langkah 2: Upload File</h4>
                                
                                <form action="{{ route('admin.products.import.preview') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="relative border-2 border-dashed rounded-lg p-8 text-center transition-colors duration-200"
                                         :class="{'border-blue-500 bg-blue-50 dark:bg-blue-900/10': isDragging, 'border-gray-300 dark:border-gray-600 hover:border-blue-400': !isDragging, 'border-red-500 bg-red-50': errorMsg}"
                                         @dragover.prevent="isDragging = true"
                                         @dragleave.prevent="isDragging = false"
                                         @drop.prevent="handleDrop($event)">
                                        
                                        <input type="file" name="file" id="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                               accept=".xlsx, .xls, .csv"
                                               @change="handleFile($event)" required>
                                        
                                        <div class="space-y-2 pointer-events-none">
                                            <div x-show="!fileName">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center mt-2">
                                                    <span class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                        <span>Upload file</span>
                                                    </span>
                                                    <p class="pl-1">atau drag and drop</p>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">XLSX, XLS, CSV hingga 10MB</p>
                                            </div>

                                            <div x-show="fileName" class="text-center">
                                                <div x-show="!errorMsg">
                                                    <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="mt-2 text-sm text-gray-900 dark:text-gray-100 font-medium" x-text="fileName"></p>
                                                    <p class="text-xs text-gray-500" x-text="fileSize"></p>
                                                </div>
                                                <div x-show="errorMsg">
                                                    <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p class="mt-2 text-sm text-red-600 font-medium" x-text="errorMsg"></p>
                                                    <button type="button" @click="reset()" class="mt-2 text-xs text-blue-600 hover:text-blue-500 underline pointer-events-auto">Pilih file lain</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <button type="submit" 
                                                class="btn-3d btn-3d-blue shimmer px-6 py-2 rounded-md font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                                :disabled="!isValid">
                                            Preview & Validasi
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
