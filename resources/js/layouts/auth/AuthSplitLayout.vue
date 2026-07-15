<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Leaf,
    TrendingUp,
    ArrowUpRight,
    Sun,
    Moon,
    Globe,
    Check,
    ChevronDown,
    Monitor,
} from '@lucide/vue';
import { home } from '@/routes';
import { useAppearance } from '@/composables/useAppearance';
import { ref, computed, useTemplateRef, provide } from 'vue';
import { onClickOutside } from '@vueuse/core';

const page = usePage();
const name = page.props.name || 'StoreMint';

const props = defineProps<{
    title?: string;
    description?: string;
}>();

// Theme setup using useAppearance
const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

// Language setup
const selectedLang = ref('English');
provide('authLanguage', selectedLang);
const langOpen = ref(false);
const langDropdownRef = useTemplateRef('langDropdownRef');
const languages = ['English', 'Spanish', 'French', 'German', 'Bengali'];

onClickOutside(langDropdownRef, () => {
    langOpen.value = false;
});

// Translation systems
const translations = {
    English: {
        titlePart1: 'Manage Your',
        titlePart2: 'Digital Commerce',
        description:
            'Access your unified StoreMint control center. Configure products, manage custom checkout flows, and view instant sales reports with ease.',
        offerTag: '✨ EXCLUSIVE OFFER',
        offerTitle: 'Get 15% Off Your First Order',
        offerDesc:
            'Sign up for a free customer account today and use promo code WELCOME15 at checkout.',
        copyright: '© 2026 StoreMint Inc.',
        status: 'Systems Operational',
    },
    Spanish: {
        titlePart1: 'Gestione su',
        titlePart2: 'Comercio Digital',
        description:
            'Acceda a su centro de control unificado de StoreMint. Configure productos, gestione flujos de pago personalizados y vea informes de ventas instantáneos.',
        offerTag: '✨ OFERTA EXCLUSIVA',
        offerTitle: '15% de descuento en tu primer pedido',
        offerDesc:
            'Registrese hoy para obtener una cuenta de cliente gratuita y use el codigo WELCOME15.',
        copyright: '© 2026 StoreMint Inc.',
        status: 'Sistemas Operativos',
    },
    French: {
        titlePart1: 'Gerez votre',
        titlePart2: 'Commerce Numerique',
        description:
            'Accedez a votre centre de controle StoreMint unifie. Configurez les produits, gerez les flux de paiement personnalises et affichez les rapports de vente.',
        offerTag: '✨ OFFRE EXCLUSIVE',
        offerTitle: '15% de reduction sur votre premier achat',
        offerDesc:
            "Inscrivez-vous des aujourd'hui et utilisez le code promo WELCOME15 lors du paiement.",
        copyright: '© 2026 StoreMint Inc.',
        status: 'Systemes Operationnels',
    },
    German: {
        titlePart1: 'Verwalten Sie Ihren',
        titlePart2: 'Digitalen Handel',
        description:
            'Greifen Sie auf Ihr einheitliches StoreMint-Kontrollzentrum zu. Konfigurieren Sie Produkte, verwalten Sie benutzerdefinierte Kassenablaufe und Berichte.',
        offerTag: '✨ EXKLUSIVES ANGEBOT',
        offerTitle: '15% Rabatt auf Ihre erste Bestellung',
        offerDesc:
            'Erstellen Sie ein kostenloses Kundenkonto und nutzen Sie den Code WELCOME15.',
        copyright: '© 2026 StoreMint Inc.',
        status: 'Systeme Betriebsbereit',
    },
    Bengali: {
        titlePart1: 'আপনার পরিচালনা করুন',
        titlePart2: 'ডিজিটাল কমার্স',
        description:
            'আপনার ইউনিফাইড স্টোরমিন্ট কন্ট্রোল সেন্টারে প্রবেশ করুন। পণ্য কনফিগার করুন, কাস্টম চেকআউট ফ্লো পরিচালনা করুন এবং তাত্ক্ষণিক প্রতিবেদন দেখুন।',
        offerTag: '✨ বিশেষ অফার',
        offerTitle: 'প্রথম অর্ডারে ১৫% মূল্যছাড় পান',
        offerDesc:
            'আজই গ্রাহক অ্যাকাউন্ট তৈরি করুন এবং চেকআউটে প্রোমো কোড WELCOME15 ব্যবহার করুন।',
        copyright: '© ২০২৬ স্টোরমিন্ট ইনকর্পোরেটেড',
        status: 'সিস্টেম সচল রয়েছে',
    },
};

