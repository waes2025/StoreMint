<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: product_id references the products table (external to this dump).
     * FK is omitted; add it once the products table exists.
     */
    public function up(): void
    {
        if (! Schema::hasTable('product_variations')) {
            Schema::create('product_variations', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('product_variations', 'variation_template_id')) {
                    $table->integer('variation_template_id')->nullable();
                }

                if (! Schema::hasColumn('product_variations', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('product_variations', 'product_id')) {
                    $table->unsignedInteger('product_id');
                }

                if (! Schema::hasColumn('product_variations', 'is_dummy')) {
                    $table->tinyInteger('is_dummy')->default(0);
                }

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
