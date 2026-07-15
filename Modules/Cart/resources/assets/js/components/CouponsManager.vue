<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Search, Plus, Edit, Trash2, X } from '@lucide/vue';

interface Coupon {
    id: number;
    code: string;
    description?: string;
    discountType: 'flat' | 'percentage';
    discountValue: number;
    minOrderAmount: number;
    usedCount?: number;
    usageLimit?: number;
    expiresAt: string;
    status: 'active' | 'inactive';
}

const props = defineProps<{
    coupons?: Coupon[];
    currentTeamSlug: string | null;
}>();

const page = usePage();
const toastMessage = ref('');
const triggerToast = (msg: string) => {
    toastMessage.value = msg;
    setTimeout(() => {
        if (toastMessage.value === msg) {
            toastMessage.value = '';
        }
    }, 3000);
};

// Search / Filtering State
const searchQuery = ref('');
const filterStatus = ref('All');

const showCreateCouponModal = ref(false);
const showEditCouponModal = ref(false);
const editingCouponId = ref<number | null>(null);

const newCoupon = ref({
    code: '',
    discountType: 'percentage' as 'flat' | 'percentage',
    discountValue: 10,
    minOrderAmount: 0,
    usageLimit: 100,
    expiresAt: '',
    status: 'active' as 'active' | 'inactive',
});

const editCoupon = ref({
    code: '',
    discountType: 'percentage' as 'flat' | 'percentage',
    discountValue: 10,
    minOrderAmount: 0,
    usageLimit: 100,
    expiresAt: '',
    status: 'active' as 'active' | 'inactive',
});

interface ValidationErrors {
    code?: string;
    discountValue?: string;
    minOrderAmount?: string;
}
const formErrors = ref<ValidationErrors>({});

const filteredCoupons = computed(() => {
    const list = props.coupons || [];
    return list.filter((c) => {
        const matchesStatus =
            filterStatus.value === 'All' ||
            (filterStatus.value === 'Active' && c.status === 'active') ||
            (filterStatus.value === 'Inactive' && c.status === 'inactive');
        const matchesSearch = c.code
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());
        return matchesStatus && matchesSearch;
    });
});

const handleCreateCoupon = () => {
    formErrors.value = {};
    let hasError = false;

    if (!newCoupon.value.code.trim()) {
        formErrors.value.code = 'Coupon code is required.';
        hasError = true;
    }

    if (newCoupon.value.discountValue <= 0) {
        formErrors.value.discountValue = 'Value must be positive.';
        hasError = true;
    } else if (
        newCoupon.value.discountType === 'percentage' &&
        newCoupon.value.discountValue > 100
    ) {
        formErrors.value.discountValue = 'Percentage cannot exceed 100%.';
        hasError = true;
    }

    if (newCoupon.value.minOrderAmount < 0) {
        formErrors.value.minOrderAmount = 'Minimum order cannot be negative.';
        hasError = true;
    }

    if (hasError) return;

    router.post(
        `/${props.currentTeamSlug}/dashboard/coupons`,
        {
            code: newCoupon.value.code.trim().toUpperCase(),
            discountType: newCoupon.value.discountType,
            discountValue: newCoupon.value.discountValue,
            minOrderAmount: newCoupon.value.minOrderAmount,
            usageLimit: newCoupon.value.usageLimit,
            expiresAt: newCoupon.value.expiresAt || null,
            status: newCoupon.value.status,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCreateCouponModal.value = false;
                newCoupon.value = {
                    code: '',
                    discountType: 'percentage',
                    discountValue: 10,
                    minOrderAmount: 0,
                    usageLimit: 100,
                    expiresAt: '',
                    status: 'active',
                };
                triggerToast('🎉 Coupon created successfully!');
            },
        },
    );
};

const openEditCouponModal = (coupon: any) => {
    editingCouponId.value = coupon.id;
    editCoupon.value = {
        code: coupon.code,
        discountType: coupon.discountType,
        discountValue: coupon.discountValue,
        minOrderAmount: coupon.minOrderAmount,
        usageLimit: coupon.usageLimit,
        expiresAt: coupon.expiresAt === 'Never' ? '' : coupon.expiresAt,
        status: coupon.status,
    };
    formErrors.value = {};
    showEditCouponModal.value = true;
};

