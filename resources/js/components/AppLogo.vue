<script setup lang="ts">
import { Leaf } from '@lucide/vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-gradient-to-tr from-emerald-400 to-emerald-600 text-white"
    >
        <Leaf class="size-4" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate leading-tight font-semibold">
            Store<span class="text-emerald-500 dark:text-emerald-400">Mint</span>
        </span>
    </div>
</template>
