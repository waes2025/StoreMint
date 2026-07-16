<script setup lang="ts">
import { computed } from 'vue';
import { SlidersHorizontal, Search } from '@lucide/vue';
import { Product } from '@/types/storefront';

const props = defineProps<{
    categories: string[];
    brands: string[];
    activeProducts: Product[];
    searchQuery: string;
    selectedCategory: string;
    selectedBrand: string;
    minPrice: number | null;
    maxPrice: number | null;
    showInStockOnly: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:searchQuery', val: string): void;
    (e: 'update:selectedCategory', val: string): void;
    (e: 'update:selectedBrand', val: string): void;
    (e: 'update:minPrice', val: number | null): void;
    (e: 'update:maxPrice', val: number | null): void;
    (e: 'update:showInStockOnly', val: boolean): void;
    (e: 'clear-all'): void;
}>();

const searchQueryComputed = computed({
    get: () => props.searchQuery,
    set: (val) => emit('update:searchQuery', val)
});

const minPriceComputed = computed({
    get: () => props.minPrice,
    set: (val) => emit('update:minPrice', val)
});

const maxPriceComputed = computed({
    get: () => props.maxPrice,
    set: (val) => emit('update:maxPrice', val)
});

const showInStockOnlyComputed = computed({
    get: () => props.showInStockOnly,
    set: (val) => emit('update:showInStockOnly', val)
});

const hasActiveFilters = computed(() => {
    return props.searchQuery ||
        props.selectedCategory !== 'All' ||
        props.selectedBrand !== 'All' ||
        props.minPrice !== null ||
        props.maxPrice !== null ||
        props.showInStockOnly;
});
</script>

