<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Cpu, Download, Trash2, Power, PowerOff, Loader2, Upload, FileArchive, X } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { route } from '@/lib/route';

const props = defineProps<{
    modules: Array<{
        name: string;
        description: string;
    }>;
    enabled_modules: string[];
    installed_modules: string[];
}>();

const installedModules = computed<string[]>(() => {
    if (Array.isArray(props.installed_modules)) {
        return props.installed_modules;
    }
    if (props.installed_modules && typeof props.installed_modules === 'object') {
        return Object.values(props.installed_modules);
    }
    return [];
});

const enabledModules = computed<string[]>(() => {
    if (Array.isArray(props.enabled_modules)) {
        return props.enabled_modules;
    }
    if (props.enabled_modules && typeof props.enabled_modules === 'object') {
        return Object.values(props.enabled_modules);
    }
    return [];
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Modules settings',
                href: route('modules.edit').url,
            },
        ],
    },
});

const processingModule = ref<string | null>(null);
const processingAction = ref<string | null>(null);

const installModule = (name: string) => {
    processingModule.value = name;
    processingAction.value = 'install';
    router.post(route('modules.install'), { module: name }, {
        preserveScroll: true,
        onFinish: () => {
            processingModule.value = null;
            processingAction.value = null;
        }
    });
};

const uninstallModule = (name: string) => {
    processingModule.value = name;
    processingAction.value = 'uninstall';
    router.post(route('modules.uninstall'), { module: name }, {
        preserveScroll: true,
        onFinish: () => {
            processingModule.value = null;
            processingAction.value = null;
        }
    });
};

const enableModule = (name: string) => {
    processingModule.value = name;
    processingAction.value = 'enable';
    router.post(route('modules.enable'), { module: name }, {
        preserveScroll: true,
        onFinish: () => {
            processingModule.value = null;
            processingAction.value = null;
        }
    });
};

const disableModule = (name: string) => {
    processingModule.value = name;
    processingAction.value = 'disable';
    router.post(route('modules.disable'), { module: name }, {
        preserveScroll: true,
        onFinish: () => {
            processingModule.value = null;
            processingAction.value = null;
        }
    });
};

// Upload State & Actions
const uploadForm = useForm({
    module_zip: null as File | null,
});

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const triggerFileSelect = () => {
    fileInput.value?.click();
};

const handleFileSelect = (e: Event) => {
    const files = (e.target as HTMLInputElement).files;
    if (files && files.length > 0) {
        uploadForm.module_zip = files[0];
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
        if (files[0].name.endsWith('.zip')) {
            uploadForm.module_zip = files[0];
        }
    }
};

