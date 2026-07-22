<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PendingInvitationsModal from '@/components/PendingInvitationsModal.vue';
import CouponsManager from '../../../Modules/Cart/resources/assets/js/components/CouponsManager.vue';
import OrdersManager from '../../../Modules/Cart/resources/assets/js/components/OrdersManager.vue';
import CartManager from '../../../Modules/Cart/resources/assets/js/components/CartManager.vue';
import PaymentsTable from '../../../Modules/Cart/resources/assets/js/components/PaymentsTable.vue';
import ProductsTable from '../../../Modules/Shop/resources/assets/js/components/ProductsTable.vue';
import MonthlyRevenue from '../../../Modules/Cart/resources/assets/js/components/MonthlyRevenue.vue';
import RecentActivityLogs from '../../../Modules/Cart/resources/assets/js/components/RecentActivityLogs.vue';
import MonthlyRevenueOverview from '../../../Modules/Cart/resources/assets/js/components/MonthlyRevenueOverview.vue';
import RecentActivityOverview from '../../../Modules/Cart/resources/assets/js/components/RecentActivityOverview.vue';
import {
    TrendingUp,
    TrendingDown,
    DollarSign,
    ShoppingBag,
    Tag,
    AlertTriangle,
    Search,
    Filter,
    Plus,
    Edit,
    Check,
    X,
    Percent,
    Calendar,
    ArrowUpRight,
    Activity,
    FileText,
    Settings,
    Users,
    CheckCircle2,
    Trash2,
    Eye,
    Package,
    Shield,
    Key,
    User,
    ArrowRight,
    Copy,
    Heart,
    PercentCircle,
    Printer,
    LifeBuoy,
    MessageSquare,
    Truck,
    MapPin,
    ClipboardList,
    ExternalLink,
} from '@lucide/vue';
import type { DashboardInvitation, Team } from '@/types';

// Props received from Laravel backend
interface Props {
    role: 'admin' | 'customer';
    stats: {
        totalRevenue?: number;
        pendingCount?: number;
        activeCouponCount?: number;
        lowStockCount?: number;
        totalOrders?: number;
        totalSaved?: number;
        wishlistCount?: number;
    };
    products?: Array<{
        id: number;
        name: string;
        category: string;
        price: number;
        stock: number;
        status: string;
    }>;
    orders?: Array<{
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
    }>;
    coupons?: Array<{
        id: number;
        code: string;
        description?: string;
        discountType: 'flat' | 'percentage';
        discountValue: number;
        minOrderAmount: number;
        usedCount?: number;
        usageLimit?: number;
        expiresAt: string;
        status: 'active' | 'inactive';
    }>;
    recommendedProducts?: Array<{
        id: number;
        name: string;
        slug: string;
        price: number;
        image: string;
        category: string;
    }>;
    pendingInvitations?: DashboardInvitation[];
    payments?: Array<{
        id: number;
        transaction_id: number;
        order_ref: string;
        customer: string;
        amount: number;
        method: string;
        gateway: string;
        paid_on: string;
        status: string;
    }>;
    carts?: Array<{
        id: number;
        customer: string;
        items_count: number;
        total: number;
        last_active: string;
        items: Array<{
            product_name: string;
            quantity: number;
            price: number;
        }>;
    }>;
    supportTickets?: Array<{
        id: string;
        db_id: number;
        category: string;
        orderId: string;
        status: string;
        date: string;
        messages: Array<{
            id: number;
            message: string;
            sender: string;
            is_admin: boolean;
            date: string;
        }>;
    }>;
}

const props = defineProps<Props>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const isShipmentEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Shipment');
});

const isCartEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Cart');
});

const isShopEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Shop');
});

// Breadcrumbs definition for layout
defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam
                    ? `/${props.currentTeam.slug}/dashboard`
                    : '/dashboard',
            },
        ],
    }),
});

// Current team slug for Admin actions
// Falls back to the first path segment from the URL (e.g. /{team}/dashboard) when the
// currentTeam prop hasn't propagated yet, to avoid generating "/undefined/..." URLs.
const currentTeamSlug = computed(() => {
    const slug = page.props.currentTeam?.slug;
    if (slug && typeof slug === 'string' && slug !== 'undefined') {
        return slug;
    }
    // Extract team slug from current URL path: /{team}/dashboard/...
    const segments = window.location.pathname.split('/').filter(Boolean);
    // The first segment should be the team slug (not 'dashboard' itself)
    const first = segments[0] || null;
    return first && first !== 'dashboard' ? first : null;
});

// Toast Feedback System
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

// TABS state
const activeTab = ref<
    'overview' | 'products' | 'orders' | 'coupons' | 'payments' | 'carts'
>('overview');
const customerTab = ref<
    'home' | 'orders' | 'invoices' | 'support' | 'profile' | 'coupons'
>('home');

const isSupportEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Support');
});

// Support Desk tickets state
const localSupportTickets = ref([
    {
        id: 'TKT-8241',
        db_id: 1,
        category: 'Delivery Issue',
        orderId: 'ORD-100201',
        status: 'Open',
        date: 'Jul 08, 2026',
        messages: [
            {
                id: 1,
                message: 'The package has not arrived yet. Tracking says it is still in warehouse.',
                sender: 'Customer',
                is_admin: false,
                date: 'Jul 08, 2026 14:30'
            }
        ]
    },
    {
        id: 'TKT-3912',
        db_id: 2,
        category: 'Billing Inquiry',
        orderId: 'ORD-100202',
        status: 'Resolved',
        date: 'Jul 05, 2026',
        messages: [
            {
                id: 2,
                message: 'Double charged for the order shipping. Please review.',
                sender: 'Customer',
                is_admin: false,
                date: 'Jul 05, 2026 10:15'
            },
            {
                id: 3,
                message: 'Hello! We reviewed the transaction and refunded the duplicate charge. Let us know if you need anything else.',
                sender: 'Support Agent',
                is_admin: true,
                date: 'Jul 05, 2026 16:45'
            }
        ]
    },
]);

const activeSupportTickets = computed(() => {
    if (isSupportEnabled.value) {
        return props.supportTickets || [];
    }
    return localSupportTickets.value;
});

const supportCategory = ref('Delivery Issue');
const supportOrder = ref('');
const supportMessage = ref('');
const customerReplyText = ref<Record<number, string>>({});
const expandedTicketId = ref<number | null>(null);

const toggleTicketExpand = (dbId: number) => {
    if (expandedTicketId.value === dbId) {
        expandedTicketId.value = null;
    } else {
        expandedTicketId.value = dbId;
    }
};

const handleCreateTicket = () => {
    if (!supportMessage.value.trim()) {
        triggerToast('⚠️ Please write a message for your support ticket.');
        return;
    }

    if (isSupportEnabled.value) {
        router.post(
            route('customer.support.store'),
            {
                category: supportCategory.value,
                orderId: supportOrder.value,
                message: supportMessage.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    supportMessage.value = '';
                    triggerToast('💬 Support ticket submitted!');
                },
            }
        );
    } else {
        const newTktId = `TKT-${Math.floor(1000 + Math.random() * 9000)}`;
        const today = new Date().toLocaleDateString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
        });

        localSupportTickets.value.unshift({
            id: newTktId,
            db_id: Math.floor(10000 + Math.random() * 90000),
            category: supportCategory.value,
            orderId: supportOrder.value || 'None',
            status: 'Open',
            date: today,
            messages: [
                {
                    id: Math.floor(100000 + Math.random() * 900000),
                    message: supportMessage.value,
                    sender: 'Customer',
                    is_admin: false,
                    date: today,
                }
            ]
        });

        supportMessage.value = '';
        triggerToast(
            `💬 Support ticket ${newTktId} submitted! Our team will contact you shortly.`,
        );
    }
};

