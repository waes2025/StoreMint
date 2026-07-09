<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PendingInvitationsModal from '@/components/PendingInvitationsModal.vue';
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
    Printer
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
}

const props = defineProps<Props>();

const page = usePage();

// Breadcrumbs definition for layout
defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam ? `/${props.currentTeam.slug}/dashboard` : '/dashboard',
            },
        ],
    }),
});

// Current team slug for Admin actions
const currentTeamSlug = computed(() => page.props.currentTeam?.slug);

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
const activeTab = ref<'overview' | 'products' | 'orders' | 'coupons'>('overview');
const customerTab = ref<'orders' | 'coupons' | 'settings'>('orders');

// Synchronize tab and display a toast for coming soon modules
const syncParams = () => {
    if (typeof window === 'undefined') return;
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam && ['overview', 'products', 'orders', 'coupons'].includes(tabParam)) {
        activeTab.value = tabParam as any;
    }
    const featureParam = urlParams.get('feature');
    if (urlParams.get('tab') === 'coming_soon' && featureParam) {
        triggerToast(`${featureParam} module is coming soon!`);
    }
};

onMounted(syncParams);
watch(() => page.url, syncParams);

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
    status: 'active' as 'active' | 'inactive'
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
    status: 'active' as 'active' | 'inactive'
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
    router.post(`/${currentTeamSlug.value}/dashboard/products/${productId}/stock`, {
        stock: newStock
    }, {
        preserveScroll: true,
        onSuccess: () => triggerToast("📦 Stock level updated successfully!")
    });
};

const shipOrder = (dbId: number) => {
    router.post(`/${currentTeamSlug.value}/dashboard/orders/${dbId}/ship`, {}, {
        preserveScroll: true,
        onSuccess: () => triggerToast("🚚 Order marked as shipped!")
    });
};

const cancelOrder = (dbId: number) => {
    router.post(`/${currentTeamSlug.value}/dashboard/orders/${dbId}/cancel`, {}, {
        preserveScroll: true,
        onSuccess: () => triggerToast("❌ Order cancelled.")
    });
};

const handleCreateCoupon = () => {
    formErrors.value = {};
    let hasError = false;

    if (!newCoupon.value.code.trim()) {
        formErrors.value.code = "Coupon code is required.";
        hasError = true;
    }

    if (newCoupon.value.discountValue <= 0) {
        formErrors.value.discountValue = "Value must be positive.";
        hasError = true;
    } else if (newCoupon.value.discountType === 'percentage' && newCoupon.value.discountValue > 100) {
        formErrors.value.discountValue = "Percentage cannot exceed 100%.";
        hasError = true;
    }

    if (newCoupon.value.minOrderAmount < 0) {
        formErrors.value.minOrderAmount = "Minimum order cannot be negative.";
        hasError = true;
    }

    if (hasError) return;

    router.post(`/${currentTeamSlug.value}/dashboard/coupons`, {
        code: newCoupon.value.code.trim().toUpperCase(),
        discountType: newCoupon.value.discountType,
        discountValue: newCoupon.value.discountValue,
        minOrderAmount: newCoupon.value.minOrderAmount,
        usageLimit: newCoupon.value.usageLimit,
        expiresAt: newCoupon.value.expiresAt || null,
        status: newCoupon.value.status
    }, {
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
                status: 'active'
            };
            triggerToast("🎉 Coupon created successfully!");
        }
    });
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
        status: coupon.status
    };
    formErrors.value = {};
    showEditCouponModal.value = true;
};

const handleUpdateCoupon = () => {
    formErrors.value = {};
    let hasError = false;

    if (!editCoupon.value.code.trim()) {
        formErrors.value.code = "Coupon code is required.";
        hasError = true;
    }

    if (editCoupon.value.discountValue <= 0) {
        formErrors.value.discountValue = "Value must be positive.";
        hasError = true;
    } else if (editCoupon.value.discountType === 'percentage' && editCoupon.value.discountValue > 100) {
        formErrors.value.discountValue = "Percentage cannot exceed 100%.";
        hasError = true;
    }

    if (editCoupon.value.minOrderAmount < 0) {
        formErrors.value.minOrderAmount = "Minimum order cannot be negative.";
        hasError = true;
    }

    if (hasError) return;

    router.put(`/${currentTeamSlug.value}/dashboard/coupons/${editingCouponId.value}`, {
        code: editCoupon.value.code.trim().toUpperCase(),
        discountType: editCoupon.value.discountType,
        discountValue: editCoupon.value.discountValue,
        minOrderAmount: editCoupon.value.minOrderAmount,
        usageLimit: editCoupon.value.usageLimit,
        expiresAt: editCoupon.value.expiresAt || null,
        status: editCoupon.value.status
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showEditCouponModal.value = false;
            editingCouponId.value = null;
            triggerToast("🎉 Coupon updated successfully!");
        }
    });
};

const toggleCouponStatus = (couponId: number) => {
    router.post(`/${currentTeamSlug.value}/dashboard/coupons/${couponId}/toggle`, {}, {
        preserveScroll: true,
        onSuccess: () => triggerToast("🏷️ Coupon status updated!")
    });
};

