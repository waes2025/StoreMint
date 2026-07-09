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
        if (! Schema::hasTable('warranties')) {
            Schema::create('warranties', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('warranties', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('warranties', 'business_id')) {
                    $table->integer('business_id')->nullable();
                }

                if (! Schema::hasColumn('warranties', 'description')) {
                    $table->text('description')->nullable();
                }

                if (! Schema::hasColumn('warranties', 'duration')) {
                    $table->integer('duration')->nullable();
                }

                if (! Schema::hasColumn('warranties', 'duration_type')) {
                    $table->enum('duration_type', ['days', 'months', 'years'])->nullable();
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
        Schema::dropIfExists('warranties');
    }
};
