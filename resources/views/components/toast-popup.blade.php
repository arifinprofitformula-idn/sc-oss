<div
    x-data="{
        init() {
            @if ($errors->any())
                this.$store.toast.show('Terdapat kesalahan validasi. Mohon periksa kembali inputan Anda.', 'error');
            @endif

            @if (session('success'))
                this.$store.toast.show('{{ session('success') }}', 'success');
            @elseif (session('error'))
                this.$store.toast.show('{{ session('error') }}', 'error');
            @elseif (session('warning'))
                this.$store.toast.show('{{ session('warning') }}', 'warning');
            @elseif (session('message'))
                this.$store.toast.show('{{ session('message') }}', 'success');
            @elseif (session('status') === 'profile-updated')
                this.$store.toast.show('Sukses! Data pribadi berhasil disimpan.', 'success');
            @elseif (session('status') === 'password-updated')
                this.$store.toast.show('Sukses! Password berhasil diperbarui.', 'success');
            @elseif (session('status') === 'verification-link-sent')
                this.$store.toast.show('Link verifikasi baru telah dikirim ke email Anda.', 'success');
            @elseif (session('status'))
                this.$store.toast.show('{{ session('status') }}', 'info');
            @endif
        }
    }"
    x-init="init()"
    x-cloak
    class="fixed inset-0 z-50 flex items-start justify-end p-4 px-4 py-6 pointer-events-none sm:p-6 mt-14"
    aria-live="assertive"
>
    <div
        x-show="$store.toast.visible"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="max-w-sm w-full bg-white dark:bg-gray-800 shadow-xl rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden border-l-4"
        :class="{
            'border-emerald-500': $store.toast.type === 'success',
            'border-rose-500': $store.toast.type === 'error',
            'border-amber-500': $store.toast.type === 'warning',
            'border-blue-500': $store.toast.type === 'info' || !$store.toast.type
        }"
    >
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <!-- Success Icon -->
                    <svg x-show="$store.toast.type === 'success'" class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Error Icon -->
                    <svg x-show="$store.toast.type === 'error'" class="h-6 w-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <!-- Warning Icon -->
                    <svg x-show="$store.toast.type === 'warning'" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z" />
                    </svg>
                    <!-- Info Icon -->
                    <svg x-show="$store.toast.type === 'info' || !$store.toast.type" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="$store.toast.message"></p>
                </div>
                <div class="ml-4 flex flex-shrink-0">
                    <button type="button" @click="$store.toast.hide()" class="inline-flex rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
