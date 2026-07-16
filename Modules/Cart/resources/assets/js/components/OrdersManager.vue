<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, Truck, X, RefreshCw, ExternalLink, MapPin, Package, Clock, Info, CheckCircle2, AlertCircle, ChevronDown } from '@lucide/vue';

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

const handleStatusChange = () => {
    if (!['shipped', 'delivered'].includes(shippingForm.value.shipping_status)) {
        shippingForm.value.courier = '';
        shippingForm.value.tracking_number = '';
        shippingForm.value.tracking_url = '';
    }
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

// ── PATHAO INTEGRATION STATE & METHODS ──────────────────────────────────────
const showPathaoBookingModal = ref(false);
const pathaoBookingOrder = ref<Order | null>(null);
const isBooking = ref(false);
const isEstimating = ref(false);
const estimatedCharge = ref<number | null>(null);

const pathaoBookingForm = ref({
    recipient_name: '',
    recipient_phone: '',
    recipient_address: '',
    recipient_city: '' as string | number,
    recipient_zone: '' as string | number,
    recipient_area: '' as string | number,
    item_weight: 0.5,
    item_type: 2, // Parcel
    special_instruction: '',
    amount_to_collect: 0,
});

const pathaoCities = ref<any[]>([]);
const pathaoZones = ref<any[]>([]);
const pathaoAreas = ref<any[]>([]);
const isPathaoLoadingZones = ref(false);
const isPathaoLoadingAreas = ref(false);

const showPathaoTrackingModal = ref(false);
const trackingOrder = ref<Order | null>(null);
const trackingHistory = ref<any[]>([]);
const isTrackingLoading = ref(false);

const openPathaoBookingModal = async (order: Order) => {
    pathaoBookingOrder.value = order;
    pathaoBookingForm.value = {
        recipient_name: order.customer || '',
        recipient_phone: '',
        recipient_address: order.shipping_address || '',
        recipient_city: '',
        recipient_zone: '',
        recipient_area: '',
        item_weight: 0.5,
        item_type: 2,
        special_instruction: '',
        amount_to_collect: order.total || 0,
    };
    estimatedCharge.value = null;
    showPathaoBookingModal.value = true;

    try {
        const res = await fetch('/dashboard/shipments/cities');
        const data = await res.json();
        if (data.success) {
            pathaoCities.value = data.data;
        }
    } catch (e) {
        console.error("Failed to load cities", e);
    }
};

const handlePathaoCityChange = async () => {
    pathaoBookingForm.value.recipient_zone = '';
    pathaoBookingForm.value.recipient_area = '';
    pathaoZones.value = [];
    pathaoAreas.value = [];
    if (!pathaoBookingForm.value.recipient_city) return;

    isPathaoLoadingZones.value = true;
    try {
        const response = await fetch(`/dashboard/shipments/zones/${pathaoBookingForm.value.recipient_city}`);
        const result = await response.json();
        if (result.success) {
            pathaoZones.value = result.data;
        }
    } catch (e) {
        console.error(e);
    } finally {
        isPathaoLoadingZones.value = false;
    }
};

const handlePathaoZoneChange = async () => {
    pathaoBookingForm.value.recipient_area = '';
    pathaoAreas.value = [];
    if (!pathaoBookingForm.value.recipient_zone) return;

    isPathaoLoadingAreas.value = true;
    try {
        const response = await fetch(`/dashboard/shipments/areas/${pathaoBookingForm.value.recipient_zone}`);
        const result = await response.json();
        if (result.success) {
            pathaoAreas.value = result.data;
        }
    } catch (e) {
        console.error(e);
    } finally {
        isPathaoLoadingAreas.value = false;
    }
};

const estimateCharge = async () => {
    if (!pathaoBookingOrder.value?.db_id) return;
    if (!pathaoBookingForm.value.recipient_city || !pathaoBookingForm.value.recipient_zone) {
        triggerToast("⚠️ City and Zone are required for pricing estimation.");
        return;
    }

    isEstimating.value = true;
    try {
        const response = await fetch(`/${props.currentTeamSlug}/dashboard/orders/${pathaoBookingOrder.value.db_id}/pathao-estimate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                recipient_city: pathaoBookingForm.value.recipient_city,
                recipient_zone: pathaoBookingForm.value.recipient_zone,
                item_weight: pathaoBookingForm.value.item_weight,
                item_type: pathaoBookingForm.value.item_type,
            })
        });
        const result = await response.json();
        if (result.success) {
            estimatedCharge.value = result.data.price || result.data.delivery_fee || 0;
            triggerToast("💰 Price estimated successfully!");
        } else {
            triggerToast(`❌ ${result.message}`);
        }
    } catch (e) {
        triggerToast("❌ Failed to estimate price.");
    } finally {
        isEstimating.value = false;
    }
};

const submitPathaoBooking = async () => {
    if (!pathaoBookingOrder.value?.db_id) return;
    if (!pathaoBookingForm.value.recipient_phone) {
        triggerToast("⚠️ Recipient phone is required.");
        return;
    }
    isBooking.value = true;

    try {
        const response = await fetch(`/${props.currentTeamSlug}/dashboard/orders/${pathaoBookingOrder.value.db_id}/pathao-book`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                recipient_city: pathaoBookingForm.value.recipient_city,
                recipient_zone: pathaoBookingForm.value.recipient_zone,
                recipient_area: pathaoBookingForm.value.recipient_area,
                item_weight: pathaoBookingForm.value.item_weight,
                item_type: pathaoBookingForm.value.item_type,
                amount_to_collect: pathaoBookingForm.value.amount_to_collect,
                special_instruction: pathaoBookingForm.value.special_instruction,
            })
        });
        const result = await response.json();
        if (result.success) {
            triggerToast(`🚚 Shipment Booked! Tracking ID: ${result.data.consignment_id}`);
            showPathaoBookingModal.value = false;
            router.reload({ only: ['orders'] });
        } else {
            triggerToast(`❌ ${result.message}`);
        }
    } catch (e) {
        triggerToast("❌ Failed to book shipment.");
    } finally {
        isBooking.value = false;
    }
};

const cancelPathaoShipment = async (dbId: number) => {
    if (!confirm("Are you sure you want to cancel this Pathao shipment?")) return;
    
    try {
        const response = await fetch(`/${props.currentTeamSlug}/dashboard/orders/${dbId}/pathao-cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            }
        });
        const result = await response.json();
        if (result.success) {
            triggerToast("❌ Pathao shipment cancelled successfully!");
            router.reload({ only: ['orders'] });
        } else {
            triggerToast(`❌ ${result.message}`);
        }
    } catch (e) {
        triggerToast("❌ Failed to cancel shipment.");
    }
};

