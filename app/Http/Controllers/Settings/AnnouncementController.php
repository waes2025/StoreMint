<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\BusinessContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /**
     * Show the announcement settings form.
     */
    public function edit(Request $request): Response
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        $business = \App\Models\Business::find($businessId);
        $enabledModules = $business ? ($business->enabled_modules ?? []) : [];
        abort_if(!in_array('Cart', $enabledModules), 403);
        $announcementSetting = DB::table('settings')
            ->where('business_id', $businessId)
            ->where('key', 'announcement_bar')
            ->first();
        $announcement = null;
        if ($announcementSetting) {
            $announcement = json_decode($announcementSetting->value, true);
        }

        if (! $announcement) {
            $announcement = [
                'enabled' => true,
                'text' => '✨ GRAND OPENING OFFER: USE COUPON {coupon} FOR 50% OFF ALL PRODUCTS!',
                'coupon' => 'MINT50',
                'bg_color' => '#059669',
                'text_color' => '#ffffff',
            ];
        }

        return Inertia::render('Cart::settings/Announcement', [
            'announcement' => $announcement,
        ]);
    }

    /**
     * Update the announcement bar settings.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        $business = \App\Models\Business::find($businessId);
        $enabledModules = $business ? ($business->enabled_modules ?? []) : [];
        abort_if(!in_array('Cart', $enabledModules), 403);

        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'text' => 'required|string|max:255',
            'coupon' => 'nullable|string|max:50',
            'bg_color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'text_color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);


        DB::table('settings')->updateOrInsert(
            ['business_id' => $businessId, 'key' => 'announcement_bar'],
            ['value' => json_encode($validated), 'updated_at' => now()]
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => '✨ Announcement bar settings updated successfully!',
        ]);
    }
}
