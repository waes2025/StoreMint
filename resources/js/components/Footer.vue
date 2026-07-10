<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { 
    Leaf, 
    Mail, 
    Phone, 
    MapPin, 
    Send, 
    Heart
} from '@lucide/vue';

type Props = {
    viewMode?: string;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:viewMode', value: 'browse' | 'categories' | 'new-arrivals' | 'support'): void;
}>();

const emailInput = ref('');
const newsletterSubscribed = ref(false);
const newsletterError = ref('');

const handleSubscribe = () => {
    newsletterError.value = '';
    if (!emailInput.value) {
        newsletterError.value = 'Please enter your email.';
        return;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailInput.value)) {
        newsletterError.value = 'Please enter a valid email address.';
        return;
    }
    newsletterSubscribed.value = true;
    emailInput.value = '';
};

const setViewMode = (mode: 'browse' | 'categories' | 'new-arrivals' | 'support') => {
    if (props.viewMode !== undefined) {
        emit('update:viewMode', mode);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        // If not in SPA state context, redirect to home or shop with query params
        if (mode === 'browse') {
            window.location.href = '/';
        } else {
            window.location.href = `/?tab=${mode}`;
        }
    }
};

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="w-full bg-white border-t border-neutral-200/80 dark:bg-neutral-950 dark:border-neutral-800 transition-colors duration-300">
        <!-- Main Footer Section -->
        <div class="mx-auto max-w-[1280px] px-6 py-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10">
            <!-- Brand Section (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                <div class="flex items-center gap-2 cursor-pointer" @click="setViewMode('browse')">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-400 to-emerald-600 text-white shadow-md shadow-emerald-500/20">
                        <Leaf class="h-5 w-5" />
                    </div>
                    <span class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">
                        Store<span class="text-emerald-500">Mint</span>
                    </span>
                </div>
                
                <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed max-w-sm">
                    StoreMint is a state-of-the-art e-commerce platform offering premium hand-crafted products designed to elevate your style and space. Pixel-perfect, fluid, and sustainable.
                </p>

                <!-- Social Links with animations -->
                <div class="flex items-center gap-3">
                    <!-- Facebook Inline SVG -->
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" 
                       class="flex h-9 w-9 items-center justify-center rounded-lg border border-neutral-200 dark:border-neutral-800 hover:border-emerald-500 dark:hover:border-emerald-400 bg-neutral-50/50 dark:bg-neutral-900/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-neutral-500 dark:text-neutral-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all duration-300 hover:scale-110" title="Facebook">
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <!-- Twitter Inline SVG -->
                    <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" 
                       class="flex h-9 w-9 items-center justify-center rounded-lg border border-neutral-200 dark:border-neutral-800 hover:border-emerald-500 dark:hover:border-emerald-400 bg-neutral-50/50 dark:bg-neutral-900/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-neutral-500 dark:text-neutral-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all duration-300 hover:scale-110" title="Twitter">
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                    <!-- Instagram Inline SVG -->
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" 
                       class="flex h-9 w-9 items-center justify-center rounded-lg border border-neutral-200 dark:border-neutral-800 hover:border-emerald-500 dark:hover:border-emerald-400 bg-neutral-50/50 dark:bg-neutral-900/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-neutral-500 dark:text-neutral-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all duration-300 hover:scale-110" title="Instagram">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <!-- LinkedIn Inline SVG -->
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" 
                       class="flex h-9 w-9 items-center justify-center rounded-lg border border-neutral-200 dark:border-neutral-800 hover:border-emerald-500 dark:hover:border-emerald-400 bg-neutral-50/50 dark:bg-neutral-900/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-neutral-500 dark:text-neutral-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all duration-300 hover:scale-110" title="LinkedIn">
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    <!-- GitHub Inline SVG -->
                    <a href="https://github.com" target="_blank" rel="noopener noreferrer" 
                       class="flex h-9 w-9 items-center justify-center rounded-lg border border-neutral-200 dark:border-neutral-800 hover:border-emerald-500 dark:hover:border-emerald-400 bg-neutral-50/50 dark:bg-neutral-900/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-neutral-500 dark:text-neutral-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all duration-300 hover:scale-110" title="GitHub">
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Quick Navigation (2 cols) -->
            <div class="lg:col-span-2 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Navigation</h4>
                <ul class="space-y-2.5 text-xs">
                    <li>
                        <button @click="setViewMode('browse')" class="text-neutral-600 dark:text-neutral-400 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium cursor-pointer">
                            Home
                        </button>
                    </li>
                    <li>
                        <Link href="/shop" class="text-neutral-600 dark:text-neutral-400 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium">
                            Shop All
                        </Link>
                    </li>
                    <li>
                        <button @click="setViewMode('categories')" class="text-neutral-600 dark:text-neutral-400 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium cursor-pointer">
                            Categories
                        </button>
                    </li>
                    <li>
                        <button @click="setViewMode('support')" class="text-neutral-600 dark:text-neutral-400 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium cursor-pointer">
                            Support Help
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Shop Categories / Dynamic collections (3 cols) -->
            <div class="lg:col-span-3 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Featured Collections</h4>
                <ul class="space-y-2.5 text-xs text-neutral-600 dark:text-neutral-400">
                    <li>
                        <Link href="/shop?category=Accessories" class="hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium">Accessories Collection</Link>
                    </li>
                    <li>
                        <Link href="/shop?category=Electronics" class="hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium">Premium Electronics</Link>
                    </li>
                    <li>
                        <Link href="/shop?category=Fashion" class="hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium">Sustainable Fashion</Link>
                    </li>
                    <li>
                        <Link href="/shop?category=Furniture" class="hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium">Modern Workspace</Link>
                    </li>
                    <li>
                        <Link href="/shop?category=Home" class="hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-medium">Home &amp; Smart Living</Link>
                    </li>
                </ul>
            </div>

            <!-- Contact & Newsletter Section (3 cols) -->
            <div class="lg:col-span-3 space-y-6">
                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Subscribe &amp; Save</h4>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                        Sign up for our newsletter to receive exclusive deals, early access to new releases, and an instant coupon at signup!
                    </p>
                    
                    <div class="space-y-2">
                        <div class="relative flex items-center">
                            <input 
                                v-model="emailInput"
                                type="email" 
                                placeholder="Your email address" 
                                @keyup.enter="handleSubscribe"
                                class="h-9 w-full rounded-lg border border-neutral-200 bg-neutral-50/50 pl-3 pr-10 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-white"
                            />
                            <button 
                                @click="handleSubscribe"
                                class="absolute right-1 flex h-7 w-8 items-center justify-center rounded-md bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer"
                                title="Subscribe"
                            >
                                <Send class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <p v-if="newsletterError" class="text-[10px] font-bold text-red-500">{{ newsletterError }}</p>
                        <p v-if="newsletterSubscribed" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">🎉 Subscribed! Check your inbox for the welcome coupon code.</p>
                    </div>
                </div>

                <!-- Small Contact details -->
                <div class="space-y-2.5 text-xs text-neutral-600 dark:text-neutral-400 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <Phone class="h-3.5 w-3.5 text-emerald-500 shrink-0" />
                        <span class="font-medium">+1 (800) 555-0199</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Mail class="h-3.5 w-3.5 text-emerald-500 shrink-0" />
                        <span class="font-medium">support@storemint.com</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <MapPin class="h-3.5 w-3.5 text-emerald-500 shrink-0" />
                        <span class="font-medium">123 StoreMint Lane, Suite 100</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Copyright Section -->
        <div class="border-t border-neutral-200/60 dark:border-neutral-800 py-6 bg-neutral-50/50 dark:bg-neutral-900/10">
            <div class="mx-auto max-w-[1280px] px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-500 dark:text-neutral-400">
                <div class="flex items-center gap-1.5 flex-wrap justify-center">
                    <span>&copy; {{ currentYear }} StoreMint Inc. All rights reserved.</span>
                    <span class="hidden sm:inline text-neutral-300 dark:text-neutral-800">|</span>
                    <span class="flex items-center gap-1">
                        Made with 
                        <Heart class="h-3 w-3 text-red-500 fill-red-500 animate-pulse" /> 
                        for premium shopping.
                    </span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-emerald-500 dark:hover:text-emerald-400 transition">Terms of Service</a>
                    <a href="#" class="hover:text-emerald-500 dark:hover:text-emerald-400 transition">Privacy Policy</a>
                    <a href="#" class="hover:text-emerald-500 dark:hover:text-emerald-400 transition">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>
</template>
