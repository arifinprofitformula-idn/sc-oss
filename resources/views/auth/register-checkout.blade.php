@extends('layouts.guest')

@section('content')
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gray-900">
        <!-- Logo -->
        <div class="mb-8 flex justify-center">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo class="w-20 h-20 fill-current text-cyan-400" />
            </a>
        </div>

        <div class="max-w-4xl mx-auto">
            @if ($errors->any())
                <div class="mb-6 bg-red-900/50 border border-red-500/50 text-red-200 p-4 rounded-xl shadow-lg backdrop-blur-sm">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-bold">Terjadi Kesalahan</p>
                            <ul class="list-disc list-inside text-sm mt-1 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Order Summary -->
                <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6 sm:p-8">
                    <div class="flex items-center space-x-3 mb-6 border-b border-gray-700 pb-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold border border-cyan-500/50">1</div>
                        <h2 class="text-xl font-bold text-white">{{ __('Konfirmasi Pesanan') }}</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-400 text-sm">Nama Lengkap</p>
                            <p class="text-white font-medium">{{ $data['name'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Email</p>
                            <p class="text-white font-medium">{{ $data['email'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">WhatsApp</p>
                            <p class="text-white font-medium">{{ $data['whatsapp'] }}</p>
                        </div>
                         <div>
                            <p class="text-gray-400 text-sm">Alamat Pengiriman</p>
                            <p class="text-white font-medium">
                                {{ $data['address'] ?? '-' }}<br>
                                @if(!empty($data['subdistrict_name']))
                                    {{ $data['subdistrict_name'] }}, 
                                @endif
                                {{ $data['city_name'] }}, {{ $data['province_name'] }}
                                @if(!empty($data['postal_code']))
                                    <br>Kode Pos: {{ $data['postal_code'] }}
                                @endif
                            </p>
                        </div>
                        <div class="pt-2">
                            <p class="text-gray-400 text-sm">Paket Dipilih</p>
                            <p class="text-cyan-400 font-bold text-lg">{{ $package->name }}</p>
                        </div>

                        <div class="pt-2 border-t border-gray-700/50">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-gray-400 text-sm">Harga Paket Dasar</span>
                                <span class="text-white font-medium">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                            </div>
                            @if($package->products_total > 0)
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-gray-400 text-sm">Produk Tambahan (Bundling)</span>
                                    <span class="text-white font-medium">Rp {{ number_format($package->products_total, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-gray-400 text-sm">Ongkos Kirim ({{ strtoupper($data['shipping_courier'] ?? '') }} - {{ strtoupper($data['shipping_service'] ?? '-') }})</span>
                                <span class="text-white font-medium">Rp {{ number_format($data['shipping_cost'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            @if(($data['packing_fee'] ?? 0) > 0)
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-gray-400 text-sm">Biaya Packing</span>
                                    <span class="text-white font-medium">Rp {{ number_format($data['packing_fee'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($package->insurance_cost > 0)
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-gray-400 text-sm">Asuransi Pengiriman (LM)</span>
                                    <span class="text-white font-medium">Rp {{ number_format($package->insurance_cost, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="pt-4 border-t border-gray-700 flex justify-between items-center" x-data="{ copied: false }">
                            <span class="text-gray-300">Total Pembayaran</span>
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-bold text-white">Rp {{ number_format($package->base_total + $package->insurance_cost + ($data['shipping_cost'] ?? 0) + ($data['packing_fee'] ?? 0), 0, ',', '.') }}</span>
                                <div class="relative">
                                    <button @click="copyToClipboard('{{ $package->base_total + $package->insurance_cost + ($data['shipping_cost'] ?? 0) + ($data['packing_fee'] ?? 0) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })" 
                                            class="text-gray-400 hover:text-cyan-400 transition-colors p-2 hover:bg-gray-800 rounded-lg group" 
                                            title="Salin Nominal">
                                        <template x-if="!copied">
                                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </template>
                                        <template x-if="copied">
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </template>
                                    </button>
                                    <div x-show="copied" 
                                         style="display: none;"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-2"
                                         class="absolute bottom-full right-0 mb-2 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded shadow-lg whitespace-nowrap z-10">
                                        Berhasil disalin!
                                        <div class="absolute -bottom-1 right-3 w-2 h-2 bg-green-500 rotate-45"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions & Upload -->
                <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6 sm:p-8">
                    <div class="flex items-center space-x-3 mb-6 border-b border-gray-700 pb-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold border border-cyan-500/50">2</div>
                        <h2 class="text-xl font-bold text-white">{{ __('Pembayaran & Aktivasi') }}</h2>
                    </div>
                    
                    <div class="mb-6 bg-blue-900/30 border border-blue-800 rounded-xl p-4">
                        <p class="text-gray-300 text-sm mb-2">Silakan transfer ke rekening berikut:</p>
                        @if(!empty($banks) && is_array($banks))
                            @foreach($banks as $bank)
                                <div class="mb-6 border-b border-gray-700 pb-6 last:border-0 last:pb-0 last:mb-0">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            @if(!empty($bank['logo']))
                                                <div class="bg-white p-2 rounded-lg shadow-sm">
                                                    <img src="{{ \Illuminate\Support\Str::startsWith($bank['logo'], ['http', '/']) ? $bank['logo'] : Storage::url($bank['logo']) }}" 
                                                         alt="{{ $bank['bank'] ?? 'Bank' }}" 
                                                         class="h-8 w-auto object-contain">
                                                </div>
                                            @endif
                                            <span class="text-cyan-400 font-bold text-xl">{{ $bank['bank'] ?? 'Bank' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mb-2" x-data="{ copied: false }">
                                        <span class="text-gray-400">No. Rekening</span>
                                        <div class="flex items-center gap-2 relative">
                                            <span class="text-white font-bold text-lg tracking-wider">{{ $bank['number'] ?? '-' }}</span>
                                            <div class="relative">
                                                <button @click="copyToClipboard('{{ $bank['number'] ?? '' }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })" 
                                                        class="text-gray-500 hover:text-cyan-400 transition-colors p-2 hover:bg-gray-800 rounded-lg group" 
                                                        title="Salin No. Rekening">
                                                    <template x-if="!copied">
                                                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                    </template>
                                                    <template x-if="copied">
                                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    </template>
                                                </button>
                                                <div x-show="copied" 
                                                     style="display: none;"
                                                     x-transition:enter="transition ease-out duration-200"
                                                     x-transition:enter-start="opacity-0 translate-y-2"
                                                     x-transition:enter-end="opacity-100 translate-y-0"
                                                     x-transition:leave="transition ease-in duration-150"
                                                     x-transition:leave-start="opacity-100 translate-y-0"
                                                     x-transition:leave-end="opacity-0 translate-y-2"
                                                     class="absolute bottom-full right-0 mb-2 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded shadow-lg whitespace-nowrap z-10">
                                                    Berhasil disalin!
                                                    <div class="absolute -bottom-1 right-3 w-2 h-2 bg-green-500 rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-400">Atas Nama</span>
                                        <span class="text-white font-bold">{{ $bank['name'] ?? '-' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-gray-400 py-4">
                                Informasi rekening belum tersedia. Silakan hubungi admin.
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('register.silver.payment', ['token' => $token]) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <div>
                            <x-input-label for="payment_proof" :value="__('Upload Bukti Transfer')" class="text-gray-300 font-medium mb-2" />
                            <div class="relative border-2 border-dashed border-gray-600 rounded-lg p-6 hover:border-cyan-500 transition-colors">
                                <input type="file" id="payment_proof" name="payment_proof" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" required onchange="previewImage(this)">
                                <div class="text-center" id="upload-placeholder">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p class="mt-1 text-sm text-gray-400">Klik atau drag file gambar kesini</p>
                                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, JPEG (Max 2MB)</p>
                                </div>
                                <div id="image-preview" class="hidden text-center">
                                    <img src="" alt="Preview" class="mx-auto max-h-48 rounded-lg shadow-lg">
                                    <p id="file-name" class="mt-2 text-sm text-cyan-400 font-medium"></p>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('payment_proof')" class="mt-2 text-red-400" />
                        </div>

                        <div class="flex justify-between pt-4">
                            <a href="{{ route('register.silver') }}" class="text-gray-400 hover:text-white transition-colors py-2">
                                &larr; Kembali
                            </a>
                            <button 
                                type="submit" 
                                class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 focus:ring-offset-gray-900 shadow-lg hover:shadow-green-500/25"
                            >
                                {{ __('Konfirmasi Pembayaran') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global copy function with fallback for HTTP
        window.copyToClipboard = function(text) {
            return new Promise((resolve, reject) => {
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(resolve).catch(() => {
                        // If writeText fails, try fallback
                        fallbackCopy(text, resolve, reject);
                    });
                } else {
                    fallbackCopy(text, resolve, reject);
                }
            });
        }

        function fallbackCopy(text, resolve, reject) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    resolve();
                } else {
                    reject(new Error('Copy failed'));
                }
            } catch (err) {
                reject(err);
            }
            
            document.body.removeChild(textArea);
        }

        function previewImage(input) {
            const placeholder = document.getElementById('upload-placeholder');
            const preview = document.getElementById('image-preview');
            const previewImg = preview.querySelector('img');
            const fileName = document.getElementById('file-name');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    placeholder.classList.add('hidden');
                    preview.classList.remove('hidden');
                    fileName.textContent = input.files[0].name;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
