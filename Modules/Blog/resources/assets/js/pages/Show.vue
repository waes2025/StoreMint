<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, User, Clock, ChevronRight, Leaf } from '@lucide/vue';
import Footer from '@/components/Footer.vue';
import { route } from '@/lib/route';

interface Post {
    id: number;
    title: string;
    excerpt: string;
    content: string;
    category: string;
    author: string;
    date: string;
    read_time: string;
    image: string;
}

defineProps<{
    post: Post;
}>();

const page = usePage();

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
    <Head :title="post.title" />

    <div class="min-h-screen bg-neutral-50 dark:bg-neutral-950 flex flex-col justify-between">
        <div>
            <!-- 80px HEADER BAR -->
            <header
                class="sticky top-0 z-40 h-20 border-b border-neutral-200/80 bg-white/90 backdrop-blur-md dark:border-neutral-800 dark:bg-neutral-900/90"
            >
                <div
                    class="mx-auto flex h-full max-w-[1280px] items-center justify-between px-6"
                >
                    <!-- Logo -->
                    <Link
                        href="/"
                        class="flex cursor-pointer items-center gap-2"
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
                    </Link>

                    <!-- Navigation links -->
                    <nav
                        class="hidden items-center gap-8 text-sm font-medium text-neutral-600 md:flex dark:text-neutral-400"
                    >
                        <Link
                            href="/"
                            class="cursor-pointer py-1 transition hover:text-emerald-500"
                        >
                            Home
                        </Link>
                        <Link
                            v-if="isShopEnabled"
                            href="/shop"
                            class="cursor-pointer py-1 transition hover:text-emerald-500"
                        >
                            Shop All
                        </Link>
                        <Link
                            v-if="isBlogEnabled"
                            href="/blogs"
                            class="cursor-pointer py-1 font-bold text-emerald-600 dark:text-emerald-400"
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
                        <template v-if="$page.props.auth.user">
                            <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">
                                Hello, {{ $page.props.auth.user.first_name }}
                            </span>
                            <Link
                                :href="route('logout').url"
                                method="post"
                                as="button"
                                class="rounded-lg bg-neutral-100 dark:bg-neutral-800 px-3 py-1.5 text-xs font-bold text-neutral-700 dark:text-neutral-350 hover:bg-neutral-200 dark:hover:bg-neutral-750 transition"
                            >
                                Log out
                            </Link>
                        </template>
                        <Link
                            v-else
                            :href="route('login').url"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 text-xs font-bold text-white shadow-xs transition"
                        >
                            Log In
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8 pb-16">
                <!-- Breadcrumbs & Back -->
                <div class="flex items-center justify-between mb-8">
                    <Link
                        :href="route('blog.index').url"
                        class="inline-flex items-center gap-2 text-xs font-bold text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to publications
                    </Link>

                    <nav class="hidden sm:flex items-center gap-1.5 text-xs text-neutral-400 font-medium">
                        <Link :href="route('blog.index').url" class="hover:text-neutral-600 dark:hover:text-neutral-200">Blog</Link>
                        <ChevronRight class="h-3 w-3" />
                        <span class="text-neutral-600 dark:text-neutral-300 font-semibold truncate max-w-[180px]">{{ post.category }}</span>
                    </nav>
                </div>

                <!-- Article Container -->
                <article class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-150 dark:border-neutral-800 overflow-hidden shadow-xs">
                    <!-- Featured Image -->
                    <div class="relative aspect-[21/9] bg-neutral-150 dark:bg-neutral-950 overflow-hidden border-b border-neutral-100 dark:border-neutral-800">
                        <img
                            :src="post.image"
                            :alt="post.title"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <!-- Content Area -->
                    <div class="p-6 sm:p-10 space-y-8">
                        <!-- Metadata -->
                        <div class="space-y-4">
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                {{ post.category }}
                            </span>

                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white leading-tight">
                                {{ post.title }}
                            </h1>

                            <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-neutral-550 font-medium pt-2 border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">
                                        {{ post.author.charAt(0) }}
                                    </div>
                                    <span class="font-semibold text-neutral-700 dark:text-neutral-300">
                                        {{ post.author }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Calendar class="h-4 w-4" />
                                    {{ post.date }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <Clock class="h-4 w-4" />
                                    {{ post.read_time }} read
                                </div>
                            </div>
                        </div>

                        <!-- Content Body -->
                        <div class="prose dark:prose-invert max-w-none text-neutral-700 dark:text-neutral-350 text-sm leading-relaxed whitespace-pre-line space-y-4">
                            {{ post.content }}
                        </div>
                    </div>
                </article>
            </main>
        </div>

        <Footer />
    </div>
</template>
