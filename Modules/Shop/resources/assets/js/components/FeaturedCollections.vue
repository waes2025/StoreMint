<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShoppingBag, Sparkles, Leaf, LayoutGrid } from '@lucide/vue';

defineProps<{
    categories: string[];
}>();
</script>

<template>
    <section class="space-y-6">
        <div
            class="flex items-center justify-between border-b border-neutral-200/80 pb-4 dark:border-neutral-800"
        >
            <div>
                <h2 class="text-2xl font-bold tracking-tight">
                    Featured Collections
                </h2>
                <p class="text-xs text-neutral-500">
                    Shop by select categories crafted for your aesthetic
                </p>
            </div>
            <Link
                href="/shop?tab=categories"
                class="text-xs font-semibold text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400"
            >
                View All Categories &rarr;
            </Link>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="(cat, index) in categories
                    .filter((c) => c !== 'All')
                    .slice(0, 3)"
                :key="cat"
                :href="`/shop?category=${cat}`"
                class="group relative block cursor-pointer overflow-hidden rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm transition duration-300 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900"
            >
                <!-- Card Gradient backdrop visual -->
                <div
                    :class="[
                        index % 3 === 0
                            ? 'from-amber-500/10 to-orange-500/10'
                            : index % 3 === 1
                              ? 'from-emerald-500/10 to-teal-500/10'
                              : 'from-blue-500/10 to-indigo-500/10',
                    ]"
                    class="absolute inset-0 bg-gradient-to-br opacity-50 transition duration-300 group-hover:opacity-100"
                ></div>

                <div
                    class="relative z-10 flex h-36 flex-col justify-between"
                >
                    <div class="flex items-start justify-between">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400"
                        >
                            <ShoppingBag
                                v-if="cat === 'Accessories'"
                                class="h-6 w-6"
                            />
                            <Sparkles
                                v-else-if="cat === 'Electronics'"
                                class="h-6 w-6"
                            />
                            <Leaf
                                v-else-if="cat === 'Fashion'"
                                class="h-6 w-6"
                            />
                            <LayoutGrid v-else class="h-6 w-6" />
                        </div>
                        <span
                            class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400"
                        >
                            Go to Collection
                        </span>
                    </div>

                    <div>
                        <h3
                            class="text-lg font-bold text-neutral-900 transition group-hover:text-emerald-500 dark:text-white"
                        >
                            {{ cat }}
                        </h3>
                        <p
                            class="mt-1 text-xs text-neutral-500 dark:text-neutral-400"
                        >
                            Explore our premium selection of hand-crafted {{ cat.toLowerCase() }}.
                        </p>
                    </div>
                </div>
            </Link>
        </div>
    </section>
</template>
