<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pusat Pesan') }}
        </h2>
    </x-slot>

    <div class="py-2 md:py-12" x-data="chatManagement()" x-init="initChat()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg flex flex-col md:flex-row relative fixed inset-x-0 bottom-0 top-16 z-30 md:static md:z-auto md:h-[80vh] overflow-hidden"
                 :style="'height: ' + containerHeight">
                
                <!-- Left Sidebar: Conversations List -->
                <div class="w-full md:w-1/4 border-r border-gray-200 flex-col"
                     :class="(!isMobile || mobileView === 'list') ? 'flex' : 'hidden'">
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

                    <!-- List with Pagination -->
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
                        <div x-show="conversations.length === 0 && !isLoading" class="p-4 text-center text-gray-500 text-sm">
                            Tidak ada pesan ditemukan.
                        </div>
                        <div x-show="isLoading" class="p-4 text-center text-gray-500 text-sm">
                            <svg class="animate-spin h-5 w-5 mx-auto text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div class="p-3 border-t border-gray-200 bg-gray-50 flex justify-between items-center text-xs" x-show="conversations.length > 0">
                        <button @click="prevPage()" :disabled="pagination.page <= 1" class="px-2 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-50">Prev</button>
                        <span class="text-gray-600">Hal <span x-text="pagination.page"></span> dari <span x-text="pagination.last_page"></span></span>
                        <button @click="nextPage()" :disabled="pagination.page >= pagination.last_page" class="px-2 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-50">Next</button>
                    </div>
                </div>

                <!-- Middle: Chat Area -->
                <div class="w-full md:w-2/4 flex-col border-r border-gray-200"
                     :class="(!isMobile || mobileView === 'chat') ? 'flex' : 'hidden'">
                    <template x-if="!activeOrder">
                        <div class="flex-1 flex items-center justify-center text-gray-400">
                            Pilih percakapan untuk memulai
                        </div>
                    </template>

                    <template x-if="activeOrder">
                        <div class="flex-1 flex flex-col h-full overflow-hidden">
                            <!-- Mobile Header (Back Button) -->
                            <div class="md:hidden p-3 border-b border-gray-200 bg-white flex justify-between items-center shadow-sm z-40 flex-shrink-0 sticky top-0"
                                 style="position: -webkit-sticky; position: sticky; top: 0;">
                                <button @click="backToList()" class="text-gray-600 hover:text-gray-900 flex items-center text-sm font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Kembali
                                </button>
                                <div class="font-bold text-sm truncate max-w-[50%]" x-text="'#' + activeOrder.order_number"></div>
                            </div>

                            <!-- Chat Header (Desktop) -->
                            <div class="hidden md:flex p-4 border-b border-gray-200 bg-gray-50 justify-between items-center flex-shrink-0">
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
                            <div class="flex-1 overflow-y-auto px-4 pt-4 pb-8 space-y-3 bg-gray-50 min-h-0 relative z-0 scroll-smooth" 
                                 id="message-container" 
                                 style="will-change: transform; max-height: 100%;"
                                 @scroll="handleScroll">
                                <div x-show="loadingPrevious" class="text-center py-2">
                                    <svg class="animate-spin h-5 w-5 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <template x-for="msg in messages" :key="msg.id">
                                    <div class="flex flex-col" :class="msg.sender_id === {{ Auth::id() }} ? 'items-end' : 'items-start'">
                                        <div class="max-w-[75%] rounded-2xl px-3 py-2 text-[13px] leading-relaxed shadow-sm relative group"
                                             :class="msg.sender_id === {{ Auth::id() }} ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-none'">
                                            <p x-text="msg.message" class="whitespace-pre-wrap"></p>
                                            <div class="text-[10px] mt-0.5 opacity-70 text-right font-medium tracking-wide" x-text="formatTime(msg.created_at)"></div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Scroll to Bottom / New Message Button -->
                                <div x-show="userScrolledUp" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-4"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 translate-y-4"
                                     class="sticky bottom-4 left-1/2 transform -translate-x-1/2 flex justify-center z-10">
                                    <button @click="scrollToBottom('smooth', true)" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white rounded-full px-4 py-2 shadow-lg flex items-center space-x-2 text-xs font-medium transition-colors">
                                        <span x-show="showNewMessageBadge" class="flex h-2 w-2 relative mr-1">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-200 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                                        </span>
                                        <span x-text="showNewMessageBadge ? 'Pesan Baru' : 'Ke Bawah'"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Input Area -->
                            <div class="p-2 bg-white border-t border-gray-200 shadow-sm z-40 flex-shrink-0">
                                <div class="mb-2 flex space-x-2 overflow-x-auto pb-1 no-scrollbar">
                                    <!-- Quick Replies -->
                                    <template x-for="qr in quickReplies" :key="qr.id">
                                        <button @click="newMessage = qr.message" class="whitespace-nowrap px-3 py-1 bg-gray-50 text-[11px] font-medium rounded-full hover:bg-gray-100 text-gray-600 border border-gray-200 flex-shrink-0 transition active:scale-95">
                                            <span x-text="qr.title"></span>
                                        </button>
                                    </template>
                                    <button @click="showQuickReplyModal = true" class="whitespace-nowrap px-3 py-1 bg-indigo-50 text-[11px] font-medium rounded-full hover:bg-indigo-100 text-indigo-600 border border-indigo-200 flex-shrink-0 flex items-center transition active:scale-95">
                                        + Template
                                    </button>
                                </div>
                                <div class="flex items-end space-x-2">
                                    <textarea x-model="newMessage" @keydown.enter.prevent="sendMessage()" placeholder="Ketik pesan..." class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-[13px] resize-none min-h-[38px] py-2"></textarea>
                                    <button @click="sendMessage()" class="bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex-shrink-0 h-[38px] w-[44px] flex items-center justify-center shadow-sm transition active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-90" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Mobile Info Accordion (Collapsible) -->
                            <div class="md:hidden border-t border-gray-200 bg-white z-40 flex-shrink-0 pb-[env(safe-area-inset-bottom)]">
                                <button @click="showMobileInfo = !showMobileInfo" class="w-full flex justify-between items-center p-3 bg-gray-50 hover:bg-gray-100 transition">
                                    <span class="font-semibold text-sm text-gray-700">Ringkasan Order</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="{'transform rotate-180': showMobileInfo}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div :class="{'hidden': !showMobileInfo}" class="border-t border-gray-100 hidden" x-effect="if(showMobileInfo) $el.style.display = null">
                                    <div class="p-4 max-h-[40vh] overflow-y-auto bg-white text-sm">
                                        @include('admin.chats.partials.order-info')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Right: Context Panel (Desktop Only) -->
                <div class="hidden md:flex md:w-1/4 bg-gray-50 flex-col h-full overflow-y-auto">
                    <template x-if="activeOrder">
                        <div class="p-4 space-y-6">
                            @include('admin.chats.partials.order-info')
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
        <div :class="{'hidden': !showQuickReplyModal}" class="fixed inset-0 z-50 overflow-y-auto hidden" style="display: none;" x-effect="if(showQuickReplyModal) $el.style.display = null">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showQuickReplyModal = false"></div>
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
        <div :class="{'hidden': !showStatsModal}" class="fixed inset-0 z-50 overflow-y-auto hidden" style="display: none;" x-effect="if(showStatsModal) $el.style.display = null">
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
        <div :class="{'hidden': !showStatusCommentModal}" class="fixed inset-0 z-50 overflow-y-auto hidden" style="display: none;" x-effect="if(showStatusCommentModal) $el.style.display = null">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showStatusCommentModal = false"></div>
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

    @push('scripts')
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
                userScrolledUp: false,
                showNewMessageBadge: false,
                hasMoreMessages: false,
                loadingPrevious: false,
                statusDefinitions: {
                    'open': { label: 'Open', color: 'bg-blue-100 text-blue-800 border-blue-200', desc: 'Tiket baru masuk, belum ditangani' },
                    'pending': { label: 'Pending', color: 'bg-orange-100 text-orange-800 border-orange-200', desc: 'Menunggu respon pelanggan' },
                    'on_progress': { label: 'In Progress', color: 'bg-indigo-100 text-indigo-800 border-indigo-200', desc: 'Sedang ditangani tim' },
                    'escalated': { label: 'Escalated', color: 'bg-purple-100 text-purple-800 border-purple-200', desc: 'Dinaikkan ke level atas' },
                    'resolved': { label: 'Resolved', color: 'bg-green-100 text-green-800 border-green-200', desc: 'Solusi diberikan, tunggu konfirmasi' },
                    'closed': { label: 'Closed', color: 'bg-gray-800 text-white border-gray-600', desc: 'Tiket selesai dan ditutup' },
                    'reopened': { label: 'Reopened', color: 'bg-pink-100 text-pink-800 border-pink-200', desc: 'Dibuka kembali oleh pelanggan' }
                },
                
                // Pagination & Mobile View States
                mobileView: 'list',
                isMobile: false,
                showMobileInfo: false,
                isLoading: false,
                containerHeight: 'calc(100dvh - 4rem)',
                pagination: {
                    page: 1,
                    limit: 10,
                    total: 0,
                    last_page: 1
                },

                initChat() {
                    this.isMobile = window.innerWidth < 768;
                    this.updateDimensions();
                    
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 768;
                        if (!this.isMobile) this.mobileView = 'list';
                        this.updateDimensions();
                    });
                    
                    window.addEventListener('orientationchange', () => {
                         setTimeout(() => this.updateDimensions(), 100);
                    });

                    this.fetchConversations();
                    this.fetchQuickReplies();
                    
                    // Poll for conversations list updates (silent)
                    setInterval(() => {
                        if(this.pagination.page === 1) { // Only poll on first page to avoid jumping
                            this.fetchConversations(1, true); 
                        }
                    }, 15000);

                    // Poll for active chat messages
                    setInterval(() => {
                        if (this.activeOrder) {
                            this.fetchMessages(true);
                        }
                    }, 5000);
                },

                fetchConversations(page = null, silent = false) {
                    if (this.isLoading && !silent) return;
                    
                    if (page) {
                        this.pagination.page = page;
                    }
                    
                    if (!silent) this.isLoading = true;

                    let params = new URLSearchParams({
                        ...this.filters,
                        page: this.pagination.page,
                        limit: this.pagination.limit
                    });

                    fetch(`/admin/api/chats/conversations?${params.toString()}`)
                        .then(res => res.json())
                        .then(data => {
                            console.log('Conversations loaded:', data);
                            this.conversations = data.data;
                            this.pagination.total = data.total;
                            this.pagination.last_page = data.last_page;
                            this.pagination.page = data.current_page;
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error fetching conversations:', error);
                            this.isLoading = false;
                        });
                },

                nextPage() {
                    if (this.pagination.page < this.pagination.last_page) {
                        this.fetchConversations(this.pagination.page + 1);
                    }
                },

                prevPage() {
                    if (this.pagination.page > 1) {
                        this.fetchConversations(this.pagination.page - 1);
                    }
                },

                // checkScroll removed as we use pagination controls now

                backToList() {
                    this.mobileView = 'list';
                    this.activeOrder = null;
                },

                selectConversation(conv) {
                    this.activeOrder = conv;
                    this.mobileView = 'chat';
                    this.showMobileInfo = false;
                    this.fetchMessages();
                    // Scroll to bottom after load handled in fetchMessages
                },

                fetchMessages(silent = false) {
                    if (!this.activeOrder) return;
                    
                    let url = `/admin/api/chats/${this.activeOrder.id}/messages`;
                    
                    // If polling (silent) and we have messages, only get new ones
                    if (silent && this.messages.length > 0) {
                        const lastMsg = this.messages[this.messages.length - 1];
                        url += `?after_id=${lastMsg.id}`;
                    }

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            // Safety check: if user navigated away during fetch, don't update state
                            if (!this.activeOrder && silent) return;

                            this.activeOrder = data.order; // Update order details
                            
                            if (silent) {
                                if (data.messages && data.messages.length > 0) {
                                    // Filter out duplicates to prevent Alpine key errors
                                    // Use a Set for faster lookup and guaranteed uniqueness based on ID
                                    const existingIds = new Set(this.messages.map(m => m.id));
                                    const newMessages = data.messages.filter(newMsg => !existingIds.has(newMsg.id));
                                    
                                    if (newMessages.length > 0) {
                                        this.messages = [...this.messages, ...newMessages];
                                        
                                        if (this.userScrolledUp) {
                                            this.showNewMessageBadge = true;
                                        } else {
                                            this.scrollToBottom('smooth');
                                        }
                                    }
                                }
                            } else {
                                // Initial load
                                // Ensure no duplicates from server response
                                const uniqueMessages = [];
                                const seenIds = new Set();
                                if (data.messages) {
                                    data.messages.forEach(msg => {
                                        if (!seenIds.has(msg.id)) {
                                            seenIds.add(msg.id);
                                            uniqueMessages.push(msg);
                                        }
                                    });
                                }
                                this.messages = uniqueMessages;
                                this.hasMoreMessages = data.has_more;
                                this.scrollToBottom('auto');
                            }
                        });
                },

                loadPreviousMessages() {
                    if (this.loadingPrevious || !this.hasMoreMessages || this.messages.length === 0) return;
                    
                    this.loadingPrevious = true;
                    const oldestId = this.messages[0].id;
                    const container = document.getElementById('message-container');
                    if (!container) {
                         this.loadingPrevious = false;
                         return;
                    }
                    const oldScrollHeight = container.scrollHeight;
                    
                    fetch(`/admin/api/chats/${this.activeOrder.id}/messages?before_id=${oldestId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.messages && data.messages.length > 0) {
                                // Filter out duplicates to prevent Alpine key errors
                                const existingIds = new Set(this.messages.map(m => m.id));
                                const newMessages = data.messages.filter(newMsg => !existingIds.has(newMsg.id));

                                if (newMessages.length > 0) {
                                    this.messages = [...newMessages, ...this.messages];
                                    this.hasMoreMessages = data.has_more;
                                    
                                    this.$nextTick(() => {
                                        const newScrollHeight = container.scrollHeight;
                                        container.scrollTop = newScrollHeight - oldScrollHeight;
                                    });
                                }
                            } else {
                                this.hasMoreMessages = false;
                            }
                        })
                        .catch(err => {
                             console.error('Error loading previous messages:', err);
                        })
                        .finally(() => {
                            this.loadingPrevious = false;
                        });
                },

                handleScroll(e) {
                    const container = e.target;
                    if (!container) return;
                    
                    // Lazy load when scrolling to top
                    if (container.scrollTop === 0) {
                        this.loadPreviousMessages();
                    }

                    // Check if scrolled up (allow 20px buffer)
                    const isAtBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 20;
                    
                    this.userScrolledUp = !isAtBottom;
                    
                    if (isAtBottom) {
                        this.showNewMessageBadge = false;
                    }
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
                        this.scrollToBottom('smooth');
                        // this.fetchConversations(false, true); // Refresh list to update sort/preview
                    });
                },
                
                scrollToBottom(behavior = 'auto', force = false) {
                    this.$nextTick(() => {
                        const container = document.getElementById('message-container');
                        if (container) {
                            if (force || !this.userScrolledUp) {
                                container.scrollTo({
                                    top: container.scrollHeight,
                                    behavior: behavior
                                });
                                if(force) {
                                    this.userScrolledUp = false;
                                    this.showNewMessageBadge = false;
                                }
                            }
                        }
                    });
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                },

                formatTime(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
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
                     fetch(`/admin/api/chats/quick-replies`)
                        .then(res => res.json())
                        .then(data => {
                            this.quickReplies = data;
                        });
                },

                saveQuickReply() {
                    if(!this.newQR.title || !this.newQR.message) return;
                    fetch(`/admin/api/chats/quick-replies`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.newQR)
                    })
                    .then(res => res.json())
                    .then(qr => {
                        this.quickReplies.push(qr);
                        this.showQuickReplyModal = false;
                        this.newQR = { title: '', message: '' };
                    });
                },

                getStats() { // Renamed from showStats to avoid conflict if any, but logic handles it
                    // The button calls showStats() which shows modal
                },
                
                showStats() {
                    this.showStatsModal = true;
                     fetch(`/admin/api/chats/stats`)
                        .then(res => res.json())
                        .then(data => {
                            this.stats = data;
                        });
                },

                exportChat() {
                    if (!this.activeOrder) return;
                    window.open(`/admin/api/chats/export?order_id=${this.activeOrder.id}`, '_blank');
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
                    fetch(`/admin/api/chats/${this.activeOrder.id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            status: status,
                            comment: comment
                        })
                    })
                    .then(res => res.json())
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

                updateDimensions() {
                    if (this.isMobile) {
                        // Calculate exact available height for mobile
                        const vh = window.innerHeight;
                        // Subtract header height (approx 4rem/64px) if fixed, or calculate dynamically if needed
                        // Assuming app header is fixed at top
                        this.containerHeight = `${vh - 64}px`;
                    } else {
                        this.containerHeight = '80vh';
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>