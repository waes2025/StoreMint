<?php

namespace Tests\Feature\Settings;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModulesTest extends TestCase
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_modules_page_cannot_be_accessed_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this
            ->actingAs($user)
            ->get(route('modules.edit'));

        $response->assertStatus(403);
    }

    public function test_modules_page_can_be_accessed_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->get(route('modules.edit'));

        $response->assertOk();
    }

    public function test_modules_cannot_be_updated_by_customer()
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this
            ->actingAs($user)
            ->patch(route('modules.update'), [
                'enabled_modules' => ['Blog'],
            ]);

        $response->assertStatus(403);
    }

    public function test_modules_can_be_updated_by_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->from(route('modules.edit'))
            ->patch(route('modules.update'), [
                'enabled_modules' => ['Blog', 'Gateway'],
            ]);

        $response->assertRedirect(route('modules.edit'));
        $response->assertSessionHasNoErrors();

        $business = Business::find(1);
        $this->assertEquals(['Blog', 'Gateway'], $business->enabled_modules);
    }
}
