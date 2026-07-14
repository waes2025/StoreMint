<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: contacts.created_by FK to users.id is added in the users migration
     * to resolve the circular dependency between contacts ↔ users.
     */
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('contacts', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('contacts', 'type')) {
                    $table->string('type', 50)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'supplier_business_name')) {
                    $table->string('supplier_business_name', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'name')) {
                    $table->string('name', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'prefix')) {
                    $table->string('prefix', 50)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'first_name')) {
                    $table->string('first_name', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'middle_name')) {
                    $table->string('middle_name', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'last_name')) {
                    $table->string('last_name', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'email')) {
                    $table->string('email', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'contact_id')) {
                    $table->string('contact_id', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'contact_status')) {
                    $table->string('contact_status', 50)->default('active');
                }

                if (! Schema::hasColumn('contacts', 'tax_number')) {
                    $table->string('tax_number', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'city')) {
                    $table->string('city', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'state')) {
                    $table->string('state', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'country')) {
                    $table->string('country', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'address_line_1')) {
                    $table->string('address_line_1', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'address_line_2')) {
                    $table->string('address_line_2', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'zip_code')) {
                    $table->string('zip_code', 20)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'dob')) {
                    $table->date('dob')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'mobile')) {
                    $table->string('mobile', 191);
                }

                if (! Schema::hasColumn('contacts', 'landline')) {
                    $table->string('landline', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'alternate_number')) {
                    $table->string('alternate_number', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'pay_term_number')) {
                    $table->integer('pay_term_number')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'pay_term_type')) {
                    $table->string('pay_term_type', 50)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'credit_limit')) {
                    $table->decimal('credit_limit', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'created_by')) {
                    $table->unsignedInteger('created_by')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'converted_by')) {
                    $table->unsignedInteger('converted_by')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'converted_on')) {
                    $table->date('converted_on')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'balance')) {
                    $table->decimal('balance', 20, 2)->default(0);
                }

                // Reward points
                if (! Schema::hasColumn('contacts', 'total_rp')) {
                    $table->integer('total_rp')->default(0);
                }

                if (! Schema::hasColumn('contacts', 'total_rp_used')) {
                    $table->integer('total_rp_used')->default(0);
                }

                if (! Schema::hasColumn('contacts', 'total_rp_expired')) {
                    $table->integer('total_rp_expired')->default(0);
                }

                if (! Schema::hasColumn('contacts', 'is_default')) {
                    $table->tinyInteger('is_default')->default(0);
                }

                // Shipping
                if (! Schema::hasColumn('contacts', 'shipping_address')) {
                    $table->string('shipping_address', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'shipping_city')) {
                    $table->string('shipping_city', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'shipping_state')) {
                    $table->string('shipping_state', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'shipping_country')) {
                    $table->string('shipping_country', 191)->nullable();
                }

                // Custom Shipping
                if (! Schema::hasColumn('contacts', 'shipping_zip_code')) {
                    $table->string('shipping_zip_code', 20)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'shipping_custom_field_details')) {
                    $table->longText('shipping_custom_field_details')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'is_export')) {
                    $table->boolean('is_export')->default(false);
                }

                if (! Schema::hasColumn('contacts', 'export_custom_field_1')) {
                    $table->string('export_custom_field_1')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'export_custom_field_2')) {
                    $table->string('export_custom_field_2')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'export_custom_field_3')) {
                    $table->string('export_custom_field_3')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'export_custom_field_4')) {
                    $table->string('export_custom_field_4')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'export_custom_field_5')) {
                    $table->string('export_custom_field_5')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'export_custom_field_6')) {
                    $table->string('export_custom_field_6')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'position')) {
                    $table->string('position')->nullable();
                }

                // CRM
                if (! Schema::hasColumn('contacts', 'customer_group_id')) {
                    $table->integer('customer_group_id')->nullable();
                }

                if (! Schema::hasColumn('contacts', 'crm_source')) {
                    $table->string('crm_source', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'crm_life_stage')) {
                    $table->string('crm_life_stage', 191)->nullable();
                }

                // Custom fields
                if (! Schema::hasColumn('contacts', 'custom_field1')) {
                    $table->string('custom_field1', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field2')) {
                    $table->string('custom_field2', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field3')) {
                    $table->string('custom_field3', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field4')) {
                    $table->string('custom_field4', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field5')) {
                    $table->string('custom_field5', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field6')) {
                    $table->string('custom_field6', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field7')) {
                    $table->string('custom_field7', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field8')) {
                    $table->string('custom_field8', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field9')) {
                    $table->string('custom_field9', 191)->nullable();
                }

                if (! Schema::hasColumn('contacts', 'custom_field10')) {
                    $table->string('custom_field10', 191)->nullable();
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
        Schema::dropIfExists('contacts');
    }
};
