<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Laravel database cache driver store.
     */
    public function up(): void
    {
        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                if (! Schema::hasColumn('cache', 'key')) {
                    $table->string('key')->primary();
                }

                if (! Schema::hasColumn('cache', 'value')) {
                    $table->mediumText('value');
                }

                if (! Schema::hasColumn('cache', 'expiration')) {
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
        Schema::dropIfExists('cache');
    }
};
