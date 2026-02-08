<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pusat Bantuan') }}
        </h2>
    </x-slot>

    <div class="h-[calc(100vh-8rem)] flex flex-col md:flex-row overflow-hidden bg-white dark:bg-gray-800 shadow-sm md:rounded-lg m-4 border border-gray-200 dark:border-gray-700" 
         x-data="supportCenter({{ Auth::id() }})">
        
        <!-- Left Sidebar: Conversation List -->
        <div class="w-full md:w-1/3 lg:w-1/4 border-r border-gray-200 dark:border-gray-700 flex flex-col bg-gray-50 dark:bg-gray-900"
             :class="{'hidden md:flex': activeConversation, 'flex': !activeConversation}">
            
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 z-10">
                <div x-show="error" class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm" x-text="error" style="display: none;"></div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-100">Riwayat Pesan</h3>
                    <button @click="openNewChatModal = true" 
                            class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
                
                <!-- Search -->
                <div class="relative">
                    <input type="text" x-model="search" @input.debounce.500ms="fetchConversations()" 
                           placeholder="Cari No. Order / Produk..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 transition">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Conversation List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar" @scroll="onScroll">
                <template x-if="loadingConversations && conversations.length === 0">
                    <div class="p-4 space-y-4">
                        <template x-for="i in 5">
                            <div class="animate-pulse flex space-x-4">
                                <div class="rounded-full bg-gray-200 dark:bg-gray-700 h-10 w-10"></div>
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!loadingConversations && conversations.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400 p-6 text-center">
                        <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <p>Belum ada percakapan.</p>
                        <button @click="openNewChatModal = true" class="mt-2 text-blue-600 hover:underline">Mulai Baru</button>
                    </div>
                </template>

                <ul>
                    <template x-for="conv in conversations" :key="conv.id">
                        <li @click="selectConversation(conv)" 
                            class="p-4 border-b border-gray-100 dark:border-gray-700 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-800 transition relative group"
                            :class="{'bg-blue-50 dark:bg-gray-800 border-l-4 border-l-blue-600': activeConversation && activeConversation.id === conv.id}">
                            
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm truncate" x-text="'Order #' + conv.order_number"></span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="conv.last_message_at"></span>
                            </div>
                            
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1 truncate" x-text="conv.product_summary"></div>
                            
                            <div class="flex justify-between items-end">
                                <p class="text-sm text-gray-600 dark:text-gray-300 truncate w-3/4" 
                                   :class="{'font-bold text-gray-900 dark:text-white': conv.unread_count > 0}"
                                   x-text="conv.last_message || 'Lampiran Gambar'"></p>
                                
                                <template x-if="conv.unread_count > 0">
                                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm" x-text="conv.unread_count"></span>
                                </template>
                            </div>

                            <!-- Status Badge -->
                            <div class="mt-2 flex items-center space-x-2">
                                <span class="px-2 py-0.5 text-[10px] rounded-full border"
                                      :class="getStatusClass(conv.support_status || 'open')"
                                      x-text="(conv.support_status || 'open').replace('_', ' ').toUpperCase()"></span>
                                <span class="text-[10px] text-gray-500 italic truncate" 
                                      x-text="getStatusDesc(conv.support_status || 'open')"></span>
                            </div>
                        </li>
                    </template>
                </ul>
                
                <div x-show="loadingMore" class="p-4 text-center">
                    <svg class="animate-spin h-5 w-5 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Right Main Area: Chat Window -->
        <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 relative h-full"
             :class="{'flex': activeConversation, 'hidden md:flex': !activeConversation}">
            
            <!-- Default State (No chat selected) -->
            <template x-if="!activeConversation">
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 p-8 text-center bg-gray-50 dark:bg-gray-900/50">
                    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Pusat Bantuan Silverchannel</h3>
                    <p class="max-w-md text-sm">Pilih percakapan dari daftar di sebelah kiri atau mulai percakapan baru untuk mendapatkan bantuan terkait pesanan Anda.</p>
                    <button @click="openNewChatModal = true" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow">
                        Mulai Percakapan Baru
                    </button>
                </div>
            </template>

            <!-- Active Chat View -->
            <template x-if="activeConversation">
                <div class="flex flex-col h-full">
                    <!-- Chat Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-white dark:bg-gray-800 shadow-sm z-10">
                        <div class="flex items-center">
                            <button @click="activeConversation = null" class="md:hidden mr-3 text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                    <span x-text="'Order #' + activeConversation.order_number"></span>
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-blue-100 text-blue-800" x-text="activeConversation.status"></span>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 text-[10px] rounded-full border"
                                            :class="getStatusClass(activeConversation.support_status || 'open')"
                                            x-text="(activeConversation.support_status || 'open').replace('_', ' ').toUpperCase()"></span>
                                        <span class="text-xs text-gray-500 italic hidden sm:inline-block" 
                                            x-text="getStatusDesc(activeConversation.support_status || 'open')"></span>
                                    </div>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="activeConversation.product_summary"></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- CS Indicator -->
                            <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                CS Online
                            </div>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900 scroll-smooth" x-ref="chatContainer">
                        <template x-if="loadingMessages">
                             <div class="flex justify-center py-4"><svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                        </template>
                        
                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex w-full" :class="msg.is_sender ? 'justify-end' : 'justify-start'">
                                <div class="max-w-[75%] md:max-w-[60%] flex flex-col" :class="msg.is_sender ? 'items-end' : 'items-start'">
                                    <div class="px-4 py-2 rounded-2xl shadow-sm relative text-sm"
                                         :class="msg.is_sender ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-bl-none border border-gray-100 dark:border-gray-600'">
                                        
                                        <template x-if="!msg.is_sender">
                                            <p class="text-[10px] text-gray-400 mb-1 font-bold" x-text="msg.sender_name"></p>
                                        </template>

                                        <p class="whitespace-pre-wrap leading-relaxed" x-text="msg.message"></p>
                                        
                                        <template x-if="msg.attachment_url">
                                            <div class="mt-2">
                                                <a :href="msg.attachment_url" target="_blank" class="block overflow-hidden rounded-lg border border-white/20">
                                                    <img :src="msg.attachment_url" class="max-w-full h-auto max-h-48 object-cover" alt="Attachment">
                                                </a>
                                            </div>
                                        </template>

                                        <div class="flex items-center justify-end space-x-1 mt-1 opacity-70">
                                            <span class="text-[10px]" x-text="msg.created_at"></span>
                                            <template x-if="msg.is_sender">
                                                <span>
                                                    <svg x-show="msg.is_read" class="w-3 h-3 text-blue-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                                                    <span x-show="!msg.is_read" class="text-[10px]">✓</span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Input Area -->
                    <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        <template x-if="!activeConversation.support_status || activeConversation.support_status !== 'closed'">
                            <form @submit.prevent="sendMessage" class="relative flex items-end gap-2">
                                <button type="button" @click="$refs.fileInput.click()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                </button>
                                <input type="file" x-ref="fileInput" class="hidden" @change="handleFileSelect">
                                
                                <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-2xl px-4 py-2 flex flex-col">
                                    <div x-show="previewFile" class="flex items-center justify-between p-2 mb-2 bg-white dark:bg-gray-600 rounded-lg shadow-sm">
                                        <span class="text-xs truncate max-w-[150px]" x-text="previewFile?.name"></span>
                                        <button type="button" @click="clearFile" class="text-red-500 hover:text-red-700">×</button>
                                    </div>
                                    <textarea x-model="newMessage" 
                                              @keydown.enter="if(!$event.shiftKey) { $event.preventDefault(); sendMessage(); }"
                                              rows="1" 
                                              placeholder="Tulis pesan..." 
                                              class="w-full bg-transparent border-none focus:ring-0 text-gray-800 dark:text-gray-200 resize-none max-h-32 text-sm p-0 disabled:opacity-50"
                                              :disabled="sending"></textarea>
                                </div>

                                <button type="submit" 
                                        :disabled="(!newMessage.trim() && !previewFile) || sending"
                                        class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                                    <svg x-show="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    <svg x-show="sending" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </button>
                            </form>
                        </template>
                        <template x-if="activeConversation.support_status === 'closed'">
                            <div class="text-center py-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    <span>Tiket ini telah ditutup. Anda tidak dapat mengirim pesan lagi.</span>
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- New Chat Modal -->
        <div x-show="openNewChatModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="openNewChatModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Mulai Percakapan Baru
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        Pilih pesanan yang ingin Anda tanyakan.
                                    </p>
                                    
                                    <!-- Order Selection List (Should be fetched via AJAX ideally, but for now linking to order page or redirect) -->
                                    <!-- For MVP: Simple input or we could load orders here. Let's redirect to My Orders with instruction -->
                                    <div class="space-y-2">
                                        <a href="{{ route('silverchannel.orders.index') }}" class="block p-3 border border-gray-200 dark:border-gray-700 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <div class="font-bold text-blue-600">Ke Daftar Pesanan Saya</div>
                                            <div class="text-xs text-gray-500">Pilih pesanan dari daftar dan klik tombol Chat.</div>
                                        </a>
                                        <!-- Later: Implement live search for orders here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="openNewChatModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function supportCenter(currentUserId) {
            return {
                currentUserId: currentUserId,
                conversations: [],
                activeConversation: null,
                messages: [],
                search: '',
                loadingConversations: false,
                loadingMessages: false,
                loadingMore: false,
                page: 1,
                hasMore: true,
                newMessage: '',
                previewFile: null,
                file: null,
                sending: false,
                openNewChatModal: false,
                pollInterval: null,
                echoChannel: null,
                error: null,

                init() {
                    this.fetchConversations();
                    // Poll for new messages every 10 seconds (Fallback)
                    this.pollInterval = setInterval(() => {
                        // Only poll if Echo is not active or for redundancy
                        if (typeof window.Echo === 'undefined') {
                            if (this.activeConversation) {
                                this.fetchMessages(this.activeConversation.id, true);
                            }
                            this.fetchConversations(true);
                        }
                    }, 10000);
                },

                setupEcho(orderId) {
                    if (typeof window.Echo !== 'undefined') {
                        // Leave previous channel if any
                        if (this.echoChannel) {
                            window.Echo.leave(this.echoChannel);
                        }
                        
                        this.echoChannel = `orders.${orderId}`;
                        window.Echo.private(this.echoChannel)
                            .listen('MessageSent', (e) => {
                                console.log('New message received:', e.message);
                                // Determine if is_sender (should be false for incoming)
                                // But we need to be careful not to duplicate if we just sent it
                                // The event is broadcast to others, so usually sender doesn't receive it 
                                // if we use toOthers(). But in Vue/Alpine we might push manually.
                                
                                // Since we use broadcast(...)->toOthers(), the sender won't get it via Echo.
                                // So we can safely push.
                                
                                // Check if already exists just in case
                                if (!this.messages.find(m => m.id === e.message.id)) {
                                     // Adjust is_sender logic:
                                     // The event payload has sender_id.
                                     // We need current user ID.
                                     e.message.is_sender = (e.message.sender_id == this.currentUserId);
                                     
                                     this.messages.push(e.message);
                                     this.scrollToBottom();
                                     
                                     // Update conversation list preview
                                     const conv = this.conversations.find(c => c.id === orderId);
                                     if (conv) {
                                         conv.last_message = e.message.message || 'Lampiran';
                                         conv.last_message_at = 'Baru saja';
                                         if (!e.message.is_sender) {
                                             conv.unread_count = (conv.unread_count || 0) + 1;
                                         }
                                     }
                                }
                            });
                    }
                },

                fetchConversations(silent = false) {
                    if (!silent) this.loadingConversations = true;
                    
                    let url = `{{ route('silverchannel.support.conversations') }}?page=${this.page}`;
                    if (this.search) url += `&search=${this.search}`;

                    fetch(url)
                        .then(res => {
                            if (!res.ok) throw new Error('Network response was not ok');
                            return res.json();
                        })
                        .then(data => {
                            if (this.page === 1) {
                                this.conversations = data.data;
                            } else {
                                this.conversations = [...this.conversations, ...data.data];
                            }
                            this.hasMore = !!data.next_page_url;
                        })
                        .catch(err => {
                            console.error('Error fetching conversations:', err);
                            if (!silent) this.error = 'Gagal memuat percakapan. Silakan coba lagi.';
                        })
                        .finally(() => {
                            this.loadingConversations = false;
                            this.loadingMore = false;
                        });
                },

                onScroll(e) {
                    const el = e.target;
                    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 50 && this.hasMore && !this.loadingMore) {
                        this.loadingMore = true;
                        this.page++;
                        this.fetchConversations(true);
                    }
                },

                selectConversation(conv) {
                    this.activeConversation = conv;
                    this.messages = [];
                    this.fetchMessages(conv.id);
                    // Mark as read in local list immediately
                    conv.unread_count = 0;
                    
                    // Setup Echo
                    this.setupEcho(conv.id);
                },

                fetchMessages(orderId, silent = false) {
                    if (!silent) this.loadingMessages = true;
                    
                    fetch(`{{ url('silverchannel/support/messages') }}/${orderId}`)
                        .then(res => {
                            if (!res.ok) throw new Error('Network response was not ok');
                            return res.json();
                        })
                        .then(data => {
                            this.messages = data.messages;
                            if (!silent) this.scrollToBottom();
                        })
                        .catch(err => {
                            console.error('Error fetching messages:', err);
                        })
                        .finally(() => {
                            this.loadingMessages = false;
                        });
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.chatContainer;
                        if (container) container.scrollTop = container.scrollHeight;
                    });
                },

                handleFileSelect(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.file = file;
                    this.previewFile = { name: file.name };
                },

                getStatusClass(status) {
                    const def = this.statusDefinitions[status] || this.statusDefinitions['open'];
                    return def.class;
                },
                statusDefinitions: {
                    'open': {
                        class: 'bg-blue-100 text-blue-800 border-blue-200',
                        label: 'OPEN',
                        desc: 'Tiket baru masuk, belum ditangani.'
                    },
                    'pending': {
                        class: 'bg-orange-100 text-orange-800 border-orange-200',
                        label: 'PENDING',
                        desc: 'Menunggu respon/info tambahan.'
                    },
                    'on_progress': {
                        class: 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        label: 'ON PROGRESS',
                        desc: 'Sedang ditangani oleh tim.'
                    },
                    'escalated': {
                        class: 'bg-purple-100 text-purple-800 border-purple-200',
                        label: 'ESCALATED',
                        desc: 'Dinaikkan ke level support lebih tinggi.'
                    },
                    'resolved': {
                        class: 'bg-green-100 text-green-800 border-green-200',
                        label: 'RESOLVED',
                        desc: 'Solusi diberikan, menunggu konfirmasi.'
                    },
                    'closed': {
                        class: 'bg-gray-100 text-gray-800 border-gray-200',
                        label: 'CLOSED',
                        desc: 'Tiket selesai dan ditutup.'
                    },
                    'reopened': {
                        class: 'bg-pink-100 text-pink-800 border-pink-200',
                        label: 'REOPENED',
                        desc: 'Tiket dibuka kembali.'
                    }
                },
                getStatusDesc(status) {
                    return (this.statusDefinitions[status] || this.statusDefinitions['open']).desc;
                },

                clearFile() {
                    this.file = null;
                    this.previewFile = null;
                    this.$refs.fileInput.value = '';
                },

                sendMessage() {
                    // Validation: Ensure message or file exists
                    if ((!this.newMessage.trim() && !this.file) || this.sending) {
                        return;
                    }

                    this.sending = true;
                    const formData = new FormData();
                    formData.append('message', this.newMessage);
                    if (this.file) {
                        formData.append('attachment', this.file);
                    }
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch(`{{ url('silverchannel/support/messages') }}/${this.activeConversation.id}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Only push if not already added by Echo (though Echo usually handles this, we do it for immediate feedback)
                            if (!this.messages.find(m => m.id === data.message.id)) {
                                this.messages.push(data.message);
                            }
                            this.newMessage = '';
                            this.clearFile();
                            this.scrollToBottom();
                            
                            // Update list preview
                            const conv = this.conversations.find(c => c.id === this.activeConversation.id);
                            if (conv) {
                                conv.last_message = data.message.message || 'Lampiran';
                                conv.last_message_at = 'Baru saja';
                            }
                        } else {
                            // Handle server-side application error
                            console.error('Failed to send message:', data);
                            alert('Gagal mengirim pesan: ' + (data.message || 'Terjadi kesalahan sistem.'));
                        }
                    })
                    .catch(err => {
                        console.error('Network error:', err);
                        alert('Gagal mengirim pesan. Periksa koneksi internet Anda.');
                    })
                    .finally(() => {
                        this.sending = false;
                        // Keep focus on textarea
                        this.$nextTick(() => {
                             // Optional: refocus if needed, but might annoy user on mobile
                        });
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
