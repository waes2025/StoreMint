<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Cart\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponsController extends Controller
{
    /**
     * Store a new coupon.
     */
    public function storeCoupon(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $businessId = $request->user()->business_id ?? 1;
        $currencySymbol = DB::table('currencies')
            ->join('business', 'currencies.id', '=', 'business.currency_id')
            ->where('business.id', $businessId)
            ->value('symbol') ?? '$';

        $request->validate([
            'code' => "required|string|max:50|unique:coupons,code,NULL,id,business_id,{$businessId}",
            'discountType' => 'required|in:flat,percentage',
            'discountValue' => 'required|numeric|min:0.01',
            'minOrderAmount' => 'nullable|numeric|min:0',
            'usageLimit' => 'nullable|integer|min:1',
            'expiresAt' => 'nullable|date|after_or_equal:today',
            'status' => 'required|in:active,inactive',
        ]);

        Coupon::create([
            'business_id' => $businessId,
            'code' => strtoupper($request->input('code')),
            'description' => $request->input('discountType') === 'percentage'
                ? "{$request->input('discountValue')}% off storewide!"
                : "Flat {$currencySymbol}{$request->input('discountValue')} off!",
            'discount_type' => $request->input('discountType'),
            'discount_value' => $request->input('discountValue'),
            'min_order_amount' => $request->input('minOrderAmount') ?? 0.00,
            'usage_limit' => $request->input('usageLimit'),
            'expires_at' => $request->input('expiresAt'),
            'status' => $request->input('status'),
            'created_by' => $request->user()->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => '🎉 Coupon created successfully!',
        ]);
    }

    /**
     * Toggle coupon status.
     */
    public function toggleCoupon(Request $request, $currentTeam, Coupon $coupon): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $newStatus = $coupon->status === 'active' ? 'inactive' : 'active';
        $coupon->update(['status' => $newStatus]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "🏷️ Coupon {$coupon->code} is now {$newStatus}!",
        ]);
    }

    /**
     * Soft delete coupon.
     */
    public function destroyCoupon(Request $request, $currentTeam, Coupon $coupon): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $coupon->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => '🗑️ Coupon deleted.',
        ]);
    }

    /**
     * Update an existing coupon.
     */
    public function updateCoupon(Request $request, $currentTeam, Coupon $coupon): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $businessId = $request->user()->business_id ?? 1;
        $currencySymbol = DB::table('currencies')
            ->join('business', 'currencies.id', '=', 'business.currency_id')
            ->where('business.id', $businessId)
            ->value('symbol') ?? '$';

        $request->validate([
            'code' => "required|string|max:50|unique:coupons,code,{$coupon->id},id,business_id,{$businessId}",
            'discountType' => 'required|in:flat,percentage',
            'discountValue' => 'required|numeric|min:0.01',
            'minOrderAmount' => 'nullable|numeric|min:0',
            'usageLimit' => 'nullable|integer|min:1',
            'expiresAt' => 'nullable|date|after_or_equal:today',
            'status' => 'required|in:active,inactive',
        ]);

        $coupon->update([
            'code' => strtoupper($request->input('code')),
            'description' => $request->input('discountType') === 'percentage'
                ? "{$request->input('discountValue')}% off storewide!"
                : "Flat {$currencySymbol}{$request->input('discountValue')} off!",
            'discount_type' => $request->input('discountType'),
            'discount_value' => $request->input('discountValue'),
            'min_order_amount' => $request->input('minOrderAmount') ?? 0.00,
            'usage_limit' => $request->input('usageLimit'),
            'expires_at' => $request->input('expiresAt'),
            'status' => $request->input('status'),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => '🏷️ Coupon updated successfully!',
        ]);
    }
}
