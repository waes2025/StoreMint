<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { route } from '@/lib/route';

const props = defineProps<{ heroSlides?: any[] }>();

const form = useForm({ slides: props.heroSlides ?? [] });

function addSlide() {
    form.slides.push({
        title: '',
        subtitle: '',
        image: '',
        link: '',
        is_active: true,
    });
}

function removeSlide(index: number) {
    form.slides.splice(index, 1);
}

function submit() {
    form.patch(route('settings.hero-slides.update'));
}
</script>

<template>
    <Head title="Hero Slides" />

    <div class="mx-auto max-w-4xl py-8">
        <h1 class="mb-4 text-2xl font-bold">Hero Slides</h1>

        <div class="space-y-4">
            <div
                v-for="(slide, idx) in form.slides"
                :key="idx"
                class="rounded-md border p-4"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div class="font-semibold">Slide {{ idx + 1 }}</div>
                    <button
                        type="button"
                        @click="removeSlide(idx)"
                        class="text-sm text-red-600"
                    >
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <input
                        v-model="form.slides[idx].title"
                        placeholder="Title"
                        class="w-full rounded border p-2"
                    />
                    <input
                        v-model="form.slides[idx].subtitle"
                        placeholder="Subtitle"
                        class="w-full rounded border p-2"
                    />
                    <input
                        v-model="form.slides[idx].image"
                        placeholder="Image URL"
                        class="w-full rounded border p-2"
                    />
                    <input
                        v-model="form.slides[idx].link"
                        placeholder="Link (optional)"
                        class="w-full rounded border p-2"
                    />
                    <label class="mt-1 inline-flex items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="form.slides[idx].is_active"
                        />
                        <span class="text-sm">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-2">
                <button
                    type="button"
                    @click="addSlide"
                    class="rounded bg-emerald-600 px-4 py-2 text-white"
                >
                    Add Slide
                </button>
                <button
                    type="button"
                    @click="submit"
                    class="rounded bg-neutral-800 px-4 py-2 text-white"
                >
                    Save Slides
                </button>
            </div>
        </div>
    </div>
</template>
