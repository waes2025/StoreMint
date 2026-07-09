<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Sparkles, Megaphone, Palette } from '@lucide/vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    announcement?: {
        enabled: boolean;
        text: string;
        coupon: string;
        bg_color: string;
        text_color: string;
    };
    isAdmin?: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: route('appearance.edit').url,
            },
        ],
    },
});

const form = useForm({
    enabled: props.announcement?.enabled ?? true,
    text: props.announcement?.text ?? '✨ GRAND OPENING OFFER: USE COUPON {coupon} FOR 50% OFF ALL PRODUCTS!',
    coupon: props.announcement?.coupon ?? 'MINT50',
    bg_color: props.announcement?.bg_color ?? '#059669',
    text_color: props.announcement?.text_color ?? '#ffffff',
});

const submit = () => {
    form.patch(route('appearance.update'), {
        preserveScroll: true,
    });
};

const formatAnnouncementText = (text: string, coupon: string) => {
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

const bgPresets = [
    { name: 'Emerald', value: '#059669' },
    { name: 'Indigo', value: '#4f46e5' },
    { name: 'Rose', value: '#e11d48' },
    { name: 'Amber', value: '#d97706' },
    { name: 'Charcoal', value: '#1f2937' },
];

const textPresets = [
    { name: 'White', value: '#ffffff' },
    { name: 'Cream', value: '#fef3c7' },
    { name: 'Soft Blue', value: '#e0f2fe' },
    { name: 'Soft Emerald', value: '#d1fae5' },
];
</script>

<template>
    <Head title="Appearance settings" />

    <h1 class="sr-only">Appearance settings</h1>

    <div class="space-y-8 pb-12">
        <Heading
            variant="small"
            title="Appearance settings"
            description="Customize theme preferences for your account."
        />
        
        <!-- Theme section -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
            <div>
                <h3 class="text-sm font-bold tracking-tight text-neutral-850 dark:text-white">Theme Preference</h3>
                <p class="text-xs text-neutral-500 mb-3">Choose the interface theme that best suits your eyes.</p>
            </div>
            <AppearanceTabs />
        </div>

        <!-- Announcement Bar Customization (Admins Only) -->
        <div v-if="props.isAdmin" class="space-y-6">
            <Heading
                variant="small"
                title="Storefront Announcement Bar"
                description="Manage global announcement promotions shown on the storefront homepage and shop."
            />

            <!-- Real-time Live Preview -->
            <div class="rounded-xl border border-neutral-200 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-950 p-4 space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Live Preview</span>
                <div 
                    v-if="form.enabled"
                    class="flex h-9 items-center justify-center rounded-lg px-4 text-center text-xs font-semibold tracking-wider transition-all duration-300 shadow-sm"
                    :style="{ backgroundColor: form.bg_color, color: form.text_color }"
                >
                    <span class="flex items-center gap-1.5 justify-center flex-wrap">
                        <Sparkles class="h-3.5 w-3.5 shrink-0" />
                        <span v-html="formatAnnouncementText(form.text, form.coupon)"></span>
                    </span>
                </div>
                <div v-else class="flex h-9 items-center justify-center border border-dashed border-neutral-300 rounded-lg text-xs text-neutral-500 dark:border-neutral-800">
                    Announcement Bar is disabled (it won't show on the storefront)
                </div>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 space-y-6">
                <!-- Enable/Disable -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold tracking-tight text-neutral-850 dark:text-white flex items-center gap-2">
                            <Megaphone class="h-4 w-4 text-emerald-500" />
                            <span>Show Announcement Bar</span>
                        </h4>
                        <p class="text-xs text-neutral-500">Toggle whether the announcement banner is active on the storefront.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            v-model="form.enabled" 
                            class="sr-only peer"
                        />
                        <div class="w-9 h-5 bg-neutral-200 dark:bg-neutral-800 rounded-full peer peer-focus:ring-2 peer-focus:ring-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-neutral-700 peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <div v-if="form.enabled" class="space-y-4 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    <!-- Text content -->
                    <div class="grid gap-2">
                        <Label for="announcement-text">Announcement Text</Label>
                        <Input
                            id="announcement-text"
                            v-model="form.text"
                            placeholder="e.g. GRAND OPENING OFFER: USE COUPON {coupon} FOR 50% OFF!"
                            required
                            class="w-full"
                        />
                        <span class="text-[10px] text-neutral-500">Use <code class="bg-neutral-100 dark:bg-neutral-800 px-1 py-0.5 rounded font-mono text-[9px]">{coupon}</code> as a placeholder to display the styled coupon code badge.</span>
                        <p v-if="form.errors.text" class="text-xs text-red-500 font-semibold">{{ form.errors.text }}</p>
                    </div>

                    <!-- Coupon code -->
                    <div class="grid gap-2">
                        <Label for="announcement-coupon">Coupon Code (to embed)</Label>
                        <Input
                            id="announcement-coupon"
                            v-model="form.coupon"
                            placeholder="e.g. MINT50"
                            class="w-full"
                        />
                        <p v-if="form.errors.coupon" class="text-xs text-red-500 font-semibold">{{ form.errors.coupon }}</p>
                    </div>

                    <!-- Colors -->
                    <div class="grid gap-6 sm:grid-cols-2 pt-2">
                        <!-- BG Color -->
                        <div class="space-y-3">
                            <Label class="flex items-center gap-1.5">
                                <Palette class="h-4 w-4 text-neutral-500" />
                                <span>Background Color</span>
                            </Label>
                            
                            <!-- Presets -->
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="preset in bgPresets" 
                                    :key="preset.value"
                                    type="button"
                                    @click="form.bg_color = preset.value"
                                    :style="{ backgroundColor: preset.value }"
                                    class="h-6 px-2.5 rounded text-[10px] font-semibold text-white border border-black/10 shadow-xs hover:scale-105 active:scale-95 transition-all"
                                    :title="preset.name"
                                >
                                    {{ preset.name }}
                                </button>
                            </div>

                            <!-- Custom Color Picker -->
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    id="bg-picker" 
                                    v-model="form.bg_color" 
                                    class="h-8 w-8 cursor-pointer rounded-lg border border-neutral-200 dark:border-neutral-800" 
                                />
                                <Input 
                                    v-model="form.bg_color" 
                                    placeholder="#059669"
                                    class="h-8 w-28 text-xs font-mono" 
                                />
                            </div>
                            <p v-if="form.errors.bg_color" class="text-xs text-red-500 font-semibold">{{ form.errors.bg_color }}</p>
                        </div>

                        <!-- Text Color -->
                        <div class="space-y-3">
                            <Label class="flex items-center gap-1.5">
                                <Palette class="h-4 w-4 text-neutral-500" />
                                <span>Text Color</span>
                            </Label>
                            
                            <!-- Presets -->
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="preset in textPresets" 
                                    :key="preset.value"
                                    type="button"
                                    @click="form.text_color = preset.value"
                                    class="h-6 px-2.5 rounded text-[10px] font-semibold bg-neutral-100 hover:bg-neutral-200 border border-neutral-300 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:border-neutral-700 dark:text-white transition-all hover:scale-105 active:scale-95"
                                    :style="{ color: preset.value }"
                                    :title="preset.name"
                                >
                                    {{ preset.name }}
                                </button>
                            </div>

                            <!-- Custom Color Picker -->
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    id="text-picker" 
                                    v-model="form.text_color" 
                                    class="h-8 w-8 cursor-pointer rounded-lg border border-neutral-200 dark:border-neutral-800" 
                                />
                                <Input 
                                    v-model="form.text_color" 
                                    placeholder="#ffffff"
                                    class="h-8 w-28 text-xs font-mono" 
                                />
                            </div>
                            <p v-if="form.errors.text_color" class="text-xs text-red-500 font-semibold">{{ form.errors.text_color }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                    <Button :disabled="form.processing" type="submit">
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
