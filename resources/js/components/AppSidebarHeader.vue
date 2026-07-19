<script setup lang="ts">
import { Globe, ChevronDown, Check, Sun, Monitor, Moon } from '@lucide/vue';
import { onClickOutside } from '@vueuse/core';
import { ref, useTemplateRef } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import NotificationDropdown from '@/components/NotificationDropdown.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { appearance, updateAppearance } = useAppearance();

const selectedLang = ref('English');
const langOpen = ref(false);
const langDropdownRef = useTemplateRef<HTMLElement>('langDropdownRef');
const languages = ['English', 'Spanish', 'French', 'German', 'Bengali'];

onClickOutside(langDropdownRef, () => {
    langOpen.value = false;
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-emerald-500/20 bg-emerald-900 px-6 text-white shadow-sm transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4 dark:bg-emerald-950"
        style="
            --foreground: 0 0% 100%;
            --muted-foreground: 142.1 70.6% 85%;
            --border: 142.1 70.6% 30%;
        "
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger
                class="hover:bg-emerald-850/50 -ml-1 text-emerald-100 hover:text-white"
            />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="flex items-center gap-3">
            <!-- Language Selector -->
            <div class="relative" ref="langDropdownRef">
                <button
                    @click="langOpen = !langOpen"
                    class="hover:bg-emerald-750 dark:hover:bg-emerald-850 flex cursor-pointer items-center gap-1.5 rounded-lg bg-emerald-800 px-3 py-1.5 text-xs font-medium text-white transition dark:bg-emerald-900"
                >
                    <Globe class="h-3.5 w-3.5 text-emerald-300" />
                    <span>{{ selectedLang }}</span>
                    <ChevronDown class="h-3 w-3 text-emerald-400" />
                </button>

                <div
                    v-if="langOpen"
                    class="absolute right-0 z-30 mt-1 w-28 rounded-lg border border-neutral-200 bg-white p-1 shadow-md dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <button
                        v-for="lang in languages"
                        :key="lang"
                        @click="
                            selectedLang = lang;
                            langOpen = false;
                        "
                        class="dark:hover:bg-neutral-850 flex w-full cursor-pointer items-center justify-between rounded-md px-2 py-1 text-left text-[11px] text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300"
                    >
                        <span>{{ lang }}</span>
                        <Check
                            v-if="selectedLang === lang"
                            class="h-2.5 w-2.5 text-emerald-500"
                        />
                    </button>
                </div>
            </div>

            <NotificationDropdown />

            <!-- Theme Selector (Segmented Control) -->
            <div
                class="flex items-center gap-0.5 rounded-lg border border-emerald-500/20 bg-emerald-950 p-0.5 shadow-xs dark:bg-neutral-900/60"
            >
                <button
                    @click="updateAppearance('light')"
                    :class="
                        appearance === 'light'
                            ? 'bg-emerald-800 text-amber-300 shadow-xs dark:bg-emerald-900'
                            : 'hover:text-emerald-350 text-emerald-400/80'
                    "
                    class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-md transition duration-200"
                    title="Light Mode"
                >
                    <Sun class="h-3.5 w-3.5" />
                </button>
                <button
                    @click="updateAppearance('system')"
                    :class="
                        appearance === 'system'
                            ? 'bg-emerald-800 text-blue-300 shadow-xs dark:bg-emerald-900'
                            : 'hover:text-emerald-350 text-emerald-400/80'
                    "
                    class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-md transition duration-200"
                    title="System Mode"
                >
                    <Monitor class="h-3.5 w-3.5" />
                </button>
                <button
                    @click="updateAppearance('dark')"
                    :class="
                        appearance === 'dark'
                            ? 'bg-emerald-800 text-emerald-300 shadow-xs dark:bg-emerald-900'
                            : 'hover:text-emerald-350 text-emerald-400/80'
                    "
                    class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-md transition duration-200"
                    title="Dark Mode"
                >
                    <Moon class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
    </header>
</template>
