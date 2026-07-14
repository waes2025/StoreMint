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
        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('currencies', 'country')) {
                    $table->string('country', 191);
                }

                if (! Schema::hasColumn('currencies', 'currency')) {
                    $table->string('currency', 191);
                }

                if (! Schema::hasColumn('currencies', 'code')) {
                    $table->string('code', 191);
                }

                if (! Schema::hasColumn('currencies', 'symbol')) {
                    $table->string('symbol', 191);
                }

                if (! Schema::hasColumn('currencies', 'thousand_separator')) {
                    $table->string('thousand_separator', 10)->nullable();
                }

                if (! Schema::hasColumn('currencies', 'decimal_separator')) {
                    $table->string('decimal_separator', 10)->nullable();
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
        Schema::dropIfExists('currencies');
    }
};
