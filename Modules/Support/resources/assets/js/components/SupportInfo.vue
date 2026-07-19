<script setup lang="ts">
import { Info, Phone, Mail } from '@lucide/vue';
</script>

<template>
    <div class="space-y-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-start gap-3">
            <div class="mt-1 rounded-2xl bg-emerald-500/10 p-3 text-emerald-500 dark:bg-emerald-500/10">
                <Info class="h-5 w-5" />
            </div>
            <div class="space-y-2">
                <h2 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">StoreMint Help Desk</h2>
                <p class="text-sm leading-6 text-neutral-500 dark:text-neutral-400">
                    Get fast answers from our support team, browse common questions, or submit a ticket with order details.
                </p>
            </div>
        </div>

        <div class="grid gap-3 text-sm text-neutral-600 dark:text-neutral-300">
            <div class="flex items-center gap-2 rounded-2xl bg-neutral-50 px-4 py-3 dark:bg-neutral-950/40">
                <Phone class="h-4 w-4 text-emerald-500" />
                <span>+1 (800) 555-0199</span>
            </div>
            <div class="flex items-center gap-2 rounded-2xl bg-neutral-50 px-4 py-3 dark:bg-neutral-950/40">
                <Mail class="h-4 w-4 text-emerald-500" />
                <span>support@storemint.com</span>
            </div>
        </div>

        <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">
            Review the FAQs for quick help. If you still need assistance, create a support ticket and our team will follow up promptly.
        </div>
    </div>
</template>
