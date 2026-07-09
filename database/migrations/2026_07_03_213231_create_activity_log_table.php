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
        if (! Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('activity_log', 'log_name')) {
                    $table->string('log_name', 191)->nullable()->index();
                }

                if (! Schema::hasColumn('activity_log', 'description')) {
                    $table->text('description');
                }

                if (! Schema::hasColumn('activity_log', 'subject_id')) {
                    $table->unsignedBigInteger('subject_id')->nullable();
                }

                if (! Schema::hasColumn('activity_log', 'subject_type')) {
                    $table->string('subject_type', 191)->nullable();
                }

                if (! Schema::hasColumn('activity_log', 'business_id')) {
                    $table->integer('business_id')->nullable();
                }

                if (! Schema::hasColumn('activity_log', 'causer_id')) {
                    $table->unsignedBigInteger('causer_id')->nullable();
                }

                if (! Schema::hasColumn('activity_log', 'causer_type')) {
                    $table->string('causer_type', 191)->nullable();
                }

                if (! Schema::hasColumn('activity_log', 'properties')) {
                    $table->json('properties')->nullable();
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
        Schema::dropIfExists('activity_log');
    }
};
