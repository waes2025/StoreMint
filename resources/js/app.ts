import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { route, WayfinderRoutePlugin } from '@/lib/route';

window.route = route;

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Eager load all core and module page components
const pages = import.meta.glob('./pages/**/*.vue', { eager: true });
const modulePages = import.meta.glob('../../Modules/*/resources/assets/js/pages/**/*.vue', { eager: true });

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        let page: any;
        
        if (name.includes('::')) {
            const [moduleName, pagePath] = name.split('::');
            const targetKey = `../../Modules/${moduleName}/resources/assets/js/pages/${pagePath}.vue`;
            page = modulePages[targetKey];
            if (!page) {
                throw new Error(`Inertia page not found: ${targetKey}`);
            }
        } else {
            const targetKey = `./pages/${name}.vue`;
            page = pages[targetKey];
            if (!page) {
                throw new Error(`Inertia page not found: ${targetKey}`);
            }
        }
        
        return page.default || page;
    },
    layout: (name) => {
        const isSettings = name.startsWith('settings/') || name.includes('::settings/');
        const isTeams = name.startsWith('teams/') || name.includes('::teams/');
        const isAuth = name.startsWith('auth/') || name.includes('::auth/');
        const isWelcomeOrShop = name === 'Welcome' || name === 'Shop' || name === 'Shop::Welcome' || name === 'Shop::Shop' || name === 'Blog::Index' || name === 'Blog::Show';

        if (isWelcomeOrShop) {
            return null;
        }
        if (isAuth) {
            return AuthLayout;
        }
        if (isSettings || isTeams) {
            return [AppLayout, SettingsLayout];
        }
        return AppLayout;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(WayfinderRoutePlugin);
        if (el) {
            app.mount(el);
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
