<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('key');
                $table->longText('value')->nullable();
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->unique(['business_id', 'key']);
            });
        }

        // if (Schema::hasTable('system') && Schema::hasTable('settings')) {
        //     $systemSettings = DB::table('system')->get();
        //     $businesses = DB::table('business')->get();

        //     foreach ($systemSettings as $setting) {
        //         if ($businesses->isEmpty()) {
        //             DB::table('settings')->updateOrInsert(
        //                 ['business_id' => 1, 'key' => $setting->key],
        //                 ['value' => $setting->value, 'created_at' => now(), 'updated_at' => now()]
        //             );
        //         } else {
        //             foreach ($businesses as $business) {
        //                 DB::table('settings')->updateOrInsert(
        //                     ['business_id' => $business->id, 'key' => $setting->key],
        //                     ['value' => $setting->value, 'created_at' => now(), 'updated_at' => now()]
        //                 );
        //             }
        //         }
        //     }
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
