<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { 
    Search, 
    MessageSquare, 
    LifeBuoy, 
    CheckCircle2, 
    Clock, 
    AlertCircle, 
    Send, 
    User, 
    Mail, 
    FolderOpen,
    ArrowLeft
} from '@lucide/vue';
import Heading from '@/components/Heading.vue';

interface Message {
    id: number;
    message: string;
    sender: string;
    is_admin: boolean;
    date: string;
}

interface Ticket {
    id: string;
    db_id: number;
    category: string;
    orderId: string;
    status: 'Open' | 'In Progress' | 'Resolved' | 'Closed';
    date: string;
    customer: {
        name: string;
        email: string;
        username: string;
    };
    messages: Message[];
}

const props = defineProps<{
    tickets: Ticket[];
}>();

const searchQuery = ref('');
const filterStatus = ref('All');
const selectedTicketId = ref<string | null>(props.tickets[0]?.id || null);
const replyMessage = ref('');
const processingReply = ref(false);

const selectedTicket = computed(() => {
    return props.tickets.find(t => t.id === selectedTicketId.value) || null;
});

const stats = computed(() => {
    const total = props.tickets.length;
    const open = props.tickets.filter(t => t.status === 'Open').length;
    const inProgress = props.tickets.filter(t => t.status === 'In Progress').length;
    const resolved = props.tickets.filter(t => t.status === 'Resolved' || t.status === 'Closed').length;
    return { total, open, inProgress, resolved };
});

const filteredTickets = computed(() => {
    return props.tickets.filter(t => {
        const matchesStatus = filterStatus.value === 'All' || t.status === filterStatus.value;
        const matchesSearch = 
            t.id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            t.customer.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            t.category.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (t.orderId && t.orderId.toLowerCase().includes(searchQuery.value.toLowerCase()));
        return matchesStatus && matchesSearch;
    });
});

const handleSelectTicket = (id: string) => {
    selectedTicketId.value = id;
};

const handlePostReply = () => {
    if (!selectedTicket.value || !replyMessage.value.trim()) return;

    processingReply.value = true;
    router.post(
        route('admin.support.reply', { 
            current_team: usePage().props.currentTeam?.slug, 
            ticket: selectedTicket.value.db_id 
        }), 
        { message: replyMessage.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                replyMessage.value = '';
            },
            onFinish: () => {
                processingReply.value = false;
            }
        }
    );
};

const handleUpdateStatus = (newStatus: string) => {
    if (!selectedTicket.value) return;

    router.post(
        route('admin.support.status', { 
            current_team: usePage().props.currentTeam?.slug, 
            ticket: selectedTicket.value.db_id 
        }), 
        { status: newStatus },
        { preserveScroll: true }
    );
};

// Helper for status badge styling
const getStatusClasses = (status: string) => {
    switch (status) {
        case 'Open':
            return 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/30';
        case 'In Progress':
            return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/30';
        case 'Resolved':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/30';
        case 'Closed':
            return 'bg-neutral-100 text-neutral-600 border-neutral-200 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700/60';
        default:
            return 'bg-neutral-50 text-neutral-600';
    }
};

import { usePage } from '@inertiajs/vue3';
const page = usePage();
const route = window.route;
</script>

