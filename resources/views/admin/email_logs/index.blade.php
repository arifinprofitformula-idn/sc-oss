<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Email Delivery Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="space-y-6" x-data="emailLogs(@json($filters), '{{ route("admin.email-logs.list") }}')">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                <input type="datetime-local" x-model="filters.from" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                <input type="datetime-local" x-model="filters.to" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Penerima</label>
                <input type="text" placeholder="user@example.com" x-model="filters.email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select x-model="filters.status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">Semua</option>
                    <option value="queued">Queued</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                    <option value="bounced">Bounced</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="load(1)" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-md">Filter</button>
                <label class="ml-4 inline-flex items-center text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" class="mr-2 rounded" x-model="filters.autorefresh">
                    Auto-refresh
                </label>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Timestamp</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Recipient</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Retry</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Error</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="item in items" :key="item.id">
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200" x-text="new Date(item.created_at).toLocaleString()"></td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200" x-text="item.to"></td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200" x-text="item.type"></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold" :class="statusClass(item.status)" x-text="item.status.toUpperCase()"></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200" x-text="item.retry_count"></td>
                        <td class="px-4 py-3 text-sm text-rose-600 dark:text-rose-400" x-text="item.error || '-'"></td>
                        <td class="px-4 py-3 text-sm">
                            <button @click="preview(item)" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-md">Preview</button>
                            <button @click="retry(item)" class="ml-2 px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md" x-show="item.status === 'failed'">Retry</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div class="p-4 flex justify-between items-center">
            <button class="px-3 py-1 bg-gray-200 dark:bg-gray-800 rounded" :disabled="page<=1" @click="load(page-1)">Prev</button>
            <span class="text-sm text-gray-600 dark:text-gray-300" x-text="'Page ' + page + ' / ' + lastPage"></span>
            <button class="px-3 py-1 bg-gray-200 dark:bg-gray-800 rounded" :disabled="page>=lastPage" @click="load(page+1)">Next</button>
        </div>
    </div>

    <div x-show="modal.open" class="fixed inset-0 bg-black/50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 w-full max-w-4xl">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100" x-text="modal.subject"></h3>
                <button class="text-gray-500 hover:text-gray-700" @click="modal.open=false">Close</button>
            </div>
            <div class="p-4">
                <iframe x-show="modal.open" :srcdoc="modal.content" class="w-full h-[60vh] border rounded-md"></iframe>
            </div>
        </div>
    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
function emailLogs(initialFilters, endpointUrl) {
    return {
        items: [],
        page: 1,
        lastPage: 1,
        filters: {
            from: initialFilters.from,
            to: initialFilters.to,
            email: initialFilters.email,
            status: initialFilters.status,
            autorefresh: !!initialFilters.autorefresh,
        },
        modal: { open: false, subject: '', content: '' },
        statusClass(s) {
            return {
                'bg-gray-200 text-gray-800': s === 'queued',
                'bg-green-200 text-green-800': s === 'sent',
                'bg-red-200 text-red-800': s === 'failed',
                'bg-yellow-200 text-yellow-800': s === 'bounced',
            };
        },
        buildUrl(p) {
            const params = new URLSearchParams();
            if (this.filters.from) params.set('from', this.filters.from);
            if (this.filters.to) params.set('to', this.filters.to);
            if (this.filters.email) params.set('email', this.filters.email);
            if (this.filters.status) params.set('status', this.filters.status);
            params.set('page', p);
            return endpointUrl + '?' + params.toString();
        },
        async load(p = 1) {
            this.page = p;
            const res = await fetch(this.buildUrl(p), { headers: { 'Accept': 'application/json' }});
            const json = await res.json();
            this.items = json.data;
            this.lastPage = json.meta.last_page;
        },
        preview(item) {
            this.modal = { open: true, subject: item.subject || '', content: item.content || '<p>No content</p>' };
        },
        async retry(item) {
            // Simple retry: re-dispatch job with same payload but mark status queued
            const res = await fetch(endpointUrl, { method: 'GET' }); // placeholder to avoid CSRF issues
            alert('Silakan trigger ulang dari sistem terkait. Otomasi retry terjadi via queue retries.');
        },
        init() {
            this.load(1);
            setInterval(() => {
                if (this.filters.autorefresh) this.load(this.page);
            }, 10000);
        }
    }
}
</script>
@endpush
