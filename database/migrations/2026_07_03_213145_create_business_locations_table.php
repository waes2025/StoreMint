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
        if (! Schema::hasTable('business_locations')) {
            Schema::create('business_locations', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('business_locations', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('business_locations', 'location_id')) {
                    $table->string('location_id', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'name')) {
                    $table->string('name', 256);
                }

                if (! Schema::hasColumn('business_locations', 'landmark')) {
                    $table->text('landmark')->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'country')) {
                    $table->string('country', 100);
                }

                if (! Schema::hasColumn('business_locations', 'state')) {
                    $table->string('state', 100);
                }

                if (! Schema::hasColumn('business_locations', 'city')) {
                    $table->string('city', 100);
                }

                if (! Schema::hasColumn('business_locations', 'zip_code')) {
                    $table->char('zip_code', 7);
                }

                if (! Schema::hasColumn('business_locations', 'invoice_scheme_id')) {
                    $table->unsignedInteger('invoice_scheme_id');
                }

                if (! Schema::hasColumn('business_locations', 'invoice_layout_id')) {
                    $table->unsignedInteger('invoice_layout_id');
                }

                if (! Schema::hasColumn('business_locations', 'sale_invoice_layout_id')) {
                    $table->integer('sale_invoice_layout_id')->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'selling_price_group_id')) {
                    $table->integer('selling_price_group_id')->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'printer_id')) {
                    $table->integer('printer_id')->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'print_receipt_on_invoice')) {
                    $table->tinyInteger('print_receipt_on_invoice')->nullable()->default(1);
                }

                if (! Schema::hasColumn('business_locations', 'receipt_printer_type')) {
                    $table->enum('receipt_printer_type', ['browser', 'printer'])->default('browser');
                }

                if (! Schema::hasColumn('business_locations', 'mobile')) {
                    $table->string('mobile', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'alternate_number')) {
                    $table->string('alternate_number', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'email')) {
                    $table->string('email', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'website')) {
                    $table->string('website', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'featured_products')) {
                    $table->text('featured_products')->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'default_payment_accounts')) {
                    $table->text('default_payment_accounts')->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'custom_field1')) {
                    $table->string('custom_field1', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'custom_field2')) {
                    $table->string('custom_field2', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'custom_field3')) {
                    $table->string('custom_field3', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'custom_field4')) {
                    $table->string('custom_field4', 191)->nullable();
                }

                if (! Schema::hasColumn('business_locations', 'is_active')) {
                    $table->tinyInteger('is_active')->default(1);
                }

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_locations');
    }
};
