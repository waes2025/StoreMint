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
        if (! Schema::hasTable('dashboard_configurations')) {
            Schema::create('dashboard_configurations', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('dashboard_configurations', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('dashboard_configurations', 'created_by')) {
                    $table->integer('created_by')->nullable();
                }

                if (! Schema::hasColumn('dashboard_configurations', 'name')) {
                    $table->string('name', 191)->nullable();
                }

                if (! Schema::hasColumn('dashboard_configurations', 'color')) {
                    $table->string('color', 50)->nullable();
                }

                if (! Schema::hasColumn('dashboard_configurations', 'configuration')) {
                    $table->text('configuration')->nullable();
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
        Schema::dropIfExists('dashboard_configurations');
    }
};
