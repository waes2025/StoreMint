<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Shopping carts — one per authenticated user or guest session.
     * Used by Cart model.
     */
    public function up(): void
    {
        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();

                if (! Schema::hasColumn('carts', 'user_id')) {
                    $table->unsignedInteger('user_id')->nullable();
                }

                if (! Schema::hasColumn('carts', 'session_id')) {
                    $table->string('session_id')->nullable()->index();
                }

                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
