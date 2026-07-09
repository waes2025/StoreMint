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
        if (! Schema::hasTable('variation_group_prices')) {
            Schema::create('variation_group_prices', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('variation_group_prices', 'variation_id')) {
                    $table->unsignedInteger('variation_id');
                }

                if (! Schema::hasColumn('variation_group_prices', 'price_group_id')) {
                    $table->unsignedInteger('price_group_id');
                }

                if (! Schema::hasColumn('variation_group_prices', 'price_inc_tax')) {
                    $table->decimal('price_inc_tax', 20, 2)->default(0);
                }

                $table->timestamps();

                $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
                $table->foreign('price_group_id')->references('id')->on('selling_price_groups')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_group_prices');
    }
};
