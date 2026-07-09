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
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('roles', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('roles', 'guard_name')) {
                    $table->string('guard_name', 191);
                }

                if (! Schema::hasColumn('roles', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('roles', 'is_default')) {
                    $table->tinyInteger('is_default')->default(0);
                }

                if (! Schema::hasColumn('roles', 'is_service_staff')) {
                    $table->tinyInteger('is_service_staff')->default(0);
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
        Schema::dropIfExists('roles');
    }
};
