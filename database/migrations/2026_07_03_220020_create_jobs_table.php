<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Queued job payloads for the database queue driver.
     */
    public function up(): void
    {
        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();

                if (! Schema::hasColumn('jobs', 'queue')) {
                    $table->string('queue')->index();
                }

                if (! Schema::hasColumn('jobs', 'payload')) {
                    $table->longText('payload');
                }

                if (! Schema::hasColumn('jobs', 'attempts')) {
                    $table->unsignedSmallInteger('attempts');
                }

                if (! Schema::hasColumn('jobs', 'reserved_at')) {
                    $table->unsignedInteger('reserved_at')->nullable();
                }

                if (! Schema::hasColumn('jobs', 'available_at')) {
                    $table->unsignedInteger('available_at');
                }

                if (! Schema::hasColumn('jobs', 'created_at')) {
                    $table->unsignedInteger('created_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
