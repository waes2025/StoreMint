<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Batched/grouped queue job tracking for Bus::batch().
     */
    public function up(): void
    {
        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                if (! Schema::hasColumn('job_batches', 'id')) {
                    $table->string('id')->primary();
                }

                if (! Schema::hasColumn('job_batches', 'name')) {
                    $table->string('name');
                }

                if (! Schema::hasColumn('job_batches', 'total_jobs')) {
                    $table->integer('total_jobs');
                }

                if (! Schema::hasColumn('job_batches', 'pending_jobs')) {
                    $table->integer('pending_jobs');
                }

                if (! Schema::hasColumn('job_batches', 'failed_jobs')) {
                    $table->integer('failed_jobs');
                }

                if (! Schema::hasColumn('job_batches', 'failed_job_ids')) {
                    $table->longText('failed_job_ids');
                }

                if (! Schema::hasColumn('job_batches', 'options')) {
                    $table->mediumText('options')->nullable();
                }

                if (! Schema::hasColumn('job_batches', 'cancelled_at')) {
                    $table->integer('cancelled_at')->nullable();
                }

                if (! Schema::hasColumn('job_batches', 'created_at')) {
                    $table->integer('created_at');
                }

                if (! Schema::hasColumn('job_batches', 'finished_at')) {
                    $table->integer('finished_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_batches');
    }
};
