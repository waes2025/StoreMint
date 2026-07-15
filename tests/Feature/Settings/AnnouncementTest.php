<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnnouncementTest extends TestCase
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

        // Create a default business record because settings has a foreign key to business table
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Test Business',
            'currency_id' => $currencyId,
            'owner_id' => $userId,
            'enabled_modules' => json_encode(['Cart']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_announcement_page_cannot_be_accessed_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this
            ->actingAs($user)
            ->get(route('announcement.edit'));

        $response->assertStatus(403);
    }

    public function test_announcement_page_can_be_accessed_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->get(route('announcement.edit'));

        $response->assertOk();
    }

    public function test_announcement_bar_cannot_be_updated_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this
            ->actingAs($user)
            ->patch(route('announcement.update'), [
                'enabled' => true,
                'text' => 'Updated text',
                'coupon' => 'CODE',
                'bg_color' => '#123456',
                'text_color' => '#654321',
            ]);

        $response->assertStatus(403);
    }

    public function test_announcement_bar_can_be_updated_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->from(route('announcement.edit'))
            ->patch(route('announcement.update'), [
                'enabled' => true,
                'text' => '✨ HUGE GRAND OPENING SALE: USE {coupon}!',
                'coupon' => 'SALE100',
                'bg_color' => '#ff0000',
                'text_color' => '#ffffff',
            ]);

        $response->assertRedirect(route('announcement.edit'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'key' => 'announcement_bar',
        ]);

        $setting = DB::table('settings')->where('key', 'announcement_bar')->first();
        $announcement = json_decode($setting->value, true);

        $this->assertTrue($announcement['enabled']);
        $this->assertSame('✨ HUGE GRAND OPENING SALE: USE {coupon}!', $announcement['text']);
        $this->assertSame('SALE100', $announcement['coupon']);
        $this->assertSame('#ff0000', $announcement['bg_color']);
        $this->assertSame('#ffffff', $announcement['text_color']);
    }

    public function test_announcement_bar_validation_fails_with_invalid_colors()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->from(route('announcement.edit'))
            ->patch(route('announcement.update'), [
                'enabled' => true,
                'text' => '✨ HUGE GRAND OPENING SALE: USE {coupon}!',
                'coupon' => 'SALE100',
                'bg_color' => 'red', // Invalid hex color
                'text_color' => '#ffg000', // Invalid hex color
            ]);

        $response->assertRedirect(route('announcement.edit'));
        $response->assertSessionHasErrors([
            'bg_color',
            'text_color',
        ]);
    }

    public function test_announcement_page_cannot_be_accessed_by_admin_if_cart_module_is_disabled()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

        // Disable Cart module
        DB::table('business')->where('id', 1)->update([
            'enabled_modules' => json_encode([]),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('announcement.edit'));

        $response->assertStatus(403);
    }
}
