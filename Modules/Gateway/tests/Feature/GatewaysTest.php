<?php

namespace Modules\Gateway\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GatewaysTest extends TestCase
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

        // Create a default business record with Gateway module enabled
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Test Business',
            'currency_id' => $currencyId,
            'owner_id' => $userId,
            'enabled_modules' => json_encode(['Gateway']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_gateways_page_cannot_be_accessed_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->get(route('gateways.edit'));

        $response->assertStatus(403);
    }

    public function test_gateways_page_can_be_accessed_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->get(route('gateways.edit'));

        $response->assertOk();
    }

    public function test_gateways_cannot_be_accessed_when_module_disabled()
    {
        // Disable Gateway module
        DB::table('business')->where('id', 1)->update([
            'enabled_modules' => json_encode([]),
        ]);

        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->get(route('gateways.edit'));

        $response->assertStatus(403);
    }

    public function test_gateways_can_be_updated_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->from(route('gateways.edit'))
            ->patch(route('gateways.update'), [
                'storage_format' => 'json',
                'stripe' => [
                    'enabled' => true,
                    'publishable_key' => 'pk_test_123',
                    'secret_key' => 'sk_test_456',
                ],
                'sslcommerz' => [
                    'enabled' => false,
                    'store_id' => '',
                    'store_password' => '',
                    'merchant_id' => '',
                    'mode' => 'live',
                ],
                'cod' => [
                    'enabled' => true,
                ],
            ]);

        $response->assertRedirect(route('gateways.edit'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'business_id' => 1,
            'key' => 'payment_gateways',
        ]);

        $setting = DB::table('settings')->where('business_id', 1)->where('key', 'payment_gateways')->first();
        $gateways = json_decode($setting->value, true);

        $this->assertTrue($gateways['stripe']['enabled']);
        $this->assertSame('pk_test_123', $gateways['stripe']['publishable_key']);
        $this->assertSame('sk_test_456', $gateways['stripe']['secret_key']);
        $this->assertFalse($gateways['sslcommerz']['enabled']);
        $this->assertTrue($gateways['cod']['enabled']);
    }

    public function test_gateways_can_be_updated_by_admin_using_serialize()
    {
        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->from(route('gateways.edit'))
            ->patch(route('gateways.update'), [
                'storage_format' => 'serialize',
                'stripe' => [
                    'enabled' => true,
                    'publishable_key' => 'pk_test_123',
                    'secret_key' => 'sk_test_456',
                ],
                'sslcommerz' => [
                    'enabled' => false,
                    'store_id' => '',
                    'store_password' => '',
                    'merchant_id' => '',
                    'mode' => 'live',
                ],
                'cod' => [
                    'enabled' => true,
                ],
            ]);

        $response->assertRedirect(route('gateways.edit'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'business_id' => 1,
            'key' => 'payment_gateways',
        ]);

        $setting = DB::table('settings')->where('business_id', 1)->where('key', 'payment_gateways')->first();
        $gateways = unserialize($setting->value);

        $this->assertTrue($gateways['stripe']['enabled']);
        $this->assertSame('pk_test_123', $gateways['stripe']['publishable_key']);
        $this->assertSame('sk_test_456', $gateways['stripe']['secret_key']);
        $this->assertFalse($gateways['sslcommerz']['enabled']);
        $this->assertTrue($gateways['cod']['enabled']);
    }

    public function test_gateways_validation_fails_when_enabled_without_keys()
    {
        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->from(route('gateways.edit'))
            ->patch(route('gateways.update'), [
                'storage_format' => 'json',
                'stripe' => [
                    'enabled' => true,
                    'publishable_key' => '',
                    'secret_key' => '',
                ],
                'sslcommerz' => [
                    'enabled' => false,
                    'store_id' => '',
                    'store_password' => '',
                    'merchant_id' => '',
                    'mode' => 'live',
                ],
                'cod' => [
                    'enabled' => true,
                ],
            ]);

        $response->assertRedirect(route('gateways.edit'));
        $response->assertSessionHasErrors([
            'stripe.publishable_key',
            'stripe.secret_key',
        ]);
    }

    public function test_gateways_validation_fails_when_sslcommerz_enabled_without_keys()
    {
        $user = User::factory()->create(['user_type' => 'admin', 'business_id' => 1]);

        $response = $this
            ->actingAs($user)
            ->from(route('gateways.edit'))
            ->patch(route('gateways.update'), [
                'storage_format' => 'json',
                'stripe' => [
                    'enabled' => false,
                    'publishable_key' => '',
                    'secret_key' => '',
                ],
                'sslcommerz' => [
                    'enabled' => true,
                    'store_id' => '',
                    'store_password' => '',
                    'merchant_id' => '',
                    'mode' => '',
                ],
                'cod' => [
                    'enabled' => true,
                ],
            ]);

        $response->assertRedirect(route('gateways.edit'));
        $response->assertSessionHasErrors([
            'sslcommerz.store_id',
            'sslcommerz.store_password',
            'sslcommerz.mode',
        ]);
    }
}
