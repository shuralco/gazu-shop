<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO landing pages built from filter combinations.
 * Each row = one URL like /lp/filtri-bosch-dlya-bydy
 * which renders catalog filtered by selected filters + custom H1/meta/content.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_landings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('h1')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->text('intro_html')->nullable()->comment('HTML вгорі сторінки (перед списком товарів)');
            $table->text('outro_html')->nullable()->comment('HTML внизу (SEO-текст)');

            // Filter context — selected category + brand + arbitrary filters
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->json('filter_ids')->nullable()->comment('[1, 5, 12] — Filter ids');

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('views_count')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_landings');
    }
};
