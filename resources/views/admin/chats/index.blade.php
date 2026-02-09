<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pusat Pesan') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="chatManagement()" x-init="initChat()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mx-[10px] sm:mx-0 bg-white overflow-hidden shadow-xl sm:rounded-lg h-[80vh] flex">
                
                <!-- Left Sidebar: Conversations List -->
                <div class="w-1/4 border-r border-gray-200 flex flex-col">
                    <!-- Filters -->
                    <div class="p-4 border-b border-gray-200 space-y-3">
                        <input type="text" x-model="filters.search" @input.debounce.500ms="fetchConversations()" placeholder="Cari order/user..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                        
                        <div class="flex space-x-2">
                            <select x-model="filters.status" @change="fetchConversations()" class="w-1/2 text-xs rounded-md border-gray-300">
                                <option value="">Aktif (Open/Progress)</option>
                                <option value="unread">Belum Dibaca</option>
                                <option value="closed">Closed (History)</option>
                                <template x-for="(def, key) in statusDefinitions" :key="key">
                                    <option :value="key" x-text="def.label"></option>
                                </template>
                            </select>
                            <select x-model="filters.priority" @change="fetchConversations()" class="w-1/2 text-xs rounded-md border-gray-300">
                                <option value="">Semua Prioritas</option>
                                <option value="urgent">Urgent</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>

                    <!-- List -->
                    <div class="flex-1 overflow-y-auto">
                        <template x-for="conv in conversations" :key="conv.id">
                            <div @click="selectConversation(conv)" 
                                 class="p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition"
                                 :class="{'bg-blue-50': activeOrder && activeOrder.id === conv.id}">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-bold text-sm text-gray-900 truncate pr-2" x-text="conv.user.name + ' - ' + (conv.user.silver_channel_id || conv.user.id)"></span>
                                    <span class="text-[10px] text-gray-500 whitespace-nowrap" x-text="formatDate(conv.updated_at)"></span>
                                </div>
                                <div class="text-xs text-gray-600 mb-1 font-mono" x-text="'#' + conv.order_number"></div>
                                <div class="flex justify-between items-center mt-1">
                                    <div class="flex items-center gap-1 flex-wrap">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-semibold border"
                                              :class="{
                                                'bg-red-50 text-red-700 border-red-200': conv.chat_priority === 'urgent',
                                                'bg-orange-50 text-orange-700 border-orange-200': conv.chat_priority === 'high',
                                                'bg-blue-50 text-blue-700 border-blue-200': conv.chat_priority === 'medium',
                                                'bg-gray-50 text-gray-700 border-gray-200': conv.chat_priority === 'low'
                                              }" x-text="conv.chat_priority"></span>
                                        
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold border"
                                              :class="(statusDefinitions[conv.support_status || 'open']?.color || 'bg-gray-100 text-gray-800 border-gray-200')"
                                              x-text="statusDefinitions[conv.support_status || 'open']?.label || 'Open'"></span>
                                    </div>
                                    
                                    <template x-if="conv.unread_count > 0">
                                        <span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full shadow-sm" x-text="conv.unread_count"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div x-show="conversations.length === 0" class="p-4 text-center text-gray-500 text-sm">
                            Tidak ada pesan ditemukan.
                        </div>
                    </div>
                </div>

                <!-- Middle: Chat Area -->
                <div class="w-2/4 flex flex-col border-r border-gray-200">
                    <template x-if="!activeOrder">
                        <div class="flex-1 flex items-center justify-center text-gray-400">
                            Pilih percakapan untuk memulai
                        </div>
                    </template>

                    <template x-if="activeOrder">
                        <div class="flex-1 flex flex-col h-full">
                            <!-- Chat Header -->
                            <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold text-gray-800" x-text="'#' + activeOrder.order_number + ' - ' + activeOrder.user.name"></h3>
                                    <p class="text-xs text-gray-500" x-text="activeOrder.status"></p>
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="showStats()" class="text-xs bg-white border border-gray-300 px-2 py-1 rounded hover:bg-gray-100">
                                        Stats
                                    </button>
                                    <button @click="exportChat()" class="text-xs bg-white border border-gray-300 px-2 py-1 rounded hover:bg-gray-100">
                                        Export
                                    </button>
                                </div>
                            </div>

                            <!-- Messages -->
                            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-100" id="message-container">
                                <template x-for="msg in messages" :key="msg.id">
                                    <div class="flex flex-col" :class="msg.sender_id === {{ Auth::id() }} ? 'items-end' : 'items-start'">
                                        <div class="max-w-[80%] rounded-lg p-3 text-sm shadow-sm"
                                             :class="msg.sender_id === {{ Auth::id() }} ? 'bg-blue-600 text-white' : 'bg-white text-gray-800'">
                                            <p x-text="msg.message"></p>
                                            <div class="text-[10px] mt-1 opacity-75 text-right" x-text="formatTime(msg.created_at)"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Input Area -->
                            <div class="p-4 bg-white border-t border-gray-200">
                                <div class="mb-2 flex space-x-2 overflow-x-auto">
                                    <!-- Quick Replies -->
                                    <template x-for="qr in quickReplies" :key="qr.id">
                                        <button @click="newMessage = qr.message" class="whitespace-nowrap px-2 py-1 bg-gray-100 text-xs rounded-full hover:bg-gray-200 text-gray-600 border border-gray-200">
                                            <span x-text="qr.title"></span>
                                        </button>
                                    </template>
                                    <button @click="showQuickReplyModal = true" class="whitespace-nowrap px-2 py-1 bg-indigo-50 text-xs rounded-full hover:bg-indigo-100 text-indigo-600 border border-indigo-200">
                                        + Buat Template
                                    </button>
                                </div>
                                <div class="flex space-x-2">
                                    <textarea x-model="newMessage" @keydown.enter.prevent="sendMessage()" placeholder="Ketik pesan..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm resize-none h-12"></textarea>
                                    <button @click="sendMessage()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-semibold text-sm">
                                        Kirim
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Right: Context Panel -->
                <div class="w-1/4 bg-gray-50 flex flex-col h-full overflow-y-auto">
                    <template x-if="activeOrder">
                        <div class="p-4 space-y-6">
                            <!-- Order Summary -->
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Ringkasan Order</h4>
                                <div class="bg-white p-3 rounded border border-gray-200 shadow-sm">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="text-sm font-bold text-gray-800" x-text="'#' + activeOrder.order_number"></div>
                                        <div class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                            :class="{
                                                'bg-gray-100 text-gray-800': activeOrder.status === 'DRAFT',
                                                'bg-blue-100 text-blue-800': activeOrder.status === 'SUBMITTED' || activeOrder.status === 'WAITING_PAYMENT',
                                                'bg-yellow-100 text-yellow-800': activeOrder.status === 'WAITING_VERIFICATION',
                                                'bg-green-100 text-green-800': activeOrder.status === 'PAID' || activeOrder.status === 'PACKING' || activeOrder.status === 'SHIPPED' || activeOrder.status === 'DELIVERED',
                                                'bg-red-100 text-red-800': activeOrder.status === 'CANCELLED' || activeOrder.status === 'REFUNDED'
                                            }" x-text="activeOrder.status"></div>
                                    </div>
                                    
                                    <!-- Items List -->
                                    <div class="space-y-2 mb-3 max-h-40 overflow-y-auto">
                                        <template x-if="activeOrder.items">
                                            <div>
                                                <template x-for="item in activeOrder.items" :key="item.id">
                                                    <div class="flex justify-between items-start text-xs border-b border-gray-100 pb-1 last:border-0 last:pb-0">
                                                        <div class="flex-1 pr-2">
                                                            <div class="font-medium text-gray-700" x-text="item.product_name"></div>
                                                            <div class="text-gray-500 mt-0.5">
                                                                <span x-text="item.quantity + ' x ' + formatCurrency(item.price)"></span>
                                                            </div>
                                                        </div>
                                                        <div class="font-bold text-gray-800 whitespace-nowrap" x-text="formatCurrency(item.quantity * item.price)"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!activeOrder.items">
                                            <div class="text-center py-2 text-gray-400 text-xs">Memuat item...</div>
                                        </template>
                                    </div>

                                    <!-- Total -->
                                    <div class="pt-2 border-t border-gray-200 flex justify-between items-center text-xs font-bold text-gray-900">
                                        <span>Total</span>
                                        <span x-text="formatCurrency(activeOrder.total_amount)"></span>
                                    </div>
                                    
                                    <div class="mt-2 text-center">
                                        <a :href="'/admin/orders/' + activeOrder.id" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center justify-center">
                                            <span>Lihat Detail Order</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignment -->
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Assign ke CS</h4>
                                <select x-model="activeOrder.chat_assigned_to" @change="assignChat($event.target.value)" class="w-full text-sm rounded-md border-gray-300">
                                    <option value="">Belum di-assign</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Priority -->
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Prioritas</h4>
                                <div class="flex space-x-1">
                                    <template x-for="p in ['low', 'medium', 'high', 'urgent']">
                                        <button @click="updatePriority(p)" 
                                                class="px-2 py-1 text-xs border rounded capitalize"
                                                :class="activeOrder.chat_priority === p ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                                                x-text="p">
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Tags</h4>
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <template x-if="activeOrder.chat_tags && activeOrder.chat_tags.length">
                                        <template x-for="(tag, index) in activeOrder.chat_tags" :key="index">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                <span x-text="tag"></span>
                                                <button @click="removeTag(index)" class="ml-1 text-indigo-600 hover:text-indigo-900">×</button>
                                            </span>
                                        </template>
                                    </template>
                                </div>
                                <input type="text" @keydown.enter="addTag($event)" placeholder="Tambah tag + Enter" class="w-full text-xs rounded-md border-gray-300">
                            </div>

                            <!-- Status Issue -->
                            <div class="relative z-10">
                                <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Status Issue</h4>
                                
                                <div @click.away="showStatusDropdown = false" class="relative">
                                    <button @click="showStatusDropdown = !showStatusDropdown; if(showStatusDropdown) $nextTick(() => { $refs.statusDropdown.scrollTop = 0 })" 
                                            class="w-full flex justify-between items-center px-3 py-2 border rounded-md shadow-sm bg-white hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <div class="flex items-center">
                                            <!-- Color Dot -->
                                            <span class="w-2.5 h-2.5 rounded-full mr-2 ring-1 ring-white" 
                                                  :class="(statusDefinitions[activeOrder.support_status || 'open']?.color || 'bg-gray-200').split(' ')[0]"></span>
                                            <!-- Label -->
                                            <span class="font-semibold text-sm text-gray-700" 
                                                  x-text="statusDefinitions[activeOrder.support_status || 'open']?.label || 'Open'"></span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div x-show="showStatusDropdown" 
                                         x-ref="statusDropdown"
                                         style="display: none; max-height: 60vh;"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute z-20 mt-1 w-full bg-white shadow-xl rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm right-0 scroll-smooth">
                                        
                                        <template x-for="(def, key) in statusDefinitions" :key="key">
                                            <div @click="updateStatus(key)" 
                                                 class="cursor-pointer select-none relative py-3 pl-3 pr-9 hover:bg-gray-50 border-b border-gray-100 last:border-0 group transition-colors">
                                                <div class="flex items-center mb-0.5">
                                                    <span class="w-2 h-2 rounded-full mr-2" :class="def.color.split(' ')[0]"></span>
                                                    <span class="font-medium text-sm block truncate" 
                                                          :class="activeOrder.support_status === key ? 'text-indigo-600' : 'text-gray-900'" 
                                                          x-text="def.label"></span>
                                                </div>
                                                <span class="text-xs text-gray-500 pl-4 block" x-text="def.desc"></span>

                                                <template x-if="activeOrder.support_status === key">
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="mt-2" x-show="activeOrder.support_closed_at">
                                    <p class="text-[10px] text-gray-500">
                                        Ditutup: <span x-text="formatDate(activeOrder.support_closed_at)"></span>
                                    </p>
                                </div>
                            </div>


                        </div>
                    </template>
                    <template x-if="!activeOrder">
                        <div class="p-4 text-center text-gray-400 text-sm mt-10">
                            Informasi order akan muncul di sini.
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Quick Reply Modal -->
        <div x-show="showQuickReplyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Tambah Template Balasan</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Judul (Singkat)</label>
                                <input type="text" x-model="newQR.title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pesan</label>
                                <textarea x-model="newQR.message" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="saveQuickReply()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button @click="showQuickReplyModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Modal -->
        <div x-show="showStatsModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showStatsModal = false"></div>
                </div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Statistik Chat</h3>
                        <template x-if="stats">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-3 rounded text-center">
                                        <div class="text-2xl font-bold text-blue-600" x-text="stats.total"></div>
                                        <div class="text-xs text-gray-500 uppercase">Total Percakapan</div>
                                    </div>
                                    <div class="bg-red-50 p-3 rounded text-center">
                                        <div class="text-2xl font-bold text-red-600" x-text="stats.unread"></div>
                                        <div class="text-xs text-gray-500 uppercase">Belum Dibaca</div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-700 mb-2">Berdasarkan Prioritas</h4>
                                    <div class="space-y-2">
                                        <template x-for="(count, priority) in stats.priority" :key="priority">
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="capitalize" x-text="priority"></span>
                                                <span class="font-semibold" x-text="count"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="showStatsModal = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Status Comment Modal -->
        <div x-show="showStatusCommentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-2">Konfirmasi Penutupan Issue</h3>
                        <p class="text-sm text-gray-500 mb-4">Mohon berikan alasan atau catatan penyelesaian sebelum menutup issue ini.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Komentar / Catatan Penyelesaian <span class="text-red-500">*</span></label>
                            <textarea x-model="statusComment" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm" placeholder="Contoh: Masalah telah diselesaikan via telepon..."></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="submitStatusComment()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Tutup Issue
                        </button>
                        <button @click="showStatusCommentModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function chatManagement() {
            return {
                conversations: [],
                activeOrder: null,
                messages: [],
                filters: { search: '', status: '', priority: '', assigned_to: '' },
                quickReplies: [],
                newMessage: '',
                showQuickReplyModal: false,
                newQR: { title: '', message: '' },
                pollInterval: null,
                stats: null,
                showStatsModal: false,
                statusComment: '',
                showStatusCommentModal: false,
                pendingStatus: '',
                showStatusDropdown: false,
                statusDefinitions: {
                    'open': { label: 'Open', color: 'bg-blue-100 text-blue-800 border-blue-200', desc: 'Tiket baru masuk, belum ditangani' },
                    'pending': { label: 'Pending', color: 'bg-orange-100 text-orange-800 border-orange-200', desc: 'Menunggu respon pelanggan' },
                    'on_progress': { label: 'In Progress', color: 'bg-indigo-100 text-indigo-800 border-indigo-200', desc: 'Sedang ditangani tim' },
                    'escalated': { label: 'Escalated', color: 'bg-purple-100 text-purple-800 border-purple-200', desc: 'Dinaikkan ke level atas' },
                    'resolved': { label: 'Resolved', color: 'bg-green-100 text-green-800 border-green-200', desc: 'Solusi diberikan, tunggu konfirmasi' },
                    'closed': { label: 'Closed', color: 'bg-gray-800 text-white border-gray-600', desc: 'Tiket selesai dan ditutup' },
                    'reopened': { label: 'Reopened', color: 'bg-pink-100 text-pink-800 border-pink-200', desc: 'Dibuka kembali oleh pelanggan' }
                },

                updateStatus(status) {
                    this.showStatusDropdown = false;
                    if (this.activeOrder.support_status === status) return;
                    
                    if (status === 'closed') {
                        this.pendingStatus = status;
                        this.statusComment = '';
                        this.showStatusCommentModal = true;
                        return;
                    }

                    this.confirmUpdateStatus(status);
                },

                confirmUpdateStatus(status, comment = null) {
                    axios.patch(`/admin/api/chats/${this.activeOrder.id}/status`, {
                        status: status,
                        comment: comment
                    })
                    .then(response => {
                        this.activeOrder.support_status = status;
                        if (status === 'closed') {
                            this.activeOrder.support_closed_at = new Date().toISOString();
                        } else {
                            this.activeOrder.support_closed_at = null;
                        }
                        this.showStatusCommentModal = false;
                        
                        // Update in conversations list too
                        let conv = this.conversations.find(c => c.id === this.activeOrder.id);
                        if (conv) {
                            conv.support_status = status;
                        }
                    })
                    .catch(error => {
                        console.error('Error updating status:', error);
                        alert('Gagal mengubah status issue.');
                    });
                },
                
                submitStatusComment() {
                    if (!this.statusComment) {
                        alert('Mohon isi komentar/alasan penutupan issue.');
                        return;
                    }
                    this.confirmUpdateStatus(this.pendingStatus, this.statusComment);
                },

                initChat() {
                    this.fetchConversations();
                    this.fetchQuickReplies();
                    
                    // Poll for conversations list updates
                    setInterval(() => {
                        this.fetchConversations(true);
                    }, 10000);

                    // Poll for active chat messages
                    setInterval(() => {
                        if (this.activeOrder) {
                            this.fetchMessages(true);
                        }
                    }, 5000);
                },

                fetchConversations(silent = false) {
                    let params = new URLSearchParams(this.filters).toString();
                    fetch(`/admin/api/chats/conversations?${params}`)
                        .then(res => res.json())
                        .then(data => {
                            // If silent update (polling), merge carefully to avoid UI jump? 
                            // For MVP, just replace. Alpine handles DOM diffing well enough.
                            this.conversations = data;
                        });
                },

                selectConversation(conv) {
                    this.activeOrder = conv;
                    this.fetchMessages();
                    // Scroll to bottom after load
                    setTimeout(() => this.scrollToBottom(), 300);
                },

                fetchMessages(silent = false) {
                    if (!this.activeOrder) return;
                    fetch(`/admin/api/chats/${this.activeOrder.id}/messages`)
                        .then(res => res.json())
                        .then(data => {
                            this.activeOrder = data.order; // Update order details (priority, tags, etc might change)
                            
                            // Only update messages if count changed or last message different
                            // For MVP simpler: just update
                            let oldLength = this.messages.length;
                            this.messages = data.messages;
                            
                            if (!silent && this.messages.length > oldLength) {
                                this.scrollToBottom();
                            }
                        });
                },

                sendMessage() {
                    if (!this.newMessage.trim() || !this.activeOrder) return;
                    
                    fetch(`/admin/api/chats/${this.activeOrder.id}/send`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ message: this.newMessage })
                    })
                    .then(res => res.json())
                    .then(msg => {
                        this.messages.push(msg);
                        this.newMessage = '';
                        this.scrollToBottom();
                        this.fetchConversations(true); // Update list sort/preview
                    });
                },

                assignChat(userId) {
                    if (!this.activeOrder) return;
                    fetch(`/admin/api/chats/${this.activeOrder.id}/assign`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                },

                updatePriority(priority) {
                    if (!this.activeOrder) return;
                    this.activeOrder.chat_priority = priority;
                    fetch(`/admin/api/chats/${this.activeOrder.id}/priority`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ priority: priority })
                    });
                },

                addTag(e) {
                    let tag = e.target.value.trim();
                    if (!tag || !this.activeOrder) return;
                    
                    let tags = this.activeOrder.chat_tags || [];
                    if (!tags.includes(tag)) {
                        tags.push(tag);
                        this.updateTagsApi(tags);
                    }
                    e.target.value = '';
                },

                removeTag(index) {
                    if (!this.activeOrder) return;
                    let tags = this.activeOrder.chat_tags;
                    tags.splice(index, 1);
                    this.updateTagsApi(tags);
                },

                updateTagsApi(tags) {
                    this.activeOrder.chat_tags = tags;
                    fetch(`/admin/api/chats/${this.activeOrder.id}/tags`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ tags: tags })
                    });
                },

                fetchQuickReplies() {
                    fetch('/admin/api/chats/quick-replies')
                        .then(res => res.json())
                        .then(data => this.quickReplies = data);
                },

                saveQuickReply() {
                    if (!this.newQR.title || !this.newQR.message) return;
                    fetch('/admin/api/chats/quick-replies', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.newQR)
                    })
                    .then(res => res.json())
                    .then(() => {
                        this.showQuickReplyModal = false;
                        this.newQR = { title: '', message: '' };
                        this.fetchQuickReplies();
                    });
                },

                exportChat() {
                   window.location.href = '/admin/api/chats/export';
                },

                showStats() {
                    fetch('/admin/api/chats/stats')
                        .then(res => res.json())
                        .then(data => {
                            this.stats = data;
                            this.showStatsModal = true;
                        });
                },

                scrollToBottom() {
                    let container = document.getElementById('message-container');
                    if (container) container.scrollTop = container.scrollHeight;
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    let date = new Date(dateString);
                    // Format: 8 Feb 14:30
                    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) + ' ' + 
                           date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
                },

                formatTime(dateString) {
                    if (!dateString) return '';
                    let date = new Date(dateString);
                    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                }
            }
        }
    </script>
</x-app-layout>