const refreshPathaoStatus = async (dbId: number) => {
    triggerToast("🔄 Refreshing shipment status...");
    try {
        const response = await fetch(`/${props.currentTeamSlug}/dashboard/orders/${dbId}/pathao-sync`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            }
        });
        const result = await response.json();
        if (result.success) {
            triggerToast(`✅ Status synced: ${result.data.order_status}`);
            router.reload({ only: ['orders'] });
        } else {
            triggerToast(`❌ ${result.message}`);
        }
    } catch (e) {
        triggerToast("❌ Failed to sync status.");
    }
};

const openTrackingModal = async (order: Order) => {
    trackingOrder.value = order;
    trackingHistory.value = [];
    showPathaoTrackingModal.value = true;
    isTrackingLoading.value = true;

    try {
        const response = await fetch(`/${props.currentTeamSlug}/dashboard/orders/${order.db_id}/pathao-sync`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            }
        });
        const result = await response.json();
        if (result.success) {
            const rawDetails = result.data;
            trackingHistory.value = rawDetails.history || [
                {
                    status: rawDetails.order_status || 'Pending',
                    time: rawDetails.updated_at || rawDetails.created_at || new Date().toISOString(),
                    description: `Order is currently marked as: ${rawDetails.order_status || 'Pending'}`
                }
            ];
        }
    } catch (e) {
        console.error(e);
    } finally {
        isTrackingLoading.value = false;
    }
};

const activeDropdownId = ref<string | null>(null);

const toggleDropdown = (orderId: string, event: Event) => {
    event.stopPropagation();
    if (activeDropdownId.value === orderId) {
        activeDropdownId.value = null;
    } else {
        activeDropdownId.value = orderId;
    }
};

const closeDropdown = () => {
    activeDropdownId.value = null;
};

onMounted(() => {
    window.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
});

