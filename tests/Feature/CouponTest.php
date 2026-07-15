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
}
