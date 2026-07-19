<script setup lang="ts">
import { Bell, CheckCircle2, PackageCheck, ShieldCheck } from '@lucide/vue';
import { onClickOutside } from '@vueuse/core';
import { ref, useTemplateRef } from 'vue';

const open = ref(false);
const notificationDropdownRef = useTemplateRef<HTMLElement>(
    'notificationDropdownRef',
);

const notifications = [
    {
        title: 'New order received',
        description: 'Order #SM-2048 is waiting for review.',
        time: '2m ago',
        icon: PackageCheck,
        read: false,
    },
    {
        title: 'Inventory synced',
        description: 'Product stock levels are up to date.',
        time: '18m ago',
        icon: CheckCircle2,
        read: false,
    },
    {
        title: 'Security check complete',
        description: 'No unusual account activity found.',
        time: '1h ago',
        icon: ShieldCheck,
        read: true,
    },
];

const unreadCount = notifications.filter(
    (notification) => !notification.read,
).length;

onClickOutside(notificationDropdownRef, () => {
    open.value = false;
});
</script>

<template>
    <div class="relative" ref="notificationDropdownRef">
        <button
            @click="open = !open"
            class="relative flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-emerald-800 text-emerald-100 transition hover:bg-emerald-700 hover:text-white dark:bg-emerald-900 dark:hover:bg-emerald-800"
            title="Notifications"
            aria-label="Notifications"
        >
            <Bell class="h-3.5 w-3.5" />
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full border border-emerald-900 bg-amber-400 px-1 text-[10px] leading-none font-bold text-emerald-950 dark:border-emerald-950"
            >
                {{ unreadCount }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-30 mt-1.5 w-72 overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-lg dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-100 px-3 py-2 dark:border-neutral-800"
            >
                <div>
                    <h3
                        class="text-xs font-semibold text-neutral-900 dark:text-white"
                    >
                        Notifications
                    </h3>
                    <p
                        class="text-[11px] text-neutral-500 dark:text-neutral-400"
                    >
                        {{ unreadCount }} new updates
                    </p>
                </div>
            </div>

            <div class="max-h-72 overflow-y-auto p-1">
                <button
                    v-for="notification in notifications"
                    :key="notification.title"
                    class="flex w-full cursor-pointer gap-2 rounded-md px-2 py-2 text-left transition hover:bg-neutral-100 dark:hover:bg-neutral-800"
                >
                    <span
                        class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <component
                            :is="notification.icon"
                            class="h-3.5 w-3.5"
                        />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span
                            class="flex items-center justify-between gap-2 text-xs font-medium text-neutral-900 dark:text-white"
                        >
                            <span class="truncate">{{
                                notification.title
                            }}</span>
                            <span
                                v-if="!notification.read"
                                class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"
                            ></span>
                        </span>
                        <span
                            class="mt-0.5 block text-[11px] leading-snug text-neutral-500 dark:text-neutral-400"
                        >
                            {{ notification.description }}
                        </span>
                        <span
                            class="mt-1 block text-[10px] font-medium text-emerald-600 dark:text-emerald-400"
                        >
                            {{ notification.time }}
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