<template>
    <!-- Sidebar Filters (3 Cols on Desktop) -->
    <aside
        class="space-y-6 rounded-xl border border-neutral-200 bg-white p-5 shadow-xs lg:col-span-3 dark:border-neutral-800 dark:bg-neutral-900"
    >
        <div class="flex items-center justify-between">
            <h3
                class="flex items-center gap-2 text-sm font-bold tracking-tight"
            >
                <SlidersHorizontal
                    class="h-4 w-4 text-emerald-500"
                />
                <span>Filters</span>
            </h3>
            <button
                v-if="hasActiveFilters"
                @click="emit('clear-all')"
                class="text-xs font-semibold text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400"
            >
                Clear All
            </button>
        </div>

        <!-- Search -->
        <div class="space-y-2">
            <label
                class="text-[10px] font-bold tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                >Search Product</label
            >
            <div class="relative">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400"
                />
                <input
                    v-model="searchQueryComputed"
                    type="text"
                    placeholder="Type to search..."
                    class="w-full rounded-lg border border-neutral-200 py-2 pr-4 pl-9 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-white"
                />
            </div>
        </div>

        <!-- Categories List -->
        <div class="space-y-2">
            <label
                class="text-[10px] font-bold tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                >Category</label
            >
            <div class="flex flex-col gap-1">
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="emit('update:selectedCategory', cat)"
                    :class="
                        selectedCategory === cat
                            ? 'bg-emerald-50 font-semibold text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                            : 'text-neutral-600 hover:bg-neutral-50 dark:text-neutral-400 dark:hover:bg-neutral-800/40'
                    "
                    class="flex items-center justify-between rounded-lg px-3 py-2 text-left text-xs transition"
                >
                    <span>{{ cat }}</span>
                    <span
                        v-if="selectedCategory === cat"
                        class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                    ></span>
                </button>
            </div>
        </div>

        <!-- Brands List -->
        <div
            class="space-y-2 border-t border-neutral-100 pt-3 dark:border-neutral-800"
        >
            <label
                class="text-[10px] font-bold tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                >Brand</label
            >
            <div class="flex flex-col gap-1">
                <button
                    v-for="brand in brands"
                    :key="brand"
                    @click="emit('update:selectedBrand', brand)"
                    :class="
                        selectedBrand === brand
                            ? 'bg-emerald-50 font-semibold text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                            : 'text-neutral-600 hover:bg-neutral-50 dark:text-neutral-400 dark:hover:bg-neutral-800/40'
                    "
                    class="flex items-center justify-between rounded-lg px-3 py-2 text-left text-xs transition"
                >
                    <span>{{ brand }}</span>
                    <span class="flex items-center gap-1.5">
                        <span
                            class="rounded bg-neutral-100 px-1.5 py-0.5 text-[9px] font-bold text-neutral-500 dark:bg-neutral-800/80 dark:text-neutral-400"
                        >
                            {{
                                brand === 'All'
                                    ? activeProducts.length
                                    : activeProducts.filter((p) => p.brand === brand).length
                            }}
                        </span>
                        <span
                            v-if="selectedBrand === brand"
                            class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                        ></span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Price Range -->
        <div class="space-y-3">
            <label
                class="text-[10px] font-bold tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                >Price Range</label
            >
            <div class="grid grid-cols-2 gap-2">
                <div class="space-y-1">
                    <span
                        class="text-[10px] text-neutral-400"
                        >Min ({{ $page.props.currency_symbol ?? '$' }})</span
                    >
                    <input
                        v-model.number="minPriceComputed"
                        type="number"
                        placeholder="0"
                        class="w-full rounded-lg border border-neutral-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-white"
                    />
                </div>
                <div class="space-y-1">
                    <span
                        class="text-[10px] text-neutral-400"
                        >Max ({{ $page.props.currency_symbol ?? '$' }})</span
                    >
                    <input
                        v-model.number="maxPriceComputed"
                        type="number"
                        placeholder="500"
                        class="w-full rounded-lg border border-neutral-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none dark:border-neutral-800 dark:bg-neutral-950 dark:text-white"
                    />
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5 pt-1">
                <button
                    @click="
                        emit('update:minPrice', null);
                        emit('update:maxPrice', 100);
                    "
                    class="rounded bg-neutral-100 px-2.5 py-1 text-[10px] font-medium text-neutral-600 transition hover:bg-emerald-500 hover:text-white dark:bg-neutral-800 dark:text-neutral-400"
                >
                    Under
                    {{ $page.props.currency_symbol ?? '$' }}100
                </button>
                <button
                    @click="
                        emit('update:minPrice', 100);
                        emit('update:maxPrice', 250);
                    "
                    class="rounded bg-neutral-100 px-2.5 py-1 text-[10px] font-medium text-neutral-600 transition hover:bg-emerald-500 hover:text-white dark:bg-neutral-800 dark:text-neutral-400"
                >
                    {{ $page.props.currency_symbol ?? '$' }}100 -
                    {{ $page.props.currency_symbol ?? '$' }}250
                </button>
                <button
                    @click="
                        emit('update:minPrice', 250);
                        emit('update:maxPrice', null);
                    "
                    class="rounded bg-neutral-100 px-2.5 py-1 text-[10px] font-medium text-neutral-600 transition hover:bg-emerald-500 hover:text-white dark:bg-neutral-800 dark:text-neutral-400"
                >
                    {{ $page.props.currency_symbol ?? '$' }}250+
                </button>
            </div>
        </div>

        <!-- Availability Toggle -->
        <div
            class="space-y-2 border-t border-neutral-100 pt-3 dark:border-neutral-800"
        >
            <label
                class="flex cursor-pointer items-center gap-2 select-none"
            >
                <input
                    v-model="showInStockOnlyComputed"
                    type="checkbox"
                    class="h-4 w-4 rounded border-neutral-300 text-emerald-600 focus:ring-emerald-500 dark:border-neutral-800 dark:bg-neutral-950"
                />
                <span
                    class="text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                    >In Stock Only</span
                >
            </label>
        </div>
    </aside>
</template>
