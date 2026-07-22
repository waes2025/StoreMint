<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Eye, Save, ChevronDown } from '@lucide/vue';
import { route } from '@/lib/route';
import WordpadEditor from '@/components/WordpadEditor.vue';

interface BlogFormPost {
    id?: number;
    slug?: string;
    title: string;
    excerpt: string;
    content: string;
    category: string;
    author_name: string;
    image: string;
    is_published: boolean;
}

const returnToAdmin = typeof window !== 'undefined'
    ? new URLSearchParams(window.location.search).get('return_to_admin') === '1'
    : false;

const props = defineProps<{
    mode: 'create' | 'edit';
    post: BlogFormPost;
    categories: string[];
    authors?: string[];
}>();

const form = useForm({
    title: props.post.title,
    slug: props.post.slug ?? '',
    excerpt: props.post.excerpt,
    content: props.post.content,
    category: props.post.category || (props.categories && props.categories.length > 0 ? props.categories[0] : 'General'),
    author_name: props.post.author_name || (props.authors && props.authors.length > 0 ? props.authors[0] : 'StoreMint Team'),
    image: props.post.image,
    is_published: props.post.is_published,
    return_to_admin: returnToAdmin ? 1 : 0,
});

// Category Selection State
const isCustomCategory = ref(
    Boolean(form.category && props.categories && !props.categories.includes(form.category))
);
const selectedCategoryOption = ref(
    isCustomCategory.value ? '__new__' : (form.category || 'General')
);

const handleCategorySelectChange = (e: Event) => {
    const val = (e.target as HTMLSelectElement).value;
    if (val === '__new__') {
        isCustomCategory.value = true;
        form.category = '';
    } else {
        isCustomCategory.value = false;
        form.category = val;
    }
};

const toggleNewCategory = () => {
    isCustomCategory.value = !isCustomCategory.value;
    if (isCustomCategory.value) {
        selectedCategoryOption.value = '__new__';
        form.category = '';
    } else {
        const defaultCat = props.categories && props.categories.length > 0 ? props.categories[0] : 'General';
        selectedCategoryOption.value = defaultCat;
        form.category = defaultCat;
    }
};

// Author Selection State
const isCustomAuthor = ref(
    Boolean(form.author_name && props.authors && !props.authors.includes(form.author_name))
);
const selectedAuthorOption = ref(
    isCustomAuthor.value ? '__new__' : (form.author_name || (props.authors && props.authors.length > 0 ? props.authors[0] : 'StoreMint Team'))
);

const handleAuthorSelectChange = (e: Event) => {
    const val = (e.target as HTMLSelectElement).value;
    if (val === '__new__') {
        isCustomAuthor.value = true;
        form.author_name = '';
    } else {
        isCustomAuthor.value = false;
        form.author_name = val;
    }
};

const toggleNewAuthor = () => {
    isCustomAuthor.value = !isCustomAuthor.value;
    if (isCustomAuthor.value) {
        selectedAuthorOption.value = '__new__';
        form.author_name = '';
    } else {
        const defaultAuth = props.authors && props.authors.length > 0 ? props.authors[0] : 'StoreMint Team';
        selectedAuthorOption.value = defaultAuth;
        form.author_name = defaultAuth;
    }
};

const slugWasEdited = ref(props.mode === 'edit' && !!props.post.slug);

const title = computed(() => props.mode === 'create' ? 'Create Blog Post' : 'Edit Blog Post');
const description = computed(() => props.mode === 'create'
    ? 'Write and publish a new article for the StoreMint blog.'
    : 'Update the article details, content, and publishing state.',
);
const submitLabel = computed(() => props.mode === 'create' ? 'Create post' : 'Save changes');
const previewUrl = computed(() => props.mode === 'edit' && props.post.slug
    ? route('blog.show', props.post.slug).url
    : null,
);
const backUrl = computed(() => returnToAdmin
    ? route('blog.adminIndex').url
    : route('blog.index').url,
);

