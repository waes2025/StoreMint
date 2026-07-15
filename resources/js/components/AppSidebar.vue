<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    ShoppingBag,
    Ticket,
    CreditCard,
    Settings,
    LogOut,
    Package,
    BookOpen,
    Truck,
    Globe,
    Layers,
    ShoppingCart,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();

const isAdmin = computed(() => {
    const user = page.props.auth?.user;
    return user && (user.user_type === 'admin' || user.user_type === 'user');
});

const isCartEnabled = computed(() => {
    const enabledModules = (page.props.enabled_modules as string[]) || [];
    return enabledModules.includes('Cart');
});

const dashboardUrl = computed(() => {
    if (isAdmin.value && page.props.currentTeam) {
        return route('dashboard', page.props.currentTeam.slug).url;
    }
    return '/dashboard';
});

const isTabActive = (tabName: string, featureName?: string) => {
    try {
        const urlObj = new URL(page.url, 'http://localhost');
        if (!urlObj.pathname.endsWith('/dashboard')) {
            return false;
        }
        const currentTab = urlObj.searchParams.get('tab') || 'overview';
        if (tabName === 'coming_soon' && featureName) {
            return (
                currentTab === 'coming_soon' &&
                urlObj.searchParams.get('feature') === featureName
            );
        }
        return currentTab === tabName;
    } catch {
        return false;
    }
};

// Safe helper to build admin URLs without raising NullPointerExceptions on currentTeam
const adminRoute = (tabName: string, featureName?: string) => {
    const team = page.props.currentTeam as any;
    if (team?.slug) {
        const baseUrl = route('dashboard', team.slug).url;
        if (tabName === 'coming_soon' && featureName) {
            return `${baseUrl}?tab=coming_soon&feature=${encodeURIComponent(featureName)}`;
        }
        return `${baseUrl}?tab=${tabName}`;
    }
    // Fallback if team slug is not loaded yet
    if (tabName === 'coming_soon' && featureName) {
        return `/dashboard?tab=coming_soon&feature=${encodeURIComponent(featureName)}`;
    }
    return `/dashboard?tab=${tabName}`;
};

const iconMap: Record<string, any> = {
    BookOpen,
    Truck,
    Globe,
    Layers,
    LayoutGrid,
    ShoppingBag,
    Ticket,
    CreditCard,
    Settings,
    Package
};

const sidebarModulesList = computed(() => {
    const moduleMenus = (page.props.module_menus as any[]) || [];
    return moduleMenus.filter(m => m.type === 'sidebar').map((menu: any) => {
        let icon = Layers;
        if (menu.icon && iconMap[menu.icon]) {
            icon = iconMap[menu.icon];
        }

        let href = menu.href || '#';
        try {
            if (menu.route) {
                href = route(menu.route).url;
            }
        } catch {}

        return {
            name: menu.title,
            title: menu.title,
            href,
            icon,
        };
    });
});
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <!-- Branded Green Header containing only Logo -->
        <SidebarHeader
            class="border-b border-emerald-500/20 bg-emerald-900 text-white dark:bg-emerald-950"
            style="
                --sidebar-accent: 142.1 70.6% 25%;
                --sidebar-accent-foreground: 0 0% 100%;
                --foreground: 0 0% 100%;
                --muted-foreground: 142.1 70.6% 85%;
            "
        >
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="hover:bg-emerald-850/50 transition hover:text-white"
                    >
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- Sidebar Body (with default sidebar background color) -->
        <SidebarContent class="space-y-4 px-2 py-3">
            <!-- Team Switcher at the top of content (matches sidebar background color) -->
            <SidebarMenu v-if="isAdmin">
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>

            <!-- Admin Navigation -->
            <template v-if="isAdmin">
                <SidebarGroup class="p-0">
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                :is-active="isTabActive('overview')"
                                as-child
                                tooltip="Overview"
                            >
                                <Link :href="adminRoute('overview')">
                                    <LayoutGrid />
                                    <span>Overview</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                :is-active="isTabActive('products')"
                                as-child
                                tooltip="Products"
                            >
                                <Link :href="adminRoute('products')">
                                    <Package />
                                    <span>Products</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem v-if="isCartEnabled">
                            <SidebarMenuButton
                                :is-active="isTabActive('orders')"
                                as-child
                                tooltip="Orders"
                            >
                                <Link :href="adminRoute('orders')">
                                    <ShoppingBag />
                                    <span>Orders</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem v-if="isCartEnabled">
                            <SidebarMenuButton
                                :is-active="isTabActive('coupons')"
                                as-child
                                tooltip="Coupons"
                            >
                                <Link :href="adminRoute('coupons')">
                                    <Ticket />
                                    <span>Coupons</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem v-if="isCartEnabled">
                            <SidebarMenuButton
                                :is-active="isTabActive('carts')"
                                as-child
                                tooltip="Carts"
                            >
                                <Link :href="adminRoute('carts')">
                                    <ShoppingCart />
                                    <span>Carts</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                :is-active="isTabActive('payments')"
                                as-child
                                tooltip="Payments"
                            >
                                <Link :href="adminRoute('payments')">
                                    <CreditCard />
                                    <span>Payments</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>

                        <!-- Dynamic Modules Menu Items -->
                        <SidebarMenuItem v-for="mod in sidebarModulesList" :key="mod.name">
                            <SidebarMenuButton
                                :is-active="isCurrentUrl(mod.href)"
                                as-child
                                :tooltip="mod.title"
                            >
                                <Link :href="mod.href">
                                    <component :is="mod.icon" />
                                    <span>{{ mod.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </template>

            <!-- Customer Navigation -->
            <template v-else>
                <SidebarGroup class="p-0">
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                :is-active="isCurrentUrl('/dashboard')"
                                as-child
                                tooltip="My Dashboard"
                            >
                                <Link href="/dashboard">
                                    <LayoutGrid />
                                    <span>My Dashboard</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                :is-active="isCurrentUrl('/shop')"
                                as-child
                                tooltip="Go to Shop"
                            >
                                <Link href="/shop">
                                    <ShoppingBag />
                                    <span>Go to Shop</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </template>
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border/50 px-2 py-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        :is-active="isCurrentUrl('/settings/profile')"
                        as-child
                        tooltip="Settings"
                    >
                        <Link :href="route('profile.edit')">
                            <Settings />
                            <span>Settings</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        as-child
                        class="dark:text-rose-455 text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/30"
                        tooltip="Sign out"
                    >
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex w-full items-center gap-2 text-left"
                        >
                            <LogOut />
                            <span>Sign out</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
