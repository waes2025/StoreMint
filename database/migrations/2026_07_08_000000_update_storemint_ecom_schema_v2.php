<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fix order_status_histories.order_id type mismatch & rename to transaction_id
        if (Schema::hasTable('order_status_histories')) {
            Schema::table('order_status_histories', function (Blueprint $table) {
                if (Schema::hasColumn('order_status_histories', 'order_id')) {
                    // Update field to match transactions.id type (int(10) unsigned)
                    $table->unsignedInteger('order_id')->change();
                    $table->renameColumn('order_id', 'transaction_id');
                }
            });
        }

        // 2. Extend transactions table with coupon fields
        if (Schema::hasTable('transactions')) {
            // Alter ENUM values on payment_status safely using raw SQL (avoiding enum change issues)
            try {
                DB::statement("ALTER TABLE `transactions` MODIFY `payment_status` ENUM('pending','paid','due','partial','failed','cancelled','refunded') DEFAULT 'pending'");
            } catch (Exception $e) {
                // If the connection/driver doesn't support direct modify, fallback
            }

            Schema::table('transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('transactions', 'coupon_id')) {
                    $table->unsignedInteger('coupon_id')->nullable()->after('discount_amount');
                }
            });
        }

        // 3. Extend transaction_payments table for gateway reconciliation
        if (Schema::hasTable('transaction_payments')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                if (! Schema::hasColumn('transaction_payments', 'status')) {
                    $table->string('status', 50)->nullable()->after('gateway');
                }
                if (! Schema::hasColumn('transaction_payments', 'currency')) {
                    $table->string('currency', 3)->nullable()->after('amount');
                }
                if (! Schema::hasColumn('transaction_payments', 'gateway_response')) {
                    $table->json('gateway_response')->nullable()->after('status');
                }
            });
        }

        // 4. Create coupons table
        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('code', 50);
                $table->string('description', 255)->nullable();
                $table->string('discount_type', 50)->default('flat');
                $table->decimal('discount_value', 20, 2)->default(0.00);
                $table->decimal('max_discount_amount', 20, 2)->nullable()->comment('Cap for percentage type');
                $table->decimal('min_order_amount', 20, 2)->nullable();
                $table->integer('usage_limit')->nullable()->comment('Total redemptions allowed; null = unlimited');
                $table->integer('usage_limit_per_user')->nullable();
                $table->integer('used_count')->default(0);
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->string('status', 50)->default('active');
                $table->unsignedInteger('created_by')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->unique(['business_id', 'code']);
                $table->index('status');
                $table->index('expires_at');
            });
        }

        // 5. Create coupon_usages table
        if (! Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('coupon_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->unsignedInteger('transaction_id')->comment('FK to transactions.id (the order)');
                $table->decimal('discount_applied', 20, 2)->default(0.00);
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['coupon_id', 'transaction_id']);
                $table->index('coupon_id');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');

        // Rollback transactions additions
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $cols = ['coupon_id'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('transactions', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // Rollback transaction_payments additions
        if (Schema::hasTable('transaction_payments')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                $cols = ['gateway_response', 'currency', 'status'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('transaction_payments', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // Rollback order_status_histories alterations
        if (Schema::hasTable('order_status_histories')) {
            Schema::table('order_status_histories', function (Blueprint $table) {
                if (Schema::hasColumn('order_status_histories', 'transaction_id')) {
                    $table->renameColumn('transaction_id', 'order_id');
                }
            });
        }
    }
};
