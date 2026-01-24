import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('toast', {
    visible: false,
    message: '',
    type: 'success',
    timeoutId: null,
    onHideCallback: null,

    show(message, type = 'success', options = {}) {
        const { duration = 3000, onHide = null } = options;

        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
            this.timeoutId = null;
        }

        this.message = message;
        this.type = type;
        this.visible = true;
        this.onHideCallback = typeof onHide === 'function' ? onHide : null;

        this.timeoutId = setTimeout(() => {
            this.hide();
        }, duration);
    },

    hide() {
        this.visible = false;
        const cb = this.onHideCallback;
        this.onHideCallback = null;
        if (typeof cb === 'function') {
            cb();
        }
    }
});

Alpine.store('cart', {
    items: [],
    isOpen: false,
    loading: false,

    async init() {
        await this.fetchItems();

        // Check for open_cart query param
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('open_cart')) {
            this.open();
            // Clean up URL without reloading
            const url = new URL(window.location.href);
            url.searchParams.delete('open_cart');
            window.history.replaceState({}, document.title, url.toString());
        }
    },

    async fetchItems() {
        this.loading = true;
        try {
            const response = await fetch('/silverchannel/cart/items', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const data = await response.json();
                this.items = data.items;
            }
        } catch (error) {
            console.error('Failed to fetch cart items', error);
        } finally {
            this.loading = false;
        }
    },

    toggle() {
        this.isOpen = !this.isOpen;
    },

    open() {
        this.isOpen = true;
    },

    close() {
        this.isOpen = false;
    },

    // Called after successful server-side add
    add(itemData) {
        // itemData should be the full cart item object from server
        const existingItemIndex = this.items.findIndex(item => item.id === itemData.id);
        
        if (existingItemIndex > -1) {
            this.items[existingItemIndex] = itemData;
        } else {
            this.items.push(itemData);
        }
    },

    async remove(id) {
        this.loading = true;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch(`/silverchannel/cart/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                this.items = this.items.filter(item => item.id !== id);
            }
        } catch (error) {
            console.error('Failed to remove item', error);
            alert('Gagal menghapus item');
        } finally {
            this.loading = false;
        }
    },

    async updateQuantity(id, change) {
        const item = this.items.find(item => item.id === id);
        if (!item) return;

        const newQty = item.quantity + change;
        
        if (newQty <= 0) {
            if (confirm('Hapus item dari keranjang?')) {
                await this.remove(id);
            }
            return;
        }

        // Check stock limit (optimistic check)
        if (item.stock && newQty > item.stock) {
            alert('Stok tidak mencukupi!');
            return;
        }

        this.loading = true;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch(`/silverchannel/cart/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newQty })
            });

            if (response.ok) {
                const data = await response.json();
                item.quantity = newQty;
                // We could also update item.price if the server returns dynamic pricing
            } else {
                const data = await response.json();
                alert(data.message || 'Gagal mengubah jumlah');
            }
        } catch (error) {
            console.error('Failed to update quantity', error);
            alert('Terjadi kesalahan jaringan');
        } finally {
            this.loading = false;
        }
    },
    
    get count() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
    },

    get subtotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },
    
    formatPrice(price) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
    }
});

document.dispatchEvent(new CustomEvent('alpine:init'));

Alpine.start();
