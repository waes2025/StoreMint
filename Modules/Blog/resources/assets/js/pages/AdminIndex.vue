<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Eye,
    Trash2,
    Search,
    Filter,
    Calendar,
    User,
    Tag,
    X,
    RotateCcw,
    FileText,
    CheckCircle2,
    Clock,
    Sparkles,
} from '@lucide/vue';
import { route } from '@/lib/route';

interface Post {
    id: number;
    slug: string;
    title: string;
    excerpt: string;
    category: string;
    image: string;
    author: string;
    created_by: number | null;
    published: boolean;
    published_at: string | null;
    published_at_formatted: string | null;
    created_at: string;
    created_at_formatted: string;
    updated_at: string;
}

const props = defineProps<{
    posts: Post[];
    categories?: string[];
    authors?: string[];
}>();

defineOptions({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Blog',
            href: '/admin/blogs',
        },
    ],
});

// Filter States
const searchQuery = ref('');
const selectedCategory = ref('All');
const selectedAuthor = ref('All');
const selectedStatus = ref('All');
const selectedDatePreset = ref('all');
const customDate = ref('');

// Confirmation Modal State for Delete
const deletingPost = ref<Post | null>(null);

const confirmDelete = (post: Post) => {
    deletingPost.value = post;
};

const cancelDelete = () => {
    deletingPost.value = null;
};

const handleDelete = () => {
    if (!deletingPost.value) return;
    const postSlug = deletingPost.value.slug;
    deletingPost.value = null;

    router.delete(route('blog.destroy', postSlug).url + '?return_to_admin=1', {
        preserveScroll: true,
    });
};

// Distinct Categories list
const categoryOptions = computed(() => {
    if (props.categories && props.categories.length > 0) {
        const unique = new Set(['All', ...props.categories]);
        return Array.from(unique);
    }
    const set = new Set(props.posts.map(p => p.category));
    return ['All', ...Array.from(set)];
});

// Distinct Authors list
const authorOptions = computed(() => {
    if (props.authors && props.authors.length > 0) {
        const unique = new Set(['All', ...props.authors]);
        return Array.from(unique);
    }
    const set = new Set(props.posts.map(p => p.author));
    return ['All', ...Array.from(set)];
});

// Statistics
const totalPostsCount = computed(() => props.posts.length);
const publishedPostsCount = computed(() => props.posts.filter(p => p.published).length);
const draftPostsCount = computed(() => props.posts.filter(p => !p.published).length);
const categoriesCount = computed(() => new Set(props.posts.map(p => p.category)).size);

// Date helper for filtering
const isWithinDatePreset = (postDateStr: string, preset: string): boolean => {
    if (preset === 'all' || !postDateStr) return true;
    const postDate = new Date(postDateStr);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (preset === 'today') {
        const postDateOnly = new Date(postDate);
        postDateOnly.setHours(0, 0, 0, 0);
        return postDateOnly.getTime() === today.getTime();
    }

    if (preset === '7days') {
        const past7 = new Date(today);
        past7.setDate(past7.getDate() - 7);
        return postDate >= past7;
    }

    if (preset === '30days') {
        const past30 = new Date(today);
        past30.setDate(past30.getDate() - 30);
        return postDate >= past30;
    }

    if (preset === 'this_month') {
        return (
            postDate.getMonth() === today.getMonth() &&
            postDate.getFullYear() === today.getFullYear()
        );
    }

    if (preset === 'custom' && customDate.value) {
        return postDateStr.startsWith(customDate.value);
    }

    return true;
};

