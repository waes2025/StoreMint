<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, Home } from '@lucide/vue';

defineProps<{
    status?: number;
    message?: string;
}>();

const goBack = () => {
    if (typeof window !== 'undefined') {
        window.history.back();
    }
};
</script>

<template>
    <Head title="Access Denied" />

    <div class="flex min-h-[70vh] items-center justify-center p-6">
        <div class="w-full max-w-md rounded-2xl border border-neutral-200 bg-white p-8 shadow-lg transition duration-300 dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex flex-col items-center text-center">
                <!-- Icon container with a pulse effect and nice gradient background -->
                <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-500 dark:bg-amber-500/20">
                    <AlertTriangle class="h-8 w-8" />
                    <span class="absolute inset-0 animate-ping rounded-2xl bg-amber-500/5 opacity-75"></span>
                </div>

                <h1 class="mt-6 text-xl font-bold tracking-tight text-neutral-900 dark:text-white">
                    Module Not Enabled
                </h1>
                
                <p class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ message || "The requested module is not enabled for this business." }}
                </p>

                <!-- Actions -->
                <div class="mt-8 flex w-full flex-col gap-3 sm:flex-row">
                    <Link
                        href="/dashboard"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-neutral-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-850 dark:bg-neutral-800 dark:hover:bg-neutral-750"
                    >
                        <Home class="h-4 w-4" />
                        Go to Dashboard
                    </Link>
                    <button
                        @click="goBack"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-white px-4 py-2.5 text-xs font-semibold text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
