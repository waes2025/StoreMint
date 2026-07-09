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
        if (! Schema::hasTable('variation_location_details')) {
            Schema::create('variation_location_details', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('variation_location_details', 'product_id')) {
                    $table->unsignedInteger('product_id');
                }

                if (! Schema::hasColumn('variation_location_details', 'product_variation_id')) {
                    $table->unsignedInteger('product_variation_id');
                }

                if (! Schema::hasColumn('variation_location_details', 'variation_id')) {
                    $table->unsignedInteger('variation_id');
                }

                if (! Schema::hasColumn('variation_location_details', 'location_id')) {
                    $table->unsignedInteger('location_id');
                }

                if (! Schema::hasColumn('variation_location_details', 'qty_available')) {
                    $table->decimal('qty_available', 20, 4)->default(0);
                }

                $table->timestamps();

                $table->foreign('variation_id')->references('id')->on('variations');
                $table->foreign('location_id')->references('id')->on('business_locations');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_location_details');
    }
};
