<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: business.owner_id FK to users.id is added in the users migration
     * to resolve the circular dependency between business ↔ users.
     */
    public function up(): void
    {
        if (! Schema::hasTable('business')) {
            Schema::create('business', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('business', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('business', 'currency_id')) {
                    $table->unsignedInteger('currency_id');
                }

                if (! Schema::hasColumn('business', 'start_date')) {
                    $table->date('start_date')->nullable();
                }

                if (! Schema::hasColumn('business', 'tax_number_1')) {
                    $table->string('tax_number_1', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'tax_label_1')) {
                    $table->string('tax_label_1', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'tax_number_2')) {
                    $table->string('tax_number_2', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'tax_label_2')) {
                    $table->string('tax_label_2', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'code_label_1')) {
                    $table->string('code_label_1', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'code_1')) {
                    $table->string('code_1', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'code_label_2')) {
                    $table->string('code_label_2', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'code_2')) {
                    $table->string('code_2', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'default_sales_tax')) {
                    $table->unsignedInteger('default_sales_tax')->nullable();
                }

                if (! Schema::hasColumn('business', 'default_profit_percent')) {
                    $table->double('default_profit_percent', 5, 2)->default(0.00);
                }

                if (! Schema::hasColumn('business', 'owner_id')) {
                    $table->unsignedInteger('owner_id');
                }

                if (! Schema::hasColumn('business', 'time_zone')) {
                    $table->string('time_zone', 191)->default('Asia/Dhaka');
                }

                if (! Schema::hasColumn('business', 'fy_start_month')) {
                    $table->tinyInteger('fy_start_month')->default(1);
                }

                if (! Schema::hasColumn('business', 'accounting_method')) {
                    $table->enum('accounting_method', ['fifo', 'lifo', 'avco'])->default('fifo');
                }

                if (! Schema::hasColumn('business', 'default_sales_discount')) {
                    $table->decimal('default_sales_discount', 5, 2)->nullable();
                }

                if (! Schema::hasColumn('business', 'sell_price_tax')) {
                    $table->enum('sell_price_tax', ['includes', 'excludes'])->default('includes');
                }

                if (! Schema::hasColumn('business', 'logo')) {
                    $table->string('logo', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'sku_prefix')) {
                    $table->string('sku_prefix', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'enable_product_expiry')) {
                    $table->tinyInteger('enable_product_expiry')->default(0);
                }

                if (! Schema::hasColumn('business', 'expiry_type')) {
                    $table->enum('expiry_type', ['add_expiry', 'add_manufacturing'])->default('add_expiry');
                }

                if (! Schema::hasColumn('business', 'on_product_expiry')) {
                    $table->enum('on_product_expiry', ['keep_selling', 'stop_selling', 'auto_delete'])->default('keep_selling');
                }

                if (! Schema::hasColumn('business', 'stop_selling_before')) {
                    $table->integer('stop_selling_before')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_tooltip')) {
                    $table->tinyInteger('enable_tooltip')->default(1);
                }

                if (! Schema::hasColumn('business', 'purchase_in_diff_currency')) {
                    $table->tinyInteger('purchase_in_diff_currency')->default(0);
                }

                if (! Schema::hasColumn('business', 'purchase_currency_id')) {
                    $table->unsignedInteger('purchase_currency_id')->nullable();
                }

                if (! Schema::hasColumn('business', 'p_exchange_rate')) {
                    $table->decimal('p_exchange_rate', 20, 3)->default(1.000);
                }

                if (! Schema::hasColumn('business', 'transaction_edit_days')) {
                    $table->unsignedInteger('transaction_edit_days')->default(30);
                }

                if (! Schema::hasColumn('business', 'stock_expiry_alert_days')) {
                    $table->unsignedInteger('stock_expiry_alert_days')->default(30);
                }

                if (! Schema::hasColumn('business', 'keyboard_shortcuts')) {
                    $table->text('keyboard_shortcuts')->nullable();
                }

                if (! Schema::hasColumn('business', 'pos_settings')) {
                    $table->text('pos_settings')->nullable();
                }

                if (! Schema::hasColumn('business', 'manufacturing_settings')) {
                    $table->text('manufacturing_settings')->nullable();
                }

                if (! Schema::hasColumn('business', 'essentials_settings')) {
                    $table->text('essentials_settings')->nullable();
                }
                if (! Schema::hasColumn('business', 'woocommerce_api_settings')) {
                    $table->text('woocommerce_api_settings')->nullable();
                }
                if (! Schema::hasColumn('business', 'woocommerce_skipped_orders')) {
                    $table->text('woocommerce_skipped_orders')->nullable();
                }
                if (! Schema::hasColumn('business', 'woocommerce_wh_oc_secret')) {
                    $table->string('woocommerce_wh_oc_secret')->nullable();
                }
                if (! Schema::hasColumn('business', 'woocommerce_wh_ou_secret')) {
                    $table->string('woocommerce_wh_ou_secret')->nullable();
                }
                if (! Schema::hasColumn('business', 'woocommerce_wh_od_secret')) {
                    $table->string('woocommerce_wh_od_secret')->nullable();
                }
                if (! Schema::hasColumn('business', 'woocommerce_wh_or_secret')) {
                    $table->string('woocommerce_wh_or_secret')->nullable();
                }

                if (! Schema::hasColumn('business', 'weighing_scale_setting')) {
                    $table->text('weighing_scale_setting')->nullable();
                }

                if (! Schema::hasColumn('business', 'email_settings')) {
                    $table->text('email_settings')->nullable();
                }

                if (! Schema::hasColumn('business', 'sms_settings')) {
                    $table->text('sms_settings')->nullable();
                }

                if (! Schema::hasColumn('business', 'custom_labels')) {
                    $table->text('custom_labels')->nullable();
                }

                if (! Schema::hasColumn('business', 'common_settings')) {
                    $table->text('common_settings')->nullable();
                }

                if (! Schema::hasColumn('business', 'db_connection')) {
                    $table->text('db_connection')->nullable();
                }

                if (! Schema::hasColumn('business', 'storage_location')) {
                    $table->text('storage_location')->nullable();
                }

                if (! Schema::hasColumn('business', 'crm_settings')) {
                    $table->text('crm_settings')->nullable();
                }

                if (! Schema::hasColumn('business', 'repair_settings')) {
                    $table->text('repair_settings')->nullable();
                }

                if (! Schema::hasColumn('business', 'ref_no_prefixes')) {
                    $table->text('ref_no_prefixes')->nullable();
                }

                if (! Schema::hasColumn('business', 'enabled_modules')) {
                    $table->text('enabled_modules')->nullable();
                }

                if (! Schema::hasColumn('business', 'enable_brand')) {
                    $table->tinyInteger('enable_brand')->default(1);
                }

                if (! Schema::hasColumn('business', 'enable_category')) {
                    $table->tinyInteger('enable_category')->default(1);
                }

                if (! Schema::hasColumn('business', 'enable_sub_category')) {
                    $table->tinyInteger('enable_sub_category')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_price_tax')) {
                    $table->tinyInteger('enable_price_tax')->default(1);
                }

                if (! Schema::hasColumn('business', 'enable_purchase_status')) {
                    $table->tinyInteger('enable_purchase_status')->default(1);
                }

                if (! Schema::hasColumn('business', 'enable_lot_number')) {
                    $table->tinyInteger('enable_lot_number')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_sub_units')) {
                    $table->tinyInteger('enable_sub_units')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_racks')) {
                    $table->tinyInteger('enable_racks')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_row')) {
                    $table->tinyInteger('enable_row')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_position')) {
                    $table->tinyInteger('enable_position')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_editing_product_from_purchase')) {
                    $table->tinyInteger('enable_editing_product_from_purchase')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_inline_tax')) {
                    $table->tinyInteger('enable_inline_tax')->default(0);
                }

                if (! Schema::hasColumn('business', 'enable_rp')) {
                    $table->tinyInteger('enable_rp')->default(0);
                }

                if (! Schema::hasColumn('business', 'default_unit')) {
                    $table->integer('default_unit')->nullable();
                }

                if (! Schema::hasColumn('business', 'sales_cmsn_agnt')) {
                    $table->enum('sales_cmsn_agnt', ['logged_in_user', 'user', 'cmsn_agnt'])->nullable();
                }

                if (! Schema::hasColumn('business', 'item_addition_method')) {
                    $table->tinyInteger('item_addition_method')->default(1);
                }

                if (! Schema::hasColumn('business', 'currency_symbol_placement')) {
                    $table->enum('currency_symbol_placement', ['before', 'after'])->default('before');
                }

                if (! Schema::hasColumn('business', 'date_format')) {
                    $table->string('date_format', 191)->default('m/d/Y');
                }

                if (! Schema::hasColumn('business', 'time_format')) {
                    $table->enum('time_format', ['12', '24'])->default('24');
                }

                if (! Schema::hasColumn('business', 'currency_precision')) {
                    $table->tinyInteger('currency_precision')->default(2);
                }

                if (! Schema::hasColumn('business', 'quantity_precision')) {
                    $table->tinyInteger('quantity_precision')->default(2);
                }

                if (! Schema::hasColumn('business', 'theme_color')) {
                    $table->char('theme_color', 20)->nullable();
                }

                if (! Schema::hasColumn('business', 'created_by')) {
                    $table->integer('created_by')->nullable();
                }

                // Reward points configuration
                if (! Schema::hasColumn('business', 'rp_name')) {
                    $table->string('rp_name', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'amount_for_unit_rp')) {
                    $table->decimal('amount_for_unit_rp', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('business', 'min_order_total_for_rp')) {
                    $table->decimal('min_order_total_for_rp', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('business', 'max_rp_per_order')) {
                    $table->integer('max_rp_per_order')->nullable();
                }

                if (! Schema::hasColumn('business', 'redeem_amount_per_unit_rp')) {
                    $table->decimal('redeem_amount_per_unit_rp', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('business', 'min_order_total_for_redeem')) {
                    $table->decimal('min_order_total_for_redeem', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('business', 'min_redeem_point')) {
                    $table->integer('min_redeem_point')->nullable();
                }

                if (! Schema::hasColumn('business', 'max_redeem_point')) {
                    $table->integer('max_redeem_point')->nullable();
                }

                if (! Schema::hasColumn('business', 'rp_expiry_period')) {
                    $table->integer('rp_expiry_period')->nullable();
                }

                if (! Schema::hasColumn('business', 'rp_expiry_type')) {
                    $table->string('rp_expiry_type', 191)->nullable();
                }

                if (! Schema::hasColumn('business', 'is_active')) {
                    $table->tinyInteger('is_active')->default(1);
                }

                $table->timestamps();

                $table->foreign('currency_id')->references('id')->on('currencies');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business');
    }
};
