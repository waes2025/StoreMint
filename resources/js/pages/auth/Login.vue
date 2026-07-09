<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TeamInvitationAlert from '@/components/TeamInvitationAlert.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import type { TeamInvitationContext } from '@/types';
import { inject, computed, ref } from 'vue';

defineOptions({
    layout: {
        title: 'Log in to your account',
        description: 'Enter your email and password below to log in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    teamInvitation?: TeamInvitationContext | null;
}>();

// Inject layout language
const selectedLang = inject('authLanguage', ref('English'));

const labels = {
    English: {
        email: 'Email address',
        password: 'Password',
        remember: 'Remember me',
        loginBtn: 'Log in',
        noAccount: "Don't have an account?",
        register: 'Register',
        forgot: 'Forgot password?',
    },
    Spanish: {
        email: 'Correo electronico',
        password: 'Contrasena',
        remember: 'Recordarme',
        loginBtn: 'Iniciar sesion',
        noAccount: '¿No tiene una cuenta?',
        register: 'Registrarse',
        forgot: '¿Olvido su contrasena?',
    },
    French: {
        email: 'Adresse e-mail',
        password: 'Mot de passe',
        remember: 'Se souvenir de moi',
        loginBtn: 'Connexion',
        noAccount: 'Vous n\'avez pas de compte ?',
        register: 'S\'inscrire',
        forgot: 'Mot de passe oublie ?',
    },
    German: {
        email: 'E-Mail-Adresse',
        password: 'Passwort',
        remember: 'Angemeldet bleiben',
        loginBtn: 'Anmelden',
        noAccount: 'Noch kein Konto?',
        register: 'Registrieren',
        forgot: 'Passwort vergessen?',
    },
    Bengali: {
        email: 'ইমেল ঠিকানা',
        password: 'পাসওয়ার্ড',
        remember: 'লগইন তথ্য সংরক্ষণ করুন',
        loginBtn: 'লগইন করুন',
        noAccount: 'অ্যাকাউন্ট নেই?',
        register: 'নিবন্ধন করুন',
        forgot: 'পাসওয়ার্ড ভুলে গেছেন?',
    }
};

const currentText = computed(() => {
    return labels[selectedLang.value as keyof typeof labels] || labels.English;
});
</script>

<template>
    <Head :title="currentText.loginBtn" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400"
    >
        {{ status }}
    </div>

    <TeamInvitationAlert
        v-if="teamInvitation"
        :invitation="teamInvitation"
        :action="currentText.loginBtn"
    />

    <Form
        v-bind="route('login.store.form')"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email" class="text-neutral-700 dark:text-neutral-300 font-semibold text-xs">{{ currentText.email }}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                    class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-neutral-700 dark:text-neutral-300 font-semibold text-xs">{{ currentText.password }}</Label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request').url"
                        class="text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors"
                        :tabindex="5"
                    >
                        {{ currentText.forgot }}
                    </Link>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password"
                    class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3 text-neutral-600 dark:text-neutral-400 text-xs font-medium cursor-pointer select-none">
                    <Checkbox 
                        id="remember" 
                        name="remember" 
                        :tabindex="3" 
                        class="data-[state=checked]:bg-emerald-600 data-[state=checked]:border-emerald-600 focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20" 
                    />
                    <span>{{ currentText.remember }}</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/20 transition-all duration-300 border-none cursor-pointer"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                {{ currentText.loginBtn }}
            </Button>
        </div>

        <div class="text-center text-xs text-neutral-500">
            {{ currentText.noAccount }}
            <Link 
                :href="route('register').url" 
                class="font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors underline decoration-emerald-500/30 hover:decoration-emerald-500 underline-offset-4"
            >
                {{ currentText.register }}
            </Link>
        </div>
    </Form>
</template>