const formTranslations = {
    English: {
        'Log in to your account': {
            title: 'Log in to your account',
            description: 'Enter your email and password below to log in',
        },
        'Create a customer account': {
            title: 'Create a customer account',
            description:
                'Enter your details below to create your storefront buyer account',
        },
        'Forgot password': {
            title: 'Forgot password',
            description: 'Enter your email to receive a password reset link',
        },
        'Reset password': {
            title: 'Reset password',
            description: 'Please enter your new password below',
        },
        'Authentication code': {
            title: 'Authentication code',
            description:
                'Enter the authentication code provided by your authenticator application.',
        },
        'Recovery code': {
            title: 'Recovery code',
            description:
                'Please confirm access to your account by entering one of your emergency recovery codes.',
        },
    },
    Spanish: {
        'Log in to your account': {
            title: 'Iniciar sesion',
            description:
                'Ingrese su correo electronico y contrasena a continuacion',
        },
        'Create a customer account': {
            title: 'Crear una cuenta',
            description: 'Ingrese sus datos a continuacion para registrarse',
        },
        'Forgot password': {
            title: '¿Olvido su contrasena?',
            description:
                'Ingrese su correo electronico para recibir un enlace de restablecimiento',
        },
        'Reset password': {
            title: 'Restablecer contrasena',
            description: 'Ingrese su nueva contrasena a continuacion',
        },
        'Authentication code': {
            title: 'Codigo de autenticacion',
            description:
                'Ingrese el codigo provisto por su aplicacion de autenticacion.',
        },
        'Recovery code': {
            title: 'Codigo de recuperacion',
            description:
                'Confirme el acceso ingresando uno de sus codigos de emergencia.',
        },
    },
    French: {
        'Log in to your account': {
            title: 'Connexion',
            description:
                'Saisissez votre e-mail et votre mot de passe pour vous connecter',
        },
        'Create a customer account': {
            title: 'Creer un compte',
            description:
                'Saisissez vos coordonnees ci-dessous pour creer votre compte',
        },
        'Forgot password': {
            title: 'Mot de passe oublie',
            description:
                'Saisissez votre e-mail pour recevoir un lien de reinitialisation',
        },
        'Reset password': {
            title: 'Reinitialiser le mot de passe',
            description:
                'Veuillez saisir votre nouveau mot de passe ci-dessous',
        },
        'Authentication code': {
            title: "Code d'authentification",
            description:
                "Entrez le code fourni par votre application d'authentification.",
        },
        'Recovery code': {
            title: 'Code de recuperation',
            description:
                "Veuillez confirmer l'acces en saisissant un code de secours.",
        },
    },
    German: {
        'Log in to your account': {
            title: 'Konto-Anmeldung',
            description:
                'Geben Sie E-Mail und Passwort ein, um sich anzumelden',
        },
        'Create a customer account': {
            title: 'Konto erstellen',
            description:
                'Geben Sie Ihre Daten ein, um ein Kundenkonto zu erstellen',
        },
        'Forgot password': {
            title: 'Passwort vergessen',
            description:
                'Geben Sie Ihre E-Mail-Adresse fur einen Rucksetzlink ein',
        },
        'Reset password': {
            title: 'Passwort zurucksetzen',
            description: 'Bitte geben Sie unten Ihr neues Passwort ein',
        },
        'Authentication code': {
            title: 'Authentifizierungscode',
            description:
                'Geben Sie den Code aus Ihrer Authentifizierungs-App ein.',
        },
        'Recovery code': {
            title: 'Wiederherstellungscode',
            description:
                'Geben Sie einen Ihrer Notfall-Wiederherstellungscodes ein.',
        },
    },
    Bengali: {
        'Log in to your account': {
            title: 'আপনার অ্যাকাউন্টে লগইন করুন',
            description: 'লগইন করতে আপনার ইমেল এবং পাসওয়ার্ড প্রবেশ করান',
        },
        'Create a customer account': {
            title: 'নতুন অ্যাকাউন্ট তৈরি করুন',
            description:
                'ক্রেতা অ্যাকাউন্ট তৈরি করতে আপনার তথ্য নিচে প্রদান করুন',
        },
        'Forgot password': {
            title: 'পাসওয়ার্ড ভুলে গেছেন?',
            description: 'পাসওয়ার্ড রিসেট লিঙ্ক পেতে আপনার ইমেল প্রবেশ করান',
        },
        'Reset password': {
            title: 'পাসওয়ার্ড পরিবর্তন করুন',
            description: 'অনুগ্রহ করে নিচে আপনার নতুন পাসওয়ার্ডটি লিখুন',
        },
        'Authentication code': {
            title: 'অথেন্টিকেশন কোড',
            description: 'আপনার অথেনটিকেটর অ্যাপের দেওয়া কোডটি প্রবেশ করান।',
        },
        'Recovery code': {
            title: 'রিকভারি কোড',
            description:
                'অনুগ্রহ করে আপনার জরুরি রিকভারি কোডগুলোর একটি প্রদান করুন।',
        },
    },
};

