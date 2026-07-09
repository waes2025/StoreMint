<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Central polymorphic transaction table covering sales, purchases, transfers,
     * adjustments, expenses, repairs, subscriptions, etc.
     *
     * NOTE: tax_id, expense_category_id, income_category_id, repair_job_sheet_id reference
     * external tables (tax_rates, expense_categories, income_categories, repair_job_sheets)
     * not included in this dump. Those FKs are omitted.
     */
    public function up(): void
    {
        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('transactions', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('transactions', 'location_id')) {
                    $table->unsignedInteger('location_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'contact_id')) {
                    $table->unsignedInteger('contact_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'created_by')) {
                    $table->unsignedInteger('created_by');
                }

                // Classification
                if (! Schema::hasColumn('transactions', 'type')) {
                    $table->string('type', 50)->nullable()->comment('sales_order, sell');
                }

                if (! Schema::hasColumn('transactions', 'sub_type')) {
                    $table->string('sub_type', 50)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'status')) {
                    $table->string('status', 50)->nullable()->comment('ordered, completed');
                }

                if (! Schema::hasColumn('transactions', 'sub_status')) {
                    $table->string('sub_status', 50)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'is_quotation')) {
                    $table->tinyInteger('is_quotation')->default(0);
                }

                if (! Schema::hasColumn('transactions', 'payment_status')) {
                    $table->enum('payment_status', ['paid', 'due', 'partial'])->nullable();
                }

                if (! Schema::hasColumn('transactions', 'reconciliation_type')) {
                    $table->string('reconciliation_type', 50)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'adjustment_type')) {
                    $table->string('adjustment_type', 50)->nullable();
                }

                // Restaurant module
                if (! Schema::hasColumn('transactions', 'res_table_id')) {
                    $table->integer('res_table_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'res_waiter_id')) {
                    $table->integer('res_waiter_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'res_order_status')) {
                    $table->string('res_order_status', 50)->nullable();
                }

                // Reference / invoice
                if (! Schema::hasColumn('transactions', 'invoice_no')) {
                    $table->string('invoice_no', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'ref_no')) {
                    $table->string('ref_no', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'source')) {
                    $table->string('source', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'subscription_no')) {
                    $table->string('subscription_no', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repeat_on')) {
                    $table->string('repeat_on', 191)->nullable();
                }

                // Financials
                if (! Schema::hasColumn('transactions', 'transaction_date')) {
                    $table->dateTime('transaction_date')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'total_before_tax')) {
                    $table->decimal('total_before_tax', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('transactions', 'tax_id')) {
                    $table->integer('tax_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'tax_amount')) {
                    $table->decimal('tax_amount', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('transactions', 'discount_type')) {
                    $table->string('discount_type', 20)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'discount_amount')) {
                    $table->decimal('discount_amount', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('transactions', 'shipping_charges')) {
                    $table->decimal('shipping_charges', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('transactions', 'final_total')) {
                    $table->decimal('final_total', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('transactions', 'round_off_amount')) {
                    $table->decimal('round_off_amount', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('transactions', 'additional_expense_key_1')) {
                    $table->string('additional_expense_key_1', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'additional_expense_value_1')) {
                    $table->decimal('additional_expense_value_1', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'additional_expense_key_2')) {
                    $table->string('additional_expense_key_2', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'additional_expense_value_2')) {
                    $table->decimal('additional_expense_value_2', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'additional_expense_key_3')) {
                    $table->string('additional_expense_key_3', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'additional_expense_value_3')) {
                    $table->decimal('additional_expense_value_3', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'additional_expense_key_4')) {
                    $table->string('additional_expense_key_4', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'additional_expense_value_4')) {
                    $table->decimal('additional_expense_value_4', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 20, 3)->nullable();
                }

                // Rewards
                if (! Schema::hasColumn('transactions', 'rp_redeemed')) {
                    $table->integer('rp_redeemed')->default(0);
                }

                if (! Schema::hasColumn('transactions', 'rp_redeemed_amount')) {
                    $table->decimal('rp_redeemed_amount', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('transactions', 'rp_earned')) {
                    $table->integer('rp_earned')->default(0);
                }

                // Shipping
                if (! Schema::hasColumn('transactions', 'shipping_details')) {
                    $table->text('shipping_details')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'shipping_address')) {
                    $table->text('shipping_address')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'delivery_date')) {
                    $table->date('delivery_date')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'shipping_status')) {
                    $table->string('shipping_status', 50)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'delivered_to')) {
                    $table->string('delivered_to', 191)->nullable();
                }

                // Accounting links (external tables — no FK constraints)
                if (! Schema::hasColumn('transactions', 'income_category_id')) {
                    $table->integer('income_category_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'expense_category_id')) {
                    $table->integer('expense_category_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'expense_for')) {
                    $table->integer('expense_for')->nullable();
                }

                // Repair module
                if (! Schema::hasColumn('transactions', 'repair_completed_on')) {
                    $table->dateTime('repair_completed_on')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_warranty_id')) {
                    $table->integer('repair_warranty_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_brand_id')) {
                    $table->integer('repair_brand_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_status_id')) {
                    $table->integer('repair_status_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_model_id')) {
                    $table->integer('repair_model_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_job_sheet_id')) {
                    $table->integer('repair_job_sheet_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_defects')) {
                    $table->text('repair_defects')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_serial_no')) {
                    $table->string('repair_serial_no', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_checklist')) {
                    $table->text('repair_checklist')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_security_pwd')) {
                    $table->string('repair_security_pwd', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_security_pattern')) {
                    $table->string('repair_security_pattern', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_due_date')) {
                    $table->date('repair_due_date')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'repair_device_id')) {
                    $table->integer('repair_device_id')->nullable();
                }

                // Manufacturing module
                if (! Schema::hasColumn('transactions', 'mfg_parent_production_purchase_id')) {
                    $table->integer('mfg_parent_production_purchase_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'mfg_wasted_units')) {
                    $table->decimal('mfg_wasted_units', 20, 4)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'mfg_production_cost')) {
                    $table->decimal('mfg_production_cost', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'mfg_production_cost_type')) {
                    $table->string('mfg_production_cost_type', 50)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'mfg_is_final')) {
                    $table->tinyInteger('mfg_is_final')->nullable();
                }

                // Essentials / HR module
                if (! Schema::hasColumn('transactions', 'essentials_duration')) {
                    $table->decimal('essentials_duration', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'essentials_duration_unit')) {
                    $table->string('essentials_duration_unit', 50)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'essentials_amount_per_unit_duration')) {
                    $table->decimal('essentials_amount_per_unit_duration', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'essentials_allowances')) {
                    $table->text('essentials_allowances')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'essentials_deductions')) {
                    $table->text('essentials_deductions')->nullable();
                }

                // Recurrence
                if (! Schema::hasColumn('transactions', 'is_recurring')) {
                    $table->tinyInteger('is_recurring')->default(0);
                }

                if (! Schema::hasColumn('transactions', 'recur_interval')) {
                    $table->integer('recur_interval')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'recur_interval_type')) {
                    $table->string('recur_interval_type', 50)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'recur_repetitions')) {
                    $table->integer('recur_repetitions')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'recur_stopped_on')) {
                    $table->dateTime('recur_stopped_on')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'recur_parent_id')) {
                    $table->integer('recur_parent_id')->nullable();
                }

                // Misc / links
                if (! Schema::hasColumn('transactions', 'transfer_parent_id')) {
                    $table->integer('transfer_parent_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'return_parent_id')) {
                    $table->integer('return_parent_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'opening_stock_product_id')) {
                    $table->integer('opening_stock_product_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'parent_transaction_id')) {
                    $table->integer('parent_transaction_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'is_parent')) {
                    $table->tinyInteger('is_parent')->default(0);
                }

                if (! Schema::hasColumn('transactions', 'document')) {
                    $table->string('document', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'custom_field_1')) {
                    $table->string('custom_field_1', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'custom_field_2')) {
                    $table->string('custom_field_2', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'custom_field_3')) {
                    $table->string('custom_field_3', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'custom_field_4')) {
                    $table->string('custom_field_4', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'types_of_service_id')) {
                    $table->integer('types_of_service_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'packing_charge')) {
                    $table->decimal('packing_charge', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'packing_charge_type')) {
                    $table->string('packing_charge_type', 20)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'woocommerce_order_id')) {
                    $table->string('woocommerce_order_id', 191)->nullable();
                }

                if (! Schema::hasColumn('transactions', 'selling_price_group_id')) {
                    $table->integer('selling_price_group_id')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'pay_term_number')) {
                    $table->integer('pay_term_number')->nullable();
                }

                if (! Schema::hasColumn('transactions', 'pay_term_type')) {
                    $table->string('pay_term_type', 50)->nullable();
                }

                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('location_id')->references('id')->on('business_locations');
                $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
