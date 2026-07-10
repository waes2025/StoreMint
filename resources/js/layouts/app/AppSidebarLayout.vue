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
        <AppContent variant="sidebar" class="flex flex-col min-h-screen overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="flex-1">
                <slot />
            </div>
            <footer class="mt-auto border-t border-neutral-200/50 py-4 px-6 text-center text-xs text-neutral-500 dark:border-neutral-850 dark:text-neutral-400">
                &copy; {{ new Date().getFullYear() }} StoreMint Inc. All rights reserved.
            </footer>
        </AppContent>
        <Toaster />
    </AppShell>
</template>
