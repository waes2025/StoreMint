<script setup lang="ts">
import { computed, ref } from 'vue';
import { Search } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';

interface Product {
    id: number;
    name: string;
    category: string;
    price: number;
    stock: number;
    status: string;
}

interface Props {
    products: Product[];
}

const props = defineProps<Props>();
const page = usePage();

const searchQuery = ref('');

const filteredProducts = computed(() => {
    if (!props.products) return [];
    
    return props.products.filter(
        (product) =>
            product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            product.category.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <div class="space-y-4">
        <!-- Search Header -->
        <div
            class="flex flex-col justify-between gap-4 border-b border-neutral-100 pb-4 sm:flex-row sm:items-center dark:border-neutral-800"
        >
            <div class="relative max-w-sm flex-1">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400"
                />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search products..."
                    class="h-10 w-full rounded-lg border border-neutral-200 bg-white pr-4 pl-10 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                />
            </div>
        </div>

        <!-- Products Table -->
        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/40"
                        >
                            <th class="w-16 p-4 font-semibold">ID</th>
                            <th class="p-4 font-semibold">Product Name</th>
                            <th class="w-32 p-4 font-semibold">Category</th>
                            <th class="w-28 p-4 text-center font-semibold">
                                Price
                            </th>
                            <th class="w-28 p-4 text-center font-semibold">
                                Stock Level
                            </th>
                            <th class="w-28 p-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 dark:divide-neutral-800/50"
                    >
                        <tr
                            v-for="product in filteredProducts"
                            :key="product.id"
                            class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20"
                        >
                            <td class="p-4 font-mono text-neutral-400">
                                #00{{ product.id }}
                            </td>
                            <td class="p-4 font-semibold">
                                {{ product.name }}
                            </td>
                            <td class="p-4 text-neutral-500">
                                {{ product.category }}
                            </td>
                            <td class="p-4 text-center font-mono font-bold">
                                {{ $page.props.currency_symbol ?? '$'
                                }}{{ product.price.toFixed(2) }}
                            </td>
                            <td class="p-4 text-center">
                                <span
                                    class="font-mono font-bold text-neutral-800 dark:text-neutral-200"
                                    >{{ product.stock }}</span
                                >
                            </td>
                            <td class="p-4">
                                <span
                                    v-if="product.status === 'In Stock'"
                                    class="inline-flex rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-bold text-green-600 dark:bg-green-950 dark:text-green-400"
                                >
                                    In Stock
                                </span>
                                <span
                                    v-else-if="
                                        product.status === 'Low Stock'
                                    "
                                    class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                                >
                                    Low Stock
                                </span>
                                <span
                                    v-else
                                    class="inline-flex rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-600 dark:bg-red-950 dark:text-red-400"
                                >
                                    Out of Stock
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
