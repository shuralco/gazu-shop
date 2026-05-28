<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stand-alone static-content pages (about, delivery, warranty, terms, etc.)
 * managed via Filament. Replaces hardcoded `InfoController::pages()`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('intro')->nullable();
            $table->longText('content_html')->nullable();
            $table->json('sections')->nullable(); // optional structured fallback
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_footer')->default(true);
            $table->boolean('show_in_topbar')->default(false);
            $table->unsignedInteger('sort_order')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_pages');
    }
};
