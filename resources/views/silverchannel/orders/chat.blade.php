<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Open Support Ticket for Order') }} #{{ $order->order_number }}
            </h2>
            <a href="{{ route('silverchannel.orders.show', $order) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Kembali ke Detail Order') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="chatHandler({{ $order->id }}, {{ Auth::id() }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-[calc(100vh-200px)] flex flex-col">
            <!-- Chat Container -->
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex-1 flex flex-col">
                
                <!-- Chat Header / Search -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-750">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ str_replace('_', ' ', $order->status) }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ $order->created_at->format('d M Y H:i') }}
                        </span>
                    </div>
                    <div class="relative">
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Cari pesan..." 
                               class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white pl-8">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container" @scroll="checkScroll">
                    <!-- Load More Button -->
                    <template x-if="nextPageUrl">
                        <div class="text-center py-2">
                            <button @click="fetchMessages(nextPageUrl)" 
                                    :disabled="loadingMore"
                                    class="text-xs text-blue-500 hover:underline disabled:opacity-50">
                                <span x-show="!loadingMore">Load previous messages</span>
                                <span x-show="loadingMore">Loading...</span>
                            </button>
                        </div>
                    </template>

                    <template x-if="loading && messages.length === 0">
                        <div class="flex justify-center items-center h-full">
                            <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </template>

                    <template x-if="!loading && messages.length === 0">
                        <div class="flex flex-col justify-center items-center h-full text-gray-400">
                            <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <p>Belum ada pesan. Silakan mulai percakapan.</p>
                        </div>
                    </template>

                    <template x-for="msg in filteredMessages" :key="msg.id">
                        <div class="flex flex-col" :class="msg.sender_id == currentUserId ? 'items-end' : 'items-start'">
                            <div class="max-w-[80%] rounded-lg px-4 py-2 shadow-sm relative"
                                 :class="msg.sender_id == currentUserId ? 'bg-blue-600 text-white rounded-br-none' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-bl-none'">
                                
                                <div class="text-xs opacity-75 mb-1 flex justify-between gap-4">
                                    <span x-text="msg.sender.name"></span>
                                    <!-- Status indicator for sent messages -->
                                    <template x-if="msg.sender_id == currentUserId">
                                        <span class="flex items-center">
                                            <template x-if="msg.is_read">
                                                <svg class="w-3 h-3 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M5 13l4 4L19 7" />
                                                </svg>
                                            </template>
                                            <template x-if="!msg.is_read">
                                                <svg class="w-3 h-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                                
                                <div x-text="msg.message" class="whitespace-pre-wrap break-words"></div>

                                <template x-if="msg.attachment_path">
                                    <div class="mt-2">
                                        <a :href="'/storage/' + msg.attachment_path" target="_blank" class="block">
                                            <template x-if="isImage(msg.attachment_path)">
                                                <img :src="'/storage/' + msg.attachment_path" class="max-w-full rounded-md max-h-48 object-cover border border-white/20">
                                            </template>
                                            <template x-if="!isImage(msg.attachment_path)">
                                                <div class="flex items-center p-2 rounded bg-white/10 border border-white/20">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                    <span class="text-sm underline">Download Attachment</span>
                                                </div>
                                            </template>
                                        </a>
                                    </div>
                                </template>

                                <div class="text-[10px] mt-1 text-right opacity-70" x-text="formatDate(msg.created_at)"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Input Area -->
                @if($order->support_status !== 'closed')
                <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <form @submit.prevent="sendMessage" class="flex flex-col gap-2">
                        <!-- Attachment Preview -->
                        <div x-show="attachment" class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-md text-sm">
                            <span class="truncate max-w-xs" x-text="attachment ? attachment.name : ''"></span>
                            <button type="button" @click="attachment = null; $refs.fileInput.value = ''" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="button" @click="$refs.fileInput.click()" class="p-2 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                            </button>
                            <input type="file" x-ref="fileInput" class="hidden" @change="attachment = $event.target.files[0]">

                            <div class="flex-1 relative">
                                <textarea 
                                    x-model="newMessage" 
                                    @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 resize-none py-2" 
                                    rows="1"
                                    placeholder="Tulis pesan... (Shift+Enter untuk baris baru)"
                                    style="min-height: 42px; max-height: 120px;"></textarea>
                            </div>

                            <button type="submit" 
                                    :disabled="sending || (!newMessage.trim() && !attachment)"
                                    class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-md">
                                <svg x-show="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                <svg x-show="sending" class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="p-4 bg-yellow-100 border-t border-yellow-200 text-center flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-black" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-black font-semibold">Tiket ini telah ditutup. Anda tidak dapat mengirim pesan lagi.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chatHandler', (orderId, userId) => ({
                orderId: orderId,
                currentUserId: userId,
                messages: [],
                newMessage: '',
                attachment: null,
                loading: true,
                sending: false,
                searchQuery: '',
                pollInterval: null,
                nextPageUrl: null,
                loadingMore: false,
                
                init() {
                    this.fetchMessages();
                    // Poll every 5 seconds
                    this.pollInterval = setInterval(() => {
                        this.pollNewMessages();
                    }, 5000);
                },

                get filteredMessages() {
                    if (this.searchQuery === '') return this.messages;
                    return this.messages.filter(msg => 
                        (msg.message && msg.message.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                        (msg.sender.name.toLowerCase().includes(this.searchQuery.toLowerCase()))
                    );
                },

                async fetchMessages(url = null) {
                    try {
                        if (!url) {
                            this.loading = true;
                            url = `/silverchannel/orders/${this.orderId}/messages`;
                        } else {
                            this.loadingMore = true;
                        }
                        
                        const response = await axios.get(url);
                        const data = response.data;
                        
                        if (this.loadingMore) {
                            // Prepend messages and restore scroll position
                            const container = document.getElementById('messages-container');
                            const oldHeight = container.scrollHeight;
                            const oldScrollTop = container.scrollTop;

                            this.messages = [...data.data, ...this.messages];
                            
                            this.$nextTick(() => {
                                // If we were at the top (which we likely were to click load more), maintain relative position
                                // Actually, standard behavior is to scroll down by the amount of new content height
                                container.scrollTop = container.scrollHeight - oldHeight + oldScrollTop;
                            });
                        } else {
                            this.messages = data.data;
                            this.$nextTick(() => this.scrollToBottom());
                        }

                        this.nextPageUrl = data.next_page_url;

                    } catch (error) {
                        console.error('Error fetching messages:', error);
                    } finally {
                        this.loading = false;
                        this.loadingMore = false;
                    }
                },

                async pollNewMessages() {
                    if (this.messages.length === 0) return;
                    
                    const lastId = this.messages[this.messages.length - 1].id;
                    try {
                        const response = await axios.get(`/silverchannel/orders/${this.orderId}/messages?after_id=${lastId}`);
                        const newMessages = response.data.data;
                        
                        if (newMessages.length > 0) {
                            this.messages = [...this.messages, ...newMessages];
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    } catch (error) {
                        console.error('Error polling messages:', error);
                    }
                },

                async sendMessage() {
                    if ((!this.newMessage.trim() && !this.attachment) || this.sending) return;

                    this.sending = true;
                    const formData = new FormData();
                    formData.append('message', this.newMessage);
                    if (this.attachment) {
                        formData.append('attachment', this.attachment);
                    }

                    try {
                        await axios.post(`/silverchannel/orders/${this.orderId}/messages`, formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        });
                        
                        this.newMessage = '';
                        this.attachment = null;
                        this.$refs.fileInput.value = '';
                        await this.pollNewMessages(); // Immediate update
                    } catch (error) {
                        console.error('Error sending message:', error);
                        alert('Gagal mengirim pesan. Silakan coba lagi.');
                    } finally {
                        this.sending = false;
                    }
                },

                scrollToBottom() {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                },

                checkScroll() {
                    // Implement pagination logic later if needed (scroll to top to load more)
                },

                isImage(path) {
                    const extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    const ext = path.split('.').pop().toLowerCase();
                    return extensions.includes(ext);
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleString('id-ID', { 
                        day: 'numeric', month: 'short', 
                        hour: '2-digit', minute: '2-digit' 
                    });
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>