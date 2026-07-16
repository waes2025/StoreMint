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
    Monitor,
} from '@lucide/vue';
import { DbProduct, DbCoupon } from '@/types/storefront';
import { useStorefront } from '@/composables/useStorefront';
import Footer from '@/components/Footer.vue';
import CartDrawer from '../../../../../Cart/resources/assets/js/components/CartDrawer.vue';
import Announcement from '../../../../../Cart/resources/assets/js/components/Announcement.vue';
import ActiveCoupons from '../../../../../Cart/resources/assets/js/components/ActiveCoupons.vue';
import OrderSummary from '../../../../../Cart/resources/assets/js/components/OrderSummary.vue';
import FeaturedCollections from '../components/FeaturedCollections.vue';
import FeaturedProducts from '../components/FeaturedProducts.vue';
import BestSellers from '../components/BestSellers.vue';

const props = defineProps<{
    dbProducts?: DbProduct[];
    dbCategories?: string[];
    dbBrands?: string[];
    dbCoupons?: DbCoupon[];
    gateways?: {
        stripe: {
            enabled: boolean;
            publishable_key: string;
            secret_key: string;
        };
        sslcommerz: {
            enabled: boolean;
            store_id: string;
            store_password: string;
        };
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
    page.props.currentTeam
        ? route('dashboard', page.props.currentTeam.slug).url
        : null,
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
    isShipmentEnabled,
    isCartEnabled: coreIsCartEnabled,
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
    selectCategory,
} = useStorefront(props);

const isShopEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Shop');
});

const isCartEnabled = computed(() => {
    return isShopEnabled.value && coreIsCartEnabled.value;
});

const isBlogEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Blog');
});

// Language setup
const selectedLang = ref('English');
const langOpen = ref(false);
const langDropdownRef = useTemplateRef('langDropdownRef');
const languages = ['English', 'Spanish', 'French', 'German', 'Bengali'];

onClickOutside(langDropdownRef, () => {
    langOpen.value = false;
});


</script>

