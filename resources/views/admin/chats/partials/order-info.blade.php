<div class="space-y-6">
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
            <template x-for="p in ['low', 'medium', 'high', 'urgent']" :key="p">
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
                <template x-for="(tag, index) in activeOrder.chat_tags" :key="tag + '-' + index">
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
            <div :class="{'hidden': !showStatusDropdown}" 
                 x-ref="statusDropdown"
                 style="max-height: 60vh;"
                 class="absolute right-0 mt-2 w-full rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none overflow-auto z-50 origin-top-right bottom-full mb-2">
                <div class="py-1">
                    <template x-for="(def, key) in statusDefinitions" :key="key">
                        <button @click="updateStatus(key); showStatusDropdown = false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center group">
                            <span class="w-2.5 h-2.5 rounded-full mr-3" :class="def.color.split(' ')[0]"></span>
                            <span class="flex-1" :class="activeOrder.support_status === key ? 'font-bold text-gray-900' : 'text-gray-700'" x-text="def.label"></span>
                            <template x-if="activeOrder.support_status === key">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </template>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>