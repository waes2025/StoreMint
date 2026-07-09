<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GatewaysTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateways_page_cannot_be_accessed_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this
            ->actingAs($user)
            ->get(route('gateways.edit'));

        $response->assertStatus(403);
    }

    public function test_gateways_page_can_be_accessed_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->get(route('gateways.edit'));

        $response->assertOk();
    }

    public function test_gateways_can_be_updated_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

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
                ],
                'cod' => [
                    'enabled' => true,
                ],
            ]);

        $response->assertRedirect(route('gateways.edit'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('system', [
            'key' => 'payment_gateways',
        ]);

        $systemSetting = DB::table('system')->where('key', 'payment_gateways')->first();
        $gateways = json_decode($systemSetting->value, true);

        $this->assertTrue($gateways['stripe']['enabled']);
        $this->assertSame('pk_test_123', $gateways['stripe']['publishable_key']);
        $this->assertSame('sk_test_456', $gateways['stripe']['secret_key']);
        $this->assertFalse($gateways['sslcommerz']['enabled']);
        $this->assertTrue($gateways['cod']['enabled']);
    }

    public function test_gateways_can_be_updated_by_admin_using_serialize()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

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
                ],
                'cod' => [
                    'enabled' => true,
                ],
            ]);

        $response->assertRedirect(route('gateways.edit'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('system', [
            'key' => 'payment_gateways',
        ]);

        $systemSetting = DB::table('system')->where('key', 'payment_gateways')->first();
        $gateways = unserialize($systemSetting->value);

        $this->assertTrue($gateways['stripe']['enabled']);
        $this->assertSame('pk_test_123', $gateways['stripe']['publishable_key']);
        $this->assertSame('sk_test_456', $gateways['stripe']['secret_key']);
        $this->assertFalse($gateways['sslcommerz']['enabled']);
        $this->assertTrue($gateways['cod']['enabled']);
    }

    public function test_gateways_validation_fails_when_enabled_without_keys()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

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
}