const removeSelectedFile = () => {
    uploadForm.module_zip = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submitUpload = () => {
    if (!uploadForm.module_zip) return;

    uploadForm.post(route('modules.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Modules settings" />

    <h1 class="sr-only">Modules settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="System Modules"
            description="Install, activate, or completely remove dynamic modular features from your store."
        />

        <!-- Upload Card -->
        <div class="space-y-4 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center gap-2 pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <Upload class="h-4 w-4 text-violet-500" />
                <h4 class="text-neutral-800 text-sm font-bold tracking-tight dark:text-white">
                    Upload & Install Module (.zip)
                </h4>
            </div>

            <div 
                class="flex flex-col items-center justify-center border-2 border-dashed rounded-xl p-8 transition-all cursor-pointer"
                :class="[
                    isDragging 
                        ? 'border-violet-500 bg-violet-50/50 dark:border-violet-400 dark:bg-violet-950/20' 
                        : 'border-neutral-205 hover:border-neutral-300 dark:border-neutral-800 dark:hover:border-neutral-700'
                ]"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                @click="triggerFileSelect"
            >
                <input 
                    ref="fileInput"
                    type="file"
                    accept=".zip"
                    class="hidden"
                    @change="handleFileSelect"
                />

                <div v-if="!uploadForm.module_zip" class="flex flex-col items-center text-center space-y-2">
                    <div class="p-3 bg-violet-50 rounded-full text-violet-500 dark:bg-violet-950/30 dark:text-violet-400">
                        <Upload class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-250">
                            Drag and drop your module zip file here, or <span class="text-violet-500 hover:underline">browse</span>
                        </p>
                        <p class="text-[10px] text-neutral-500 dark:text-neutral-450 mt-1">
                            Supports module .zip packages up to 50MB
                        </p>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center text-center space-y-3 w-full max-w-sm">
                    <div class="p-3 bg-emerald-50 rounded-full text-emerald-500 dark:bg-emerald-950/30 dark:text-emerald-400">
                        <FileArchive class="h-6 w-6" />
                    </div>
                    <div class="w-full">
                        <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200 truncate">
                            {{ uploadForm.module_zip.name }}
                        </p>
                        <p class="text-[10px] text-neutral-500 dark:text-neutral-450">
                            {{ (uploadForm.module_zip.size / (1024 * 1024)).toFixed(2) }} MB
                        </p>
                    </div>

                    <div class="flex items-center gap-2 w-full justify-center" @click.stop>
                        <Button 
                            size="sm" 
                            class="bg-violet-600 hover:bg-violet-700 text-white gap-1.5 h-8 dark:bg-violet-500 dark:hover:bg-violet-600"
                            :disabled="uploadForm.processing"
                            @click="submitUpload"
                        >
                            <Loader2 v-if="uploadForm.processing" class="h-3 w-3 animate-spin" />
                            Install Now
                        </Button>
                        <Button 
                            size="sm" 
                            variant="outline" 
                            class="h-8 p-2"
                            :disabled="uploadForm.processing"
                            @click="removeSelectedFile"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Error message if any -->
            <p v-if="uploadForm.errors.module_zip" class="text-xs text-red-500 dark:text-red-400 font-medium">
                {{ uploadForm.errors.module_zip }}
            </p>
        </div>

        <div class="space-y-4 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center gap-2 pb-4 border-b border-neutral-100 dark:border-neutral-800">
                <Cpu class="h-4 w-4 text-emerald-500" />
                <h4 class="text-neutral-800 text-sm font-bold tracking-tight dark:text-white">
                    App Module Manager
                </h4>
            </div>

            <div v-if="modules.length === 0" class="py-12 text-center text-xs text-neutral-500">
                No dynamic modules discovered in this workspace.
            </div>

            <div v-else class="grid gap-4 pt-2">
                <div
                    v-for="module in modules"
                    :key="module.name"
                    class="flex flex-col gap-4 rounded-xl border border-neutral-150 p-5 transition-all hover:border-neutral-300 dark:border-neutral-800 dark:hover:border-neutral-700 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="space-y-1">
                        <h5 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
                            <span>{{ module.name }}</span>
                            <!-- Status Badges -->
                            <span
                                v-if="!installedModules.includes(module.name)"
                                class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 text-[10px] font-semibold text-neutral-600 dark:bg-neutral-800 dark:text-neutral-450"
                            >
                                Not Installed
                            </span>
                            <span
                                v-else-if="enabledModules.includes(module.name)"
                                class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-950/45 dark:text-emerald-400"
                            >
                                Active
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/45 dark:text-amber-400"
                            >
                                Disabled
                            </span>
                        </h5>
                        <p class="text-xs text-neutral-500 max-w-lg">
                            {{ module.description }}
                        </p>
                    </div>

                    <!-- Life Cycle Actions -->
                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <!-- Case 1: Module is NOT installed -->
                        <template v-if="!installedModules.includes(module.name)">
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-8 gap-1.5 border-emerald-500/30 text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 dark:border-emerald-500/20 dark:text-emerald-400 dark:hover:bg-emerald-950/30"
                                :disabled="processingModule !== null"
                                @click="installModule(module.name)"
                            >
                                <Loader2 v-if="processingModule === module.name && processingAction === 'install'" class="h-3 w-3 animate-spin" />
                                <Download v-else class="h-3 w-3" />
                                Install
                            </Button>
                        </template>

                        <!-- Case 2: Module IS installed -->
                        <template v-else>
                            <!-- Enable / Disable Switcher -->
                            <Button
                                v-if="enabledModules.includes(module.name)"
                                size="sm"
                                variant="outline"
                                class="h-8 gap-1.5 border-amber-500/30 text-amber-600 hover:bg-amber-50 hover:text-amber-700 dark:border-amber-500/20 dark:text-amber-400 dark:hover:bg-amber-950/30"
                                :disabled="processingModule !== null"
                                @click="disableModule(module.name)"
                            >
                                <Loader2 v-if="processingModule === module.name && processingAction === 'disable'" class="h-3 w-3 animate-spin" />
                                <PowerOff v-else class="h-3 w-3" />
                                Disable
                            </Button>

                            <Button
                                v-else
                                size="sm"
                                variant="outline"
                                class="h-8 gap-1.5 border-emerald-500/30 text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 dark:border-emerald-500/20 dark:text-emerald-400 dark:hover:bg-emerald-950/30"
                                :disabled="processingModule !== null"
                                @click="enableModule(module.name)"
                            >
                                <Loader2 v-if="processingModule === module.name && processingAction === 'enable'" class="h-3 w-3 animate-spin" />
                                <Power v-else class="h-3 w-3" />
                                Enable
                            </Button>

                            <!-- Uninstall -->
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-8 gap-1.5 border-red-500/30 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-500/20 dark:text-red-400 dark:hover:bg-red-950/30"
                                :disabled="processingModule !== null"
                                @click="uninstallModule(module.name)"
                            >
                                <Loader2 v-if="processingModule === module.name && processingAction === 'uninstall'" class="h-3 w-3 animate-spin" />
                                <Trash2 v-else class="h-3 w-3" />
                                Uninstall
                            </Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
