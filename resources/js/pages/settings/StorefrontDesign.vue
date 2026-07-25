<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { route } from '@/lib/route';
import {
    Palette,
    Type,
    Layout,
    Sparkles,
    Globe,
    Layers,
    Sliders,
    Eye,
    Plus,
    Trash2,
    Check,
    Phone,
    Mail,
    ShoppingBag,
    Leaf,
    ShoppingCart,
    ArrowRight,
} from '@lucide/vue';

const props = defineProps<{
    design?: Record<string, any>;
}>();

const page = usePage();

const activeTab = ref('colors');

const fontOptions = [
    'Inter',
    'Outfit',
    'Roboto',
    'Poppins',
    'Plus Jakarta Sans',
    'Playfair Display',
    'Cinzel',
    'Montserrat',
    'Lato',
    'Open Sans',
];

const form = useForm({
    // Store identity
    store_name: props.design?.store_name ?? 'StoreMint',
    store_tagline: props.design?.store_tagline ?? 'E-Commerce Redefined',
    store_description: props.design?.store_description ?? 'Discover premium lifestyle goods, curated for you.',
    logo_url: props.design?.logo_url ?? '',
    favicon_url: props.design?.favicon_url ?? '',

    // Colors
    primary_color: props.design?.primary_color ?? '#10b981',
    secondary_color: props.design?.secondary_color ?? '#0d9488',
    accent_color: props.design?.accent_color ?? '#f59e0b',
    hero_bg_color: props.design?.hero_bg_color ?? '#171717',
    header_bg_color: props.design?.header_bg_color ?? '#ffffff',
    footer_bg_color: props.design?.footer_bg_color ?? '#111827',
    topbar_bg_color: props.design?.topbar_bg_color ?? '#064e3b',
    topbar_text_color: props.design?.topbar_text_color ?? '#ffffff',
    body_bg_color: props.design?.body_bg_color ?? '#f9fafb',
    text_color: props.design?.text_color ?? '#1f2937',

    // Typography
    font_family: props.design?.font_family ?? 'Inter',
    heading_font: props.design?.heading_font ?? 'Inter',
    base_font_size: props.design?.base_font_size ?? 16,

    // Header
    header_style: props.design?.header_style ?? 'classic',
    show_topbar: props.design?.show_topbar ?? true,
    topbar_phone: props.design?.topbar_phone ?? '+1 (800) 555-0199',
    topbar_email: props.design?.topbar_email ?? 'support@storemint.com',
    logo_height: props.design?.logo_height ?? 40,

    // Hero section
    hero_style: props.design?.hero_style ?? 'fullwidth',
    hero_badge_text: props.design?.hero_badge_text ?? 'REDESIGNED PLATFORM',
    hero_heading: props.design?.hero_heading ?? 'State-of-the-Art E-Commerce',
    hero_subheading: props.design?.hero_subheading ?? 'Designed strictly according to modern Design Grid & System Guidelines.',
    hero_cta_text: props.design?.hero_cta_text ?? 'Shop the Collection',
    hero_cta_secondary: props.design?.hero_cta_secondary ?? 'View Categories',
    hero_image_url: props.design?.hero_image_url ?? '',
    show_hero_badge: props.design?.show_hero_badge ?? true,

    // Featured Brands
    show_brands: props.design?.show_brands ?? true,
    brands_title: props.design?.brands_title ?? 'Trusted Brands & Partners',
    brands_list: Array.isArray(props.design?.brands_list)
        ? [...props.design.brands_list]
        : ['Apple', 'Nike', 'Samsung', 'Sony', 'Adidas', 'Puma'],

    // Footer
    footer_style: props.design?.footer_style ?? 'multi_column',
    show_footer: props.design?.show_footer ?? true,
    footer_tagline: props.design?.footer_tagline ?? 'Your one-stop premium e-commerce platform.',
    footer_copyright: props.design?.footer_copyright ?? '© 2025 StoreMint. All rights reserved.',
    show_newsletter: props.design?.show_newsletter ?? true,
    show_social_links: props.design?.show_social_links ?? true,

    // Social links
    social_facebook: props.design?.social_facebook ?? 'https://facebook.com',
    social_instagram: props.design?.social_instagram ?? 'https://instagram.com',
    social_twitter: props.design?.social_twitter ?? 'https://twitter.com',
    social_youtube: props.design?.social_youtube ?? 'https://youtube.com',
    social_tiktok: props.design?.social_tiktok ?? '',
    social_linkedin: props.design?.social_linkedin ?? 'https://linkedin.com',
    social_github: props.design?.social_github ?? 'https://github.com',

    // Layout
    layout_width: props.design?.layout_width ?? 1280,
    border_radius: props.design?.border_radius ?? 'xl',
    card_shadow: props.design?.card_shadow ?? 'sm',

    // SEO
    meta_title: props.design?.meta_title ?? 'StoreMint – Premium E-Commerce',
    meta_description: props.design?.meta_description ?? 'Shop the finest products at StoreMint.',
    og_image_url: props.design?.og_image_url ?? '',
});

