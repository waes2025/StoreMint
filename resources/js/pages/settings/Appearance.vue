<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { route } from '@/lib/route';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: route('appearance.edit').url,
            },
        ],
    },
});
</script>

<template>
    <Head title="Appearance settings" />

    <h1 class="sr-only">Appearance settings</h1>

    <div class="space-y-8 pb-12">
        <Heading
            variant="small"
            title="Appearance settings"
            description="Customize theme preferences for your account."
        />

        <!-- Theme section -->
        <div
            class="space-y-4 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div>
                <h3
                    class="text-neutral-850 text-sm font-bold tracking-tight dark:text-white"
                >
                    Theme Preference
                </h3>
                <p class="mb-3 text-xs text-neutral-500">
                    Choose the interface theme that best suits your eyes.
                </p>
            </div>
            <AppearanceTabs />
        </div>
    </div>
</template>
