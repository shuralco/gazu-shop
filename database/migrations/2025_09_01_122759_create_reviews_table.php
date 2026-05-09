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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('author_name');
            $table->string('author_email')->nullable();
            $table->tinyInteger('rating')->unsigned()->comment('1-5 stars');
            $table->text('comment');
            $table->boolean('is_verified')->default(false)->comment('Verified purchase');
            $table->boolean('is_approved')->default(true)->comment('Admin moderation');
            $table->timestamps();

            $table->index(['product_id', 'is_approved']);
            $table->index(['rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
