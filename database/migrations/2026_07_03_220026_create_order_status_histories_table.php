<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Audit trail of order status transitions.
     * Used by OrderStatusHistory model.
     *
     * NOTE: order_id references the orders table (external). FK omitted.
     */
    public function up(): void
    {
        if (! Schema::hasTable('order_status_histories')) {
            Schema::create('order_status_histories', function (Blueprint $table) {
                $table->id();

                if (! Schema::hasColumn('order_status_histories', 'order_id')) {
                    $table->unsignedBigInteger('order_id');
                }

                if (! Schema::hasColumn('order_status_histories', 'status')) {
                    $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled']);
                }

                if (! Schema::hasColumn('order_status_histories', 'note')) {
                    $table->string('note')->nullable();
                }

                if (! Schema::hasColumn('order_status_histories', 'changed_by')) {
                    $table->unsignedInteger('changed_by')->nullable();
                }

                if (! Schema::hasColumn('order_status_histories', 'created_at')) {
                    $table->timestamp('created_at')->useCurrent();
                }

                $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
