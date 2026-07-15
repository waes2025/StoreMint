<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cart line items — one row per product per cart.
     * Used by CartItem model.
     *
     * NOTE: product_id references the products table (external). FK omitted.
     */
    public function up(): void
    {
        if (! Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();

                if (! Schema::hasColumn('cart_items', 'cart_id')) {
                    $table->unsignedBigInteger('cart_id');
                }

                if (! Schema::hasColumn('cart_items', 'product_id')) {
                    $table->unsignedBigInteger('product_id');
                }

                if (! Schema::hasColumn('cart_items', 'quantity')) {
                    $table->unsignedInteger('quantity')->default(1);
                }

                $table->timestamps();

                $table->unique(['cart_id', 'product_id']);

                $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
