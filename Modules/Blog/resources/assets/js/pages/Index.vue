<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Search,
    Calendar,
    Clock,
    ArrowRight,
    BookOpen,
    Leaf,
    Pencil,
    Plus,
    Phone,
    Mail,
    Globe,
    ChevronDown,
    Check,
    Sun,
    Monitor,
    Moon,
    Lock,
    Sparkles,
    Tag,
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

const props = defineProps<{
    posts: Post[];
}>();

const page = usePage();
const { appearance, updateAppearance } = useAppearance();

// Language dropdown setup
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

const searchQuery = ref('');
const selectedCategory = ref('All');

const categories = computed(() => {
    const list = new Set(props.posts.map(p => p.category));
    return ['All', ...Array.from(list)];
});

const filteredPosts = computed(() => {
    return props.posts.filter(post => {
        const matchesCategory = selectedCategory.value === 'All' || post.category === selectedCategory.value;
        const matchesSearch =
            post.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            post.excerpt.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            post.category.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            post.author.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});

const featuredPost = computed(() => {
    return props.posts.find(p => p.is_published) || props.posts[0] || null;
});

const isFeaturedBannerVisible = computed(() => {
    return featuredPost.value && selectedCategory.value === 'All' && !searchQuery.value.trim();
});

const displayPosts = computed(() => {
    if (isFeaturedBannerVisible.value && featuredPost.value) {
        return filteredPosts.value.filter(p => p.id !== featuredPost.value?.id);
    }
    return filteredPosts.value;
});

const getPostUrl = (post?: Post | null) => {
    if (!post || !post.slug) return '/blogs';
    try {
        const res = route('blog.show', post.slug);
        return typeof res === 'string' ? res : (res?.url || `/blogs/${post.slug}`);
    } catch {
        return `/blogs/${post.slug}`;
    }
};
</script>

<template>
    <Head title="StoreMint Blog - Articles & Storefront Insights" />

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
                <div class="mx-auto flex h-full max-w-[1280px] items-center justify-between px-6">
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
                            :href="route('blog.create').url"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 text-xs font-bold text-white shadow-xs transition hover:bg-emerald-700"
                        >
                            <Plus class="h-4 w-4" />
                            New Post
                        </Link>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('blog.adminIndex').url"
                            class="inline-flex h-9 items-center justify-center rounded-lg border border-neutral-200 bg-white px-3 text-xs font-bold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                        >
                            Admin Blog Panel
                        </Link>
                    </div>
                </div>
            </header>

            <!-- 3. STOREFRONT HERO BANNER -->
            <div class="relative overflow-hidden border-b border-neutral-200/80 bg-gradient-to-b from-emerald-900/10 via-neutral-50 to-neutral-50 px-6 py-14 dark:border-neutral-800 dark:from-emerald-950/30 dark:via-neutral-950 dark:to-neutral-950">
                <div class="mx-auto max-w-[1280px] space-y-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                        <Sparkles class="h-3.5 w-3.5 text-emerald-500" />
                        <span>StoreMint Publications & Insights</span>
                    </div>

                    <div class="max-w-2xl space-y-3">
                        <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 sm:text-4xl dark:text-white">
                            Commerce Trends, Architecture & Growth Strategies
                        </h1>
                        <p class="text-sm leading-relaxed text-neutral-600 dark:text-neutral-400">
                            Discover standard guidelines, multi-tenant engineering deep dives, and conversion optimization articles curated for StoreMint merchants.
                        </p>
                    </div>

                    <!-- Search & Categories Bar -->
                    <div class="flex flex-col gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative w-full sm:w-80">
                            <Search class="absolute left-3.5 top-3 h-4 w-4 text-neutral-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search articles, topics, or authors..."
                                class="h-10 w-full rounded-xl border border-neutral-200 bg-white pl-10 pr-4 text-xs text-neutral-800 shadow-xs outline-none transition focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                            />
                        </div>

                        <!-- Category Pill Filters -->
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                v-for="cat in categories"
                                :key="cat"
                                @click="selectedCategory = cat"
                                :class="[
                                    'rounded-lg px-3 py-1.5 text-xs font-bold transition-all cursor-pointer',
                                    selectedCategory === cat
                                        ? 'bg-emerald-600 text-white shadow-xs dark:bg-emerald-500'
                                        : 'bg-white text-neutral-600 border border-neutral-200 hover:bg-neutral-100 dark:bg-neutral-900 dark:border-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-800'
                                ]"
                            >
                                {{ cat }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. MAIN VIEW CONTAINER -->
            <main class="mx-auto max-w-[1280px] px-6 py-12 space-y-12">
                <!-- Featured Post Banner (when viewing 'All' and searchQuery is empty) -->
                <div v-if="isFeaturedBannerVisible" class="group overflow-hidden rounded-3xl border border-neutral-200 bg-white shadow-sm transition hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="grid gap-0 lg:grid-cols-12">
                        <Link
                            :href="getPostUrl(featuredPost)"
                            class="relative aspect-video lg:aspect-auto lg:col-span-7 bg-neutral-100 dark:bg-neutral-950 overflow-hidden block cursor-pointer"
                        >
                            <img
                                :src="featuredPost!.image"
                                :alt="featuredPost!.title"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <span class="absolute left-4 top-4 rounded-md border border-neutral-100 bg-white/90 px-3 py-1 text-xs font-extrabold text-emerald-600 shadow-xs backdrop-blur-xs dark:border-neutral-800 dark:bg-neutral-900/90 dark:text-emerald-400">
                                Featured Article
                            </span>
                        </Link>

                        <div class="flex flex-col justify-between p-8 lg:col-span-5 space-y-6">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <Tag class="h-3.5 w-3.5" />
                                    <span>{{ featuredPost!.category }}</span>
                                </div>
                                <Link :href="getPostUrl(featuredPost)">
                                    <h2 class="text-xl font-extrabold tracking-tight text-neutral-900 transition hover:text-emerald-600 dark:text-white dark:hover:text-emerald-400 cursor-pointer">
                                        {{ featuredPost!.title }}
                                    </h2>
                                </Link>
                                <p class="text-xs leading-relaxed text-neutral-600 dark:text-neutral-400 line-clamp-3">
                                    {{ featuredPost!.excerpt }}
                                </p>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                                <div class="flex items-center justify-between text-xs text-neutral-500">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 font-extrabold text-white text-xs">
                                            {{ featuredPost!.author.charAt(0) }}
                                        </div>
                                        <span class="font-bold text-neutral-700 dark:text-neutral-300">{{ featuredPost!.author }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center gap-1"><Calendar class="h-3.5 w-3.5" /> {{ featuredPost!.date }}</span>
                                        <span class="flex items-center gap-1"><Clock class="h-3.5 w-3.5" /> {{ featuredPost!.read_time }}</span>
                                    </div>
                                </div>

                                <Link
                                    :href="getPostUrl(featuredPost)"
                                    class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-neutral-900 px-4 text-xs font-extrabold text-white transition hover:bg-neutral-800 dark:bg-emerald-600 dark:hover:bg-emerald-700 cursor-pointer"
                                >
                                    Read Featured Article
                                    <ArrowRight class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Articles Grid Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold tracking-tight text-neutral-900 dark:text-white">
                            {{ selectedCategory === 'All' ? 'All Articles' : `${selectedCategory} Articles` }}
                        </h3>
                        <p class="text-xs text-neutral-500">
                            Showing {{ filteredPosts.length }} {{ filteredPosts.length === 1 ? 'article' : 'articles' }}
                        </p>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredPosts.length === 0" class="rounded-3xl border border-dashed border-neutral-200 bg-white p-12 text-center dark:border-neutral-800 dark:bg-neutral-900">
                    <BookOpen class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-700 mb-4" />
                    <h3 class="text-sm font-bold text-neutral-800 dark:text-neutral-200">No publications found</h3>
                    <p class="mt-1 text-xs text-neutral-500">Try adjusting your search query or selected category filter.</p>
                </div>

                <!-- Articles Grid -->
                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="post in displayPosts"
                        :key="post.id"
                        class="group flex flex-col overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs transition hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <Link :href="getPostUrl(post)" class="relative aspect-video bg-neutral-100 dark:bg-neutral-950 overflow-hidden block cursor-pointer">
                            <img
                                :src="post.image"
                                :alt="post.title"
                                class="h-full w-full object-cover transition-transform duration-350 group-hover:scale-105"
                            />
                            <span class="absolute left-3 top-3 rounded-md border border-neutral-100 bg-white/90 px-2.5 py-1 text-[10px] font-bold text-emerald-600 shadow-xs backdrop-blur-xs dark:border-neutral-800 dark:bg-neutral-900/90 dark:text-emerald-400">
                                {{ post.category }}
                            </span>
                            <span
                                v-if="$page.props.auth.user && !post.is_published"
                                class="absolute right-3 top-3 rounded-md border border-amber-200 bg-amber-50/95 px-2.5 py-1 text-[10px] font-bold text-amber-700 shadow-xs dark:border-amber-900/60 dark:bg-amber-950/90 dark:text-amber-300"
                            >
                                Draft
                            </span>
                        </Link>

                        <div class="flex flex-1 flex-col justify-between p-5 space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-4 text-[10px] text-neutral-500">
                                    <span class="flex items-center gap-1">
                                        <Calendar class="h-3.5 w-3.5" />
                                        {{ post.date }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Clock class="h-3.5 w-3.5" />
                                        {{ post.read_time }}
                                    </span>
                                </div>

                                <Link :href="getPostUrl(post)">
                                    <h4 class="text-sm font-bold text-neutral-900 line-clamp-2 transition hover:text-emerald-600 dark:text-neutral-100 dark:hover:text-emerald-400 cursor-pointer">
                                        {{ post.title }}
                                    </h4>
                                </Link>

                                <p class="text-xs text-neutral-600 line-clamp-3 dark:text-neutral-400">
                                    {{ post.excerpt }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-neutral-100 dark:border-neutral-800/80">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">
                                        {{ post.author.charAt(0) }}
                                    </div>
                                    <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">
                                        {{ post.author }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <Link
                                        v-if="$page.props.auth.user"
                                        :href="route('blog.edit', post.slug).url"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-neutral-500 transition hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                        Edit
                                    </Link>
                                    <Link
                                        :href="getPostUrl(post)"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 cursor-pointer"
                                    >
                                        Read Article
                                        <ArrowRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <Footer />
    </div>
</template>
