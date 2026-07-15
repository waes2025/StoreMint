<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\BusinessContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HeroSlidesController extends Controller
{
    /**
     * Show the hero slides editor.
     */
    public function edit(Request $request)
    {
        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        $setting = DB::table('settings')
            ->where('business_id', $businessId)
            ->where('key', 'hero_slides')
            ->first();
        $slides = [];
        if ($setting) {
            $slides = json_decode($setting->value, true) ?: [];
        }

        return Inertia::render('settings/HeroSlides', [
            'heroSlides' => $slides,
        ]);
    }

    /**
     * Update hero slides.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'slides' => 'required|array',
            'slides.*.title' => 'nullable|string|max:255',
            'slides.*.subtitle' => 'nullable|string|max:1000',
            'slides.*.image' => 'nullable|string|max:2000',
            'slides.*.link' => 'nullable|url|max:2000',
            'slides.*.is_active' => 'nullable|boolean',
        ]);

        $slides = $data['slides'];

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        DB::table('settings')
            ->updateOrInsert(
                ['business_id' => $businessId, 'key' => 'hero_slides'],
                ['value' => json_encode($slides), 'updated_at' => now()]
            );

        return redirect()->back()->with('success', 'Hero slides updated.');
    }
}
