<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    stats?: { totalRevenue?: number };
}>();

const revenue = computed(() => props.stats?.totalRevenue ?? 0);
</script>

<template>
    <div class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center justify-between text-neutral-400">
            <span class="text-xs font-semibold tracking-wider text-neutral-500 uppercase">Monthly Revenue</span>
            <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3v18h18" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>

        <div class="my-2 space-y-1">
            <div class="font-mono text-2xl font-bold tracking-tight">${{ revenue.toLocaleString() }}</div>
            <div class="flex items-center gap-1 text-[10px] text-neutral-400">Revenue for the last 30 days</div>
        </div>

        <div class="h-1 w-full rounded-full bg-neutral-100 dark:bg-neutral-800">
            <div class="h-1 rounded-full bg-emerald-500" style="width: 60%"></div>
        </div>
    </div>
</template>