// Filtered Posts Logic
const filteredPosts = computed(() => {
    return props.posts.filter(post => {
        // Search Filter
        const query = searchQuery.value.trim().toLowerCase();
        const matchesSearch =
            !query ||
            post.title.toLowerCase().includes(query) ||
            post.excerpt.toLowerCase().includes(query) ||
            post.category.toLowerCase().includes(query) ||
            post.author.toLowerCase().includes(query);

        // Category Filter
        const matchesCategory =
            selectedCategory.value === 'All' || post.category === selectedCategory.value;

        // Author Filter
        const matchesAuthor =
            selectedAuthor.value === 'All' || post.author === selectedAuthor.value;

        // Status Filter
        const matchesStatus =
            selectedStatus.value === 'All' ||
            (selectedStatus.value === 'published' && post.published) ||
            (selectedStatus.value === 'draft' && !post.published);

        // Date Filter (checks created_at date or published_at)
        const dateToCheck = post.published_at || post.created_at;
        const matchesDate = isWithinDatePreset(dateToCheck, selectedDatePreset.value);

        return matchesSearch && matchesCategory && matchesAuthor && matchesStatus && matchesDate;
    });
});

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return (
        searchQuery.value.trim() !== '' ||
        selectedCategory.value !== 'All' ||
        selectedAuthor.value !== 'All' ||
        selectedStatus.value !== 'All' ||
        selectedDatePreset.value !== 'all' ||
        customDate.value !== ''
    );
});

const resetFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = 'All';
    selectedAuthor.value = 'All';
    selectedStatus.value = 'All';
    selectedDatePreset.value = 'all';
    customDate.value = '';
};
</script>

