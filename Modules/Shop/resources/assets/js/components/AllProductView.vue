<script setup lang="ts">
import { computed } from 'vue';
import { Eye, Star, Search } from '@lucide/vue';
import { Product } from '@/types/storefront';

const props = defineProps<{
    filteredProducts: Product[];
    sortBy: string;
    isCartEnabled: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:sortBy', val: string): void;
    (e: 'select-product', product: Product): void;
    (e: 'add-to-cart', product: Product): void;
    (e: 'reset-filters'): void;
}>();

const sortByComputed = computed({
    get: () => props.sortBy,
    set: (val) => emit('update:sortBy', val)
});
</script>

<template>
    <!-- Product Display Area (9 Cols on Desktop) -->
    <div class="space-y-6 lg:col-span-9">
        <!-- Filter Metadata Header -->
        <div
            class="flex items-center justify-between gap-4 rounded-xl border border-neutral-100 bg-neutral-50 px-4 py-3 dark:border-neutral-800/60 dark:bg-neutral-900/40"
        >
            <span
                class="text-xs text-neutral-500 dark:text-neutral-400"
            >
                Showing
                <span
                    class="font-bold text-neutral-800 dark:text-white"
                    >{{ filteredProducts.length }}</span
                >
                products
            </span>

            <div class="flex items-center gap-2">
                <span class="text-xs text-neutral-400"
                    >Sort by:</span
                >
                <select
                    v-model="sortByComputed"
                    class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-white"
                >
                    <option value="featured">
                        Featured
                    </option>
                    <option value="price-asc">
                        Price: Low to High
                    </option>
                    <option value="price-desc">
                        Price: High to Low
                    </option>
                    <option value="rating">
                        Top Rated
                    </option>
                    <option value="best-seller">
                        Best Sellers
                    </option>
                </select>
            </div>
        </div>

        <!-- Grid Layout: 1 col mobile, 2 col tablet, 3 col desktop (within the 9 cols) -->
        <div
            v-if="filteredProducts.length > 0"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <!-- Product Card -->
            <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900"
            >
                <!-- Badge -->
                <span
                    v-if="product.badge"
                    :class="product.badgeColor"
                    class="absolute top-3 left-3 z-10 rounded-full px-2 py-0.5 text-[10px] font-bold tracking-wider uppercase"
                >
                    {{ product.badge }}
                </span>

                <!-- Product Image -->
                <div
                    class="relative aspect-square w-full cursor-pointer overflow-hidden bg-neutral-100 dark:bg-neutral-800"
                    @click="emit('select-product', product)"
                >
                    <img
                        v-if="product.image"
                        :src="product.image"
                        :alt="product.name"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                    <div
                        v-else
                        :class="product.imageGradient"
                        class="absolute inset-0 bg-gradient-to-tr opacity-90 transition-transform duration-500 group-hover:scale-105"
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
                            <Eye class="h-3.5 w-3.5" />
                            Quick View
                        </span>
                    </div>
                </div>

                <!-- Product Detail & Actions -->
                <div
                    class="flex flex-1 flex-col space-y-3 p-5"
                >
                    <div class="space-y-1">
                        <span
                            class="text-[10px] font-semibold tracking-wider text-emerald-500 uppercase"
                            >{{ product.category }}</span
                        >
                        <h3
                            class="line-clamp-2 h-10 cursor-pointer text-sm leading-tight font-semibold text-neutral-900 transition hover:text-emerald-500 dark:text-white"
                            @click="emit('select-product', product)"
                        >
                            {{ product.name }}
                        </h3>
                    </div>

                    <!-- Rating -->
                    <div
                        class="flex items-center gap-1 text-xs text-amber-500"
                    >
                        <Star
                            class="h-3 w-3 fill-current"
                        />
                        <span class="font-bold">{{
                            product.rating
                        }}</span>
                        <span class="text-neutral-400"
                            >({{
                                product.reviewsCount
                            }})</span
                        >
                    </div>

                    <!-- Bottom Row: Price & Add to Cart -->
                    <div
                        class="flex items-center justify-between pt-2"
                    >
                        <div class="flex flex-col">
                            <span
                                class="font-mono text-base font-bold text-neutral-900 dark:text-white"
                            >
                                {{
                                    $page.props.currency_symbol ?? '$'
                                }}{{
                                    product.price.toFixed(2)
                                }}
                            </span>
                            <span
                                v-if="product.originalPrice"
                                class="font-mono text-xs text-neutral-400 line-through"
                            >
                                {{
                                    $page.props.currency_symbol ?? '$'
                                }}{{
                                    product.originalPrice.toFixed(2)
                                }}
                            </span>
                        </div>

                        <button
                            v-if="isCartEnabled"
                            @click="emit('add-to-cart', product)"
                            :disabled="product.stock === 0"
                            class="rounded-lg bg-neutral-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600 disabled:bg-neutral-200 disabled:text-neutral-500 dark:bg-neutral-800 dark:hover:bg-emerald-600 dark:disabled:bg-neutral-800"
                        >
                            {{
                                product.stock === 0
                                    ? 'Out of Stock'
                                    : 'Add'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- No products found view -->
        <div
            v-else
            class="flex flex-col items-center justify-center space-y-4 rounded-xl border border-dashed border-neutral-200 bg-white py-20 text-center dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div
                class="rounded-full bg-neutral-100 p-4 dark:bg-neutral-950"
            >
                <Search class="h-8 w-8 text-neutral-400" />
            </div>
            <h3
                class="font-bold text-neutral-800 dark:text-neutral-200"
            >
                No products found
            </h3>
            <p class="max-w-xs text-xs text-neutral-500">
                We couldn't find any products matching your
                current filters. Try resetting or adjusting
                them.
            </p>
            <button
                @click="emit('reset-filters')"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
            >
                Reset All Filters
            </button>
        </div>
    </div>
</template>
