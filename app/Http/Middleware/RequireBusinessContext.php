<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to ensure business and location context is selected before proceeding
 */
class RequireBusinessContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! hasCurrentBusiness() || ! hasCurrentLocation()) {
            return response()->json([
                'error' => 'Business and location context must be selected',
                'available_businesses' => availableBusinesses(),
            ], 422);
        }

        return $next($request);
    }
}