<template>
    <Head title="Support Inquiries" />

    <div class="space-y-6 px-4 py-6 pb-12 text-neutral-800 sm:px-6 lg:px-8 dark:text-neutral-200">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight flex items-center gap-2">
                <LifeBuoy class="h-6 w-6 text-emerald-500" />
                Support Inquiries Desk
            </h1>
            <p class="text-xs text-neutral-500">
                Manage customer questions, billing issues, refund requests, and support tickets.
            </p>
        </div>

        <!-- Support Stats Cards -->
        <div class="grid gap-4 grid-cols-2 sm:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Total Tickets</div>
                <div class="mt-1 text-2xl font-bold font-mono">{{ stats.total }}</div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="text-[10px] font-bold text-red-500 uppercase tracking-wider flex items-center gap-1">
                    <AlertCircle class="h-3.5 w-3.5" />
                    Open Tickets
                </div>
                <div class="mt-1 text-2xl font-bold font-mono text-red-600 dark:text-red-400">{{ stats.open }}</div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="text-[10px] font-bold text-amber-500 uppercase tracking-wider flex items-center gap-1">
                    <Clock class="h-3.5 w-3.5" />
                    In Progress
                </div>
                <div class="mt-1 text-2xl font-bold font-mono text-amber-600 dark:text-amber-400">{{ stats.inProgress }}</div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                <div class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider flex items-center gap-1">
                    <CheckCircle2 class="h-3.5 w-3.5" />
                    Resolved
                </div>
                <div class="mt-1 text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">{{ stats.resolved }}</div>
            </div>
        </div>

        <!-- Main Workspace: Split Panel -->
        <div class="grid gap-6 lg:grid-cols-3 bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden min-h-[600px]">
            <!-- Sidebar Panel: Ticket Lists -->
            <div class="lg:col-span-1 border-r border-neutral-200 dark:border-neutral-800 flex flex-col h-[650px] bg-neutral-50/50 dark:bg-neutral-900/50">
                <!-- Search & Filters -->
                <div class="p-4 border-b border-neutral-200 dark:border-neutral-800 space-y-3">
                    <div class="relative">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search tickets, customers..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-neutral-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-950 dark:focus:bg-neutral-900"
                        />
                    </div>
                    <div class="flex gap-1.5 overflow-x-auto pb-1">
                        <button 
                            v-for="status in ['All', 'Open', 'In Progress', 'Resolved', 'Closed']"
                            :key="status"
                            @click="filterStatus = status"
                            :class="[
                                'px-2.5 py-1 text-[10px] font-bold rounded-md transition-all whitespace-nowrap',
                                filterStatus === status
                                    ? 'bg-emerald-600 text-white dark:bg-emerald-500'
                                    : 'bg-neutral-100 hover:bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-750'
                            ]"
                        >
                            {{ status }}
                        </button>
                    </div>
                </div>

                <!-- Tickets Scroll Container -->
                <div class="flex-1 overflow-y-auto divide-y divide-neutral-150 dark:divide-neutral-800/60">
                    <div 
                        v-for="ticket in filteredTickets" 
                        :key="ticket.id"
                        @click="handleSelectTicket(ticket.id)"
                        :class="[
                            'p-4 cursor-pointer transition-all flex flex-col gap-2',
                            selectedTicketId === ticket.id 
                                ? 'bg-white dark:bg-neutral-850 border-l-4 border-emerald-500' 
                                : 'hover:bg-neutral-100/50 dark:hover:bg-neutral-800/40 border-l-4 border-transparent'
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-neutral-700 dark:text-neutral-350">{{ ticket.id }}</span>
                            <span 
                                class="px-2 py-0.5 text-[9px] font-bold rounded-md border"
                                :class="getStatusClasses(ticket.status)"
                            >
                                {{ ticket.status }}
                            </span>
                        </div>
                        <div class="text-xs font-bold truncate text-neutral-900 dark:text-neutral-150">{{ ticket.category }}</div>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 line-clamp-2">
                            {{ ticket.messages[0]?.message || 'No description provided.' }}
                        </p>
                        <div class="flex items-center justify-between text-[10px] text-neutral-450 mt-1">
                            <span class="font-medium truncate max-w-[120px]">{{ ticket.customer.name }}</span>
                            <span class="font-mono">{{ ticket.date }}</span>
                        </div>
                    </div>

                    <div v-if="filteredTickets.length === 0" class="py-12 text-center text-xs text-neutral-400">
                        <FolderOpen class="h-8 w-8 mx-auto mb-2 text-neutral-300 dark:text-neutral-700" />
                        No support tickets found.
                    </div>
                </div>
            </div>

            <!-- Detail Panel: Conversations -->
            <div class="lg:col-span-2 flex flex-col h-[650px]">
                <template v-if="selectedTicket">
                    <!-- Ticket Header -->
                    <div class="p-4 border-b border-neutral-200 dark:border-neutral-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-mono text-sm font-extrabold">{{ selectedTicket.id }}</h3>
                                <span class="bg-neutral-100 text-neutral-600 px-2 py-0.5 text-[10px] font-semibold rounded-md border dark:bg-neutral-850 dark:border-neutral-750 dark:text-neutral-400">
                                    {{ selectedTicket.category }}
                                </span>
                                <span v-if="selectedTicket.orderId !== 'None'" class="bg-blue-50 text-blue-700 px-2 py-0.5 text-[10px] font-semibold rounded-md border border-blue-200 dark:bg-blue-950/40 dark:border-blue-900/30 dark:text-blue-400">
                                    Order: {{ selectedTicket.orderId }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-neutral-500">
                                <span class="flex items-center gap-1"><User class="h-3.5 w-3.5" /> {{ selectedTicket.customer.name }}</span>
                                <span class="flex items-center gap-1"><Mail class="h-3.5 w-3.5" /> {{ selectedTicket.customer.email }}</span>
                            </div>
                        </div>

                        <!-- Status Controls -->
                        <div class="flex items-center gap-2 self-start sm:self-center">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Status:</span>
                            <select 
                                :value="selectedTicket.status"
                                @change="handleUpdateStatus(($event.target as HTMLSelectElement).value)"
                                class="rounded-lg border border-neutral-200 p-1.5 text-xs bg-white dark:bg-neutral-950 dark:border-neutral-800 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="Open">🔴 Open</option>
                                <option value="In Progress">🟡 In Progress</option>
                                <option value="Resolved">🟢 Resolved</option>
                                <option value="Closed">⚫ Closed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Discussion History -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-neutral-50/20 dark:bg-neutral-950/5">
                        <div 
                            v-for="msg in selectedTicket.messages" 
                            :key="msg.id"
                            :class="[
                                'flex flex-col max-w-[85%] rounded-2xl p-4 shadow-2xs border',
                                msg.is_admin 
                                    ? 'ml-auto bg-emerald-600 text-white border-emerald-700 dark:bg-emerald-650' 
                                    : 'mr-auto bg-white text-neutral-800 border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800 dark:text-neutral-100'
                            ]"
                        >
                            <div class="flex items-center gap-2 justify-between border-b pb-1.5 mb-2 text-[10px]" :class="msg.is_admin ? 'border-emerald-500/30 text-emerald-100' : 'border-neutral-100 dark:border-neutral-800 text-neutral-400'">
                                <span class="font-bold">{{ msg.sender }} {{ msg.is_admin ? '(Staff)' : '' }}</span>
                                <span class="font-mono">{{ msg.date }}</span>
                            </div>
                            <p class="text-xs whitespace-pre-wrap leading-relaxed">{{ msg.message }}</p>
                        </div>
                    </div>

                    <!-- Composer / Reply Form -->
                    <div class="p-4 border-t border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                        <div class="relative">
                            <textarea
                                v-model="replyMessage"
                                rows="3"
                                placeholder="Type your reply to the customer..."
                                class="w-full rounded-xl border border-neutral-200 p-3 pr-12 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:border-neutral-800 dark:bg-neutral-950"
                                @keydown.ctrl.enter="handlePostReply"
                            ></textarea>
                            <button
                                @click="handlePostReply"
                                :disabled="processingReply || !replyMessage.trim()"
                                class="absolute right-3 bottom-3 flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition disabled:opacity-40 disabled:hover:bg-emerald-600"
                            >
                                <Send class="h-4 w-4" />
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-neutral-400 mt-1">
                            <span>Press Ctrl + Enter to submit reply</span>
                            <span>Replying as Support Agent</span>
                        </div>
                    </div>
                </template>

                <div v-else class="flex-1 flex flex-col items-center justify-center text-center p-6">
                    <MessageSquare class="h-16 w-16 text-neutral-300 dark:text-neutral-700 mb-4 animate-bounce" />
                    <h3 class="text-sm font-bold text-neutral-850 dark:text-neutral-200">No ticket selected</h3>
                    <p class="text-xs text-neutral-400 mt-1 max-w-sm">
                        Select a support ticket from the sidebar list to inspect the ticket thread, change status, and post responses.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