const currentText = computed(() => {
    return (
        translations[selectedLang.value as keyof typeof translations] ||
        translations.English
    );
});

const promoCoupon = computed(
    () =>
        page.props.promo_coupon as {
            code: string;
            discount_type: string;
            discount_value: number;
            description: string;
        } | null,
);

const promoTitle = computed(() => {
    if (promoCoupon.value) {
        const discountStr =
            promoCoupon.value.discount_type === 'percentage'
                ? `${Math.round(promoCoupon.value.discount_value)}%`
                : `$${Math.round(promoCoupon.value.discount_value)}`;

        if (selectedLang.value === 'Bengali') {
            return `প্রথম অর্ডারে ${discountStr} মূল্যছাড় পান`;
        } else if (selectedLang.value === 'Spanish') {
            return `${discountStr} de descuento en tu primer pedido`;
        } else if (selectedLang.value === 'French') {
            return `${discountStr} de reduction sur votre premier achat`;
        } else if (selectedLang.value === 'German') {
            return `${discountStr} Rabatt auf Ihre erste Bestellung`;
        }
        return `Get ${discountStr} Off Your First Order`;
    }
    return currentText.value.offerTitle;
});

const promoDesc = computed(() => {
    if (promoCoupon.value) {
        const code = promoCoupon.value.code;
        if (selectedLang.value === 'Bengali') {
            return `আজই গ্রাহক অ্যাকাউন্ট তৈরি করুন এবং চেকআউটে প্রোমো কোড ${code} ব্যবহার করুন।`;
        } else if (selectedLang.value === 'Spanish') {
            return `Registrese hoy para obtener una cuenta de cliente gratuita y use el codigo ${code}.`;
        } else if (selectedLang.value === 'French') {
            return `Inscrivez-vous des aujourd'hui et utilisez le code promo ${code} lors du paiement.`;
        } else if (selectedLang.value === 'German') {
            return `Erstellen Sie ein kostenloses Kundenkonto und nutzen Sie den Code ${code}.`;
        }
        return `Sign up for a free customer account today and use promo code ${code} at checkout.`;
    }
    return currentText.value.offerDesc;
});

