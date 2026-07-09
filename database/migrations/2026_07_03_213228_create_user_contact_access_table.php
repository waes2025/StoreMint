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
        if (! Schema::hasTable('user_contact_access')) {
            Schema::create('user_contact_access', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('user_contact_access', 'user_id')) {
                    $table->integer('user_id');
                }

                if (! Schema::hasColumn('user_contact_access', 'contact_id')) {
                    $table->integer('contact_id');
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
        Schema::dropIfExists('user_contact_access');
    }
};
