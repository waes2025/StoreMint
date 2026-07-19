<script setup lang="ts">
import { ref } from 'vue';
import { Plus, Minus } from '@lucide/vue';

const faqs = [
    {
        q: 'How long does shipping take?',
        a: 'Standard shipping takes 3-5 business days. Express shipping options take 1-2 business days. Tracking details will be emailed to you immediately after shipment.',
        shipping: true,
    },
    {
        q: 'What is your refund/return policy?',
        a: 'We offer a 30-day hassle-free return policy. If you are not satisfied with your purchase, please email returns@storemint.com with your receipt to start the process.',
    },
    {
        q: 'Do you offer international delivery?',
        a: 'Yes! We deliver worldwide. Shipping rates and delivery timeframes vary by country and are calculated at checkout.',
        shipping: true,
    },
    {
        q: 'How can I update my billing information?',
        a: 'You can update your personal billing information from the profile settings within the Admin Dashboard.',
    },
];

const expandedFaq = ref<number | null>(null);
const toggleFaq = (index: number) => {
    expandedFaq.value = expandedFaq.value === index ? null : index;
};
</script>

<template>
    <div class="space-y-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div>
            <h3 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">Frequently Asked Questions</h3>
            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                Browse common questions, then start a ticket if you need personalized help.
            </p>
        </div>

        <div class="space-y-3">
            <div
                v-for="(faq, index) in faqs"
                :key="faq.q"
                class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-950"
            >
                <button
                    @click="toggleFaq(index)"
                    class="flex w-full items-center justify-between px-5 py-4 text-left text-sm font-semibold text-neutral-800 transition hover:text-emerald-600 dark:text-neutral-200"
                >
                    <span>{{ faq.q }}</span>
                    <span>
                        <Plus v-if="expandedFaq !== index" class="h-4 w-4 text-neutral-400" />
                        <Minus v-else class="h-4 w-4 text-emerald-500" />
                    </span>
                </button>
                <div
                    v-show="expandedFaq === index"
                    class="border-t border-neutral-200 px-5 py-4 text-sm leading-6 text-neutral-500 dark:border-neutral-800 dark:text-neutral-400"
                >
                    {{ faq.a }}
                </div>
            </div>
        </div>
    </div>
</template>
