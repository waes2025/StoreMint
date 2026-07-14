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
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('categories', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('categories', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('categories', 'short_code')) {
                    $table->string('short_code', 191)->nullable();
                }

                if (! Schema::hasColumn('categories', 'parent_id')) {
                    $table->integer('parent_id')->nullable();
                }

                if (! Schema::hasColumn('categories', 'created_by')) {
                    $table->unsignedInteger('created_by');
                }

                if (! Schema::hasColumn('categories', 'woocommerce_cat_id')) {
                    $table->string('woocommerce_cat_id', 191)->nullable();
                }

                if (! Schema::hasColumn('categories', 'category_type')) {
                    $table->string('category_type', 191)->nullable();
                }

                if (! Schema::hasColumn('categories', 'description')) {
                    $table->text('description')->nullable();
                }

                if (! Schema::hasColumn('categories', 'slug')) {
                    $table->string('slug', 191)->nullable();
                }

                if (! Schema::hasColumn('categories', 'image')) {
                    $table->string('image', 191)->nullable();
                }

                if (! Schema::hasColumn('categories', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }

                if (! Schema::hasColumn('categories', 'is_allow_ecom')) {
                    $table->boolean('is_allow_ecom')->default(false);
                }

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
