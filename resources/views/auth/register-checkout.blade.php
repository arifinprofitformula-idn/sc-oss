@extends('layouts.guest')

@section('content')
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        <div class="mb-8 flex justify-center">
            <a href="/" class="flex items-center space-x-2">
                <x-application-logo />
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
                                <span class="text-gray-400 text-sm">Harga Paket</span>
                                <span class="text-white font-medium">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-gray-400 text-sm">Ongkos Kirim ({{ strtoupper($data['shipping_courier'] ?? '') }} - {{ strtoupper($data['shipping_service'] ?? '-') }})</span>
                                <span class="text-white font-medium">Rp {{ number_format($data['shipping_cost'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-700 flex justify-between items-center">
                            <span class="text-gray-300">Total Pembayaran</span>
                            <span class="text-2xl font-bold text-white">Rp {{ number_format($package->price + ($data['shipping_cost'] ?? 0), 0, ',', '.') }}</span>
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
                        @if($bankDetails)
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-400">Bank</span>
                                <span class="text-white font-bold">{{ $bankDetails->bank_name ?? 'BCA' }}</span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-400">No. Rekening</span>
                                <span class="text-white font-bold text-lg tracking-wider">{{ $bankDetails->bank_account_no ?? '1234567890' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Atas Nama</span>
                                <span class="text-white font-bold">{{ $bankDetails->bank_account_name ?? 'PT Emas Perak Indonesia' }}</span>
                            </div>
                        @else
                             <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-400">Bank</span>
                                <span class="text-white font-bold">BCA</span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-400">No. Rekening</span>
                                <span class="text-white font-bold text-lg tracking-wider">8000 1234 5678</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Atas Nama</span>
                                <span class="text-white font-bold">PT Emas Perak Indonesia</span>
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
