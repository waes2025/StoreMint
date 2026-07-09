<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

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
            'business_id' => $user->business_id ?? 1,
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
            'business_id' => $user->business_id ?? 1,
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
            'business_id' => $user->business_id ?? 1,
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