const hasActions = (order: Order) => {
    if (!order.db_id) return false;
    
    const canBookPathao = props.isShipmentEnabled && 
                          order.courier !== 'Pathao' && 
                          order.shipping_status !== 'cancelled' && 
                          order.shipping_status !== 'delivered';
                          
    const isPathaoActive = props.isShipmentEnabled && 
                           order.courier === 'Pathao' && 
                           order.tracking_number;
                           
    const canUpdateShipping = props.isShipmentEnabled && 
                              order.courier !== 'Pathao' && 
                              order.shipping_status !== 'cancelled';
                              
    const canCancelOrder = order.shipping_status !== 'cancelled' && 
                           order.shipping_status !== 'delivered' && 
                           order.courier !== 'Pathao';

    return canBookPathao || isPathaoActive || canUpdateShipping || canCancelOrder;
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
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Action Dropdown Button -->
                                    <div v-if="hasActions(order)" class="relative inline-block text-left">
                                        <button
                                            @click="toggleDropdown(order.id, $event)"
                                            class="inline-flex h-7 items-center gap-1 rounded-lg border border-neutral-200 bg-white px-2.5 text-[10px] font-bold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300"
                                        >
                                            Actions
                                            <ChevronDown class="h-3 w-3 text-neutral-400" />
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div
                                            v-if="activeDropdownId === order.id"
                                            @click.stop
                                            class="absolute right-0 z-50 mt-1 w-44 origin-top-right rounded-lg border border-neutral-200 bg-white p-1 shadow-lg dark:border-neutral-800 dark:bg-neutral-900"
                                        >
                                            <!-- Book with Pathao -->
                                            <button
                                                v-if="
                                                    isShipmentEnabled &&
                                                    order.db_id &&
                                                    order.courier !== 'Pathao' &&
                                                    order.shipping_status !== 'cancelled' &&
                                                    order.shipping_status !== 'delivered'
                                                "
                                                @click="openPathaoBookingModal(order); activeDropdownId = null"
                                                class="flex w-full items-center gap-2 rounded-md px-2.5 py-1.5 text-left text-[10px] font-bold text-orange-600 hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-950/20"
                                            >
                                                <Truck class="h-3.5 w-3.5" />
                                                Book Pathao
                                            </button>

                                            <!-- Pathao active shipment actions -->
                                            <template v-if="isShipmentEnabled && order.courier === 'Pathao' && order.tracking_number">
                                                <button
                                                    @click="openTrackingModal(order); activeDropdownId = null"
                                                    class="flex w-full items-center gap-2 rounded-md px-2.5 py-1.5 text-left text-[10px] font-bold text-orange-600 hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-950/20"
                                                >
                                                    <Info class="h-3.5 w-3.5" />
                                                    Track Shipment
                                                </button>
                                                
                                                <button
                                                    @click="refreshPathaoStatus(order.db_id); activeDropdownId = null"
                                                    class="flex w-full items-center gap-2 rounded-md px-2.5 py-1.5 text-left text-[10px] font-bold text-neutral-700 hover:bg-neutral-50 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                                >
                                                    <RefreshCw class="h-3.5 w-3.5" />
                                                    Sync Status
                                                </button>

                                                <button
                                                    v-if="order.shipping_status !== 'cancelled' && order.shipping_status !== 'delivered'"
                                                    @click="cancelPathaoShipment(order.db_id); activeDropdownId = null"
                                                    class="flex w-full items-center gap-2 rounded-md px-2.5 py-1.5 text-left text-[10px] font-bold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20"
                                                >
                                                    <X class="h-3.5 w-3.5" />
                                                    Cancel Shipment
                                                </button>
                                            </template>

                                            <!-- Standard Manual Update Shipping -->
                                            <button
                                                v-if="
                                                    isShipmentEnabled &&
                                                    order.db_id &&
                                                    order.courier !== 'Pathao' &&
                                                    order.shipping_status !== 'cancelled'
                                                "
                                                @click="openShippingModal(order); activeDropdownId = null"
                                                class="flex w-full items-center gap-2 rounded-md px-2.5 py-1.5 text-left text-[10px] font-bold text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/20"
                                            >
                                                <Truck class="h-3.5 w-3.5" />
                                                Update Shipping
                                            </button>

                                            <!-- Standard Cancel Order -->
                                            <button
                                                v-if="
                                                    order.db_id &&
                                                    order.shipping_status !== 'cancelled' &&
                                                    order.shipping_status !== 'delivered' &&
                                                    order.courier !== 'Pathao'
                                                "
                                                @click="cancelOrder(order.db_id); activeDropdownId = null"
                                                class="flex w-full items-center gap-2 rounded-md px-2.5 py-1.5 text-left text-[10px] font-bold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20"
                                            >
                                                <X class="h-3.5 w-3.5" />
                                                Cancel Order
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Read-only states when there are no actions -->
                                    <span v-if="order.shipping_status === 'delivered' && order.courier !== 'Pathao'" class="text-[10px] font-bold text-emerald-600">
                                        ✅ Delivered
                                    </span>
                                    <span v-if="order.shipping_status === 'cancelled' && order.courier !== 'Pathao'" class="text-[10px] font-bold text-red-500">
                                        ❌ Cancelled
                                    </span>
                                    <span v-else-if="!hasActions(order)" class="text-neutral-400">-</span>
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
                            @change="handleStatusChange"
                            class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                        >
                            <option v-for="opt in shippingStatusOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 transform -translate-y-2 scale-95"
                        enter-to-class="opacity-100 transform translate-y-0 scale-100"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 transform translate-y-0 scale-100"
                        leave-to-class="opacity-0 transform -translate-y-2 scale-95"
                    >
                        <div v-if="['shipped', 'delivered'].includes(shippingForm.shipping_status)" class="space-y-4 overflow-hidden pt-1">
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
                    </Transition>
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

        <!-- Pathao Booking Dialog -->
        <div v-if="showPathaoBookingModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4">
            <div @click="showPathaoBookingModal = false" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
            <div
                class="relative w-full max-w-lg space-y-4 rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <div>
                        <h3 class="text-base font-bold tracking-tight">Book Pathao Shipment</h3>
                        <p class="text-xs text-neutral-400">Order ID: {{ pathaoBookingOrder?.id }}</p>
                    </div>
                    <button @click="showPathaoBookingModal = false" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                    <!-- Recipient Details -->
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase">Recipient Name</label>
                            <input
                                v-model="pathaoBookingForm.recipient_name"
                                type="text"
                                class="h-9 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800"
                                required
                            />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase">Recipient Phone</label>
                            <input
                                v-model="pathaoBookingForm.recipient_phone"
                                type="text"
                                placeholder="e.g. 017XXXXXXXX"
                                class="h-9 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800"
                                required
                            />
                        </div>
                        <div class="sm:col-span-2 flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase">Delivery Address</label>
                            <textarea
                                v-model="pathaoBookingForm.recipient_address"
                                rows="2"
                                class="rounded-lg border border-neutral-200 p-2.5 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800 resize-none"
                                required
                            ></textarea>
                        </div>
                    </div>

                    <!-- Location Dropdowns -->
                    <div class="grid gap-3 sm:grid-cols-3 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase">City</label>
                            <select
                                v-model="pathaoBookingForm.recipient_city"
                                @change="handlePathaoCityChange"
                                class="h-9 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800"
                                required
                            >
                                <option value="">Select City</option>
                                <option v-for="city in pathaoCities" :key="city.city_id" :value="city.city_id">
                                    {{ city.city_name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase flex items-center gap-1">
                                Zone
                                <span v-if="isPathaoLoadingZones" class="h-2 w-2 rounded-full bg-orange-500 animate-ping"></span>
                            </label>
                            <select
                                v-model="pathaoBookingForm.recipient_zone"
                                @change="handlePathaoZoneChange"
                                :disabled="isPathaoLoadingZones"
                                class="h-9 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800 disabled:opacity-50"
                                required
                            >
                                <option value="">Select Zone</option>
                                <option v-for="zone in pathaoZones" :key="zone.zone_id" :value="zone.zone_id">
                                    {{ zone.zone_name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase flex items-center gap-1">
                                Area
                                <span v-if="isPathaoLoadingAreas" class="h-2 w-2 rounded-full bg-orange-500 animate-ping"></span>
                            </label>
                            <select
                                v-model="pathaoBookingForm.recipient_area"
                                :disabled="isPathaoLoadingAreas"
                                class="h-9 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800 disabled:opacity-50"
                                required
                            >
                                <option value="">Select Area</option>
                                <option v-for="area in pathaoAreas" :key="area.area_id" :value="area.area_id">
                                    {{ area.area_name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Weight & Price & COD Details -->
                    <div class="grid gap-3 sm:grid-cols-3 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase">Item Type</label>
                            <select
                                v-model="pathaoBookingForm.item_type"
                                class="h-9 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800"
                            >
                                <option :value="1">Document</option>
                                <option :value="2">Parcel</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase">Weight (KG)</label>
                            <input
                                v-model="pathaoBookingForm.item_weight"
                                type="number"
                                step="0.1"
                                class="h-9 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-neutral-400 uppercase">COD Amount</label>
                            <input
                                v-model="pathaoBookingForm.amount_to_collect"
                                type="number"
                                class="h-9 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                        <label class="text-[10px] font-bold text-neutral-400 uppercase">Special Instructions</label>
                        <input
                            v-model="pathaoBookingForm.special_instruction"
                            type="text"
                            placeholder="e.g. Call before delivery"
                            class="h-9 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-orange-500 dark:border-neutral-800 dark:bg-neutral-800"
                        />
                    </div>

                    <!-- Estimated price output card -->
                    <div v-if="estimatedCharge !== null" class="rounded-lg bg-orange-50 p-3.5 border border-orange-100 dark:bg-orange-950/20 dark:border-orange-900/50">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-orange-800 dark:text-orange-300">Estimated Delivery Charge:</span>
                            <span class="font-mono font-bold text-orange-900 dark:text-orange-200">{{ estimatedCharge }} BDT</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center gap-3 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                    <button
                        @click="estimateCharge"
                        :disabled="isEstimating"
                        class="h-9 rounded-lg border border-orange-200 bg-orange-50 text-orange-700 px-4 text-xs font-semibold hover:bg-orange-100 transition disabled:opacity-50 dark:border-orange-900 dark:bg-orange-950/20 dark:text-orange-400"
                    >
                        {{ isEstimating ? 'Estimating...' : '💰 Estimate Price' }}
                    </button>
                    <div class="flex gap-2">
                        <button
                            @click="showPathaoBookingModal = false"
                            class="h-9 rounded-lg border px-4 text-xs font-semibold hover:bg-neutral-50 dark:border-neutral-700"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitPathaoBooking"
                            :disabled="isBooking"
                            class="h-9 rounded-lg bg-orange-600 px-4 text-xs font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50"
                        >
                            {{ isBooking ? 'Booking...' : 'Book Shipment' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pathao Tracking & Timeline Dialog -->
        <div v-if="showPathaoTrackingModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4">
            <div @click="showPathaoTrackingModal = false" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
            <div
                class="relative w-full max-w-md space-y-4 rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <div>
                        <h3 class="text-base font-bold tracking-tight">Pathao Shipment Tracking</h3>
                        <p class="text-xs font-mono text-neutral-400">Tracking: {{ trackingOrder?.tracking_number }}</p>
                    </div>
                    <button @click="showPathaoTrackingModal = false" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-6 py-2 max-h-[60vh] overflow-y-auto pr-1">
                    <div v-if="isTrackingLoading" class="flex flex-col items-center justify-center py-12 gap-3">
                        <RefreshCw class="h-8 w-8 text-orange-500 animate-spin" />
                        <span class="text-xs text-neutral-400">Fetching latest shipment timeline...</span>
                    </div>
                    
                    <div v-else class="space-y-4">
                        <!-- Top Summary Badge -->
                        <div class="flex items-center justify-between rounded-lg bg-neutral-50 p-3 dark:bg-neutral-800/50">
                            <span class="text-xs text-neutral-500">Current Courier Status:</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-400 px-2.5 py-0.5 text-xs font-bold uppercase">
                                {{ trackingOrder?.shipping_status }}
                            </span>
                        </div>

                        <!-- Vertical Timeline -->
                        <div class="relative pl-6 border-l border-neutral-200 dark:border-neutral-800 space-y-6 ml-3">
                            <div v-for="(log, idx) in trackingHistory" :key="idx" class="relative">
                                <!-- Dot indicator -->
                                <div class="absolute -left-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-white dark:bg-neutral-900">
                                    <div :class="[
                                        'h-2 w-2 rounded-full',
                                        idx === 0 ? 'bg-orange-500 ring-4 ring-orange-100 dark:ring-orange-950/60' : 'bg-neutral-300 dark:bg-neutral-700'
                                    ]"></div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-bold text-neutral-800 dark:text-neutral-200 capitalize">
                                        {{ log.status }}
                                    </h4>
                                    <p class="text-[11px] text-neutral-500 mt-0.5">
                                        {{ log.description || log.text || 'Status changed' }}
                                    </p>
                                    <span class="text-[9px] font-mono text-neutral-400 block mt-1">
                                        {{ new Date(log.time).toLocaleString() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-neutral-100 pt-3 dark:border-neutral-800">
                    <button
                        @click="showPathaoTrackingModal = false"
                        class="h-9 bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 rounded-lg px-4 text-xs font-semibold hover:opacity-90 transition"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
