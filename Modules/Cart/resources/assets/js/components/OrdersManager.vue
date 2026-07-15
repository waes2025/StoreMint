<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, Truck, X } from '@lucide/vue';

interface Order {
    id: string;
    invoice_no?: string;
    customer?: string;
    date: string;
    total: number;
    gateway: string;
    status: string;
    payment_status?: string;
    shipping_status?: string;
    tracking_number?: string | null;
    tracking_url?: string | null;
    courier?: string | null;
    shipped_at?: string | null;
    delivered_at?: string | null;
    subtotal?: number;
    tax?: number;
    discount?: number;
    shipping?: number;
    shipping_address?: string;
    db_id?: number;
}

const props = defineProps<{
    orders?: Order[];
    currentTeamSlug: string | null;
    isShipmentEnabled: boolean;
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

// Search / Filtering State
const searchQuery = ref('');
const filterStatus = ref('All');

// Filtered Orders
const filteredOrders = computed(() => {
    const list = props.orders || [];
    return list.filter((o) => {
        const matchesStatus =
            filterStatus.value === 'All' || o.shipping_status === filterStatus.value;
        const matchesSearch =
            (o.customer || '')
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            o.id.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesSearch;
    });
});

const shippingStatusLabel = (s: string | undefined) =>
    ({
        ordered: 'Order Placed',
        packed: 'Packed',
        shipped: 'Shipped',
        delivered: 'Delivered',
        cancelled: 'Cancelled',
    })[s ?? 'ordered'] ?? s;

const shippingStatusColor = (s: string | undefined) =>
    ({
        ordered: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400',
        packed: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400',
        shipped: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400',
        delivered: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400',
        cancelled: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400',
    })[s ?? 'ordered'] ?? 'bg-neutral-100 text-neutral-500';

const cancelOrder = (dbId: number) => {
    if (!props.currentTeamSlug) {
        triggerToast('⚠️ No team context found. Please refresh the page.');
        return;
    }
    router.post(
        `/${props.currentTeamSlug}/dashboard/orders/${dbId}/cancel`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => triggerToast('❌ Order cancelled.'),
        },
    );
};

// ── SHIPPING MODAL ─────────────────────────────────────────────────────────
const showShippingModal = ref(false);
const shippingOrder = ref<Order | null>(null);
const shippingForm = ref({
    shipping_status: 'ordered' as string,
    tracking_number: '',
    tracking_url: '',
    courier: '',
});

const shippingStatusOptions = [
    { value: 'ordered', label: '📋 Ordered', color: 'text-blue-600' },
    { value: 'packed', label: '📦 Packed', color: 'text-indigo-600' },
    { value: 'shipped', label: '🚚 Shipped', color: 'text-amber-600' },
    { value: 'delivered', label: '✅ Delivered', color: 'text-emerald-600' },
    { value: 'cancelled', label: '❌ Cancelled', color: 'text-red-600' },
];

const openShippingModal = (order: Order) => {
    shippingOrder.value = order;
    shippingForm.value = {
        shipping_status: order.shipping_status || 'ordered',
        tracking_number: order.tracking_number || '',
        tracking_url: order.tracking_url || '',
        courier: order.courier || '',
    };
    showShippingModal.value = true;
};

const closeShippingModal = () => {
    showShippingModal.value = false;
    shippingOrder.value = null;
};

