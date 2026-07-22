<script setup lang="ts">
import BlogForm from '../components/BlogForm.vue';

defineProps<{
    post: {
        id: number;
        title: string;
        slug: string;
        excerpt: string;
        content: string;
        category: string;
        author_name: string;
        image: string;
        is_published: boolean;
    };
    categories: string[];
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
        {
            title: 'Edit Post',
            href: '#',
        },
    ],
});
</script>

<template>
    <BlogForm mode="edit" :post="post" :categories="categories" :authors="authors" />
</template>