const handleReplyTicket = (dbId: number) => {
    const text = customerReplyText.value[dbId];
    if (!text || !text.trim()) return;

    if (isSupportEnabled.value) {
        router.post(
            route('customer.support.reply', { ticket: dbId }),
            { message: text },
            {
                preserveScroll: true,
                onSuccess: () => {
                    customerReplyText.value[dbId] = '';
                    triggerToast('💬 Reply posted successfully!');
                },
            }
        );
    } else {
        const ticket = localSupportTickets.value.find(t => t.db_id === dbId);
        if (ticket) {
            ticket.messages.push({
                id: Math.floor(100000 + Math.random() * 900000),
                message: text,
                sender: 'Customer',
                is_admin: false,
                date: 'Just now',
            });
            ticket.status = 'Open';
        }
        customerReplyText.value[dbId] = '';
        triggerToast('💬 Reply posted successfully!');
    }
};

// Synchronize tab and display a toast for coming soon modules
const syncParams = () => {
    if (typeof window === 'undefined') return;
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam === 'blog') {
        router.visit(route('blog.adminIndex').url);
        return;
    }
    if (
        tabParam &&
        ['overview', 'products', 'orders', 'coupons', 'payments', 'carts'].includes(
            tabParam,
        )
    ) {
        if (
            (tabParam === 'orders' ||
                tabParam === 'coupons' ||
                tabParam === 'carts') &&
            !isCartEnabled.value
        ) {
            activeTab.value = 'overview';
        } else if (tabParam === 'products' && !isShopEnabled.value) {
            activeTab.value = 'overview';
        } else {
            activeTab.value = tabParam as any;
        }
    }
    const featureParam = urlParams.get('feature');
    if (urlParams.get('tab') === 'coming_soon' && featureParam) {
        triggerToast(`${featureParam} module is coming soon!`);
    }
};

onMounted(syncParams);
watch(() => page.url, syncParams);
watch(activeTab, () => {
    searchQuery.value = '';
    filterStatus.value = 'All';
});

// Coupon creation state
const showCreateCouponModal = ref(false);
const showEditCouponModal = ref(false);
const editingCouponId = ref<number | null>(null);
const editCoupon = ref({
    code: '',
    discountType: 'percentage' as 'flat' | 'percentage',
    discountValue: 10,
    minOrderAmount: 0,
    usageLimit: 100,
    expiresAt: '',
    status: 'active' as 'active' | 'inactive',
});

// Invoice View State
const selectedInvoice = ref<any | null>(null);
const showInvoiceModal = ref(false);
const openInvoice = (order: any) => {
    selectedInvoice.value = order;
    showInvoiceModal.value = true;
};
const closeInvoice = () => {
    selectedInvoice.value = null;
    showInvoiceModal.value = false;
};
const printInvoice = () => {
    window.print();
};
const newCoupon = ref({
    code: '',
    discountType: 'percentage' as 'flat' | 'percentage',
    discountValue: 10,
    minOrderAmount: 0,
    usageLimit: 100,
    expiresAt: '',
    status: 'active' as 'active' | 'inactive',
});

interface ValidationErrors {
    code?: string;
    discountValue?: string;
    minOrderAmount?: string;
}
const formErrors = ref<ValidationErrors>({});

// ADMIN ACTIONS
const updateProductStock = (productId: number, newStock: number) => {
    if (newStock < 0) return;
    if (!currentTeamSlug.value) {
        triggerToast('⚠️ No team context found. Please refresh the page.');
        return;
    }
    router.post(
        `/${currentTeamSlug.value}/dashboard/products/${productId}/stock`,
        {
            stock: newStock,
        },
        {
            preserveScroll: true,
            onSuccess: () =>
                triggerToast('📦 Stock level updated successfully!'),
        },
    );
};

const shipOrder = (dbId: number) => {
    if (!currentTeamSlug.value) {
        triggerToast('⚠️ No team context found. Please refresh the page.');
        return;
    }
    router.post(
        `/${currentTeamSlug.value}/dashboard/orders/${dbId}/ship`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => triggerToast('🚚 Order marked as shipped!'),
        },
    );
};

const cancelOrder = (dbId: number) => {
    if (!currentTeamSlug.value) {
        triggerToast('⚠️ No team context found. Please refresh the page.');
        return;
    }
    router.post(
        `/${currentTeamSlug.value}/dashboard/orders/${dbId}/cancel`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => triggerToast('❌ Order cancelled.'),
        },
    );
};

// ── SHIPPING MODAL ─────────────────────────────────────────────────────────
const showShippingModal = ref(false);
const shippingOrder = ref<any | null>(null);
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

const openShippingModal = (order: any) => {
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
    if (!currentTeamSlug.value || !shippingOrder.value?.db_id) return;
    router.post(
        `/${currentTeamSlug.value}/dashboard/orders/${shippingOrder.value.db_id}/shipping`,
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

// Shipping status helpers
const shippingSteps = ['ordered', 'packed', 'shipped', 'delivered'];
const shippingStepIndex = (status: string | undefined) =>
    shippingSteps.indexOf(status || 'ordered');
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
        ordered:
            'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400',
        packed: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400',
        shipped:
            'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400',
        delivered:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400',
        cancelled: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400',
    })[s ?? 'ordered'] ?? 'bg-neutral-100 text-neutral-500';
// ──────────────────────────────────────────────────────────────────────────

const handleCreateCoupon = () => {
    formErrors.value = {};
    let hasError = false;

    if (!newCoupon.value.code.trim()) {
        formErrors.value.code = 'Coupon code is required.';
        hasError = true;
    }

    if (newCoupon.value.discountValue <= 0) {
        formErrors.value.discountValue = 'Value must be positive.';
        hasError = true;
    } else if (
        newCoupon.value.discountType === 'percentage' &&
        newCoupon.value.discountValue > 100
    ) {
        formErrors.value.discountValue = 'Percentage cannot exceed 100%.';
        hasError = true;
    }

    if (newCoupon.value.minOrderAmount < 0) {
        formErrors.value.minOrderAmount = 'Minimum order cannot be negative.';
        hasError = true;
    }

    if (hasError) return;

    router.post(
        `/${currentTeamSlug.value}/dashboard/coupons`,
        {
            code: newCoupon.value.code.trim().toUpperCase(),
            discountType: newCoupon.value.discountType,
            discountValue: newCoupon.value.discountValue,
            minOrderAmount: newCoupon.value.minOrderAmount,
            usageLimit: newCoupon.value.usageLimit,
            expiresAt: newCoupon.value.expiresAt || null,
            status: newCoupon.value.status,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCreateCouponModal.value = false;
                newCoupon.value = {
                    code: '',
                    discountType: 'percentage',
                    discountValue: 10,
                    minOrderAmount: 0,
                    usageLimit: 100,
                    expiresAt: '',
                    status: 'active',
                };
                triggerToast('🎉 Coupon created successfully!');
            },
        },
    );
};

const openEditCouponModal = (coupon: any) => {
    editingCouponId.value = coupon.id;
    editCoupon.value = {
        code: coupon.code,
        discountType: coupon.discountType,
        discountValue: coupon.discountValue,
        minOrderAmount: coupon.minOrderAmount,
        usageLimit: coupon.usageLimit,
        expiresAt: coupon.expiresAt === 'Never' ? '' : coupon.expiresAt,
        status: coupon.status,
    };
    formErrors.value = {};
    showEditCouponModal.value = true;
};

const handleUpdateCoupon = () => {
    formErrors.value = {};
    let hasError = false;

    if (!editCoupon.value.code.trim()) {
        formErrors.value.code = 'Coupon code is required.';
        hasError = true;
    }

    if (editCoupon.value.discountValue <= 0) {
        formErrors.value.discountValue = 'Value must be positive.';
        hasError = true;
    } else if (
        editCoupon.value.discountType === 'percentage' &&
        editCoupon.value.discountValue > 100
    ) {
        formErrors.value.discountValue = 'Percentage cannot exceed 100%.';
        hasError = true;
    }

    if (editCoupon.value.minOrderAmount < 0) {
        formErrors.value.minOrderAmount = 'Minimum order cannot be negative.';
        hasError = true;
    }

    if (hasError) return;

    router.put(
        `/${currentTeamSlug.value}/dashboard/coupons/${editingCouponId.value}`,
        {
            code: editCoupon.value.code.trim().toUpperCase(),
            discountType: editCoupon.value.discountType,
            discountValue: editCoupon.value.discountValue,
            minOrderAmount: editCoupon.value.minOrderAmount,
            usageLimit: editCoupon.value.usageLimit,
            expiresAt: editCoupon.value.expiresAt || null,
            status: editCoupon.value.status,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showEditCouponModal.value = false;
                editingCouponId.value = null;
                triggerToast('🎉 Coupon updated successfully!');
            },
        },
    );
};

