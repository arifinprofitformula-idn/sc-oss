<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Konfirmasi Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Flash Message -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <!-- Order Summary Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">Order #{{ $order->order_number }}</h3>
                            <p class="text-sm text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Tagihan</p>
                            <p class="text-2xl font-bold text-blue-600">IDR {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    @if($order->unique_code > 0)
                        <div class="mt-2 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Kode Unik: {{ $order->unique_code }}
                            </span>
                        </div>
                    @endif

                    @if($order->expires_at)
                         <p class="text-sm text-red-500 mt-2 font-medium text-right">Batas Pembayaran: {{ \Carbon\Carbon::parse($order->expires_at)->format('d M Y H:i') }}</p>
                    @endif
                </div>

                <form action="{{ route('payment.process', $order) }}" method="POST" enctype="multipart/form-data" x-data="paymentForm()" @submit="submitHandler($event)">
                    @csrf
                    
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Pilih Metode Pembayaran</label>
                        <div class="space-y-3">
                            <!-- Manual Transfer -->
                            <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                                :class="{ 'border-blue-500 ring-2 ring-blue-500 bg-blue-50 dark:bg-gray-700': method === 'manual' }">
                                <input type="radio" name="payment_method" value="manual" class="h-4 w-4 text-blue-600 focus:ring-blue-500" x-model="method">
                                <div class="ml-4 flex-1">
                                    <span class="block text-sm font-bold text-gray-900 dark:text-gray-100">Transfer Bank Manual</span>
                                    <span class="block text-xs text-gray-500 mt-1">Transfer ke rekening berikut dan upload bukti transfer.</span>
                                </div>
                                <svg x-show="method === 'manual'" class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </label>

                            <!-- Midtrans (Disabled/Optional based on config) -->
                            <!--
                            <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                                :class="{ 'border-blue-500 ring-2 ring-blue-500 bg-blue-50 dark:bg-gray-700': method === 'midtrans' }">
                                <input type="radio" name="payment_method" value="midtrans" class="h-4 w-4 text-blue-600 focus:ring-blue-500" x-model="method">
                                <div class="ml-4 flex-1">
                                    <span class="block text-sm font-bold text-gray-900 dark:text-gray-100">Pembayaran Online Otomatis</span>
                                    <span class="block text-xs text-gray-500 mt-1">Virtual Account, Kartu Kredit, QRIS (Verifikasi Otomatis).</span>
                                </div>
                                <svg x-show="method === 'midtrans'" class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </label>
                            -->
                        </div>
                    </div>

                    <!-- Manual Transfer Details & Upload -->
                    <div x-show="method === 'manual'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="mb-8">
                        
                        <!-- Bank Info Card -->
                        <div class="space-y-4 mb-8">
                            @forelse($banks as $bank)
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 flex flex-col md:flex-row items-center gap-6">
                                    <!-- Bank Logo -->
                                    <div class="flex-shrink-0">
                                        @if(!empty($bank['logo']))
                                            <div class="w-24 h-16 bg-white rounded-lg border border-gray-200 flex items-center justify-center p-2 overflow-hidden">
                                                @php
                                                    $logoSrc = $bank['logo'];
                                                    // Handle full URLs or paths already containing storage
                                                    if (!filter_var($logoSrc, FILTER_VALIDATE_URL) && !str_contains($logoSrc, '/storage/')) {
                                                        $logoSrc = Storage::url($logoSrc);
                                                    }
                                                @endphp
                                                <img src="{{ $logoSrc }}" alt="{{ $bank['bank_name'] }}" class="w-full h-full object-contain">
                                            </div>
                                        @else
                                            <div class="w-24 h-16 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-sm">
                                                {{ strtoupper(substr($bank['bank_name'], 0, 4)) }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Account Details -->
                                    <div class="flex-grow text-left">
                                        <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold mb-1">{{ $bank['bank_name'] }}</p>
                                        <div class="flex items-center justify-start gap-2 mb-1">
                                            <p class="font-mono text-2xl font-bold tracking-wider text-gray-900 dark:text-gray-100" id="rek-{{ $loop->index }}">
                                                {{ $bank['account_number'] }}
                                            </p>
                                            <button type="button" 
                                                @click="copyToClipboard('{{ $bank['account_number'] }}', 'btn-copy-{{ $loop->index }}')" 
                                                class="text-gray-400 hover:text-blue-600 transition-colors p-1"
                                                title="Salin No. Rekening">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                            <!-- Success Indicator -->
                                            <span x-show="copiedIndex === 'btn-copy-{{ $loop->index }}'" 
                                                  x-transition 
                                                  class="text-xs text-green-600 font-bold px-2 py-0.5 bg-green-100 rounded">
                                                Disalin!
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">a.n {{ $bank['account_name'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-6 border border-yellow-200 dark:border-yellow-800 text-center">
                                    <p class="text-yellow-800 dark:text-yellow-200 font-medium">Belum ada informasi rekening bank yang diatur.</p>
                                    <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">Silakan hubungi admin untuk instruksi pembayaran.</p>
                                </div>
                            @endforelse

                            <div class="text-xs text-gray-500 bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800 flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <span class="font-bold text-gray-700 dark:text-gray-300">PENTING:</span> 
                                    Pastikan nominal transfer sesuai tagihan untuk mempercepat verifikasi otomatis.
                                </div>
                            </div>
                        </div>
                        
                        <!-- File Upload -->
                        <div>
                            <x-input-label for="proof_file" :value="__('Upload Bukti Transfer')" class="mb-2" />
                            
                            <!-- Custom Upload UI -->
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:border-blue-500 transition-colors relative" 
                                :class="{'border-blue-500 bg-blue-50 dark:bg-blue-900/10': isDragOver}"
                                @dragover.prevent="isDragOver = true"
                                @dragleave.prevent="isDragOver = false"
                                @drop.prevent="isDragOver = false; handleDrop($event)">
                                
                                <div class="space-y-1 text-center" x-show="!previewUrl">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                        <label for="proof_file" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload file</span>
                                            <input id="proof_file" name="proof_file" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf" @change="handleFileSelect" :required="method === 'manual'">
                                        </label>
                                        <p class="pl-1">atau drag & drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, PDF up to 2MB</p>
                                </div>

                                <!-- Preview -->
                                <div x-show="previewUrl" class="text-center w-full relative">
                                    <div class="relative inline-block">
                                        <template x-if="fileType && fileType.startsWith('image/')">
                                            <img :src="previewUrl" class="max-h-64 rounded-lg shadow-md mx-auto">
                                        </template>
                                        <template x-if="fileType === 'application/pdf'">
                                            <div class="flex flex-col items-center justify-center h-48 bg-gray-100 rounded-lg w-full p-4">
                                                <svg class="w-16 h-16 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" /><path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" /></svg>
                                                <span class="mt-2 text-sm font-medium text-gray-900" x-text="fileName"></span>
                                            </div>
                                        </template>
                                        <button type="button" @click="clearFile" class="absolute -top-3 -right-3 bg-red-500 text-white rounded-full p-1 shadow-lg hover:bg-red-600 focus:outline-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <p class="text-sm text-green-600 mt-2 font-medium">File siap diupload</p>
                                </div>

                            </div>
                            <x-input-error :messages="$errors->get('proof_file')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                        <style>
                            /* From Login Page & Products Page */
                            .button-shine { 
                                position: relative; 
                                transition: all 0.3s ease-in-out; 
                                box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2); 
                                padding-block: 0.5rem; 
                                padding-inline: 1.25rem; 
                                background: linear-gradient(to right, #06b6d4, #2563eb); /* cyan-500 to blue-600 */
                                border-radius: 6px; 
                                display: flex; 
                                align-items: center; 
                                justify-content: center; 
                                color: #ffff; 
                                gap: 10px; 
                                font-weight: bold; 
                                border: 3px solid #ffffff4d; 
                                outline: none; 
                                overflow: hidden; 
                                font-size: 15px; 
                                cursor: pointer; 
                            } 
                            .button-shine:disabled {
                                opacity: 0.7;
                                cursor: not-allowed;
                                background: #9ca3af; /* Gray-400 fallback */
                            }
                            .button-shine:not(:disabled):hover { 
                                transform: scale(1.05); 
                                border-color: #fff9; 
                            } 
                            .button-shine:not(:disabled):hover::before { 
                                animation: shine 1.5s ease-out infinite; 
                            } 
                            .button-shine::before { 
                                content: ""; 
                                position: absolute; 
                                width: 100px; 
                                height: 100%; 
                                background-image: linear-gradient( 
                                    120deg, 
                                    rgba(255, 255, 255, 0) 30%, 
                                    rgba(255, 255, 255, 0.8), 
                                    rgba(255, 255, 255, 0) 70% 
                                ); 
                                top: 0; 
                                left: -100px; 
                                opacity: 0.6; 
                            } 
                            @keyframes shine { 
                                0% { left: -100px; } 
                                60% { left: 100%; } 
                                to { left: 100%; } 
                            } 
                        </style>
                        <a href="{{ route('silverchannel.orders.show', $order) }}" class="mr-4 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 flex items-center">
                            Batal
                        </a>
                        <button type="submit" class="button-shine ml-3" :disabled="processing">
                            <span x-show="!processing">{{ __('Kirim Konfirmasi Pembayaran') }}</span>
                            <span x-show="processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function paymentForm() {
            return {
                method: 'manual',
                isDragOver: false,
                previewUrl: null,
                fileName: null,
                fileType: null,
                processing: false,
                copiedIndex: null, // Track which copy button was clicked
                
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    this.processFile(file);
                },
                
                copyToClipboard(text, btnId) {
                    // Fallback for non-secure contexts (http)
                    if (!navigator.clipboard) {
                        const textArea = document.createElement("textarea");
                        textArea.value = text;
                        textArea.style.position = "fixed";  // Avoid scrolling to bottom
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        try {
                            document.execCommand('copy');
                            this.showCopyFeedback(btnId);
                        } catch (err) {
                            console.error('Fallback: Oops, unable to copy', err);
                        }
                        document.body.removeChild(textArea);
                        return;
                    }

                    // Modern Async Clipboard API
                    navigator.clipboard.writeText(text).then(() => {
                        this.showCopyFeedback(btnId);
                    }, (err) => {
                        console.error('Async: Could not copy text: ', err);
                    });
                },

                showCopyFeedback(btnId) {
                    this.copiedIndex = btnId;
                    setTimeout(() => {
                        this.copiedIndex = null;
                    }, 2000);
                },
                
                handleDrop(event) {
                    const file = event.dataTransfer.files[0];
                    // Update input manually
                    const input = document.getElementById('proof_file');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                    
                    this.processFile(file);
                },
                
                processFile(file) {
                    if (!file) return;
                    
                    // Validate size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal 2MB');
                        this.clearFile();
                        return;
                    }

                    // Validate type
                    if (!['image/jpeg', 'image/png', 'application/pdf'].includes(file.type)) {
                        alert('Format file harus JPG, PNG, atau PDF');
                        this.clearFile();
                        return;
                    }

                    this.fileName = file.name;
                    this.fileType = file.type;
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previewUrl = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.previewUrl = 'pdf'; // Marker for PDF
                    }
                },
                
                clearFile() {
                    this.previewUrl = null;
                    this.fileName = null;
                    this.fileType = null;
                    document.getElementById('proof_file').value = '';
                },

                submitHandler(event) {
                    if (this.method === 'manual') {
                        const fileInput = document.getElementById('proof_file');
                        if (!fileInput.files || fileInput.files.length === 0) {
                            alert('Silakan upload bukti pembayaran terlebih dahulu.');
                            event.preventDefault();
                            return;
                        }
                    }
                    
                    // Show loading state only if validation passes
                    this.processing = true;
                }
            }
        }
    </script>
</x-app-layout>
