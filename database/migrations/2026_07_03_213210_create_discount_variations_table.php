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
        if (! Schema::hasTable('discount_variations')) {
            Schema::create('discount_variations', function (Blueprint $table) {
                if (! Schema::hasColumn('discount_variations', 'discount_id')) {
                    $table->unsignedInteger('discount_id');
                }

                if (! Schema::hasColumn('discount_variations', 'variation_id')) {
                    $table->unsignedInteger('variation_id');
                }

                $table->index('discount_id');
                $table->index('variation_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_variations');
    }
};