const translatedTitle = computed(() => {
    const lang = selectedLang.value as keyof typeof formTranslations;
    const key = props.title || '';
    if (formTranslations[lang] && (formTranslations[lang] as any)[key]) {
        return (formTranslations[lang] as any)[key].title;
    }
    return props.title;
});

const translatedDescription = computed(() => {
    const lang = selectedLang.value as keyof typeof formTranslations;
    const key = props.title || '';
    if (formTranslations[lang] && (formTranslations[lang] as any)[key]) {
        return (formTranslations[lang] as any)[key].description;
    }
    return props.description;
});
</script>

<template>
    <div
        class="relative grid h-screen w-screen grid-cols-1 overflow-hidden bg-neutral-50 lg:grid-cols-2 dark:bg-neutral-950"
    >
        <!-- Left Hero Column -->
        <div
            class="relative hidden h-full flex-col overflow-hidden border-r border-neutral-200 bg-neutral-100/40 p-10 text-neutral-800 lg:flex dark:border-neutral-800/40 dark:bg-neutral-900/40 dark:text-neutral-200"
        >
            <!-- Glowing background accents -->
            <div
                class="pointer-events-none absolute -top-24 -left-24 h-[400px] w-[400px] rounded-full bg-emerald-500/5 blur-[100px] dark:bg-emerald-500/15"
            ></div>
            <div
                class="pointer-events-none absolute -right-32 -bottom-32 h-[500px] w-[500px] rounded-full bg-teal-500/5 blur-[120px] dark:bg-teal-500/10"
            ></div>

            <!-- Grid pattern overlay -->
            <div
                class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(0,0,0,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,0,0,0.02)_1px,transparent_1px)] bg-[size:24px_24px] dark:bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)]"
            ></div>

            <!-- Logo Section -->
            <div class="relative z-20 flex items-center justify-between">
                <Link
                    :href="home().url"
                    class="flex items-center gap-2.5 text-lg font-bold tracking-tight text-neutral-900 transition hover:opacity-90 dark:text-white"
                >
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-tr from-emerald-400 to-emerald-600 text-white shadow-md shadow-emerald-500/20"
                    >
                        <Leaf class="h-4.5 w-4.5" />
                    </div>
                    <span>Store<span class="text-emerald-500">Mint</span></span>
                </Link>
            </div>

            <!-- Middle Content Section -->
            <div class="relative z-20 my-auto max-w-md space-y-6">
                <h2
                    class="text-4xl leading-tight font-extrabold tracking-tight text-neutral-900 lg:text-5xl dark:text-white"
                >
                    {{ currentText.titlePart1 }} <br />
                    <span
                        class="bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent dark:from-emerald-400 dark:to-teal-300"
                        >{{ currentText.titlePart2 }}</span
                    >
                </h2>
                <p
                    class="text-sm leading-relaxed text-neutral-600 dark:text-neutral-400"
                >
                    {{ currentText.description }}
                </p>

                <!-- Premium Offer Card -->
                <div
                    class="group relative mt-8 overflow-hidden rounded-xl border border-emerald-500/20 bg-emerald-50/40 p-6 shadow-sm backdrop-blur-md transition duration-500 hover:border-emerald-500/50 dark:border-emerald-500/30 dark:bg-emerald-950/20"
                >
                    <div
                        class="absolute top-0 right-0 left-0 h-[2px] bg-gradient-to-r from-emerald-500 to-teal-400 opacity-80"
                    ></div>

                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <span
                                class="inline-block rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-0.5 text-[9px] font-extrabold tracking-wider text-emerald-800 uppercase dark:border-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300"
                            >
                                {{ currentText.offerTag }}
                            </span>
                            <h3
                                class="text-lg font-bold text-neutral-900 dark:text-white"
                            >
                                {{ promoTitle }}
                            </h3>
                            <p
                                class="text-xs leading-relaxed text-neutral-600 dark:text-neutral-400"
                            >
                                {{ promoDesc }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Section -->
            <div
                class="relative z-20 mt-auto flex items-center justify-between border-t border-neutral-200/60 pt-6 text-xs text-neutral-500 dark:border-white/5"
            >
                <span>{{ currentText.copyright }}</span>
                <span class="flex items-center gap-1.5">
                    <span
                        class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                    ></span>
                    <span>{{ currentText.status }}</span>
                </span>
            </div>
        </div>

        <!-- Right Login/Form Column -->
        <div
            class="relative flex h-full items-center justify-center overflow-y-auto bg-neutral-50/30 p-8 dark:bg-neutral-950/20"
        >
            <!-- Theme and Language controls on the right side -->
            <div class="absolute top-6 right-6 z-20 flex items-center gap-2">
                <!-- Language Selector -->
                <div class="relative" ref="langDropdownRef">
                    <button
                        @click="langOpen = !langOpen"
                        class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-neutral-200 bg-white/80 px-2.5 py-1.5 text-xs font-semibold text-neutral-700 shadow-xs transition hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900/80 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        <Globe
                            class="h-3.5 w-3.5 text-neutral-500 dark:text-neutral-400"
                        />
                        <span>{{ selectedLang }}</span>
                        <ChevronDown class="h-3 w-3 text-neutral-400" />
                    </button>

                    <div
                        v-if="langOpen"
                        class="absolute right-0 z-30 mt-1.5 w-36 rounded-lg border border-neutral-200 bg-white p-1 shadow-md dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <button
                            v-for="lang in languages"
                            :key="lang"
                            @click="
                                selectedLang = lang;
                                langOpen = false;
                            "
                            class="flex w-full cursor-pointer items-center justify-between rounded-md px-2.5 py-1.5 text-left text-xs text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800"
                        >
                            <span>{{ lang }}</span>
                            <Check
                                v-if="selectedLang === lang"
                                class="h-3 w-3 text-emerald-500"
                            />
                        </button>
                    </div>
                </div>

                <!-- Theme Selector (Segmented Control) -->
                <div
                    class="flex items-center gap-0.5 rounded-lg border border-neutral-200/50 bg-neutral-100 p-1 shadow-xs dark:border-neutral-800/40 dark:bg-neutral-900/60"
                >
                    <button
                        @click="updateAppearance('light')"
                        :class="
                            appearance === 'light'
                                ? 'bg-white text-amber-500 shadow-xs dark:bg-neutral-800'
                                : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-400'
                        "
                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md transition duration-200"
                        title="Light Mode"
                    >
                        <Sun class="h-3.5 w-3.5" />
                    </button>
                    <button
                        @click="updateAppearance('system')"
                        :class="
                            appearance === 'system'
                                ? 'bg-white text-blue-500 shadow-xs dark:bg-neutral-800'
                                : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-400'
                        "
                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md transition duration-200"
                        title="System Mode"
                    >
                        <Monitor class="h-3.5 w-3.5" />
                    </button>
                    <button
                        @click="updateAppearance('dark')"
                        :class="
                            appearance === 'dark'
                                ? 'bg-white text-emerald-500 shadow-xs dark:bg-neutral-800'
                                : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-400'
                        "
                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md transition duration-200"
                        title="Dark Mode"
                    >
                        <Moon class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <div
                class="mx-auto mt-12 flex w-full flex-col justify-center space-y-6 sm:w-[360px] lg:mt-0"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1
                        class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white"
                        v-if="translatedTitle"
                    >
                        {{ translatedTitle }}
                    </h1>
                    <p
                        class="text-sm text-neutral-500 dark:text-neutral-400"
                        v-if="translatedDescription"
                    >
                        {{ translatedDescription }}
                    </p>
                </div>
                <div
                    class="relative overflow-hidden rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm backdrop-blur-md dark:border-neutral-800/40 dark:bg-neutral-900/50"
                >
                    <div
                        class="absolute top-0 right-0 left-0 h-[2px] bg-gradient-to-r from-emerald-500 to-teal-400 opacity-60"
                    ></div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
