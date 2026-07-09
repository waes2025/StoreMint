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
        if (! Schema::hasTable('system')) {
            Schema::create('system', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('system', 'key')) {
                    $table->string('key', 191);
                }

                if (! Schema::hasColumn('system', 'value')) {
                    $table->text('value')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system');
    }
};
