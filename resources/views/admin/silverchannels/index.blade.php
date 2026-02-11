<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Silverchannels') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="silverchannelManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
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
                    /* Combine 3D shadow with Pulse animation logic (handled in keyframes) */
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

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row gap-4 mb-6 px-[10px] md:px-0">
                <!-- Kolom 1: Search (40%) -->
                <div class="w-full md:w-[40%]">
                    <form method="GET" action="{{ route('admin.silverchannels.index') }}" class="w-full">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" class="block w-full pl-11 pr-3 py-2 h-12 border border-gray-300 rounded-md leading-5 bg-[#F5F5F5] placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-blue-300 focus:ring focus:ring-blue-200 sm:text-sm text-[#333333]" placeholder="Search by name, email, phone...">
                        </div>
                    </form>
                </div>

                <!-- Kolom 2: Import Data (30%) -->
                <div class="w-full md:w-[30%]">
                    <a href="{{ route('admin.silverchannels.import') }}" class="btn-3d btn-3d-gold shimmer w-full h-12 text-white font-bold py-2 px-4 rounded inline-flex justify-center items-center text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        {{ __('Import Data') }}
                    </a>
                </div>

                <!-- Kolom 3: Add New Silverchannel (30%) -->
                <div class="w-full md:w-[30%]">
                    <button @click="openCreateModal()" class="btn-3d btn-3d-blue shimmer w-full h-12 text-white font-bold py-2 px-4 rounded inline-flex justify-center items-center text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('Add New Silverchannel') }}
                    </button>
                </div>
            </div>

            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-blue-200 dark:border-blue-900 shadow-md">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-fixed">
                            <thead class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider w-[15%]">ID / Pereferral</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider w-[20%]">Email / Phone</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider w-[12%]">Status</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider w-[15%] hidden lg:table-cell">Joined</th>
                                    <th scope="col" class="px-4 py-3 text-end text-xs font-bold text-white uppercase tracking-wider w-[10%]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($silverchannels as $user)
                                    <tr class="hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                        <td class="px-4 py-4 align-top">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 break-words">{{ $user->silver_channel_id ?? $user->referral_code ?? '-' }}</div>
                                            @if($user->referrer)
                                                <div class="text-xs mt-1">
                                                    <a href="#" class="text-blue-600 hover:text-blue-800 underline break-words"
                                                       @click.prevent="openReferrerModal({{ json_encode($user->referrer) }})">
                                                        <span class="block font-medium">Ref: {{ $user->referrer->name }}</span>
                                                        <span class="block text-gray-500">{{ $user->referrer->silver_channel_id ?? $user->referrer->id }}</span>
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500 mt-1">-</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 break-words line-clamp-2" title="{{ $user->name }}">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500 mt-1 break-words">{{ $user->city_name }}, {{ $user->province_name }}</div>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <div class="text-sm text-gray-500 dark:text-gray-400 break-all">{{ $user->email }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $user->phone }}</div>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            @if($user->status === 'ACTIVE')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    ACTIVE
                                                </span>
                                            @elseif($user->status === 'PENDING_REVIEW' || $user->status === 'WAITING_VERIFICATION')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 whitespace-normal">
                                                    {{ str_replace('_', ' ', $user->status) }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    {{ $user->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 align-top text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell">
                                            {{ $user->created_at->format('d M Y') }}
                                            <div class="text-xs text-gray-400">{{ $user->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-4 py-4 align-top text-end text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <div class="relative group">
                                                    <a href="{{ route('admin.silverchannels.edit', $user) }}" aria-label="Edit" class="inline-block p-1.5 rounded hover:shadow hover:bg-indigo-50 text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4h2M4 20h16M4 20l4-4m0 0l10-10a2.828 2.828 0 114 4L12 20m-4-4l4 4"/></svg>
                                                    </a>
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none transition whitespace-nowrap z-10">Edit</div>
                                                </div>

                                                <div class="relative group">
                                                    <button @click="openConfirmModal('delete', '{{ route('admin.silverchannels.destroy', $user) }}', 'Hapus Silverchannel', 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.', 'Ya, Hapus', 'bg-red-600 hover:bg-red-700 focus:ring-red-500', 'DELETE')" aria-label="Delete" class="p-1.5 rounded hover:shadow hover:bg-red-50 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h10"/></svg>
                                                    </button>
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none transition whitespace-nowrap z-10">Delete</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                <span class="text-base font-medium">No Silverchannels found</span>
                                                <span class="text-sm mt-1">Try adjusting your search criteria</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 flex flex-col md:flex-row justify-between items-center pagination-container relative">
                        <!-- Loading Overlay for Pagination -->
                        <div x-show="pageLoading" 
                             class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 z-10 flex items-center justify-center rounded-lg"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             style="display: none;">
                            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <div class="text-sm text-gray-700 dark:text-gray-300 mb-4 md:mb-0 w-full md:w-auto text-center md:text-left">
                            Showing <span class="font-medium">{{ $silverchannels->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $silverchannels->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $silverchannels->total() }}</span> entries
                        </div>
                        <div class="w-full md:w-auto flex justify-center md:justify-end">
                            {{ $silverchannels->links() }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div x-show="confirmModal.show" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="if(!confirmModal.loading) confirmModal.show = false">
            
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="if(!confirmModal.loading) confirmModal.show = false">
                    <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full"
                     x-transition:enter="transform transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transform transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10"
                                 :class="{
                                    'bg-red-100 text-red-600': confirmModal.type === 'delete' || confirmModal.type === 'danger',
                                    'bg-yellow-100 text-yellow-600': confirmModal.type === 'warning' || confirmModal.type === 'reject',
                                    'bg-green-100 text-green-600': confirmModal.type === 'success' || confirmModal.type === 'approve',
                                    'bg-blue-100 text-blue-600': confirmModal.type === 'info'
                                 }">
                                <!-- Heroicon based on type -->
                                <template x-if="confirmModal.type === 'delete' || confirmModal.type === 'danger'">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </template>
                                <template x-if="confirmModal.type === 'reject' || confirmModal.type === 'warning'">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                <template x-if="confirmModal.type === 'approve' || confirmModal.type === 'success'">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="confirmModal.type === 'info'">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title" x-text="confirmModal.title">
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="confirmModal.message">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="confirmModal.buttonClass"
                                :disabled="confirmModal.loading"
                                @click="submitConfirm()">
                            <svg x-show="confirmModal.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="confirmModal.loading ? 'Memproses...' : confirmModal.buttonText"></span>
                        </button>
                        <button type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="confirmModal.loading"
                                @click="confirmModal.show = false">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form :action="editMode ? '{{ url('admin/silverchannels') }}/' + form.id : '{{ route('admin.silverchannels.store') }}'" method="POST" enctype="multipart/form-data">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-6 sm:p-8">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-6" x-text="editMode ? 'Edit Silverchannel' : 'Add New Silverchannel'"></h3>
                            
                            <div class="space-y-6">
                                <!-- Account Information -->
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Account Information</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Name -->
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" id="name" x-model="form.name" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <!-- Email -->
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                                            <input type="email" name="email" id="email" x-model="form.email" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <!-- Whatsapp -->
                                        <div>
                                            <label for="whatsapp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Whatsapp Number <span class="text-red-500">*</span></label>
                                            <input type="text" name="whatsapp" id="whatsapp" x-model="form.whatsapp" required placeholder="e.g. 628123456789" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <!-- Referrer -->
                                        <div>
                                            <label for="referrer_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referrer Code (Optional)</label>
                                            <input type="text" name="referrer_code" id="referrer_code" x-model="form.referrer_code" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Information -->
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Personal Information</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- NIK -->
                                        <div>
                                            <label for="nik" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK</label>
                                            <input type="text" name="nik" id="nik" x-model="form.nik" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <!-- Job -->
                                        <div>
                                            <label for="job" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job</label>
                                            <input type="text" name="job" id="job" x-model="form.job" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <!-- Birth Place -->
                                        <div>
                                            <label for="birth_place" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Birth Place</label>
                                            <input type="text" name="birth_place" id="birth_place" x-model="form.birth_place" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <!-- Birth Date -->
                                        <div>
                                            <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Birth Date</label>
                                            <input type="date" name="birth_date" id="birth_date" x-model="form.birth_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <!-- Gender -->
                                        <div>
                                            <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                                            <select name="gender" id="gender" x-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Select Gender</option>
                                                <option value="Laki-laki">Laki-laki</option>
                                                <option value="Perempuan">Perempuan</option>
                                            </select>
                                        </div>
                                        <!-- Religion -->
                                        <div>
                                            <label for="religion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Religion</label>
                                            <select name="religion" id="religion" x-model="form.religion" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Select Religion</option>
                                                <option value="Islam">Islam</option>
                                                <option value="Kristen">Kristen</option>
                                                <option value="Katolik">Katolik</option>
                                                <option value="Hindu">Hindu</option>
                                                <option value="Buddha">Buddha</option>
                                                <option value="Konghucu">Konghucu</option>
                                            </select>
                                        </div>
                                        <!-- Marital Status -->
                                        <div>
                                            <label for="marital_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marital Status</label>
                                            <select name="marital_status" id="marital_status" x-model="form.marital_status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Select Status</option>
                                                <option value="Belum Menikah">Belum Menikah</option>
                                                <option value="Menikah">Menikah</option>
                                                <option value="Cerai Hidup">Cerai Hidup</option>
                                                <option value="Cerai Mati">Cerai Mati</option>
                                            </select>
                                        </div>
                                        <!-- Photo -->
                                        <div>
                                            <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Photo</label>
                                            <input type="file" name="photo" id="photo" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                            <p class="text-xs text-gray-500 mt-1">Leave blank to keep current photo</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address Information -->
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Address Information</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Province -->
                                        <div>
                                            <label for="province_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Province <span class="text-red-500">*</span></label>
                                            <select name="province_id" id="province_id" x-model="form.province_id" @change="onProvinceChange($event)" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Select Province</option>
                                                <template x-for="province in provinces" :key="province.province_id">
                                                    <option :value="province.province_id" x-text="province.province"></option>
                                                </template>
                                            </select>
                                            <input type="hidden" name="province_name" x-model="form.province_name">
                                        </div>
                                        <!-- City -->
                                        <div>
                                            <label for="city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City/District <span class="text-red-500">*</span></label>
                                            <select name="city_id" id="city_id" x-model="form.city_id" @change="onCityChange($event)" required :disabled="!form.province_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:opacity-50">
                                                <option value="">Select City</option>
                                                <template x-for="city in cities" :key="city.city_id">
                                                    <option :value="city.city_id" x-text="city.type + ' ' + city.city_name"></option>
                                                </template>
                                            </select>
                                            <input type="hidden" name="city_name" x-model="form.city_name">
                                        </div>
                                        <!-- Postal Code -->
                                        <div>
                                            <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
                                            <input type="text" name="postal_code" id="postal_code" x-model="form.postal_code" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <!-- Address -->
                                        <div class="sm:col-span-2">
                                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Address</label>
                                            <textarea name="address" id="address" x-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Password Management (Super Admin Only) -->
                                @role('SUPER_ADMIN')
                                <div class="border-t pt-4 mt-4">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Security</h4>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- New Password -->
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <span x-text="editMode ? 'New Password (Optional)' : 'Password'"></span>
                                            </label>
                                            <input type="password" name="password" id="password" x-model="form.password" :required="!editMode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Min. 8 characters">
                                            <p x-show="editMode" class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
                                        </div>

                                        <!-- Confirm Password -->
                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" x-model="form.password_confirmation" :required="!editMode && form.password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                                @endrole
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse sm:items-center gap-3 sm:gap-4">
                            <button type="submit" class="w-full h-10 inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Save
                            </button>
                            <button type="button" @click="showModal = false" class="w-full h-10 inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-4 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <div x-show="showRefModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="showRefModal = false">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" @click.self="showRefModal = false">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
                    <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-6 sm:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Profil Pereferral</h3>
                            <button class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700" @click="showRefModal = false">
                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <template x-if="referrer && referrer.id">
                            <div class="space-y-4">
                                <div class="flex items-start space-x-4">
                                    <!-- Photo or Initials -->
                                    <template x-if="referrer.profile && referrer.profile.photo_path">
                                         <img :src="'/storage/' + referrer.profile.photo_path" alt="Photo" class="w-16 h-16 rounded-full object-cover border border-gray-200 shadow-sm">
                                    </template>
                                    <template x-if="!referrer.profile || !referrer.profile.photo_path">
                                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl font-bold border border-indigo-200" x-text="(referrer.name || '-').substring(0,1).toUpperCase()"></div>
                                    </template>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 truncate" x-text="referrer.name || '-'"></h4>
                                        <p class="text-sm text-gray-500 truncate" x-text="'ID: ' + (referrer.silver_channel_id || referrer.id)"></p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" x-text="referrer.email"></span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800" x-text="referrer.phone || referrer.whatsapp"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                     <div class="grid grid-cols-1 gap-y-3 text-sm">
                                         <div class="flex justify-between items-center">
                                             <span class="text-gray-500">Lokasi</span>
                                             <span class="text-gray-900 dark:text-gray-100 font-medium text-right truncate pl-4" x-text="(referrer.city_name || '-') + ', ' + (referrer.province_name || '-')"></span>
                                         </div>
                                         <div class="flex justify-between items-center">
                                             <span class="text-gray-500">Referral Code</span>
                                             <span class="font-mono text-gray-900 dark:text-gray-100 font-medium bg-gray-100 px-2 py-1 rounded" x-text="referrer.referral_code || '-'"></span>
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!referrer || !referrer.id">
                            <div class="p-4 bg-red-50 text-red-700 rounded">Data pereferral tidak ditemukan.</div>
                        </template>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex justify-end">
                        <button type="button" @click="showRefModal = false" class="h-10 inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-4 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const API_ROUTES = {
            provinces: "{{ route('admin.silverchannels.locations.provinces') }}",
            cities: "{{ url('admin/silverchannels/locations/cities') }}"
        };

        function silverchannelManager() {
            return {
                pageLoading: false,
                showModal: false,
                showRefModal: false,
                referrer: null,
                editMode: false,
                form: {
                    id: null,
                    name: '',
                    email: '',
                    whatsapp: '',
                    province_id: '',
                    province_name: '',
                    city_id: '',
                    city_name: '',
                    referrer_code: '',
                    password: '',
                    password_confirmation: ''
                },
                provinces: [],
                cities: [],
                
                // Confirmation Modal State
                confirmModal: {
                    show: false,
                    type: 'info', // info, warning, danger, success
                    title: '',
                    message: '',
                    actionUrl: '',
                    actionMethod: 'POST',
                    loading: false,
                    buttonText: 'Confirm',
                    buttonClass: 'bg-blue-600 hover:bg-blue-700'
                },

                init() {
                    this.fetchProvinces();
                },
                

                fetchProvinces() {
                    fetch(API_ROUTES.provinces)
                        .then(response => response.json())
                        .then(data => {
                            this.provinces = data;
                        })
                        .catch(error => console.error('Error fetching provinces:', error));
                },
                
                fetchCities(provinceId) {
                    if(!provinceId) {
                        this.cities = [];
                        return;
                    }
                    fetch(`${API_ROUTES.cities}/${provinceId}`)
                        .then(response => response.json())
                        .then(data => {
                            this.cities = data;
                        })
                        .catch(error => console.error('Error fetching cities:', error));
                },
                
                onProvinceChange(event) {
                    const select = event.target;
                    const index = select.selectedIndex;
                    const text = select.options[index].text;
                    this.form.province_name = text;
                    this.form.city_id = '';
                    this.form.city_name = '';
                    this.fetchCities(this.form.province_id);
                },

                onCityChange(event) {
                    const select = event.target;
                    const index = select.selectedIndex;
                    const text = select.options[index].text;
                    this.form.city_name = text;
                },

                resetForm() {
                    this.form = {
                        id: null,
                        name: '',
                        email: '',
                        whatsapp: '',
                        province_id: '',
                        province_name: '',
                        city_id: '',
                        city_name: '',
                        referrer_code: '',
                        password: '',
                        password_confirmation: ''
                    };
                    this.cities = [];
                },

                openCreateModal() {
                    this.resetForm();
                    this.editMode = false;
                    this.showModal = true;
                },
                
                openEditModal(user) {
                    this.form = {
                        id: user.id,
                        name: user.name,
                        email: user.email,
                        whatsapp: user.phone || user.whatsapp,
                        province_id: user.province_id,
                        province_name: user.province_name,
                        city_id: user.city_id,
                        city_name: user.city_name,
                        referrer_code: user.referrer ? user.referrer.referral_code : '',
                        password: '',
                        password_confirmation: '',
                        nik: user.nik || '',
                        job: user.profile ? user.profile.job : '',
                        birth_place: user.profile ? user.profile.birth_place : '',
                        birth_date: user.profile && user.profile.birth_date ? user.profile.birth_date.split('T')[0] : '',
                        gender: user.profile ? user.profile.gender : '',
                        religion: user.profile ? user.profile.religion : '',
                        marital_status: user.profile ? user.profile.marital_status : '',
                        address: user.address || '',
                        postal_code: user.postal_code || ''
                    };
                    
                    // Pre-fill cities
                    if (this.form.province_id) {
                        this.fetchCities(this.form.province_id);
                    }
                    
                    this.editMode = true;
                    this.showModal = true;
                },
                openReferrerModal(ref) {
                    if (!ref || !ref.id) {
                        this.referrer = null;
                        this.showRefModal = true;
                        return;
                    }
                    this.referrer = ref;
                    this.showRefModal = true;
                },
                
                confirmDelete(url) {
                    this.openConfirmModal(
                        'delete',
                        url,
                        'Hapus Silverchannel',
                        'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
                        'Ya, Hapus',
                        'bg-red-600 hover:bg-red-700 focus:ring-red-500'
                    );
                },

                openConfirmModal(type, url, title, message, buttonText, buttonClass, method = 'POST') {
                    this.confirmModal = {
                        show: true,
                        type: type,
                        title: title,
                        message: message,
                        actionUrl: url,
                        actionMethod: method,
                        loading: false,
                        buttonText: buttonText,
                        buttonClass: buttonClass
                    };
                },

                submitConfirm() {
                    this.confirmModal.loading = true;
                    
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.confirmModal.actionUrl;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    if (this.confirmModal.actionMethod !== 'POST') {
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = this.confirmModal.actionMethod;
                        form.appendChild(methodInput);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
</x-app-layout>
