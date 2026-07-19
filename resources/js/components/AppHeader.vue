<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    Menu,
    Search,
    Globe,
    ChevronDown,
    Check,
    Sun,
    Monitor,
    Moon,
} from '@lucide/vue';
import { onClickOutside } from '@vueuse/core';
import { computed, ref, useTemplateRef } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import NotificationDropdown from '@/components/NotificationDropdown.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { toUrl } from '@/lib/utils';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { isCurrentUrl, whenCurrentUrl } = useCurrentUrl();

const dashboardUrl = computed(() =>
    page.props.currentTeam
        ? route('dashboard', page.props.currentTeam.slug).url
        : '/',
);

const activeItemStyles = 'text-white bg-emerald-800/80 dark:bg-emerald-900/80';

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboardUrl.value,
        icon: LayoutGrid,
    },
]);

const rightNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

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
    <div>
        <div
            class="border-b border-emerald-500/20 bg-emerald-900 text-white shadow-sm dark:bg-emerald-950"
            style="
                --foreground: 0 0% 100%;
                --muted-foreground: 142.1 70.6% 85%;
                --border: 142.1 70.6% 30%;
                --sidebar-border: 142.1 70.6% 30%;
                --accent: 142.1 70.6% 25%;
                --accent-foreground: 0 0% 100%;
            "
        >
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only"
                                >Navigation menu</SheetTitle
                            >
                            <SheetHeader class="flex justify-start text-left">
                                <div class="flex items-center gap-x-2">
                                    <AppLogo />
                                </div>
                            </SheetHeader>
                            <div
                                class="flex h-full flex-1 flex-col justify-between space-y-4 py-6"
                            >
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="
                                            whenCurrentUrl(
                                                item.href,
                                                activeItemStyles,
                                            )
                                        "
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="toUrl(item.href)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="dashboardUrl" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList
                            class="flex h-full items-stretch space-x-2"
                        >
                            <NavigationMenuItem
                                v-for="(item, index) in mainNavItems"
                                :key="index"
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        whenCurrentUrl(
                                            item.href,
                                            activeItemStyles,
                                        ),
                                        'h-9 cursor-pointer px-3',
                                    ]"
                                    :href="item.href"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="mr-2 h-4 w-4"
                                    />
                                    {{ item.title }}
                                </Link>
                                <div
                                    v-if="isCurrentUrl(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-white dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-3">
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

                    <div class="relative flex items-center space-x-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="group h-9 w-9 cursor-pointer"
                        >
                            <Search
                                class="size-5 opacity-80 group-hover:opacity-100"
                            />
                        </Button>

                        <div class="hidden space-x-1 lg:flex">
                            <template
                                v-for="item in rightNavItems"
                                :key="item.title"
                            >
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                as-child
                                                class="group h-9 w-9 cursor-pointer"
                                            >
                                                <a
                                                    :href="toUrl(item.href)"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <span class="sr-only">{{
                                                        item.title
                                                    }}</span>
                                                    <component
                                                        :is="item.icon"
                                                        class="size-5 opacity-80 group-hover:opacity-100"
                                                    />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <TeamSwitcher :in-header="true" />
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
