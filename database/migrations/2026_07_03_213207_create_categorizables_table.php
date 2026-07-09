<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Polymorphic pivot — no PK, indexed on (categorizable_type, categorizable_id).
     */
    public function up(): void
    {
        if (! Schema::hasTable('categorizables')) {
            Schema::create('categorizables', function (Blueprint $table) {
                if (! Schema::hasColumn('categorizables', 'category_id')) {
                    $table->unsignedInteger('category_id');
                }

                if (! Schema::hasColumn('categorizables', 'categorizable_type')) {
                    $table->string('categorizable_type', 191);
                }

                if (! Schema::hasColumn('categorizables', 'categorizable_id')) {
                    $table->unsignedBigInteger('categorizable_id');
                }

                $table->index(['categorizable_type', 'categorizable_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorizables');
    }
};
