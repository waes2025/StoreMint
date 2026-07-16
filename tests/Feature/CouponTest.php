<?php

namespace Tests\Feature;

use Modules\Cart\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $currencyId = \Illuminate\Support\Facades\DB::table('currencies')->insertGetId([
            'country' => 'United States',
            'currency' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
            'first_name' => 'Owner',
            'last_name' => 'User',
            'username' => 'owner_coupon',
            'email' => 'owner_coupon@example.com',
            'password' => '$2y$12$Z.jQn1Lw.bW8Lw7N/Vw9/.DlhE6Tj7CszYQG2sKq4lJ3hB0mJzB4y',
            'user_type' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('business')->insert([
            'id' => config('ecommerce.business_id', 1),
            'name' => 'Test Business',
            'currency_id' => $currencyId,
            'owner_id' => $userId,
            'enabled_modules' => json_encode(['Cart']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('business_locations')->insert([
            'id' => config('ecommerce.location_id', 1),
            'business_id' => config('ecommerce.business_id', 1),
            'name' => 'Test Location',
            'country' => 'United States',
            'state' => 'New York',
            'city' => 'New York',
            'zip_code' => '10001',
            'landmark' => 'Landmark',
            'mobile' => '1234567890',
            'invoice_scheme_id' => 1,
            'invoice_layout_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_create_coupon()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        $team = $user->currentTeam;

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.coupons.store', ['current_team' => $team->slug]), [
                'code' => 'TESTCOUPON',
                'discountType' => 'percentage',
                'discountValue' => 15,
                'minOrderAmount' => 50,
                'usageLimit' => 10,
                'status' => 'active',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('coupons', [
            'code' => 'TESTCOUPON',
            'discount_value' => 15.00,
        ]);
    }

    public function test_admin_can_update_coupon()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        $team = $user->currentTeam;

        $coupon = Coupon::create([
            'business_id' => $user->business_id ?? config('ecommerce.business_id', 1),
            'code' => 'OLDCODE',
            'discount_type' => 'flat',
            'discount_value' => 10,
            'min_order_amount' => 0,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('dashboard.coupons.update', ['current_team' => $team->slug, 'coupon' => $coupon->id]), [
                'code' => 'NEWCODE',
                'discountType' => 'percentage',
                'discountValue' => 20,
                'minOrderAmount' => 10,
                'usageLimit' => 5,
                'status' => 'inactive',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'NEWCODE',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_toggle_coupon()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        $team = $user->currentTeam;

        $coupon = Coupon::create([
            'business_id' => $user->business_id ?? config('ecommerce.business_id', 1),
            'code' => 'TOGGLEME',
            'discount_type' => 'flat',
            'discount_value' => 10,
            'min_order_amount' => 0,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.coupons.toggle', ['current_team' => $team->slug, 'coupon' => $coupon->id]));

        $response->assertRedirect();
        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_coupon()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        $team = $user->currentTeam;

        $coupon = Coupon::create([
            'business_id' => $user->business_id ?? config('ecommerce.business_id', 1),
            'code' => 'DELETEME',
            'discount_type' => 'flat',
            'discount_value' => 10,
            'min_order_amount' => 0,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('dashboard.coupons.destroy', ['current_team' => $team->slug, 'coupon' => $coupon->id]));

        $response->assertRedirect();
        $this->assertSoftDeleted('coupons', [
            'id' => $coupon->id,
        ]);
    }

    public function test_admin_can_create_coupon_with_usage_limit_per_user()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        $team = $user->currentTeam;

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.coupons.store', ['current_team' => $team->slug]), [
                'code' => 'PERUSER1',
                'discountType' => 'percentage',
                'discountValue' => 10,
                'minOrderAmount' => 0,
                'usageLimit' => 50,
                'usageLimitPerUser' => 2,
                'status' => 'active',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('coupons', [
            'code' => 'PERUSER1',
            'usage_limit_per_user' => 2,
        ]);
    }

    public function test_admin_can_update_coupon_with_usage_limit_per_user()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        $team = $user->currentTeam;

        $coupon = Coupon::create([
            'business_id' => $user->business_id ?? config('ecommerce.business_id', 1),
            'code' => 'OLDCODE2',
            'discount_type' => 'flat',
            'discount_value' => 10,
            'min_order_amount' => 0,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('dashboard.coupons.update', ['current_team' => $team->slug, 'coupon' => $coupon->id]), [
                'code' => 'NEWCODE2',
                'discountType' => 'flat',
                'discountValue' => 10,
                'minOrderAmount' => 0,
                'usageLimit' => 50,
                'usageLimitPerUser' => 3,
                'status' => 'active',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'NEWCODE2',
            'usage_limit_per_user' => 3,
        ]);
    }

    public function test_checkout_coupon_usage_limits()
    {
        // 1. Setup Customer User and Coupon
        $user = User::factory()->create(['user_type' => 'customer']);
        $coupon = Coupon::create([
            'business_id' => config('ecommerce.business_id', 1),
            'code' => 'TESTLIMIT',
            'discount_type' => 'flat',
            'discount_value' => 5.00,
            'min_order_amount' => 10.00,
            'usage_limit' => 10,
            'usage_limit_per_user' => 1,
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Place a first order as the customer with the coupon code
        $payload = [
            'customer' => [
                'email' => $user->email,
                'name' => 'John Doe',
                'phone' => '1234567890',
                'paymentMethod' => 'cod',
            ],
            'items' => [],
            'subtotal' => 20.00,
            'discount' => 5.00,
            'couponCode' => 'TESTLIMIT',
            'shipping' => 0.00,
            'grandTotal' => 15.00,
        ];

        $response = $this
            ->actingAs($user)
            ->postJson(route('checkout.place'), $payload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Place a second order as the same customer with the same coupon code -> should fail limit per customer check
        $response2 = $this
            ->actingAs($user)
            ->postJson(route('checkout.place'), $payload);

        $response2->assertOk();
        $response2->assertJson([
            'success' => false,
            'message' => 'You have reached the usage limit per customer for this coupon.',
        ]);

        // Create coupon with total usage limit of 1
        $coupon2 = Coupon::create([
            'business_id' => config('ecommerce.business_id', 1),
            'code' => 'TOTAL1',
            'discount_type' => 'flat',
            'discount_value' => 5.00,
            'min_order_amount' => 10.00,
            'usage_limit' => 1,
            'used_count' => 1, // Already used once
            'status' => 'active',
            'created_by' => 1,
        ]);

        $payload['couponCode'] = 'TOTAL1';

        $response3 = $this
            ->actingAs($user)
            ->postJson(route('checkout.place'), $payload);

        $response3->assertOk();
        $response3->assertJson([
            'success' => false,
            'message' => 'This coupon usage limit has been reached.',
        ]);
    }
}