<template>
    <Head title="Blog posts" />

    <div class="space-y-6 overflow-x-hidden px-4 py-6 pb-12 text-neutral-800 sm:px-6 lg:px-8 dark:text-neutral-200">
        <!-- Top Header & Create Button -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">
                    Blog Posts Management
                </h1>
                <p class="text-xs text-neutral-500">
                    View, filter, edit, and publish blog articles directly from the admin panel.
                </p>
            </div>

            <Link
                :href="route('blog.create').url + '?return_to_admin=1'"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white shadow-xs transition hover:bg-emerald-700 active:scale-[0.99]"
            >
                <Plus class="h-4 w-4" />
                New post
            </Link>
        </div>

        <!-- Metrics / Summary Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold tracking-wider text-neutral-500 uppercase">Total Articles</span>
                    <FileText class="h-4 w-4 text-emerald-500" />
                </div>
                <div class="mt-2 text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                    {{ totalPostsCount }}
                </div>
            </div>

            <div class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold tracking-wider text-neutral-500 uppercase">Published</span>
                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                </div>
                <div class="mt-2 text-2xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">
                    {{ publishedPostsCount }}
                </div>
            </div>

            <div class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold tracking-wider text-neutral-500 uppercase">Drafts</span>
                    <Clock class="h-4 w-4 text-amber-500" />
                </div>
                <div class="mt-2 text-2xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">
                    {{ draftPostsCount }}
                </div>
            </div>

            <div class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex items-center justify-between text-neutral-400">
                    <span class="text-xs font-semibold tracking-wider text-neutral-500 uppercase">Categories</span>
                    <Sparkles class="h-4 w-4 text-indigo-500" />
                </div>
                <div class="mt-2 text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                    {{ categoriesCount }}
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <!-- Search input -->
                <div class="relative flex-1">
                    <Search class="absolute left-3.5 top-3 h-4 w-4 text-neutral-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by title, excerpt, category, author..."
                        class="h-10 w-full rounded-lg border border-neutral-200 bg-white pl-10 pr-4 text-xs text-neutral-800 outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                    />
                </div>

                <!-- Select Filters Container -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Category Select -->
                    <div class="flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs dark:border-neutral-800 dark:bg-neutral-900">
                        <Tag class="h-3.5 w-3.5 text-neutral-400" />
                        <select
                            v-model="selectedCategory"
                            class="cursor-pointer bg-transparent text-xs font-medium text-neutral-700 outline-none dark:text-neutral-300"
                        >
                            <option value="All">All Categories</option>
                            <option
                                v-for="cat in categoryOptions.filter(c => c !== 'All')"
                                :key="cat"
                                :value="cat"
                            >
                                {{ cat }}
                            </option>
                        </select>
                    </div>

                    <!-- Created By / Author Select -->
                    <div class="flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs dark:border-neutral-800 dark:bg-neutral-900">
                        <User class="h-3.5 w-3.5 text-neutral-400" />
                        <select
                            v-model="selectedAuthor"
                            class="cursor-pointer bg-transparent text-xs font-medium text-neutral-700 outline-none dark:text-neutral-300"
                        >
                            <option value="All">All Authors</option>
                            <option
                                v-for="auth in authorOptions.filter(a => a !== 'All')"
                                :key="auth"
                                :value="auth"
                            >
                                {{ auth }}
                            </option>
                        </select>
                    </div>

                    <!-- Date Filter Select -->
                    <div class="flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs dark:border-neutral-800 dark:bg-neutral-900">
                        <Calendar class="h-3.5 w-3.5 text-neutral-400" />
                        <select
                            v-model="selectedDatePreset"
                            class="cursor-pointer bg-transparent text-xs font-medium text-neutral-700 outline-none dark:text-neutral-300"
                        >
                            <option value="all">All Dates</option>
                            <option value="today">Today</option>
                            <option value="7days">Last 7 Days</option>
                            <option value="30days">Last 30 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="custom">Custom Date</option>
                        </select>
                    </div>

                    <!-- Custom Date Picker (when 'custom' preset is selected) -->
                    <input
                        v-if="selectedDatePreset === 'custom'"
                        v-model="customDate"
                        type="date"
                        class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-xs text-neutral-700 outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300"
                    />

                    <!-- Status Filter -->
                    <div class="flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs dark:border-neutral-800 dark:bg-neutral-900">
                        <Filter class="h-3.5 w-3.5 text-neutral-400" />
                        <select
                            v-model="selectedStatus"
                            class="cursor-pointer bg-transparent text-xs font-medium text-neutral-700 outline-none dark:text-neutral-300"
                        >
                            <option value="All">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <!-- Reset Filters Button -->
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-neutral-100 px-3 py-2 text-xs font-bold text-neutral-600 transition hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                        title="Reset all filters"
                    >
                        <RotateCcw class="h-3.5 w-3.5" />
                        Reset
                    </button>
                </div>
            </div>

            <!-- Active Filters Chip Bar -->
            <div v-if="hasActiveFilters" class="mt-3 flex flex-wrap items-center gap-2 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                <span class="text-[11px] font-semibold text-neutral-400">Active filters:</span>

                <span
                    v-if="searchQuery.trim()"
                    class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                >
                    Search: "{{ searchQuery.trim() }}"
                    <button @click="searchQuery = ''" class="hover:text-emerald-900 dark:hover:text-white">
                        <X class="h-3 w-3" />
                    </button>
                </span>

                <span
                    v-if="selectedCategory !== 'All'"
                    class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                >
                    Category: {{ selectedCategory }}
                    <button @click="selectedCategory = 'All'" class="hover:text-emerald-900 dark:hover:text-white">
                        <X class="h-3 w-3" />
                    </button>
                </span>

                <span
                    v-if="selectedAuthor !== 'All'"
                    class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                >
                    Author: {{ selectedAuthor }}
                    <button @click="selectedAuthor = 'All'" class="hover:text-emerald-900 dark:hover:text-white">
                        <X class="h-3 w-3" />
                    </button>
                </span>

                <span
                    v-if="selectedDatePreset !== 'all'"
                    class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                >
                    Date: {{ selectedDatePreset }} {{ customDate ? `(${customDate})` : '' }}
                    <button @click="selectedDatePreset = 'all'; customDate = ''" class="hover:text-emerald-900 dark:hover:text-white">
                        <X class="h-3 w-3" />
                    </button>
                </span>

                <span
                    v-if="selectedStatus !== 'All'"
                    class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                >
                    Status: {{ selectedStatus }}
                    <button @click="selectedStatus = 'All'" class="hover:text-emerald-900 dark:hover:text-white">
                        <X class="h-3 w-3" />
                    </button>
                </span>
            </div>
        </div>

        <!-- Table View -->
        <div v-if="filteredPosts.length === 0" class="rounded-xl border border-dashed border-neutral-200 bg-white p-12 text-center dark:border-neutral-800 dark:bg-neutral-900">
            <FileText class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-700" />
            <p class="mt-4 text-sm font-bold text-neutral-800 dark:text-neutral-200">No blog posts found.</p>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                {{ hasActiveFilters ? 'No posts match your selected filter criteria.' : 'Create your first blog post to get started.' }}
            </p>

            <div class="mt-6 flex justify-center gap-3">
                <button
                    v-if="hasActiveFilters"
                    @click="resetFilters"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-white px-4 text-xs font-bold text-neutral-700 shadow-xs transition hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                >
                    <RotateCcw class="h-3.5 w-3.5" />
                    Reset filters
                </button>
                <Link
                    :href="route('blog.create').url + '?return_to_admin=1'"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-xs font-bold text-white shadow-xs transition hover:bg-emerald-700"
                >
                    <Plus class="h-4 w-4" />
                    Add post
                </Link>
            </div>
        </div>

        <div v-else class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-neutral-50 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/40 dark:text-neutral-400">
                            <th class="p-4 font-semibold">Article</th>
                            <th class="w-32 p-4 font-semibold">Category</th>
                            <th class="w-40 p-4 font-semibold">Created By</th>
                            <th class="w-32 p-4 font-semibold">Date</th>
                            <th class="w-28 p-4 font-semibold">Status</th>
                            <th class="w-44 p-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                        <tr v-for="post in filteredPosts" :key="post.id" class="transition hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20">
                            <!-- Article Title & Excerpt -->
                            <td class="p-4 max-w-sm">
                                <div class="flex items-center gap-3">
                                    <img
                                        :src="post.image"
                                        :alt="post.title"
                                        class="h-10 w-12 shrink-0 rounded-lg object-cover bg-neutral-100 dark:bg-neutral-800"
                                    />
                                    <div class="min-w-0">
                                        <div class="font-bold text-neutral-900 truncate dark:text-neutral-100" :title="post.title">
                                            {{ post.title }}
                                        </div>
                                        <div class="text-xs text-neutral-500 truncate dark:text-neutral-400">
                                            {{ post.excerpt }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="p-4 whitespace-nowrap">
                                <span class="inline-flex rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    {{ post.category }}
                                </span>
                            </td>

                            <!-- Created By / Author -->
                            <td class="p-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-extrabold text-white">
                                        {{ (post.author || 'U').charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                        {{ post.author || 'Unknown' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="p-4 whitespace-nowrap text-xs text-neutral-600 dark:text-neutral-400">
                                {{ post.published_at_formatted || post.created_at_formatted }}
                            </td>

                            <!-- Status -->
                            <td class="p-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold',
                                        post.published
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'h-1.5 w-1.5 rounded-full',
                                            post.published ? 'bg-emerald-500' : 'bg-amber-500'
                                        ]"
                                    />
                                    {{ post.published ? 'Published' : 'Draft' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="p-4 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-2">
                                    <Link
                                        :href="route('blog.edit', post.slug).url + '?return_to_admin=1'"
                                        class="inline-flex h-8 items-center justify-center gap-1 rounded-lg border border-neutral-200 bg-white px-2.5 text-xs font-bold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
                                        title="Edit article"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                        Edit
                                    </Link>

                                    <Link
                                        :href="route('blog.show', post.slug).url"
                                        class="inline-flex h-8 items-center justify-center gap-1 rounded-lg bg-neutral-900 px-2.5 text-xs font-bold text-white transition hover:bg-neutral-800 dark:bg-emerald-600 dark:hover:bg-emerald-700"
                                        title="Preview article"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                        Preview
                                    </Link>

                                    <button
                                        @click="confirmDelete(post)"
                                        class="inline-flex h-8 items-center justify-center gap-1 rounded-lg border border-red-200 bg-red-50/50 px-2.5 text-xs font-bold text-red-600 transition hover:bg-red-100 hover:text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-400 dark:hover:bg-red-900/60"
                                        title="Delete article"
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

        <!-- Delete Confirmation Modal -->
        <div
            v-if="deletingPost"
            class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 p-4 backdrop-blur-xs"
        >
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white p-6 shadow-xl border border-neutral-200 dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 dark:bg-red-950">
                        <Trash2 class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Delete Blog Post</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Are you sure you want to delete this article?</p>
                    </div>
                </div>

                <p class="rounded-lg bg-neutral-50 p-3 text-xs font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                    "{{ deletingPost.title }}"
                </p>

                <div class="flex justify-end gap-2.5 pt-2">
                    <button
                        @click="cancelDelete"
                        class="rounded-lg border border-neutral-200 bg-white px-4 py-2 text-xs font-bold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleDelete"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-red-700"
                    >
                        Confirm Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
