<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Saved/favorited products per user.
     * Used by Wishlist model.
     *
     * NOTE: product_id references the products table (external). FK omitted.
     */
    public function up(): void
    {
        if (! Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function (Blueprint $table) {
                $table->id();

                if (! Schema::hasColumn('wishlists', 'user_id')) {
                    $table->unsignedInteger('user_id');
                }

                if (! Schema::hasColumn('wishlists', 'product_id')) {
                    $table->unsignedBigInteger('product_id');
                }

                $table->timestamps();

                // One wishlist entry per user per product
                $table->unique(['user_id', 'product_id']);

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
