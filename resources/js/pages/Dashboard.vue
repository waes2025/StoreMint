<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
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
    Eye
} from '@lucide/vue';
import type { DashboardInvitation, Team } from '@/types';

// Props matching backend
defineProps<{
    pendingInvitations?: DashboardInvitation[];
}>();

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

// Admin panel tabs
const activeTab = ref<'overview' | 'products' | 'orders' | 'coupons'>('overview');

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

// 1. Mock Products State
interface Product {
    id: number;
    name: string;
    category: string;
    price: number;
    stock: number;
    status: 'In Stock' | 'Low Stock' | 'Out of Stock';
}
const products = ref<Product[]>([
    { id: 1, name: "Quantum Chronograph Watch", category: "Accessories", price: 299.00, stock: 12, status: 'In Stock' },
    { id: 2, name: "AeroBuds Pro Wireless", category: "Electronics", price: 149.00, stock: 5, status: 'Low Stock' },
    { id: 3, name: "Minimalist Leather Backpack", category: "Fashion", price: 89.00, stock: 3, status: 'Low Stock' },
    { id: 4, name: "Lumbar Comfort Office Chair", category: "Furniture", price: 249.00, stock: 15, status: 'In Stock' },
    { id: 5, name: "Ember Mug Smart Temperature", category: "Home", price: 129.00, stock: 0, status: 'Out of Stock' },
    { id: 6, name: "Aura Light Ring Lamp", category: "Electronics", price: 59.00, stock: 22, status: 'In Stock' }
]);

// 2. Mock Orders State (Section 3.3 Status Badge Colors)
interface Order {
    id: string;
    customer: string;
    date: string;
    total: number;
    gateway: 'cod' | 'sslcommerz' | 'stripe';
    status: 'Paid' | 'Pending' | 'Failed' | 'Cancelled';
}
const orders = ref<Order[]>([
    { id: "ORD-9831", customer: "Sarah Jenkins", date: "Jul 08, 2026", total: 463.00, gateway: 'stripe', status: 'Paid' },
    { id: "ORD-9830", customer: "Rahim Islam", date: "Jul 07, 2026", total: 149.00, gateway: 'sslcommerz', status: 'Paid' },
    { id: "ORD-9829", customer: "John Doe", date: "Jul 07, 2026", total: 104.00, gateway: 'cod', status: 'Pending' },
    { id: "ORD-9828", customer: "Emily Watson", date: "Jul 06, 2026", total: 299.00, gateway: 'stripe', status: 'Paid' },
    { id: "ORD-9827", customer: "Karim Khan", date: "Jul 05, 2026", total: 89.00, gateway: 'sslcommerz', status: 'Failed' },
    { id: "ORD-9826", customer: "Lisa Cuthbert", date: "Jul 04, 2026", total: 129.00, gateway: 'cod', status: 'Cancelled' }
]);

// 3. Mock Coupons State (Section 3.5 Coupon System)
interface Coupon {
    id: number;
    code: string;
    discountType: 'flat' | 'percentage';
    discountValue: number;
    minOrderAmount: number;
    usedCount: number;
    usageLimit: number;
    status: 'active' | 'inactive';
    expiresAt: string;
}
const coupons = ref<Coupon[]>([
    { id: 1, code: "MINT50", discountType: 'percentage', discountValue: 50, minOrderAmount: 50, usedCount: 42, usageLimit: 100, status: 'active', expiresAt: '2026-12-31' },
    { id: 2, code: "WELCOME10", discountType: 'flat', discountValue: 10, minOrderAmount: 40, usedCount: 124, usageLimit: 500, status: 'active', expiresAt: '2026-08-31' },
    { id: 3, code: "SUMMER20", discountType: 'percentage', discountValue: 20, minOrderAmount: 60, usedCount: 50, usageLimit: 50, status: 'inactive', expiresAt: '2026-06-30' }
]);

// 4. Create Coupon Form State
const showCreateCouponModal = ref(false);
const newCoupon = ref({
    code: '',
    discountType: 'percentage' as 'flat' | 'percentage',
    discountValue: 10,
    minOrderAmount: 0,
    usageLimit: 100,
    expiresAt: '2026-12-31',
    status: 'active' as 'active' | 'inactive'
});

interface ValidationErrors {
    code?: string;
    discountValue?: string;
    minOrderAmount?: string;
}
const formErrors = ref<ValidationErrors>({});

