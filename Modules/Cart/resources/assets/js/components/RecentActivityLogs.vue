<script setup lang="ts">
import { computed } from 'vue';

interface Activity {
    id?: number | string;
    customer?: string;
    date?: string;
    total?: number;
    action?: string;
}

const props = defineProps<{
    activities?: Activity[];
}>();

const list = computed(() => (props.activities || []).slice(0, 5));
</script>

<template>
    <div class="flex flex-col justify-between rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center justify-between text-neutral-400">
            <span class="text-xs font-semibold tracking-wider text-neutral-500 uppercase">Recent Activity Logs</span>
            <svg class="h-4 w-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 6v6l4 2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>

        <div class="my-2 space-y-2 text-[12px]">
            <div v-if="list.length === 0" class="text-neutral-400">No recent activity</div>
            <div v-for="(a, i) in list" :key="a.id || i" class="flex items-center justify-between">
                <div class="text-xs text-neutral-700 font-medium">{{ a.customer ?? a.action ?? '—' }}</div>
                <div class="text-[11px] text-neutral-400">{{ a.date ?? '' }}</div>
            </div>
        </div>

        <div class="mt-2 text-[11px] text-neutral-500">Latest orders and account activity</div>
    </div>
</template>
