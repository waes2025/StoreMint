<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref, inject, computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
defineOptions({
    layout: {
        title: 'Reset password',
        description: 'Please enter your new password below',
    },
});

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const inputEmail = ref(props.email);

// Inject layout language
const selectedLang = inject('authLanguage', ref('English'));

const labels = {
    English: {
        email: 'Email',
        password: 'Password',
        confirmPassword: 'Confirm password',
        btn: 'Reset password',
    },
    Spanish: {
        email: 'Correo electronico',
        password: 'Contrasena',
        confirmPassword: 'Confirmar contrasena',
        btn: 'Restablecer contrasena',
    },
    French: {
        email: 'E-mail',
        password: 'Mot de passe',
        confirmPassword: 'Confirmer le mot de passe',
        btn: 'Reinitialiser le mot de passe',
    },
    German: {
        email: 'E-Mail',
        password: 'Passwort',
        confirmPassword: 'Passwort bestatigen',
        btn: 'Passwort zurucksetzen',
    },
    Bengali: {
        email: 'ইমেল',
        password: 'পাসওয়ার্ড',
        confirmPassword: 'পাসওয়ার্ড নিশ্চিত করুন',
        btn: 'পাসওয়ার্ড পরিবর্তন করুন',
    },
};

const currentText = computed(() => {
    return labels[selectedLang.value as keyof typeof labels] || labels.English;
});
</script>

<template>
    <Head :title="currentText.btn" />

    <Form
        v-bind="route('password.update.form')"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-6">
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
                    autocomplete="email"
                    v-model="inputEmail"
                    class="mt-1 block w-full focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                    readonly
                />
                <InputError :message="errors.email" class="mt-2" />
            </div>

            <div class="grid gap-2">
                <Label
                    for="password"
                    class="text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                    >{{ currentText.password }}</Label
                >
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    class="mt-1 block w-full focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                    autofocus
                    placeholder="Password"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label
                    for="password_confirmation"
                    class="text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                    >{{ currentText.confirmPassword }}</Label
                >
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    class="mt-1 block w-full focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                    placeholder="Confirm password"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full cursor-pointer border-none bg-gradient-to-r from-emerald-600 to-teal-600 font-bold text-white shadow-md shadow-emerald-500/10 transition-all duration-300 hover:from-emerald-500 hover:to-teal-500 hover:shadow-emerald-500/20"
                :disabled="processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="processing" />
                {{ currentText.btn }}
            </Button>
        </div>
    </Form>
</template>
