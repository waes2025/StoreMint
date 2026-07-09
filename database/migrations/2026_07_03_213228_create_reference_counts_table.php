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
        if (! Schema::hasTable('reference_counts')) {
            Schema::create('reference_counts', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('reference_counts', 'ref_type')) {
                    $table->string('ref_type', 191);
                }

                if (! Schema::hasColumn('reference_counts', 'ref_count')) {
                    $table->integer('ref_count')->default(0);
                }

                if (! Schema::hasColumn('reference_counts', 'business_id')) {
                    $table->integer('business_id');
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
        Schema::dropIfExists('reference_counts');
    }
};
