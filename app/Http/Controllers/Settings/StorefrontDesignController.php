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
     * Default storefront style schema – merged with stored values on read.
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
            'footer_bg_color'   => '#111827',   // neutral-900
            'topbar_bg_color'   => '#064e3b',   // emerald-900
            'topbar_text_color' => '#ffffff',
            'body_bg_color'     => '#f9fafb',
            'text_color'        => '#1f2937',

            // Typography
            'font_family'       => 'Inter',
            'heading_font'      => 'Inter',
            'base_font_size'    => '16',        // px

            // Header & Topbar
            'header_style'      => 'classic',   // classic / modern / minimal / centered
            'show_topbar'       => true,
            'topbar_phone'      => '+1 (800) 555-0199',
            'topbar_email'      => 'support@storemint.com',
            'logo_height'       => '40',        // px

            // Hero section content & style
            'hero_style'            => 'fullwidth', // fullwidth / card_split / gradient_overlay / minimal
            'hero_badge_text'       => 'REDESIGNED PLATFORM',
            'hero_heading'          => 'State-of-the-Art E-Commerce',
            'hero_subheading'       => 'Designed strictly according to modern Design Grid & System Guidelines.',
            'hero_cta_text'         => 'Shop the Collection',
            'hero_cta_secondary'    => 'View Categories',
            'hero_image_url'        => '',
            'show_hero_badge'       => true,

            // Featured Brands section
            'show_brands'       => true,
            'brands_title'      => 'Trusted Brands & Partners',
            'brands_list'       => ['Apple', 'Nike', 'Samsung', 'Sony', 'Adidas', 'Puma'],

            // Footer section
            'footer_style'      => 'multi_column', // multi_column / simple / minimal
            'show_footer'       => true,
            'footer_tagline'    => 'Your one-stop premium e-commerce platform.',
            'footer_copyright'  => '© 2025 StoreMint. All rights reserved.',
            'show_newsletter'   => true,
            'show_social_links' => true,

            // Social links
            'social_facebook'   => 'https://facebook.com',
            'social_instagram'  => 'https://instagram.com',
            'social_twitter'    => 'https://twitter.com',
            'social_youtube'    => 'https://youtube.com',
            'social_tiktok'     => '',
            'social_linkedin'   => 'https://linkedin.com',
            'social_github'     => 'https://github.com',

            // Layout & style
            'layout_width'      => '1280',  // px, max container width
            'border_radius'     => 'xl',    // none / sm / md / lg / xl / 2xl / 3xl / full
            'card_shadow'       => 'sm',    // none / sm / md / lg / xl

            // SEO / meta
            'meta_title'        => 'StoreMint – Premium E-Commerce',
            'meta_description'  => 'Shop the finest products at StoreMint.',
            'og_image_url'      => '',
        ];
    }

    /**
     * Show the storefront design customiser page in Admin Panel.
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
     * Stored in database system table using key 'storefront_style' as array-type data.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $validated = $request->validate([
            // Identity
            'store_name'         => 'nullable|string|max:100',
            'store_tagline'      => 'nullable|string|max:200',
            'store_description'  => 'nullable|string|max:500',
            'logo_url'           => 'nullable|string|max:2000',
            'favicon_url'        => 'nullable|string|max:2000',

            // Colors
            'primary_color'      => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color'       => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'hero_bg_color'      => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'header_bg_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'footer_bg_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'topbar_bg_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'topbar_text_color'  => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'body_bg_color'      => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'text_color'         => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],

            // Typography
            'font_family'        => 'nullable|string|max:100',
            'heading_font'       => 'nullable|string|max:100',
            'base_font_size'     => 'nullable|integer|min:12|max:24',

            // Header & Topbar
            'header_style'       => 'nullable|string|in:classic,modern,minimal,centered',
            'show_topbar'        => 'nullable|boolean',
            'topbar_phone'       => 'nullable|string|max:50',
            'topbar_email'       => 'nullable|email|max:100',
            'logo_height'        => 'nullable|integer|min:20|max:120',

            // Hero
            'hero_style'         => 'nullable|string|in:fullwidth,card_split,gradient_overlay,minimal',
            'hero_badge_text'    => 'nullable|string|max:100',
            'hero_heading'       => 'nullable|string|max:200',
            'hero_subheading'    => 'nullable|string|max:500',
            'hero_cta_text'      => 'nullable|string|max:80',
            'hero_cta_secondary' => 'nullable|string|max:80',
            'hero_image_url'     => 'nullable|string|max:2000',
            'show_hero_badge'    => 'nullable|boolean',

            // Brands
            'show_brands'        => 'nullable|boolean',
            'brands_title'       => 'nullable|string|max:100',
            'brands_list'        => 'nullable|array',
            'brands_list.*'      => 'nullable|string|max:100',

            // Footer
            'footer_style'       => 'nullable|string|in:multi_column,simple,minimal',
            'show_footer'        => 'nullable|boolean',
            'footer_tagline'     => 'nullable|string|max:255',
            'footer_copyright'   => 'nullable|string|max:255',
            'show_newsletter'    => 'nullable|boolean',
            'show_social_links'  => 'nullable|boolean',

            // Social links
            'social_facebook'    => 'nullable|string|max:300',
            'social_instagram'   => 'nullable|string|max:300',
            'social_twitter'     => 'nullable|string|max:300',
            'social_youtube'     => 'nullable|string|max:300',
            'social_tiktok'      => 'nullable|string|max:300',
            'social_linkedin'    => 'nullable|string|max:300',
            'social_github'      => 'nullable|string|max:300',

            // Layout & aesthetics
            'layout_width'       => 'nullable|integer|min:960|max:1920',
            'border_radius'      => 'nullable|string|in:none,sm,md,lg,xl,2xl,3xl,full',
            'card_shadow'        => 'nullable|string|in:none,sm,md,lg,xl',

            // SEO / Meta
            'meta_title'         => 'nullable|string|max:100',
            'meta_description'   => 'nullable|string|max:300',
            'og_image_url'       => 'nullable|string|max:2000',
        ]);

        $businessId = BusinessContextService::getCurrentBusinessId()
            ?: ($request->user()->business_id ?? 1);

        // 1. Primary storage in system table using key 'storefront_style' as array-type data (JSON encoded array)
        DB::table('system')->updateOrInsert(
            ['key' => 'storefront_style'],
            ['value' => json_encode($validated)]
        );

        // 2. Secondary sync in settings table for multi-tenant / business compatibility
        DB::table('settings')->updateOrInsert(
            ['business_id' => $businessId, 'key' => 'storefront_style'],
            ['value' => json_encode($validated), 'updated_at' => now()]
        );

        // 3. Keep storefront_design key updated for backwards compatibility
        DB::table('settings')->updateOrInsert(
            ['business_id' => $businessId, 'key' => 'storefront_design'],
            ['value' => json_encode($validated), 'updated_at' => now()]
        );

        return back()->with('toast', [
            'type'    => 'success',
            'message' => '🎨 Storefront style saved successfully in system table!',
        ]);
    }

    /**
     * Load the stored design from database system table key 'storefront_style', merged with defaults.
     */
    public static function loadDesign(?int $businessId = null): array
    {
        $stored = [];

        // First attempt: Check system table with key 'storefront_style'
        $systemSetting = DB::table('system')
            ->where('key', 'storefront_style')
            ->first();

        if ($systemSetting && ! empty($systemSetting->value)) {
            $val = $systemSetting->value;
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                if (is_array($decoded)) {
                    $stored = $decoded;
                } else {
                    $unserialized = @unserialize($val);
                    if (is_array($unserialized)) {
                        $stored = $unserialized;
                    }
                }
            } elseif (is_array($val)) {
                $stored = $val;
            }
        }

        // Fallback attempt: Check settings table if system table key had no value
        if (empty($stored) && $businessId) {
            $businessSetting = DB::table('settings')
                ->where('business_id', $businessId)
                ->where(function ($query) {
                    $query->where('key', 'storefront_style')
                          ->orWhere('key', 'storefront_design');
                })
                ->first();

            if ($businessSetting && ! empty($businessSetting->value)) {
                $val = $businessSetting->value;
                if (is_string($val)) {
                    $decoded = json_decode($val, true);
                    if (is_array($decoded)) {
                        $stored = $decoded;
                    } else {
                        $unserialized = @unserialize($val);
                        if (is_array($unserialized)) {
                            $stored = $unserialized;
                        }
                    }
                } elseif (is_array($val)) {
                    $stored = $val;
                }
            }
        }

        return array_merge(static::defaults(), $stored);
    }
}
