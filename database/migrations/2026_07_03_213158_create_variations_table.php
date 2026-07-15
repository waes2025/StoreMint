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
     * FK to products is omitted; add it once the products table exists.
     */
    public function up(): void
    {
        if (! Schema::hasTable('variations')) {
            Schema::create('variations', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('variations', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('variations', 'product_id')) {
                    $table->unsignedInteger('product_id');
                }

                if (! Schema::hasColumn('variations', 'sub_sku')) {
                    $table->string('sub_sku', 191)->nullable();
                }

                if (! Schema::hasColumn('variations', 'product_variation_id')) {
                    $table->unsignedInteger('product_variation_id');
                }

                if (! Schema::hasColumn('variations', 'woocommerce_variation_id')) {
                    $table->string('woocommerce_variation_id', 191)->nullable();
                }

                if (! Schema::hasColumn('variations', 'variation_value_id')) {
                    $table->integer('variation_value_id')->nullable();
                }

                if (! Schema::hasColumn('variations', 'default_purchase_price')) {
                    $table->decimal('default_purchase_price', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('variations', 'dpp_inc_tax')) {
                    $table->decimal('dpp_inc_tax', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('variations', 'profit_percent')) {
                    $table->decimal('profit_percent', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('variations', 'default_sell_price')) {
                    $table->decimal('default_sell_price', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('variations', 'sell_price_inc_tax')) {
                    $table->decimal('sell_price_inc_tax', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('variations', 'pcs_per_box')) {
                    $table->decimal('pcs_per_box', 20, 4)->nullable();
                }

                if (! Schema::hasColumn('variations', 'combo_variations')) {
                    $table->text('combo_variations')->nullable();
                }

                // Storefront ECOM extra fields
                if (! Schema::hasColumn('variations', 'slug')) {
                    $table->string('slug', 191)->nullable()->unique();
                }
                if (! Schema::hasColumn('variations', 'compare_at_price')) {
                    $table->decimal('compare_at_price', 20, 2)->nullable();
                }
                if (! Schema::hasColumn('variations', 'is_best_seller')) {
                    $table->boolean('is_best_seller')->default(false);
                }

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variations');
    }
};
