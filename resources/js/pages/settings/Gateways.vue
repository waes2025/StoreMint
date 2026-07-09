<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    gateways: {
        stripe: {
            enabled: boolean;
            publishable_key: string;
            secret_key: string;
        };
        sslcommerz: {
            enabled: boolean;
            store_id: string;
            store_password: string;
            merchant_id?: string;
            mode?: 'live' | 'sandbox';
        };
        cod: {
            enabled: boolean;
        };
    };
    storage_format?: 'json' | 'serialize';
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Gateway settings',
                href: route('gateways.edit').url,
            },
        ],
    },
});

const form = useForm({
    storage_format: props.storage_format ?? 'json',
    stripe: {
        enabled: props.gateways.stripe?.enabled ?? false,
        publishable_key: props.gateways.stripe?.publishable_key ?? '',
        secret_key: props.gateways.stripe?.secret_key ?? '',
    },
    sslcommerz: {
        enabled: props.gateways.sslcommerz?.enabled ?? false,
        store_id: props.gateways.sslcommerz?.store_id ?? '',
        store_password: props.gateways.sslcommerz?.store_password ?? '',
        merchant_id: props.gateways.sslcommerz?.merchant_id ?? '',
        mode: props.gateways.sslcommerz?.mode ?? 'live',
    },
    cod: {
        enabled: props.gateways.cod?.enabled ?? true,
    },
});

const submit = () => {
    form.patch(route('gateways.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Payment Gateways" />

    <h1 class="sr-only">Payment Gateways</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Payment Gateways"
            description="Configure your active checkout payment methods."
        />

        <form @submit.prevent="submit" class="space-y-8 pb-12">
            <!-- 1. Stripe -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-neutral-850 dark:text-white">Stripe Integration</h3>
                        <p class="text-xs text-neutral-500">Accept credit and debit card payments worldwide.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            v-model="form.stripe.enabled" 
                            class="sr-only peer"
                        />
                        <div class="w-9 h-5 bg-neutral-200 dark:bg-neutral-800 rounded-full peer peer-focus:ring-2 peer-focus:ring-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-neutral-700 peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <div v-if="form.stripe.enabled" class="grid gap-4 sm:grid-cols-2 pt-2">
                    <div class="flex flex-col gap-2">
                        <Label for="stripe-pub">Publishable Key</Label>
                        <Input
                            id="stripe-pub"
                            v-model="form.stripe.publishable_key"
                            placeholder="pk_test_..."
                            class="w-full"
                        />
                        <p v-if="form.errors['stripe.publishable_key']" class="text-xs text-red-500 font-semibold">{{ form.errors['stripe.publishable_key'] }}</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Label for="stripe-sec">Secret Key</Label>
                        <Input
                            id="stripe-sec"
                            type="password"
                            v-model="form.stripe.secret_key"
                            placeholder="sk_test_..."
                            class="w-full"
                        />
                        <p v-if="form.errors['stripe.secret_key']" class="text-xs text-red-500 font-semibold">{{ form.errors['stripe.secret_key'] }}</p>
                    </div>
                </div>
            </div>

            <!-- 2. SSLCommerz -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-neutral-850 dark:text-white">SSLCommerz</h3>
                        <p class="text-xs text-neutral-500">Enable local payment gateway for Bangladesh.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            v-model="form.sslcommerz.enabled" 
                            class="sr-only peer"
                        />
                        <div class="w-9 h-5 bg-neutral-200 dark:bg-neutral-800 rounded-full peer peer-focus:ring-2 peer-focus:ring-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-neutral-700 peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <div v-if="form.sslcommerz.enabled" class="grid gap-4 sm:grid-cols-2 pt-2">
                    <div class="flex flex-col gap-2">
                        <Label for="ssl-store">Store ID</Label>
                        <Input
                            id="ssl-store"
                            v-model="form.sslcommerz.store_id"
                            placeholder="Store ID"
                            class="w-full"
                        />
                        <p v-if="form.errors['sslcommerz.store_id']" class="text-xs text-red-500 font-semibold">{{ form.errors['sslcommerz.store_id'] }}</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Label for="ssl-pass">Store Password</Label>
                        <Input
                            id="ssl-pass"
                            type="password"
                            v-model="form.sslcommerz.store_password"
                            placeholder="Store Password"
                            class="w-full"
                        />
                        <p v-if="form.errors['sslcommerz.store_password']" class="text-xs text-red-500 font-semibold">{{ form.errors['sslcommerz.store_password'] }}</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Label for="ssl-merchant">Merchant ID (optional)</Label>
                        <Input
                            id="ssl-merchant"
                            v-model="form.sslcommerz.merchant_id"
                            placeholder="Merchant ID"
                            class="w-full"
                        />
                        <p v-if="form.errors['sslcommerz.merchant_id']" class="text-xs text-red-500 font-semibold">{{ form.errors['sslcommerz.merchant_id'] }}</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Label for="ssl-mode">Mode</Label>
                        <select
                            id="ssl-mode"
                            v-model="form.sslcommerz.mode"
                            class="flex h-10 w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-neutral-800 dark:bg-neutral-950 dark:ring-offset-neutral-950 dark:placeholder:text-neutral-400 dark:focus-visible:ring-emerald-800"
                        >
                            <option value="live">Live</option>
                            <option value="sandbox">Sandbox</option>
                        </select>
                        <p v-if="form.errors['sslcommerz.mode']" class="text-xs text-red-500 font-semibold">{{ form.errors['sslcommerz.mode'] }}</p>
                    </div>
                </div>

                <p v-if="form.sslcommerz.enabled" class="text-xs text-neutral-500 dark:text-neutral-400 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    Checkout currently signs with the server-environment SSLCommerz credentials; values saved here are stored encrypted and take over when config-driven routing ships. IPN endpoint: <code class="bg-neutral-100 dark:bg-neutral-800 px-1 py-0.5 rounded text-[10px]">/payments/sslcommerz/ipn</code>
                </p>
            </div>

            <!-- 3. Cash on Delivery -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-neutral-850 dark:text-white">Cash on Delivery (COD)</h3>
                        <p class="text-xs text-neutral-500">Allow customers to pay in cash upon delivery.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            v-model="form.cod.enabled" 
                            class="sr-only peer"
                        />
                        <div class="w-9 h-5 bg-neutral-200 dark:bg-neutral-800 rounded-full peer peer-focus:ring-2 peer-focus:ring-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-neutral-700 peer-checked:bg-emerald-500"></div>
                    </label>
                </div>
            </div>

            <!-- 4. Database Storage Format -->
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 space-y-4">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-neutral-850 dark:text-white">Database Storage Format</h3>
                    <p class="text-xs text-neutral-500">Choose the format in which payment gateways settings are saved in the system table's value column.</p>
                </div>
                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" value="json" v-model="form.storage_format" class="text-emerald-500 focus:ring-emerald-500" />
                        <span class="text-sm font-medium">JSON Format</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" value="serialize" v-model="form.storage_format" class="text-emerald-500 focus:ring-emerald-500" />
                        <span class="text-sm font-medium">PHP Serialized (Array) Format</span>
                    </label>
                </div>
                <p v-if="form.errors.storage_format" class="text-xs text-red-500 font-semibold">{{ form.errors.storage_format }}</p>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="form.processing" type="submit">
                    {{ form.processing ? 'Saving...' : 'Save Settings' }}
                </Button>
            </div>
        </form>
    </div>
</template>
