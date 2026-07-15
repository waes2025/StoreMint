<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { inject, computed, ref } from 'vue';

defineOptions({
    layout: {
        title: 'Forgot password',
        description: 'Enter your email to receive a password reset link',
    },
});

defineProps<{
    status?: string;
}>();

// Inject layout language
const selectedLang = inject('authLanguage', ref('English'));

const labels = {
    English: {
        email: 'Email address',
        btn: 'Email password reset link',
        orReturn: 'Or, return to',
        login: 'log in',
    },
    Spanish: {
        email: 'Correo electronico',
        btn: 'Enviar enlace de restablecimiento',
        orReturn: 'O volver a',
        login: 'iniciar sesion',
    },
    French: {
        email: 'Adresse e-mail',
        btn: 'Envoyer le lien de reinitialisation',
        orReturn: 'Ou retourner a la page de',
        login: 'connexion',
    },
    German: {
        email: 'E-Mail-Adresse',
        btn: 'Link zum Zurucksetzen senden',
        orReturn: 'Oder zuruck zur',
        login: 'Anmeldung',
    },
    Bengali: {
        email: 'ইমেল ঠিকানা',
        btn: 'পাসওয়ার্ড রিসেট লিঙ্ক পাঠান',
        orReturn: 'অথবা, ফিরে যান',
        login: 'লগইন পেজে',
    },
};

const currentText = computed(() => {
    return labels[selectedLang.value as keyof typeof labels] || labels.English;
});
</script>

<template>
    <Head :title="currentText.btn" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400"
    >
        {{ status }}
    </div>

    <div class="space-y-6">
        <Form
            v-bind="route('password.email.form')"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label
                    for="email"
                    class="text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                    >{{ currentText.email }}</Label
                >
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="off"
                    autofocus
                    placeholder="email@example.com"
                    class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="my-6 flex items-center justify-start">
                <Button
                    class="w-full cursor-pointer border-none bg-gradient-to-r from-emerald-600 to-teal-600 font-bold text-white shadow-md shadow-emerald-500/10 transition-all duration-300 hover:from-emerald-500 hover:to-teal-500 hover:shadow-emerald-500/20"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    {{ currentText.btn }}
                </Button>
            </div>
        </Form>

        <div class="space-x-1 text-center text-xs text-neutral-500">
            <span>{{ currentText.orReturn }}</span>
            <Link
                :href="route('login').url"
                class="font-semibold text-emerald-600 underline decoration-emerald-500/30 underline-offset-4 transition-colors hover:text-emerald-500 hover:decoration-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300"
            >
                {{ currentText.login }}
            </Link>
        </div>
    </div>
</template>
