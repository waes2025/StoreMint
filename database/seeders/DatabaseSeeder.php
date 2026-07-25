<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsTableSeeder::class,
            CurrenciesTableSeeder::class,
        ]);

        if (config('app.env') === 'demo') {
            $this->call(DemoDataSeeder::class);
        }
    }
}