// Coupon Form Actions
const handleCreateCoupon = () => {
    formErrors.value = {};
    let hasError = false;

    if (!newCoupon.value.code.trim()) {
        formErrors.value.code = "Coupon code is required.";
        hasError = true;
    } else if (coupons.value.some(c => c.code === newCoupon.value.code.trim().toUpperCase())) {
        formErrors.value.code = "Coupon code already exists.";
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

    // Add to coupons list
    coupons.value.push({
        id: coupons.value.length + 1,
        code: newCoupon.value.code.trim().toUpperCase(),
        discountType: newCoupon.value.discountType,
        discountValue: newCoupon.value.discountValue,
        minOrderAmount: newCoupon.value.minOrderAmount,
        usedCount: 0,
        usageLimit: newCoupon.value.usageLimit,
        status: newCoupon.value.status,
        expiresAt: newCoupon.value.expiresAt
    });

    // Reset Form
    newCoupon.value = {
        code: '',
        discountType: 'percentage',
        discountValue: 10,
        minOrderAmount: 0,
        usageLimit: 100,
        expiresAt: '2026-12-31',
        status: 'active'
    };
    showCreateCouponModal.value = false;
    triggerToast("🎉 Coupon created successfully!");
};

// Toggle Coupon status inline
const toggleCouponStatus = (couponId: number) => {
    const c = coupons.value.find(item => item.id === couponId);
    if (c) {
        c.status = c.status === 'active' ? 'inactive' : 'active';
        triggerToast(`🏷️ Coupon "${c.code}" toggled to ${c.status}.`);
    }
};

// Filtered Lists
const filteredProducts = computed(() => {
    return products.value.filter(p => {
        const matchesCategory = filterStatus.value === 'All' || p.category === filterStatus.value;
        const matchesSearch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            p.category.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});

const filteredOrders = computed(() => {
    return orders.value.filter(o => {
        const matchesStatus = filterStatus.value === 'All' || o.status === filterStatus.value;
        const matchesSearch = o.customer.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            o.id.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesSearch;
    });
});

const filteredCoupons = computed(() => {
    return coupons.value.filter(c => {
        const matchesStatus = filterStatus.value === 'All' || 
                             (filterStatus.value === 'Active' && c.status === 'active') ||
                             (filterStatus.value === 'Inactive' && c.status === 'inactive');
        const matchesSearch = c.code.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesSearch;
    });
});

// Stat calculation helpers
const totalRevenue = computed(() => orders.value.filter(o => o.status === 'Paid').reduce((sum, o) => sum + o.total, 0));
const pendingCount = computed(() => orders.value.filter(o => o.status === 'Pending').length);
const lowStockCount = computed(() => products.value.filter(p => p.stock <= 5).length);
const activeCouponCount = computed(() => coupons.value.filter(c => c.status === 'active').length);

// Actions on data tables
const updateProductStock = (productId: number, newStock: number) => {
    const p = products.value.find(item => item.id === productId);
    if (p) {
        p.stock = newStock;
        p.status = newStock === 0 ? 'Out of Stock' : newStock <= 5 ? 'Low Stock' : 'In Stock';
        triggerToast(`📦 Stock updated for "${p.name}".`);
    }
};

const shipOrder = (orderId: string) => {
    const o = orders.value.find(item => item.id === orderId);
    if (o) {
        o.status = 'Paid';
        triggerToast(`🚚 Order "${orderId}" marked as shipped and paid.`);
    }
};

const cancelOrder = (orderId: string) => {
    const o = orders.value.find(item => item.id === orderId);
    if (o) {
        o.status = 'Cancelled';
        triggerToast(`❌ Order "${orderId}" cancelled.`);
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <PendingInvitationsModal
        v-if="pendingInvitations && pendingInvitations.length > 0"
        :invitations="pendingInvitations"
    />

    <!-- Outer container following guidelines spacing (space-6 = 24px content area padding) -->
    <div class="space-y-6 pb-12 text-neutral-800 dark:text-neutral-200">
        
        <!-- Header title block (Guidelines Section 3.1) -->
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

        <!-- 4-Column Stat Cards Widgets (Guidelines Section 3.1 FR-1.3) -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Revenue widget -->
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Paid Revenue</span>
                    <DollarSign class="h-4 w-4 text-emerald-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">${{ totalRevenue.toFixed(2) }}</div>
                    <div class="flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                        <TrendingUp class="h-3 w-3" /> +14.2% from last week
                    </div>
                </div>
                <!-- Sparkline SVG -->
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
                    <div class="font-mono text-2xl font-bold tracking-tight">{{ pendingCount }}</div>
                    <div class="text-[10px] font-medium text-neutral-400">Requires processing dispatch</div>
                </div>
                <!-- Sparkbar SVG -->
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
                    <div class="font-mono text-2xl font-bold tracking-tight">{{ activeCouponCount }}</div>
                    <div class="text-[10px] text-neutral-400 flex items-center gap-1">
                        <CheckCircle2 class="h-3 w-3 text-emerald-500" /> {{ coupons.filter(c => c.status === 'active').reduce((sum,c) => sum + c.usedCount, 0) }} total usages
                    </div>
                </div>
                <div class="text-[10px] font-mono text-neutral-400 border-t border-neutral-100 pt-2 dark:border-neutral-800">
                    Active: MINT50 · WELCOME10
                </div>
            </div>

            <!-- Low Stock widget -->
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 flex flex-col justify-between">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Low Stock Alert</span>
                    <AlertTriangle class="h-4 w-4 text-red-500" />
                </div>
                <div class="my-2 space-y-1">
                    <div class="font-mono text-2xl font-bold tracking-tight">{{ lowStockCount }}</div>
                    <div class="flex items-center gap-1 text-[10px] font-bold text-amber-500">
                        Requires inventory replenishment
                    </div>
                </div>
                <!-- Progress visual list -->
                <div class="space-y-1.5">
                    <div class="flex justify-between text-[10px] text-neutral-500">
                        <span>AeroBuds Pro</span>
                        <span class="font-mono font-bold">5 left</span>
                    </div>
                    <div class="w-full bg-neutral-100 dark:bg-neutral-800 h-1 rounded-full">
                        <div class="bg-amber-500 h-1 rounded-full" style="width: 25%"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 1. TAB: ANALYTICS OVERVIEW -->
        <div v-if="activeTab === 'overview'" class="grid gap-6 lg:grid-cols-3">
            <!-- Monthly sales line chart (Guidelines Section 3.1) -->
            <div class="lg:col-span-2 rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 space-y-6">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <h3 class="text-base font-bold tracking-tight flex items-center gap-2">
                        <Activity class="h-4 w-4 text-emerald-500" /> Monthly Revenue Analytics
                    </h3>
                    <span class="text-[10px] font-bold text-neutral-400">FY 2026</span>
                </div>
                
                <!-- Mock SVG Chart -->
                <div class="relative h-60 w-full pt-4">
                    <svg class="h-full w-full overflow-visible" viewBox="0 0 100 50">
                        <!-- Grid lines -->
                        <line x1="0" y1="10" x2="100" y2="10" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                        <line x1="0" y1="20" x2="100" y2="20" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                        <line x1="0" y1="30" x2="100" y2="30" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                        <line x1="0" y1="40" x2="100" y2="40" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                        
                        <!-- Axis Labels -->
                        <text x="0" y="48" font-size="2" fill="#999">Jan</text>
                        <text x="20" y="48" font-size="2" fill="#999">Mar</text>
                        <text x="40" y="48" font-size="2" fill="#999">May</text>
                        <text x="60" y="48" font-size="2" fill="#999">Jul</text>
                        <text x="80" y="48" font-size="2" fill="#999">Sep</text>
                        <text x="96" y="48" font-size="2" fill="#999">Nov</text>
                        
                        <!-- Line Path -->
                        <path d="M0,45 L10,38 L20,41 L30,22 L40,25 L50,15 L60,18 L70,8 L80,12 L90,6 L100,2" fill="none" stroke="rgb(16,185,129)" stroke-width="1" />
                        <!-- Shadow Fill under Line -->
                        <path d="M0,45 L10,38 L20,41 L30,22 L40,25 L50,15 L60,18 L70,8 L80,12 L90,6 L100,2 L100,45 L0,45 Z" fill="rgba(16,185,129,0.08)" />
                    </svg>
                </div>
            </div>

            <!-- Recent Activity log -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                <h3 class="text-base font-bold tracking-tight border-b border-neutral-100 pb-3 dark:border-neutral-800">Recent Activity Logs</h3>
                <div class="space-y-4 max-h-[16.5rem] overflow-y-auto pr-2 text-xs">
                    <div class="flex gap-2.5">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 shrink-0 dark:bg-emerald-950 dark:text-emerald-400">
                            <Check class="h-3 w-3" />
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-semibold">Order #ORD-9831 Paid via Stripe</p>
                            <span class="text-[10px] text-neutral-400">Sarah Jenkins · 2 mins ago</span>
                        </div>
                    </div>
                    <div class="flex gap-2.5">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 shrink-0 dark:bg-emerald-950 dark:text-emerald-400">
                            <Tag class="h-3 w-3" />
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-semibold">New Coupon MINT50 Created</p>
                            <span class="text-[10px] text-neutral-400">Admin Waes · 2 hours ago</span>
                        </div>
                    </div>
                    <div class="flex gap-2.5">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-50 text-amber-600 shrink-0 dark:bg-amber-950 dark:text-amber-400">
                            <AlertTriangle class="h-3 w-3" />
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-semibold">Product stock low: Minimalist Backpack</p>
                            <span class="text-[10px] text-neutral-400">Inventory Monitor · 4 hours ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TAB: PRODUCTS TABLE (Guidelines Section 1.3 / 5.3) -->
        <div v-else-if="activeTab === 'products'" class="space-y-4">
            
            <!-- Controls search & filter bar -->
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
                <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4 text-neutral-400" />
                    <select v-model="filterStatus" class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900">
                        <option value="All">All Categories</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Fashion">Fashion</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Home">Home</option>
                    </select>
                </div>
            </div>

            <!-- Products Data Table (Guidelines Section 5.3) -->
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
                                <th class="p-4 font-semibold text-center w-24">Actions</th>
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
                                <td class="p-4 text-center">
                                    <button 
                                        @click="triggerToast(`✏️ Edit action triggered for product ID ${product.id}`)"
                                        class="h-8 w-8 rounded-lg bg-neutral-100 text-neutral-500 hover:bg-emerald-50 hover:text-emerald-600 flex items-center justify-center transition dark:bg-neutral-800 dark:hover:bg-emerald-950"
                                    >
                                        <Edit class="h-3.5 w-3.5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- 3. TAB: ORDERS TABLE (Guidelines Section 1.3 / 5.3) -->
        <div v-else-if="activeTab === 'orders'" class="space-y-4">
            
            <!-- Controls search & filter bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-100 pb-4 dark:border-neutral-800">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search orders by customer or ID..." 
                        class="h-10 w-full rounded-lg border border-neutral-200 bg-white pl-10 pr-4 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4 text-neutral-400" />
                    <select v-model="filterStatus" class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900">
                        <option value="All">All Statuses</option>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                        <option value="Failed">Failed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Orders Data Table (Guidelines Section 3.3 Status Badge Colors) -->
            <div class="rounded-xl border border-neutral-200 bg-white overflow-hidden shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50 border-b border-neutral-200 text-neutral-500 dark:bg-neutral-800/40 dark:border-neutral-800">
                                <th class="p-4 font-semibold w-24">Order ID</th>
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
                                            v-if="order.status === 'Pending'"
                                            @click="shipOrder(order.id)"
                                            class="h-7 px-2.5 rounded bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition"
                                        >
                                            Ship & Pay
                                        </button>
                                        <button 
                                            v-if="order.status === 'Pending'"
                                            @click="cancelOrder(order.id)"
                                            class="h-7 px-2.5 rounded bg-neutral-100 border hover:bg-red-50 hover:text-red-500 transition dark:bg-neutral-800 dark:border-neutral-700"
                                        >
                                            Cancel
                                        </button>
                                        <span v-else class="text-[10px] text-neutral-400">Audit Completed</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- 4. TAB: COUPONS MANAGEMENT (Guidelines Section 3.5 / 5.3) -->
        <div v-else-if="activeTab === 'coupons'" class="space-y-4">
            
            <!-- Controls search & filter bar + Create Coupon Button -->
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
                
                <div class="flex items-center gap-3">
                    <select v-model="filterStatus" class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900">
                        <option value="All">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>

                    <button 
                        @click="showCreateCouponModal = true"
                        class="h-10 rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 flex items-center gap-1.5 transition"
                    >
                        <Plus class="h-4 w-4" /> Create Coupon
                    </button>
                </div>
            </div>

            <!-- Coupons Data Table -->
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
                                    {{ coupon.usedCount }} / <span class="text-neutral-400">{{ coupon.usageLimit }}</span>
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
                                <td class="p-4 text-center">
                                    <button 
                                        @click="coupons = coupons.filter(c => c.id !== coupon.id); triggerToast('🗑️ Coupon deleted.');"
                                        class="h-8 w-8 rounded-lg bg-neutral-100 text-neutral-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition dark:bg-neutral-800 dark:hover:bg-red-950"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create Coupon Dialog Form (FR-5.3 Form Layout and Validation Guidelines) -->
            <div v-if="showCreateCouponModal" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center">
                <!-- Backdrop -->
                <div @click="showCreateCouponModal = false" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
                
                <!-- Form container -->
                <div class="relative w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                        <h3 class="text-base font-bold tracking-tight">Create Coupon Code</h3>
                        <button @click="showCreateCouponModal = false" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Coupon Form Body (Guidelines Section 5.4 fields height, labels gap, spacing) -->
                    <div class="space-y-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Coupon Code</label>
                            <input 
                                v-model="newCoupon.code" 
                                type="text" 
                                placeholder="e.g. SUMMER50"
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
                                <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Min Order Amount ($)</label>
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

        </div>

        <!-- GLOBAL TOAST FEEDBACK ALERT -->
        <div 
            v-if="toastMessage"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-xl bg-neutral-900 px-4 py-3 text-xs font-bold text-white shadow-xl dark:bg-neutral-100 dark:text-neutral-900"
        >
            <CheckCircle2 class="h-4 w-4 text-emerald-500" />
            <span>{{ toastMessage }}</span>
        </div>

    </div>
</template>
