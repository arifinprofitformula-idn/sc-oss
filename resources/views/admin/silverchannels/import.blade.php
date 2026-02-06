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
            {{ __('Import Silverchannels') }}
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
                                Upload File CSV
                            @endif
                        </h3>
                        <a href="{{ route('admin.silverchannels.index') }}" class="btn-3d btn-3d-gray shimmer px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">
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
                                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ session('import_result')['success_count'] }}</span>
                                </div>
                                <div class="p-3 bg-red-100 dark:bg-red-900 rounded border border-red-200 dark:border-red-800">
                                    <span class="block text-sm text-red-800 dark:text-red-200">Gagal</span>
                                    <span class="text-2xl font-bold text-red-600 dark:text-red-400">{{ session('import_result')['failed_count'] }}</span>
                                </div>
                            </div>

                            @if(!empty(session('import_result')['errors']))
                                <div class="mt-4">
                                    <h5 class="font-bold text-red-600 dark:text-red-400 mb-2">Detail Error:</h5>
                                    <div class="overflow-x-auto max-h-60 overflow-y-auto">
                                        <table class="min-w-full text-xs">
                                            <thead>
                                                <tr class="bg-gray-200 dark:bg-gray-600 sticky top-0">
                                                    <th class="px-2 py-1 text-left">Row</th>
                                                    <th class="px-2 py-1 text-left">Error</th>
                                                    <th class="px-2 py-1 text-left">Data</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(session('import_result')['errors'] as $error)
                                                    <tr class="border-b border-gray-200 dark:border-gray-600">
                                                        <td class="px-2 py-1">{{ $error['row'] }}</td>
                                                        <td class="px-2 py-1 text-red-500">
                                                            <ul class="list-disc pl-4">
                                                                @foreach($error['errors'] as $msg)
                                                                    <li>{{ $msg }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </td>
                                                        <td class="px-2 py-1 font-mono break-all">{{ json_encode($error['data']) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
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
                                    Ini adalah preview 5 baris pertama dari file CSV Anda. Pastikan kolom terbaca dengan benar sebelum memproses import.
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
                                                        {{ $row[$header] ?? '-' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <form action="{{ route('admin.silverchannels.import.process') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-3d btn-3d-blue shimmer px-6 py-2 rounded-md font-semibold">
                                        Proses Import
                                    </button>
                                </form>

                                <a href="{{ route('admin.silverchannels.import.cancel') }}" class="btn-3d btn-3d-gray shimmer px-6 py-2 rounded-md font-semibold">
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
                                    Unduh template CSV berikut untuk memastikan format data Anda sesuai dengan sistem.
                                    Template sudah mencakup kolom untuk data dasar, kependudukan, dan kontak.
                                </p>
                                <a href="{{ route('admin.silverchannels.import.template') }}" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 text-sm font-medium rounded-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download Template CSV
                                </a>

                                <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-800">
                                    <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-2">Kolom yang tersedia:</p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-600 dark:text-gray-400">
                                        <div>
                                            <strong class="block text-blue-700 dark:text-blue-400">Data Akun & Dasar</strong>
                                            <ul class="list-disc pl-4 mt-1 space-y-1">
                                                <li>id_silverchannel (Unik)</li>
                                                <li>nama_channel</li>
                                                <li>email (Login)</li>
                                                <li>telepon</li>
                                                <li>password (Opsional, default: 12345678)</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <strong class="block text-blue-700 dark:text-blue-400">Data Profil</strong>
                                            <ul class="list-disc pl-4 mt-1 space-y-1">
                                                <li>nik</li>
                                                <li>tempat_lahir</li>
                                                <li>tanggal_lahir (YYYY-MM-DD)</li>
                                                <li>jenis_kelamin (L/P)</li>
                                                <li>agama</li>
                                                <li>status_perkawinan</li>
                                                <li>pekerjaan</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <strong class="block text-blue-700 dark:text-blue-400">Alamat & Bank</strong>
                                            <ul class="list-disc pl-4 mt-1 space-y-1">
                                                <li>alamat</li>
                                                <li>kota</li>
                                                <li>kode_pos</li>
                                                <li>nama_bank</li>
                                                <li>no_rekening</li>
                                                <li>pemilik_rekening</li>
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
                                        // Note: file.type might be empty or specific on some systems for CSV
                                        const validTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain', 'text/x-csv', 'application/csv', 'application/x-csv', 'text/comma-separated-values', 'text/x-comma-separated-values'];
                                        const isCsvExtension = file.name.toLowerCase().endsWith('.csv');
                                        
                                        if (!isCsvExtension && !validTypes.includes(file.type)) {
                                            this.errorMsg = 'Format file harus CSV (.csv).';
                                            this.isValid = false;
                                            return;
                                        }
                                        
                                        if (file.size > 2 * 1024 * 1024) { // 2MB
                                            this.errorMsg = 'Ukuran file melebihi batas maksimum 2MB.';
                                            this.isValid = false;
                                            return;
                                        }

                                        this.fileSize = (file.size / 1024).toFixed(2) + ' KB';
                                        this.isValid = true;
                                        this.errorMsg = null;
                                    },
                                    
                                    reset() {
                                        this.fileName = null;
                                        this.fileSize = null;
                                        this.isValid = false;
                                        this.errorMsg = null;
                                        document.getElementById('file').value = '';
                                    }
                                }">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Langkah 2: Upload & Preview</h4>
                                <form action="{{ route('admin.silverchannels.import.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    
                                    <div>
                                        <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih File CSV</label>
                                        
                                        <div class="flex items-center justify-center w-full"
                                             @dragover.prevent="isDragging = true"
                                             @dragleave.prevent="isDragging = false"
                                             @drop.prevent="handleDrop($event)">
                                            
                                            <label for="file" 
                                                   class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-lg cursor-pointer transition relative overflow-hidden"
                                                   :class="{
                                                       'border-blue-500 bg-blue-50 dark:bg-blue-900/30': isDragging,
                                                       'border-green-500 bg-green-50 dark:bg-green-900/30': isValid,
                                                       'border-red-500 bg-red-50 dark:bg-red-900/30': errorMsg,
                                                       'border-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600': !isDragging && !isValid && !errorMsg
                                                   }">
                                                
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4" x-show="!fileName">
                                                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                    </svg>
                                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">CSV only (MAX. 2MB)</p>
                                                </div>

                                                <!-- File Selected State -->
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4 w-full" x-show="fileName" style="display: none;">
                                                    <template x-if="isValid">
                                                        <div class="text-green-600 dark:text-green-400 mb-2">
                                                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <template x-if="!isValid">
                                                        <div class="text-red-600 dark:text-red-400 mb-2">
                                                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate w-full px-8" x-text="fileName"></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="fileSize" x-text="fileSize"></p>
                                                    
                                                    <p class="text-sm text-red-600 dark:text-red-400 mt-2 font-semibold" x-show="errorMsg" x-text="errorMsg"></p>
                                                    <p class="text-sm text-green-600 dark:text-green-400 mt-2 font-semibold" x-show="isValid">File siap diupload</p>

                                                    <button type="button" @click.prevent="reset()" class="mt-4 text-xs text-gray-500 underline hover:text-gray-700 dark:hover:text-gray-300">
                                                        Ganti File
                                                    </button>
                                                </div>

                                                <input id="file" name="file" type="file" accept=".csv" class="hidden" required @change="handleFile($event)" />
                                            </label>
                                        </div>
                                        @error('file')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" 
                                                :disabled="!isValid"
                                                :class="{ 'opacity-50 cursor-not-allowed': !isValid }"
                                                class="btn-3d btn-3d-blue shimmer px-6 py-2 rounded-md font-semibold text-xs uppercase tracking-widest">
                                            Upload & Preview
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