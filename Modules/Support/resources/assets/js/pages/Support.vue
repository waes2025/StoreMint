<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SupportHelpCenter from '../components/SupportHelpCenter.vue';

const props = defineProps<{}>();
</script>

<template>
    <Head title="Support" />

    <div class="min-h-screen bg-neutral-50 px-6 py-10 text-neutral-800 dark:bg-neutral-950 dark:text-neutral-200">
        <SupportHelpCenter />
    </div>
</template>