const toggleCouponStatus = (couponId: number) => {
    router.post(
        `/${currentTeamSlug.value}/dashboard/coupons/${couponId}/toggle`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => triggerToast('🏷️ Coupon status updated!'),
        },
    );
};

const deleteCoupon = (couponId: number) => {
    router.delete(`/${currentTeamSlug.value}/dashboard/coupons/${couponId}`, {
        preserveScroll: true,
        onSuccess: () => triggerToast('🗑️ Coupon removed.'),
    });
};

// CUSTOMER ACTIONS
const copyCouponCode = (code: string) => {
    navigator.clipboard.writeText(code);
    triggerToast(`📋 Code "${code}" copied to clipboard!`);
};

// Filtered Lists for Admin
const filteredProducts = computed(() => {
    const list = props.products || [];
    return list.filter((p) => {
        const matchesCategory =
            filterStatus.value === 'All' || p.category === filterStatus.value;
        const matchesSearch =
            p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            p.category.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});

const filteredOrders = computed(() => {
    const list = props.orders || [];
    return list.filter((o) => {
        const matchesStatus =
            filterStatus.value === 'All' || o.status === filterStatus.value;
        const matchesSearch =
            (o.customer || '')
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            o.id.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesSearch;
    });
});

const filteredCoupons = computed(() => {
    const list = props.coupons || [];
    return list.filter((c) => {
        const matchesStatus =
            filterStatus.value === 'All' ||
            (filterStatus.value === 'Active' && c.status === 'active') ||
            (filterStatus.value === 'Inactive' && c.status === 'inactive');
        const matchesSearch = c.code
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesSearch;
    });
});
</script>

<template>
    <Head
        :title="role === 'admin' ? 'Admin Dashboard' : 'Customer Dashboard'"
    />

    <!-- 1. ADMIN DASHBOARD TEMPLATE -->
    <div
        v-if="role === 'admin'"
        class="space-y-6 overflow-x-hidden px-4 py-6 pb-12 text-neutral-800 sm:px-6 lg:px-8 dark:text-neutral-200"
    >
        <PendingInvitationsModal
            v-if="pendingInvitations && pendingInvitations.length > 0"
            :invitations="pendingInvitations"
        />

        <!-- Admin Header -->
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight">
                Admin Overview Dashboard
            </h1>
            <p class="text-xs text-neutral-500">
                Monitor store sales, manage coupons, update stock, and audit
                payment gateways.
            </p>
        </div>

        <!-- 4-Column Stat Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Revenue widget -->
            <div
                v-if="isCartEnabled"
                class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between text-neutral-400">
                    <span
                        class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                        >Paid Revenue</span
                    >
                    <DollarSign class="h-4 w-4 text-emerald-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">
                        {{ $page.props.currency_symbol ?? '$'
                        }}{{ (stats.totalRevenue ?? 0).toFixed(2) }}
                    </div>
                    <div
                        class="flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400"
                    >
                        <TrendingUp class="h-3 w-3" /> +14.2% from last week
                    </div>
                </div>
                <svg
                    class="h-8 w-full text-emerald-500"
                    viewBox="0 0 100 10"
                    preserveAspectRatio="none"
                >
                    <path
                        d="M0,8 L20,6 L40,9 L60,4 L80,7 L100,2"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    />
                </svg>
            </div>

            <!-- Orders widget -->
            <div
                v-if="isCartEnabled"
                class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between text-neutral-400">
                    <span
                        class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                        >Pending Orders</span
                    >
                    <ShoppingBag class="h-4 w-4 text-amber-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">
                        {{ stats.pendingCount ?? 0 }}
                    </div>
                    <div class="text-[10px] font-medium text-neutral-400">
                        Requires processing dispatch
                    </div>
                </div>
                <div class="flex h-8 items-end gap-1 pt-2">
                    <div
                        class="h-3 w-full rounded bg-neutral-100 dark:bg-neutral-800"
                    ></div>
                    <div
                        class="h-5 w-full rounded bg-neutral-100 dark:bg-neutral-800"
                    ></div>
                    <div
                        class="h-2 w-full rounded bg-neutral-100 dark:bg-neutral-800"
                    ></div>
                    <div class="h-7 w-full rounded bg-amber-500"></div>
                </div>
            </div>

            <!-- Coupons widget -->
            <div
                v-if="isCartEnabled"
                class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between text-neutral-400">
                    <span
                        class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                        >Active Coupons</span
                    >
                    <Tag class="h-4 w-4 text-indigo-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">
                        {{ stats.activeCouponCount ?? 0 }}
                    </div>
                    <div
                        class="flex items-center gap-1 text-[10px] text-neutral-400"
                    >
                        <CheckCircle2 class="h-3 w-3 text-emerald-500" /> Active
                        in checkout system
                    </div>
                </div>
                <div
                    class="border-t border-neutral-100 pt-2 font-mono text-[10px] text-neutral-400 dark:border-neutral-800"
                >
                    Offers discounts storewide
                </div>
            </div>

            <!-- Monthly Revenue (Cart module) -->
            <MonthlyRevenue v-if="isCartEnabled" :stats="props.stats" />

            <!-- Recent Activity Logs (Cart module) -->
            <RecentActivityLogs v-if="isCartEnabled" :activities="props.orders || []" />

            <!-- Low Stock widget -->
            <div v-if="isShopEnabled"
                class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between text-neutral-400">
                    <span
                        class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                        >Low Stock Alert</span
                    >
                    <AlertTriangle class="h-4 w-4 text-red-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">
                        {{ stats.lowStockCount ?? 0 }}
                    </div>
                    <div
                        class="flex items-center gap-1 text-[10px] font-bold text-amber-500"
                    >
                        Requires inventory replenishment
                    </div>
                </div>
                <div
                    class="h-1 w-full rounded-full bg-neutral-100 dark:bg-neutral-800"
                >
                    <div
                        class="h-1 rounded-full bg-red-500"
                        style="width: 35%"
                    ></div>
                </div>
            </div>
        </div>

        <!-- TAB Content -->
        <!-- 1. TAB: ANALYTICS OVERVIEW -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
            <div v-if="isCartEnabled" class="grid gap-6 lg:grid-cols-3">
                <MonthlyRevenueOverview v-if="isCartEnabled" :stats="props.stats" />

                <!-- Activity Log -->
                <RecentActivityOverview v-if="isCartEnabled" :orders="orders" />
            </div>

            <!-- When Cart is disabled, show Catalog Mode active placeholder -->
            <div v-else class="rounded-xl border border-neutral-200 bg-white p-8 text-center shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <Package class="mx-auto h-12 w-12 text-emerald-600 dark:text-emerald-500" />
                <h3 class="mt-4 text-base font-bold text-neutral-800 dark:text-neutral-100">Catalog Mode Active</h3>
                <p class="mt-2 text-xs text-neutral-500 max-w-md mx-auto">
                    The Cart module is currently disabled. Customers can browse products, but checkout, order placement, and discount coupons are deactivated.
                </p>
            </div>
        </div>

        <!-- 2. TAB: PRODUCTS TABLE -->
        <div v-else-if="activeTab === 'products' && isShopEnabled" class="space-y-4">
            <ProductsTable :products="products || []" />
        </div>

        <!-- Shop Module Disabled Message -->
        <div v-else-if="activeTab === 'products' && !isShopEnabled" class="rounded-xl border border-dashed border-neutral-200 p-12 text-center dark:border-neutral-800">
            <Package class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-700" />
            <h3 class="mt-4 text-sm font-bold">Shop Module Disabled</h3>
            <p class="mt-2 text-xs text-neutral-500 max-w-md mx-auto">
                The Shop module is currently disabled. Product management is not available. Enable the Shop module to manage products.
            </p>
        </div>

        <!-- 3. TAB: ORDERS TABLE -->
        <div v-else-if="activeTab === 'orders' && isCartEnabled" class="space-y-4">
            <OrdersManager
                :orders="orders"
                :currentTeamSlug="currentTeamSlug"
                :isShipmentEnabled="isShipmentEnabled"
            />
        </div>

        <!-- 4. TAB: COUPONS -->
        <div v-else-if="activeTab === 'coupons' && isCartEnabled" class="space-y-4">
            <CouponsManager
                :coupons="coupons"
                :currentTeamSlug="currentTeamSlug"
            />
        </div>

        <!-- 6. TAB: CARTS -->
        <div v-else-if="activeTab === 'carts' && isCartEnabled" class="space-y-4">
            <CartManager
                :carts="carts"
                :currentTeamSlug="currentTeamSlug"
            />
        </div>

        <!-- 5. TAB: PAYMENTS TABLE -->
        <div v-else-if="activeTab === 'payments'" class="space-y-4">
            <PaymentsTable :payments="payments" />
        </div>
    </div>

    <!-- ── SHIPPING MANAGEMENT MODAL ─────────────────────────────────────── -->
    <Teleport to="body">
        <div
            v-if="showShippingModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @click.self="closeShippingModal"
        >
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="closeShippingModal"
            />
            <div
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-700 dark:bg-neutral-900"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-neutral-100 px-6 py-4 dark:border-neutral-800"
                >
                    <div class="flex items-center gap-2">
                        <Truck class="h-4 w-4 text-emerald-600" />
                        <h2
                            class="text-sm font-bold text-neutral-800 dark:text-neutral-100"
                        >
                            Update Shipping
                        </h2>
                    </div>
                    <button
                        @click="closeShippingModal"
                        class="rounded-lg p-1 transition hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    >
                        <X class="h-4 w-4 text-neutral-500" />
                    </button>
                </div>

                <!-- Order ref -->
                <div
                    class="border-b border-neutral-100 bg-neutral-50 px-6 py-3 text-xs text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/50"
                >
                    Order
                    <span
                        class="font-mono font-bold text-neutral-700 dark:text-neutral-200"
                        >{{ shippingOrder?.id }}</span
                    >
                </div>

                <!-- Form -->
                <div class="space-y-4 px-6 py-5">
                    <!-- Status picker -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                            >Shipping Status</label
                        >
                        <div class="grid grid-cols-1 gap-2">
                            <label
                                v-for="opt in shippingStatusOptions"
                                :key="opt.value"
                                :class="[
                                    'flex cursor-pointer items-center gap-3 rounded-xl border-2 px-4 py-2.5 transition',
                                    shippingForm.shipping_status === opt.value
                                        ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30'
                                        : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700',
                                ]"
                            >
                                <input
                                    type="radio"
                                    :value="opt.value"
                                    v-model="shippingForm.shipping_status"
                                    class="sr-only"
                                />
                                <div
                                    :class="[
                                        'flex h-3 w-3 items-center justify-center rounded-full border-2',
                                        shippingForm.shipping_status ===
                                        opt.value
                                            ? 'border-emerald-600'
                                            : 'border-neutral-300',
                                    ]"
                                >
                                    <div
                                        v-if="
                                            shippingForm.shipping_status ===
                                            opt.value
                                        "
                                        class="h-1.5 w-1.5 rounded-full bg-emerald-600"
                                    />
                                </div>
                                <span
                                    :class="[
                                        'text-xs font-semibold',
                                        opt.color,
                                    ]"
                                    >{{ opt.label }}</span
                                >
                            </label>
                        </div>
                    </div>

                    <!-- Tracking fields — only shown for packed/shipped/delivered -->
                    <template
                        v-if="
                            ['packed', 'shipped', 'delivered'].includes(
                                shippingForm.shipping_status,
                            )
                        "
                    >
                        <div class="space-y-1">
                            <label
                                class="text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                                >Courier / Carrier</label
                            >
                            <input
                                v-model="shippingForm.courier"
                                type="text"
                                placeholder="e.g. FedEx, DHL, Pathao"
                                class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs outline-none focus:border-emerald-500 dark:border-neutral-700 dark:bg-neutral-800"
                            />
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                                >Tracking Number</label
                            >
                            <input
                                v-model="shippingForm.tracking_number"
                                type="text"
                                placeholder="e.g. 1Z999AA10123456784"
                                class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 font-mono text-xs outline-none focus:border-emerald-500 dark:border-neutral-700 dark:bg-neutral-800"
                            />
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                                >Tracking URL
                                <span class="font-normal text-neutral-400"
                                    >(optional)</span
                                ></label
                            >
                            <input
                                v-model="shippingForm.tracking_url"
                                type="url"
                                placeholder="https://track.courier.com/..."
                                class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs outline-none focus:border-emerald-500 dark:border-neutral-700 dark:bg-neutral-800"
                            />
                        </div>
                    </template>
                </div>

                <!-- Actions -->
                <div
                    class="flex items-center justify-end gap-2 border-t border-neutral-100 bg-neutral-50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-800/30"
                >
                    <button
                        @click="closeShippingModal"
                        class="h-9 rounded-lg border border-neutral-200 px-4 text-xs font-semibold text-neutral-600 transition hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitShipping"
                        class="flex h-9 items-center gap-1.5 rounded-lg bg-emerald-600 px-5 text-xs font-bold text-white transition hover:bg-emerald-700"
                    >
                        <Check class="h-3.5 w-3.5" /> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
    <!-- ──────────────────────────────────────────────────────────────────── -->

    <div
        v-if="role === 'customer'"
        class="space-y-6 overflow-x-hidden px-4 py-6 pb-12 text-neutral-800 sm:px-6 lg:px-8 dark:text-neutral-200"
    >
        <!-- Customer Stats -->
        <div class="grid grid-cols-3 gap-4">
            <div
                class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
            >
                <span
                    class="text-[10px] font-bold tracking-wider text-neutral-400 uppercase"
                    >Orders Placed</span
                >
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="font-mono text-2xl leading-none font-black">{{
                        stats.totalOrders ?? 0
                    }}</span>
                    <span class="text-[10px] text-neutral-400">total</span>
                </div>
                <div
                    class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"
                >
                    <Package class="h-3 w-3" /> Fully tracked
                </div>
            </div>

            <div
                class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
            >
                <span
                    class="text-[10px] font-bold tracking-wider text-neutral-400 uppercase"
                    >Saved with Coupons</span
                >
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="font-mono text-2xl leading-none font-black"
                        >{{ $page.props.currency_symbol ?? '$'
                        }}{{ (stats.totalSaved ?? 0).toFixed(2) }}</span
                    >
                    <span class="text-[10px] text-neutral-400">saved</span>
                </div>
                <div
                    class="flex items-center gap-1 text-[9px] font-bold text-indigo-600 dark:text-indigo-400"
                >
                    <PercentCircle class="h-3 w-3" /> Best offers utilized
                </div>
            </div>

            <div
                class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
            >
                <span
                    class="text-[10px] font-bold tracking-wider text-neutral-400 uppercase"
                    >Wishlist items</span
                >
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="font-mono text-2xl leading-none font-black">{{
                        stats.wishlistCount ?? 0
                    }}</span>
                    <span class="text-[10px] text-neutral-400">items</span>
                </div>
                <div
                    class="flex items-center gap-1 text-[9px] font-bold text-rose-600 dark:text-rose-400"
                >
                    <Heart class="h-3 w-3" /> Saved for later
                </div>
            </div>
        </div>

        <!-- Main section layout -->
        <div class="space-y-6">
            <!-- Customer Tabs Navigation -->
            <div
                class="flex gap-6 overflow-x-auto border-b border-neutral-200 pb-px dark:border-neutral-800"
            >
                <button
                    @click="customerTab = 'home'"
                    :class="
                        customerTab === 'home'
                            ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400'
                            : 'border-transparent text-neutral-400 hover:text-neutral-700'
                    "
                    class="border-b-2 pb-3 text-xs font-bold whitespace-nowrap transition"
                >
                    Home
                </button>
                <button
                    v-if="isCartEnabled"
                    @click="customerTab = 'orders'"
                    :class="
                        customerTab === 'orders'
                            ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400'
                            : 'border-transparent text-neutral-400 hover:text-neutral-700'
                    "
                    class="border-b-2 pb-3 text-xs font-bold whitespace-nowrap transition"
                >
                    Order List
                </button>
                <button
                    v-if="isCartEnabled"
                    @click="customerTab = 'invoices'"
                    :class="
                        customerTab === 'invoices'
                            ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400'
                            : 'border-transparent text-neutral-400 hover:text-neutral-700'
                    "
                    class="border-b-2 pb-3 text-xs font-bold whitespace-nowrap transition"
                >
                    Customer Invoice
                </button>
                <button
                    @click="customerTab = 'support'"
                    :class="
                        customerTab === 'support'
                            ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400'
                            : 'border-transparent text-neutral-400 hover:text-neutral-700'
                    "
                    class="border-b-2 pb-3 text-xs font-bold whitespace-nowrap transition"
                >
                    Shop Support
                </button>
                <button
                    @click="customerTab = 'profile'"
                    :class="
                        customerTab === 'profile'
                            ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400'
                            : 'border-transparent text-neutral-400 hover:text-neutral-700'
                    "
                    class="border-b-2 pb-3 text-xs font-bold whitespace-nowrap transition"
                >
                    Profile
                </button>
                <button
                    v-if="isCartEnabled"
                    @click="customerTab = 'coupons'"
                    :class="
                        customerTab === 'coupons'
                            ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400'
                            : 'border-transparent text-neutral-400 hover:text-neutral-700'
                    "
                    class="border-b-2 pb-3 text-xs font-bold whitespace-nowrap transition"
                >
                    Available Coupons
                </button>
            </div>

            <!-- Tab 1: Home Overview -->
            <div v-if="customerTab === 'home'" class="space-y-6">
                <!-- Welcome Hero Banner -->
                <div
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white shadow-md"
                >
                    <div
                        class="pointer-events-none absolute right-0 bottom-0 translate-x-1/4 translate-y-1/4 transform opacity-10"
                    >
                        <ShoppingBag class="h-64 w-64" />
                    </div>
                    <div class="relative z-10 space-y-2">
                        <span
                            class="bg-emerald-550/30 rounded-full px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase"
                            >Customer Portal</span
                        >
                        <h2 class="text-2xl font-black">
                            Hello,
                            {{ authUser?.first_name || 'Valued Customer' }}!
                        </h2>
                        <p class="max-w-md text-xs text-emerald-100">
                            Welcome to your personal dashboard. Here you can
                            track your shipments, view detailed invoices, manage
                            your profile, and receive dedicated shop support.
                        </p>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div class="grid gap-4 sm:grid-cols-3">
                    <button
                        @click="customerTab = 'orders'"
                        class="group space-y-2 rounded-xl border border-neutral-200 bg-white p-4 text-left shadow-xs transition hover:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition duration-300 group-hover:scale-110 dark:bg-emerald-950 dark:text-emerald-400"
                        >
                            <Package class="h-4 w-4" />
                        </div>
                        <h4 class="text-xs font-bold">Track Your Orders</h4>
                        <p class="text-[10px] text-neutral-400">
                            View shipping statuses and historical items
                            purchased.
                        </p>
                    </button>

                    <button
                        @click="customerTab = 'support'"
                        class="group space-y-2 rounded-xl border border-neutral-200 bg-white p-4 text-left shadow-xs transition hover:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600 transition duration-300 group-hover:scale-110 dark:bg-teal-950 dark:text-teal-400"
                        >
                            <LifeBuoy class="h-4 w-4" />
                        </div>
                        <h4 class="text-xs font-bold">Shop Support</h4>
                        <p class="text-[10px] text-neutral-400">
                            Submit an inquiry or get assist on payment disputes.
                        </p>
                    </button>

                    <button
                        @click="customerTab = 'profile'"
                        class="group space-y-2 rounded-xl border border-neutral-200 bg-white p-4 text-left shadow-xs transition hover:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition duration-300 group-hover:scale-110 dark:bg-indigo-950 dark:text-indigo-400"
                        >
                            <User class="h-4 w-4" />
                        </div>
                        <h4 class="text-xs font-bold">Manage Profile</h4>
                        <p class="text-[10px] text-neutral-400">
                            Update security settings or switch associated
                            contacts.
                        </p>
                    </button>
                </div>

                <!-- Recommended For You Section -->
                <div
                    class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <h3
                        class="flex items-center gap-1.5 text-xs font-black tracking-wider text-neutral-400 uppercase"
                    >
                        <TrendingUp class="h-4 w-4 text-emerald-500" />
                        Recommended For You
                    </h3>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div
                            v-for="prod in recommendedProducts"
                            :key="prod.id"
                            class="group flex flex-col justify-between rounded-xl border border-neutral-100 p-3 transition hover:shadow-md dark:border-neutral-800"
                        >
                            <div class="space-y-2">
                                <div
                                    class="relative aspect-square overflow-hidden rounded-lg"
                                >
                                    <img
                                        :src="prod.image"
                                        alt=""
                                        class="h-full w-full bg-neutral-100 object-cover transition duration-500 group-hover:scale-105"
                                    />
                                </div>
                                <span
                                    class="text-[9px] font-bold tracking-tight text-neutral-400 uppercase"
                                    >{{ prod.category }}</span
                                >
                                <h4
                                    class="truncate text-xs leading-tight font-bold transition group-hover:text-emerald-600 dark:group-hover:text-emerald-400"
                                >
                                    {{ prod.name }}
                                </h4>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="font-mono text-xs font-bold"
                                    >{{ $page.props.currency_symbol ?? '$'
                                    }}{{ prod.price.toFixed(2) }}</span
                                >
                                <Link
                                    :href="`/shop?category=${prod.category}`"
                                    class="flex h-7 items-center gap-1 rounded-lg bg-neutral-50 px-3 text-[10px] font-bold text-neutral-600 transition hover:bg-emerald-50 hover:text-emerald-600 dark:bg-neutral-800 dark:hover:bg-emerald-950"
                                >
                                    <span>Shop</span>
                                    <ArrowRight class="h-3 w-3" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Order List -->
            <div v-else-if="customerTab === 'orders' && isCartEnabled" class="space-y-4">
                <div
                    v-if="!orders || orders.length === 0"
                    class="rounded-xl border-2 border-dashed border-neutral-200 p-8 text-center dark:border-neutral-800"
                >
                    <ShoppingBag class="mx-auto h-8 w-8 text-neutral-300" />
                    <h3 class="mt-2 text-sm font-bold">No orders placed yet</h3>
                    <p class="mt-1 text-xs text-neutral-400">
                        Once you complete a checkout, your order will show up
                        here.
                    </p>
                    <Link
                        href="/shop"
                        class="mt-4 inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Explore Catalog
                    </Link>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <!-- Order header row -->
                        <div
                            class="mb-4 flex flex-wrap items-start justify-between gap-3"
                        >
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-mono text-sm font-bold text-neutral-800 dark:text-neutral-100"
                                        >{{ order.id }}</span
                                    >
                                    <span
                                        class="rounded bg-neutral-100 px-2 py-0.5 text-[9px] font-bold text-neutral-500 uppercase dark:bg-neutral-800"
                                        >{{ order.gateway }}</span
                                    >
                                </div>
                                <div class="text-[10px] text-neutral-400">
                                    {{ order.invoice_no || '—' }} ·
                                    {{ order.date }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-base font-bold"
                                    >{{ $page.props.currency_symbol ?? '$'
                                    }}{{ order.total.toFixed(2) }}</span
                                >
                                <span
                                    v-if="isShipmentEnabled"
                                    :class="[
                                        'inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold',
                                        shippingStatusColor(
                                            order.shipping_status,
                                        ),
                                    ]"
                                >
                                    {{
                                        shippingStatusLabel(
                                            order.shipping_status,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Cancelled state -->
                        <div
                            v-if="order.shipping_status === 'cancelled'"
                            class="flex items-center gap-2 rounded-lg bg-red-50 px-4 py-3 text-xs text-red-600 dark:bg-red-950/30 dark:text-red-400"
                        >
                            <X class="h-4 w-4 shrink-0" />
                            <span class="font-semibold"
                                >This order has been cancelled.</span
                            >
                        </div>

                        <!-- Shipping Progress Stepper -->
                        <div v-else-if="isShipmentEnabled" class="space-y-4">
                            <div class="flex items-center gap-0">
                                <template
                                    v-for="(step, idx) in shippingSteps"
                                    :key="step"
                                >
                                    <!-- Step circle -->
                                    <div
                                        class="relative z-10 flex flex-col items-center gap-1"
                                    >
                                        <div
                                            :class="[
                                                'flex h-8 w-8 items-center justify-center rounded-full border-2 transition-all duration-300',
                                                shippingStepIndex(
                                                    order.shipping_status,
                                                ) >= idx
                                                    ? 'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-500/30'
                                                    : 'border-neutral-200 bg-white text-neutral-300 dark:border-neutral-700 dark:bg-neutral-900',
                                            ]"
                                        >
                                            <Check
                                                v-if="
                                                    shippingStepIndex(
                                                        order.shipping_status,
                                                    ) > idx
                                                "
                                                class="h-4 w-4"
                                            />
                                            <span
                                                v-else
                                                class="text-[10px] font-bold"
                                                >{{ idx + 1 }}</span
                                            >
                                        </div>
                                        <span
                                            :class="[
                                                'text-[9px] font-semibold tracking-tight whitespace-nowrap',
                                                shippingStepIndex(
                                                    order.shipping_status,
                                                ) >= idx
                                                    ? 'text-emerald-600 dark:text-emerald-400'
                                                    : 'text-neutral-400',
                                            ]"
                                            >{{
                                                shippingStatusLabel(step)
                                            }}</span
                                        >
                                    </div>
                                    <!-- Connector line -->
                                    <div
                                        v-if="idx < shippingSteps.length - 1"
                                        :class="[
                                            'mx-1 mb-4 h-0.5 flex-1 rounded transition-all duration-300',
                                            shippingStepIndex(
                                                order.shipping_status,
                                            ) > idx
                                                ? 'bg-emerald-500'
                                                : 'bg-neutral-200 dark:bg-neutral-700',
                                        ]"
                                    />
                                </template>
                            </div>

                            <!-- Tracking info -->
                            <div
                                v-if="isShipmentEnabled && (order.tracking_number || order.courier)"
                                class="space-y-2 rounded-lg border border-neutral-100 bg-neutral-50 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-800/50"
                            >
                                <div class="flex items-center gap-2 text-xs">
                                    <Truck
                                        class="h-3.5 w-3.5 shrink-0 text-emerald-600"
                                    />
                                    <span
                                        class="font-semibold text-neutral-700 dark:text-neutral-300"
                                        >Shipment Details</span
                                    >
                                </div>
                                <div
                                    class="grid grid-cols-2 gap-x-6 gap-y-1 text-[11px]"
                                >
                                    <div v-if="order.courier">
                                        <span class="text-neutral-400"
                                            >Courier</span
                                        >
                                        <p
                                            class="font-semibold text-neutral-700 dark:text-neutral-200"
                                        >
                                            {{ order.courier }}
                                        </p>
                                    </div>
                                    <div v-if="order.tracking_number">
                                        <span class="text-neutral-400"
                                            >Tracking No.</span
                                        >
                                        <p
                                            class="font-mono font-bold text-neutral-700 dark:text-neutral-200"
                                        >
                                            {{ order.tracking_number }}
                                        </p>
                                    </div>
                                    <div v-if="order.shipped_at">
                                        <span class="text-neutral-400"
                                            >Shipped On</span
                                        >
                                        <p
                                            class="font-semibold text-neutral-700 dark:text-neutral-200"
                                        >
                                            {{ order.shipped_at }}
                                        </p>
                                    </div>
                                    <div v-if="order.delivered_at">
                                        <span class="text-neutral-400"
                                            >Delivered On</span
                                        >
                                        <p
                                            class="font-semibold text-emerald-600"
                                        >
                                            {{ order.delivered_at }}
                                        </p>
                                    </div>
                                </div>
                                <a
                                    v-if="order.tracking_url"
                                    :href="order.tracking_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="mt-1 inline-flex h-7 items-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-[10px] font-bold text-white transition hover:bg-emerald-700"
                                >
                                    <ExternalLink class="h-3 w-3" /> Track
                                    Package
                                </a>
                            </div>
                        </div>

                        <!-- Footer actions -->
                        <div
                            class="mt-4 flex items-center justify-end border-t border-neutral-100 pt-3 dark:border-neutral-800"
                        >
                            <button
                                @click="openInvoice(order)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-neutral-50 px-2.5 py-1 text-[10px] font-bold text-neutral-600 transition hover:bg-emerald-50 hover:text-emerald-600 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-emerald-950/40"
                            >
                                <FileText class="h-3.5 w-3.5" /> View Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Customer Invoice -->
            <div v-else-if="customerTab === 'invoices' && isCartEnabled" class="space-y-4">
                <div
                    v-if="!orders || orders.length === 0"
                    class="rounded-xl border-2 border-dashed border-neutral-200 p-8 text-center dark:border-neutral-800"
                >
                    <FileText class="mx-auto h-8 w-8 text-neutral-300" />
                    <h3 class="mt-2 text-sm font-bold">
                        No invoices generated
                    </h3>
                    <p class="mt-1 text-xs text-neutral-400">
                        Once you complete a purchase, your billing invoices will
                        list here.
                    </p>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="flex flex-col justify-between space-y-4 rounded-xl border border-neutral-200 bg-white p-5 shadow-xs transition duration-300 hover:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <span
                                    class="rounded bg-emerald-50 px-2 py-0.5 text-[9px] font-bold text-emerald-600 uppercase dark:bg-emerald-950/40 dark:text-emerald-400"
                                >
                                    {{ order.invoice_no || 'DRAFT' }}
                                </span>
                                <h4 class="mt-1 text-xs font-bold">
                                    Order Ref: {{ order.id }}
                                </h4>
                                <span class="text-[10px] text-neutral-400">{{
                                    order.date
                                }}</span>
                            </div>
                            <span
                                class="font-mono text-sm font-black text-neutral-900 dark:text-white"
                            >
                                {{ $page.props.currency_symbol ?? '$'
                                }}{{ order.total.toFixed(2) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-neutral-100 pt-3 dark:border-neutral-800/80"
                        >
                            <div
                                class="flex items-center gap-1.5 text-[10px] text-neutral-500"
                            >
                                <CreditCard class="h-3.5 w-3.5" />
                                <span class="uppercase"
                                    >Via {{ order.gateway }}</span
                                >
                            </div>
                            <button
                                @click="openInvoice(order)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-neutral-50 px-2.5 py-1 text-[10px] font-bold text-neutral-600 transition hover:bg-emerald-50 hover:text-emerald-600 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-emerald-950/40"
                            >
                                <FileText class="h-3.5 w-3.5" /> View Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Tab 4: Shop Support -->
            <div
                v-else-if="customerTab === 'support'"
                class="grid gap-6 md:grid-cols-3"
            >
                <!-- Ticket Submission Form -->
                <div
                    class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5 shadow-xs md:col-span-1 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <h3
                        class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-neutral-400 uppercase"
                    >
                        <LifeBuoy class="h-4 w-4 text-emerald-500" /> Support
                        Desk
                    </h3>

                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-bold text-neutral-500 uppercase"
                                >Related Order</label
                            >
                            <select
                                v-model="supportOrder"
                                class="dark:bg-neutral-850 w-full rounded-lg border border-neutral-200 p-2 text-xs dark:border-neutral-700"
                            >
                                <option value="">None / General Inquiry</option>
                                <option
                                    v-for="order in orders"
                                    :key="order.id"
                                    :value="order.id"
                                >
                                    {{ order.id }} ({{
                                        order.invoice_no || 'No Invoice'
                                    }})
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-bold text-neutral-500 uppercase"
                                >Category</label
                            >
                            <select
                                v-model="supportCategory"
                                class="dark:bg-neutral-850 w-full rounded-lg border border-neutral-200 p-2 text-xs dark:border-neutral-700"
                            >
                                <option value="Delivery Issue">
                                    Delivery Issue
                                </option>
                                <option value="Refund Request">
                                    Refund Request
                                </option>
                                <option value="Product Question">
                                    Product Question
                                </option>
                                <option value="Billing Inquiry">
                                    Billing Inquiry
                                </option>
                                <option value="Other">Other Inquiry</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-bold text-neutral-500 uppercase"
                                >Describe Your Issue</label
                            >
                            <textarea
                                v-model="supportMessage"
                                rows="4"
                                placeholder="Provide order details, transaction date, or details about your question..."
                                class="dark:bg-neutral-850 w-full rounded-lg border border-neutral-200 p-2 text-xs dark:border-neutral-700"
                            ></textarea>
                        </div>

                        <button
                            @click="handleCreateTicket"
                            class="flex h-9 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 text-xs font-bold text-white transition hover:bg-emerald-700"
                        >
                            <MessageSquare class="h-4 w-4" />
                            <span>Submit Ticket</span>
                        </button>
                    </div>
                </div>

                <!-- Ticket list with conversation view -->
                <div class="space-y-4 md:col-span-2">
                    <h3
                        class="text-xs font-bold tracking-wider text-neutral-400 uppercase"
                    >
                        Your Support Inquiries
                    </h3>

                    <div class="space-y-3">
                        <div
                            v-for="ticket in activeSupportTickets"
                            :key="ticket.id"
                            class="space-y-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <div class="flex items-center justify-between cursor-pointer" @click="toggleTicketExpand(ticket.db_id)">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-mono text-xs font-bold text-neutral-700 dark:text-neutral-355"
                                    >
                                        {{ ticket.id }}
                                    </span>
                                    <span
                                        class="rounded bg-neutral-100 px-2 py-0.5 text-[9px] font-bold text-neutral-600 uppercase dark:bg-neutral-850 dark:text-neutral-400"
                                    >
                                        {{ ticket.category }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="
                                            ticket.status === 'Open'
                                                ? 'bg-red-50 text-red-650 dark:bg-red-955/40 dark:text-red-400'
                                                : ticket.status === 'In Progress'
                                                ? 'bg-amber-50 text-amber-650 dark:bg-amber-955/40 dark:text-amber-400'
                                                : 'bg-green-50 text-green-650 dark:bg-green-955/40 dark:text-green-400'
                                        "
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border border-transparent"
                                    >
                                        {{ ticket.status }}
                                    </span>
                                    <span class="text-xs text-neutral-400 dark:text-neutral-500">
                                        {{ expandedTicketId === ticket.db_id ? '▲ Hide' : '▼ View Thread' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Initial message preview or full discussion thread -->
                            <div v-if="expandedTicketId !== ticket.db_id">
                                <p class="text-xs text-neutral-600 dark:text-neutral-400 line-clamp-1">
                                    {{ ticket.messages[0]?.message || 'No messages.' }}
                                </p>
                            </div>

                            <!-- Expanded discussion thread -->
                            <div v-else class="space-y-4 pt-3 border-t border-neutral-100 dark:border-neutral-800/80">
                                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                                    <div 
                                        v-for="msg in ticket.messages" 
                                        :key="msg.id"
                                        :class="[
                                            'p-3 rounded-lg border text-xs max-w-[90%]',
                                            msg.is_admin 
                                                ? 'bg-emerald-50 text-emerald-900 border-emerald-100 ml-auto dark:bg-emerald-950/20 dark:text-emerald-300 dark:border-emerald-900/30' 
                                                : 'bg-neutral-50 text-neutral-800 border-neutral-150 mr-auto dark:bg-neutral-850 dark:text-neutral-200 dark:border-neutral-800'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between text-[9px] text-neutral-400 dark:text-neutral-500 mb-1">
                                            <span class="font-bold">{{ msg.sender }}</span>
                                            <span class="font-mono">{{ msg.date }}</span>
                                        </div>
                                        <p class="whitespace-pre-wrap">{{ msg.message }}</p>
                                    </div>
                                </div>

                                <!-- Post reply composer -->
                                <div v-if="ticket.status !== 'Closed'" class="flex gap-2 items-end pt-2 border-t border-neutral-100 dark:border-neutral-800/80">
                                    <input 
                                        type="text" 
                                        v-model="customerReplyText[ticket.db_id]"
                                        placeholder="Type your reply..."
                                        class="flex-1 rounded-lg border border-neutral-200 p-2 text-xs bg-white dark:bg-neutral-955 dark:border-neutral-800 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                        @keydown.enter="handleReplyTicket(ticket.db_id)"
                                    />
                                    <button 
                                        @click="handleReplyTicket(ticket.db_id)"
                                        class="px-3 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition"
                                    >
                                        Send
                                    </button>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between border-t border-neutral-50 pt-2 text-[10px] text-neutral-450 dark:border-neutral-800/60"
                            >
                                <span
                                    >Linked Order:
                                    <strong class="font-mono">{{
                                        ticket.orderId
                                    }}</strong></span
                                >
                                <span>Submitted: {{ ticket.date }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Profile Settings -->
            <div
                v-else-if="customerTab === 'profile'"
                class="grid gap-6 md:grid-cols-3"
            >
                <!-- Profile Card -->
                <div
                    class="space-y-5 rounded-xl border border-neutral-200 bg-white p-5 text-center shadow-xs md:col-span-1 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400"
                    >
                        {{ (authUser?.first_name || 'U').charAt(0)
                        }}{{ (authUser?.last_name || '').charAt(0) }}
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-sm font-bold">
                            {{ authUser?.first_name }} {{ authUser?.last_name }}
                        </h3>
                        <span
                            class="inline-block rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 uppercase dark:bg-emerald-950 dark:text-emerald-400"
                        >
                            {{ authUser?.user_type }}
                        </span>
                    </div>

                    <div
                        class="space-y-3 border-t border-neutral-100 pt-4 text-left text-[11px] dark:border-neutral-800/80"
                    >
                        <div class="flex justify-between">
                            <span class="text-neutral-400">Username</span>
                            <span class="font-semibold">{{
                                authUser?.username || '-'
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-400">Email Address</span>
                            <span class="font-semibold">{{
                                authUser?.email || '-'
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Security Shortcuts / Link cards -->
                <div class="space-y-4 md:col-span-2">
                    <h3
                        class="text-xs font-bold tracking-wider text-neutral-400 uppercase"
                    >
                        Account Security & Options
                    </h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <Link
                            href="/settings/profile"
                            class="flex flex-col justify-between space-y-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-xs transition hover:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400"
                            >
                                <User class="h-4 w-4" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold">
                                    Update Profile Details
                                </h4>
                                <p class="mt-1 text-[10px] text-neutral-400">
                                    Change your display name, username, or email
                                    details directly.
                                </p>
                            </div>
                        </Link>

                        <Link
                            href="/settings/password"
                            class="flex flex-col justify-between space-y-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-xs transition hover:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                            >
                                <Key class="h-4 w-4" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold">
                                    Change Password
                                </h4>
                                <p class="mt-1 text-[10px] text-neutral-400">
                                    Keep your portal access secure by rotating
                                    passwords.
                                </p>
                            </div>
                        </Link>

                        <Link
                            href="/settings/security"
                            class="flex flex-col justify-between space-y-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-xs transition hover:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400"
                            >
                                <Shield class="h-4 w-4" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold">
                                    2FA Authentication
                                </h4>
                                <p class="mt-1 text-[10px] text-neutral-400">
                                    Setup multi-factor credentials to maximize
                                    account safety.
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Tab 6: Available Coupons -->
            <div
                v-else-if="customerTab === 'coupons' && isCartEnabled"
                class="grid gap-4 sm:grid-cols-2"
            >
                <div
                    v-for="coupon in coupons"
                    :key="coupon.id"
                    class="relative flex flex-col justify-between gap-4 overflow-hidden rounded-xl border-2 border-dashed border-emerald-500/30 bg-emerald-50/20 p-5 dark:bg-emerald-950/10"
                >
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span
                                class="font-mono text-sm font-black tracking-wider text-emerald-600 dark:text-emerald-400"
                            >
                                {{ coupon.code }}
                            </span>
                            <button
                                @click="copyCouponCode(coupon.code)"
                                class="flex h-7 w-7 items-center justify-center rounded-md border bg-white transition hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900"
                            >
                                <Copy class="h-3.5 w-3.5 text-neutral-500" />
                            </button>
                        </div>
                        <p
                            class="text-xs text-neutral-600 dark:text-neutral-400"
                        >
                            {{
                                coupon.description ||
                                'Enjoy flat discount storewide'
                            }}
                        </p>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-dashed border-emerald-500/20 pt-2 text-[10px] text-neutral-500"
                    >
                        <span
                            >Min Order:
                            <strong
                                >{{ $page.props.currency_symbol ?? '$'
                                }}{{ coupon.minOrderAmount.toFixed(2) }}</strong
                            ></span
                        >
                        <span
                            class="rounded-full bg-emerald-600 px-2 py-0.5 font-bold text-white"
                        >
                            {{
                                coupon.discountType === 'percentage'
                                    ? `${coupon.discountValue}% OFF`
                                    : `${$page.props.currency_symbol ?? '$'}${coupon.discountValue} OFF`
                            }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="!coupons || coupons.length === 0"
                    class="col-span-2 py-8 text-center text-xs text-neutral-400"
                >
                    No active promotional coupons at this moment. Check back
                    soon!
                </div>
            </div>
        </div>
    </div>

    <!-- DETAILED INVOICE MODAL -->
    <div
        v-if="showInvoiceModal && selectedInvoice"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 p-4 backdrop-blur-xs print:relative print:z-0 print:bg-white print:p-0"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-2xl flex-col gap-6 overflow-y-auto rounded-2xl border border-neutral-100 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 print:max-h-none print:overflow-visible print:border-none print:p-0 print:shadow-none"
        >
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between border-b border-neutral-100 pb-4 dark:border-neutral-800 print:border-neutral-200"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 font-black text-white"
                    >
                        M
                    </div>
                    <div>
                        <h2
                            class="text-sm font-extrabold tracking-tight text-neutral-900 dark:text-white print:text-neutral-900"
                        >
                            StoreMint Inc.
                        </h2>
                        <p class="text-[10px] text-neutral-400">
                            Premium E-commerce Experience
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 print:hidden">
                    <button
                        @click="printInvoice"
                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 text-xs font-semibold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                    >
                        <Printer class="h-3.5 w-3.5" />
                        <span>Print Invoice</span>
                    </button>
                    <button
                        @click="closeInvoice"
                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-neutral-200 transition hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-800"
                    >
                        <X class="h-4 w-4 animate-in" />
                    </button>
                </div>
            </div>

            <!-- Invoice Identity -->
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span
                        class="text-[10px] font-bold tracking-wider text-neutral-400 uppercase"
                        >Invoice Information</span
                    >
                    <h3
                        class="mt-1 text-sm font-black text-emerald-600 dark:text-emerald-400"
                    >
                        {{ selectedInvoice.invoice_no || 'N/A' }}
                    </h3>
                    <p class="mt-0.5 text-[10px] text-neutral-500">
                        Order ID: {{ selectedInvoice.id }}
                    </p>
                    <p class="text-[10px] text-neutral-500">
                        Date: {{ selectedInvoice.date }}
                    </p>
                </div>
                <div class="text-right">
                    <span
                        class="text-[10px] font-bold tracking-wider text-neutral-400 uppercase"
                        >Payment Status</span
                    >
                    <div class="mt-1">
                        <span
                            class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black tracking-wider uppercase"
                            :class="{
                                'bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-400':
                                    selectedInvoice.status === 'Paid',
                                'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400':
                                    selectedInvoice.status === 'Pending',
                                'bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-400':
                                    selectedInvoice.status === 'Failed',
                                'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400':
                                    selectedInvoice.status === 'Cancelled',
                            }"
                        >
                            {{ selectedInvoice.status }}
                        </span>
                    </div>
                    <p class="mt-1.5 text-[10px] text-neutral-500">
                        Payment Status:
                        <span
                            class="print:text-neutral-850 font-bold text-neutral-800 uppercase dark:text-neutral-200"
                            >{{ selectedInvoice.payment_status }}</span
                        >
                    </p>
                    <p class="text-[10px] text-neutral-500">
                        Gateway:
                        <span
                            class="print:text-neutral-850 font-bold text-neutral-800 uppercase dark:text-neutral-200"
                            >{{ selectedInvoice.gateway }}</span
                        >
                    </p>
                </div>
            </div>

            <!-- Billing & Shipping Details -->
            <div
                class="grid grid-cols-2 gap-6 border-t border-b border-neutral-100 py-4 text-xs dark:border-neutral-800"
            >
                <div>
                    <h4
                        class="mb-1.5 text-[10px] font-bold tracking-wider text-neutral-400 uppercase"
                    >
                        Billed To
                    </h4>
                    <p
                        class="text-neutral-850 font-bold dark:text-neutral-100 print:text-neutral-900"
                    >
                        {{ page.props.auth.user.name }}
                    </p>
                    <p class="text-neutral-500">
                        {{ page.props.auth.user.email }}
                    </p>
                </div>
                <div>
                    <h4
                        class="mb-1.5 text-[10px] font-bold tracking-wider text-neutral-400 uppercase"
                    >
                        Shipping Address
                    </h4>
                    <p
                        class="dark:text-neutral-350 font-sans leading-relaxed whitespace-pre-line text-neutral-600 print:text-neutral-800"
                    >
                        {{
                            selectedInvoice.shipping_address ||
                            'No shipping address specified.'
                        }}
                    </p>
                </div>
            </div>

            <!-- Summary calculations -->
            <div class="space-y-3">
                <h4
                    class="text-[10px] font-bold tracking-wider text-neutral-400 uppercase"
                >
                    Financial Summary
                </h4>

                <div
                    class="space-y-2 rounded-xl bg-neutral-50 p-4 text-xs dark:bg-neutral-800/40 print:bg-neutral-50/50"
                >
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Subtotal</span>
                        <span class="font-mono font-semibold"
                            >{{ $page.props.currency_symbol ?? '$'
                            }}{{ selectedInvoice.subtotal.toFixed(2) }}</span
                        >
                    </div>
                    <div
                        v-if="selectedInvoice.discount > 0"
                        class="flex justify-between text-emerald-600 dark:text-emerald-400"
                    >
                        <span>Coupon Savings</span>
                        <span class="font-mono font-bold"
                            >-{{ $page.props.currency_symbol ?? '$'
                            }}{{ selectedInvoice.discount.toFixed(2) }}</span
                        >
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500"
                            >Shipping & Handling</span
                        >
                        <span class="font-mono font-semibold"
                            >{{ $page.props.currency_symbol ?? '$'
                            }}{{ selectedInvoice.shipping.toFixed(2) }}</span
                        >
                    </div>
                    <div
                        v-if="selectedInvoice.tax > 0"
                        class="flex justify-between"
                    >
                        <span class="text-neutral-500">Estimated Taxes</span>
                        <span class="font-mono font-semibold"
                            >{{ $page.props.currency_symbol ?? '$'
                            }}{{ selectedInvoice.tax.toFixed(2) }}</span
                        >
                    </div>
                    <div
                        class="flex items-baseline justify-between border-t border-neutral-200 pt-2 dark:border-neutral-700"
                    >
                        <span
                            class="font-bold text-neutral-900 dark:text-white print:text-neutral-900"
                            >Grand Total</span
                        >
                        <span
                            class="font-mono text-base font-black text-emerald-600 dark:text-emerald-400"
                        >
                            {{ $page.props.currency_symbol ?? '$'
                            }}{{ selectedInvoice.total.toFixed(2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer terms -->
            <div class="mt-2 text-center text-[10px] text-neutral-400">
                <p>
                    Thank you for shopping at StoreMint! If you have any
                    inquiries regarding this transaction, contact support.
                </p>
            </div>
        </div>
    </div>

    <!-- GLOBAL TOAST FEEDBACK ALERT -->
    <div
        v-if="toastMessage"
        class="fixed right-6 bottom-6 z-50 flex items-center gap-2 rounded-xl bg-neutral-900 px-4 py-3 text-xs font-bold text-white shadow-xl dark:bg-neutral-100 dark:text-neutral-900"
    >
        <CheckCircle2 class="h-4 w-4 text-emerald-500" />
        <span>{{ toastMessage }}</span>
    </div>
</template>
