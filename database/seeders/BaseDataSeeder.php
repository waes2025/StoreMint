<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BaseDataSeeder extends Seeder
{
    /**
     * Seed base configuration and admin user without any demo products, orders, or coupons.
     */
    public function run(): void
    {
        // Disable foreign key checks for clean truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        DB::table('coupon_usages')->truncate();
        DB::table('coupons')->truncate();
        DB::table('variation_location_details')->truncate();
        DB::table('variations')->truncate();
        DB::table('product_variations')->truncate();
        DB::table('products')->truncate();
        DB::table('brands')->truncate();
        DB::table('categories')->truncate();
        DB::table('business_locations')->truncate();
        DB::table('business')->truncate();
        DB::table('team_invitations')->truncate();
        DB::table('team_members')->truncate();
        DB::table('teams')->truncate();
        DB::table('users')->truncate();
        DB::table('transaction_sell_lines')->truncate();
        DB::table('purchase_lines')->truncate();
        DB::table('transaction_payments')->truncate();
        DB::table('transactions')->truncate();
        DB::table('contacts')->truncate();
        DB::table('user_contact_access')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bdtCurrencyId = DB::table('currencies')->where('code', 'BDT')->value('id');

        // ── Owner User (temp business_id=null to break circular dependency) ──────
        $userId = DB::table('users')->insertGetId([
            'first_name' => 'Waes',
            'last_name' => 'Ahmed',
            'username' => 'waes',
            'email' => 'waes@storemint.com',
            'password' => Hash::make('password'),
            'user_type' => 'user',
            'business_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $targetBusinessId = (int) config('ecommerce.business_id', 1);
        $targetLocationId = (int) config('ecommerce.location_id', 1);

        // ── Business ──────────────────────────────────────────────────────────────
        DB::table('business')->insert([
            'id' => $targetBusinessId,
            'name' => 'StoreMint',
            'currency_id' => $bdtCurrencyId,
            'start_date' => now()->subYear()->toDateString(),
            'owner_id' => $userId,
            'default_profit_percent' => 40.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $businessId = $targetBusinessId;

        // Update user with real business_id
        DB::table('users')->where('id', $userId)->update(['business_id' => $businessId]);

        // ── Base Store Team ───────────────────────────────────────────────────────
        $storeTeamId = DB::table('teams')->insertGetId([
            'name' => 'StoreMint Store',
            'slug' => 'storemint-store',
            'is_personal' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', $userId)->update(['current_team_id' => $storeTeamId]);

        DB::table('team_members')->insert([
            'team_id' => $storeTeamId,
            'user_id' => $userId,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Business Location ─────────────────────────────────────────────────────
        DB::table('business_locations')->insert([
            'id' => $targetLocationId,
            'business_id' => $businessId,
            'name' => 'Main Store',
            'country' => 'Bangladesh',
            'state' => 'Dhaka',
            'city' => 'Dhaka',
            'zip_code' => '1200',
            'landmark' => 'Dhaka, Bangladesh',
            'mobile' => '+880 1700-000000',
            'invoice_scheme_id' => 1,
            'invoice_layout_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
