<?php

namespace Tests\Feature\Settings;

use App\Http\Controllers\Settings\StorefrontDesignController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontDesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic currency & business for testing environment
        $currencyId = DB::table('currencies')->insertGetId([
            'country' => 'United States',
            'currency' => 'USD',
            'code' => 'USD',
            'symbol' => '$',
            'thousand_separator' => ',',
            'decimal_separator' => '.',
        ]);

        DB::table('business')->insert([
            'id' => 1,
            'name' => 'StoreMint Demo',
            'currency_id' => $currencyId,
            'start_date' => '2025-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_access_storefront_design_page(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'business_id' => 1,
        ]);

        $response = $this->actingAs($admin)->get('/settings/storefront-design');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_storefront_design_page(): void
    {
        $response = $this->get('/settings/storefront-design');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_update_storefront_style_in_system_table(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'business_id' => 1,
        ]);

        $payload = [
            'store_name'        => 'Customized StoreMint',
            'store_tagline'     => 'Your Ultimate Luxury Destination',
            'primary_color'     => '#3b82f6',
            'secondary_color'   => '#1d4ed8',
            'accent_color'      => '#f59e0b',
            'hero_heading'      => 'Experience Next-Gen Shopping',
            'hero_subheading'   => 'Curated collections designed for your lifestyle.',
            'hero_badge_text'   => 'NEW SUMMER COLLECTION',
            'brands_title'      => 'Featured Luxury Partners',
            'brands_list'       => ['Apple', 'Rolex', 'Gucci', 'Prada'],
            'footer_copyright'  => '© 2026 Customized StoreMint Inc.',
            'social_instagram'  => 'https://instagram.com/customstoremint',
        ];

        $response = $this->actingAs($admin)
            ->patch('/settings/storefront-design', $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 1. Verify system table has key 'storefront_style'
        $systemSetting = DB::table('system')->where('key', 'storefront_style')->first();
        $this->assertNotNull($systemSetting);

        $decodedValue = json_decode($systemSetting->value, true);
        $this->assertIsArray($decodedValue);
        $this->assertEquals('Customized StoreMint', $decodedValue['store_name']);
        $this->assertEquals('#3b82f6', $decodedValue['primary_color']);
        $this->assertEquals(['Apple', 'Rolex', 'Gucci', 'Prada'], $decodedValue['brands_list']);

        // 2. Verify StorefrontDesignController::loadDesign() returns saved values
        $loadedDesign = StorefrontDesignController::loadDesign(1);
        $this->assertEquals('Customized StoreMint', $loadedDesign['store_name']);
        $this->assertEquals('#3b82f6', $loadedDesign['primary_color']);
        $this->assertEquals('NEW SUMMER COLLECTION', $loadedDesign['hero_badge_text']);
    }
}
