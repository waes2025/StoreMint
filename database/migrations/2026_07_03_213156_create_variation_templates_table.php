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
        if (! Schema::hasTable('variation_templates')) {
            Schema::create('variation_templates', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('variation_templates', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('variation_templates', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('variation_templates', 'woocommerce_attr_id')) {
                    $table->string('woocommerce_attr_id', 191)->nullable();
                }

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
        Schema::dropIfExists('variation_templates');
    }
};
