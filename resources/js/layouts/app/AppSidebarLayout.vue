<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent
            variant="sidebar"
            class="flex min-h-screen flex-col overflow-x-hidden"
        >
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="flex-1">
                <slot />
            </div>
            <footer
                class="dark:border-neutral-850 mt-auto border-t border-neutral-200/50 px-6 py-4 text-center text-xs text-neutral-500 dark:text-neutral-400"
            >
                &copy; {{ new Date().getFullYear() }} StoreMint Inc. All rights
                reserved.
            </footer>
        </AppContent>
        <Toaster />
    </AppShell>
</template>