const handleUpdateCoupon = () => {
    formErrors.value = {};
    let hasError = false;

    if (!editCoupon.value.code.trim()) {
        formErrors.value.code = 'Coupon code is required.';
        hasError = true;
    }

    if (editCoupon.value.discountValue <= 0) {
        formErrors.value.discountValue = 'Value must be positive.';
        hasError = true;
    } else if (
        editCoupon.value.discountType === 'percentage' &&
        editCoupon.value.discountValue > 100
    ) {
        formErrors.value.discountValue = 'Percentage cannot exceed 100%.';
        hasError = true;
    }

    if (editCoupon.value.minOrderAmount < 0) {
        formErrors.value.minOrderAmount = 'Minimum order cannot be negative.';
        hasError = true;
    }

    if (hasError) return;

    router.put(
        `/${props.currentTeamSlug}/dashboard/coupons/${editingCouponId.value}`,
        {
            code: editCoupon.value.code.trim().toUpperCase(),
            discountType: editCoupon.value.discountType,
            discountValue: editCoupon.value.discountValue,
            minOrderAmount: editCoupon.value.minOrderAmount,
            usageLimit: editCoupon.value.usageLimit,
            expiresAt: editCoupon.value.expiresAt || null,
            status: editCoupon.value.status,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showEditCouponModal.value = false;
                editingCouponId.value = null;
                triggerToast('🎉 Coupon updated successfully!');
            },
        },
    );
};

const toggleCouponStatus = (couponId: number) => {
    router.post(
        `/${props.currentTeamSlug}/dashboard/coupons/${couponId}/toggle`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => triggerToast('🏷️ Coupon status updated!'),
        },
    );
};

