<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Plus,
    Trash2,
    Truck,
    MapPin,
    Package,
    Clock,
    ExternalLink,
    Zap,
} from '@lucide/vue';

interface ShippingZone {
    name: string;
    rate: number;
    enabled: boolean;
}

interface ShippingSettings {
    free_shipping_enabled: boolean;
    free_shipping_threshold: number;
    flat_rate_enabled: boolean;
    flat_rate_amount: number;
    local_pickup_enabled: boolean;
    local_pickup_label: string;
    default_courier: string;
    estimated_delivery_days: number;
    tracking_base_url: string;
    zones: ShippingZone[];
}

const props = defineProps<{ shippingSettings: ShippingSettings }>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Shipping settings',
                href: '/settings/shipping',
            },
        ],
    },
});

const form = useForm({
    free_shipping_enabled:
        props.shippingSettings.free_shipping_enabled ?? false,
    free_shipping_threshold:
        props.shippingSettings.free_shipping_threshold ?? 0,
    flat_rate_enabled: props.shippingSettings.flat_rate_enabled ?? true,
    flat_rate_amount: props.shippingSettings.flat_rate_amount ?? 5.0,
    local_pickup_enabled: props.shippingSettings.local_pickup_enabled ?? false,
    local_pickup_label:
        props.shippingSettings.local_pickup_label ?? 'Local Pickup',
    default_courier: props.shippingSettings.default_courier ?? '',
    estimated_delivery_days:
        props.shippingSettings.estimated_delivery_days ?? 3,
    tracking_base_url: props.shippingSettings.tracking_base_url ?? '',
    zones: (props.shippingSettings.zones ?? []).map((z) => ({
        ...z,
    })) as ShippingZone[],
});

const addZone = () => {
    form.zones.push({ name: '', rate: 0, enabled: true });
};

const removeZone = (idx: number) => {
    form.zones.splice(idx, 1);
};

const submit = () => {
    form.patch('/settings/shipping', { preserveScroll: true });
};

// Toggle switch component CSS helper
const toggleClass = (on: boolean) =>
    `w-9 h-5 rounded-full peer peer-focus:ring-2 peer-focus:ring-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-neutral-700 ${
        on
            ? 'bg-emerald-500 after:translate-x-full after:border-white'
            : 'bg-neutral-200 dark:bg-neutral-800 after:border-neutral-300'
    }`;
</script>

