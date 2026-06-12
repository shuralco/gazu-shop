<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('help_articles')) {
            return;
        }

        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();           // route key: /admin/help?topic=products
            $table->string('title');
            $table->string('section')->default('Загальне'); // група в сайдбарі
            $table->string('icon')->nullable();          // heroicon, напр. heroicon-o-cube
            $table->longText('content')->nullable();     // Markdown
            $table->string('match_path')->nullable();    // admin-path для контекстної кнопки, напр. products
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['section', 'sort_order']);
            $table->index('match_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_articles');
    }
};
