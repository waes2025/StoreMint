<?php

namespace Modules\Shipment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $currencyId = DB::table('currencies')->insertGetId([
            'country' => 'United States',
            'currency' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'first_name' => 'Owner',
            'last_name' => 'User',
            'username' => 'owner',
            'email' => 'owner@example.com',
            'password' => '$2y$12$Z.jQn1Lw.bW8Lw7N/Vw9/.DlhE6Tj7CszYQG2sKq4lJ3hB0mJzB4y', // 'password'
            'user_type' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a default business record with Shipment module enabled
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Test Business',
            'currency_id' => $currencyId,
            'owner_id' => $userId,
            'enabled_modules' => json_encode(['Shipment']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_shipping_page_cannot_be_accessed_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->get(route('shipping.edit'));

        $response->assertStatus(403);
    }

    public function test_shipping_page_can_be_accessed_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->get(route('shipping.edit'));

        $response->assertOk();
    }

    public function test_shipping_cannot_be_accessed_when_module_disabled()
    {
        // Disable Shipment module
        DB::table('business')->where('id', 1)->update([
            'enabled_modules' => json_encode([]),
        ]);

        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->get(route('shipping.edit'));

        $response->assertStatus(403);
    }

    public function test_shipping_settings_can_be_updated_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->from(route('shipping.edit'))
            ->patch(route('shipping.update'), [
                'free_shipping_enabled' => true,
                'free_shipping_threshold' => 150.00,
                'flat_rate_enabled' => true,
                'flat_rate_amount' => 12.50,
                'local_pickup_enabled' => true,
                'local_pickup_label' => 'Warehouse Pickup',
                'default_courier' => 'DHL Express',
                'estimated_delivery_days' => 5,
                'tracking_base_url' => 'https://www.dhl.com/track?id=',
                'zones' => [
                    ['name' => 'Domestic Zone', 'rate' => 10.00, 'enabled' => true],
                ],
            ]);

        $response->assertRedirect(route('shipping.edit'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'business_id' => 1,
            'key' => 'shipping_settings',
        ]);

        $setting = DB::table('settings')->where('business_id', 1)->where('key', 'shipping_settings')->first();
        $shipping = json_decode($setting->value, true);

        $this->assertTrue($shipping['free_shipping_enabled']);
        $this->assertEquals(150.0, $shipping['free_shipping_threshold']);
        $this->assertTrue($shipping['flat_rate_enabled']);
        $this->assertEquals(12.5, $shipping['flat_rate_amount']);
        $this->assertTrue($shipping['local_pickup_enabled']);
        $this->assertSame('Warehouse Pickup', $shipping['local_pickup_label']);
        $this->assertSame('DHL Express', $shipping['default_courier']);
        $this->assertSame(5, $shipping['estimated_delivery_days']);
        $this->assertSame('https://www.dhl.com/track?id=', $shipping['tracking_base_url']);
        $this->assertCount(1, $shipping['zones']);
        $this->assertSame('Domestic Zone', $shipping['zones'][0]['name']);
        $this->assertEquals(10.0, $shipping['zones'][0]['rate']);
        $this->assertTrue($shipping['zones'][0]['enabled']);
    }

    public function test_shipping_validation_fails_with_invalid_data()
    {
        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->from(route('shipping.edit'))
            ->patch(route('shipping.update'), [
                'free_shipping_enabled' => 'not-a-bool',
                'free_shipping_threshold' => -10, // Must be positive
                'flat_rate_enabled' => true,
                'flat_rate_amount' => 'invalid-amount',
                'local_pickup_enabled' => false,
                'estimated_delivery_days' => 500, // Exceeds max 365
                'tracking_base_url' => 'not-a-url',
            ]);

        $response->assertRedirect(route('shipping.edit'));
        $response->assertSessionHasErrors([
            'free_shipping_enabled',
            'free_shipping_threshold',
            'flat_rate_amount',
            'estimated_delivery_days',
            'tracking_base_url',
        ]);
    }
}
