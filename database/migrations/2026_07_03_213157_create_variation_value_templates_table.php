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
        if (! Schema::hasTable('variation_value_templates')) {
            Schema::create('variation_value_templates', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('variation_value_templates', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('variation_value_templates', 'variation_template_id')) {
                    $table->unsignedInteger('variation_template_id');
                }

                $table->timestamps();

                $table->foreign('variation_template_id')->references('id')->on('variation_templates')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_value_templates');
    }
};
