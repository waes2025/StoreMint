<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { computed, ref, watchEffect, inject } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import type { TwoFactorConfigContent } from '@/types';

const showRecoveryInput = ref<boolean>(false);
const code = ref<string>('');

// Inject layout language
const selectedLang = inject('authLanguage', ref('English'));

const contentTranslations = {
    English: {
        recovery: {
            title: 'Recovery code',
            description:
                'Please confirm access to your account by entering one of your emergency recovery codes.',
            buttonText: 'login using an authentication code',
        },
        auth: {
            title: 'Authentication code',
            description:
                'Enter the authentication code provided by your authenticator application.',
            buttonText: 'login using a recovery code',
        },
        continueBtn: 'Continue',
        orYouCan: 'or you can ',
    },
    Spanish: {
        recovery: {
            title: 'Codigo de recuperacion',
            description:
                'Confirme el acceso ingresando uno de sus codigos de emergencia.',
            buttonText: 'iniciar sesion con codigo de autenticacion',
        },
        auth: {
            title: 'Codigo de autenticacion',
            description:
                'Ingrese el codigo provisto por su aplicacion de autenticacion.',
            buttonText: 'iniciar sesion con codigo de recuperacion',
        },
        continueBtn: 'Continuar',
        orYouCan: 'o puede ',
    },
    French: {
        recovery: {
            title: 'Code de recuperation',
            description:
                "Veuillez confirmer l'acces en saisissant un code de secours.",
            buttonText: "se connecter avec un code d'authentification",
        },
        auth: {
            title: "Code d'authentification",
            description:
                "Entrez le code fourni par votre application d'authentification.",
            buttonText: 'se connecter avec un code de recuperation',
        },
        continueBtn: 'Continuer',
        orYouCan: 'ou vous pouvez ',
    },
    German: {
        recovery: {
            title: 'Wiederherstellungscode',
            description:
                'Geben Sie einen Ihrer Notfall-Wiederherstellungscodes ein.',
            buttonText: 'Mit Authentifizierungscode anmelden',
        },
        auth: {
            title: 'Authentifizierungscode',
            description:
                'Geben Sie den Code aus Ihrer Authentifizierungs-App ein.',
            buttonText: 'Mit Wiederherstellungscode anmelden',
        },
        continueBtn: 'Weiter',
        orYouCan: 'oder Sie konnen ',
    },
    Bengali: {
        recovery: {
            title: 'রিকভারি কোড',
            description:
                'অনুগ্রহ করে আপনার জরুরি রিকভারি কোডগুলোর একটি প্রদান করুন।',
            buttonText: 'অথেন্টিকেশন কোড ব্যবহার করে লগইন করুন',
        },
        auth: {
            title: 'অথেন্টিকেশন কোড',
            description: 'আপনার অথেনটিকেটর অ্যাপের দেওয়া কোডটি প্রবেশ করান।',
            buttonText: 'রিকভারি কোড ব্যবহার করে লগইন করুন',
        },
        continueBtn: 'এগিয়ে যান',
        orYouCan: 'অথবা আপনি করতে পারেন ',
    },
};

const authConfigContent = computed(() => {
    const lang = selectedLang.value as keyof typeof contentTranslations;
    const trans = contentTranslations[lang] || contentTranslations.English;
    if (showRecoveryInput.value) {
        return {
            title: trans.recovery.title,
            description: trans.recovery.description,
            buttonText: trans.recovery.buttonText,
            continueBtn: trans.continueBtn,
            orYouCan: trans.orYouCan,
        };
    }
    return {
        title: trans.auth.title,
        description: trans.auth.description,
        buttonText: trans.auth.buttonText,
        continueBtn: trans.continueBtn,
        orYouCan: trans.orYouCan,
    };
});

watchEffect(() => {
    setLayoutProps({
        title: authConfigContent.value.title,
        description: authConfigContent.value.description,
    });
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};
</script>

<template>
    <Head :title="authConfigContent.title" />

    <div class="space-y-6">
        <template v-if="!showRecoveryInput">
            <Form
                v-bind="route('two-factor.login.store.form')"
                class="space-y-4"
                reset-on-error
                @error="code = ''"
                #default="{ errors, processing, clearErrors }"
            >
                <input type="hidden" name="code" :value="code" />
                <div
                    class="flex flex-col items-center justify-center space-y-3 text-center"
                >
                    <div class="flex w-full items-center justify-center">
                        <InputOTP
                            id="otp"
                            v-model="code"
                            :maxlength="6"
                            :disabled="processing"
                            autofocus
                        >
                            <InputOTPGroup>
                                <InputOTPSlot
                                    v-for="index in 6"
                                    :key="index"
                                    :index="index - 1"
                                />
                            </InputOTPGroup>
                        </InputOTP>
                    </div>
                    <InputError :message="errors.code" />
                </div>
                <Button
                    type="submit"
                    class="w-full cursor-pointer border-none bg-gradient-to-r from-emerald-600 to-teal-600 font-bold text-white shadow-md shadow-emerald-500/10 transition-all duration-300 hover:from-emerald-500 hover:to-teal-500 hover:shadow-emerald-500/20"
                    :disabled="processing"
                >
                    {{ authConfigContent.continueBtn }}
                </Button>
                <div class="text-center text-xs text-neutral-500">
                    <span>{{ authConfigContent.orYouCan }}</span>
                    <button
                        type="button"
                        class="cursor-pointer border-none bg-transparent p-0 font-semibold text-emerald-600 underline decoration-emerald-500/30 underline-offset-4 transition-colors hover:text-emerald-500 hover:decoration-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300"
                        @click="() => toggleRecoveryMode(clearErrors)"
                    >
                        {{ authConfigContent.buttonText }}
                    </button>
                </div>
            </Form>
        </template>

        <template v-else>
            <Form
                v-bind="route('two-factor.login.store.form')"
                class="space-y-4"
                reset-on-error
                #default="{ errors, processing, clearErrors }"
            >
                <Input
                    name="recovery_code"
                    type="text"
                    placeholder="Enter recovery code"
                    :autofocus="showRecoveryInput"
                    required
                    class="focus-visible:border-emerald-500 focus-visible:ring-emerald-500/20"
                />
                <InputError :message="errors.recovery_code" />
                <Button
                    type="submit"
                    class="w-full cursor-pointer border-none bg-gradient-to-r from-emerald-600 to-teal-600 font-bold text-white shadow-md shadow-emerald-500/10 transition-all duration-300 hover:from-emerald-500 hover:to-teal-500 hover:shadow-emerald-500/20"
                    :disabled="processing"
                >
                    {{ authConfigContent.continueBtn }}
                </Button>

                <div class="text-center text-xs text-neutral-500">
                    <span>{{ authConfigContent.orYouCan }}</span>
                    <button
                        type="button"
                        class="cursor-pointer border-none bg-transparent p-0 font-semibold text-emerald-600 underline decoration-emerald-500/30 underline-offset-4 transition-colors hover:text-emerald-500 hover:decoration-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300"
                        @click="() => toggleRecoveryMode(clearErrors)"
                    >
                        {{ authConfigContent.buttonText }}
                    </button>
                </div>
            </Form>
        </template>
    </div>
</template>
