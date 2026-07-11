<script setup lang="ts">
import { computed, ref, useTemplateRef } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { 
    ShoppingBag, 
    ShoppingCart, 
    Tag, 
    Trash2, 
    Plus, 
    Minus, 
    Check, 
    X, 
    ArrowRight, 
    Search, 
    Sparkles, 
    Star, 
    SlidersHorizontal, 
    CreditCard, 
    Truck, 
    Info, 
    Lock, 
    AlertTriangle, 
    CheckCircle2, 
    Printer, 
    Download, 
    RefreshCw, 
    Sun, 
    Moon, 
    Menu, 
    ArrowLeft, 
    Leaf,
    LayoutGrid,
    Eye,
    Phone,
    Mail,
    Globe,
    ChevronDown,
    Monitor
} from '@lucide/vue';
import { DbProduct, DbCoupon } from '@/types/storefront';
import { useStorefront } from '@/composables/useStorefront';
import Footer from '@/components/Footer.vue';


const props = defineProps<{
    dbProducts?: DbProduct[];
    dbCategories?: string[];
    dbBrands?: string[];
    dbCoupons?: DbCoupon[];
    gateways?: {
        stripe: { enabled: boolean; publishable_key: string; secret_key: string };
        sslcommerz: { enabled: boolean; store_id: string; store_password: string };
        cod: { enabled: boolean };
    };
    announcement?: {
        enabled: boolean;
        text: string;
        coupon: string;
        bg_color: string;
        text_color: string;
    };
}>();

// Page properties
const page = usePage();
const dashboardUrl = computed(() =>
    page.props.currentTeam ? route('dashboard', page.props.currentTeam.slug).url : null
);


// Destructure storefront state & actions from shared composable
const {
    isDarkMode,
    appearance,
    resolvedAppearance,
    updateAppearance,
    searchQuery,
    selectedCategory,
    cartOpen,
    selectedProduct,
    viewMode,
    supportForm,
    supportSubmitted,
    expandedFaq,
    chatMessages,
    chatOptions,
    chatProcessing,
    sendChatMessage,
    submitSupportTicket,
    cart,
    cartSubtotal,
    discountAmount,
    shippingFee,
    cartTotal,
    cartQuantity,
    addToCart,
    updateCartQuantity,
    removeFromCart,
    activeCoupons,
    couponInput,
    appliedCoupon,
    couponError,
    couponSuccess,
    applyCoupon,
    removeCoupon,
    checkoutForm,
    stripeCard,
    orderInvoice,
    proceedToCheckout,
    placeOrder,
    toastMessage,
    categories,
    activeProducts,
    minPrice,
    maxPrice,
    showInStockOnly,
    sortBy,
    filteredProducts,
    featuredProducts,
    bestSellerProducts,
    handlePrint,
    resetStorefront,
    scrollToCollection,
    selectCategory
} = useStorefront(props);

// Language setup
const selectedLang = ref('English');
const langOpen = ref(false);
const langDropdownRef = useTemplateRef('langDropdownRef');
const languages = ['English', 'Spanish', 'French', 'German', 'Bengali'];

onClickOutside(langDropdownRef, () => {
    langOpen.value = false;
});

const formatAnnouncementText = (text?: string, coupon?: string) => {
    if (!text) return '';
    const escapedText = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
        
    const safeCoupon = coupon
        ? coupon
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
        : '';
        
    const badgeHtml = `<span class="rounded bg-white/20 px-1 py-0.5 font-mono text-amber-200 font-bold mx-1">${safeCoupon}</span>`;
    return escapedText.replace('{coupon}', badgeHtml);
};
</script>