const deleteCoupon = (couponId: number) => {
    router.delete(`/${props.currentTeamSlug}/dashboard/coupons/${couponId}`, {
        preserveScroll: true,
        onSuccess: () => triggerToast('🗑️ Coupon removed.'),
    });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Toast feedback inside component -->
        <div
            v-if="toastMessage"
            class="fixed top-4 right-4 z-50 rounded-lg bg-neutral-900 px-4 py-2.5 text-xs font-semibold text-white shadow-lg dark:bg-white dark:text-neutral-900"
        >
            {{ toastMessage }}
        </div>

        <div
            class="flex flex-col justify-between gap-4 border-b border-neutral-100 pb-4 sm:flex-row sm:items-center dark:border-neutral-800"
        >
            <div class="flex items-center gap-2 flex-1 max-w-md">
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-neutral-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search coupons..."
                        class="h-10 w-full rounded-lg border border-neutral-200 bg-white pr-4 pl-10 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                    />
                </div>
                <select
                    v-model="filterStatus"
                    class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <option value="All">All Status</option>
                    <option value="Active">Active Only</option>
                    <option value="Inactive">Inactive Only</option>
                </select>
            </div>

            <button
                @click="showCreateCouponModal = true"
                class="flex h-10 items-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white transition hover:bg-emerald-700"
            >
                <Plus class="h-4 w-4" /> Create Coupon
            </button>
        </div>

        <!-- Coupons Table -->
        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-neutral-800 dark:bg-neutral-800/40"
                        >
                            <th class="p-4 font-semibold">Code</th>
                            <th class="w-32 p-4 text-center font-semibold">Type</th>
                            <th class="w-24 p-4 text-center font-semibold">Discount Value</th>
                            <th class="w-28 p-4 text-center font-semibold">Min Order</th>
                            <th class="w-32 p-4 text-center font-semibold">Usages (Limit)</th>
                            <th class="w-28 p-4 font-semibold">Expires At</th>
                            <th class="w-24 p-4 font-semibold">Status</th>
                            <th class="w-24 p-4 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/50">
                        <tr
                            v-for="coupon in filteredCoupons"
                            :key="coupon.id"
                            class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20"
                        >
                            <td class="p-4">
                                <span
                                    class="rounded bg-neutral-100 px-2 py-1 font-mono font-bold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300"
                                >
                                    {{ coupon.code }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-semibold text-neutral-500 uppercase">
                                {{ coupon.discountType }}
                            </td>
                            <td class="p-4 text-center font-mono font-bold">
                                {{
                                    coupon.discountType === 'percentage'
                                        ? `${coupon.discountValue}%`
                                        : `${$page.props.currency_symbol ?? '$'}${coupon.discountValue.toFixed(2)}`
                                }}
                            </td>
                            <td class="p-4 text-center font-mono">
                                {{ $page.props.currency_symbol ?? '$' }}{{ coupon.minOrderAmount.toFixed(2) }}
                            </td>
                            <td class="p-4 text-center font-mono">
                                {{ coupon.usedCount ?? 0 }} /
                                <span class="text-neutral-400">{{ coupon.usageLimit }}</span>
                            </td>
                            <td class="p-4 font-mono text-neutral-500">
                                {{ coupon.expiresAt }}
                            </td>
                            <td class="p-4">
                                <button
                                    @click="toggleCouponStatus(coupon.id)"
                                    :class="
                                        coupon.status === 'active'
                                            ? 'bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-400'
                                            : 'bg-neutral-100 text-neutral-400 dark:bg-neutral-800 dark:text-neutral-400'
                                    "
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold transition hover:opacity-85"
                                >
                                    {{ coupon.status === 'active' ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button
                                        @click="openEditCouponModal(coupon)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-neutral-100 text-neutral-400 transition hover:bg-emerald-50 hover:text-emerald-600 dark:bg-neutral-800 dark:hover:bg-emerald-950"
                                        title="Edit Coupon"
                                    >
                                        <Edit class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="deleteCoupon(coupon.id)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-neutral-100 text-neutral-400 transition hover:bg-red-50 hover:text-red-500 dark:bg-neutral-800 dark:hover:bg-red-950"
                                        title="Delete Coupon"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCoupons.length === 0">
                            <td colspan="8" class="p-8 text-center text-neutral-400">
                                No coupons found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Coupon Dialog -->
        <div v-if="showCreateCouponModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden">
            <div @click="showCreateCouponModal = false" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
            <div
                class="relative w-full max-w-md space-y-4 rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <h3 class="text-base font-bold tracking-tight">Create Coupon Code</h3>
                    <button @click="showCreateCouponModal = false" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Coupon Code</label>
                        <input
                            v-model="newCoupon.code"
                            type="text"
                            placeholder="e.g. MINT75"
                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                        />
                        <p v-if="formErrors.code" class="text-[10px] font-semibold text-red-500">{{ formErrors.code }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Discount Type</label>
                            <select
                                v-model="newCoupon.discountType"
                                class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            >
                                <option value="percentage">Percentage (%)</option>
                                <option value="flat">Flat Amount ({{ $page.props.currency_symbol ?? '$' }})</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Discount Value</label>
                            <input
                                v-model="newCoupon.discountValue"
                                type="number"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                            <p v-if="formErrors.discountValue" class="text-[10px] font-semibold text-red-500">
                                {{ formErrors.discountValue }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                Min Order ({{ $page.props.currency_symbol ?? '$' }})
                            </label>
                            <input
                                v-model="newCoupon.minOrderAmount"
                                type="number"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                            <p v-if="formErrors.minOrderAmount" class="text-[10px] font-semibold text-red-500">
                                {{ formErrors.minOrderAmount }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Usage Limit</label>
                            <input
                                v-model="newCoupon.usageLimit"
                                type="number"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Expiry Date</label>
                            <input
                                v-model="newCoupon.expiresAt"
                                type="date"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Active Status</label>
                            <select
                                v-model="newCoupon.status"
                                class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                    <button
                        @click="showCreateCouponModal = false"
                        class="h-9 rounded-lg border px-4 text-xs font-semibold hover:bg-neutral-50 dark:border-neutral-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleCreateCoupon"
                        class="h-9 rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Create Coupon
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Coupon Dialog -->
        <div v-if="showEditCouponModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden">
            <div @click="showEditCouponModal = false" class="absolute inset-0 bg-neutral-950/40 backdrop-blur-xs"></div>
            <div
                class="relative w-full max-w-md space-y-4 rounded-xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <h3 class="text-base font-bold tracking-tight">Edit Coupon Code</h3>
                    <button @click="showEditCouponModal = false" class="rounded p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Coupon Code</label>
                        <input
                            v-model="editCoupon.code"
                            type="text"
                            placeholder="e.g. MINT75"
                            class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                        />
                        <p v-if="formErrors.code" class="text-[10px] font-semibold text-red-500">{{ formErrors.code }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Discount Type</label>
                            <select
                                v-model="editCoupon.discountType"
                                class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            >
                                <option value="percentage">Percentage (%)</option>
                                <option value="flat">Flat Amount ({{ $page.props.currency_symbol ?? '$' }})</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Discount Value</label>
                            <input
                                v-model="editCoupon.discountValue"
                                type="number"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                            <p v-if="formErrors.discountValue" class="text-[10px] font-semibold text-red-500">
                                {{ formErrors.discountValue }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                Min Order ({{ $page.props.currency_symbol ?? '$' }})
                            </label>
                            <input
                                v-model="editCoupon.minOrderAmount"
                                type="number"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                            <p v-if="formErrors.minOrderAmount" class="text-[10px] font-semibold text-red-500">
                                {{ formErrors.minOrderAmount }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Usage Limit</label>
                            <input
                                v-model="editCoupon.usageLimit"
                                type="number"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Expiry Date</label>
                            <input
                                v-model="editCoupon.expiresAt"
                                type="date"
                                class="h-10 rounded-lg border border-neutral-200 px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Active Status</label>
                            <select
                                v-model="editCoupon.status"
                                class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-xs outline-none focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-800"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-neutral-100 pt-3 dark:border-neutral-800">
                    <button
                        @click="showEditCouponModal = false"
                        class="h-9 rounded-lg border px-4 text-xs font-semibold hover:bg-neutral-50 dark:border-neutral-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleUpdateCoupon"
                        class="h-9 rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
