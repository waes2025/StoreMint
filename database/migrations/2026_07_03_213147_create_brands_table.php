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
        if (! Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('brands', 'business_id')) {
                    $table->unsignedInteger('business_id');
                }

                if (! Schema::hasColumn('brands', 'name')) {
                    $table->string('name', 191);
                }

                if (! Schema::hasColumn('brands', 'description')) {
                    $table->text('description')->nullable();
                }

                if (! Schema::hasColumn('brands', 'created_by')) {
                    $table->unsignedInteger('created_by');
                }

                if (! Schema::hasColumn('brands', 'slug')) {
                    $table->string('slug', 191)->nullable();
                }

                if (! Schema::hasColumn('brands', 'image')) {
                    $table->string('image', 191)->nullable();
                }

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
