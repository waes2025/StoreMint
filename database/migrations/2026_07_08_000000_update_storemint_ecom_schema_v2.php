<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // 2. Extend transactions table with coupon, gateway, order number, and address fields
        if (Schema::hasTable('transactions')) {
            // Alter ENUM values on payment_status safely using raw SQL (avoiding enum change issues)
            try {
                DB::statement("ALTER TABLE `transactions` MODIFY `payment_status` ENUM('pending','paid','due','partial','failed','cancelled','refunded') DEFAULT 'pending'");
            } catch (\Exception $e) {
                // If the connection/driver doesn't support direct modify, fallback
            }

            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'order_number')) {
                    $table->string('order_number', 30)->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('transactions', 'coupon_id')) {
                    $table->unsignedInteger('coupon_id')->nullable()->after('discount_amount');
                }
                if (!Schema::hasColumn('transactions', 'coupon_discount_amount')) {
                    $table->decimal('coupon_discount_amount', 20, 2)->default(0.00)->after('coupon_id');
                }
                if (!Schema::hasColumn('transactions', 'payment_gateway')) {
                    $table->enum('payment_gateway', ['cod', 'sslcommerz', 'stripe'])->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('transactions', 'shipping_address_id')) {
                    $table->unsignedBigInteger('shipping_address_id')->nullable()->after('shipping_address');
                }
                if (!Schema::hasColumn('transactions', 'billing_address_id')) {
                    $table->unsignedBigInteger('billing_address_id')->nullable()->after('shipping_address_id');
                }
            });
        }

        // 3. Extend transaction_payments table for gateway reconciliation
        if (Schema::hasTable('transaction_payments')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('transaction_payments', 'status')) {
                    $table->enum('status', ['initiated', 'success', 'failed', 'cancelled', 'refunded'])->default('initiated')->after('gateway');
                }
                if (!Schema::hasColumn('transaction_payments', 'currency')) {
                    $table->string('currency', 3)->nullable()->after('amount');
                }
                if (!Schema::hasColumn('transaction_payments', 'gateway_response')) {
                    $table->json('gateway_response')->nullable()->after('status');
                }
            });
        }

        // 4. Create coupons table
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('code', 50);
                $table->string('description', 255)->nullable();
                $table->enum('discount_type', ['flat', 'percentage'])->default('flat');
                $table->decimal('discount_value', 20, 2)->default(0.00);
                $table->decimal('max_discount_amount', 20, 2)->nullable()->comment('Cap for percentage type');
                $table->decimal('min_order_amount', 20, 2)->nullable();
                $table->integer('usage_limit')->nullable()->comment('Total redemptions allowed; null = unlimited');
                $table->integer('usage_limit_per_user')->nullable();
                $table->integer('used_count')->default(0);
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->unsignedInteger('created_by')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->unique(['business_id', 'code']);
                $table->index('status');
                $table->index('expires_at');
            });
        }

        // 5. Create addresses table
        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id');
                $table->string('label', 50)->nullable()->comment('e.g. Home, Office');
                $table->string('full_name', 191);
                $table->string('phone', 20);
                $table->string('address_line_1', 191);
                $table->string('address_line_2', 191)->nullable();
                $table->string('city', 100);
                $table->string('state', 100)->nullable();
                $table->string('zip_code', 20)->nullable();
                $table->string('country', 100);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index('user_id');
            });
        }

        // 6. Create coupon_usages table
        if (!Schema::hasTable('coupon_usages')) {
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

        // 7. Create invoices table
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('transaction_id')->comment('FK to transactions.id (the order)');
                $table->string('invoice_number', 30);
                $table->decimal('subtotal', 20, 2)->default(0.00);
                $table->decimal('discount_amount', 20, 2)->default(0.00);
                $table->decimal('coupon_discount_amount', 20, 2)->default(0.00);
                $table->decimal('tax_amount', 20, 2)->default(0.00);
                $table->decimal('shipping_charges', 20, 2)->default(0.00);
                $table->decimal('grand_total', 20, 2)->default(0.00);
                $table->string('pdf_path', 255)->nullable();
                $table->timestamp('issued_at')->nullable();
                $table->timestamps();

                $table->unique('transaction_id');
                $table->unique('invoice_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('coupons');

        // Rollback transactions additions
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $cols = ['billing_address_id', 'shipping_address_id', 'payment_gateway', 'coupon_discount_amount', 'coupon_id', 'order_number'];
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
