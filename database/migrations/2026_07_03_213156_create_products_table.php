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
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 191);
                $table->unsignedInteger('business_id');
                $table->string('type', 191)->nullable();
                $table->unsignedInteger('unit_id')->nullable();
                $table->unsignedInteger('secondary_unit_id')->nullable();
                $table->string('sub_unit_ids', 191)->nullable();
                $table->unsignedInteger('brand_id')->nullable();
                $table->unsignedInteger('category_id')->nullable();
                $table->unsignedInteger('sub_category_id')->nullable();
                $table->string('tax', 191)->nullable();
                $table->string('tax_type', 191)->nullable();
                $table->tinyInteger('enable_stock')->default(0);
                $table->tinyInteger('is_size')->nullable();
                $table->string('width', 191)->nullable();
                $table->string('height', 191)->nullable();
                $table->decimal('alert_quantity', 20, 2)->nullable();
                $table->string('sku', 191)->nullable();
                $table->string('barcode_type', 191)->nullable();
                $table->integer('expiry_period')->nullable();
                $table->string('expiry_period_type', 191)->nullable();
                $table->tinyInteger('enable_sr_no')->default(0);
                $table->string('weight', 191)->nullable();
                $table->string('product_custom_field1', 191)->nullable();
                $table->string('product_custom_field2', 191)->nullable();
                $table->string('product_custom_field3', 191)->nullable();
                $table->string('product_custom_field4', 191)->nullable();
                $table->string('image', 191)->nullable();
                $table->integer('woocommerce_media_id')->nullable();
                $table->text('product_description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->integer('woocommerce_product_id')->nullable();
                $table->tinyInteger('woocommerce_disable_sync')->default(0);
                $table->integer('preparation_time_in_minutes')->nullable();
                $table->integer('warranty_id')->nullable();
                $table->tinyInteger('is_inactive')->default(0);
                $table->unsignedInteger('repair_model_id')->nullable();
                $table->tinyInteger('not_for_selling')->default(0);
                $table->integer('is_no_print')->default(0);

                // Storefront ECOM extra fields
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_allow_ecom')->default(false)->comment('Active for direct own E-commerce platform permission.');
                $table->integer('sold_count')->default(0);

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'description')) {
                $table->dropColumn('description');
            }
        });
        Schema::dropIfExists('products');
    }
};
