<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, ShoppingBag, Trash2, Eye, X } from '@lucide/vue';

interface CartItemDetail {
    product_name: string;
    quantity: number;
    price: number;
}

interface CartRecord {
    id: number;
    customer: string;
    items_count: number;
    total: number;
    last_active: string;
    items: CartItemDetail[];
}

const props = defineProps<{
    carts?: CartRecord[];
    currentTeamSlug: string | null;
}>();

const toastMessage = ref('');
const triggerToast = (msg: string) => {
    toastMessage.value = msg;
    setTimeout(() => {
        if (toastMessage.value === msg) {
            toastMessage.value = '';
        }
    }, 3000);
};

// Search state
const searchQuery = ref('');

// Filtered carts
const filteredCarts = computed(() => {
    const list = props.carts || [];
    return list.filter((c) => {
        return (
            c.customer.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            c.id.toString().includes(searchQuery.value)
        );
    });
});

// Detailed Cart modal state
const selectedCart = ref<CartRecord | null>(null);

const viewCartDetails = (cart: CartRecord) => {
    selectedCart.value = cart;
};

const closeDetailsModal = () => {
    selectedCart.value = null;
};

const deleteCart = (cartId: number) => {
    if (!confirm('Are you sure you want to delete this cart?')) return;
    
    if (!props.currentTeamSlug) {
        triggerToast('⚠️ No team context found. Please refresh the page.');
        return;
    }

    router.delete(
        `/${props.currentTeamSlug}/dashboard/carts/${cartId}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                triggerToast('🛒 Cart deleted successfully.');
                if (selectedCart.value?.id === cartId) {
                    closeDetailsModal();
                }
            },
        }
    );
};
</script>

<template>
    <div class="space-y-4">
        <!-- Toast feedback inside component -->
        <div
            v-if="toastMessage"
            class="fixed top-4 right-4 z-50 rounded-lg bg-neutral-900 px-4 py-2.5 text-xs font-semibold text-white shadow-lg dark:bg-white dark:text-neutral-900"
        >
            {{ toastMessage }}
        </div>

        <div
            class="flex flex-col justify-between gap-4 border-b border-neutral-100 pb-4 sm:flex-row sm:items-center dark:border-neutral-800"
        >
            <div class="flex items-center gap-2 flex-1 max-w-md">
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search active carts by customer..."
                        class="h-10 w-full rounded-lg border border-neutral-200 bg-white pr-4 pl-10 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    />
                </div>
            </div>
        </div>

        <!-- Carts Table -->
        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/40"
                        >
                            <th class="w-24 p-4 font-semibold">Cart ID</th>
                            <th class="p-4 font-semibold">Customer / Session</th>
                            <th class="w-28 p-4 font-semibold">Items Count</th>
                            <th class="w-28 p-4 font-semibold">Total Price</th>
                            <th class="w-40 p-4 font-semibold">Last Active</th>
                            <th class="w-28 p-4 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                        <tr
                            v-for="cart in filteredCarts"
                            :key="cart.id"
                            class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20"
                        >
                            <td class="p-4 font-mono font-semibold">
                                #{{ cart.id }}
                            </td>
                            <td class="p-4 font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ cart.customer }}
                            </td>
                            <td class="p-4 text-neutral-600 dark:text-neutral-400">
                                {{ cart.items_count }} items
                            </td>
                            <td class="p-4 font-mono font-bold text-neutral-900 dark:text-white">
                                {{ $page.props.currency_symbol ?? '$' }}{{ cart.total.toFixed(2) }}
                            </td>
                            <td class="p-4 text-neutral-500">
                                {{ cart.last_active }}
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button
                                        @click="viewCartDetails(cart)"
                                        class="flex h-7 items-center gap-1 rounded-lg bg-neutral-100 px-2.5 text-[10px] font-bold text-neutral-700 transition hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                                    >
                                        <Eye class="h-3 w-3 text-emerald-500" />
                                        View Items
                                    </button>
                                    <button
                                        @click="deleteCart(cart.id)"
                                        class="flex h-7 items-center justify-center rounded-lg border bg-white px-2 text-[10px] text-red-600 transition hover:bg-red-50 hover:text-red-700 dark:border-neutral-700 dark:bg-neutral-850"
                                    >
                                        <Trash2 class="h-3 w-3" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCarts.length === 0">
                            <td colspan="6" class="p-8 text-center text-neutral-400">
                                No active database carts found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cart Details Modal -->
        <div v-if="selectedCart" class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden">
            <div @click="closeDetailsModal" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
            <div
                class="relative w-full max-w-lg space-y-4 rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold tracking-tight">Cart Details (#{{ selectedCart.id }})</h3>
                        <p class="text-[10px] text-neutral-500">Customer: {{ selectedCart.customer }}</p>
                    </div>
                    <button @click="closeDetailsModal" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-3">
                    <h4 class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Cart items</h4>
                    <div class="max-h-60 overflow-y-auto rounded-lg border border-neutral-150 dark:border-neutral-800">
                        <table class="w-full border-collapse text-left text-xs">
                            <thead>
                                <tr class="bg-neutral-50 text-neutral-500 dark:bg-neutral-800/40">
                                    <th class="p-3 font-semibold">Product Name</th>
                                    <th class="w-20 p-3 text-center font-semibold">Qty</th>
                                    <th class="w-28 p-3 text-right font-semibold">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/50">
                                <tr v-for="item in selectedCart.items" :key="item.product_name">
                                    <td class="p-3 font-semibold text-neutral-800 dark:text-neutral-200">
                                        {{ item.product_name }}
                                    </td>
                                    <td class="p-3 text-center font-mono">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold">
                                        {{ $page.props.currency_symbol ?? '$' }}{{ (item.price * item.quantity).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between border-t border-neutral-100 pt-3 dark:border-neutral-800">
                        <span class="text-xs font-bold text-neutral-600 dark:text-neutral-400">Total Value:</span>
                        <span class="font-mono text-sm font-extrabold text-emerald-600">
                            {{ $page.props.currency_symbol ?? '$' }}{{ selectedCart.total.toFixed(2) }}
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                    <button
                        @click="deleteCart(selectedCart.id)"
                        class="h-9 rounded-lg bg-red-50 px-4 text-xs font-semibold text-red-600 hover:bg-red-100 hover:text-red-700"
                    >
                        Delete Cart
                    </button>
                    <button
                        @click="closeDetailsModal"
                        class="h-9 rounded-lg border px-4 text-xs font-semibold hover:bg-neutral-50 dark:border-neutral-700"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
