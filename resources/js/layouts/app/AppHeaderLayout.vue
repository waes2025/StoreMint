<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
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
    <AppShell variant="header">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent variant="header" class="flex flex-col min-h-screen">
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
