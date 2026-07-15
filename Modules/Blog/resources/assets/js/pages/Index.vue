<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Search, Calendar, User, Clock, ArrowRight, BookOpen } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
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

const props = defineProps<{
    posts: Post[];
}>();

const searchQuery = ref('');
const selectedCategory = ref('All');

const categories = computed(() => {
    const list = new Set(props.posts.map(p => p.category));
    return ['All', ...Array.from(list)];
});

const filteredPosts = computed(() => {
    return props.posts.filter(post => {
        const matchesCategory = selectedCategory.value === 'All' || post.category === selectedCategory.value;
        const matchesSearch = post.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            post.excerpt.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});
</script>

<template>
    <Head title="StoreMint Blog" />

    <div class="space-y-8 pb-12 px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <Heading
                title="StoreMint Publications"
                description="Explore standard guidelines, updates, commerce trends, and business strategy articles."
            />
        </div>

        <!-- Filter & Search Bar -->
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-center bg-white dark:bg-neutral-900 p-4 rounded-xl border border-neutral-150 dark:border-neutral-800">
            <!-- Categories -->
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="selectedCategory = cat"
                    :class="[
                        'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all',
                        selectedCategory === cat
                            ? 'bg-emerald-600 text-white shadow-sm dark:bg-emerald-500'
                            : 'bg-neutral-100 hover:bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:hover:bg-neutral-750 dark:text-neutral-300'
                    ]"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Search input -->
            <div class="relative w-full sm:w-72">
                <Search class="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Search articles..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-950 dark:focus:bg-neutral-900"
                />
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredPosts.length === 0" class="text-center py-16 bg-white dark:bg-neutral-900 rounded-xl border border-neutral-150 dark:border-neutral-800">
            <BookOpen class="h-12 w-12 mx-auto text-neutral-300 dark:text-neutral-700 mb-4" />
            <h3 class="text-sm font-bold text-neutral-800 dark:text-neutral-200">No articles found</h3>
            <p class="text-xs text-neutral-500 mt-1">Try resetting your search query or filters.</p>
        </div>

        <!-- Blog Grid -->
        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="post in filteredPosts"
                :key="post.id"
                class="flex flex-col bg-white dark:bg-neutral-900 rounded-xl border border-neutral-150 dark:border-neutral-800 overflow-hidden shadow-xs hover:shadow-md transition-all group"
            >
                <div class="relative aspect-video bg-neutral-100 dark:bg-neutral-950 overflow-hidden">
                    <img
                        :src="post.image"
                        :alt="post.title"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-350"
                    />
                    <span class="absolute top-3 left-3 bg-white/90 dark:bg-neutral-900/90 backdrop-blur-xs px-2.5 py-1 rounded-md text-[10px] font-bold text-emerald-600 dark:text-emerald-400 border border-neutral-100 dark:border-neutral-800 shadow-xs">
                        {{ post.category }}
                    </span>
                </div>

                <div class="flex-1 p-5 flex flex-col justify-between space-y-4">
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

                        <h4 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 line-clamp-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                            {{ post.title }}
                        </h4>

                        <p class="text-xs text-neutral-550 dark:text-neutral-400 line-clamp-3">
                            {{ post.excerpt }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-neutral-100 dark:border-neutral-800/80">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">
                                {{ post.author.charAt(0) }}
                            </div>
                            <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">
                                {{ post.author }}
                            </span>
                        </div>

                        <Link
                            :href="route('blog.show', post.id).url"
                            class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors"
                        >
                            Read Article
                            <ArrowRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
