<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            My Referrals
        </h2>
    </x-slot>

    <div class="py-8" x-data="referralsPage({
        search: {{ \Illuminate\Support\Js::from($filters['search'] ?? '') }},
        baseUrl: {{ \Illuminate\Support\Js::from(url('silverchannel/referrals')) }}
    })">
        <!-- Custom CSS for 3D Buttons -->
        <style>
            .btn-3d {
                /* Custom properties for maintainability */
                --btn-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                --btn-shadow-color: rgba(0, 0, 0, 0.2);
                --btn-highlight: rgba(255, 255, 255, 0.2);
                --btn-pulse-color: rgba(5, 186, 218, 0.4); /* Default cyan pulse */
                
                position: relative;
                overflow: hidden;
                transition: var(--btn-transition);
                box-shadow: 
                    0 4px 6px -1px var(--btn-shadow-color),
                    0 2px 4px -1px var(--btn-shadow-color),
                    inset 0 1px 0 var(--btn-highlight);
                z-index: 1;
                background-size: 100% auto;
            }

            /* Gradient Overlay for Lighting Effect */
            .btn-3d::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: linear-gradient(to bottom, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0.05) 100%);
                opacity: 0;
                transition: opacity 0.3s;
                z-index: -1;
            }

            /* Hover State: Lift up + Deep Shadow + Pulse */
            .btn-3d:hover {
                transform: translateY(-2px);
                background-position: right center;
                background-size: 200% auto;
                animation: pulse512 1.5s infinite;
            }
            
            .btn-3d:hover::before {
                opacity: 1;
            }

            /* Active State: Press down */
            .btn-3d:active {
                transform: translateY(1px);
                box-shadow: 
                    0 2px 4px -1px var(--btn-shadow-color),
                    inset 0 2px 4px rgba(0,0,0,0.1);
                animation: none;
            }

            /* Gold Variant */
            .btn-3d-gold {
                background: linear-gradient(135deg, #EEA727 0%, #D97706 100%);
                --btn-pulse-color: rgba(238, 167, 39, 0.6);
            }

            /* Blue Variant */
            .btn-3d-blue {
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
                --btn-pulse-color: rgba(37, 99, 235, 0.6);
            }

            /* Green Variant */
            .btn-3d-green {
                background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
                --btn-pulse-color: rgba(22, 163, 74, 0.6);
            }

            /* Pulse Animation */
            @keyframes pulse512 {
                0% {
                    box-shadow: 
                        0 10px 15px -3px var(--btn-shadow-color),
                        0 4px 6px -2px var(--btn-shadow-color),
                        inset 0 1px 0 var(--btn-highlight),
                        0 0 0 0 var(--btn-pulse-color);
                }
                70% {
                    box-shadow: 
                        0 10px 15px -3px var(--btn-shadow-color),
                        0 4px 6px -2px var(--btn-shadow-color),
                        inset 0 1px 0 var(--btn-highlight),
                        0 0 0 10px rgba(255, 255, 255, 0);
                }
                100% {
                    box-shadow: 
                        0 10px 15px -3px var(--btn-shadow-color),
                        0 4px 6px -2px var(--btn-shadow-color),
                        inset 0 1px 0 var(--btn-highlight),
                        0 0 0 0 rgba(255, 255, 255, 0);
                }
            }

            /* Shimmer Animation */
            @keyframes shimmer {
                0% { transform: translateX(-100%) skewX(-15deg); }
                100% { transform: translateX(200%) skewX(-15deg); }
            }
            
            .shimmer {
                position: relative;
                overflow: hidden;
            }
            
            .shimmer::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 50%;
                height: 100%;
                background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
                transform: skewX(-15deg);
                animation: shimmer 2s infinite;
                pointer-events: none;
            }
        </style>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" class="text-green-500 hover:text-green-700" @click="$event.target.closest('div').remove()">&times;</button>
                </div>
            @endif

            @if($totalDueToday > 0)
                <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Anda memiliki <strong>{{ $totalDueToday }}</strong> follow up yang jatuh tempo atau lewat hari ini.</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <form method="GET" action="{{ route('silverchannel.referrals.index') }}" x-ref="filterForm">
                        <input type="hidden" name="per_page" value="{{ $filters['per_page'] ?? 10 }}">
                        <div class="referrals-toolbar flex flex-wrap items-end gap-4">
                            <div class="w-full sm:w-auto min-w-[150px]">
                                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Status</label>
                                <select name="status" class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 h-11">
                                    @php
                                        $statusOptions = [
                                            'ALL' => 'Semua Status', 
                                            'ACTIVE' => 'Active', 
                                            'PENDING_REVIEW' => 'Pending Review', 
                                            'WAITING_VERIFICATION' => 'Waiting Verification',
                                            'REJECTED' => 'Rejected'
                                        ];
                                    @endphp
                                    @foreach($statusOptions as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['status'] ?? 'ALL') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Dari</label>
                                    <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full sm:w-36 border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 h-11" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Sampai</label>
                                    <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full sm:w-36 border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 h-11" />
                                </div>
                            </div>
                            <button type="submit" class="btn-3d btn-3d-blue shimmer inline-flex items-center justify-center h-11 px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">Filter</button>
                            
                            <div class="relative flex-1 min-w-[200px]">
                                <input type="text" name="search" x-model="search" @input.debounce.400ms="onSearch" class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm pl-9 pr-3 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 h-11" placeholder="Cari nama..." />
                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16a6 6 0 1112 0 6 6 0 01-12 0zm10 0l4 4"></path></svg>
                                </span>
                            </div>

                            <a href="{{ route('silverchannel.referrals.export', request()->all()) }}" class="btn-3d btn-3d-green shimmer inline-flex items-center justify-center px-4 py-2 text-sm text-white rounded-md h-11 min-w-[44px]">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" /></svg>
                                Export
                            </a>
                        </div>
                    </form>
                </div>

                <div class="relative">
                    <div x-show="loading" class="absolute inset-0 bg-white/70 dark:bg-gray-900/70 flex items-center justify-center z-10" style="display: none;">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path></svg>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Memuat data...</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    @php
                                        $columns = [
                                            'name' => 'Nama Lengkap',
                                            'contact' => 'Email / WhatsApp',
                                            'city_name' => 'Asal Kota',
                                            'status' => 'Status',
                                            'last_follow_up_at' => 'Follow Up Terakhir',
                                        ];
                                    @endphp
                                    @foreach($columns as $key => $label)
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            @if(in_array($key, ['name','city_name','status']))
                                                @php
                                                    $isActive = ($filters['sort'] ?? 'created_at') === $key;
                                                    $nextDirection = $isActive && ($filters['direction'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
                                                @endphp
                                                <a href="{{ route('silverchannel.referrals.index', array_merge(request()->all(), ['sort' => $key, 'direction' => $nextDirection])) }}" class="inline-flex items-center gap-1">
                                                    <span>{{ $label }}</span>
                                                    @if($isActive)
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ ($filters['direction'] ?? 'desc') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" /></svg>
                                                    @endif
                                                </a>
                                            @else
                                                <span>{{ $label }}</span>
                                            @endif
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($prospects as $prospect)
                                    @php
                                        /** @var \App\Models\User $prospect */
                                        $followUp = $prospect->referralFollowUpAsReferred;
                                        $status = $followUp->status ?? 'PENDING';
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer" @click="openDetail({
                                            id: {{ $prospect->id }},
                                            name: @js($prospect->name),
                                            email: @js($prospect->email),
                                            whatsapp: @js($prospect->whatsapp),
                                            city: @js($prospect->city_name),
                                            province: @js($prospect->province_name),
                                            status: @js($status),
                                            last_follow_up_at: @js(optional($followUp)->last_follow_up_at?->format('Y-m-d H:i')), 
                                            next_follow_up_at: @js(optional($followUp)->next_follow_up_at?->format('Y-m-d H:i')), 
                                            note: @js($followUp->note ?? ''),
                                            registered_at: @js($prospect->created_at->format('Y-m-d H:i')),
                                        })">
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                            <div class="font-medium">{{ $prospect->name }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $prospect->silver_channel_id ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col gap-1 text-xs">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-gray-500">Email:</span>
                                                    <span>{{ $prospect->email ?? '-' }}</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-gray-500">WA:</span>
                                                    <span>{{ $prospect->whatsapp ?? $prospect->phone ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                            <div class="text-sm">{{ $prospect->city_name ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $prospect->province_name ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($prospect->status === 'ACTIVE')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    ACTIVE
                                                </span>
                                            @elseif($prospect->status === 'PENDING_REVIEW' || $prospect->status === 'WAITING_VERIFICATION')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 whitespace-normal">
                                                    {{ str_replace('_', ' ', $prospect->status) }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    {{ $prospect->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                            <div class="text-sm">{{ optional($followUp)->last_follow_up_at?->format('d M Y H:i') ?? '-' }}</div>
                                            @if($followUp && $followUp->next_follow_up_at)
                                                <div class="text-xs text-gray-500">Next: {{ $followUp->next_follow_up_at->format('d M Y H:i') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                            <button type="button" @click.stop="openUpdate({
                                                    id: {{ $prospect->id }},
                                                    name: @js($prospect->name),
                                                    status: @js($status),
                                                    last_follow_up_at: @js(optional($followUp)->last_follow_up_at?->format('Y-m-d\TH:i')),
                                                    next_follow_up_at: @js(optional($followUp)->next_follow_up_at?->format('Y-m-d\TH:i')),
                                                    note: @js($followUp->note ?? ''),
                                                })" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-3 py-1.5 border border-transparent text-xs leading-4 font-semibold rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Update
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                            Belum ada prospek yang mendaftar menggunakan kode referral Anda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <form method="GET" action="{{ route('silverchannel.referrals.index') }}" class="flex items-center gap-2">
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                            <input type="hidden" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                            <input type="hidden" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                            <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'created_at' }}">
                            <input type="hidden" name="direction" value="{{ $filters['direction'] ?? 'desc' }}">
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Halaman</label>
                            <select name="per_page" class="w-24 border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 h-11" onchange="this.form.submit()">
                                @foreach([10,25,50] as $per)
                                    <option value="{{ $per }}" @selected(($filters['per_page'] ?? 10) == $per)>{{ $per }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>
                            {{ $prospects->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div x-show="showDetail" class="fixed inset-0 z-40 flex items-center justify-center bg-black/40" style="display: none;" x-transition>
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-lg w-full mx-4" @click.away="showDetail = false">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detail Prospek</h3>
                    <button class="text-gray-400 hover:text-gray-600" @click="showDetail = false">&times;</button>
                </div>
                <div class="px-6 py-4 space-y-3 text-sm text-gray-800 dark:text-gray-100">
                    <div>
                        <div class="text-xs text-gray-500">Nama Lengkap</div>
                        <div class="font-semibold" x-text="detail.name"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <div class="text-xs text-gray-500">Email</div>
                            <div x-text="detail.email || '-'" class="break-all"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">WhatsApp</div>
                            <div x-text="detail.whatsapp || '-'" class="break-all"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <div class="text-xs text-gray-500">Kota</div>
                            <div x-text="detail.city || '-'" ></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Provinsi</div>
                            <div x-text="detail.province || '-'" ></div>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Status Prospek</div>
                        <div class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800" x-text="detail.statusLabel"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <div class="text-xs text-gray-500">Terdaftar Pada</div>
                            <div x-text="detail.registered_at"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Follow Up Terakhir</div>
                            <div x-text="detail.last_follow_up_at || '-'" ></div>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Catatan Follow Up</div>
                        <div x-text="detail.note || '-'" class="whitespace-pre-line"></div>
                    </div>
                </div>
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900" @click="showDetail = false">Tutup</button>
                </div>
            </div>
        </div>

        <!-- Update Modal -->
        <div x-show="showUpdate" class="fixed inset-0 z-40 flex items-center justify-center bg-black/40" style="display: none;" x-transition>
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-lg w-full mx-4" @click.away="showUpdate = false">
                <form method="POST" :action="updateAction" @submit="loading = true">
                    @csrf
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Update Follow Up</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" @click="showUpdate = false">&times;</button>
                    </div>
                    <div class="px-6 py-4 space-y-4 text-sm text-gray-800 dark:text-gray-100">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Prospek</div>
                            <div class="font-semibold" x-text="form.name"></div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                            <select name="status" x-model="form.status" class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200">
                                <option value="PENDING">Pending</option>
                                <option value="FOLLOW_UP">Follow Up</option>
                                <option value="CONVERTED">Converted</option>
                                <option value="EXPIRED">Expired</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Follow Up Terakhir</label>
                                <input type="datetime-local" name="last_follow_up_at" x-model="form.last_follow_up_at" class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Follow Up Berikutnya</label>
                                <input type="datetime-local" name="next_follow_up_at" x-model="form.next_follow_up_at" class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan</label>
                            <textarea name="note" rows="3" x-model="form.note" class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200" placeholder="Ringkasan hasil follow up"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900" @click="showUpdate = false">Batal</button>
                        <button type="submit" class="btn-3d btn-3d-blue shimmer inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        /**
         * @param {Object} config
         * @param {string} config.search
         * @param {string} config.baseUrl
         */
        function referralsPage(config) {
            return {
                loading: false,
                search: config.search || '',
                baseUrl: config.baseUrl || '',
                showDetail: false,
                showUpdate: false,
                detail: {
                    name: '',
                    email: '',
                    whatsapp: '',
                    city: '',
                    province: '',
                    statusLabel: '',
                    last_follow_up_at: '',
                    next_follow_up_at: '',
                    note: '',
                    registered_at: '',
                },
                form: {
                    id: null,
                    name: '',
                    status: 'PENDING',
                    last_follow_up_at: '',
                    next_follow_up_at: '',
                    note: '',
                },
                updateAction: '',
                onSearch() {
                    const form = this.$refs.filterForm;
                    const url = new URL(form.action, window.location.origin);
                    const params = new URLSearchParams(new FormData(form));
                    if (this.search) {
                        params.set('search', this.search);
                    } else {
                        params.delete('search');
                    }
                    url.search = params.toString();
                    this.loading = true;
                    window.location = url.toString();
                },
                openDetail(data) {
                    this.detail = {
                        ...data,
                        statusLabel: this.formatStatus(data.status),
                    };
                    this.showDetail = true;
                },
                openUpdate(data) {
                    this.form = { ...data };
                    this.updateAction = this.baseUrl + '/' + data.id + '/follow-up';
                    this.showUpdate = true;
                },
                formatStatus(status) {
                    if (!status) return '-';
                    return status.replace(/_/g, ' ').toLowerCase().replace(/(^|\s)\S/g, function (t) { return t.toUpperCase(); });
                }
            }
        }
    </script>
</x-app-layout>
