<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $businessId = currentBusinessId() ?: ($request->user()?->business_id ?? config('ecommerce.business_id', 1));
        
        $business = Business::find($businessId);
        $enabled = $business ? ($business->enabled_modules ?? []) : [];

        if (! in_array($module, $enabled)) {
            abort(403, "The {$module} module is not enabled for this business.");
        }

        return $next($request);
    }
}
