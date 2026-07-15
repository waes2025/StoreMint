import type { App } from 'vue';
import * as baseRoutes from '@/routes';

const wrapRoute = (fn: Function, obj: any) => {
    const wrapped = (...args: any[]) => fn(...args);
    return Object.assign(wrapped, fn, obj);
};

const routeRegistry: Record<string, any> = {
    ...baseRoutes,
};

// Dynamically import all route modules under resources/js/routes/
const routeModules = import.meta.glob('../routes/*/index.ts', { eager: true });

for (const path in routeModules) {
    const match = path.match(/\.\.\/routes\/(.+?)\/index\.ts$/);
    if (match) {
        const namespace = match[1]; // e.g. 'blog', 'two-factor', 'dashboard', etc.
        const module: any = routeModules[path];
        const routesObj = module.default || module;
        
        if (namespace in routeRegistry) {
            routeRegistry[namespace] = wrapRoute(routeRegistry[namespace], routesObj);
        } else {
            routeRegistry[namespace] = routesObj;
        }
    }
}

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
