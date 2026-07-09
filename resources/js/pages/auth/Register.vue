<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { inject, computed, ref } from 'vue';

defineOptions({
    layout: {
        title: 'Create a customer account',
        description: 'Enter your details below to create your storefront buyer account',
    },
});

// Inject layout language
const selectedLang = inject('authLanguage', ref('English'));

const labels = {
    English: {
        firstName: 'First name',
        lastName: 'Last name',
        email: 'Email address',
        password: 'Password',
        confirmPassword: 'Confirm Password',
        createBtn: 'Create Account',
        alreadyHave: 'Already have an account?',
        login: 'Log in',
        placeholderFirstName: 'Enter first name',
        placeholderLastName: 'Enter last name',
    },
    Spanish: {
        firstName: 'Nombre',
        lastName: 'Apellido',
        email: 'Correo electronico',
        password: 'Contrasena',
        confirmPassword: 'Confirmar contrasena',
        createBtn: 'Crear cuenta',
        alreadyHave: '¿Ya tiene una cuenta?',
        login: 'Iniciar sesion',
        placeholderFirstName: 'Ingrese el primer nombre',
        placeholderLastName: 'Ingrese el apellido',
    },
    French: {
        firstName: 'Prenom',
        lastName: 'Nom',
        email: 'Adresse e-mail',
        password: 'Mot de passe',
        confirmPassword: 'Confirmer le mot de passe',
        createBtn: 'Creer un compte',
        alreadyHave: 'Vous avez deja un compte ?',
        login: 'Connexion',
        placeholderFirstName: 'Entrez le prenom',
        placeholderLastName: 'Entrez le nom',
    },
    German: {
        firstName: 'Vorname',
        lastName: 'Nachname',
        email: 'E-Mail-Adresse',
        password: 'Passwort',
        confirmPassword: 'Passwort bestatigen',
        createBtn: 'Konto erstellen',
        alreadyHave: 'Bereits ein Konto?',
        login: 'Anmelden',
        placeholderFirstName: 'Vorname eingeben',
        placeholderLastName: 'Nachname eingeben',
    },
    Bengali: {
        firstName: 'প্রথম নাম',
        lastName: 'শেষ নাম',
        email: 'ইমেল ঠিকানা',
        password: 'পাসওয়ার্ড',
        confirmPassword: 'পাসওয়ার্ড নিশ্চিত করুন',
        createBtn: 'অ্যাকাউন্ট তৈরি করুন',
        alreadyHave: 'ইতিমধ্যে অ্যাকাউন্ট আছে?',
        login: 'লগইন করুন',
        placeholderFirstName: 'প্রথম নাম লিখুন',
        placeholderLastName: 'শেষ নাম লিখুন',
    }
};

const currentText = computed(() => {
    return labels[selectedLang.value as keyof typeof labels] || labels.English;
});
</script>

<template>
    <Head :title="currentText.createBtn" />

    <Form
        v-bind="route('register.store.form')"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="first_name" class="text-neutral-700 dark:text-neutral-300 font-semibold text-xs">{{ currentText.firstName }}</Label>
                    <Input
                        id="first_name"
                        type="text"
                        name="first_name"
                        required
                        autofocus
                        :placeholder="currentText.placeholderFirstName"
                        class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                    />
                    <InputError :message="errors.first_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="last_name" class="text-neutral-700 dark:text-neutral-300 font-semibold text-xs">{{ currentText.lastName }}</Label>
                    <Input
                        id="last_name"
                        type="text"
                        name="last_name"
                        required
                        :placeholder="currentText.placeholderLastName"
                        class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                    />
                    <InputError :message="errors.last_name" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="email" class="text-neutral-700 dark:text-neutral-300 font-semibold text-xs">{{ currentText.email }}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                    class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-neutral-700 dark:text-neutral-300 font-semibold text-xs">{{ currentText.password }}</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="Password"
                    class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="text-neutral-700 dark:text-neutral-300 font-semibold text-xs">{{ currentText.confirmPassword }}</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm Password"
                    class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/20 transition-all duration-300 border-none cursor-pointer"
                :disabled="processing"
                data-test="register-button"
            >
                <Spinner v-if="processing" />
                {{ currentText.createBtn }}
            </Button>
        </div>

        <div class="text-center text-xs text-neutral-500">
            {{ currentText.alreadyHave }}
            <Link 
                :href="route('login').url" 
                class="font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors underline decoration-emerald-500/30 hover:decoration-emerald-500 underline-offset-4"
            >
                {{ currentText.login }}
            </Link>
        </div>
    </Form>
</template>
