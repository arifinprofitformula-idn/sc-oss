<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Konfirmasi Pembayaran Berhasil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">
                
                <div class="mb-6 flex justify-center">
                    <div class="rounded-full bg-green-100 p-4">
                        <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    Konfirmasi Pembayaran Diterima!
                </h3>
                
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Terima kasih telah melakukan konfirmasi pembayaran untuk Order <span class="font-bold text-gray-900 dark:text-gray-200">#{{ $order->order_number }}</span>.<br>
                    Tim kami akan segera memverifikasi bukti pembayaran Anda.
                </p>

                <div class="bg-blue-50 dark:bg-gray-700 rounded-lg p-4 mb-8 max-w-lg mx-auto border border-blue-100 dark:border-gray-600">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-left text-sm text-gray-600 dark:text-gray-300">
                            <p class="font-semibold text-blue-800 dark:text-blue-300 mb-1">Informasi Penting</p>
                            <p>Proses verifikasi biasanya memakan waktu <strong>1x24 jam kerja</strong>. Status pesanan Anda akan otomatis berubah menjadi <strong>PAID</strong> setelah verifikasi selesai.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('silverchannel.orders.show', $order) }}" class="inline-flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Lihat Detail Order
                    </a>
                    
                    <a href="{{ route('dashboard') }}" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 disabled:opacity-25 transition">
                        Kembali ke Dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
