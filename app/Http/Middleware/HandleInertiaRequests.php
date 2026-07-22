<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $businessId = currentBusinessId()
            ?: $request->input('business_id')
            ?: session('storefront_business_id')
            ?: ($user?->business_id ?? config('ecommerce.business_id', 1));

        $currencySymbol = DB::table('currencies')
            ->join('business', 'currencies.id', '=', 'business.currency_id')
            ->where('business.id', $businessId)
            ->value('symbol') ?? '$';

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'currency_symbol' => $currencySymbol,
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
            'enabled_modules' => function () use ($businessId) {
                $business = Business::find($businessId);

                return $business ? ($business->enabled_modules ?? []) : [];
            },
            'module_menus' => function () use ($businessId) {
                $business = Business::find($businessId);
                $enabled = $business ? ($business->enabled_modules ?? []) : [];
                $menus = [];
                foreach ($enabled as $moduleName) {
                    $module = \Nwidart\Modules\Facades\Module::find($moduleName);
                    if ($module) {
                        $lowerName = strtolower($moduleName);
                        $moduleMenus = config("{$lowerName}.menus");
                        if (is_array($moduleMenus)) {
                            foreach ($moduleMenus as $menu) {
                                try {
                                    $menu['href'] = route($menu['route']);
                                } catch (\Exception $e) {
                                    $menu['href'] = '#';
                                }
                                $menus[] = $menu;
                            }
                        }
                    }
                }
                usort($menus, fn($a, $b) => ($a['order'] ?? 100) <=> ($b['order'] ?? 100));
                return $menus;
            },
            'promo_coupon' => function () use ($businessId) {
                $coupon = DB::table('coupons')
                    ->where('business_id', $businessId)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('starts_at')
                            ->orWhere('starts_at', '<=', now());
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                return $coupon ? [
                    'code' => $coupon->code,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => (float) $coupon->discount_value,
                    'description' => $coupon->description,
                ] : null;
            },
        ];
    }
}
