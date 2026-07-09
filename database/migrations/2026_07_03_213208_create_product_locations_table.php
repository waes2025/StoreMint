<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pivot table — no PK/FK constraints in original dump.
     */
    public function up(): void
    {
        if (! Schema::hasTable('product_locations')) {
            Schema::create('product_locations', function (Blueprint $table) {
                if (! Schema::hasColumn('product_locations', 'product_id')) {
                    $table->unsignedInteger('product_id');
                }

                if (! Schema::hasColumn('product_locations', 'location_id')) {
                    $table->unsignedInteger('location_id');
                }

                $table->index('product_id');
                $table->index('location_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_locations');
    }
};
