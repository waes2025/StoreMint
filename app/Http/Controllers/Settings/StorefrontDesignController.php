<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\BusinessContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontDesignController extends Controller
{
    /**
     * Default design schema – merged with stored values on read.
     */
    public static function defaults(): array
    {
        return [
            // Brand identity
            'store_name'        => 'StoreMint',
            'store_tagline'     => 'E-Commerce Redefined',
            'store_description' => 'Discover premium lifestyle goods, curated for you.',
            'logo_url'          => '',
            'favicon_url'       => '',

            // Color palette
            'primary_color'     => '#10b981',   // emerald-500
            'secondary_color'   => '#0d9488',   // teal-600
            'accent_color'      => '#f59e0b',   // amber-500
            'hero_bg_color'     => '#171717',   // neutral-900
            'header_bg_color'   => '#ffffff',
            'footer_bg_color'   => '#111827',

            // Typography
            'font_family'       => 'Inter',
            'heading_font'      => 'Inter',
            'base_font_size'    => '16',        // px

            // Hero content
            'hero_badge_text'       => 'REDESIGNED PLATFORM',
            'hero_heading'          => 'State-of-the-Art E-Commerce',
            'hero_subheading'       => 'Designed strictly according to modern Design Grid & System Guidelines.',
            'hero_cta_text'         => 'Shop the Collection',
            'hero_cta_secondary'    => 'View Categories',
            'hero_image_url'        => '',
            'show_hero_badge'       => true,

            // Layout & style
            'layout_width'          => '1280',  // px, max container width
            'border_radius'         => 'xl',    // none / sm / md / lg / xl / 2xl / 3xl / full
            'card_shadow'           => 'sm',    // none / sm / md / lg / xl
            'show_topbar'           => true,
            'show_footer'           => true,
            'topbar_bg_color'       => '#064e3b',
            'topbar_text_color'     => '#ffffff',
            'topbar_phone'          => '+1 (800) 555-0199',
            'topbar_email'          => 'support@storemint.com',

            // Social links
            'social_facebook'   => '',
            'social_instagram'  => '',
            'social_twitter'    => '',
            'social_youtube'    => '',
            'social_tiktok'     => '',

            // Footer
            'footer_tagline'    => 'Your one-stop premium e-commerce platform.',
            'footer_copyright'  => '© 2025 StoreMint. All rights reserved.',

            // SEO / meta
            'meta_title'        => 'StoreMint – Premium E-Commerce',
            'meta_description'  => 'Shop the finest products at StoreMint.',
            'og_image_url'      => '',
        ];
    }

    /**
     * Show the storefront design customiser page.
     */
    public function edit(Request $request): Response
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $businessId = BusinessContextService::getCurrentBusinessId()
            ?: ($request->user()->business_id ?? 1);

        $design = $this->loadDesign($businessId);

        return Inertia::render('settings/StorefrontDesign', [
            'design' => $design,
        ]);
    }

    /**
     * Save the storefront design settings.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'store_name'         => 'nullable|string|max:100',
            'store_tagline'      => 'nullable|string|max:200',
            'store_description'  => 'nullable|string|max:500',
            'logo_url'           => 'nullable|string|max:2000',
            'favicon_url'        => 'nullable|string|max:2000',

            'primary_color'      => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color'       => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'hero_bg_color'      => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'header_bg_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'footer_bg_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],

            'font_family'        => 'nullable|string|max:100',
            'heading_font'       => 'nullable|string|max:100',
            'base_font_size'     => 'nullable|integer|min:12|max:24',

            'hero_badge_text'       => 'nullable|string|max:100',
            'hero_heading'          => 'nullable|string|max:200',
            'hero_subheading'       => 'nullable|string|max:500',
            'hero_cta_text'         => 'nullable|string|max:80',
            'hero_cta_secondary'    => 'nullable|string|max:80',
            'hero_image_url'        => 'nullable|string|max:2000',
            'show_hero_badge'       => 'nullable|boolean',

            'layout_width'       => 'nullable|integer|min:960|max:1920',
            'border_radius'      => 'nullable|string|in:none,sm,md,lg,xl,2xl,3xl,full',
            'card_shadow'        => 'nullable|string|in:none,sm,md,lg,xl',
            'show_topbar'        => 'nullable|boolean',
            'show_footer'        => 'nullable|boolean',
            'topbar_bg_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'topbar_text_color'  => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'topbar_phone'       => 'nullable|string|max:30',
            'topbar_email'       => 'nullable|email|max:100',

            'social_facebook'    => 'nullable|string|max:300',
            'social_instagram'   => 'nullable|string|max:300',
            'social_twitter'     => 'nullable|string|max:300',
            'social_youtube'     => 'nullable|string|max:300',
            'social_tiktok'      => 'nullable|string|max:300',

            'footer_tagline'     => 'nullable|string|max:255',
            'footer_copyright'   => 'nullable|string|max:255',

            'meta_title'         => 'nullable|string|max:100',
            'meta_description'   => 'nullable|string|max:300',
            'og_image_url'       => 'nullable|string|max:2000',
        ]);

        $businessId = BusinessContextService::getCurrentBusinessId()
            ?: ($request->user()->business_id ?? 1);

        DB::table('settings')->updateOrInsert(
            ['business_id' => $businessId, 'key' => 'storefront_design'],
            ['value' => json_encode($validated), 'updated_at' => now()]
        );

        return back()->with('toast', [
            'type'    => 'success',
            'message' => '🎨 Storefront design saved successfully!',
        ]);
    }

    /**
     * Load the stored design, merged with defaults.
     */
    public static function loadDesign(int $businessId): array
    {
        $setting = DB::table('settings')
            ->where('business_id', $businessId)
            ->where('key', 'storefront_design')
            ->first();

        $stored = [];
        if ($setting) {
            $stored = json_decode($setting->value, true) ?? [];
        }

        return array_merge(static::defaults(), $stored);
    }
}
