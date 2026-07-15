<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, User, Clock, ChevronRight } from '@lucide/vue';
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
</script>

<template>
    <Head :title="post.title" />

    <div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8 pb-16">
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

                    <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-neutral-500 font-medium pt-2 border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
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
    </div>
</template>
