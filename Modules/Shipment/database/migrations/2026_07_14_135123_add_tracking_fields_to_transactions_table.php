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
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'tracking_number')) {
                $table->string('tracking_number', 100)->nullable()->after('shipping_status');
            }
            if (! Schema::hasColumn('transactions', 'tracking_url')) {
                $table->string('tracking_url', 500)->nullable()->after('tracking_number');
            }
            if (! Schema::hasColumn('transactions', 'courier')) {
                $table->string('courier', 100)->nullable()->after('tracking_url');
            }
            if (! Schema::hasColumn('transactions', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('courier');
            }
            if (! Schema::hasColumn('transactions', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['tracking_number', 'tracking_url', 'courier', 'shipped_at', 'delivered_at']);
        });
    }
};
