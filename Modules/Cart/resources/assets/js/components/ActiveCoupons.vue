<script setup lang="ts">
import { Tag } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';

interface Coupon {
    code: string;
    discountType: string;
    discountValue: number;
    description: string;
}

defineProps<{
    activeCoupons?: Coupon[];
    appliedCoupon?: Coupon | null;
}>();

const emit = defineEmits<{
    (e: 'apply', code: string): void;
}>();

const page = usePage();
</script>

<template>
    <section v-if="activeCoupons && activeCoupons.length > 0" class="space-y-4">
        <div class="flex items-center gap-2">
            <Tag class="h-5 w-5 text-emerald-500" />
            <h2 class="text-lg font-bold tracking-tight">
                Active Coupons & Discounts
            </h2>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div
                v-for="coupon in activeCoupons"
                :key="coupon.code"
                class="flex items-start justify-between rounded-xl border border-dashed border-emerald-500/30 bg-emerald-500/5 p-6 transition hover:bg-emerald-500/10"
            >
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded bg-emerald-500/20 px-2 py-1 font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400"
                        >
                            {{ coupon.code }}
                        </span>
                        <span
                            class="text-xs text-neutral-500 dark:text-neutral-400"
                        >
                            {{
                                coupon.discountType === 'percentage'
                                    ? `${coupon.discountValue}% Off`
                                    : `${page.props.currency_symbol ?? '$'}${coupon.discountValue} Flat Off`
                            }}
                        </span>
                    </div>
                    <p
                        class="text-xs text-neutral-600 dark:text-neutral-400"
                    >
                        {{ coupon.description }}
                    </p>
                </div>
                <button
                    @click="emit('apply', coupon.code)"
                    :disabled="appliedCoupon?.code === coupon.code"
                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:bg-neutral-200 disabled:text-neutral-500 dark:disabled:bg-neutral-800"
                >
                    {{
                        appliedCoupon?.code === coupon.code
                            ? 'Applied'
                            : 'Apply Coupon'
                    }}
                </button>
            </div>
        </div>
    </section>
</template>
