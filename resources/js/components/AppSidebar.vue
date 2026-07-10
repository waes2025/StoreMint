<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    ShoppingBag, 
    Ticket, 
    CreditCard, 
    Settings, 
    LogOut,
    Package
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

const dashboardUrl = computed(() => {
    if (isAdmin.value && page.props.currentTeam) {
        return route('dashboard', page.props.currentTeam.slug).url;
    }
    return '/dashboard';
});

const isTabActive = (tabName: string, featureName?: string) => {
    try {
        const urlObj = new URL(page.url, 'http://localhost');
        const currentTab = urlObj.searchParams.get('tab') || 'overview';
        if (tabName === 'coming_soon' && featureName) {
            return currentTab === 'coming_soon' && urlObj.searchParams.get('feature') === featureName;
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
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <!-- Branded Green Header containing only Logo -->
        <SidebarHeader class="bg-emerald-900 text-white dark:bg-emerald-950 border-b border-emerald-500/20" style="--sidebar-accent: 142.1 70.6% 25%; --sidebar-accent-foreground: 0 0% 100%; --foreground: 0 0% 100%; --muted-foreground: 142.1 70.6% 85%;">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="hover:bg-emerald-850/50 hover:text-white transition">
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- Sidebar Body (with default sidebar background color) -->
        <SidebarContent class="px-2 py-3 space-y-4">
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
                            <SidebarMenuButton :is-active="isTabActive('overview')" as-child tooltip="Overview">
                                <Link :href="adminRoute('overview')">
                                    <LayoutGrid />
                                    <span>Overview</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton :is-active="isTabActive('products')" as-child tooltip="Products">
                                <Link :href="adminRoute('products')">
                                    <Package />
                                    <span>Products</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton :is-active="isTabActive('orders')" as-child tooltip="Orders">
                                <Link :href="adminRoute('orders')">
                                    <ShoppingBag />
                                    <span>Orders</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton :is-active="isTabActive('coupons')" as-child tooltip="Coupons">
                                <Link :href="adminRoute('coupons')">
                                    <Ticket />
                                    <span>Coupons</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton :is-active="isTabActive('payments')" as-child tooltip="Payments">
                                <Link :href="adminRoute('payments')">
                                    <CreditCard />
                                    <span>Payments</span>
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
                            <SidebarMenuButton :is-active="isCurrentUrl('/dashboard')" as-child tooltip="My Dashboard">
                                <Link href="/dashboard">
                                    <LayoutGrid />
                                    <span>My Dashboard</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton :is-active="isCurrentUrl('/shop')" as-child tooltip="Go to Shop">
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

        <SidebarFooter class="px-2 py-3 border-t border-sidebar-border/50">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton :is-active="isCurrentUrl('/settings/profile')" as-child tooltip="Settings">
                        <Link :href="route('profile.edit')">
                            <Settings />
                            <span>Settings</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child class="text-rose-600 dark:text-rose-455 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30" tooltip="Sign out">
                        <Link :href="route('logout')" method="post" as="button" class="w-full text-left flex items-center gap-2">
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
