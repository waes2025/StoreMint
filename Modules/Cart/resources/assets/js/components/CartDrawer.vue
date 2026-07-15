<script setup lang="ts">
import { ShoppingCart, X, ShoppingBag, Minus, Plus, Trash2, Tag, ArrowRight } from '@lucide/vue';

interface CartItem {
    product: {
        id: number;
        name: string;
        price: number;
        imageGradient: string;
    };
    quantity: number;
}

const props = defineProps<{
    cartOpen: boolean;
    cart: CartItem[];
    cartQuantity: number;
    cartSubtotal: number;
    discountAmount: number;
    shippingFee: number;
    cartTotal: number;
    appliedCoupon: any;
    couponInput: string;
    couponError: string;
    couponSuccess: string;
    isShipmentEnabled: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:cartOpen', val: boolean): void;
    (e: 'update:couponInput', val: string): void;
    (e: 'updateCartQuantity', id: number, delta: number): void;
    (e: 'removeFromCart', id: number): void;
    (e: 'applyCoupon'): void;
    (e: 'removeCoupon'): void;
    (e: 'proceedToCheckout'): void;
}>();

const handleCouponInput = (event: Event) => {
    emit('update:couponInput', (event.target as HTMLInputElement).value);
};
</script>

<template>
    <div
        v-if="cartOpen"
        class="fixed inset-0 z-50 overflow-hidden"
        aria-labelledby="slide-over-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 overflow-hidden">
            <!-- Backdrop -->
            <div
                @click="emit('update:cartOpen', false)"
                class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs transition-opacity"
            ></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div class="pointer-events-auto w-screen max-w-md">
                    <div class="flex h-full flex-col bg-white shadow-2xl dark:bg-neutral-900">
                        <!-- Drawer Header -->
                        <div class="flex items-center justify-between border-b border-neutral-100 px-6 py-5 dark:border-neutral-800">
                            <h2 class="flex items-center gap-1.5 text-sm font-bold tracking-tight">
                                <ShoppingCart class="h-4 w-4 text-emerald-500" />
                                Shopping Cart ({{ cartQuantity }})
                            </h2>
                            <button
                                @click="emit('update:cartOpen', false)"
                                class="rounded-lg p-1 text-neutral-400 transition hover:text-neutral-600 dark:hover:text-white"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Cart Items list -->
                        <div class="flex-1 space-y-4 overflow-y-auto px-6 py-4">
                            <div
                                v-if="cart.length === 0"
                                class="flex flex-col items-center justify-center space-y-3 py-20 text-center"
                            >
                                <div class="rounded-full bg-neutral-50 p-4 dark:bg-neutral-800">
                                    <ShoppingBag class="h-8 w-8 text-neutral-300" />
                                </div>
                                <h3 class="font-bold">Your cart is empty</h3>
                                <p class="max-w-xs text-xs text-neutral-400">
                                    Looks like you haven't added any products to your cart yet.
                                </p>
                            </div>

                            <div
                                v-else
                                v-for="item in cart"
                                :key="item.product.id"
                                class="flex items-start gap-4 rounded-xl border border-neutral-100 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-800/20"
                            >
                                <!-- Image representation -->
                                <div
                                    :class="item.product.imageGradient"
                                    class="text-md flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-gradient-to-tr font-bold text-white shadow-xs"
                                >
                                    {{
                                        item.product.name
                                            .split(' ')
                                            .map((w) => w[0])
                                            .join('')
                                    }}
                                </div>

                                <!-- Content -->
                                <div class="min-w-0 flex-1 space-y-2">
                                    <div class="space-y-0.5">
                                        <h4 class="truncate pr-6 text-xs font-bold">
                                            {{ item.product.name }}
                                        </h4>
                                        <span class="font-mono text-[10px] text-neutral-400">
                                            {{ $page.props.currency_symbol ?? '$' }}{{ item.product.price.toFixed(2) }} each
                                        </span>
                                    </div>

                                    <!-- Quantity Actions -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900">
                                            <button
                                                @click="emit('updateCartQuantity', item.product.id, -1)"
                                                class="flex h-7 w-7 items-center justify-center text-neutral-500 hover:text-neutral-800 dark:hover:text-white"
                                            >
                                                <Minus class="h-3 w-3" />
                                            </button>
                                            <span class="w-8 text-center font-mono text-xs font-semibold">{{ item.quantity }}</span>
                                            <button
                                                @click="emit('updateCartQuantity', item.product.id, 1)"
                                                class="flex h-7 w-7 items-center justify-center text-neutral-500 hover:text-neutral-800 dark:hover:text-white"
                                            >
                                                <Plus class="h-3 w-3" />
                                            </button>
                                        </div>

                                        <span class="font-mono text-xs font-bold">
                                            {{ $page.props.currency_symbol ?? '$' }}{{ (item.product.price * item.quantity).toFixed(2) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Remove Action -->
                                <button
                                    @click="emit('removeFromCart', item.product.id)"
                                    class="ml-auto shrink-0 text-neutral-400 transition hover:text-red-500"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Cart Summary / Coupon application -->
                        <div class="space-y-4 border-t border-neutral-100 bg-neutral-50/50 p-6 dark:border-neutral-800 dark:bg-neutral-800/10">
                            <!-- Coupon input group -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-neutral-500 uppercase">Apply Promo Coupon</label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input
                                            :value="couponInput"
                                            @input="handleCouponInput"
                                            type="text"
                                            placeholder="e.g. MINT50"
                                            :disabled="!!appliedCoupon"
                                            class="h-10 w-full rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                                        />
                                        <Tag class="absolute top-1/2 right-3 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400" />
                                    </div>
                                    <button
                                        v-if="!appliedCoupon"
                                        @click="emit('applyCoupon')"
                                        class="rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                    >
                                        Apply
                                    </button>
                                    <button
                                        v-else
                                        @click="emit('removeCoupon')"
                                        class="rounded-lg bg-red-500 px-4 text-xs font-semibold text-white transition hover:bg-red-600"
                                    >
                                        Remove
                                    </button>
                                </div>
                                <p v-if="couponError" class="text-[10px] font-medium text-red-500">{{ couponError }}</p>
                                <p v-if="couponSuccess" class="text-[10px] font-medium text-emerald-500">{{ couponSuccess }}</p>
                            </div>

                            <!-- Summary rows -->
                            <div class="space-y-2 border-t border-neutral-100 pt-4 text-xs dark:border-neutral-800">
                                <div class="flex justify-between text-neutral-500">
                                    <span>Subtotal</span>
                                    <span class="font-mono">{{ $page.props.currency_symbol ?? '$' }}{{ cartSubtotal.toFixed(2) }}</span>
                                </div>
                                <div v-if="appliedCoupon" class="flex justify-between text-emerald-600">
                                    <span>Coupon Discount</span>
                                    <span class="font-mono">- {{ $page.props.currency_symbol ?? '$' }}{{ discountAmount.toFixed(2) }}</span>
                                </div>
                                <div v-if="isShipmentEnabled" class="flex justify-between text-neutral-500">
                                    <span>Shipping</span>
                                    <span class="font-mono">
                                        {{ shippingFee === 0 ? 'Free' : `${$page.props.currency_symbol ?? '$'}${shippingFee.toFixed(2)}` }}
                                    </span>
                                </div>
                                <div class="flex justify-between border-t border-neutral-200 pt-3 text-sm font-bold text-neutral-900 dark:border-neutral-800 dark:text-white">
                                    <span>Grand Total</span>
                                    <span class="font-mono text-base">{{ $page.props.currency_symbol ?? '$' }}{{ cartTotal.toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- CTA button -->
                            <div class="pt-2">
                                <button
                                    @click="emit('proceedToCheckout')"
                                    :disabled="cart.length === 0"
                                    class="flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:bg-neutral-200 disabled:text-neutral-500"
                                >
                                    <span>Proceed to Checkout</span>
                                    <ArrowRight class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