<template>
    <Head title="Shipping Settings" />

    <h1 class="sr-only">Shipping Settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Shipping Settings"
            description="Configure shipping methods, zones, rates, and delivery preferences for your store."
        />

        <form @submit.prevent="submit" class="space-y-6 pb-12">
            <!-- ── 1. SHIPPING METHODS ──────────────────────────────────────── -->
            <div class="space-y-3">
                <h2
                    class="text-xs font-bold tracking-widest text-neutral-400 uppercase dark:text-neutral-500"
                >
                    Shipping Methods
                </h2>

                <!-- Free Shipping -->
                <div
                    class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/40"
                            >
                                <Zap class="h-4 w-4 text-emerald-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold tracking-tight">
                                    Free Shipping
                                </h3>
                                <p class="text-xs text-neutral-500">
                                    Offer free shipping above a minimum order
                                    amount.
                                </p>
                            </div>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                type="checkbox"
                                v-model="form.free_shipping_enabled"
                                class="peer sr-only"
                            />
                            <div
                                :class="toggleClass(form.free_shipping_enabled)"
                                class="relative"
                            />
                        </label>
                    </div>

                    <div
                        v-if="form.free_shipping_enabled"
                        class="flex flex-col gap-2 border-t border-neutral-100 pt-1 dark:border-neutral-800"
                    >
                        <Label for="free-threshold"
                            >Minimum Order Amount for Free Shipping</Label
                        >
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-neutral-500">{{
                                $page.props.currency_symbol ?? '$'
                            }}</span>
                            <Input
                                id="free-threshold"
                                type="number"
                                step="0.01"
                                min="0"
                                v-model="form.free_shipping_threshold"
                                placeholder="0.00"
                                class="w-40"
                            />
                        </div>
                        <p
                            v-if="form.errors.free_shipping_threshold"
                            class="text-xs font-semibold text-red-500"
                        >
                            {{ form.errors.free_shipping_threshold }}
                        </p>
                    </div>
                </div>

                <!-- Flat Rate -->
                <div
                    class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-950/40"
                            >
                                <Truck class="h-4 w-4 text-blue-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold tracking-tight">
                                    Flat Rate Shipping
                                </h3>
                                <p class="text-xs text-neutral-500">
                                    Charge a fixed fee for all orders.
                                </p>
                            </div>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                type="checkbox"
                                v-model="form.flat_rate_enabled"
                                class="peer sr-only"
                            />
                            <div
                                :class="toggleClass(form.flat_rate_enabled)"
                                class="relative"
                            />
                        </label>
                    </div>

                    <div
                        v-if="form.flat_rate_enabled"
                        class="flex flex-col gap-2 border-t border-neutral-100 pt-1 dark:border-neutral-800"
                    >
                        <Label for="flat-rate">Flat Rate Amount</Label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-neutral-500">{{
                                $page.props.currency_symbol ?? '$'
                            }}</span>
                            <Input
                                id="flat-rate"
                                type="number"
                                step="0.01"
                                min="0"
                                v-model="form.flat_rate_amount"
                                placeholder="5.00"
                                class="w-40"
                            />
                        </div>
                        <p
                            v-if="form.errors.flat_rate_amount"
                            class="text-xs font-semibold text-red-500"
                        >
                            {{ form.errors.flat_rate_amount }}
                        </p>
                    </div>
                </div>

                <!-- Local Pickup -->
                <div
                    class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-950/40"
                            >
                                <MapPin class="h-4 w-4 text-amber-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold tracking-tight">
                                    Local Pickup
                                </h3>
                                <p class="text-xs text-neutral-500">
                                    Let customers pick up their order in person.
                                </p>
                            </div>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                type="checkbox"
                                v-model="form.local_pickup_enabled"
                                class="peer sr-only"
                            />
                            <div
                                :class="toggleClass(form.local_pickup_enabled)"
                                class="relative"
                            />
                        </label>
                    </div>

                    <div
                        v-if="form.local_pickup_enabled"
                        class="flex flex-col gap-2 border-t border-neutral-100 pt-1 dark:border-neutral-800"
                    >
                        <Label for="pickup-label">Pickup Option Label</Label>
                        <Input
                            id="pickup-label"
                            v-model="form.local_pickup_label"
                            placeholder="Local Pickup"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>

            <!-- ── 2. DELIVERY DEFAULTS ──────────────────────────────────────── -->
            <div class="space-y-3">
                <h2
                    class="text-xs font-bold tracking-widest text-neutral-400 uppercase dark:text-neutral-500"
                >
                    Delivery Defaults
                </h2>

                <div
                    class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <Label
                                for="default-courier"
                                class="flex items-center gap-1.5"
                            >
                                <Package class="h-3.5 w-3.5 text-neutral-400" />
                                Default Courier / Carrier
                            </Label>
                            <Input
                                id="default-courier"
                                v-model="form.default_courier"
                                placeholder="e.g. FedEx, DHL, Pathao Courier"
                                class="w-full"
                            />
                            <p class="text-[10px] text-neutral-400">
                                Pre-fills the courier field when updating order
                                shipping status.
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label
                                for="est-days"
                                class="flex items-center gap-1.5"
                            >
                                <Clock class="h-3.5 w-3.5 text-neutral-400" />
                                Estimated Delivery Days
                            </Label>
                            <Input
                                id="est-days"
                                type="number"
                                min="0"
                                max="365"
                                v-model="form.estimated_delivery_days"
                                placeholder="3"
                                class="w-full"
                            />
                            <p class="text-[10px] text-neutral-400">
                                Displayed as estimated delivery window at
                                checkout.
                            </p>
                            <p
                                v-if="form.errors.estimated_delivery_days"
                                class="text-xs font-semibold text-red-500"
                            >
                                {{ form.errors.estimated_delivery_days }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-2 sm:col-span-2">
                            <Label
                                for="tracking-url"
                                class="flex items-center gap-1.5"
                            >
                                <ExternalLink
                                    class="h-3.5 w-3.5 text-neutral-400"
                                />
                                Default Tracking Base URL
                            </Label>
                            <Input
                                id="tracking-url"
                                v-model="form.tracking_base_url"
                                type="url"
                                placeholder="https://track.yourcourier.com/?id="
                                class="w-full"
                            />
                            <p class="text-[10px] text-neutral-400">
                                Tracking number is appended to this URL to
                                auto-generate the tracking link.
                            </p>
                            <p
                                v-if="form.errors.tracking_base_url"
                                class="text-xs font-semibold text-red-500"
                            >
                                {{ form.errors.tracking_base_url }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── 3. SHIPPING ZONES & RATES ──────────────────────────────────── -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2
                        class="text-xs font-bold tracking-widest text-neutral-400 uppercase dark:text-neutral-500"
                    >
                        Shipping Zones & Rates
                    </h2>
                    <button
                        type="button"
                        @click="addZone"
                        class="inline-flex h-7 items-center gap-1 rounded-lg bg-emerald-600 px-3 text-[10px] font-bold text-white transition hover:bg-emerald-700"
                    >
                        <Plus class="h-3 w-3" /> Add Zone
                    </button>
                </div>

                <div
                    v-if="form.zones.length === 0"
                    class="rounded-xl border-2 border-dashed border-neutral-200 p-8 text-center dark:border-neutral-700"
                >
                    <MapPin class="mx-auto mb-2 h-6 w-6 text-neutral-300" />
                    <p class="text-xs text-neutral-400">
                        No shipping zones defined. Click
                        <strong>Add Zone</strong> to create one.
                    </p>
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="(zone, idx) in form.zones"
                        :key="idx"
                        class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Enable toggle -->
                            <label
                                class="relative inline-flex shrink-0 cursor-pointer items-center"
                            >
                                <input
                                    type="checkbox"
                                    v-model="zone.enabled"
                                    class="peer sr-only"
                                />
                                <div
                                    :class="toggleClass(zone.enabled)"
                                    class="relative"
                                />
                            </label>

                            <!-- Zone name -->
                            <Input
                                v-model="zone.name"
                                :placeholder="`Zone ${idx + 1} name (e.g. Domestic)`"
                                class="min-w-0 flex-1"
                            />

                            <!-- Rate -->
                            <div class="flex shrink-0 items-center gap-1">
                                <span class="text-xs text-neutral-400">{{
                                    $page.props.currency_symbol ?? '$'
                                }}</span>
                                <Input
                                    v-model="zone.rate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-24"
                                />
                            </div>

                            <!-- Remove -->
                            <button
                                type="button"
                                @click="removeZone(idx)"
                                class="shrink-0 rounded-lg p-1.5 text-neutral-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/30"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── SAVE ─────────────────────────────────────────────────────── -->
            <div class="flex items-center gap-4 pt-2">
                <Button :disabled="form.processing" type="submit">
                    {{
                        form.processing ? 'Saving...' : 'Save Shipping Settings'
                    }}
                </Button>
                <p
                    v-if="form.recentlySuccessful"
                    class="text-xs font-semibold text-emerald-600"
                >
                    ✅ Saved!
                </p>
            </div>
        </form>
    </div>
</template>
