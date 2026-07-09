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
        if (!Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('is_personal')->default(false);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('team_members')) {
            Schema::create('team_members', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('team_id');
                $table->unsignedInteger('user_id'); // Match users.id increments
                $table->string('role');
                $table->timestamps();

                $table->unique(['team_id', 'user_id']);
                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('team_invitations')) {
            Schema::create('team_invitations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('team_id');
                $table->string('email');
                $table->string('role');
                $table->unsignedInteger('invited_by');
                $table->string('code')->unique();
                $table->dateTime('expires_at')->nullable();
                $table->dateTime('accepted_at')->nullable();
                $table->timestamps();

                $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
                $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'current_team_id')) {
                    $table->unsignedBigInteger('current_team_id')->nullable()->after('remember_token');
                    $table->foreign('current_team_id')->references('id')->on('teams')->onDelete('set null');
                }
                if (!Schema::hasColumn('users', 'name')) {
                    $table->string('name')->nullable()->after('last_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('users', 'current_team_id')) {
                    $table->dropForeign(['current_team_id']);
                    $table->dropColumn('current_team_id');
                }
            });
        }

        Schema::dropIfExists('team_invitations');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
