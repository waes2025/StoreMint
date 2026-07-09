<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Homepage banner/carousel slides. Used by HeroSlide model.
     */
    public function up(): void
    {
        if (! Schema::hasTable('hero_slides')) {
            Schema::create('hero_slides', function (Blueprint $table) {
                $table->id();

                if (! Schema::hasColumn('hero_slides', 'image')) {
                    $table->string('image');
                }

                if (! Schema::hasColumn('hero_slides', 'link')) {
                    $table->string('link')->nullable();
                }

                if (! Schema::hasColumn('hero_slides', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0);
                }

                if (! Schema::hasColumn('hero_slides', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }

                $table->timestamps();

                $table->index(['is_active', 'sort_order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
