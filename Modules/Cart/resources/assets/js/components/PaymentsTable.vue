<script setup lang="ts">
import { ref, computed } from 'vue';
import { Search } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';

interface Payment {
    id: number;
    transaction_id: number;
    order_ref: string;
    customer: string;
    amount: number;
    method: string;
    gateway: string;
    paid_on: string;
    status: string;
}

const props = defineProps<{
    payments?: Payment[];
}>();

const searchQuery = ref('');
const page = usePage();

const filteredPayments = computed(() => {
    const list = props.payments || [];
    const query = searchQuery.value.trim().toLowerCase();
    if (!query) return list;

    return list.filter((p) => {
        return (
            (p.customer || '').toLowerCase().includes(query) ||
            (p.order_ref || '').toLowerCase().includes(query) ||
            (p.gateway || '').toLowerCase().includes(query) ||
            (p.method || '').toLowerCase().includes(query)
        );
    });
});
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col justify-between gap-4 border-b border-neutral-100 pb-4 sm:flex-row sm:items-center dark:border-neutral-800">
            <div class="relative max-w-sm flex-1">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search payments..."
                    class="h-10 w-full rounded-lg border border-neutral-200 bg-white pr-4 pl-10 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                />
            </div>
        </div>

        <!-- Payments Table -->
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/40">
                            <th class="w-16 p-4 font-semibold">ID</th>
                            <th class="w-28 p-4 font-semibold">Order Ref</th>
                            <th class="p-4 font-semibold">Customer</th>
                            <th class="w-32 p-4 font-semibold">Payment Method</th>
                            <th class="w-28 p-4 text-center font-semibold">Gateway</th>
                            <th class="w-28 p-4 text-center font-semibold">Amount</th>
                            <th class="w-32 p-4 font-semibold">Paid On</th>
                            <th class="w-24 p-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                        <tr
                            v-for="payment in filteredPayments"
                            :key="payment.id"
                            class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20"
                        >
                            <td class="p-4 font-mono text-neutral-400">
                                #{{ payment.id }}
                            </td>
                            <td class="p-4 font-mono font-semibold">
                                {{ payment.order_ref }}
                            </td>
                            <td class="p-4 font-semibold">
                                {{ payment.customer }}
                            </td>
                            <td class="p-4 text-neutral-500 capitalize">
                                {{ payment.method }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="rounded bg-neutral-100 px-2 py-0.5 text-[9px] font-bold text-neutral-600 uppercase dark:bg-neutral-800 dark:text-neutral-400">
                                    {{ payment.gateway || 'N/A' }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-mono font-bold">
                                {{ page.props.currency_symbol ?? '$' }}{{ payment.amount.toFixed(2) }}
                            </td>
                            <td class="p-4 text-neutral-500">
                                {{ payment.paid_on }}
                            </td>
                            <td class="p-4">
                                <span
                                    v-if="payment.status === 'Success'"
                                    class="inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-[10px] font-bold text-green-600 dark:bg-green-950 dark:text-green-400"
                                >
                                    Success
                                </span>
                                <span
                                    v-else-if="payment.status === 'Failed'"
                                    class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-[10px] font-bold text-red-600 dark:bg-red-950 dark:text-red-400"
                                >
                                    Failed
                                </span>
                                <span
                                    v-else-if="payment.status === 'Cancelled'"
                                    class="inline-flex rounded-full bg-neutral-100 px-2.5 py-0.5 text-[10px] font-bold text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400"
                                >
                                    Cancelled
                                </span>
                                <span
                                    v-else
                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                                >
                                    {{ payment.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="filteredPayments.length === 0">
                            <td colspan="8" class="p-8 text-center text-neutral-400">
                                No payments found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
