<script setup lang="ts">
import { computed } from 'vue';

interface OrderSummary {
    id?: string;
    customer?: string;
    date?: string;
    status?: string;
}

const props = defineProps<{
    orders?: OrderSummary[];
}>();

const recentOrders = computed(() => (props.orders || []).slice(0, 3));
</script>

<template>
    <div class="space-y-4 rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
        <h3 class="border-b border-neutral-100 pb-3 text-base font-bold tracking-tight dark:border-neutral-800">Recent Activity Logs</h3>
        <div class="max-h-[16.5rem] space-y-4 overflow-y-auto pr-2 text-xs">
            <div v-for="order in recentOrders" :key="order.id || order.date" class="flex gap-2.5">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="space-y-0.5">
                    <p class="font-semibold">Order #{{ order.id }} - {{ order.status }}</p>
                    <span class="text-[10px] text-neutral-400">{{ order.customer }} · {{ order.date }}</span>
                </div>
            </div>

            <div v-if="recentOrders.length === 0" class="flex gap-2.5">
                <span class="text-neutral-400">No recent orders yet.</span>
            </div>
        </div>
    </div>
</template>
