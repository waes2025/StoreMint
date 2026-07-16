<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Star, Eye } from '@lucide/vue';
import { Product } from '@/types/storefront';

defineProps<{
    bestSellerProducts: Product[];
    isCartEnabled: boolean;
}>();

defineEmits<{
    (e: 'select-product', product: Product): void;
    (e: 'add-to-cart', product: Product): void;
}>();
</script>

<template>
    <section class="space-y-6">
        <div
            class="flex items-center justify-between border-b border-neutral-200/80 pb-4 dark:border-neutral-800"
        >
            <div>
                <h2 class="text-2xl font-bold tracking-tight">
                    Best Sellers
                </h2>
                <p class="text-xs text-neutral-500">
                    Our customer favorites and top-ranking essentials
                </p>
            </div>
            <Link
                href="/shop?sortBy=best-seller"
                class="text-xs font-semibold text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400"
            >
                View Best Sellers &rarr;
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="product in bestSellerProducts"
                :key="'best-' + product.id"
                class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900"
            >
                <!-- Badge -->
                <span
                    class="absolute top-3 left-3 z-10 rounded-full bg-indigo-600 px-2 py-0.5 text-[9px] font-bold tracking-wider text-white uppercase"
                >
                    Best Seller
                </span>

                <div
                    class="relative aspect-square w-full cursor-pointer overflow-hidden bg-neutral-100 dark:bg-neutral-800"
                    @click="$emit('select-product', product)"
                >
                    <img
                        v-if="product.image"
                        :src="product.image"
                        :alt="product.name"
                        class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    />
                    <div
                        v-else
                        :class="product.imageGradient"
                        class="absolute inset-0 bg-gradient-to-tr opacity-90 transition duration-500 group-hover:scale-105"
                    ></div>
                    <div
                        v-if="!product.image"
                        class="absolute inset-0 flex items-center justify-center text-4xl font-bold text-white/10 transition duration-500 select-none group-hover:scale-110"
                    >
                        {{
                            product.name
                                .split(' ')
                                .map((w) => w[0])
                                .join('')
                        }}
                    </div>
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 transition duration-300 group-hover:opacity-100"
                    >
                        <span
                            class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-neutral-900 shadow-lg"
                        >
                            <Eye class="h-3.5 w-3.5" /> Quick View
                        </span>
                    </div>
                </div>

                <div class="flex flex-1 flex-col justify-between space-y-3 p-4">
                    <div class="space-y-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-indigo-500 uppercase"
                            >{{ product.category }}</span
                        >
                        <h4
                            class="line-clamp-1 h-5 cursor-pointer text-xs font-bold text-neutral-900 transition group-hover:text-emerald-500 dark:text-white"
                            @click="$emit('select-product', product)"
                        >
                            {{ product.name }}
                        </h4>
                    </div>

                    <div class="flex items-center gap-1 text-[10px] text-amber-500">
                        <Star class="h-3 w-3 fill-current" />
                        <span class="font-bold">{{ product.rating }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span
                            class="font-mono text-sm font-extrabold text-neutral-900 dark:text-white"
                            >{{ $page.props.currency_symbol ?? '$' }}{{ product.price.toFixed(2) }}</span
                        >
                        <button
                            v-if="isCartEnabled"
                            @click="$emit('add-to-cart', product)"
                            :disabled="product.stock === 0"
                            class="rounded-lg bg-neutral-950 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-600 disabled:bg-neutral-200 disabled:text-neutral-500 dark:bg-neutral-800 dark:hover:bg-emerald-600"
                        >
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
