<script setup lang="ts">
import SupportInfo from './SupportInfo.vue';
import SupportFAQ from './SupportFAQ.vue';
import { Info } from '@lucide/vue';
import { ref } from 'vue';

const chatMessages = ref([
    { sender: 'assistant', text: 'Hello! I’m Minty, your virtual support assistant.' },
    { sender: 'assistant', text: 'Share your issue and I’ll help you get the right support channel.' },
]);
const chatOptions = ['Order Status', 'Billing Question', 'Return Policy', 'Speak to Support'];
const chatProcessing = ref(false);
</script>

<template>
    <div class="grid items-start gap-8 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-7">
            <SupportInfo />
            <SupportFAQ />
        </div>

        <div class="overflow-hidden rounded-3xl border border-neutral-200 bg-white shadow-sm lg:col-span-5 dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center gap-2 bg-emerald-600 p-4 text-white">
                <div class="relative">
                    <span class="block h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-300"></span>
                </div>
                <div>
                    <h4 class="text-xs font-bold">Minty Live Assistant</h4>
                    <span class="text-[10px] opacity-75">Typically replies instantly</span>
                </div>
            </div>

            <div class="h-80 space-y-3 overflow-y-auto bg-neutral-50 p-4 dark:bg-neutral-950/20">
                <div
                    v-for="(msg, index) in chatMessages"
                    :key="index"
                    :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'"
                    class="flex"
                >
                    <div
                        :class="msg.sender === 'user'
                            ? 'rounded-br-none bg-emerald-600 text-white'
                            : 'rounded-bl-none border border-neutral-100 bg-white text-neutral-800 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200'"
                        class="max-w-[85%] rounded-2xl px-4 py-2.5 text-xs shadow-xs"
                    >
                        {{ msg.text }}
                    </div>
                </div>

                <div v-if="chatProcessing" class="flex justify-start">
                    <div class="flex items-center gap-1 rounded-2xl rounded-bl-none border border-neutral-100 bg-white px-4 py-2 text-xs dark:border-neutral-800 dark:bg-neutral-900">
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-400" style="animation-delay: 0ms"></span>
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-400" style="animation-delay: 150ms"></span>
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-neutral-400" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            </div>

            <div class="space-y-2 border-t border-neutral-100 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
                <p class="text-[10px] font-semibold text-neutral-400 uppercase">Quick Actions:</p>
                <div class="flex flex-col gap-2">
                    <button
                        v-for="option in chatOptions"
                        :key="option"
                        class="w-full rounded-2xl border border-neutral-200 px-3 py-2 text-left text-xs font-semibold text-neutral-700 transition hover:border-emerald-200 hover:bg-emerald-50 dark:border-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-950"
                    >
                        {{ option }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
