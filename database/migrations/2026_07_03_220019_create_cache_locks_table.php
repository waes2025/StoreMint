<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Atomic lock records for Cache::lock().
     */
    public function up(): void
    {
        if (! Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                if (! Schema::hasColumn('cache_locks', 'key')) {
                    $table->string('key')->primary();
                }

                if (! Schema::hasColumn('cache_locks', 'owner')) {
                    $table->string('owner');
                }

                if (! Schema::hasColumn('cache_locks', 'expiration')) {
                    $table->integer('expiration');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
    }
};
