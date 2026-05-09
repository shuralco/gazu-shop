<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // products, enrichment, seo, translation, tags
            $table->string('provider')->nullable(); // openai, anthropic, manual
            $table->string('model')->nullable();
            $table->text('prompt')->nullable();
            $table->longText('response')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->integer('products_created')->default(0);
            $table->integer('products_updated')->default(0);
            $table->json('errors')->nullable();
            $table->string('status'); // success, error, pending
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_logs');
    }
};
