<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Notifikasi Email') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subjek</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $emailLog->subject }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tujuan</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $emailLog->to }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Waktu Kirim</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $emailLog->created_at->format('d M Y H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <div class="mt-1">
                                @if ($emailLog->status === 'sent')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Terkirim
                                    </span>
                                @elseif ($emailLog->status === 'failed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Gagal
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($emailLog->error)
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 text-red-600">Error</label>
                                <p class="mt-1 text-sm text-red-600 bg-red-50 p-2 rounded">{{ $emailLog->error }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Konten Email</h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 overflow-auto max-h-[600px] prose max-w-none">
                            {!! $emailLog->content !!}
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('silverchannel.email-history.index') }}" class="bg-gray-200 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
