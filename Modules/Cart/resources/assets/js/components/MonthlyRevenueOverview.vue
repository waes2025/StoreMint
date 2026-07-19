<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    stats?: {
        totalRevenue?: number;
        totalOrders?: number;
        monthlyRevenue?: number[];
    };
}>();

const revenue = computed(() => props.stats?.totalRevenue ?? 0);
const totalOrders = computed(() => props.stats?.totalOrders ?? 0);
const formattedRevenue = computed(() =>
    revenue.value.toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }),
);

const revenueSeries = computed(() => {
    const series = props.stats?.monthlyRevenue;
    if (series && series.length > 0) {
        return series;
    }
    return [45, 38, 41, 22, 25, 15, 18, 8, 12, 6, 2];
});

const chartPoints = computed(() => {
    const values = revenueSeries.value;
    const count = values.length;
    if (count === 0) {
        return [];
    }

    const max = Math.max(...values);
    const min = Math.min(...values);
    const range = max === min ? max || 1 : max - min;

    return values.map((value, index) => {
        const x = count === 1 ? 50 : (index / (count - 1)) * 100;
        const normalized = (value - min) / range;
        const y = 45 - normalized * 40;
        return { x, y };
    });
});

const linePath = computed(() => {
    const points = chartPoints.value;
    if (points.length === 0) {
        return '';
    }
    return points
        .map((point, index) => `${index === 0 ? 'M' : 'L'}${point.x.toFixed(2)},${point.y.toFixed(2)}`)
        .join(' ');
});

const areaPath = computed(() => {
    const points = chartPoints.value;
    if (points.length === 0) {
        return '';
    }
    const line = points
        .map((point, index) => `${index === 0 ? 'M' : 'L'}${point.x.toFixed(2)},${point.y.toFixed(2)}`)
        .join(' ');
    return `${line} L100,45 L0,45 Z`;
});
</script>

<template>
    <div class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 shadow-xs lg:col-span-2 dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
            <h3 class="flex items-center gap-2 text-base font-bold tracking-tight">
                <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none"><path d="M3 12h3l3-8 4 16 3-8 4 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Monthly Revenue Analytics
            </h3>
            <span class="text-[10px] font-bold text-neutral-400">FY 2026</span>
        </div>

        <div class="grid gap-4 pt-4 sm:grid-cols-[1fr_auto]">
            <div>
                <div class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                    {{ formattedRevenue }}
                </div>
                <div class="text-sm text-neutral-500 dark:text-neutral-400">
                    Total revenue this month
                </div>
            </div>
            <div class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                {{ totalOrders }} orders
            </div>
        </div>

        <div class="relative h-60 w-full pt-1">
            <svg class="h-full w-full overflow-visible" viewBox="0 0 100 50">
                <line x1="0" y1="10" x2="100" y2="10" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                <line x1="0" y1="20" x2="100" y2="20" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                <line x1="0" y1="30" x2="100" y2="30" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />
                <line x1="0" y1="40" x2="100" y2="40" stroke="rgba(0,0,0,0.05)" stroke-width="0.5" class="dark:stroke-neutral-800" />

                <text x="0" y="48" font-size="2" fill="#999">Jan</text>
                <text x="20" y="48" font-size="2" fill="#999">Mar</text>
                <text x="40" y="48" font-size="2" fill="#999">May</text>
                <text x="60" y="48" font-size="2" fill="#999">Jul</text>
                <text x="80" y="48" font-size="2" fill="#999">Sep</text>
                <text x="96" y="48" font-size="2" fill="#999">Nov</text>

                <path
                    v-if="linePath"
                    :d="linePath"
                    fill="none"
                    stroke="rgb(16,185,129)"
                    stroke-width="1"
                />
                <path
                    v-if="areaPath"
                    :d="areaPath"
                    fill="rgba(16,185,129,0.08)"
                />
            </svg>
        </div>
    </div>
</template>
