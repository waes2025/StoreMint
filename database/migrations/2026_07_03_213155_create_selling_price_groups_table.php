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
        if (! Schema::hasTable('selling_price_groups')) {
            Schema::create('selling_price_groups', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('selling_price_groups', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('selling_price_groups', 'description')) {
                    $table->text('description')->nullable();
                }

                if (! Schema::hasColumn('selling_price_groups', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('selling_price_groups', 'is_active')) {
                    $table->tinyInteger('is_active')->default(1);
                }

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selling_price_groups');
    }
};
