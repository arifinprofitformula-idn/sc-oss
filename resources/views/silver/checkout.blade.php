<x-guest-layout>
    <div class="max-w-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Konfirmasi Pembayaran</h2>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-indigo-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Detail Paket Silverchannel</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Silakan lakukan pembayaran untuk mengaktifkan akun Anda.</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Nama Paket</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $package->name }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Harga</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold text-lg">Rp {{ number_format($package->price, 0, ',', '.') }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Benefit</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <ul class="list-disc pl-5">
                                @if(is_array($package->benefits))
                                    @foreach($package->benefits as $benefit)
                                        <li>{{ $benefit }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-yellow-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Instruksi Pembayaran</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <p class="text-gray-700 mb-2">Silakan transfer ke rekening berikut:</p>
                <div class="bg-gray-100 p-4 rounded-md">
                    <p class="font-semibold text-gray-800">{{ $bankDetails['bank_name'] }}</p>
                    <p class="text-xl font-bold text-gray-900 tracking-wider">{{ $bankDetails['account_number'] }}</p>
                    <p class="text-gray-600">a.n. {{ $bankDetails['account_name'] }}</p>
                </div>
                <p class="text-sm text-gray-500 mt-2">*Pastikan nominal transfer sesuai hingga 3 digit terakhir.</p>
            </div>
        </div>

        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upload Bukti Transfer</h3>
                
                <form action="{{ route('silver.checkout.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bukti Pembayaran (JPG/PNG)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="payment_proof" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload file</span>
                                        <input id="payment_proof" name="payment_proof" type="file" class="sr-only" required accept="image/*">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('payment_proof')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-primary-button class="w-full justify-center">
                            {{ __('Konfirmasi Pembayaran') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-4 text-center">
             <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
