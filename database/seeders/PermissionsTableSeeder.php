<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'user.view'],
            ['name' => 'user.create'],
            ['name' => 'user.update'],
            ['name' => 'user.delete'],

            ['name' => 'customer.view'],
            ['name' => 'customer.create'],
            ['name' => 'customer.update'],
            ['name' => 'customer.delete'],

            ['name' => 'product.view'],
            ['name' => 'product.create'],
            ['name' => 'product.update'],
            ['name' => 'product.delete'],

            ['name' => 'sell.view'],
            ['name' => 'sell.create'],
            ['name' => 'sell.update'],
            ['name' => 'sell.delete'],

            ['name' => 'brand.view'],
            ['name' => 'brand.create'],
            ['name' => 'brand.update'],
            ['name' => 'brand.delete'],

            ['name' => 'unit.view'],
            ['name' => 'unit.create'],
            ['name' => 'unit.update'],
            ['name' => 'unit.delete'],

            ['name' => 'category.view'],
            ['name' => 'category.create'],
            ['name' => 'category.update'],
            ['name' => 'category.delete'],
            ['name' => 'expense.access'],

            ['name' => 'dashboard.data'],

            ['name' => 'trending_product_report.view'],

            ['name' => 'business_settings.access'],
            ['name' => 'design_settings'],
            ['name' => 'invoice_settings.access'],
            ['name' => 'coupon_settings'],
            ['name' => 'broadcust'],
            ['name' => 'news'],

            ['name' => 'task.view'],
            ['name' => 'task.create'],
            ['name' => 'task.update'],
            ['name' => 'task.delete'],

            ['name' => 'blog_post.view'],
            ['name' => 'blog_post.create'],
            ['name' => 'blog_post.update'],
            ['name' => 'blog_post.delete'],

            ['name' => 'live_support.view'],
            ['name' => 'live_support.create'],
            ['name' => 'live_support.update'],
            ['name' => 'live_support.delete'],
        ];

        $insert_data = [];
        $time_stamp = now()->toDateTimeString();
        foreach ($data as $d) {
            $d['guard_name'] = 'web';
            $d['created_at'] = $time_stamp;
            $d['updated_at'] = $time_stamp;
            $insert_data[] = $d;
        }
        DB::table('permissions')->insert($insert_data);
    }
}
