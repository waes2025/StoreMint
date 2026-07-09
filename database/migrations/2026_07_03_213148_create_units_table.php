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
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('units', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('units', 'actual_name')) {
                    $table->string('actual_name', 191);
                }

                if (! Schema::hasColumn('units', 'short_name')) {
                    $table->string('short_name', 191);
                }

                if (! Schema::hasColumn('units', 'allow_decimal')) {
                    $table->tinyInteger('allow_decimal')->default(1);
                }

                if (! Schema::hasColumn('units', 'base_unit_id')) {
                    $table->integer('base_unit_id')->nullable();
                }

                if (! Schema::hasColumn('units', 'base_unit_multiplier')) {
                    $table->decimal('base_unit_multiplier', 20, 4)->nullable();
                }

                if (! Schema::hasColumn('units', 'created_by')) {
                    $table->unsignedInteger('created_by');
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
        Schema::dropIfExists('units');
    }
};
