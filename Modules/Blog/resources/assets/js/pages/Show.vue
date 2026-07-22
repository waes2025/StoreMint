<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock,
    ChevronRight,
    Leaf,
    Pencil,
    Phone,
    Mail,
    Globe,
    ChevronDown,
    Check,
    Sun,
    Monitor,
    Moon,
    Lock,
    Tag,
    Flame,
    FileText,
    Ticket,
    ShoppingBag,
} from '@lucide/vue';
import Footer from '@/components/Footer.vue';
import { route } from '@/lib/route';
import { useAppearance } from '@/composables/useAppearance';

interface Post {
    id: number;
    slug: string;
    title: string;
    excerpt: string;
    content: string;
    category: string;
    author: string;
    date: string;
    read_time: string;
    image: string;
    is_published: boolean;
}

interface Product {
    id: number;
    name: string;
    slug: string | null;
    price: number;
    compare_at_price: number | null;
    image: string;
    category: string;
    stock_status: string;
    is_best_seller: boolean;
}

interface CouponItem {
    id: number;
    code: string;
    description: string;
    discount_type: string;
    discount_value: number;
    discount_display: string;
    min_order_amount: number | null;
    expires_at: string | null;
}

defineProps<{
    post: Post;
    recentPosts?: Post[];
    activeCoupons?: CouponItem[];
    bestSellingProducts?: Product[];
}>();

const page = usePage();
const { appearance, updateAppearance } = useAppearance();

// Language setup
const selectedLang = ref('English');
const langOpen = ref(false);
const languages = ['English', 'Spanish', 'French', 'German', 'Bengali'];

const isShopEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Shop');
});

const isBlogEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Blog');
});

const dashboardUrl = computed(() =>
    page.props.currentTeam
        ? route('dashboard', page.props.currentTeam.slug).url
        : null,
);
</script>