<template>
    <Head title="StoreMint Storefront" />

    <div
        :class="{ dark: isDarkMode }"
        class="min-h-screen bg-neutral-50 text-neutral-800 transition-colors duration-300 dark:bg-neutral-950 dark:text-neutral-200"
    >
        <!-- TOP PERSISTENT DEMO BAR (Designed to showcase tokens & guides) -->
        <div
            class="sticky top-0 z-50 flex flex-wrap items-center justify-between border-b border-emerald-500/20 bg-emerald-900 px-4 py-2 text-xs font-medium text-white shadow-md dark:bg-emerald-950"
        >
            <div class="flex items-center gap-4 text-neutral-200">
                <a
                    href="tel:+18005550199"
                    class="flex items-center gap-1.5 transition hover:text-white"
                >
                    <Phone class="h-3 w-3 text-emerald-400" />
                    <span>+1 (800) 555-0199</span>
                </a>
                <span class="text-emerald-700/60">|</span>
                <a
                    href="mailto:support@storemint.com"
                    class="flex items-center gap-1.5 transition hover:text-white"
                >
                    <Mail class="h-3 w-3 text-emerald-400" />
                    <span>support@storemint.com</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <!-- Language Selector -->
                <div class="relative" ref="langDropdownRef">
                    <button
                        @click="langOpen = !langOpen"
                        class="flex cursor-pointer items-center gap-1.5 rounded bg-emerald-800 px-2 py-1 text-[11px] font-medium text-white transition hover:bg-emerald-700"
                    >
                        <Globe class="h-3 w-3 text-emerald-300" />
                        <span>{{ selectedLang }}</span>
                        <ChevronDown class="h-2.5 w-2.5 text-emerald-400" />
                    </button>

                    <div
                        v-if="langOpen"
                        class="absolute right-0 z-30 mt-1 w-28 rounded-lg border border-neutral-200 bg-white p-1 shadow-md dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <button
                            v-for="lang in languages"
                            :key="lang"
                            @click="
                                selectedLang = lang;
                                langOpen = false;
                            "
                            class="flex w-full cursor-pointer items-center justify-between rounded-md px-2 py-1 text-left text-[11px] text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800"
                        >
                            <span>{{ lang }}</span>
                            <Check
                                v-if="selectedLang === lang"
                                class="h-2.5 w-2.5 text-emerald-500"
                            />
                        </button>
                    </div>
                </div>

                <!-- Theme Selector (Segmented Control) -->
                <div
                    class="flex items-center gap-0.5 rounded-lg border border-emerald-500/20 bg-emerald-950 p-0.5 shadow-xs"
                >
                    <button
                        @click="updateAppearance('light')"
                        :class="
                            appearance === 'light'
                                ? 'bg-emerald-800 text-amber-300 shadow-xs'
                                : 'text-emerald-400/80 hover:text-emerald-300'
                        "
                        class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-md transition duration-200"
                        title="Light Mode"
                    >
                        <Sun class="h-3.5 w-3.5" />
                    </button>
                    <button
                        @click="updateAppearance('system')"
                        :class="
                            appearance === 'system'
                                ? 'bg-emerald-800 text-blue-300 shadow-xs'
                                : 'text-emerald-400/80 hover:text-emerald-300'
                        "
                        class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-md transition duration-200"
                        title="System Mode"
                    >
                        <Monitor class="h-3.5 w-3.5" />
                    </button>
                    <button
                        @click="updateAppearance('dark')"
                        :class="
                            appearance === 'dark'
                                ? 'bg-emerald-800 text-emerald-300 shadow-xs'
                                : 'text-emerald-400/80 hover:text-emerald-300'
                        "
                        class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-md transition duration-200"
                        title="Dark Mode"
                    >
                        <Moon class="h-3.5 w-3.5" />
                    </button>
                </div>

                <template v-if="$page.props.auth.user">
                    <span
                        v-if="dashboardUrl"
                        class="text-[11px] font-medium text-emerald-100"
                    >
                        Hello, {{ $page.props.auth.user.first_name }}
                    </span>
                    <span
                        v-else
                        class="text-[11px] font-medium text-emerald-100"
                    >
                        Hello, {{ $page.props.auth.user.first_name }}
                    </span>
                    <Link
                        :href="route('logout').url"
                        method="post"
                        as="button"
                        class="flex items-center gap-1 rounded border border-emerald-700/50 bg-emerald-800/80 px-2 py-1 text-[11px] font-semibold text-white transition hover:bg-emerald-800"
                    >
                        Log out
                    </Link>
                </template>
                <Link
                    v-else
                    :href="route('login').url"
                    class="flex items-center gap-1 rounded bg-white px-2 py-1 text-[11px] text-emerald-900 transition hover:bg-neutral-100"
                >
                    <Lock class="h-3 w-3" />
                    <span>Log In</span>
                </Link>
            </div>
        </div>

        <!-- 36px ANNOUNCEMENT BAR (Guidelines Section 1.5) -->
        <Announcement
            v-if="isCartEnabled"
            :announcement="props.announcement"
        />

        <!-- 80px HEADER BAR (Guidelines Section 1.5) -->
        <header
            class="sticky top-10 z-40 h-20 border-b border-neutral-200/80 bg-white/90 backdrop-blur-md dark:border-neutral-800 dark:bg-neutral-900/90"
        >
            <div
                class="mx-auto flex h-full max-w-[1280px] items-center justify-between px-6"
            >
                <!-- Logo -->
                <div
                    class="flex cursor-pointer items-center gap-2"
                    @click="resetStorefront"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-400 to-emerald-600 text-white shadow-md shadow-emerald-500/20"
                    >
                        <Leaf class="h-5 w-5" />
                    </div>
                    <span
                        class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white"
                    >
                        Store<span class="text-emerald-500">Mint</span>
                    </span>
                </div>

                <!-- Navigation links -->
                <nav
                    class="hidden items-center gap-8 text-sm font-medium text-neutral-600 md:flex dark:text-neutral-400"
                >
                    <button
                        @click="viewMode = 'browse'"
                        :class="
                            viewMode === 'browse'
                                ? 'font-bold text-emerald-600 dark:text-emerald-400'
                                : 'hover:text-emerald-500'
                        "
                        class="cursor-pointer border-none bg-transparent py-1 transition"
                    >
                        Home
                    </button>
                    <Link
                        v-if="isShopEnabled"
                        href="/shop"
                        class="cursor-pointer py-1 transition hover:text-emerald-500"
                    >
                        Shop All
                    </Link>
                    <button
                        v-if="isShopEnabled"
                        @click="viewMode = 'categories'"
                        :class="
                            viewMode === 'categories'
                                ? 'font-bold text-emerald-600 dark:text-emerald-400'
                                : 'hover:text-emerald-500'
                        "
                        class="cursor-pointer border-none bg-transparent py-1 transition"
                    >
                        Categories
                    </button>
                    <button
                        @click="viewMode = 'support'"
                        :class="
                            viewMode === 'support'
                                ? 'font-bold text-emerald-600 dark:text-emerald-400'
                                : 'hover:text-emerald-500'
                        "
                        class="cursor-pointer border-none bg-transparent py-1 transition"
                    >
                        Support
                    </button>
                    <Link
                        v-if="isBlogEnabled"
                        href="/blogs"
                        class="cursor-pointer py-1 transition hover:text-emerald-500"
                    >
                        Blog
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboardUrl || '/dashboard'"
                        class="cursor-pointer py-1 transition hover:text-emerald-500"
                    >
                        Dashboard
                    </Link>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-4">
                    <!-- Search Input -->
                    <div class="relative hidden w-64 sm:block" v-if="isShopEnabled">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search products..."
                            class="h-10 w-full rounded-lg border border-neutral-200 bg-neutral-50 pr-4 pl-10 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800 dark:focus:border-emerald-500"
                        />
                    </div>

                    <!-- Cart Toggle -->
                    <button
                        v-if="isCartEnabled"
                        @click="cartOpen = true"
                        class="relative flex h-10 items-center gap-2 rounded-lg bg-neutral-100 px-4 text-sm font-semibold transition hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700"
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
                <section
                    class="relative overflow-hidden rounded-2xl bg-neutral-900 text-white dark:bg-neutral-900"
                >
                    <!-- Soft background ambient glows -->
                    <div
                        class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-emerald-500/20 blur-3xl"
                    ></div>
                    <div
                        class="absolute -right-24 -bottom-24 h-96 w-96 rounded-full bg-emerald-600/10 blur-3xl"
                    ></div>

                    <div
                        class="relative z-10 max-w-2xl space-y-6 px-8 py-16 md:p-20"
                    >
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400"
                        >
                            <Sparkles class="h-3 w-3" />
                            <span>REDESIGNED PLATFORM</span>
                        </div>
                        <h1
                            class="text-4xl leading-tight font-extrabold tracking-tight md:text-5xl"
                        >
                            State-of-the-Art <br />
                            <span
                                class="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent"
                                >E-Commerce Redefined</span
                            >
                        </h1>
                        <p
                            class="max-w-lg text-sm leading-relaxed text-neutral-300 md:text-base"
                        >
                            Designed strictly according to the Design Grid &
                            System Guidelines. Experience fluid 12-column
                            layouts, unified spacing scales, and pixel-perfect
                            contrast.
                        </p>
                        <div class="pt-4" v-if="isShopEnabled">
                            <button
                                @click="scrollToCollection"
                                class="inline-flex h-12 items-center gap-2 rounded-lg bg-emerald-500 px-6 text-sm font-semibold text-neutral-950 transition hover:bg-emerald-400"
                            >
                                <span>Shop the Collection</span>
                                <ArrowRight class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </section>

                <!-- ACTIVE PROMOS HIGHLIGHT (Guidelines Section 1.5) -->
                <ActiveCoupons
                    v-if="isCartEnabled"
                    :active-coupons="activeCoupons"
                    :applied-coupon="appliedCoupon"
                    @apply="code => { couponInput = code; applyCoupon(); }"
                />

                <!-- FEATURED CATEGORIES SECTION -->
                <FeaturedCollections v-if="isShopEnabled" :categories="categories" />

                <!-- FEATURED PRODUCTS SECTION -->
                <FeaturedProducts
                    v-if="isShopEnabled"
                    :featured-products="featuredProducts"
                    :is-cart-enabled="isCartEnabled"
                    @select-product="product => selectedProduct = product"
                    @add-to-cart="product => addToCart(product)"
                />

                <!-- BEST SELLERS SECTION -->
                <BestSellers
                    v-if="isShopEnabled"
                    :best-seller-products="bestSellerProducts"
                    :is-cart-enabled="isCartEnabled"
                    @select-product="product => selectedProduct = product"
                    @add-to-cart="product => addToCart(product)"
                />

                <!-- BOTTOM CTA CATALOG PROMOTION -->
                <section
                    v-if="isShopEnabled"
                    class="relative space-y-6 overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-800 p-8 text-center text-white shadow-lg md:p-12"
                >
                    <div
                        class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.15),transparent)]"
                    ></div>
                    <div class="relative z-10 mx-auto max-w-xl space-y-4">
                        <h2 class="text-3xl font-extrabold tracking-tight">
                            Explore the Complete StoreMint Collection
                        </h2>
                        <p class="text-xs text-neutral-100 md:text-sm">
                            Browse through hundreds of modern lifestyle goods.
                            Filter by price, category, availability, and sort
                            them dynamically to match your lifestyle.
                        </p>
                        <div class="pt-2">
                            <Link
                                href="/shop"
                                class="inline-flex h-11 items-center justify-center rounded-lg bg-white px-8 text-xs font-semibold text-emerald-900 shadow-md transition hover:bg-neutral-100"
                            >
                                Go to Shop Catalog
                            </Link>
                        </div>
                    </div>
                </section>
            </div>

            <!-- CATEGORIES STATE -->
            <div v-else-if="viewMode === 'categories'" class="space-y-10">
                <div class="mx-auto max-w-xl space-y-3 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                    >
                        <Leaf class="h-3.5 w-3.5" /> Curated Collections
                    </span>
                    <h1
                        class="text-3xl font-extrabold tracking-tight md:text-4xl"
                    >
                        Browse by Category
                    </h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Explore premium goods tailored to your lifestyle and
                        space. Filter and find what you need.
                    </p>
                </div>

                <div class="grid gap-6 pt-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(cat, index) in categories.filter(
                            (c) => c !== 'All',
                        )"
                        :key="cat"
                        @click="selectCategory(cat)"
                        class="group relative cursor-pointer overflow-hidden rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm transition duration-300 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <!-- Card Gradient backdrop visual -->
                        <div
                            :class="[
                                index % 5 === 0
                                    ? 'from-amber-500/10 to-orange-500/10'
                                    : index % 5 === 1
                                      ? 'from-emerald-500/10 to-teal-500/10'
                                      : index % 5 === 2
                                        ? 'from-blue-500/10 to-indigo-500/10'
                                        : index % 5 === 3
                                          ? 'from-pink-500/10 to-rose-500/10'
                                          : 'from-purple-500/10 to-fuchsia-500/10',
                            ]"
                            class="absolute inset-0 bg-gradient-to-br opacity-50 transition duration-300 group-hover:opacity-100"
                        ></div>

                        <div
                            class="relative z-10 flex h-40 flex-col justify-between"
                        >
                            <div class="flex items-start justify-between">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400"
                                >
                                    <ShoppingBag
                                        v-if="cat === 'Accessories'"
                                        class="h-6 w-6"
                                    />
                                    <Sparkles
                                        v-else-if="cat === 'Electronics'"
                                        class="h-6 w-6"
                                    />
                                    <Leaf
                                        v-else-if="cat === 'Fashion'"
                                        class="h-6 w-6"
                                    />
                                    <LayoutGrid v-else class="h-6 w-6" />
                                </div>
                                <span
                                    class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400"
                                >
                                    {{
                                        activeProducts.filter(
                                            (p) => p.category === cat,
                                        ).length
                                    }}
                                    Items
                                </span>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-bold text-neutral-900 transition group-hover:text-emerald-500 dark:text-white"
                                >
                                    {{ cat }}
                                </h3>
                                <p
                                    class="mt-1 text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    Explore our premium selection of
                                    {{ cat.toLowerCase() }} designs.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW ARRIVALS STATE -->
            <div v-else-if="viewMode === 'new-arrivals'" class="space-y-10">
                <div class="mx-auto max-w-xl space-y-3 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-600 dark:text-amber-400"
                    >
                        <Sparkles class="h-3.5 w-3.5" /> Just Released
                    </span>
                    <h1
                        class="text-3xl font-extrabold tracking-tight md:text-4xl"
                    >
                        New Arrivals
                    </h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        The latest additions to the StoreMint lineup. Be the
                        first to grab these fresh premium products.
                    </p>
                </div>

                <!-- Featured New Arrival Spotlight Card -->
                <div
                    class="relative flex flex-col items-center gap-8 overflow-hidden rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm md:flex-row md:p-8 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div
                        class="absolute top-0 right-0 h-40 w-40 rounded-full bg-emerald-500/10 blur-3xl"
                    ></div>
                    <div
                        class="relative flex aspect-square w-full items-center justify-center overflow-hidden rounded-xl bg-neutral-100 md:aspect-[4/3] md:w-1/2 dark:bg-neutral-800"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-tr from-emerald-600 to-indigo-900 opacity-90"
                        ></div>
                        <div
                            class="absolute inset-0 flex flex-col items-center justify-center space-y-2 p-6 text-center text-white"
                        >
                            <span
                                class="rounded bg-white/20 px-2 py-0.5 text-[10px] font-bold tracking-wider uppercase"
                                >Spotlight</span
                            >
                            <h2 class="text-2xl font-bold tracking-tight">
                                Quantum Chronograph
                            </h2>
                            <p class="text-xs text-neutral-200">
                                Our flagship premium watch model
                            </p>
                        </div>
                    </div>

                    <div class="w-full space-y-6 md:w-1/2">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold tracking-wider text-amber-800 uppercase dark:bg-amber-950/60 dark:text-amber-400"
                                    >Hot Pick</span
                                >
                                <span class="text-xs text-neutral-400"
                                    >Released this week</span
                                >
                            </div>
                            <h3 class="text-2xl font-extrabold">
                                Quantum Chronograph Watch
                            </h3>
                            <p
                                class="text-xs text-neutral-500 dark:text-neutral-400"
                            >
                                Precision timekeeper with mechanical elegance,
                                featuring a surgical-grade stainless steel
                                casing, genuine leather straps, and
                                scratch-resistant sapphire crystal cover.
                            </p>
                        </div>

                        <div class="flex items-baseline gap-2 font-mono">
                            <span
                                class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400"
                                >{{
                                    $page.props.currency_symbol ?? '$'
                                }}299.00</span
                            >
                            <span class="text-sm text-neutral-400 line-through"
                                >{{
                                    $page.props.currency_symbol ?? '$'
                                }}399.00</span
                            >
                        </div>

                        <div>
                            <button
                                @click="selectedProduct = activeProducts[0]"
                                class="inline-flex h-11 items-center gap-2 rounded-lg bg-emerald-600 px-6 text-xs font-semibold text-white transition hover:bg-emerald-700"
                            >
                                <Eye class="h-4 w-4" /> View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid of new arrival products -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold tracking-tight">
                        Recent Releases
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            v-for="product in activeProducts.slice(0, 4)"
                            :key="'new-' + product.id"
                            class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <span
                                class="absolute top-3 left-3 z-10 rounded-full bg-emerald-600 px-2 py-0.5 text-[9px] font-bold tracking-wider text-white uppercase"
                                >New</span
                            >

                            <div
                                class="relative aspect-square w-full cursor-pointer overflow-hidden bg-neutral-100 dark:bg-neutral-800"
                                @click="selectedProduct = product"
                            >
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    :class="product.imageGradient"
                                    class="absolute inset-0 bg-gradient-to-tr opacity-90 transition duration-500 group-hover:scale-105"
                                ></div>
                                <div
                                    v-if="!product.image"
                                    class="absolute inset-0 flex items-center justify-center text-4xl font-bold text-white/10 transition duration-500 select-none group-hover:scale-110"
                                >
                                    {{
                                        product.name
                                            .split(' ')
                                            .map((w) => w[0])
                                            .join('')
                                    }}
                                </div>
                            </div>

                            <div
                                class="flex flex-1 flex-col justify-between space-y-3 bg-white p-4 dark:bg-neutral-900"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="text-[10px] font-bold text-neutral-400 uppercase"
                                        >{{ product.category }}</span
                                    >
                                    <h4
                                        class="line-clamp-1 cursor-pointer text-xs font-bold text-neutral-900 transition group-hover:text-emerald-500 dark:text-white"
                                        @click="selectedProduct = product"
                                    >
                                        {{ product.name }}
                                    </h4>
                                </div>

                                <div
                                    class="flex items-center justify-between pt-1"
                                >
                                    <span
                                        class="font-mono text-sm font-extrabold text-neutral-900 dark:text-white"
                                        >{{ $page.props.currency_symbol ?? '$'
                                        }}{{ product.price.toFixed(2) }}</span
                                    >
                                    <button
                                        @click="selectedProduct = product"
                                        class="rounded-lg bg-neutral-100 p-2 text-neutral-600 transition hover:bg-emerald-50 hover:text-white dark:bg-neutral-800 dark:text-neutral-400"
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
            <div
                v-else-if="viewMode === 'support'"
                class="grid items-start gap-8 lg:grid-cols-12"
            >
                <!-- Support Info & FAQ (7 Cols) -->
                <div class="space-y-8 lg:col-span-7">
                    <div class="space-y-3">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                        >
                            <Info class="h-3.5 w-3.5" /> StoreMint Help Desk
                        </span>
                        <h1 class="text-3xl font-extrabold tracking-tight">
                            How can we help?
                        </h1>
                        <p
                            class="text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Browse frequently asked questions, create a support
                            ticket, or chat with our automated support agent.
                        </p>
                    </div>

                    <!-- FAQ Section -->
                    <div class="space-y-4">
                        <h3
                            class="border-b border-neutral-100 pb-2 text-lg font-bold tracking-tight dark:border-neutral-800"
                        >
                            Frequently Asked Questions
                        </h3>

                        <div class="space-y-2">
                            <div
                                v-for="(faq, i) in [
                                    {
                                        q: 'How long does shipping take?',
                                        a: 'Standard shipping takes 3-5 business days. Express shipping options take 1-2 business days. Tracking details will be emailed to you immediately after shipment.',
                                        shipping: true,
                                    },
                                    {
                                        q: 'What is your refund/return policy?',
                                        a: 'We offer a 30-day hassle-free return policy. If you are not satisfied with your purchase, please email returns@storemint.com with your receipt to start the process.',
                                    },
                                    {
                                        q: 'Do you offer international delivery?',
                                        a: 'Yes! We deliver worldwide. Shipping rates and delivery timeframes vary by country and are calculated at checkout.',
                                        shipping: true,
                                    },
                                    {
                                        q: 'How can I update my billing information?',
                                        a: 'You can update your personal billing information from the profile settings within the Admin Dashboard.',
                                    },
                                ].filter(faq => !faq.shipping || isShipmentEnabled)"
                                :key="i"
                                class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900"
                            >
                                <button
                                    @click="
                                        expandedFaq =
                                            expandedFaq === i ? null : i
                                    "
                                    class="flex w-full items-center justify-between px-5 py-4 text-left text-xs font-bold text-neutral-800 transition select-none hover:text-emerald-500 dark:text-neutral-200"
                                >
                                    <span>{{ faq.q }}</span>
                                    <Plus
                                        v-if="expandedFaq !== i"
                                        class="h-4 w-4 text-neutral-400"
                                    />
                                    <Minus
                                        v-else
                                        class="h-4 w-4 text-emerald-500"
                                    />
                                </button>

                                <div
                                    v-show="expandedFaq === i"
                                    class="border-t border-neutral-50 px-5 pt-2 pb-4 text-xs leading-relaxed text-neutral-500 dark:border-neutral-800/50 dark:text-neutral-400"
                                >
                                    {{ faq.a }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create Ticket Form -->
                    <div
                        class="space-y-4 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <h3 class="text-sm font-bold tracking-tight">
                            Create a Support Ticket
                        </h3>

                        <div
                            v-if="supportSubmitted"
                            class="flex items-start gap-2 rounded-lg border border-emerald-100 bg-emerald-50/50 p-4 text-xs text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/20 dark:text-emerald-400"
                        >
                            <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0" />
                            <div>
                                <span class="font-bold">Ticket Submitted!</span>
                                Thank you. Our support engineers will review
                                your request and get back to you within 2 hours.
                            </div>
                        </div>

                        <form
                            v-else
                            @submit.prevent="submitSupportTicket"
                            class="space-y-3"
                        >
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="flex flex-col gap-1">
                                    <label
                                        class="text-[10px] font-bold text-neutral-500"
                                        >Your Name *</label
                                    >
                                    <input
                                        v-model="supportForm.name"
                                        type="text"
                                        required
                                        placeholder="e.g. John Doe"
                                        class="h-9 rounded-lg border border-neutral-200 px-3 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                    />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label
                                        class="text-[10px] font-bold text-neutral-500"
                                        >Your Email *</label
                                    >
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
                                <label
                                    class="text-[10px] font-bold text-neutral-500"
                                    >Subject *</label
                                >
                                <input
                                    v-model="supportForm.subject"
                                    type="text"
                                    required
                                    placeholder="Brief summary of issue"
                                    class="h-9 rounded-lg border border-neutral-200 px-3 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950"
                                />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label
                                    class="text-[10px] font-bold text-neutral-500"
                                    >Description of Issue *</label
                                >
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
                                class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                            >
                                Submit Ticket
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Simulated Live Chat Widget (5 Cols) -->
                <div
                    class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm lg:col-span-5 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div
                        class="flex items-center gap-2 bg-emerald-600 p-4 text-white"
                    >
                        <div class="relative">
                            <span
                                class="block h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-300"
                            ></span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold">
                                Minty Live Assistant
                            </h4>
                            <span class="text-[10px] opacity-75"
                                >Typically replies instantly</span
                            >
                        </div>
                    </div>

                    <div
                        class="h-80 space-y-3 overflow-y-auto bg-neutral-50 p-4 dark:bg-neutral-950/20"
                    >
                        <div
                            v-for="(msg, index) in chatMessages"
                            :key="index"
                            :class="
                                msg.sender === 'user'
                                    ? 'justify-end'
                                    : 'justify-start'
                            "
                            class="flex"
                        >
                            <div
                                :class="
                                    msg.sender === 'user'
                                        ? 'rounded-br-none bg-emerald-600 text-white'
                                        : 'rounded-bl-none border border-neutral-100 bg-white text-neutral-800 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200'
                                "
                                class="max-w-[85%] rounded-2xl px-4 py-2.5 text-xs shadow-xs"
                            >
                                {{ msg.text }}
                            </div>
                        </div>

                        <div v-if="chatProcessing" class="flex justify-start">
                            <div
                                class="flex items-center gap-1 rounded-2xl rounded-bl-none border border-neutral-100 bg-white px-4 py-2 text-xs dark:border-neutral-800 dark:bg-neutral-900"
                            >
                                <span
                                    class="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-400"
                                    style="animation-delay: 0ms"
                                ></span>
                                <span
                                    class="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-400"
                                    style="animation-delay: 150ms"
                                ></span>
                                <span
                                    class="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-400"
                                    style="animation-delay: 300ms"
                                ></span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="space-y-2 border-t border-neutral-100 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <p
                            class="text-[10px] font-semibold text-neutral-400 uppercase"
                        >
                            Quick Actions:
                        </p>
                        <div class="flex flex-col gap-1.5">
                            <button
                                v-for="option in chatOptions"
                                :key="option"
                                @click="sendChatMessage(option)"
                                :disabled="chatProcessing"
                                class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-left text-xs font-semibold text-neutral-700 transition hover:border-emerald-200 hover:bg-emerald-50 dark:border-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-950"
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
                    <button
                        @click="viewMode = 'browse'"
                        class="flex items-center gap-1.5 text-xs font-semibold text-neutral-500 hover:text-neutral-800 dark:hover:text-white"
                    >
                        <ArrowLeft class="h-4 w-4" /> Back to store
                    </button>
                </div>

                <div class="grid gap-8 lg:grid-cols-12">
                    <!-- Checkout Form Fields (Left Pane - 7 Cols) -->
                    <div class="space-y-6 lg:col-span-7">
                        <div
                            class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
                        >
                             <h2
                                class="mb-4 flex items-center gap-2 text-lg font-bold tracking-tight"
                            >
                                <component :is="isShipmentEnabled ? Truck : Info" class="h-5 w-5 text-emerald-500" />
                                {{ isShipmentEnabled ? 'Shipping Information' : 'Billing & Contact Information' }}
                            </h2>

                            <!-- Forms following Section 5.4 layout rules -->
                            <div class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="text-xs font-semibold text-neutral-600 dark:text-neutral-400"
                                            >Full Name</label
                                        >
                                        <input
                                            v-model="checkoutForm.name"
                                            type="text"
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="text-xs font-semibold text-neutral-600 dark:text-neutral-400"
                                            >Email Address</label
                                        >
                                        <input
                                            v-model="checkoutForm.email"
                                            type="email"
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label
                                        class="text-xs font-semibold text-neutral-600 dark:text-neutral-400"
                                    >{{ isShipmentEnabled ? 'Delivery Address' : 'Address' }}</label>
                                    <input
                                        v-model="checkoutForm.address"
                                        type="text"
                                        class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                    />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="text-xs font-semibold text-neutral-600 dark:text-neutral-400"
                                            >City</label
                                        >
                                        <input
                                            v-model="checkoutForm.city"
                                            type="text"
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="text-xs font-semibold text-neutral-600 dark:text-neutral-400"
                                            >Postal Code</label
                                        >
                                        <input
                                            v-model="checkoutForm.zip"
                                            type="text"
                                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="text-xs font-semibold text-neutral-600 dark:text-neutral-400"
                                            >Phone Number</label
                                        >
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
                        <div
                            class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
                        >
                            <h2
                                class="mb-4 flex items-center gap-2 text-lg font-bold tracking-tight"
                            >
                                <CreditCard class="h-5 w-5 text-emerald-500" />
                                Payment Method Selection
                            </h2>

                            <div class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <!-- Stripe -->
                                    <label
                                        v-if="
                                            !gateways ||
                                            gateways.stripe?.enabled
                                        "
                                        :class="
                                            checkoutForm.paymentMethod ===
                                            'stripe'
                                                ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500'
                                                : 'border-neutral-200'
                                        "
                                        class="flex cursor-pointer flex-col items-center justify-center space-y-2 rounded-xl border-2 p-4 text-center transition hover:border-emerald-500 dark:border-neutral-800"
                                    >
                                        <input
                                            type="radio"
                                            value="stripe"
                                            v-model="checkoutForm.paymentMethod"
                                            class="sr-only"
                                        />
                                        <CreditCard
                                            class="h-6 w-6 text-emerald-600 dark:text-emerald-400"
                                        />
                                        <span class="text-xs font-bold"
                                            >Stripe</span
                                        >
                                        <span
                                            class="text-[10px] text-neutral-400"
                                            >International / Credit Cards</span
                                        >
                                    </label>

                                    <!-- SSLCommerz -->
                                    <label
                                        v-if="
                                            !gateways ||
                                            gateways.sslcommerz?.enabled
                                        "
                                        :class="
                                            checkoutForm.paymentMethod ===
                                            'sslcommerz'
                                                ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500'
                                                : 'border-neutral-200'
                                        "
                                        class="flex cursor-pointer flex-col items-center justify-center space-y-2 rounded-xl border-2 p-4 text-center transition hover:border-emerald-500 dark:border-neutral-800"
                                    >
                                        <input
                                            type="radio"
                                            value="sslcommerz"
                                            v-model="checkoutForm.paymentMethod"
                                            class="sr-only"
                                        />
                                        <ShoppingBag
                                            class="h-6 w-6 text-emerald-600 dark:text-emerald-400"
                                        />
                                        <span class="text-xs font-bold"
                                            >SSLCommerz</span
                                        >
                                        <span
                                            class="text-[10px] text-neutral-400"
                                            >Local Cards & Mobile Banking</span
                                        >
                                    </label>

                                    <!-- COD -->
                                    <label
                                        v-if="
                                            !gateways || gateways.cod?.enabled
                                        "
                                        :class="
                                            checkoutForm.paymentMethod === 'cod'
                                                ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500'
                                                : 'border-neutral-200'
                                        "
                                        class="flex cursor-pointer flex-col items-center justify-center space-y-2 rounded-xl border-2 p-4 text-center transition hover:border-emerald-500 dark:border-neutral-800"
                                    >
                                        <input
                                            type="radio"
                                            value="cod"
                                            v-model="checkoutForm.paymentMethod"
                                            class="sr-only"
                                        />
                                        <Truck
                                            class="h-6 w-6 text-emerald-600 dark:text-emerald-400"
                                        />
                                        <span class="text-xs font-bold"
                                            >Cash on Delivery</span
                                        >
                                        <span
                                            class="text-[10px] text-neutral-400"
                                            >Pay when products arrive</span
                                        >
                                    </label>
                                </div>

                                <!-- Stripe Fields details (FR-3) -->
                                <div
                                    v-if="
                                        checkoutForm.paymentMethod === 'stripe'
                                    "
                                    class="mt-4 space-y-4 border-t border-neutral-100 pt-4 dark:border-neutral-800"
                                >
                                    <div
                                        class="space-y-3 rounded-lg bg-neutral-50 p-4 dark:bg-neutral-800/50"
                                    >
                                        <span
                                            class="flex items-center gap-1 text-[10px] font-bold tracking-widest text-neutral-400 uppercase"
                                        >
                                            <Lock class="h-3 w-3" /> Secure
                                            Stripe Element
                                        </span>
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <div
                                                class="flex flex-col gap-1.5 sm:col-span-2"
                                            >
                                                <label
                                                    class="text-[10px] font-semibold text-neutral-600 dark:text-neutral-400"
                                                    >Card Number</label
                                                >
                                                <input
                                                    v-model="stripeCard.number"
                                                    type="text"
                                                    class="h-9 rounded border border-neutral-200 bg-white px-2.5 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                                                />
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <label
                                                    class="text-[10px] font-semibold text-neutral-600 dark:text-neutral-400"
                                                    >Expiry (MM/YY)</label
                                                >
                                                <input
                                                    v-model="stripeCard.expiry"
                                                    type="text"
                                                    placeholder="MM/YY"
                                                    class="h-9 rounded border border-neutral-200 bg-white px-2.5 text-center text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                                                />
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-neutral-500">
                                            Stripe test keys will process this
                                            order simulation.
                                        </p>
                                    </div>
                                </div>

                                <!-- SSLCommerz detail warning -->
                                <div
                                    v-if="
                                        checkoutForm.paymentMethod ===
                                        'sslcommerz'
                                    "
                                    class="mt-4 flex items-start gap-2 rounded-lg border border-blue-100 bg-blue-50/50 p-4 text-xs text-blue-600 dark:border-blue-900 dark:bg-blue-950/20 dark:text-blue-400"
                                >
                                    <Info class="mt-0.5 h-4 w-4 shrink-0" />
                                    <div>
                                        <span class="font-bold"
                                            >SSLCommerz Sandbox:</span
                                        >
                                        You will be redirected to the secure
                                        local payment portal simulating
                                        Visa/Mastercard/bKash checkouts.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Side Panel (Right Pane - 5 Cols) -->
                    <div class="space-y-6 lg:col-span-5">
                        <OrderSummary
                            :cart="cart"
                            :cart-subtotal="cartSubtotal"
                            :applied-coupon="appliedCoupon"
                            :discount-amount="discountAmount"
                            :is-shipment-enabled="isShipmentEnabled"
                            :shipping-fee="shippingFee"
                            :cart-total="cartTotal"
                            :stripe-card="stripeCard"
                            @place-order="placeOrder"
                        />
                    </div>
                </div>
            </div>

            <!-- ORDER CONFIRMATION & DOCUMENT INVOICE -->
            <div
                v-else-if="viewMode === 'confirmation' && orderInvoice"
                class="mx-auto max-w-2xl space-y-4"
            >
                <!-- Action Buttons -->
                <div class="flex justify-end gap-2">
                    <button
                        @click="handlePrint"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md border border-neutral-200 bg-white px-3 text-xs font-semibold transition hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200"
                    >
                        <Printer class="h-3.5 w-3.5" /> Print Invoice
                    </button>
                    <button
                        @click="resetStorefront"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md bg-emerald-600 px-3 text-xs font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Back to Shop
                    </button>
                </div>

                <!-- DOCUMENT INVOICE -->
                <div
                    id="invoice-printable"
                    class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-white print:border-0 print:shadow-none"
                >
                    <!-- Company Header -->
                    <div
                        class="border-b border-neutral-300 px-10 pt-8 pb-4 text-center dark:border-neutral-600"
                    >
                        <div
                            class="text-3xl font-black tracking-tight text-neutral-900 uppercase"
                            style="letter-spacing: 0.05em"
                        >
                            {{ $page.props.name ?? 'StoreMint' }}
                        </div>
                        <div class="mt-1 text-[11px] text-neutral-600">
                            45 Design Grid Plaza, Gulshan-2,
                            Dhaka-1212&nbsp;&nbsp;|&nbsp;&nbsp; Mobile: +880
                            1600 000 000&nbsp;&nbsp;|&nbsp;&nbsp; Email:
                            support@storemint.com
                        </div>
                    </div>

                    <!-- Invoice Title -->
                    <div class="py-3 text-center">
                        <span
                            class="text-base font-semibold tracking-wide text-neutral-700"
                            >Invoice</span
                        >
                    </div>

                    <!-- Two-column Metadata -->
                    <div
                        class="grid grid-cols-2 gap-x-6 px-10 pb-4 text-[11px]"
                    >
                        <!-- Left -->
                        <div class="space-y-1">
                            <div>
                                <span class="font-semibold text-neutral-700"
                                    >Invoice No.</span
                                >
                                <span class="ml-1 font-mono text-neutral-600">{{
                                    orderInvoice.invoiceNo
                                }}</span>
                            </div>
                            <div>
                                <div class="font-semibold text-neutral-700">
                                    Customer
                                </div>
                                <div class="text-neutral-600">
                                    {{ orderInvoice.customer.name }}
                                </div>
                            </div>
                            <div>
                                <span class="font-semibold text-neutral-700"
                                    >Mobile</span
                                >
                                <span class="ml-1 text-neutral-600">{{
                                    orderInvoice.customer.phone || '-'
                                }}</span>
                            </div>
                            <div class="pt-2">
                                <span class="text-neutral-500">Created by</span>
                                <span
                                    class="ml-1 font-semibold text-neutral-700"
                                    >{{ orderInvoice.customer.name }}</span
                                >
                            </div>
                        </div>
                        <!-- Right -->
                        <div class="space-y-1 text-right">
                            <div>
                                <span class="font-semibold text-neutral-700"
                                    >Date</span
                                >
                                <span class="ml-1 text-neutral-600">{{
                                    orderInvoice.date
                                }}</span>
                            </div>
                            <div>
                                <span class="font-semibold text-neutral-700"
                                    >Serve By</span
                                >
                                <span class="ml-1 text-neutral-600">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div
                        class="border-t border-b border-neutral-300 dark:border-neutral-600"
                    >
                        <table class="w-full text-[11px]">
                            <thead>
                                <tr
                                    class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-100"
                                >
                                    <th
                                        class="w-7 px-3 py-2 text-left font-bold text-neutral-700"
                                    >
                                        #
                                    </th>
                                    <th
                                        class="px-3 py-2 text-left font-bold text-neutral-700"
                                    >
                                        Product
                                    </th>
                                    <th
                                        class="w-24 px-3 py-2 text-center font-bold text-neutral-700"
                                    >
                                        Quantity
                                    </th>
                                    <th
                                        class="w-24 px-3 py-2 text-right font-bold text-neutral-700"
                                    >
                                        Unit Price
                                    </th>
                                    <th
                                        class="w-20 px-3 py-2 text-right font-bold text-neutral-700"
                                    >
                                        Discount
                                    </th>
                                    <th
                                        class="w-24 px-3 py-2 text-right font-bold text-neutral-700"
                                    >
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, index) in orderInvoice.items"
                                    :key="item.name"
                                    class="border-b border-neutral-100 last:border-0"
                                >
                                    <td class="px-3 py-2.5 text-neutral-500">
                                        {{ index + 1 }}
                                    </td>
                                    <td
                                        class="px-3 py-2.5 leading-snug font-medium text-neutral-800"
                                    >
                                        {{ item.name }}
                                        <span
                                            class="block text-[10px] font-normal text-neutral-400"
                                            >1 Pc(s)</span
                                        >
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-center text-neutral-700"
                                    >
                                        {{ item.quantity }} Pc(s)
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-neutral-700"
                                    >
                                        {{
                                            Number(item.price).toLocaleString(
                                                undefined,
                                                {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                },
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-neutral-700"
                                    >
                                        0.00
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono font-semibold text-neutral-800"
                                    >
                                        {{
                                            Number(item.total).toLocaleString(
                                                undefined,
                                                {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                },
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Summary -->
                    <div
                        class="grid grid-cols-2 gap-x-8 px-10 py-5 text-[11px]"
                    >
                        <!-- Left: Payment rows -->
                        <div class="space-y-1.5">
                            <div class="flex justify-between">
                                <span class="font-semibold text-neutral-700">{{
                                    orderInvoice.paymentMethod
                                }}</span>
                                <span class="font-mono text-neutral-700">
                                    ৳
                                    {{
                                        Number(
                                            orderInvoice.grandTotal,
                                        ).toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })
                                    }}
                                </span>
                            </div>
                            <div class="text-[10px] text-neutral-500">
                                {{ orderInvoice.date }}
                            </div>
                            <div class="mt-2 flex justify-between">
                                <span class="font-bold text-neutral-800"
                                    >Total Paid</span
                                >
                                <span
                                    class="font-mono font-bold text-neutral-800"
                                >
                                    ৳
                                    {{
                                        Number(
                                            orderInvoice.grandTotal,
                                        ).toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })
                                    }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-bold text-neutral-800"
                                    >Total Due</span
                                >
                                <span
                                    class="font-mono font-bold text-neutral-800"
                                    >৳ 0.00</span
                                >
                            </div>
                        </div>

                        <!-- Right: Quantity & Net Total -->
                        <div
                            class="space-y-1.5 border-l border-neutral-200 pl-8 text-right"
                        >
                            <div class="flex justify-between">
                                <span class="text-neutral-600"
                                    >Total quantity</span
                                >
                                <span class="font-semibold text-neutral-800">
                                    {{
                                        orderInvoice.items.reduce(
                                            (s, i) => s + i.quantity,
                                            0,
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-600"
                                    >Total Items</span
                                >
                                <span class="font-semibold text-neutral-800">{{
                                    orderInvoice.items.length
                                }}</span>
                            </div>
                            <div
                                v-if="orderInvoice.discount > 0"
                                class="flex justify-between"
                            >
                                <span class="text-neutral-600">Discount</span>
                                <span
                                    class="font-mono font-semibold text-neutral-800"
                                >
                                    ৳
                                    {{
                                        Number(
                                            orderInvoice.discount,
                                        ).toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })
                                    }}
                                </span>
                            </div>
                            <div
                                class="mt-1 flex justify-between border-t border-neutral-300 pt-1.5"
                            >
                                <span class="font-bold text-neutral-800"
                                    >Net Total:</span
                                >
                                <span
                                    class="font-mono font-bold text-neutral-800"
                                >
                                    ৳
                                    {{
                                        Number(
                                            orderInvoice.grandTotal,
                                        ).toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div
                        class="flex justify-center border-t border-neutral-200 pb-8"
                    >
                        <div class="flex flex-col items-center gap-2 pt-5">
                            <img
                                :src="`https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(orderInvoice.invoiceNo + '-' + orderInvoice.orderNo)}&size=100x100&margin=4`"
                                alt="Invoice QR Code"
                                class="h-24 w-24"
                                loading="lazy"
                            />
                            <span
                                class="font-mono text-[9px] text-neutral-400"
                                >{{ orderInvoice.invoiceNo }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Dynamic Storefront Footer -->
        <Footer v-model:viewMode="viewMode" />

        <CartDrawer
            v-if="isCartEnabled"
            v-model:cartOpen="cartOpen"
            v-model:couponInput="couponInput"
            :cart="cart"
            :cartQuantity="cartQuantity"
            :cartSubtotal="cartSubtotal"
            :discountAmount="discountAmount"
            :shippingFee="shippingFee"
            :cartTotal="cartTotal"
            :appliedCoupon="appliedCoupon"
            :couponError="couponError"
            :couponSuccess="couponSuccess"
            :isShipmentEnabled="isShipmentEnabled"
            @updateCartQuantity="updateCartQuantity"
            @removeFromCart="removeFromCart"
            @applyCoupon="applyCoupon"
            @removeCoupon="removeCoupon"
            @proceedToCheckout="proceedToCheckout"
        />

        <!-- QUICK PRODUCT DETAILS VIEW DRAWER (PDP - Section 3.2 storefront) -->
        <div
            v-if="selectedProduct"
            class="fixed inset-0 z-50 overflow-hidden"
            role="dialog"
            aria-modal="true"
        >
            <div class="absolute inset-0 overflow-hidden">
                <div
                    @click="selectedProduct = null"
                    class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs transition-opacity"
                ></div>

                <div
                    class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10"
                >
                    <div class="pointer-events-auto w-screen max-w-2xl">
                        <div
                            class="flex h-full flex-col bg-white shadow-2xl dark:bg-neutral-900"
                        >
                            <!-- Header -->
                            <div
                                class="flex items-center justify-between border-b border-neutral-100 px-6 py-5 dark:border-neutral-800"
                            >
                                <h2 class="text-sm font-bold tracking-tight">
                                    Product Details
                                </h2>
                                <button
                                    @click="selectedProduct = null"
                                    class="rounded-lg p-1 text-neutral-400 transition hover:text-neutral-600 dark:hover:text-white"
                                >
                                    <X class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Body (Responsive layout details page) -->
                            <div class="flex-1 space-y-6 overflow-y-auto p-8">
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <!-- Image Block -->
                                    <div
                                        :class="selectedProduct.imageGradient"
                                        class="flex aspect-square w-full shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr text-6xl font-bold text-white/20 shadow-md"
                                    >
                                        {{
                                            selectedProduct.name
                                                .split(' ')
                                                .map((w) => w[0])
                                                .join('')
                                        }}
                                    </div>

                                    <!-- Purchase details panel -->
                                    <div class="space-y-4">
                                        <div class="space-y-1">
                                            <span
                                                class="text-xs font-semibold tracking-wider text-emerald-500 uppercase"
                                                >{{
                                                    selectedProduct.category
                                                }}</span
                                            >
                                            <h1
                                                class="text-xl leading-tight font-bold tracking-tight text-neutral-900 dark:text-white"
                                            >
                                                {{ selectedProduct.name }}
                                            </h1>
                                        </div>

                                        <!-- Rating -->
                                        <div
                                            class="flex items-center gap-1 text-xs text-amber-500"
                                        >
                                            <Star
                                                class="h-3.5 w-3.5 fill-current"
                                            />
                                            <span class="font-bold">{{
                                                selectedProduct.rating
                                            }}</span>
                                            <span class="text-neutral-400"
                                                >({{
                                                    selectedProduct.reviewsCount
                                                }}
                                                verified customer reviews)</span
                                            >
                                        </div>

                                        <!-- Price -->
                                        <div class="flex items-baseline gap-2">
                                            <span
                                                class="font-mono text-xl font-bold text-neutral-900 dark:text-white"
                                            >
                                                {{
                                                    $page.props
                                                        .currency_symbol ?? '$'
                                                }}{{
                                                    selectedProduct.price.toFixed(
                                                        2,
                                                    )
                                                }}
                                            </span>
                                            <span
                                                v-if="
                                                    selectedProduct.originalPrice
                                                "
                                                class="font-mono text-xs text-neutral-400 line-through"
                                            >
                                                {{
                                                    $page.props
                                                        .currency_symbol ?? '$'
                                                }}{{
                                                    selectedProduct.originalPrice.toFixed(
                                                        2,
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <!-- Stock status indicator -->
                                        <div>
                                            <span
                                                v-if="selectedProduct.stock > 5"
                                                class="inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-bold text-green-600 dark:bg-green-950 dark:text-green-400"
                                            >
                                                In Stock ({{
                                                    selectedProduct.stock
                                                }}
                                                available)
                                            </span>
                                            <span
                                                v-else-if="
                                                    selectedProduct.stock > 0
                                                "
                                                class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                                            >
                                                Low Stock (Only
                                                {{
                                                    selectedProduct.stock
                                                }}
                                                left!)
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-bold text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400"
                                            >
                                                Out of Stock
                                            </span>
                                        </div>

                                        <p
                                            class="text-xs leading-relaxed text-neutral-500 dark:text-neutral-400"
                                        >
                                            {{ selectedProduct.description }}
                                        </p>

                                        <!-- Actions -->
                                        <div class="flex gap-3 pt-4">
                                            <button
                                                v-if="isCartEnabled"
                                                @click="
                                                    addToCart(selectedProduct);
                                                    selectedProduct = null;
                                                "
                                                :disabled="
                                                    selectedProduct.stock === 0
                                                "
                                                class="flex h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-600 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:bg-neutral-200 disabled:text-neutral-500"
                                            >
                                                <ShoppingCart class="h-4 w-4" />
                                                Add to Shopping Cart
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
            class="animate-fade-in fixed right-6 bottom-6 z-50 flex items-center gap-2 rounded-xl bg-neutral-900 px-4 py-3 text-xs font-bold text-white shadow-xl dark:bg-neutral-100 dark:text-neutral-900"
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
    0%,
    100% {
        transform: translateY(-25%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
</style>
