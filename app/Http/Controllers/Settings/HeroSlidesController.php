<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class HeroSlidesController extends Controller
{
    /**
     * Show the hero slides editor.
     */
    public function edit(Request $request)
    {
        $setting = \Illuminate\Support\Facades\DB::table('system')->where('key', 'hero_slides')->first();
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

        \Illuminate\Support\Facades\DB::table('system')
            ->updateOrInsert(
                ['key' => 'hero_slides'],
                ['value' => json_encode($slides)]
            );

        return redirect()->back()->with('success', 'Hero slides updated.');
    }
}
