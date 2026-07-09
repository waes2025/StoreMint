<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * WebAuthn passkey credentials for passwordless login.
     */
    public function up(): void
    {
        if (! Schema::hasTable('passkeys')) {
            Schema::create('passkeys', function (Blueprint $table) {
                $table->id();

                if (! Schema::hasColumn('passkeys', 'user_id')) {
                    $table->unsignedInteger('user_id')->index();
                }

                if (! Schema::hasColumn('passkeys', 'name')) {
                    $table->string('name');
                }

                if (! Schema::hasColumn('passkeys', 'credential_id')) {
                    $table->string('credential_id')->unique();
                }

                if (! Schema::hasColumn('passkeys', 'credential')) {
                    $table->json('credential');
                }

                if (! Schema::hasColumn('passkeys', 'last_used_at')) {
                    $table->timestamp('last_used_at')->nullable();
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
        Schema::dropIfExists('passkeys');
    }
};
