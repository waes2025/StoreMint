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
        // 1. purchase_lines
        if (Schema::hasTable('purchase_lines')) {
            Schema::table('purchase_lines', function (Blueprint $table) {
                if (! Schema::hasColumn('purchase_lines', 'id')) {
                    $table->increments('id');
                }
                if (! Schema::hasColumn('purchase_lines', 'transaction_id')) {
                    $table->integer('transaction_id')->unsigned();
                }
                if (! Schema::hasColumn('purchase_lines', 'product_id')) {
                    $table->integer('product_id')->unsigned();
                }
                if (! Schema::hasColumn('purchase_lines', 'variation_id')) {
                    $table->integer('variation_id')->unsigned();
                }
                if (! Schema::hasColumn('purchase_lines', 'quantity')) {
                    $table->decimal('quantity', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('purchase_lines', 'secondary_unit_quantity')) {
                    $table->decimal('secondary_unit_quantity', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('purchase_lines', 'pp_without_discount')) {
                    $table->decimal('pp_without_discount', 22, 4)->default(0.0000)->comment('Purchase price before inline discounts');
                }
                if (! Schema::hasColumn('purchase_lines', 'discount_percent')) {
                    $table->decimal('discount_percent', 5, 2)->default(0.00)->comment('Inline discount percentage');
                }
                if (! Schema::hasColumn('purchase_lines', 'purchase_price')) {
                    $table->decimal('purchase_price', 22, 4);
                }
                if (! Schema::hasColumn('purchase_lines', 'purchase_price_inc_tax')) {
                    $table->decimal('purchase_price_inc_tax', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('purchase_lines', 'item_tax')) {
                    $table->decimal('item_tax', 22, 4)->comment('Tax for one quantity');
                }
                if (! Schema::hasColumn('purchase_lines', 'tax_id')) {
                    $table->integer('tax_id')->unsigned()->nullable();
                }
                if (! Schema::hasColumn('purchase_lines', 'purchase_requisition_line_id')) {
                    $table->integer('purchase_requisition_line_id')->nullable();
                }
                if (! Schema::hasColumn('purchase_lines', 'purchase_order_line_id')) {
                    $table->integer('purchase_order_line_id')->nullable();
                }
                if (! Schema::hasColumn('purchase_lines', 'quantity_sold')) {
                    $table->decimal('quantity_sold', 22, 4)->default(0.0000)->comment('Quanity sold from this purchase line');
                }
                if (! Schema::hasColumn('purchase_lines', 'quantity_adjusted')) {
                    $table->decimal('quantity_adjusted', 22, 4)->default(0.0000)->comment('Quanity adjusted in stock adjustment from this purchase line');
                }
                if (! Schema::hasColumn('purchase_lines', 'quantity_returned')) {
                    $table->decimal('quantity_returned', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('purchase_lines', 'po_quantity_purchased')) {
                    $table->decimal('po_quantity_purchased', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('purchase_lines', 'mfg_quantity_used')) {
                    $table->decimal('mfg_quantity_used', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('purchase_lines', 'mfg_date')) {
                    $table->date('mfg_date')->nullable();
                }
                if (! Schema::hasColumn('purchase_lines', 'exp_date')) {
                    $table->date('exp_date')->nullable();
                }
                if (! Schema::hasColumn('purchase_lines', 'lot_number')) {
                    $table->string('lot_number', 191)->nullable();
                }
                if (! Schema::hasColumn('purchase_lines', 'sub_unit_id')) {
                    $table->integer('sub_unit_id')->nullable();
                }
                if (! Schema::hasColumn('purchase_lines', 'created_at') || ! Schema::hasColumn('purchase_lines', 'updated_at')) {
                    $table->timestamps();
                }
            });
        } else {
            Schema::create('purchase_lines', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('transaction_id')->unsigned();
                $table->integer('product_id')->unsigned();
                $table->integer('variation_id')->unsigned();
                $table->decimal('quantity', 22, 4)->default(0.0000);
                $table->decimal('secondary_unit_quantity', 22, 4)->default(0.0000);
                $table->decimal('pp_without_discount', 22, 4)->default(0.0000)->comment('Purchase price before inline discounts');
                $table->decimal('discount_percent', 5, 2)->default(0.00)->comment('Inline discount percentage');
                $table->decimal('purchase_price', 22, 4);
                $table->decimal('purchase_price_inc_tax', 22, 4)->default(0.0000);
                $table->decimal('item_tax', 22, 4)->comment('Tax for one quantity');
                $table->integer('tax_id')->unsigned()->nullable();
                $table->integer('purchase_requisition_line_id')->nullable();
                $table->integer('purchase_order_line_id')->nullable();
                $table->decimal('quantity_sold', 22, 4)->default(0.0000)->comment('Quanity sold from this purchase line');
                $table->decimal('quantity_adjusted', 22, 4)->default(0.0000)->comment('Quanity adjusted in stock adjustment from this purchase line');
                $table->decimal('quantity_returned', 22, 4)->default(0.0000);
                $table->decimal('po_quantity_purchased', 22, 4)->default(0.0000);
                $table->decimal('mfg_quantity_used', 22, 4)->default(0.0000);
                $table->date('mfg_date')->nullable();
                $table->date('exp_date')->nullable();
                $table->string('lot_number', 191)->nullable();
                $table->integer('sub_unit_id')->nullable();
                $table->timestamps();
            });
        }

        // 2. transaction_sell_lines
        if (Schema::hasTable('transaction_sell_lines')) {
            Schema::table('transaction_sell_lines', function (Blueprint $table) {
                if (! Schema::hasColumn('transaction_sell_lines', 'id')) {
                    $table->increments('id');
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'transaction_id')) {
                    $table->integer('transaction_id')->unsigned();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'product_id')) {
                    $table->integer('product_id')->unsigned();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'variation_id')) {
                    $table->integer('variation_id')->unsigned();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'quantity')) {
                    $table->decimal('quantity', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'mfg_waste_percent')) {
                    $table->decimal('mfg_waste_percent', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'mfg_ingredient_group_id')) {
                    $table->integer('mfg_ingredient_group_id')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'secondary_unit_quantity')) {
                    $table->decimal('secondary_unit_quantity', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'quantity_returned')) {
                    $table->decimal('quantity_returned', 20, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'unit_price_before_discount')) {
                    $table->decimal('unit_price_before_discount', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'unit_price')) {
                    $table->decimal('unit_price', 22, 4)->nullable()->comment('Sell price excluding tax');
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'line_discount_type')) {
                    $table->enum('line_discount_type', ['fixed', 'percentage'])->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'line_discount_amount')) {
                    $table->decimal('line_discount_amount', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'unit_price_inc_tax')) {
                    $table->decimal('unit_price_inc_tax', 22, 4)->nullable()->comment('Sell price including tax');
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'item_tax')) {
                    $table->decimal('item_tax', 22, 4)->comment('Tax for one quantity');
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'tax_id')) {
                    $table->integer('tax_id')->unsigned()->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'discount_id')) {
                    $table->integer('discount_id')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'lot_no_line_id')) {
                    $table->integer('lot_no_line_id')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'sell_line_note')) {
                    $table->text('sell_line_note')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'woocommerce_line_items_id')) {
                    $table->integer('woocommerce_line_items_id')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'so_line_id')) {
                    $table->integer('so_line_id')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'so_quantity_invoiced')) {
                    $table->decimal('so_quantity_invoiced', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'res_service_staff_id')) {
                    $table->integer('res_service_staff_id')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'res_line_order_status')) {
                    $table->string('res_line_order_status', 191)->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'parent_sell_line_id')) {
                    $table->integer('parent_sell_line_id')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'children_type')) {
                    $table->string('children_type', 191)->default('')->comment('Type of children for the parent, like modifier or combo');
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'sub_unit_id')) {
                    $table->integer('sub_unit_id')->nullable();
                }
                if (! Schema::hasColumn('transaction_sell_lines', 'created_at') || ! Schema::hasColumn('transaction_sell_lines', 'updated_at')) {
                    $table->timestamps();
                }
            });
        } else {
            Schema::create('transaction_sell_lines', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('transaction_id')->unsigned();
                $table->integer('product_id')->unsigned();
                $table->integer('variation_id')->unsigned();
                $table->decimal('quantity', 22, 4)->default(0.0000);
                $table->decimal('mfg_waste_percent', 22, 4)->default(0.0000);
                $table->integer('mfg_ingredient_group_id')->nullable();
                $table->decimal('secondary_unit_quantity', 22, 4)->default(0.0000);
                $table->decimal('quantity_returned', 20, 4)->default(0.0000);
                $table->decimal('unit_price_before_discount', 22, 4)->default(0.0000);
                $table->decimal('unit_price', 22, 4)->nullable()->comment('Sell price excluding tax');
                $table->enum('line_discount_type', ['fixed', 'percentage'])->nullable();
                $table->decimal('line_discount_amount', 22, 4)->default(0.0000);
                $table->decimal('unit_price_inc_tax', 22, 4)->nullable()->comment('Sell price including tax');
                $table->decimal('item_tax', 22, 4)->comment('Tax for one quantity');
                $table->integer('tax_id')->unsigned()->nullable();
                $table->integer('discount_id')->nullable();
                $table->integer('lot_no_line_id')->nullable();
                $table->text('sell_line_note')->nullable();
                $table->integer('woocommerce_line_items_id')->nullable();
                $table->integer('so_line_id')->nullable();
                $table->decimal('so_quantity_invoiced', 22, 4)->default(0.0000);
                $table->integer('res_service_staff_id')->nullable();
                $table->string('res_line_order_status', 191)->nullable();
                $table->integer('parent_sell_line_id')->nullable();
                $table->string('children_type', 191)->default('')->comment('Type of children for the parent, like modifier or combo');
                $table->integer('sub_unit_id')->nullable();
                $table->timestamps();
            });
        }

        // 3. transaction_sell_lines_purchase_lines
        if (Schema::hasTable('transaction_sell_lines_purchase_lines')) {
            Schema::table('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
                if (! Schema::hasColumn('transaction_sell_lines_purchase_lines', 'id')) {
                    $table->increments('id');
                }
                if (! Schema::hasColumn('transaction_sell_lines_purchase_lines', 'sell_line_id')) {
                    $table->integer('sell_line_id')->unsigned()->nullable()->comment('id from transaction_sell_lines');
                }
                if (! Schema::hasColumn('transaction_sell_lines_purchase_lines', 'stock_adjustment_line_id')) {
                    $table->integer('stock_adjustment_line_id')->unsigned()->nullable()->comment('id from stock_adjustment_lines');
                }
                if (! Schema::hasColumn('transaction_sell_lines_purchase_lines', 'purchase_line_id')) {
                    $table->integer('purchase_line_id')->unsigned()->nullable()->comment('id from purchase_lines');
                }
                if (! Schema::hasColumn('transaction_sell_lines_purchase_lines', 'quantity')) {
                    $table->decimal('quantity', 22, 4);
                }
                if (! Schema::hasColumn('transaction_sell_lines_purchase_lines', 'qty_returned')) {
                    $table->decimal('qty_returned', 22, 4)->default(0.0000);
                }
                if (! Schema::hasColumn('transaction_sell_lines_purchase_lines', 'created_at') || ! Schema::hasColumn('transaction_sell_lines_purchase_lines', 'updated_at')) {
                    $table->timestamps();
                }
            });
        } else {
            Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sell_line_id')->unsigned()->nullable()->comment('id from transaction_sell_lines');
                $table->integer('stock_adjustment_line_id')->unsigned()->nullable()->comment('id from stock_adjustment_lines');
                $table->integer('purchase_line_id')->unsigned()->nullable()->comment('id from purchase_lines');
                $table->decimal('quantity', 22, 4);
                $table->decimal('qty_returned', 22, 4)->default(0.0000);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_sell_lines_purchase_lines');
        Schema::dropIfExists('transaction_sell_lines');
        Schema::dropIfExists('purchase_lines');
    }
};