watch(
    () => form.title,
    (value) => {
        if (!slugWasEdited.value) {
            form.slug = value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    },
);

const submit = () => {
    if (props.mode === 'create') {
        form.post(route('blog.store').url);
        return;
    }

    form.put(route('blog.update', props.post.slug ?? props.post.id ?? '').url);
};
</script>

<template>
    <Head :title="title" />

    <div class="space-y-6 overflow-x-hidden px-4 py-6 pb-12 text-neutral-800 sm:px-6 lg:px-8 dark:text-neutral-200">
        <!-- Top Header section -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-2">
                <Link
                    :href="backUrl"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-500 transition hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                >
                    <ArrowLeft class="h-4 w-4" />
                    {{ returnToAdmin ? 'Back to Admin Blog List' : 'Back to Blog' }}
                </Link>
                <h1 class="text-2xl font-extrabold tracking-tight">
                    {{ title }}
                </h1>
                <p class="text-xs text-neutral-500">
                    {{ description }}
                </p>
            </div>

            <Link
                v-if="previewUrl"
                :href="previewUrl"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-white px-4 text-xs font-bold text-neutral-700 shadow-xs transition hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
            >
                <Eye class="h-4 w-4" />
                Preview
            </Link>
        </div>

        <!-- Main Form Grid -->
        <form class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]" @submit.prevent="submit">
            <section class="space-y-5 rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="space-y-2">
                    <label for="title" class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Title</label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-lg border border-neutral-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-neutral-900 outline-none transition focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-white"
                        placeholder="How to grow your storefront..."
                    />
                    <p v-if="form.errors.title" class="text-xs font-semibold text-red-600">{{ form.errors.title }}</p>
                </div>

                <div class="space-y-2">
                    <label for="excerpt" class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Excerpt</label>
                    <textarea
                        id="excerpt"
                        v-model="form.excerpt"
                        rows="3"
                        class="w-full rounded-lg border border-neutral-200 bg-white px-3.5 py-2.5 text-xs text-neutral-800 outline-none transition focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                        placeholder="Short summary shown on the blog listing."
                    />
                    <p v-if="form.errors.excerpt" class="text-xs font-semibold text-red-600">{{ form.errors.excerpt }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Content (Tiptap WYSIWYG Rich Text Editor)</label>
                    <WordpadEditor v-model="form.content" placeholder="Write the full article content with rich formatting..." />
                    <p v-if="form.errors.content" class="text-xs font-semibold text-red-600">{{ form.errors.content }}</p>
                </div>
            </section>

            <aside class="space-y-5">
                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Publishing</h3>
                    <p class="mt-0.5 text-xs text-neutral-500">Draft posts are hidden from guest visitors.</p>

                    <label class="mt-4 flex items-center justify-between gap-3 rounded-lg border border-neutral-200 bg-neutral-50 px-3.5 py-2.5 dark:border-neutral-800 dark:bg-neutral-900/60">
                        <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">Published</span>
                        <input
                            v-model="form.is_published"
                            type="checkbox"
                            class="h-4 w-4 rounded border-neutral-300 text-emerald-600 focus:ring-emerald-500"
                        />
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-5 inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white shadow-xs transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Saving...' : submitLabel }}
                    </button>
                </div>

                <div class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="space-y-1.5">
                        <label for="slug" class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Slug</label>
                        <input
                            id="slug"
                            v-model="form.slug"
                            type="text"
                            class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-800 outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                            placeholder="auto-generated"
                            @input="slugWasEdited = true"
                        />
                        <p v-if="form.errors.slug" class="text-xs font-semibold text-red-600">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Category Selector with Custom Category Save support -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="category" class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Category</label>
                            <button
                                type="button"
                                @click="toggleNewCategory"
                                class="text-[11px] font-bold text-emerald-600 hover:underline dark:text-emerald-400 cursor-pointer"
                            >
                                {{ isCustomCategory ? 'Select Existing' : '+ Add New' }}
                            </button>
                        </div>

                        <!-- Dropdown Select -->
                        <div v-if="!isCustomCategory" class="relative">
                            <select
                                id="category-select"
                                v-model="selectedCategoryOption"
                                @change="handleCategorySelectChange"
                                class="w-full appearance-none rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs font-semibold text-neutral-800 outline-none transition focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 pr-8"
                            >
                                <option v-for="cat in categories" :key="cat" :value="cat">
                                    {{ cat }}
                                </option>
                                <option value="__new__">+ Create New Category...</option>
                            </select>
                            <ChevronDown class="pointer-events-none absolute right-2.5 top-2.5 h-3.5 w-3.5 text-neutral-400" />
                        </div>

                        <!-- Custom New Category Input -->
                        <div v-else class="space-y-1">
                            <input
                                id="category-custom"
                                v-model="form.category"
                                type="text"
                                placeholder="Enter new category name..."
                                class="w-full rounded-lg border border-emerald-500 bg-white px-3 py-2 text-xs font-semibold text-neutral-800 outline-none transition focus:ring-1 focus:ring-emerald-500 dark:border-emerald-600 dark:bg-neutral-900 dark:text-neutral-200"
                            />
                            <p class="text-[10px] text-neutral-400">
                                This new category will be saved and added to storefront category filters.
                            </p>
                        </div>

                        <p v-if="form.errors.category" class="text-xs font-semibold text-red-600">{{ form.errors.category }}</p>
                    </div>

                    <!-- Author Selector with Custom Author support -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="author_name" class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Author</label>
                            <button
                                type="button"
                                @click="toggleNewAuthor"
                                class="text-[11px] font-bold text-emerald-600 hover:underline dark:text-emerald-400 cursor-pointer"
                            >
                                {{ isCustomAuthor ? 'Select Existing' : '+ Custom Author' }}
                            </button>
                        </div>

                        <!-- Dropdown Select -->
                        <div v-if="!isCustomAuthor" class="relative">
                            <select
                                id="author-select"
                                v-model="selectedAuthorOption"
                                @change="handleAuthorSelectChange"
                                class="w-full appearance-none rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs font-semibold text-neutral-800 outline-none transition focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200 pr-8"
                            >
                                <option v-for="author in (authors && authors.length ? authors : ['StoreMint Team'])" :key="author" :value="author">
                                    {{ author }}
                                </option>
                                <option value="__new__">+ Enter Custom Author...</option>
                            </select>
                            <ChevronDown class="pointer-events-none absolute right-2.5 top-2.5 h-3.5 w-3.5 text-neutral-400" />
                        </div>

                        <!-- Custom Author Text Input -->
                        <div v-else class="space-y-1">
                            <input
                                id="author-custom"
                                v-model="form.author_name"
                                type="text"
                                placeholder="Enter custom author name..."
                                class="w-full rounded-lg border border-emerald-500 bg-white px-3 py-2 text-xs font-semibold text-neutral-800 outline-none transition focus:ring-1 focus:ring-emerald-500 dark:border-emerald-600 dark:bg-neutral-900 dark:text-neutral-200"
                            />
                            <p class="text-[10px] text-neutral-400">
                                This author name will be saved and displayed on the article.
                            </p>
                        </div>

                        <p v-if="form.errors.author_name" class="text-xs font-semibold text-red-600">{{ form.errors.author_name }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label for="image" class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Image URL</label>
                        <input
                            id="image"
                            v-model="form.image"
                            type="text"
                            class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-800 outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                            placeholder="/modules/blog/images/ecommerce.png"
                        />
                        <p v-if="form.errors.image" class="text-xs font-semibold text-red-600">{{ form.errors.image }}</p>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</template>
