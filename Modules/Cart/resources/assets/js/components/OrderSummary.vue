<script setup lang="ts">
import { RefreshCw } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';

interface CartItem {
    product: {
        id: number;
        name: string;
        price: number;
        imageGradient: string;
    };
    quantity: number;
}

interface Coupon {
    code: string;
}

interface StripeCard {
    isProcessing: boolean;
}

defineProps<{
    cart: CartItem[];
    cartSubtotal: number;
    appliedCoupon?: Coupon | null;
    discountAmount: number;
    isShipmentEnabled: boolean;
    shippingFee: number;
    cartTotal: number;
    stripeCard: StripeCard;
}>();

const emit = defineEmits<{
    (e: 'placeOrder'): void;
}>();

const page = usePage();
</script>

<template>
    <div
        class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
    >
        <h2
            class="border-b border-neutral-100 pb-3 text-base font-bold tracking-tight dark:border-neutral-800"
        >
            Order Summary
        </h2>

        <!-- Line items list -->
        <div class="max-h-60 space-y-4 overflow-y-auto pr-2">
            <div
                v-for="item in cart"
                :key="item.product.id"
                class="flex items-center gap-3"
            >
                <div
                    :class="item.product.imageGradient"
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gradient-to-tr text-xs font-bold text-white opacity-95"
                >
                    {{
                        item.product.name
                            .split(' ')
                            .map((w) => w[0])
                            .join('')
                    }}
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="truncate text-xs font-bold">
                        {{ item.product.name }}
                    </h4>
                    <span class="font-mono text-[10px] text-neutral-500">
                        {{ item.quantity }} × {{ page.props.currency_symbol ?? '$' }}{{ item.product.price.toFixed(2) }}
                    </span>
                </div>
                <span class="ml-auto font-mono text-xs font-bold">
                    {{ page.props.currency_symbol ?? '$' }}{{ (item.product.price * item.quantity).toFixed(2) }}
                </span>
            </div>
        </div>

        <!-- Totals Calculations (Section 3.2 rules) -->
        <div class="space-y-2 border-t border-neutral-100 pt-4 text-xs dark:border-neutral-800">
            <div class="flex justify-between text-neutral-600 dark:text-neutral-400">
                <span>Subtotal</span>
                <span class="font-mono">
                    {{ page.props.currency_symbol ?? '$' }}{{ cartSubtotal.toFixed(2) }}
                </span>
            </div>

            <div
                v-if="appliedCoupon"
                class="flex justify-between text-emerald-600 dark:text-emerald-400"
            >
                <span>Discount (Coupon: {{ appliedCoupon.code }})</span>
                <span class="font-mono">
                    - {{ page.props.currency_symbol ?? '$' }}{{ discountAmount.toFixed(2) }}
                </span>
            </div>

            <div
                v-if="isShipmentEnabled"
                class="flex justify-between text-neutral-600 dark:text-neutral-400"
            >
                <span>Shipping</span>
                <span class="font-mono">
                    {{ shippingFee === 0 ? 'Free' : `${page.props.currency_symbol ?? '$'}${shippingFee.toFixed(2)}` }}
                </span>
            </div>

            <div
                class="flex justify-between border-t border-neutral-100 pt-3 text-sm font-bold text-neutral-900 dark:border-neutral-800 dark:text-white"
            >
                <span>Grand Total</span>
                <span class="font-mono text-base">
                    {{ page.props.currency_symbol ?? '$' }}{{ cartTotal.toFixed(2) }}
                </span>
            </div>
        </div>

        <!-- CTA Buttons -->
        <div class="pt-2">
            <button
                @click="emit('placeOrder')"
                :disabled="stripeCard.isProcessing"
                class="flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:bg-neutral-200 disabled:text-neutral-500"
            >
                <RefreshCw
                    v-if="stripeCard.isProcessing"
                    class="h-4 w-4 animate-spin"
                />
                <span v-else>Confirm & Place Order</span>
            </button>
        </div>
    </div>
</template>
