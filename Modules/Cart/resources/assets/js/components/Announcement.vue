<script setup lang="ts">
import { Sparkles } from '@lucide/vue';

const props = defineProps<{
    announcement?: {
        enabled: boolean;
        text: string;
        coupon: string;
        bg_color: string;
        text_color: string;
    };
}>();

const formatAnnouncementText = (text?: string, coupon?: string) => {
    if (!text) return '';
    const escapedText = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const safeCoupon = coupon
        ? coupon
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;')
        : '';

    const badgeHtml = `<span class="rounded bg-white/20 px-1 py-0.5 font-mono text-amber-200 font-bold mx-1">${safeCoupon}</span>`;
    return escapedText.replace('{coupon}', badgeHtml);
};
</script>

<template>
    <div
        v-if="props.announcement?.enabled"
        class="animate-fade-in flex h-9 items-center justify-center px-4 text-center text-xs font-semibold tracking-wider transition-all duration-300"
        :style="{
            backgroundColor: props.announcement.bg_color,
            color: props.announcement.text_color,
        }"
    >
        <span class="flex flex-wrap items-center justify-center gap-1.5">
            <Sparkles class="h-3.5 w-3.5 shrink-0" />
            <span
                v-html="
                    formatAnnouncementText(
                        props.announcement.text,
                        props.announcement.coupon,
                    )
                "
            ></span>
        </span>
    </div>
</template>
