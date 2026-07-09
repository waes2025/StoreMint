<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Customer product reviews, optionally tied to a verified order.
     * Used by Review model.
     *
     * NOTE: product_id references products (external), order_id references orders (external).
     * Both FKs are omitted; add them once those tables exist.
     */
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();

                if (! Schema::hasColumn('reviews', 'product_id')) {
                    $table->unsignedBigInteger('product_id');
                }

                if (! Schema::hasColumn('reviews', 'user_id')) {
                    $table->unsignedInteger('user_id');
                }

                if (! Schema::hasColumn('reviews', 'order_id')) {
                    $table->unsignedBigInteger('order_id')->nullable();
                }

                if (! Schema::hasColumn('reviews', 'rating')) {
                    $table->unsignedTinyInteger('rating');
                }

                if (! Schema::hasColumn('reviews', 'comment')) {
                    $table->text('comment')->nullable();
                }

                if (! Schema::hasColumn('reviews', 'is_approved')) {
                    $table->boolean('is_approved')->default(false);
                }

                if (! Schema::hasColumn('reviews', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }

                $table->timestamps();

                // One review per user per product
                $table->unique(['user_id', 'product_id']);

                // Efficient lookup of approved reviews per product
                $table->index(['product_id', 'is_approved']);

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
