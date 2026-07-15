<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
