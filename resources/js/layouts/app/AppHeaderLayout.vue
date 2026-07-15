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
        <AppContent variant="header" class="flex min-h-screen flex-col">
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
