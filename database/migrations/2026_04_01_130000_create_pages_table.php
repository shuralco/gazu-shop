<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('content')->nullable();
            $table->json('excerpt')->nullable();

            // SEO fields (translatable)
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();

            // SEO directives
            $table->boolean('is_indexable')->default(true);
            $table->boolean('is_followable')->default(true);
            $table->string('robots_custom')->nullable();

            // Page settings
            $table->string('template')->default('default');
            $table->string('layout')->default('full');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_menu')->default(false);
            $table->boolean('show_in_footer')->default(false);
            $table->string('menu_group')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('icon')->nullable();

            // Open Graph
            $table->string('og_image')->nullable();
            $table->string('og_type')->default('article');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
