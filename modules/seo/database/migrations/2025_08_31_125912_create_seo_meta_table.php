<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();

            // Полиморфні зв'язки
            $table->string('seoable_type')->nullable();
            $table->unsignedBigInteger('seoable_id')->nullable();

            // Тип сторінки
            $table->string('page_type', 100); // product, category, homepage, hits, etc.
            $table->string('url_slug', 255)->unique();

            // Основні SEO поля
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('h1_title', 255)->nullable();
            $table->string('canonical_url', 500)->nullable();

            // Open Graph теги
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image', 500)->nullable();
            $table->string('og_type', 50)->default('website');

            // Twitter Card теги
            $table->string('twitter_title', 255)->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image', 500)->nullable();
            $table->string('twitter_card', 50)->default('summary_large_image');

            // Robots директиви
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('robots_custom', 255)->nullable();

            // Sitemap налаштування
            $table->decimal('priority', 2, 1)->default(0.5);
            $table->enum('changefreq', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->default('weekly');

            // Мова та автогенерація
            $table->string('language', 5)->default('uk');
            $table->boolean('auto_generated')->default(false);
            $table->boolean('is_active')->default(true);

            // Structured Data (JSON)
            $table->json('structured_data')->nullable();

            // Додаткові SEO поля
            $table->json('custom_meta')->nullable(); // Для кастомних мета-тегів
            $table->text('seo_text')->nullable(); // SEO текст для сторінки

            $table->timestamps();

            // Індекси для продуктивності
            $table->index(['seoable_type', 'seoable_id']);
            $table->index(['page_type']);
            $table->index(['url_slug']);
            $table->index(['language']);
            $table->index(['is_active']);
            $table->index(['priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