<template>
    <Head title="StoreMint Storefront" />
    
    <div :class="{'dark': isDarkMode}" class="min-h-screen bg-neutral-50 text-neutral-800 transition-colors duration-300 dark:bg-neutral-950 dark:text-neutral-200">
        
        <!-- TOP PERSISTENT DEMO BAR (Designed to showcase tokens & guides) -->
        <div class="sticky top-0 z-50 flex flex-wrap items-center justify-between border-b border-emerald-500/20 bg-emerald-900 px-4 py-2 text-xs font-medium text-white shadow-md dark:bg-emerald-950">
            <div class="flex items-center gap-4 text-neutral-200">
                <a href="tel:+18005550199" class="flex items-center gap-1.5 hover:text-white transition">
                    <Phone class="h-3 w-3 text-emerald-400" />
                    <span>+1 (800) 555-0199</span>
                </a>
                <span class="text-emerald-700/60">|</span>
                <a href="mailto:support@storemint.com" class="flex items-center gap-1.5 hover:text-white transition">
                    <Mail class="h-3 w-3 text-emerald-400" />
                    <span>support@storemint.com</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <!-- Language Selector -->
                <div class="relative" ref="langDropdownRef">
                    <button
                        @click="langOpen = !langOpen"
                        class="flex items-center gap-1.5 rounded bg-emerald-800 px-2 py-1 hover:bg-emerald-700 transition cursor-pointer text-[11px] text-white font-medium"
                    >
                        <Globe class="h-3 w-3 text-emerald-300" />
                        <span>{{ selectedLang }}</span>
                        <ChevronDown class="h-2.5 w-2.5 text-emerald-400" />
                    </button>
                    
                    <div
                        v-if="langOpen"
                        class="absolute right-0 mt-1 w-28 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-1 shadow-md z-30"
                    >
                        <button
                            v-for="lang in languages"
                            :key="lang"
                            @click="selectedLang = lang; langOpen = false"
                            class="flex w-full items-center justify-between rounded-md px-2 py-1 text-left text-[11px] text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span>{{ lang }}</span>
                            <Check v-if="selectedLang === lang" class="h-2.5 w-2.5 text-emerald-500" />
                        </button>
                    </div>
                </div>

                <!-- Theme Selector (Segmented Control) -->
                <div class="flex items-center gap-0.5 rounded-lg bg-emerald-950 p-0.5 border border-emerald-500/20 shadow-xs">
                    <button
                        @click="updateAppearance('light')"
                        :class="appearance === 'light' ? 'bg-emerald-800 text-amber-300 shadow-xs' : 'text-emerald-400/80 hover:text-emerald-300'"
                        class="flex h-6 w-6 items-center justify-center rounded-md cursor-pointer transition duration-200"
                        title="Light Mode"
                    >
                        <Sun class="h-3.5 w-3.5" />
                    </button>
                    <button
                        @click="updateAppearance('system')"
                        :class="appearance === 'system' ? 'bg-emerald-800 text-blue-300 shadow-xs' : 'text-emerald-400/80 hover:text-emerald-300'"
                        class="flex h-6 w-6 items-center justify-center rounded-md cursor-pointer transition duration-200"
                        title="System Mode"
                    >
                        <Monitor class="h-3.5 w-3.5" />
                    </button>
                    <button
                        @click="updateAppearance('dark')"
                        :class="appearance === 'dark' ? 'bg-emerald-800 text-emerald-300 shadow-xs' : 'text-emerald-400/80 hover:text-emerald-300'"
                        class="flex h-6 w-6 items-center justify-center rounded-md cursor-pointer transition duration-200"
                        title="Dark Mode"
                    >
                        <Moon class="h-3.5 w-3.5" />
                    </button>
                </div>
                
                <template v-if="$page.props.auth.user">
                    <Link 
                        v-if="dashboardUrl"
                        :href="dashboardUrl"
                        class="flex items-center gap-1 rounded bg-white px-2 py-1 text-[11px] text-emerald-900 hover:bg-neutral-100 transition"
                    >
                        <LayoutGrid class="h-3 w-3" />
                        <span>Go to Admin Dashboard</span>
                    </Link>
                    <span v-else class="text-[11px] text-emerald-100 font-medium">
                        Hello, {{ $page.props.auth.user.first_name }}
                    </span>
                    <Link
                        :href="route('logout').url"
                        method="post"
                        as="button"
                        class="flex items-center gap-1 rounded bg-emerald-800/80 border border-emerald-700/50 px-2 py-1 hover:bg-emerald-800 transition text-[11px] text-white font-semibold"
                    >
                        Log out
                    </Link>
                </template>
                <Link 
                    v-else
                    :href="route('login').url"
                    class="flex items-center gap-1 rounded bg-white px-2 py-1 text-[11px] text-emerald-900 hover:bg-neutral-100 transition"
                >
                    <Lock class="h-3 w-3" />
                    <span>Log In</span>
                </Link>
            </div>
        </div>

        <!-- 36px ANNOUNCEMENT BAR (Guidelines Section 1.5) -->
        <div 
            v-if="props.announcement?.enabled"
            class="flex h-9 items-center justify-center px-4 text-center text-xs font-semibold tracking-wider transition-all duration-300 animate-fade-in"
            :style="{ backgroundColor: props.announcement.bg_color, color: props.announcement.text_color }"
        >
            <span class="flex items-center gap-1.5 justify-center flex-wrap">
                <Sparkles class="h-3.5 w-3.5 shrink-0" />
                <span v-html="formatAnnouncementText(props.announcement.text, props.announcement.coupon)"></span>
            </span>
        </div>

        <!-- 80px HEADER BAR (Guidelines Section 1.5) -->
        <header class="sticky top-10 z-40 h-20 border-b border-neutral-200/80 bg-white/90 backdrop-blur-md dark:border-neutral-800 dark:bg-neutral-900/90">
            <div class="mx-auto flex h-full max-w-[1280px] items-center justify-between px-6">
                <!-- Logo -->
                <div class="flex items-center gap-2 cursor-pointer" @click="resetStorefront">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-400 to-emerald-600 text-white shadow-md shadow-emerald-500/20">
                        <Leaf class="h-5 w-5" />
                    </div>
                    <span class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">
                        Store<span class="text-emerald-500">Mint</span>
                    </span>
                </div>

                <!-- Navigation links -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                    <button 
                        @click="viewMode = 'browse'" 
                        :class="viewMode === 'browse' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'hover:text-emerald-500'" 
                        class="cursor-pointer transition bg-transparent border-none py-1"
                    >
                        Home
                    </button>
                    <Link 
                        href="/shop" 
                        class="hover:text-emerald-500 cursor-pointer transition py-1"
                    >
                        Shop All
                    </Link>
                    <button 
                        @click="viewMode = 'categories'" 
                        :class="viewMode === 'categories' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'hover:text-emerald-500'" 
                        class="cursor-pointer transition bg-transparent border-none py-1"
                    >
                        Categories
                    </button>
                    <button 
                        @click="viewMode = 'support'" 
                        :class="viewMode === 'support' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'hover:text-emerald-500'" 
                        class="cursor-pointer transition bg-transparent border-none py-1"
                    >
                        Support
                    </button>
                    <Link 
                        v-if="$page.props.auth.user"
                        :href="dashboardUrl || '/dashboard'"
                        class="hover:text-emerald-500 cursor-pointer transition py-1"
                    >
                        Dashboard
                    </Link>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-4">
                    <!-- Search Input -->
                    <div class="relative hidden sm:block w-64">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search products..." 
                            class="h-10 w-full rounded-lg border border-neutral-200 bg-neutral-50 pl-10 pr-4 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800 dark:focus:border-emerald-500"
                        />
                    </div>

                    <!-- Cart Toggle -->
                    <button 
                        @click="cartOpen = true" 
                        class="relative flex h-10 items-center gap-2 rounded-lg bg-neutral-100 px-4 text-sm font-semibold hover:bg-neutral-200 transition dark:bg-neutral-800 dark:hover:bg-neutral-700"
                    >
                        <ShoppingCart class="h-4 w-4 text-emerald-500" />
                        <span class="hidden sm:inline">Cart</span>
                        <span 
                            v-if="cartQuantity > 0"
                            class="absolute -top-1.5 -right-1.5 flex h-5 min-w-5 animate-bounce items-center justify-center rounded-full bg-emerald-600 px-1 font-mono text-[10px] text-white"
                        >
                            {{ cartQuantity }}
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- MAIN VIEW WRAPPER -->
        <main class="mx-auto max-w-[1280px] px-6 py-8">
            
            <!-- BROWSE STATE -->
            <div v-if="viewMode === 'browse'" class="space-y-12">
                
                <!-- HERO SECTION (Guidelines Section 2) -->
                <section class="relative overflow-hidden rounded-2xl bg-neutral-900 text-white dark:bg-neutral-900">
                    <!-- Soft background ambient glows -->
                    <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-emerald-600/10 blur-3xl"></div>
                    
                    <div class="relative z-10 px-8 py-16 md:p-20 max-w-2xl space-y-6">
                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3  py-1 text-xs font-semibold text-emerald-400">
                            <Sparkles class="h-3 w-3" />
                            <span>REDESIGNED PLATFORM</span>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight leading-tight">
                            State-of-the-Art <br />
                            <span class="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">E-Commerce Redefined</span>
                        </h1>
                        <p class="text-sm md:text-base text-neutral-300 leading-relaxed max-w-lg">
                            Designed strictly according to the Design Grid & System Guidelines. Experience fluid 12-column layouts, unified spacing scales, and pixel-perfect contrast.
                        </p>
                        <div class="pt-4">
                            <button 
                                @click="scrollToCollection"
                                class="inline-flex h-12 items-center gap-2 rounded-lg bg-emerald-500 px-6 text-sm font-semibold text-neutral-950 hover:bg-emerald-400 transition"
                            >
                                <span>Shop the Collection</span>
                                <ArrowRight class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </section>

                <!-- ACTIVE PROMOS HIGHLIGHT (Guidelines Section 1.5) -->
                <section class="space-y-4">
                    <div class="flex items-center gap-2">
                        <Tag class="h-5 w-5 text-emerald-500" />
                        <h2 class="text-lg font-bold tracking-tight">Active Coupons & Discounts</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div 
                            v-for="coupon in activeCoupons" 
                            :key="coupon.code"
                            class="flex items-start justify-between rounded-xl border border-dashed border-emerald-500/30 bg-emerald-500/5 p-6 hover:bg-emerald-500/10 transition"
                        >
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-emerald-500/20 px-2 py-1 font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ coupon.code }}
                                    </span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ coupon.discountType === 'percentage' ? `${coupon.discountValue}% Off` : `${$page.props.currency_symbol ?? '$'}${coupon.discountValue} Flat Off` }}
                                    </span>
                                </div>
                                <p class="text-xs text-neutral-600 dark:text-neutral-400">{{ coupon.description }}</p>
                            </div>
                            <button 
                                @click="couponInput = coupon.code; applyCoupon()"
                                :disabled="appliedCoupon?.code === coupon.code"
                                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:bg-neutral-200 disabled:text-neutral-500 dark:disabled:bg-neutral-800"
                            >
                                {{ appliedCoupon?.code === coupon.code ? 'Applied' : 'Apply Coupon' }}
                            </button>
                        </div>
                    </div>
                </section>

                    <!-- FEATURED CATEGORIES SECTION -->
                <section class="space-y-6">
                    <div class="flex items-center justify-between border-b border-neutral-200/80 pb-4 dark:border-neutral-800">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight">Featured Collections</h2>
                            <p class="text-xs text-neutral-500">Shop by select categories crafted for your aesthetic</p>
                        </div>
                        <Link 
                            href="/shop?tab=categories"
                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition"
                        >
                            View All Categories &rarr;
                        </Link>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Link 
                            v-for="(cat, index) in categories.filter(c => c !== 'All').slice(0, 3)" 
                            :key="cat"
                            :href="`/shop?category=${cat}`"
                            class="group relative overflow-hidden rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm hover:shadow-md transition duration-300 dark:border-neutral-800 dark:bg-neutral-900 cursor-pointer block"
                        >
                            <!-- Card Gradient backdrop visual -->
                            <div 
                                :class="[
                                    index % 3 === 0 ? 'from-amber-500/10 to-orange-500/10' :
                                    index % 3 === 1 ? 'from-emerald-500/10 to-teal-500/10' :
                                    'from-blue-500/10 to-indigo-500/10'
                                ]"
                                class="absolute inset-0 bg-gradient-to-br opacity-50 group-hover:opacity-100 transition duration-300"
                            ></div>
                            
                            <div class="relative z-10 flex flex-col justify-between h-36">
                                <div class="flex justify-between items-start">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                                        <ShoppingBag v-if="cat === 'Accessories'" class="h-6 w-6" />
                                        <Sparkles v-else-if="cat === 'Electronics'" class="h-6 w-6" />
                                        <Leaf v-else-if="cat === 'Fashion'" class="h-6 w-6" />
                                        <LayoutGrid v-else class="h-6 w-6" />
                                    </div>
                                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full dark:bg-emerald-950 dark:text-emerald-400">
                                        Go to Collection
                                    </span>
                                </div>
                                
                                <div>
                                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white group-hover:text-emerald-500 transition">{{ cat }}</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Explore our premium selection of hand-crafted {{ cat.toLowerCase() }}.</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- FEATURED PRODUCTS SECTION -->
                <section class="space-y-6">
                    <div class="flex items-center justify-between border-b border-neutral-200/80 pb-4 dark:border-neutral-800">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight">Featured Products</h2>
                            <p class="text-xs text-neutral-500">Handpicked premium goods selected for style and value</p>
                        </div>
                        <Link 
                            href="/shop"
                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition"
                        >
                            Explore Shop &rarr;
                        </Link>
                    </div>

                    <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                        <div 
                            v-for="product in featuredProducts" 
                            :key="'featured-' + product.id"
                            class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm hover:shadow-md transition dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <!-- Badge -->
                            <span class="absolute top-3 left-3 z-10 rounded-full bg-emerald-600 text-white px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider">
                                Featured
                            </span>

                            <div 
                                class="relative aspect-square w-full overflow-hidden bg-neutral-100 dark:bg-neutral-800 cursor-pointer"
                                @click="selectedProduct = product"
                            >
                                <img v-if="product.image" :src="product.image" :alt="product.name" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                <div v-else :class="product.imageGradient" class="absolute inset-0 bg-gradient-to-tr opacity-90 transition duration-500 group-hover:scale-105"></div>
                                <div v-if="!product.image" class="absolute inset-0 flex items-center justify-center text-white/10 font-bold text-4xl select-none group-hover:scale-110 transition duration-500">
                                    {{ product.name.split(' ').map(w => w[0]).join('') }}
                                </div>
                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                                    <span class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-neutral-900 shadow-lg flex items-center gap-1.5">
                                        <Eye class="h-3.5 w-3.5" /> Quick View
                                    </span>
                                </div>
                            </div>

                            <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">{{ product.category }}</span>
                                    <h4 class="font-bold text-xs line-clamp-1 text-neutral-900 dark:text-white group-hover:text-emerald-500 transition cursor-pointer h-5" @click="selectedProduct = product">
                                        {{ product.name }}
                                    </h4>
                                </div>

                                <div class="flex items-center gap-1 text-[10px] text-amber-500">
                                    <Star class="h-3 w-3 fill-current" />
                                    <span class="font-bold">{{ product.rating }}</span>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-mono text-sm font-extrabold text-neutral-900 dark:text-white">{{ $page.props.currency_symbol ?? '$' }}{{ product.price.toFixed(2) }}</span>
                                    <button 
                                        @click="addToCart(product)"
                                        :disabled="product.stock === 0"
                                        class="rounded-lg bg-neutral-950 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600 disabled:bg-neutral-200 disabled:text-neutral-500 transition dark:bg-neutral-800 dark:hover:bg-emerald-600"
                                    >
                                        Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- BEST SELLERS SECTION -->
                <section class="space-y-6">
                    <div class="flex items-center justify-between border-b border-neutral-200/80 pb-4 dark:border-neutral-800">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight">Best Sellers</h2>
                            <p class="text-xs text-neutral-500">Our customer favorites and top-ranking essentials</p>
                        </div>
                        <Link 
                            href="/shop?sortBy=best-seller"
                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition"
                        >
                            View Best Sellers &rarr;
                        </Link>
                    </div>

                    <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                        <div 
                            v-for="product in bestSellerProducts" 
                            :key="'best-' + product.id"
                            class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm hover:shadow-md transition dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <!-- Badge -->
                            <span class="absolute top-3 left-3 z-10 rounded-full bg-indigo-600 text-white px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider">
                                Best Seller
                            </span>

                            <div 
                                class="relative aspect-square w-full overflow-hidden bg-neutral-100 dark:bg-neutral-800 cursor-pointer"
                                @click="selectedProduct = product"
                            >
                                <img v-if="product.image" :src="product.image" :alt="product.name" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                <div v-else :class="product.imageGradient" class="absolute inset-0 bg-gradient-to-tr opacity-90 transition duration-500 group-hover:scale-105"></div>
                                <div v-if="!product.image" class="absolute inset-0 flex items-center justify-center text-white/10 font-bold text-4xl select-none group-hover:scale-110 transition duration-500">
                                    {{ product.name.split(' ').map(w => w[0]).join('') }}
                                </div>
                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                                    <span class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-neutral-900 shadow-lg flex items-center gap-1.5">
                                        <Eye class="h-3.5 w-3.5" /> Quick View
                                    </span>
                                </div>
                            </div>

                            <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">{{ product.category }}</span>
                                    <h4 class="font-bold text-xs line-clamp-1 text-neutral-900 dark:text-white group-hover:text-emerald-500 transition cursor-pointer h-5" @click="selectedProduct = product">
                                        {{ product.name }}
                                    </h4>
                                </div>

                                <div class="flex items-center gap-1 text-[10px] text-amber-500">
                                    <Star class="h-3 w-3 fill-current" />
                                    <span class="font-bold">{{ product.rating }}</span>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-mono text-sm font-extrabold text-neutral-900 dark:text-white">{{ $page.props.currency_symbol ?? '$' }}{{ product.price.toFixed(2) }}</span>
                                    <button 
                                        @click="addToCart(product)"
                                        :disabled="product.stock === 0"
                                        class="rounded-lg bg-neutral-950 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600 disabled:bg-neutral-200 disabled:text-neutral-500 transition dark:bg-neutral-800 dark:hover:bg-emerald-600"
                                    >
                                        Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- BOTTOM CTA CATALOG PROMOTION -->
                <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-800 text-white shadow-lg p-8 md:p-12 text-center space-y-6">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.15),transparent)]"></div>
                    <div class="relative z-10 max-w-xl mx-auto space-y-4">
                        <h2 class="text-3xl font-extrabold tracking-tight">Explore the Complete StoreMint Collection</h2>
                        <p class="text-xs md:text-sm text-neutral-100">Browse through hundreds of modern lifestyle goods. Filter by price, category, availability, and sort them dynamically to match your lifestyle.</p>
                        <div class="pt-2">
                            <Link 
                                href="/shop"
                                class="inline-flex h-11 items-center justify-center rounded-lg bg-white px-8 text-xs font-semibold text-emerald-900 hover:bg-neutral-100 transition shadow-md"
                            >
                                Go to Shop Catalog
                            </Link>
                        </div>
                    </div>
                </section>
            </div>

            <!-- CATEGORIES STATE -->
            <div v-else-if="viewMode === 'categories'" class="space-y-10">
                <div class="text-center max-w-xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <Leaf class="h-3.5 w-3.5" /> Curated Collections
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Browse by Category</h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Explore premium goods tailored to your lifestyle and space. Filter and find what you need.</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 pt-6">
                    <div 
                        v-for="(cat, index) in categories.filter(c => c !== 'All')" 
                        :key="cat"
                        @click="selectCategory(cat)"
                        class="group relative overflow-hidden rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm hover:shadow-md transition duration-300 dark:border-neutral-800 dark:bg-neutral-900 cursor-pointer"
                    >
                        <!-- Card Gradient backdrop visual -->
                        <div 
                            :class="[
                                index % 5 === 0 ? 'from-amber-500/10 to-orange-500/10' :
                                index % 5 === 1 ? 'from-emerald-500/10 to-teal-500/10' :
                                index % 5 === 2 ? 'from-blue-500/10 to-indigo-500/10' :
                                index % 5 === 3 ? 'from-pink-500/10 to-rose-500/10' :
                                'from-purple-500/10 to-fuchsia-500/10'
                            ]"
                            class="absolute inset-0 bg-gradient-to-br opacity-50 group-hover:opacity-100 transition duration-300"
                        ></div>
                        
                        <div class="relative z-10 flex flex-col justify-between h-40">
                            <div class="flex justify-between items-start">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                                    <ShoppingBag v-if="cat === 'Accessories'" class="h-6 w-6" />
                                    <Sparkles v-else-if="cat === 'Electronics'" class="h-6 w-6" />
                                    <Leaf v-else-if="cat === 'Fashion'" class="h-6 w-6" />
                                    <LayoutGrid v-else class="h-6 w-6" />
                                </div>
                                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full dark:bg-emerald-950 dark:text-emerald-400">
                                    {{ activeProducts.filter(p => p.category === cat).length }} Items
                                </span>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-bold text-neutral-900 dark:text-white group-hover:text-emerald-500 transition">{{ cat }}</h3>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Explore our premium selection of {{ cat.toLowerCase() }} designs.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW ARRIVALS STATE -->
            <div v-else-if="viewMode === 'new-arrivals'" class="space-y-10">
                <div class="text-center max-w-xl mx-auto space-y-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-600 dark:text-amber-400">
                        <Sparkles class="h-3.5 w-3.5" /> Just Released
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">New Arrivals</h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">The latest additions to the StoreMint lineup. Be the first to grab these fresh premium products.</p>
                </div>

                <!-- Featured New Arrival Spotlight Card -->
                <div class="relative overflow-hidden rounded-2xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 shadow-sm p-6 md:p-8 flex flex-col md:flex-row gap-8 items-center">
                    <div class="absolute top-0 right-0 h-40 w-40 rounded-full bg-emerald-500/10 blur-3xl"></div>
                    <div class="w-full md:w-1/2 aspect-square md:aspect-[4/3] rounded-xl overflow-hidden bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center relative">
                        <div class="absolute inset-0 bg-gradient-to-tr from-emerald-600 to-indigo-900 opacity-90"></div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-white p-6 text-center space-y-2">
                            <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">Spotlight</span>
                            <h2 class="text-2xl font-bold tracking-tight">Quantum Chronograph</h2>
                            <p class="text-xs text-neutral-200">Our flagship premium watch model</p>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-1/2 space-y-6">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider">Hot Pick</span>
                                <span class="text-xs text-neutral-400">Released this week</span>
                            </div>
                            <h3 class="text-2xl font-extrabold">Quantum Chronograph Watch</h3>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Precision timekeeper with mechanical elegance, featuring a surgical-grade stainless steel casing, genuine leather straps, and scratch-resistant sapphire crystal cover.</p>
                        </div>

                        <div class="flex items-baseline gap-2 font-mono">
                            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $page.props.currency_symbol ?? '$' }}299.00</span>
                            <span class="text-sm text-neutral-400 line-through">{{ $page.props.currency_symbol ?? '$' }}399.00</span>
                        </div>

                        <div>
                            <button 
                                @click="selectedProduct = activeProducts[0]"
                                class="inline-flex h-11 items-center gap-2 rounded-lg bg-emerald-600 px-6 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                            >
                                <Eye class="h-4 w-4" /> View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid of new arrival products -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold tracking-tight">Recent Releases</h3>
                    <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                        <div 
                            v-for="product in activeProducts.slice(0, 4)" 
                            :key="'new-' + product.id"
                            class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm hover:shadow-md transition dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <span class="absolute top-3 left-3 z-10 rounded-full bg-emerald-600 text-white px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider">New</span>
                            
                            <div class="relative aspect-square w-full overflow-hidden bg-neutral-100 dark:bg-neutral-800 cursor-pointer" @click="selectedProduct = product">
                                <img v-if="product.image" :src="product.image" :alt="product.name" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                <div v-else :class="product.imageGradient" class="absolute inset-0 bg-gradient-to-tr opacity-90 transition duration-500 group-hover:scale-105"></div>
                                <div v-if="!product.image" class="absolute inset-0 flex items-center justify-center text-white/10 font-bold text-4xl select-none group-hover:scale-110 transition duration-500">
                                    {{ product.name.split(' ').map(w => w[0]).join('') }}
                                </div>
                            </div>

                            <div class="p-4 space-y-3 flex-1 flex flex-col justify-between bg-white dark:bg-neutral-900">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold text-neutral-400 uppercase">{{ product.category }}</span>
                                    <h4 class="font-bold text-xs line-clamp-1 text-neutral-900 dark:text-white group-hover:text-emerald-500 transition cursor-pointer" @click="selectedProduct = product">
                                        {{ product.name }}
                                    </h4>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-mono text-sm font-extrabold text-neutral-900 dark:text-white">{{ $page.props.currency_symbol ?? '$' }}{{ product.price.toFixed(2) }}</span>
                                    <button 
                                        @click="selectedProduct = product"
                                        class="rounded-lg bg-neutral-100 p-2 text-neutral-600 hover:bg-emerald-50 hover:text-white transition dark:bg-neutral-800 dark:text-neutral-400"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUPPORT / HELP CENTER STATE -->
            <div v-else-if="viewMode === 'support'" class="grid gap-8 lg:grid-cols-12 items-start">
                
                <!-- Support Info & FAQ (7 Cols) -->
                <div class="lg:col-span-7 space-y-8">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <Info class="h-3.5 w-3.5" /> StoreMint Help Desk
                        </span>
                        <h1 class="text-3xl font-extrabold tracking-tight">How can we help?</h1>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Browse frequently asked questions, create a support ticket, or chat with our automated support agent.</p>
                    </div>

                    <!-- FAQ Section -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold tracking-tight border-b border-neutral-100 pb-2 dark:border-neutral-800">Frequently Asked Questions</h3>
                        
                        <div class="space-y-2">
                            <div 
                                v-for="(faq, i) in [
                                    { q: 'How long does shipping take?', a: 'Standard shipping takes 3-5 business days. Express shipping options take 1-2 business days. Tracking details will be emailed to you immediately after shipment.' },
                                    { q: 'What is your refund/return policy?', a: 'We offer a 30-day hassle-free return policy. If you are not satisfied with your purchase, please email returns@storemint.com with your receipt to start the process.' },
                                    { q: 'Do you offer international delivery?', a: 'Yes! We deliver worldwide. Shipping rates and delivery timeframes vary by country and are calculated at checkout.' },
                                    { q: 'How can I update my billing information?', a: 'You can update your personal billing information from the profile settings within the Admin Dashboard.' }
                                ]" 
                                :key="i"
                                class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 overflow-hidden"
                            >
                                <button 
                                    @click="expandedFaq = expandedFaq === i ? null : i"
                                    class="w-full flex items-center justify-between px-5 py-4 text-left text-xs font-bold select-none text-neutral-800 dark:text-neutral-200 hover:text-emerald-500 transition"
                                >
                                    <span>{{ faq.q }}</span>
                                    <Plus v-if="expandedFaq !== i" class="h-4 w-4 text-neutral-400" />
                                    <Minus v-else class="h-4 w-4 text-emerald-500" />
                                </button>
                                
                                <div 
                                    v-show="expandedFaq === i"
                                    class="px-5 pb-4 text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed border-t border-neutral-50 dark:border-neutral-800/50 pt-2"
                                >
                                    {{ faq.a }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create Ticket Form -->
                    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                        <h3 class="text-sm font-bold tracking-tight">Create a Support Ticket</h3>
                        
                        <div v-if="supportSubmitted" class="rounded-lg bg-emerald-50/50 p-4 border border-emerald-100 text-xs text-emerald-600 dark:bg-emerald-950/20 dark:border-emerald-900 dark:text-emerald-400 flex items-start gap-2">
                            <CheckCircle2 class="h-4 w-4 shrink-0 mt-0.5" />
                            <div>
                                <span class="font-bold">Ticket Submitted!</span> Thank you. Our support engineers will review your request and get back to you within 2 hours.
                            </div>
                        </div>

                        <form v-else @submit.prevent="submitSupportTicket" class="space-y-3">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-neutral-500">Your Name *</label>
                                    <input 
                                        v-model="supportForm.name" 
                                        type="text" 
                                        required 
                                        placeholder="e.g. John Doe"
                                        class="h-9 rounded-lg border border-neutral-200 px-3 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                    />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-neutral-500">Your Email *</label>
                                    <input 
                                        v-model="supportForm.email" 
                                        type="email" 
                                        required 
                                        placeholder="john@example.com"
                                        class="h-9 rounded-lg border border-neutral-200 px-3 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                    />
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-neutral-500">Subject *</label>
                                <input 
                                    v-model="supportForm.subject" 
                                    type="text" 
                                    required 
                                    placeholder="Brief summary of issue"
                                    class="h-9 rounded-lg border border-neutral-200 px-3 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-neutral-500">Description of Issue *</label>
                                <textarea 
                                    v-model="supportForm.message" 
                                    rows="4" 
                                    required 
                                    placeholder="Detail your inquiry..."
                                    class="rounded-lg border border-neutral-200 p-3 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                ></textarea>
                            </div>

                            <button 
                                type="submit"
                                class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-5 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                            >
                                Submit Ticket
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Simulated Live Chat Widget (5 Cols) -->
                <div class="lg:col-span-5 rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900 overflow-hidden">
                    <div class="bg-emerald-600 p-4 text-white flex items-center gap-2">
                        <div class="relative">
                            <span class="block h-2.5 w-2.5 rounded-full bg-emerald-300 animate-pulse"></span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold">Minty Live Assistant</h4>
                            <span class="text-[10px] opacity-75">Typically replies instantly</span>
                        </div>
                    </div>

                    <div class="h-80 overflow-y-auto p-4 space-y-3 bg-neutral-50 dark:bg-neutral-950/20">
                        <div 
                            v-for="(msg, index) in chatMessages" 
                            :key="index"
                            :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'"
                            class="flex"
                        >
                            <div 
                                :class="msg.sender === 'user' ? 'bg-emerald-600 text-white rounded-br-none' : 'bg-white dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800 text-neutral-800 dark:text-neutral-200 rounded-bl-none'"
                                class="max-w-[85%] rounded-2xl px-4 py-2.5 text-xs shadow-xs"
                            >
                                {{ msg.text }}
                            </div>
                        </div>
                        
                        <div v-if="chatProcessing" class="flex justify-start">
                            <div class="bg-white dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800 rounded-2xl rounded-bl-none px-4 py-2 text-xs flex gap-1 items-center">
                                <span class="h-1.5 w-1.5 rounded-full bg-neutral-400 animate-bounce" style="animation-delay: 0ms"></span>
                                <span class="h-1.5 w-1.5 rounded-full bg-neutral-400 animate-bounce" style="animation-delay: 150ms"></span>
                                <span class="h-1.5 w-1.5 rounded-full bg-neutral-400 animate-bounce" style="animation-delay: 300ms"></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-neutral-100 dark:border-neutral-800 bg-white dark:bg-neutral-900 space-y-2">
                        <p class="text-[10px] font-semibold text-neutral-400 uppercase">Quick Actions:</p>
                        <div class="flex flex-col gap-1.5">
                            <button 
                                v-for="option in chatOptions" 
                                :key="option"
                                @click="sendChatMessage(option)"
                                :disabled="chatProcessing"
                                class="text-left w-full rounded-lg border border-neutral-200 px-3 py-2 text-xs font-semibold hover:bg-emerald-50 hover:border-emerald-200 transition dark:border-neutral-800 dark:hover:bg-neutral-950 text-neutral-700 dark:text-neutral-300"
                            >
                                {{ option }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CHECKOUT VIEW STATE (Guidelines Section 1.3 / 3.2) -->
            <div v-else-if="viewMode === 'checkout'" class="space-y-6">
                <!-- Header / Back link -->
                <div class="flex items-center gap-2">
                    <button @click="viewMode = 'browse'" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-500 hover:text-neutral-800 dark:hover:text-white">
                        <ArrowLeft class="h-4 w-4" /> Back to store
                    </button>
                </div>

                <div class="grid gap-8 lg:grid-cols-12">
                    
                    <!-- Checkout Form Fields (Left Pane - 7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                            <h2 class="text-lg font-bold tracking-tight mb-4 flex items-center gap-2">
                                <Truck class="h-5 w-5 text-emerald-500" /> Shipping Information
                            </h2>

                            <!-- Forms following Section 5.4 layout rules -->
                            <div class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="flex flex-col gap-2">
                                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Full Name</label>
                                        <input 
                                            v-model="checkoutForm.name" 
                                            type="text" 
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Email Address</label>
                                        <input 
                                            v-model="checkoutForm.email" 
                                            type="email" 
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Delivery Address</label>
                                    <input 
                                        v-model="checkoutForm.address" 
                                        type="text" 
                                        class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                    />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div class="flex flex-col gap-2">
                                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">City</label>
                                        <input 
                                            v-model="checkoutForm.city" 
                                            type="text" 
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Postal Code</label>
                                        <input 
                                            v-model="checkoutForm.zip" 
                                            type="text" 
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Phone Number</label>
                                        <input 
                                            v-model="checkoutForm.phone" 
                                            type="text" 
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods Selection (Section 3.3 Gateway expanded) -->
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                            <h2 class="text-lg font-bold tracking-tight mb-4 flex items-center gap-2">
                                <CreditCard class="h-5 w-5 text-emerald-500" /> Payment Method Selection
                            </h2>

                            <div class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <!-- Stripe -->
                                    <label 
                                        v-if="!gateways || gateways.stripe?.enabled"
                                        :class="checkoutForm.paymentMethod === 'stripe' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500' : 'border-neutral-200'"
                                        class="flex flex-col items-center justify-center p-4 rounded-xl border-2 cursor-pointer hover:border-emerald-500 transition text-center space-y-2 dark:border-neutral-800"
                                    >
                                        <input type="radio" value="stripe" v-model="checkoutForm.paymentMethod" class="sr-only" />
                                        <CreditCard class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                                        <span class="text-xs font-bold">Stripe</span>
                                        <span class="text-[10px] text-neutral-400">International / Credit Cards</span>
                                    </label>
                                    
                                    <!-- SSLCommerz -->
                                    <label 
                                        v-if="!gateways || gateways.sslcommerz?.enabled"
                                        :class="checkoutForm.paymentMethod === 'sslcommerz' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500' : 'border-neutral-200'"
                                        class="flex flex-col items-center justify-center p-4 rounded-xl border-2 cursor-pointer hover:border-emerald-500 transition text-center space-y-2 dark:border-neutral-800"
                                    >
                                        <input type="radio" value="sslcommerz" v-model="checkoutForm.paymentMethod" class="sr-only" />
                                        <ShoppingBag class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                                        <span class="text-xs font-bold">SSLCommerz</span>
                                        <span class="text-[10px] text-neutral-400">Local Cards & Mobile Banking</span>
                                    </label>
 
                                    <!-- COD -->
                                    <label 
                                        v-if="!gateways || gateways.cod?.enabled"
                                        :class="checkoutForm.paymentMethod === 'cod' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500' : 'border-neutral-200'"
                                        class="flex flex-col items-center justify-center p-4 rounded-xl border-2 cursor-pointer hover:border-emerald-500 transition text-center space-y-2 dark:border-neutral-800"
                                    >
                                        <input type="radio" value="cod" v-model="checkoutForm.paymentMethod" class="sr-only" />
                                        <Truck class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                                        <span class="text-xs font-bold">Cash on Delivery</span>
                                        <span class="text-[10px] text-neutral-400">Pay when products arrive</span>
                                    </label>
                                </div>

                                <!-- Stripe Fields details (FR-3) -->
                                <div v-if="checkoutForm.paymentMethod === 'stripe'" class="mt-4 border-t border-neutral-100 pt-4 dark:border-neutral-800 space-y-4">
                                    <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-800/50 space-y-3">
                                        <span class="text-[10px] font-bold tracking-widest text-neutral-400 uppercase flex items-center gap-1">
                                            <Lock class="h-3 w-3" /> Secure Stripe Element
                                        </span>
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <div class="sm:col-span-2 flex flex-col gap-1.5">
                                                <label class="text-[10px] font-semibold text-neutral-600 dark:text-neutral-400">Card Number</label>
                                                <input 
                                                    v-model="stripeCard.number" 
                                                    type="text" 
                                                    class="h-9 rounded border border-neutral-200 bg-white px-2.5 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                                                />
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <label class="text-[10px] font-semibold text-neutral-600 dark:text-neutral-400">Expiry (MM/YY)</label>
                                                <input 
                                                    v-model="stripeCard.expiry" 
                                                    type="text" 
                                                    placeholder="MM/YY"
                                                    class="h-9 rounded border border-neutral-200 bg-white px-2.5 text-xs text-center outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                                                />
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-neutral-500">Stripe test keys will process this order simulation.</p>
                                    </div>
                                </div>

                                <!-- SSLCommerz detail warning -->
                                <div v-if="checkoutForm.paymentMethod === 'sslcommerz'" class="mt-4 rounded-lg bg-blue-50/50 p-4 border border-blue-100 dark:bg-blue-950/20 dark:border-blue-900 text-xs text-blue-600 dark:text-blue-400 flex items-start gap-2">
                                    <Info class="h-4 w-4 shrink-0 mt-0.5" />
                                    <div>
                                        <span class="font-bold">SSLCommerz Sandbox:</span> You will be redirected to the secure local payment portal simulating Visa/Mastercard/bKash checkouts.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Side Panel (Right Pane - 5 Cols) -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900 space-y-6">
                            <h2 class="text-base font-bold tracking-tight border-b border-neutral-100 pb-3 dark:border-neutral-800">Order Summary</h2>

                            <!-- Line items list -->
                            <div class="space-y-4 max-h-60 overflow-y-auto pr-2">
                                <div v-for="item in cart" :key="item.product.id" class="flex items-center gap-3">
                                    <div :class="item.product.imageGradient" class="h-12 w-12 rounded-lg bg-gradient-to-tr opacity-95 shrink-0 flex items-center justify-center text-white text-xs font-bold">
                                        {{ item.product.name.split(' ').map(w => w[0]).join('') }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold truncate">{{ item.product.name }}</h4>
                                        <span class="text-[10px] text-neutral-500 font-mono">{{ item.quantity }} × {{ $page.props.currency_symbol ?? '$' }}{{ item.product.price.toFixed(2) }}</span>
                                    </div>
                                    <span class="text-xs font-bold font-mono ml-auto">{{ $page.props.currency_symbol ?? '$' }}{{ (item.product.price * item.quantity).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Totals Calculations (Section 3.2 rules) -->
                            <div class="border-t border-neutral-100 pt-4 dark:border-neutral-800 space-y-2 text-xs">
                                <div class="flex justify-between text-neutral-600 dark:text-neutral-400">
                                    <span>Subtotal</span>
                                    <span class="font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ cartSubtotal.toFixed(2) }}</span>
                                </div>
                                
                                <div v-if="appliedCoupon" class="flex justify-between text-emerald-600 dark:text-emerald-400">
                                    <span>Discount (Coupon: {{ appliedCoupon.code }})</span>
                                    <span class="font-mono">- {{ $page.props.currency_symbol ?? '$' }}{{ discountAmount.toFixed(2) }}</span>
                                </div>

                                <div class="flex justify-between text-neutral-600 dark:text-neutral-400">
                                    <span>Shipping</span>
                                    <span class="font-mono">{{ shippingFee === 0 ? 'Free' : `${$page.props.currency_symbol ?? '$'}${shippingFee.toFixed(2)}` }}</span>
                                </div>

                                <div class="flex justify-between border-t border-neutral-100 pt-3 text-sm font-bold text-neutral-900 dark:border-neutral-800 dark:text-white">
                                    <span>Grand Total</span>
                                    <span class="font-mono text-base">{{ $page.props.currency_symbol ?? '$' }}{{ cartTotal.toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- CTA Buttons -->
                            <div class="pt-2">
                                <button 
                                    @click="placeOrder"
                                    :disabled="stripeCard.isProcessing"
                                    class="w-full flex h-12 items-center justify-center gap-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700 disabled:bg-neutral-200 disabled:text-neutral-500 transition"
                                >
                                    <RefreshCw v-if="stripeCard.isProcessing" class="h-4 w-4 animate-spin" />
                                    <span v-else>Confirm & Place Order</span>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ORDER CONFIRMATION & REDESIGNED INVOICE (Guidelines Section 3.4 / 3.5) -->
            <div v-else-if="viewMode === 'confirmation' && orderInvoice" class="space-y-6 max-w-2xl mx-auto">
                <div class="flex flex-col items-center justify-center text-center space-y-3 py-6">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                        <CheckCircle2 class="h-8 w-8" />
                    </div>
                    <h1 class="text-2xl font-extrabold tracking-tight">Order Placed Successfully!</h1>
                    <p class="text-xs text-neutral-500 max-w-xs">Your payment has been cleared and your invoice was generated automatically.</p>
                    
                    <div class="flex gap-3 pt-2">
                        <button 
                            @click="handlePrint"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-4 text-xs font-semibold hover:bg-neutral-50 transition dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <Download class="h-3.5 w-3.5" /> Download PDF Invoice
                        </button>
                        <button 
                            @click="resetStorefront"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                        >
                            Back to Shop
                        </button>
                    </div>
                </div>

                <!-- REDESIGNED HIGH FIDELITY INVOICE BLOCK (Guidelines Section 3.4) -->
                <div class="rounded-xl border border-neutral-200 bg-white p-8 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 space-y-6">
                    
                    <!-- Invoice Header (FR-4.1) -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 border-b border-neutral-100 pb-6 dark:border-neutral-800">
                        <div class="space-y-2">
                            <!-- Brand -->
                            <div class="flex items-center gap-1.5">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-xs">
                                    <Leaf class="h-4 w-4" />
                                </div>
                                <span class="text-md font-bold text-neutral-900 dark:text-white">StoreMint Inc.</span>
                            </div>
                            <p class="text-[10px] text-neutral-500 leading-tight">
                                45 Design Grid Plaza, Level 6<br />
                                Gulshan-2, Dhaka 1212<br />
                                support@storemint.com
                            </p>
                        </div>
                        
                        <!-- Metadata block (FR-4.2) -->
                        <div class="sm:text-right space-y-1">
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                                {{ orderInvoice.paymentStatus }}
                            </span>
                            <h3 class="text-sm font-bold font-mono">{{ orderInvoice.invoiceNo }}</h3>
                            <div class="text-[10px] text-neutral-500 space-y-0.5">
                                <div>Order Number: <span class="font-mono">{{ orderInvoice.orderNo }}</span></div>
                                <div>Date: {{ orderInvoice.date }}</div>
                                <div>Payment: {{ orderInvoice.paymentMethod }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer & Shipping details block (FR-4.3) -->
                    <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-800/40 grid gap-4 sm:grid-cols-2 text-xs">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Billed To:</span>
                            <div class="font-bold text-neutral-900 dark:text-white">{{ orderInvoice.customer.name }}</div>
                            <div class="text-neutral-500">{{ orderInvoice.customer.email }}</div>
                            <div class="text-neutral-500">{{ orderInvoice.customer.phone }}</div>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400">Shipment Details:</span>
                            <div class="text-neutral-700 dark:text-neutral-300">{{ orderInvoice.customer.address }}</div>
                            <div class="text-neutral-500">{{ orderInvoice.customer.city }} - {{ orderInvoice.customer.zip }}</div>
                        </div>
                    </div>

                    <!-- Itemized table (FR-4.4) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-100 text-neutral-400 dark:border-neutral-800">
                                    <th class="py-2.5 font-semibold">Product Description</th>
                                    <th class="py-2.5 font-semibold text-center w-20">Unit Price</th>
                                    <th class="py-2.5 font-semibold text-center w-16">Qty</th>
                                    <th class="py-2.5 font-semibold text-right w-24">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800/50">
                                <tr v-for="item in orderInvoice.items" :key="item.name" class="text-neutral-800 dark:text-neutral-300">
                                    <td class="py-3 font-semibold">{{ item.name }}</td>
                                    <td class="py-3 text-center font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ item.price.toFixed(2) }}</td>
                                    <td class="py-3 text-center font-mono">{{ item.quantity }}</td>
                                    <td class="py-3 text-right font-bold font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ item.total.toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals breakdown (FR-4.5) -->
                    <div class="border-t border-neutral-100 pt-4 dark:border-neutral-800 flex justify-end">
                        <div class="w-64 space-y-2 text-xs">
                            <div class="flex justify-between text-neutral-500">
                                <span>Subtotal:</span>
                                <span class="font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ orderInvoice.subtotal.toFixed(2) }}</span>
                            </div>
                            
                            <div v-if="orderInvoice.discount > 0" class="flex justify-between text-emerald-600">
                                <span>Discount ({{ orderInvoice.couponCode }}):</span>
                                <span class="font-mono">- {{ $page.props.currency_symbol ?? '$' }}{{ orderInvoice.discount.toFixed(2) }}</span>
                            </div>

                            <div class="flex justify-between text-neutral-500">
                                <span>Shipping Fee:</span>
                                <span class="font-mono">{{ orderInvoice.shipping === 0 ? 'Free' : `${$page.props.currency_symbol ?? '$'}${orderInvoice.shipping.toFixed(2)}` }}</span>
                            </div>

                            <div class="flex justify-between border-t border-neutral-100 pt-2 text-sm font-bold text-neutral-900 dark:border-neutral-800 dark:text-white">
                                <span>Grand Total:</span>
                                <span class="font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ orderInvoice.grandTotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer (FR-4.7) -->
                    <div class="border-t border-neutral-100 pt-4 dark:border-neutral-800 text-center text-[10px] text-neutral-400">
                        Thank you for shopping at StoreMint. This invoice is generated dynamically upon order payment approval. For support, mail support@storemint.com.
                    </div>
                </div>
            </div>

        </main>

        <!-- Dynamic Storefront Footer -->
        <Footer v-model:viewMode="viewMode" />

        <!-- CART SIDE OVER DRAWER (Guidelines Section 1.5 / 2.4) -->
        <div v-if="cartOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <!-- Backdrop -->
                <div @click="cartOpen = false" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs transition-opacity"></div>

                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col bg-white shadow-2xl dark:bg-neutral-900">
                            
                            <!-- Drawer Header -->
                            <div class="flex items-center justify-between px-6 py-5 border-b border-neutral-100 dark:border-neutral-800">
                                <h2 class="text-sm font-bold tracking-tight flex items-center gap-1.5">
                                    <ShoppingCart class="h-4 w-4 text-emerald-500" /> Shopping Cart ({{ cartQuantity }})
                                </h2>
                                <button @click="cartOpen = false" class="rounded-lg p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white transition">
                                    <X class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Cart Items list -->
                            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                                <div v-if="cart.length === 0" class="flex flex-col items-center justify-center py-20 text-center space-y-3">
                                    <div class="rounded-full bg-neutral-50 p-4 dark:bg-neutral-800">
                                        <ShoppingBag class="h-8 w-8 text-neutral-300" />
                                    </div>
                                    <h3 class="font-bold">Your cart is empty</h3>
                                    <p class="text-xs text-neutral-400 max-w-xs">Looks like you haven't added any products to your cart yet.</p>
                                </div>

                                <div 
                                    v-else 
                                    v-for="item in cart" 
                                    :key="item.product.id"
                                    class="flex items-start gap-4 rounded-xl border border-neutral-100 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-800/20"
                                >
                                    <!-- Image representation -->
                                    <div :class="item.product.imageGradient" class="h-16 w-16 rounded-lg bg-gradient-to-tr shrink-0 flex items-center justify-center text-white text-md font-bold shadow-xs">
                                        {{ item.product.name.split(' ').map(w => w[0]).join('') }}
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <div class="space-y-0.5">
                                            <h4 class="text-xs font-bold truncate pr-6">{{ item.product.name }}</h4>
                                            <span class="text-[10px] text-neutral-400 font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ item.product.price.toFixed(2) }} each</span>
                                        </div>

                                        <!-- Quantity Actions -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900">
                                                <button 
                                                    @click="updateCartQuantity(item.product.id, -1)"
                                                    class="h-7 w-7 flex items-center justify-center text-neutral-500 hover:text-neutral-800 dark:hover:text-white"
                                                >
                                                    <Minus class="h-3 w-3" />
                                                </button>
                                                <span class="w-8 text-center text-xs font-semibold font-mono">{{ item.quantity }}</span>
                                                <button 
                                                    @click="updateCartQuantity(item.product.id, 1)"
                                                    class="h-7 w-7 flex items-center justify-center text-neutral-500 hover:text-neutral-800 dark:hover:text-white"
                                                >
                                                    <Plus class="h-3 w-3" />
                                                </button>
                                            </div>

                                            <span class="text-xs font-bold font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ (item.product.price * item.quantity).toFixed(2) }}</span>
                                        </div>
                                    </div>

                                    <!-- Remove Action -->
                                    <button 
                                        @click="removeFromCart(item.product.id)"
                                        class="text-neutral-400 hover:text-red-500 transition shrink-0 ml-auto"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Cart Summary / Coupon application (Guidelines Section 3.5 storefront) -->
                            <div class="border-t border-neutral-100 p-6 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-800/10 space-y-4">
                                
                                <!-- Coupon input group -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-neutral-500 uppercase">Apply Promo Coupon</label>
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <input 
                                                v-model="couponInput"
                                                type="text" 
                                                placeholder="e.g. MINT50"
                                                :disabled="!!appliedCoupon"
                                                class="h-10 w-full rounded-lg border border-neutral-200 px-3 text-xs outline-none bg-white focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                                            />
                                            <Tag class="absolute right-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-neutral-400" />
                                        </div>
                                        <button 
                                            v-if="!appliedCoupon"
                                            @click="applyCoupon"
                                            class="rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                                        >
                                            Apply
                                        </button>
                                        <button 
                                            v-else
                                            @click="removeCoupon"
                                            class="rounded-lg bg-red-500 px-4 text-xs font-semibold text-white hover:bg-red-600 transition"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                    <p v-if="couponError" class="text-[10px] font-medium text-red-500">{{ couponError }}</p>
                                    <p v-if="couponSuccess" class="text-[10px] font-medium text-emerald-500">{{ couponSuccess }}</p>
                                </div>

                                <!-- Summary rows -->
                                <div class="space-y-2 border-t border-neutral-100 pt-4 dark:border-neutral-800 text-xs">
                                    <div class="flex justify-between text-neutral-500">
                                        <span>Subtotal</span>
                                        <span class="font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ cartSubtotal.toFixed(2) }}</span>
                                    </div>
                                    <div v-if="appliedCoupon" class="flex justify-between text-emerald-600">
                                        <span>Coupon Discount</span>
                                        <span class="font-mono">- {{ $page.props.currency_symbol ?? '$' }}{{ discountAmount.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-neutral-500">
                                        <span>Shipping</span>
                                        <span class="font-mono">{{ shippingFee === 0 ? 'Free' : `${$page.props.currency_symbol ?? '$'}${shippingFee.toFixed(2)}` }}</span>
                                    </div>
                                    <div class="flex justify-between border-t border-neutral-200 pt-3 text-sm font-bold text-neutral-900 dark:border-neutral-800 dark:text-white">
                                        <span>Grand Total</span>
                                        <span class="font-mono text-base">{{ $page.props.currency_symbol ?? '$' }}{{ cartTotal.toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- CTA button -->
                                <div class="pt-2">
                                    <button 
                                        @click="proceedToCheckout"
                                        :disabled="cart.length === 0"
                                        class="w-full flex h-12 items-center justify-center gap-2 rounded-lg bg-emerald-600 text-xs font-semibold text-white hover:bg-emerald-700 disabled:bg-neutral-200 disabled:text-neutral-500 transition"
                                    >
                                        <span>Proceed to Checkout</span>
                                        <ArrowRight class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK PRODUCT DETAILS VIEW DRAWER (PDP - Section 3.2 storefront) -->
        <div v-if="selectedProduct" class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <div @click="selectedProduct = null" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs transition-opacity"></div>

                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-2xl">
                        <div class="flex h-full flex-col bg-white shadow-2xl dark:bg-neutral-900">
                            <!-- Header -->
                            <div class="flex items-center justify-between px-6 py-5 border-b border-neutral-100 dark:border-neutral-800">
                                <h2 class="text-sm font-bold tracking-tight">Product Details</h2>
                                <button @click="selectedProduct = null" class="rounded-lg p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white transition">
                                    <X class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Body (Responsive layout details page) -->
                            <div class="flex-1 overflow-y-auto p-8 space-y-6">
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <!-- Image Block -->
                                    <div :class="selectedProduct.imageGradient" class="aspect-square w-full rounded-xl bg-gradient-to-tr shrink-0 flex items-center justify-center text-white/20 text-6xl font-bold shadow-md">
                                        {{ selectedProduct.name.split(' ').map(w => w[0]).join('') }}
                                    </div>

                                    <!-- Purchase details panel -->
                                    <div class="space-y-4">
                                        <div class="space-y-1">
                                            <span class="text-xs font-semibold text-emerald-500 uppercase tracking-wider">{{ selectedProduct.category }}</span>
                                            <h1 class="text-xl font-bold tracking-tight leading-tight text-neutral-900 dark:text-white">
                                                {{ selectedProduct.name }}
                                            </h1>
                                        </div>

                                        <!-- Rating -->
                                        <div class="flex items-center gap-1 text-xs text-amber-500">
                                            <Star class="h-3.5 w-3.5 fill-current" />
                                            <span class="font-bold">{{ selectedProduct.rating }}</span>
                                            <span class="text-neutral-400">({{ selectedProduct.reviewsCount }} verified customer reviews)</span>
                                        </div>

                                        <!-- Price -->
                                        <div class="flex items-baseline gap-2">
                                            <span class="font-mono text-xl font-bold text-neutral-900 dark:text-white">
                                                {{ $page.props.currency_symbol ?? '$' }}{{ selectedProduct.price.toFixed(2) }}
                                            </span>
                                            <span v-if="selectedProduct.originalPrice" class="font-mono text-xs text-neutral-400 line-through">
                                                {{ $page.props.currency_symbol ?? '$' }}{{ selectedProduct.originalPrice.toFixed(2) }}
                                            </span>
                                        </div>

                                        <!-- Stock status indicator -->
                                        <div>
                                            <span 
                                                v-if="selectedProduct.stock > 5" 
                                                class="inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-bold text-green-600 dark:bg-green-950 dark:text-green-400"
                                            >
                                                In Stock ({{ selectedProduct.stock }} available)
                                            </span>
                                            <span 
                                                v-else-if="selectedProduct.stock > 0" 
                                                class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                                            >
                                                Low Stock (Only {{ selectedProduct.stock }} left!)
                                            </span>
                                            <span 
                                                v-else 
                                                class="inline-flex rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-bold text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400"
                                            >
                                                Out of Stock
                                            </span>
                                        </div>

                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                            {{ selectedProduct.description }}
                                        </p>

                                        <!-- Actions -->
                                        <div class="pt-4 flex gap-3">
                                            <button 
                                                @click="addToCart(selectedProduct); selectedProduct = null"
                                                :disabled="selectedProduct.stock === 0"
                                                class="flex-1 flex h-10 items-center justify-center gap-2 rounded-lg bg-emerald-600 text-xs font-semibold text-white hover:bg-emerald-700 disabled:bg-neutral-200 disabled:text-neutral-500 transition"
                                            >
                                                <ShoppingCart class="h-4 w-4" /> Add to Shopping Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GLOBAL TOAST FEEDBACK ALERT -->
        <div 
            v-if="toastMessage"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-xl bg-neutral-900 px-4 py-3 text-xs font-bold text-white shadow-xl animate-fade-in dark:bg-neutral-100 dark:text-neutral-900"
        >
            <Info class="h-4 w-4 text-emerald-500" />
            <span>{{ toastMessage }}</span>
        </div>

    </div>
</template>

<style scoped>
/* Inline styling micro-animations for sandbox aesthetics */
.animate-bounce {
    animation: bounce 1s infinite;
}
@keyframes bounce {
    0%, 100% {
        transform: translateY(-25%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
</style>