const newBrandInput = ref('');

function addBrand() {
    if (newBrandInput.value.trim()) {
        form.brands_list.push(newBrandInput.value.trim());
        newBrandInput.value = '';
    }
}

function removeBrand(index: number) {
    form.brands_list.splice(index, 1);
}

function submit() {
    form.patch(route('settings.storefront-design.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Storefront Design & Styling" />

    <div class="space-y-6 pb-16">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <Heading
                title="Storefront Style & Design"
                description="Fully customize your storefront style, colors, fonts, brands, hero section, header, and footer from the admin panel."
            />
            <button
                @click="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:opacity-50"
            >
                <Check class="h-4 w-4" />
                <span>{{ form.processing ? 'Saving...' : 'Save Design Settings' }}</span>
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap border-b border-neutral-200 dark:border-neutral-800">
            <button
                @click="activeTab = 'colors'"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer',
                    activeTab === 'colors'
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                ]"
            >
                <Palette class="h-4 w-4" />
                <span>Colors & Palette</span>
            </button>
            <button
                @click="activeTab = 'typography'"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer',
                    activeTab === 'typography'
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                ]"
            >
                <Type class="h-4 w-4" />
                <span>Typography</span>
            </button>
            <button
                @click="activeTab = 'header'"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer',
                    activeTab === 'header'
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                ]"
            >
                <Layout class="h-4 w-4" />
                <span>Header & Topbar</span>
            </button>
            <button
                @click="activeTab = 'hero'"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer',
                    activeTab === 'hero'
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                ]"
            >
                <Sparkles class="h-4 w-4" />
                <span>Hero Section</span>
            </button>
            <button
                @click="activeTab = 'brands'"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer',
                    activeTab === 'brands'
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                ]"
            >
                <Layers class="h-4 w-4" />
                <span>Featured Brands</span>
            </button>
            <button
                @click="activeTab = 'footer'"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer',
                    activeTab === 'footer'
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                ]"
            >
                <Globe class="h-4 w-4" />
                <span>Footer & Socials</span>
            </button>
            <button
                @click="activeTab = 'layout'"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer',
                    activeTab === 'layout'
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                ]"
            >
                <Sliders class="h-4 w-4" />
                <span>Layout & Aesthetics</span>
            </button>
            <button
                @click="activeTab = 'preview'"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer',
                    activeTab === 'preview'
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300',
                ]"
            >
                <Eye class="h-4 w-4" />
                <span>Live Preview</span>
            </button>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- TAB 1: COLORS & PALETTE -->
            <div v-if="activeTab === 'colors'" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Color Theme & Palette</h3>
                <p class="text-xs text-neutral-500">Configure global color tokens for your storefront interface.</p>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Primary Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.primary_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.primary_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Secondary Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.secondary_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.secondary_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Accent Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.accent_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.accent_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Hero Section Background</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.hero_bg_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.hero_bg_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Header Background</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.header_bg_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.header_bg_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Footer Background</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.footer_bg_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.footer_bg_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Topbar Background</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.topbar_bg_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.topbar_bg_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Topbar Text Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.topbar_text_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.topbar_text_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Body Background Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.body_bg_color" class="h-9 w-12 cursor-pointer rounded border" />
                            <input type="text" v-model="form.body_bg_color" class="h-9 w-full rounded border px-3 text-xs uppercase" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: TYPOGRAPHY -->
            <div v-if="activeTab === 'typography'" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Typography & Fonts</h3>
                <p class="text-xs text-neutral-500">Choose Google fonts for body copy and headings.</p>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Body Font Family</label>
                        <select v-model="form.font_family" class="h-9 w-full rounded border px-3 text-xs">
                            <option v-for="font in fontOptions" :key="font" :value="font">{{ font }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Heading Font Family</label>
                        <select v-model="form.heading_font" class="h-9 w-full rounded border px-3 text-xs">
                            <option v-for="font in fontOptions" :key="font" :value="font">{{ font }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Base Font Size (px)</label>
                        <input type="number" min="12" max="24" v-model="form.base_font_size" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                </div>

                <div class="rounded-lg border p-4 bg-neutral-50 dark:bg-neutral-800 space-y-2">
                    <p class="text-xs text-neutral-400 font-bold">Font Preview:</p>
                    <h2 class="text-xl font-bold" :style="{ fontFamily: form.heading_font }">
                        The Quick Brown Fox Jumps Over The Lazy Dog
                    </h2>
                    <p class="text-sm" :style="{ fontFamily: form.font_family }">
                        StoreMint premium e-commerce design system guidelines and typography scale.
                    </p>
                </div>
            </div>

            <!-- TAB 3: HEADER & TOPBAR -->
            <div v-if="activeTab === 'header'" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Header & Topbar Settings</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Store Name</label>
                        <input type="text" v-model="form.store_name" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Store Tagline</label>
                        <input type="text" v-model="form.store_tagline" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Logo Image URL (Optional)</label>
                        <input type="text" v-model="form.logo_url" placeholder="https://example.com/logo.png" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Header Layout Style</label>
                        <select v-model="form.header_style" class="h-9 w-full rounded border px-3 text-xs">
                            <option value="classic">Classic (Logo Left, Links Center)</option>
                            <option value="modern">Modern (Clean & Glassmorphism)</option>
                            <option value="minimal">Minimalist</option>
                            <option value="centered">Centered Logo</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Topbar Support Phone</label>
                        <input type="text" v-model="form.topbar_phone" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Topbar Support Email</label>
                        <input type="email" v-model="form.topbar_email" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="checkbox" id="show_topbar" v-model="form.show_topbar" class="h-4 w-4 rounded text-emerald-600" />
                    <label for="show_topbar" class="text-xs font-medium cursor-pointer">Show Persistent Top Announcement / Contact Bar</label>
                </div>
            </div>

            <!-- TAB 4: HERO SECTION -->
            <div v-if="activeTab === 'hero'" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Hero Section Customizer</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Hero Layout Style</label>
                        <select v-model="form.hero_style" class="h-9 w-full rounded border px-3 text-xs">
                            <option value="fullwidth">Full Width Banner</option>
                            <option value="card_split">Split Grid (Text Left, Image Right)</option>
                            <option value="gradient_overlay">Gradient Overlay</option>
                            <option value="minimal">Minimal</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Hero Badge Text</label>
                        <input type="text" v-model="form.hero_badge_text" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Hero Heading</label>
                        <input type="text" v-model="form.hero_heading" class="h-9 w-full rounded border px-3 text-xs font-bold" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Hero Subheading</label>
                        <textarea v-model="form.hero_subheading" rows="3" class="w-full rounded border p-3 text-xs"></textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Primary CTA Button Text</label>
                        <input type="text" v-model="form.hero_cta_text" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Secondary CTA Button Text</label>
                        <input type="text" v-model="form.hero_cta_secondary" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Hero Custom Image URL (Optional)</label>
                        <input type="text" v-model="form.hero_image_url" placeholder="https://images.unsplash.com/photo-..." class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="checkbox" id="show_hero_badge" v-model="form.show_hero_badge" class="h-4 w-4 rounded text-emerald-600" />
                    <label for="show_hero_badge" class="text-xs font-medium cursor-pointer">Show Hero Badge Tag</label>
                </div>
            </div>

            <!-- TAB 5: FEATURED BRANDS -->
            <div v-if="activeTab === 'brands'" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Featured Brands & Partners</h3>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="show_brands" v-model="form.show_brands" class="h-4 w-4 rounded text-emerald-600" />
                    <label for="show_brands" class="text-xs font-medium cursor-pointer">Display Brands Section on Storefront</label>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Brands Section Title</label>
                    <input type="text" v-model="form.brands_title" class="h-9 w-full rounded border px-3 text-xs max-w-md" />
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Brands List</label>

                    <div class="flex items-center gap-2 max-w-md">
                        <input
                            type="text"
                            v-model="newBrandInput"
                            @keyup.enter="addBrand"
                            placeholder="Add brand name..."
                            class="h-9 w-full rounded border px-3 text-xs"
                        />
                        <button
                            type="button"
                            @click="addBrand"
                            class="inline-flex h-9 items-center gap-1 rounded bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-500 shrink-0"
                        >
                            <Plus class="h-4 w-4" /> Add
                        </button>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <span
                            v-for="(brand, idx) in form.brands_list"
                            :key="idx"
                            class="inline-flex items-center gap-1.5 rounded-full bg-neutral-100 dark:bg-neutral-800 border px-3 py-1 text-xs font-medium"
                        >
                            <span>{{ brand }}</span>
                            <button type="button" @click="removeBrand(idx)" class="text-neutral-400 hover:text-red-500">
                                <Trash2 class="h-3 w-3" />
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <!-- TAB 6: FOOTER & SOCIAL LINKS -->
            <div v-if="activeTab === 'footer'" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Footer & Social Links</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Footer Tagline</label>
                        <input type="text" v-model="form.footer_tagline" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Copyright Text</label>
                        <input type="text" v-model="form.footer_copyright" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Facebook URL</label>
                        <input type="text" v-model="form.social_facebook" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Instagram URL</label>
                        <input type="text" v-model="form.social_instagram" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Twitter URL</label>
                        <input type="text" v-model="form.social_twitter" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">YouTube URL</label>
                        <input type="text" v-model="form.social_youtube" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">LinkedIn URL</label>
                        <input type="text" v-model="form.social_linkedin" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">GitHub URL</label>
                        <input type="text" v-model="form.social_github" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.show_footer" class="h-4 w-4 rounded text-emerald-600" />
                        <span class="text-xs font-medium">Display Footer</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.show_newsletter" class="h-4 w-4 rounded text-emerald-600" />
                        <span class="text-xs font-medium">Show Newsletter Subscription Box</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.show_social_links" class="h-4 w-4 rounded text-emerald-600" />
                        <span class="text-xs font-medium">Show Social Media Links</span>
                    </label>
                </div>
            </div>

            <!-- TAB 7: LAYOUT & AESTHETICS -->
            <div v-if="activeTab === 'layout'" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Layout & Border Radius</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Max Container Width (px)</label>
                        <input type="number" min="960" max="1920" v-model="form.layout_width" class="h-9 w-full rounded border px-3 text-xs" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Border Radius Scale</label>
                        <select v-model="form.border_radius" class="h-9 w-full rounded border px-3 text-xs">
                            <option value="none">Square (none)</option>
                            <option value="sm">Small (sm)</option>
                            <option value="md">Medium (md)</option>
                            <option value="lg">Large (lg)</option>
                            <option value="xl">Extra Large (xl)</option>
                            <option value="2xl">2X Large (2xl)</option>
                            <option value="full">Pill / Full Round</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-neutral-700 dark:text-neutral-300">Card Shadow Depth</label>
                        <select v-model="form.card_shadow" class="h-9 w-full rounded border px-3 text-xs">
                            <option value="none">Flat (no shadow)</option>
                            <option value="sm">Subtle (sm)</option>
                            <option value="md">Medium (md)</option>
                            <option value="lg">Elevated (lg)</option>
                            <option value="xl">Deep (xl)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- TAB 8: LIVE PREVIEW -->
            <div v-if="activeTab === 'preview'" class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-900 p-6 text-white">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Live Mockup Preview</span>
                    <span class="text-[11px] text-neutral-400">Real-time simulation of admin settings</span>
                </div>

                <!-- Preview Box -->
                <div class="rounded-xl overflow-hidden border border-neutral-800 bg-neutral-950 text-neutral-200">
                    <!-- Topbar Preview -->
                    <div
                        v-if="form.show_topbar"
                        :style="{ backgroundColor: form.topbar_bg_color, color: form.topbar_text_color }"
                        class="px-4 py-1.5 text-xs flex justify-between items-center font-medium"
                    >
                        <div class="flex gap-4">
                            <span>📞 {{ form.topbar_phone }}</span>
                            <span>✉️ {{ form.topbar_email }}</span>
                        </div>
                        <div>Welcome to {{ form.store_name }}</div>
                    </div>

                    <!-- Header Preview -->
                    <div :style="{ backgroundColor: form.header_bg_color }" class="px-6 py-4 flex items-center justify-between border-b border-neutral-800">
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-lg flex items-center justify-center text-white" :style="{ backgroundColor: form.primary_color }">
                                <Leaf class="h-4 w-4" />
                            </div>
                            <span class="text-base font-bold text-neutral-900 dark:text-white" :style="{ fontFamily: form.heading_font }">
                                {{ form.store_name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                            <span>Home</span>
                            <span>Shop</span>
                            <span>Categories</span>
                            <span>Support</span>
                        </div>
                        <div class="h-8 px-4 rounded flex items-center gap-2 text-xs font-semibold text-white" :style="{ backgroundColor: form.primary_color }">
                            <ShoppingCart class="h-3.5 w-3.5" /> Cart (0)
                        </div>
                    </div>

                    <!-- Hero Preview -->
                    <div :style="{ backgroundColor: form.hero_bg_color }" class="p-8 text-white space-y-4 relative overflow-hidden">
                        <div v-if="form.show_hero_badge" class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold text-emerald-300" :style="{ backgroundColor: form.primary_color + '33' }">
                            {{ form.hero_badge_text }}
                        </div>
                        <h1 class="text-2xl font-extrabold" :style="{ fontFamily: form.heading_font }">
                            {{ form.hero_heading }}
                        </h1>
                        <p class="text-xs text-neutral-300 max-w-md" :style="{ fontFamily: form.font_family }">
                            {{ form.hero_subheading }}
                        </p>
                        <div class="flex gap-3 pt-2">
                            <button type="button" class="px-4 py-2 rounded text-xs font-bold text-neutral-950" :style="{ backgroundColor: form.primary_color }">
                                {{ form.hero_cta_text }}
                            </button>
                            <button type="button" class="px-4 py-2 rounded text-xs font-bold border border-white/20">
                                {{ form.hero_cta_secondary }}
                            </button>
                        </div>
                    </div>

                    <!-- Brands Section Preview -->
                    <div v-if="form.show_brands" class="p-6 bg-white dark:bg-neutral-900 border-t border-neutral-800 text-center space-y-3">
                        <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">{{ form.brands_title }}</h4>
                        <div class="flex flex-wrap justify-center gap-6 text-xs font-bold text-neutral-600 dark:text-neutral-400">
                            <span v-for="brand in form.brands_list" :key="brand">{{ brand }}</span>
                        </div>
                    </div>

                    <!-- Footer Preview -->
                    <div v-if="form.show_footer" :style="{ backgroundColor: form.footer_bg_color }" class="p-6 text-xs text-neutral-400 space-y-3 border-t border-neutral-800">
                        <div class="flex justify-between items-center">
                            <div class="font-bold text-white">{{ form.store_name }}</div>
                            <div class="text-[11px]">{{ form.footer_tagline }}</div>
                        </div>
                        <div class="text-[10px] text-neutral-500 text-center pt-2 border-t border-neutral-800">
                            {{ form.footer_copyright }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:opacity-50"
                >
                    <Check class="h-4 w-4" />
                    <span>{{ form.processing ? 'Saving...' : 'Save Storefront Design' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