const deleteCoupon = (couponId: number) => {
    router.delete(`/${currentTeamSlug.value}/dashboard/coupons/${couponId}`, {
        preserveScroll: true,
        onSuccess: () => triggerToast("🗑️ Coupon removed.")
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
    return list.filter(p => {
        const matchesCategory = filterStatus.value === 'All' || p.category === filterStatus.value;
        const matchesSearch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            p.category.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});

const filteredOrders = computed(() => {
    const list = props.orders || [];
    return list.filter(o => {
        const matchesStatus = filterStatus.value === 'All' || o.status === filterStatus.value;
        const matchesSearch = (o.customer || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            o.id.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesSearch;
    });
});

const filteredCoupons = computed(() => {
    const list = props.coupons || [];
    return list.filter(c => {
        const matchesStatus = filterStatus.value === 'All' || 
                             (filterStatus.value === 'Active' && c.status === 'active') ||
                             (filterStatus.value === 'Inactive' && c.status === 'inactive');
        const matchesSearch = c.code.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesSearch;
    });
});
</script>

<template>
    <Head :title="role === 'admin' ? 'Admin Dashboard' : 'Customer Dashboard'" />

    <!-- 1. ADMIN DASHBOARD TEMPLATE -->
    <div v-if="role === 'admin'" class="px-4 sm:px-6 lg:px-8 py-6 space-y-6 pb-12 text-neutral-800 dark:text-neutral-200 overflow-x-hidden">
        
        <PendingInvitationsModal
            v-if="pendingInvitations && pendingInvitations.length > 0"
            :invitations="pendingInvitations"
        />

        <!-- Admin Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">Admin Overview Dashboard</h1>
                <p class="text-xs text-neutral-500">Monitor store sales, manage coupons, update stock, and audit payment gateways.</p>
            </div>
            
            <!-- Quick navigation tabs -->
            <div class="flex flex-wrap items-center bg-neutral-100 rounded-lg p-1 dark:bg-neutral-800 self-start">
                <button 
                    @click="activeTab = 'overview'; searchQuery = ''; filterStatus = 'All'"
                    :class="activeTab === 'overview' ? 'bg-white text-emerald-600 shadow-xs dark:bg-neutral-900 dark:text-emerald-400' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition"
                >
                    Overview
                </button>
                <button 
                    @click="activeTab = 'products'; searchQuery = ''; filterStatus = 'All'"
                    :class="activeTab === 'products' ? 'bg-white text-emerald-600 shadow-xs dark:bg-neutral-900 dark:text-emerald-400' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition"
                >
                    Products
                </button>
                <button 
                    @click="activeTab = 'orders'; searchQuery = ''; filterStatus = 'All'"
                    :class="activeTab === 'orders' ? 'bg-white text-emerald-600 shadow-xs dark:bg-neutral-900 dark:text-emerald-400' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition"
                >
                    Orders
                </button>
                <button 
                    @click="activeTab = 'coupons'; searchQuery = ''; filterStatus = 'All'"
                    :class="activeTab === 'coupons' ? 'bg-white text-emerald-600 shadow-xs dark:bg-neutral-900 dark:text-emerald-400' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition"
                >
                    Coupons
                </button>
            </div>
        </div>

        <!-- 4-Column Stat Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Revenue widget -->
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Paid Revenue</span>
                    <DollarSign class="h-4 w-4 text-emerald-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">${{ (stats.totalRevenue ?? 0).toFixed(2) }}</div>
                    <div class="flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                        <TrendingUp class="h-3 w-3" /> +14.2% from last week
                    </div>
                </div>
                <svg class="h-8 w-full text-emerald-500" viewBox="0 0 100 10" preserveAspectRatio="none">
                    <path d="M0,8 L20,6 L40,9 L60,4 L80,7 L100,2" fill="none" stroke="currentColor" stroke-width="1.5" />
                </svg>
            </div>

            <!-- Orders widget -->
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Pending Orders</span>
                    <ShoppingBag class="h-4 w-4 text-amber-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">{{ stats.pendingCount ?? 0 }}</div>
                    <div class="text-[10px] font-medium text-neutral-400">Requires processing dispatch</div>
                </div>
                <div class="flex items-end gap-1 h-8 pt-2">
                    <div class="bg-neutral-100 dark:bg-neutral-800 w-full h-3 rounded"></div>
                    <div class="bg-neutral-100 dark:bg-neutral-800 w-full h-5 rounded"></div>
                    <div class="bg-neutral-100 dark:bg-neutral-800 w-full h-2 rounded"></div>
                    <div class="bg-amber-500 w-full h-7 rounded"></div>
                </div>
            </div>

            <!-- Coupons widget -->
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Active Coupons</span>
                    <Tag class="h-4 w-4 text-indigo-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">{{ stats.activeCouponCount ?? 0 }}</div>
                    <div class="text-[10px] text-neutral-400 flex items-center gap-1">
                        <CheckCircle2 class="h-3 w-3 text-emerald-500" /> Active in checkout system
                    </div>
                </div>
                <div class="text-[10px] font-mono text-neutral-400 border-t border-neutral-100 pt-2 dark:border-neutral-800">
                    Offers discounts storewide
                </div>
            </div>

            <!-- Low Stock widget -->
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Low Stock Alert</span>
                    <AlertTriangle class="h-4 w-4 text-red-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">{{ stats.lowStockCount ?? 0 }}</div>
                    <div class="flex items-center gap-1 text-[10px] font-bold text-amber-500">
                        Requires inventory replenishment
                    </div>
                </div>
                <div class="w-full bg-neutral-100 dark:bg-neutral-800 h-1 rounded-full">
                    <div class="bg-red-500 h-1 rounded-full" style="width: 35%"></div>
                </div>
            </div>

        </div>

        <!-- TAB Content -->
        <!-- 1. TAB: ANALYTICS OVERVIEW -->
        <div v-if="activeTab === 'overview'" class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 space-y-6">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <h3 class="text-base font-bold tracking-tight flex items-center gap-2">
                        <Activity class="h-4 w-4 text-emerald-500" /> Monthly Revenue Analytics
                    </h3>
                    <span class="text-[10px] font-bold text-neutral-400">FY 2026</span>
                </div>
                <div class="relative h-60 w-full pt-4">
                    <svg class="h-full w-full overflow-visible" viewBox="0 0 100 50">
                        <line x1="0" y1="10" x2="100" y2="10" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                        <line x1="0" y1="20" x2="100" y2="20" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                        <line x1="0" y1="30" x2="100" y2="30" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                        <line x1="0" y1="40" x2="100" y2="40" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                        
                        <text x="0" y="48" font-size="2" fill="#999">Jan</text>
                        <text x="20" y="48" font-size="2" fill="#999">Mar</text>
                        <text x="40" y="48" font-size="2" fill="#999">May</text>
                        <text x="60" y="48" font-size="2" fill="#999">Jul</text>
                        <text x="80" y="48" font-size="2" fill="#999">Sep</text>
                        <text x="96" y="48" font-size="2" fill="#999">Nov</text>
                        
                        <path d="M0,45 L10,38 L20,41 L30,22 L40,25 L50,15 L60,18 L70,8 L80,12 L90,6 L100,2" fill="none" stroke="rgb(16,185,129)" stroke-width="1" />
                        <path d="M0,45 L10,38 L20,41 L30,22 L40,25 L50,15 L60,18 L70,8 L80,12 L90,6 L100,2 L100,45 L0,45 Z" fill="rgba(16,185,129,0.08)" />
                    </svg>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                <h3 class="text-base font-bold tracking-tight border-b border-neutral-100 pb-3 dark:border-neutral-800">Recent Activity Logs</h3>
                <div class="space-y-4 max-h-[16.5rem] overflow-y-auto pr-2 text-xs">
                    <div class="flex gap-2.5" v-for="order in (orders || []).slice(0, 3)" :key="order.id">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 shrink-0 dark:bg-emerald-950 dark:text-emerald-400">
                            <Check class="h-3 w-3" />
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-semibold">Order #{{ order.id }} - {{ order.status }}</p>
                            <span class="text-[10px] text-neutral-400">{{ order.customer }} · {{ order.date }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2.5" v-if="!orders || orders.length === 0">
                        <span class="text-neutral-400">No recent orders yet.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TAB: PRODUCTS TABLE -->
        <div v-else-if="activeTab === 'products'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-100 pb-4 dark:border-neutral-800">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search products..." 
                        class="h-10 w-full rounded-lg border border-neutral-200 bg-white pl-10 pr-4 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    />
                </div>
            </div>

            <!-- Products Table -->
            <div class="rounded-xl border border-neutral-200 bg-white overflow-hidden shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50 border-b border-neutral-200 text-neutral-500 dark:bg-neutral-800/40 dark:border-neutral-800">
                                <th class="p-4 font-semibold w-16">ID</th>
                                <th class="p-4 font-semibold">Product Name</th>
                                <th class="p-4 font-semibold w-32">Category</th>
                                <th class="p-4 font-semibold text-center w-28">Price</th>
                                <th class="p-4 font-semibold text-center w-28">Stock Level</th>
                                <th class="p-4 font-semibold w-28">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20">
                                <td class="p-4 font-mono text-neutral-400">#00{{ product.id }}</td>
                                <td class="p-4 font-semibold">{{ product.name }}</td>
                                <td class="p-4 text-neutral-500">{{ product.category }}</td>
                                <td class="p-4 text-center font-bold font-mono">${{ product.price.toFixed(2) }}</td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            @click="updateProductStock(product.id, Math.max(0, product.stock - 1))"
                                            class="h-6 w-6 rounded border border-neutral-200 bg-neutral-50 hover:bg-neutral-100 flex items-center justify-center text-xs dark:border-neutral-800 dark:bg-neutral-800 dark:hover:bg-neutral-700"
                                        >
                                            -
                                        </button>
                                        <span class="w-8 font-mono font-bold">{{ product.stock }}</span>
                                        <button 
                                            @click="updateProductStock(product.id, product.stock + 1)"
                                            class="h-6 w-6 rounded border border-neutral-200 bg-neutral-50 hover:bg-neutral-100 flex items-center justify-center text-xs dark:border-neutral-800 dark:bg-neutral-800 dark:hover:bg-neutral-700"
                                        >
                                            +
                                        </button>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span 
                                        v-if="product.status === 'In Stock'" 
                                        class="inline-flex rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-bold text-green-600 dark:bg-green-950 dark:text-green-400"
                                    >
                                        In Stock
                                    </span>
                                    <span 
                                        v-else-if="product.status === 'Low Stock'" 
                                        class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                                    >
                                        Low Stock
                                    </span>
                                    <span 
                                        v-else 
                                        class="inline-flex rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-600 dark:bg-red-950 dark:text-red-400"
                                    >
                                        Out of Stock
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. TAB: ORDERS TABLE -->
        <div v-else-if="activeTab === 'orders'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-100 pb-4 dark:border-neutral-800">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search orders..." 
                        class="h-10 w-full rounded-lg border border-neutral-200 bg-white pl-10 pr-4 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    />
                </div>
            </div>

            <!-- Orders Table -->
            <div class="rounded-xl border border-neutral-200 bg-white overflow-hidden shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50 border-b border-neutral-200 text-neutral-500 dark:bg-neutral-800/40 dark:border-neutral-800">
                                <th class="p-4 font-semibold w-24">Order ID</th>
                                <th class="p-4 font-semibold w-28">Invoice No</th>
                                <th class="p-4 font-semibold">Customer</th>
                                <th class="p-4 font-semibold w-28">Order Date</th>
                                <th class="p-4 font-semibold text-center w-28">Gateway</th>
                                <th class="p-4 font-semibold text-center w-24">Total</th>
                                <th class="p-4 font-semibold w-24">Status</th>
                                <th class="p-4 font-semibold text-center w-36">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                            <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20">
                                <td class="p-4 font-mono font-semibold">{{ order.id }}</td>
                                <td class="p-4 font-mono text-neutral-500">{{ order.invoice_no || '-' }}</td>
                                <td class="p-4 font-semibold">{{ order.customer }}</td>
                                <td class="p-4 text-neutral-500">{{ order.date }}</td>
                                <td class="p-4 text-center">
                                    <span class="rounded bg-neutral-100 px-2 py-0.5 text-[9px] font-bold uppercase text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                                        {{ order.gateway }}
                                    </span>
                                </td>
                                <td class="p-4 text-center font-bold font-mono">${{ order.total.toFixed(2) }}</td>
                                <td class="p-4">
                                    <span 
                                        v-if="order.status === 'Paid'" 
                                        class="inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-[10px] font-bold text-green-600 dark:bg-green-950 dark:text-green-400"
                                    >
                                        Paid
                                    </span>
                                    <span 
                                        v-else-if="order.status === 'Pending'" 
                                        class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                                    >
                                        Pending
                                    </span>
                                    <span 
                                        v-else-if="order.status === 'Failed'" 
                                        class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-[10px] font-bold text-red-600 dark:bg-red-950 dark:text-red-400"
                                    >
                                        Failed
                                    </span>
                                    <span 
                                        v-else 
                                        class="inline-flex rounded-full bg-neutral-100 px-2.5 py-0.5 text-[10px] font-bold text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400"
                                    >
                                        Cancelled
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            v-if="order.status === 'Pending' && order.db_id"
                                            @click="shipOrder(order.db_id)"
                                            class="h-7 px-2.5 rounded bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition"
                                        >
                                            Ship & Pay
                                        </button>
                                        <button 
                                            v-if="order.status === 'Pending' && order.db_id"
                                            @click="cancelOrder(order.db_id)"
                                            class="h-7 px-2.5 rounded bg-neutral-100 border hover:bg-red-50 hover:text-red-500 transition dark:bg-neutral-800 dark:border-neutral-700"
                                        >
                                            Cancel
                                        </button>
                                        <span v-else class="text-[10px] text-neutral-400">Completed</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. TAB: COUPONS -->
        <div v-else-if="activeTab === 'coupons'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-100 pb-4 dark:border-neutral-800">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search coupons..." 
                        class="h-10 w-full rounded-lg border border-neutral-200 bg-white pl-10 pr-4 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    />
                </div>
                
                <button 
                    @click="showCreateCouponModal = true"
                    class="h-10 rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 flex items-center gap-1.5 transition"
                >
                    <Plus class="h-4 w-4" /> Create Coupon
                </button>
            </div>

            <!-- Coupons Table -->
            <div class="rounded-xl border border-neutral-200 bg-white overflow-hidden shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50 border-b border-neutral-200 text-neutral-500 dark:bg-neutral-800/40 dark:border-neutral-800">
                                <th class="p-4 font-semibold">Code</th>
                                <th class="p-4 font-semibold w-32 text-center">Type</th>
                                <th class="p-4 font-semibold w-24 text-center">Discount Value</th>
                                <th class="p-4 font-semibold w-28 text-center">Min Order</th>
                                <th class="p-4 font-semibold w-32 text-center">Usages (Limit)</th>
                                <th class="p-4 font-semibold w-28">Expires At</th>
                                <th class="p-4 font-semibold w-24">Status</th>
                                <th class="p-4 font-semibold text-center w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                            <tr v-for="coupon in filteredCoupons" :key="coupon.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20">
                                <td class="p-4">
                                    <span class="rounded bg-neutral-100 px-2 py-1 font-mono font-bold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                        {{ coupon.code }}
                                    </span>
                                </td>
                                <td class="p-4 text-center uppercase font-semibold text-neutral-500">
                                    {{ coupon.discountType }}
                                </td>
                                <td class="p-4 text-center font-bold font-mono">
                                    {{ coupon.discountType === 'percentage' ? `${coupon.discountValue}%` : `$${coupon.discountValue.toFixed(2)}` }}
                                </td>
                                <td class="p-4 text-center font-mono">${{ coupon.minOrderAmount.toFixed(2) }}</td>
                                <td class="p-4 text-center font-mono">
                                    {{ coupon.usedCount ?? 0 }} / <span class="text-neutral-400">{{ coupon.usageLimit }}</span>
                                </td>
                                <td class="p-4 text-neutral-500 font-mono">{{ coupon.expiresAt }}</td>
                                <td class="p-4">
                                    <button 
                                        @click="toggleCouponStatus(coupon.id)"
                                        :class="coupon.status === 'active' ? 'bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-400' : 'bg-neutral-100 text-neutral-400 dark:bg-neutral-800 dark:text-neutral-400'"
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold transition hover:opacity-85"
                                    >
                                        {{ coupon.status === 'active' ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            @click="openEditCouponModal(coupon)"
                                            class="h-8 w-8 rounded-lg bg-neutral-100 text-neutral-400 hover:bg-emerald-50 hover:text-emerald-600 flex items-center justify-center transition dark:bg-neutral-800 dark:hover:bg-emerald-950"
                                            title="Edit Coupon"
                                        >
                                            <Edit class="h-3.5 w-3.5" />
                                        </button>
                                        <button 
                                            @click="deleteCoupon(coupon.id)"
                                            class="h-8 w-8 rounded-lg bg-neutral-100 text-neutral-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition dark:bg-neutral-800 dark:hover:bg-red-950"
                                            title="Delete Coupon"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create Coupon Dialog -->
            <div v-if="showCreateCouponModal" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center">
                <div @click="showCreateCouponModal = false" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
                <div class="relative w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                        <h3 class="text-base font-bold tracking-tight">Create Coupon Code</h3>
                        <button @click="showCreateCouponModal = false" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Coupon Code</label>
                            <input 
                                v-model="newCoupon.code" 
                                type="text" 
                                placeholder="e.g. MINT75"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                            <p v-if="formErrors.code" class="text-[10px] font-semibold text-red-500">{{ formErrors.code }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Discount Type</label>
                                <select 
                                    v-model="newCoupon.discountType" 
                                    class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                >
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="flat">Flat Amount ($)</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Discount Value</label>
                                <input 
                                    v-model="newCoupon.discountValue" 
                                    type="number" 
                                    class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                />
                                <p v-if="formErrors.discountValue" class="text-[10px] font-semibold text-red-500">{{ formErrors.discountValue }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Min Order ($)</label>
                                <input 
                                    v-model="newCoupon.minOrderAmount" 
                                    type="number" 
                                    class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                />
                                <p v-if="formErrors.minOrderAmount" class="text-[10px] font-semibold text-red-500">{{ formErrors.minOrderAmount }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Usage Limit</label>
                                <input 
                                    v-model="newCoupon.usageLimit" 
                                    type="number" 
                                    class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Expiry Date</label>
                                <input 
                                    v-model="newCoupon.expiresAt" 
                                    type="date" 
                                    class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Active Status</label>
                                <select 
                                    v-model="newCoupon.status" 
                                    class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-neutral-100 dark:border-neutral-800">
                        <button 
                            @click="showCreateCouponModal = false"
                            class="h-9 px-4 rounded-lg border text-xs font-semibold hover:bg-neutral-50 dark:border-neutral-700"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="handleCreateCoupon"
                            class="h-9 px-4 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition"
                        >
                            Create Coupon
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Coupon Dialog -->
            <div v-if="showEditCouponModal" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center">
                <div @click="showEditCouponModal = false" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
                <div class="relative w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                        <h3 class="text-base font-bold tracking-tight">Edit Coupon Code</h3>
                        <button @click="showEditCouponModal = false" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Coupon Code</label>
                            <input 
                                v-model="editCoupon.code" 
                                type="text" 
                                placeholder="e.g. MINT75"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                            <p v-if="formErrors.code" class="text-[10px] font-semibold text-red-500">{{ formErrors.code }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Discount Type</label>
                                <select 
                                    v-model="editCoupon.discountType" 
                                    class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                >
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="flat">Flat Amount ($)</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Discount Value</label>
                                <input 
                                    v-model="editCoupon.discountValue" 
                                    type="number" 
                                    class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                />
                                <p v-if="formErrors.discountValue" class="text-[10px] font-semibold text-red-500">{{ formErrors.discountValue }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Min Order ($)</label>
                                <input 
                                    v-model="editCoupon.minOrderAmount" 
                                    type="number" 
                                    class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                />
                                <p v-if="formErrors.minOrderAmount" class="text-[10px] font-semibold text-red-500">{{ formErrors.minOrderAmount }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Usage Limit</label>
                                <input 
                                    v-model="editCoupon.usageLimit" 
                                    type="number" 
                                    class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Expiry Date</label>
                                <input 
                                    v-model="editCoupon.expiresAt" 
                                    type="date" 
                                    class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Active Status</label>
                                <select 
                                    v-model="editCoupon.status" 
                                    class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-neutral-100 dark:border-neutral-800">
                        <button 
                            @click="showEditCouponModal = false"
                            class="h-9 px-4 rounded-lg border text-xs font-semibold hover:bg-neutral-50 dark:border-neutral-700"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="handleUpdateCoupon"
                            class="h-9 px-4 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- 2. CUSTOMER DASHBOARD TEMPLATE -->
    <div v-else class="px-4 sm:px-6 lg:px-8 py-6 space-y-6 pb-12 text-neutral-800 dark:text-neutral-200 overflow-x-hidden">
        

        <!-- Customer Stats -->
        <div class="grid gap-4 grid-cols-3">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Orders Placed</span>
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-mono leading-none">{{ stats.totalOrders ?? 0 }}</span>
                    <span class="text-[10px] text-neutral-400">total</span>
                </div>
                <div class="text-[9px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-bold">
                    <Package class="h-3 w-3" /> Fully tracked
                </div>
            </div>
            
            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Saved with Coupons</span>
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-mono leading-none">${{ (stats.totalSaved ?? 0).toFixed(2) }}</span>
                    <span class="text-[10px] text-neutral-400">saved</span>
                </div>
                <div class="text-[9px] text-indigo-600 dark:text-indigo-400 flex items-center gap-1 font-bold">
                    <PercentCircle class="h-3 w-3" /> Best offers utilized
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Wishlist items</span>
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-mono leading-none">{{ stats.wishlistCount ?? 0 }}</span>
                    <span class="text-[10px] text-neutral-400">items</span>
                </div>
                <div class="text-[9px] text-rose-600 dark:text-rose-400 flex items-center gap-1 font-bold">
                    <Heart class="h-3 w-3" /> Saved for later
                </div>
            </div>
        </div>

        <!-- Main section layout -->
        <div class="grid gap-6 lg:grid-cols-3">
            
            <!-- Left 2 columns: Tab Content -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- Customer Tabs -->
                <div class="flex border-b border-neutral-200 dark:border-neutral-800 gap-6">
                    <button 
                        @click="customerTab = 'orders'"
                        :class="customerTab === 'orders' ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-neutral-400 hover:text-neutral-700'"
                        class="pb-3 border-b-2 font-bold text-xs transition"
                    >
                        My Order History
                    </button>
                    <button 
                        @click="customerTab = 'coupons'"
                        :class="customerTab === 'coupons' ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-neutral-400 hover:text-neutral-700'"
                        class="pb-3 border-b-2 font-bold text-xs transition"
                    >
                        Available Coupons
                    </button>
                    <button 
                        @click="customerTab = 'settings'"
                        :class="customerTab === 'settings' ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-neutral-400 hover:text-neutral-700'"
                        class="pb-3 border-b-2 font-bold text-xs transition"
                    >
                        Account Security Settings
                    </button>
                </div>

                <!-- Tab 1: Orders -->
                <div v-if="customerTab === 'orders'" class="space-y-4">
                    <div v-if="!orders || orders.length === 0" class="rounded-xl border-2 border-dashed border-neutral-200 p-8 text-center dark:border-neutral-800">
                        <ShoppingBag class="mx-auto h-8 w-8 text-neutral-300" />
                        <h3 class="mt-2 text-sm font-bold">No orders placed yet</h3>
                        <p class="text-xs text-neutral-400 mt-1">Once you complete a checkout, your order will show up here.</p>
                        <Link href="/shop" class="mt-4 inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                            Explore Catalog
                        </Link>
                    </div>

                    <div v-else class="rounded-xl border border-neutral-200 bg-white overflow-hidden shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead>
                                    <tr class="bg-neutral-50 border-b border-neutral-200 text-neutral-500 dark:bg-neutral-800/40 dark:border-neutral-800">
                                        <th class="p-4 font-semibold">Order ID</th>
                                        <th class="p-4 font-semibold">Invoice No</th>
                                        <th class="p-4 font-semibold">Date</th>
                                        <th class="p-4 font-semibold text-center">Payment Gateway</th>
                                        <th class="p-4 font-semibold text-right">Total Amount</th>
                                        <th class="p-4 font-semibold text-center">Delivery Status</th>
                                        <th class="p-4 font-semibold text-center">Invoice Detail</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                                    <tr v-for="order in orders" :key="order.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20">
                                        <td class="p-4 font-mono font-semibold">{{ order.id }}</td>
                                        <td class="p-4 font-mono text-neutral-500">{{ order.invoice_no || '-' }}</td>
                                        <td class="p-4 text-neutral-500">{{ order.date }}</td>
                                        <td class="p-4 text-center">
                                            <span class="rounded bg-neutral-100 px-2 py-0.5 text-[9px] font-bold uppercase text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                                                {{ order.gateway }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-right font-bold font-mono">${{ order.total.toFixed(2) }}</td>
                                        <td class="p-4 text-center">
                                            <span 
                                                v-if="order.status === 'Paid'" 
                                                class="inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-[10px] font-bold text-green-600 dark:bg-green-950 dark:text-green-400"
                                            >
                                                Completed (Shipped)
                                            </span>
                                            <span 
                                                v-else-if="order.status === 'Pending'" 
                                                class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                                            >
                                                Processing
                                            </span>
                                            <span 
                                                v-else-if="order.status === 'Failed'" 
                                                class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-[10px] font-bold text-red-600 dark:bg-red-950 dark:text-red-400"
                                            >
                                                Failed
                                            </span>
                                            <span 
                                                v-else 
                                                class="inline-flex rounded-full bg-neutral-100 px-2.5 py-0.5 text-[10px] font-bold text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400"
                                            >
                                                Cancelled
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            <button 
                                                @click="openInvoice(order)"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-neutral-50 border border-neutral-200 px-2.5 py-1 text-[10px] font-bold text-neutral-600 hover:bg-emerald-50 hover:text-emerald-600 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-emerald-950/40 transition"
                                            >
                                                <FileText class="h-3.5 w-3.5" />
                                                <span>View Invoice</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Available Coupons -->
                <div v-else-if="customerTab === 'coupons'" class="grid gap-4 sm:grid-cols-2">
                    <div 
                        v-for="coupon in coupons" 
                        :key="coupon.id" 
                        class="relative overflow-hidden rounded-xl border-2 border-dashed border-emerald-500/30 bg-emerald-50/20 p-5 dark:bg-emerald-950/10 flex flex-col justify-between gap-4"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-sm font-black tracking-wider text-emerald-600 dark:text-emerald-400">
                                    {{ coupon.code }}
                                </span>
                                <button 
                                    @click="copyCouponCode(coupon.code)"
                                    class="h-7 w-7 rounded-md bg-white border flex items-center justify-center hover:bg-neutral-50 dark:bg-neutral-900 dark:border-neutral-700 transition"
                                >
                                    <Copy class="h-3.5 w-3.5 text-neutral-500" />
                                </button>
                            </div>
                            <p class="text-xs text-neutral-600 dark:text-neutral-400">
                                {{ coupon.description || 'Enjoy flat discount storewide' }}
                            </p>
                        </div>
                        <div class="border-t border-dashed border-emerald-500/20 pt-2 flex justify-between items-center text-[10px] text-neutral-500">
                            <span>Min Order: <strong>${{ coupon.minOrderAmount.toFixed(2) }}</strong></span>
                            <span class="bg-emerald-600 text-white px-2 py-0.5 rounded-full font-bold">
                                {{ coupon.discountType === 'percentage' ? `${coupon.discountValue}% OFF` : `$${coupon.discountValue} OFF` }}
                            </span>
                        </div>
                    </div>

                    <div v-if="!coupons || coupons.length === 0" class="col-span-2 text-center text-xs text-neutral-400 py-8">
                        No active promotional coupons at this moment. Check back soon!
                    </div>
                </div>

                <!-- Tab 3: Settings Shortcuts -->
                <div v-else-if="customerTab === 'settings'" class="grid gap-4 sm:grid-cols-3">
                    <Link 
                        href="/settings/profile" 
                        class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs hover:border-emerald-500 transition dark:border-neutral-800 dark:bg-neutral-900 space-y-3 flex flex-col justify-between"
                    >
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center dark:bg-emerald-950 dark:text-emerald-400">
                            <User class="h-4 w-4" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold">Update Profile</h4>
                            <p class="text-[10px] text-neutral-400 mt-1">Change your name, email address, or contact details.</p>
                        </div>
                    </Link>

                    <Link 
                        href="/settings/password" 
                        class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs hover:border-emerald-500 transition dark:border-neutral-800 dark:bg-neutral-900 space-y-3 flex flex-col justify-between"
                    >
                        <div class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center dark:bg-amber-950 dark:text-amber-400">
                            <Key class="h-4 w-4" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold">Manage Password</h4>
                            <p class="text-[10px] text-neutral-400 mt-1">Change and secure your login password regularly.</p>
                        </div>
                    </Link>

                    <Link 
                        href="/settings/security" 
                        class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs hover:border-emerald-500 transition dark:border-neutral-800 dark:bg-neutral-900 space-y-3 flex flex-col justify-between"
                    >
                        <div class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center dark:bg-indigo-950 dark:text-indigo-400">
                            <Shield class="h-4 w-4" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold">Two-Factor Security</h4>
                            <p class="text-[10px] text-neutral-400 mt-1">Add authentication steps for improved account safety.</p>
                        </div>
                    </Link>
                </div>

            </div>

            <!-- Right Column: Recommended products / Side widgets -->
            <div class="space-y-6">
                
                <!-- Recommended Items Widget -->
                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-neutral-400 flex items-center gap-1.5">
                        <TrendingUp class="h-4 w-4 text-emerald-500" /> Recommended For You
                    </h3>

                    <div class="space-y-3">
                        <div 
                            v-for="prod in recommendedProducts" 
                            :key="prod.id"
                            class="flex gap-3 items-center group"
                        >
                            <img 
                                :src="prod.image" 
                                alt="" 
                                class="h-12 w-12 rounded-lg object-cover bg-neutral-100"
                            />
                            <div class="flex-1 space-y-0.5">
                                <span class="text-[9px] font-bold text-neutral-400 uppercase tracking-tight">{{ prod.category }}</span>
                                <h4 class="text-xs font-bold leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition truncate max-w-[140px]">
                                    {{ prod.name }}
                                </h4>
                                <span class="font-mono text-xs font-bold">${{ prod.price.toFixed(2) }}</span>
                            </div>
                            <Link 
                                :href="`/shop?category=${prod.category}`" 
                                class="h-8 w-8 rounded-lg bg-neutral-50 hover:bg-emerald-50 flex items-center justify-center text-neutral-400 hover:text-emerald-600 transition dark:bg-neutral-800 dark:hover:bg-emerald-950"
                            >
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Support Ticket Callout -->
                <div class="rounded-xl bg-gradient-to-br from-neutral-900 to-neutral-850 p-5 text-white shadow-lg space-y-3 dark:from-neutral-900 dark:to-neutral-950">
                    <h4 class="text-xs font-bold">Need Help with an Order?</h4>
                    <p class="text-[10px] text-neutral-400">
                        Our customer service operates 24/7. Open a support ticket, and our support representatives will respond shortly.
                    </p>
                    <button 
                        @click="triggerToast('💬 Support ticket request sent! We will connect with you via email.')"
                        class="w-full h-8 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-xs font-bold transition"
                    >
                        Contact Customer Support
                    </button>
                </div>

            </div>

        </div>

    </div>

    <!-- DETAILED INVOICE MODAL -->
    <div 
        v-if="showInvoiceModal && selectedInvoice" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 backdrop-blur-xs p-4 print:p-0 print:bg-white print:relative print:z-0"
    >
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800 max-h-[90vh] overflow-y-auto flex flex-col gap-6 print:max-h-none print:overflow-visible print:border-none print:shadow-none print:p-0">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-neutral-100 pb-4 dark:border-neutral-800 print:border-neutral-200">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-black">
                        M
                    </div>
                    <div>
                        <h2 class="font-extrabold text-sm tracking-tight text-neutral-900 dark:text-white print:text-neutral-900">StoreMint Inc.</h2>
                        <p class="text-[10px] text-neutral-400">Premium E-commerce Experience</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 print:hidden">
                    <button 
                        @click="printInvoice"
                        class="h-8 inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 text-xs font-semibold text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 transition"
                    >
                        <Printer class="h-3.5 w-3.5" />
                        <span>Print Invoice</span>
                    </button>
                    <button 
                        @click="closeInvoice"
                        class="h-8 w-8 flex items-center justify-center rounded-lg border border-neutral-200 hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-800 transition"
                    >
                        <X class="h-4 w-4 animate-in" />
                    </button>
                </div>
            </div>

            <!-- Invoice Identity -->
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Invoice Information</span>
                    <h3 class="font-black text-sm text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ selectedInvoice.invoice_no || 'N/A' }}
                    </h3>
                    <p class="text-[10px] text-neutral-500 mt-0.5">Order ID: {{ selectedInvoice.id }}</p>
                    <p class="text-[10px] text-neutral-500">Date: {{ selectedInvoice.date }}</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Payment Status</span>
                    <div class="mt-1">
                        <span 
                            class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider"
                            :class="{
                                'bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-400': selectedInvoice.status === 'Paid',
                                'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400': selectedInvoice.status === 'Pending',
                                'bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-400': selectedInvoice.status === 'Failed',
                                'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400': selectedInvoice.status === 'Cancelled'
                            }"
                        >
                            {{ selectedInvoice.status }}
                        </span>
                    </div>
                    <p class="text-[10px] text-neutral-500 mt-1.5">Payment Status: <span class="font-bold uppercase text-neutral-800 dark:text-neutral-200 print:text-neutral-850">{{ selectedInvoice.payment_status }}</span></p>
                    <p class="text-[10px] text-neutral-500">Gateway: <span class="font-bold uppercase text-neutral-800 dark:text-neutral-200 print:text-neutral-850">{{ selectedInvoice.gateway }}</span></p>
                </div>
            </div>

            <!-- Billing & Shipping Details -->
            <div class="grid grid-cols-2 gap-6 border-t border-b border-neutral-100 py-4 dark:border-neutral-800 text-xs">
                <div>
                    <h4 class="font-bold text-neutral-400 uppercase tracking-wider text-[10px] mb-1.5">Billed To</h4>
                    <p class="font-bold text-neutral-850 dark:text-neutral-100 print:text-neutral-900">{{ page.props.auth.user.name }}</p>
                    <p class="text-neutral-500">{{ page.props.auth.user.email }}</p>
                </div>
                <div>
                    <h4 class="font-bold text-neutral-400 uppercase tracking-wider text-[10px] mb-1.5">Shipping Address</h4>
                    <p class="text-neutral-600 dark:text-neutral-350 leading-relaxed font-sans whitespace-pre-line print:text-neutral-800">
                        {{ selectedInvoice.shipping_address || 'No shipping address specified.' }}
                    </p>
                </div>
            </div>

            <!-- Summary calculations -->
            <div class="space-y-3">
                <h4 class="font-bold text-neutral-400 uppercase tracking-wider text-[10px]">Financial Summary</h4>
                
                <div class="rounded-xl bg-neutral-50 p-4 dark:bg-neutral-800/40 space-y-2 text-xs print:bg-neutral-50/50">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Subtotal</span>
                        <span class="font-mono font-semibold">${{ selectedInvoice.subtotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="selectedInvoice.discount > 0" class="flex justify-between text-emerald-600 dark:text-emerald-400">
                        <span>Coupon Savings</span>
                        <span class="font-mono font-bold">-${{ selectedInvoice.discount.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Shipping & Handling</span>
                        <span class="font-mono font-semibold">${{ selectedInvoice.shipping.toFixed(2) }}</span>
                    </div>
                    <div v-if="selectedInvoice.tax > 0" class="flex justify-between">
                        <span class="text-neutral-500">Estimated Taxes</span>
                        <span class="font-mono font-semibold">${{ selectedInvoice.tax.toFixed(2) }}</span>
                    </div>
                    <div class="border-t border-neutral-200 pt-2 flex justify-between items-baseline dark:border-neutral-700">
                        <span class="font-bold text-neutral-900 dark:text-white print:text-neutral-900">Grand Total</span>
                        <span class="font-mono text-base font-black text-emerald-600 dark:text-emerald-400">
                            ${{ selectedInvoice.total.toFixed(2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer terms -->
            <div class="text-center text-[10px] text-neutral-400 mt-2">
                <p>Thank you for shopping at StoreMint! If you have any inquiries regarding this transaction, contact support.</p>
            </div>
            
        </div>
    </div>

    <!-- GLOBAL TOAST FEEDBACK ALERT -->
    <div 
        v-if="toastMessage"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-xl bg-neutral-900 px-4 py-3 text-xs font-bold text-white shadow-xl dark:bg-neutral-100 dark:text-neutral-900"
    >
        <CheckCircle2 class="h-4 w-4 text-emerald-500" />
        <span>{{ toastMessage }}</span>
    </div>
</template>
