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
            ['name' => 'storefront_settings'],
            ['name' => 'design_settings'],
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