<template>
    <Head :title="`${post.title} - StoreMint Blog`" />

    <div class="min-h-screen bg-neutral-50 text-neutral-800 transition-colors duration-300 dark:bg-neutral-950 dark:text-neutral-200 flex flex-col justify-between">
        <div>
            <!-- 1. TOP PERSISTENT STOREFRONT GREEN BAR -->
            <div
                class="sticky top-0 z-50 flex flex-wrap items-center justify-between border-b border-emerald-500/20 bg-emerald-900 px-4 py-2 text-xs font-medium text-white shadow-xs dark:bg-emerald-950"
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
                    <div class="relative">
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
                    <div class="flex items-center gap-0.5 rounded-lg border border-emerald-500/20 bg-emerald-950 p-0.5 shadow-xs">
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

                    <!-- Auth Status -->
                    <template v-if="$page.props.auth.user">
                        <span class="text-[11px] font-medium text-emerald-100">
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

            <!-- 2. STOREFRONT MAIN NAVIGATION BAR -->
            <header
                class="sticky top-10 z-40 h-20 border-b border-neutral-200/80 bg-white/90 backdrop-blur-md dark:border-neutral-800 dark:bg-neutral-900/90"
            >
                <div class="mx-auto flex h-full max-w-[1400px] items-center justify-between px-6">
                    <!-- Logo -->
                    <Link href="/" class="flex cursor-pointer items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-400 to-emerald-600 text-white shadow-md shadow-emerald-500/20">
                            <Leaf class="h-5 w-5" />
                        </div>
                        <span class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            Store<span class="text-emerald-500">Mint</span>
                        </span>
                    </Link>

                    <!-- Navigation links -->
                    <nav class="hidden items-center gap-8 text-sm font-medium text-neutral-600 md:flex dark:text-neutral-400">
                        <Link href="/" class="cursor-pointer py-1 transition hover:text-emerald-500">
                            Home
                        </Link>
                        <Link v-if="isShopEnabled" href="/shop" class="cursor-pointer py-1 transition hover:text-emerald-500">
                            Shop All
                        </Link>
                        <Link v-if="isBlogEnabled" href="/blogs" class="cursor-pointer py-1 font-bold text-emerald-600 dark:text-emerald-400">
                            Blog
                        </Link>
                        <Link v-if="$page.props.auth.user" :href="dashboardUrl || '/dashboard'" class="cursor-pointer py-1 transition hover:text-emerald-500">
                            Dashboard
                        </Link>
                    </nav>

                    <!-- Header Right Actions -->
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('blog.edit', post.slug).url"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 text-xs font-bold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                            Edit Article
                        </Link>
                    </div>
                </div>
            </header>

            <!-- 3. THREE-COLUMN ARTICLE & SIDEBAR CONTAINER -->
            <main class="mx-auto max-w-[1400px] px-4 py-8 pb-16 sm:px-6 lg:px-8">
                <!-- Breadcrumbs & Back Bar -->
                <div class="mb-6 flex items-center justify-between">
                    <Link
                        :href="route('blog.index').url"
                        class="inline-flex items-center gap-2 text-xs font-bold text-neutral-600 transition-colors hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to all publications
                    </Link>

                    <nav class="hidden sm:flex items-center gap-1.5 text-xs text-neutral-400 font-medium">
                        <Link :href="route('blog.index').url" class="hover:text-neutral-600 dark:hover:text-neutral-200">Blog</Link>
                        <ChevronRight class="h-3 w-3" />
                        <span class="font-semibold text-neutral-600 dark:text-neutral-300 truncate max-w-[180px]">{{ post.category }}</span>
                    </nav>
                </div>

                <!-- 3 COLUMNS GRID -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- LEFT COLUMN (3 cols): Web Banner & Best Selling Products -->
                    <aside class="lg:col-span-3 space-y-6">
                        <!-- Web Banner Widget -->
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-900 p-5 text-white shadow-md">
                            <div class="absolute -right-6 -bottom-6 h-28 w-28 rounded-full bg-emerald-400/20 blur-2xl"></div>
                            <div class="relative z-10 space-y-3">
                                <span class="inline-block rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider backdrop-blur-xs">
                                    Special Offer
                                </span>
                                <h3 class="text-base font-extrabold leading-snug">
                                    Upgrade Your Shopping Experience
                                </h3>
                                <p class="text-xs text-emerald-100/90 leading-relaxed">
                                    Discover top deals, exclusive discounts, and fresh organic picks on StoreMint today!
                                </p>
                                <Link
                                    v-if="isShopEnabled"
                                    href="/shop"
                                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs font-bold text-emerald-800 shadow-xs transition hover:bg-emerald-50 active:scale-95"
                                >
                                    <ShoppingBag class="h-3.5 w-3.5" />
                                    <span>Shop Collections</span>
                                    <ChevronRight class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>

                        <!-- Best Selling Products Widget -->
                        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                            <div class="mb-4 flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                                <div class="flex items-center gap-2">
                                    <Flame class="h-4 w-4 text-amber-500" />
                                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white">
                                        Best Selling
                                    </h3>
                                </div>
                                <Link v-if="isShopEnabled" href="/shop" class="text-[11px] font-bold text-emerald-600 hover:underline dark:text-emerald-400">
                                    View All
                                </Link>
                            </div>

                            <div v-if="bestSellingProducts && bestSellingProducts.length" class="space-y-3">
                                <div
                                    v-for="product in bestSellingProducts"
                                    :key="product.id"
                                    class="group flex items-center gap-3 rounded-xl p-1.5 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-800/60"
                                >
                                    <img
                                        :src="product.image || '/modules/shop/images/placeholder.png'"
                                        :alt="product.name"
                                        class="h-12 w-12 rounded-lg object-cover border border-neutral-100 dark:border-neutral-800 shrink-0"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">
                                            {{ product.category }}
                                        </span>
                                        <h4 class="truncate text-xs font-bold text-neutral-800 transition-colors group-hover:text-emerald-600 dark:text-neutral-200 dark:group-hover:text-emerald-400">
                                            {{ product.name }}
                                        </h4>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-xs font-extrabold text-neutral-900 dark:text-white">
                                                ${{ Number(product.price).toFixed(2) }}
                                            </span>
                                            <span v-if="product.compare_at_price && product.compare_at_price > product.price" class="text-[10px] text-neutral-400 line-through">
                                                ${{ Number(product.compare_at_price).toFixed(2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-4 text-center text-xs text-neutral-400">
                                No best sellers available.
                            </div>
                        </div>
                    </aside>

                    <!-- CENTER COLUMN (6 cols): The Article -->
                    <div class="lg:col-span-6">
                        <article class="overflow-hidden rounded-3xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                            <!-- Featured Image -->
                            <div class="relative aspect-[16/9] border-b border-neutral-100 bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 overflow-hidden">
                                <img
                                    :src="post.image"
                                    :alt="post.title"
                                    class="h-full w-full object-cover"
                                />
                            </div>

                            <!-- Content Area -->
                            <div class="p-5 sm:p-8 space-y-6">
                                <!-- Metadata Header -->
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-extrabold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            <Tag class="h-3.5 w-3.5" />
                                            {{ post.category }}
                                        </span>
                                        <span
                                            v-if="$page.props.auth.user && !post.is_published"
                                            class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300"
                                        >
                                            Draft
                                        </span>
                                    </div>

                                    <h1 class="text-2xl font-extrabold tracking-tight text-neutral-900 sm:text-3xl leading-snug dark:text-white">
                                        {{ post.title }}
                                    </h1>

                                    <div class="flex flex-wrap items-center gap-y-2 gap-x-4 border-b border-neutral-100 pb-4 pt-1 text-xs font-medium text-neutral-500 dark:border-neutral-800">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-[11px] font-bold text-white">
                                                {{ post.author.charAt(0) }}
                                            </div>
                                            <span class="font-bold text-neutral-700 dark:text-neutral-300">
                                                {{ post.author }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-3.5 w-3.5 text-emerald-500" />
                                            {{ post.date }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Clock class="h-3.5 w-3.5 text-emerald-500" />
                                            {{ post.read_time }} read
                                        </div>
                                    </div>
                                </div>

                                <!-- Content Body (HTML Rendered) -->
                                <div
                                    class="text-sm leading-relaxed text-neutral-700 dark:text-neutral-300 space-y-4 font-normal [&_h1]:text-2xl [&_h1]:font-extrabold [&_h1]:text-neutral-900 dark:[&_h1]:text-white [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-neutral-900 dark:[&_h2]:text-white [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-neutral-900 dark:[&_h3]:text-white [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_blockquote]:border-l-4 [&_blockquote]:border-emerald-500 [&_blockquote]:pl-4 [&_blockquote]:italic [&_blockquote]:text-neutral-600 dark:[&_blockquote]:text-neutral-400 [&_img]:max-w-full [&_img]:rounded-xl [&_a]:text-emerald-600 [&_a]:underline dark:[&_a]:text-emerald-400"
                                    v-html="post.content"
                                ></div>
                            </div>
                        </article>
                    </div>

                    <!-- RIGHT COLUMN (3 cols): Last 5 Blog Posts & Last 5 Active Coupons -->
                    <aside class="lg:col-span-3 space-y-6">
                        <!-- Last 5 Blog Posts Widget -->
                        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                            <div class="mb-4 flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                                <div class="flex items-center gap-2">
                                    <FileText class="h-4 w-4 text-emerald-500" />
                                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white">
                                        Latest Posts
                                    </h3>
                                </div>
                                <Link :href="route('blog.index').url" class="text-[11px] font-bold text-emerald-600 hover:underline dark:text-emerald-400">
                                    View All
                                </Link>
                            </div>

                            <div v-if="recentPosts && recentPosts.length" class="space-y-3">
                                <Link
                                    v-for="item in recentPosts"
                                    :key="item.id"
                                    :href="route('blog.show', item.slug).url"
                                    class="group flex items-start gap-3 rounded-xl p-1.5 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-800/60"
                                >
                                    <img
                                        :src="item.image"
                                        :alt="item.title"
                                        class="h-12 w-12 rounded-lg object-cover border border-neutral-100 dark:border-neutral-800 shrink-0"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                            {{ item.category }}
                                        </span>
                                        <h4 class="line-clamp-2 text-xs font-bold text-neutral-800 transition-colors group-hover:text-emerald-600 dark:text-neutral-200 dark:group-hover:text-emerald-400">
                                            {{ item.title }}
                                        </h4>
                                        <span class="mt-0.5 block text-[10px] text-neutral-400">
                                            {{ item.date }}
                                        </span>
                                    </div>
                                </Link>
                            </div>
                            <div v-else class="py-4 text-center text-xs text-neutral-400">
                                No recent posts available.
                            </div>
                        </div>

                        <!-- Last 5 Active Coupons & Discounts Widget -->
                        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                            <div class="mb-4 flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                                <div class="flex items-center gap-2">
                                    <Ticket class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white">
                                        Active Coupons
                                    </h3>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    Discounts
                                </span>
                            </div>

                            <div v-if="activeCoupons && activeCoupons.length" class="space-y-3">
                                <div
                                    v-for="coupon in activeCoupons"
                                    :key="coupon.id"
                                    class="relative overflow-hidden rounded-xl border border-dashed border-emerald-300/80 bg-emerald-50/40 p-3 transition-all hover:bg-emerald-50 dark:border-emerald-700/50 dark:bg-emerald-950/30 dark:hover:bg-emerald-950/50"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="inline-block rounded bg-emerald-600 px-2 py-0.5 text-[11px] font-extrabold text-white shadow-2xs">
                                                {{ coupon.discount_display }}
                                            </span>
                                            <h4 class="mt-1.5 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                                Code: <span class="font-mono text-emerald-700 dark:text-emerald-400 select-all font-extrabold">{{ coupon.code }}</span>
                                            </h4>
                                            <p class="mt-1 line-clamp-1 text-[11px] text-neutral-500 dark:text-neutral-400">
                                                {{ coupon.description }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2.5 flex items-center justify-between border-t border-emerald-200/50 pt-2 text-[10px] text-neutral-400 dark:border-emerald-800/40">
                                        <span v-if="coupon.min_order_amount">
                                            Min order: ${{ Number(coupon.min_order_amount).toFixed(2) }}
                                        </span>
                                        <span v-else>
                                            No min order
                                        </span>
                                        <span v-if="coupon.expires_at" class="font-medium text-amber-600 dark:text-amber-400">
                                            Exp: {{ coupon.expires_at }}
                                        </span>
                                        <span v-else class="font-medium text-emerald-600 dark:text-emerald-400">
                                            No Expiry
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-4 text-center text-xs text-neutral-400">
                                No active coupons available.
                            </div>
                        </div>
                    </aside>

                </div>
            </main>
        </div>

        <Footer />
    </div>
</template>
