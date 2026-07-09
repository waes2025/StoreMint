<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: No FK constraints on business_id per original design.
     */
    public function up(): void
    {
        if (! Schema::hasTable('discounts')) {
            Schema::create('discounts', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('discounts', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('discounts', 'business_id')) {
                    $table->integer('business_id')->nullable();
                }

                if (! Schema::hasColumn('discounts', 'brand_id')) {
                    $table->integer('brand_id')->nullable();
                }

                if (! Schema::hasColumn('discounts', 'category_id')) {
                    $table->integer('category_id')->nullable();
                }

                if (! Schema::hasColumn('discounts', 'location_id')) {
                    $table->integer('location_id')->nullable();
                }

                if (! Schema::hasColumn('discounts', 'priority')) {
                    $table->integer('priority')->default(0);
                }

                if (! Schema::hasColumn('discounts', 'discount_type')) {
                    $table->string('discount_type', 50)->nullable();
                }

                if (! Schema::hasColumn('discounts', 'discount_amount')) {
                    $table->decimal('discount_amount', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('discounts', 'starts_at')) {
                    $table->dateTime('starts_at')->nullable();
                }

                if (! Schema::hasColumn('discounts', 'ends_at')) {
                    $table->dateTime('ends_at')->nullable();
                }

                if (! Schema::hasColumn('discounts', 'is_active')) {
                    $table->tinyInteger('is_active')->default(1);
                }

                if (! Schema::hasColumn('discounts', 'spg')) {
                    $table->integer('spg')->nullable();
                }

                if (! Schema::hasColumn('discounts', 'applicable_in_cg')) {
                    $table->integer('applicable_in_cg')->nullable();
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
        Schema::dropIfExists('discounts');
    }
};
