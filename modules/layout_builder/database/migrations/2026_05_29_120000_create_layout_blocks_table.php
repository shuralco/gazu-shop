<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * layout_blocks — OpenCart-стиль призначення блоків у іменовані зони
 * storefront. Кожен рядок = "render цей блок у цій зоні з таким sort".
 *
 *   zone        — назва зони (layout.home.top, layout.home.bottom, product.sidebar)
 *   type        — html | banner | featured
 *   title       — внутрішня назва (admin) + optional заголовок блоку
 *   content     — HTML-вміст (для type=html) або текст
 *   config      — json: для banner (image_url, link_url, alt), featured (limit, source)
 *   sort_order  — порядок у межах зони (ASC)
 *   is_active   — гейт рендеру
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('layout_blocks')) {
            return;
        }

        Schema::create('layout_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('zone')->index();
            $table->string('type')->default('html'); // html | banner | featured
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->json('config')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['zone', 'is_active', 'sort_order'], 'layout_blocks_zone_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layout_blocks');
    }
};