const submitShipping = () => {
    if (!props.currentTeamSlug || !shippingOrder.value?.db_id) return;
    router.post(
        `/${props.currentTeamSlug}/dashboard/orders/${shippingOrder.value.db_id}/shipping`,
        { ...shippingForm.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeShippingModal();
                triggerToast('🚚 Shipping status updated!');
            },
        },
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
                        placeholder="Search orders..."
                        class="h-10 w-full rounded-lg border border-neutral-200 bg-white pr-4 pl-10 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    />
                </div>
                <select
                    v-model="filterStatus"
                    class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <option value="All">All Statuses</option>
                    <option value="ordered">Ordered</option>
                    <option value="packed">Packed</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <!-- Orders Table -->
        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/40"
                        >
                            <th class="w-24 p-4 font-semibold">Order ID</th>
                            <th class="w-28 p-4 font-semibold">Invoice No</th>
                            <th class="p-4 font-semibold">Customer</th>
                            <th class="w-28 p-4 font-semibold">Date</th>
                            <th class="w-24 p-4 text-center font-semibold">Total</th>
                            <th v-if="isShipmentEnabled" class="w-28 p-4 font-semibold">Shipping Status</th>
                            <th class="w-40 p-4 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                        <tr
                            v-for="order in filteredOrders"
                            :key="order.id"
                            class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20"
                        >
                            <td class="p-4 font-mono font-semibold">
                                {{ order.id }}
                            </td>
                            <td class="p-4 font-mono text-neutral-500">
                                {{ order.invoice_no || '-' }}
                            </td>
                            <td class="p-4 font-semibold">
                                {{ order.customer }}
                            </td>
                            <td class="p-4 text-neutral-500">
                                {{ order.date }}
                            </td>
                            <td class="p-4 text-center font-mono font-bold">
                                {{ $page.props.currency_symbol ?? '$' }}{{ order.total.toFixed(2) }}
                            </td>
                            <td v-if="isShipmentEnabled" class="p-4">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold',
                                        shippingStatusColor(order.shipping_status),
                                    ]"
                                >
                                    {{ shippingStatusLabel(order.shipping_status) }}
                                </span>
                                <div
                                    v-if="order.tracking_number"
                                    class="mt-1 flex items-center gap-1 font-mono text-[9px] text-neutral-400"
                                >
                                    <Truck class="h-2.5 w-2.5" />
                                    {{ order.tracking_number }}
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button
                                        v-if="
                                            isShipmentEnabled &&
                                            order.db_id &&
                                            order.shipping_status !== 'cancelled'
                                        "
                                        @click="openShippingModal(order)"
                                        class="flex h-7 items-center gap-1 rounded-lg bg-emerald-600 px-2.5 text-[10px] font-bold text-white transition hover:bg-emerald-700"
                                    >
                                        <Truck class="h-3 w-3" />
                                        Update Shipping
                                    </button>
                                    <button
                                        v-if="
                                            order.db_id &&
                                            order.shipping_status !== 'cancelled' &&
                                            order.shipping_status !== 'delivered'
                                        "
                                        @click="cancelOrder(order.db_id)"
                                        class="h-7 rounded-lg border bg-neutral-100 px-2 text-[10px] transition hover:bg-red-50 hover:text-red-500 dark:border-neutral-700 dark:bg-neutral-800"
                                    >
                                        Cancel
                                    </button>
                                    <span v-if="order.shipping_status === 'delivered'" class="text-[10px] font-bold text-emerald-600">
                                        ✅ Delivered
                                    </span>
                                    <span v-if="order.shipping_status === 'cancelled'" class="text-[10px] font-bold text-red-500">
                                        ❌ Cancelled
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredOrders.length === 0">
                            <td colspan="7" class="p-8 text-center text-neutral-400">
                                No orders found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Shipping Status Dialog -->
        <div v-if="showShippingModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden">
            <div @click="closeShippingModal" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
            <div
                class="relative w-full max-w-md space-y-4 rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <h3 class="text-base font-bold tracking-tight">Update Shipping status</h3>
                    <button @click="closeShippingModal" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Status</label>
                        <select
                            v-model="shippingForm.shipping_status"
                            class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                        >
                            <option v-for="opt in shippingStatusOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Courier Company</label>
                        <input
                            v-model="shippingForm.courier"
                            type="text"
                            placeholder="e.g. FedEx, DHL"
                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Tracking Number</label>
                        <input
                            v-model="shippingForm.tracking_number"
                            type="text"
                            placeholder="e.g. TRK9832104"
                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Tracking URL</label>
                        <input
                            v-model="shippingForm.tracking_url"
                            type="text"
                            placeholder="e.g. https://fedex.com/track/..."
                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                    <button
                        @click="closeShippingModal"
                        class="h-9 rounded-lg border px-4 text-xs font-semibold hover:bg-neutral-50 dark:border-neutral-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitShipping"
                        class="h-9 rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Update Shipping
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
