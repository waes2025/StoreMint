<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_appearance_page_can_be_accessed_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this
            ->actingAs($user)
            ->get(route('appearance.edit'));

        $response->assertOk();
    }

    public function test_appearance_page_can_be_accessed_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->get(route('appearance.edit'));

        $response->assertOk();
    }

    public function test_announcement_bar_cannot_be_updated_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this
            ->actingAs($user)
            ->patch(route('appearance.update'), [
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
            ->from(route('appearance.edit'))
            ->patch(route('appearance.update'), [
                'enabled' => true,
                'text' => '✨ HUGE GRAND OPENING SALE: USE {coupon}!',
                'coupon' => 'SALE100',
                'bg_color' => '#ff0000',
                'text_color' => '#ffffff',
            ]);

        $response->assertRedirect(route('appearance.edit'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('system', [
            'key' => 'announcement_bar',
        ]);

        $systemSetting = DB::table('system')->where('key', 'announcement_bar')->first();
        $announcement = json_decode($systemSetting->value, true);

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
            ->from(route('appearance.edit'))
            ->patch(route('appearance.update'), [
                'enabled' => true,
                'text' => '✨ HUGE GRAND OPENING SALE: USE {coupon}!',
                'coupon' => 'SALE100',
                'bg_color' => 'red', // Invalid hex color
                'text_color' => '#ffg000', // Invalid hex color
            ]);

        $response->assertRedirect(route('appearance.edit'));
        $response->assertSessionHasErrors([
            'bg_color',
            'text_color',
        ]);
    }
}
