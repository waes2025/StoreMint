import type { App } from 'vue';
import * as baseRoutes from '@/routes';
import appearance from '@/routes/appearance';
import gateways from '@/routes/gateways';
import invitations from '@/routes/invitations';
import login from '@/routes/login';
import password from '@/routes/password';
import profile from '@/routes/profile';
import register from '@/routes/register';
import security from '@/routes/security';
import storage from '@/routes/storage';
import teams from '@/routes/teams';
import twoFactor from '@/routes/two-factor';
import userPassword from '@/routes/user-password';

const wrapRoute = (fn: Function, obj: any) => {
    const wrapped = (...args: any[]) => fn(...args);
    return Object.assign(wrapped, fn, obj);
};

const routeRegistry: Record<string, any> = {
    ...baseRoutes,
    appearance,
    gateways,
    invitations,
    login: wrapRoute(baseRoutes.login, login),
    password,
    profile,
    register: wrapRoute(baseRoutes.register, register),
    security,
    storage,
    teams,
    'two-factor': twoFactor,
    'user-password': userPassword,
};

export interface RouteResult {
    url: string;
    method: string;
    toString(): string;
}

/**
 * Common Vue route helper that works with Wayfinder routes.
 * Supports dot-notation, e.g. route('teams.members.update', args, options)
 */
export function route(name: string, ...args: any[]): any {
    const parts = name.split('.');
    let current: any = routeRegistry;

    for (const part of parts) {
        if (current === undefined || current === null) {
            throw new Error(`Route "${name}" not found (failed at part "${part}").`);
        }
        if (part in current) {
            current = current[part];
        } else {
            const camelCased = part.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
            if (camelCased in current) {
                current = current[camelCased];
            } else {
                throw new Error(`Route "${name}" not found (failed at part "${part}").`);
            }
        }
    }

    if (typeof current !== 'function') {
        throw new Error(`Route "${name}" is not a valid route function.`);
    }

    const result = current(...args);

    if (result && typeof result === 'object' && 'url' in result && 'method' in result) {
        return {
            url: result.url,
            method: result.method,
            toString() {
                return result.url;
            },
        };
    }

    return result;
}

export const WayfinderRoutePlugin = {
    install(app: App) {
        app.config.globalProperties.route = route;
    },
};
