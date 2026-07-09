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
        if (! Schema::hasTable('types_of_services')) {
            Schema::create('types_of_services', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('types_of_services', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('types_of_services', 'description')) {
                    $table->text('description')->nullable();
                }

                if (! Schema::hasColumn('types_of_services', 'business_id')) {
                    $table->integer('business_id')->nullable();
                }

                if (! Schema::hasColumn('types_of_services', 'location_price_group')) {
                    $table->text('location_price_group')->nullable();
                }

                if (! Schema::hasColumn('types_of_services', 'packing_charge')) {
                    $table->decimal('packing_charge', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('types_of_services', 'packing_charge_type')) {
                    $table->string('packing_charge_type', 20)->nullable();
                }

                if (! Schema::hasColumn('types_of_services', 'enable_custom_fields')) {
                    $table->tinyInteger('enable_custom_fields')->default(0);
                }

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_of_services');
    }
};
