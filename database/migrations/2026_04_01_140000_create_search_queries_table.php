<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query', 255);
            $table->string('normalized_query', 255)->index();
            $table->integer('results_count')->default(0);
            $table->integer('search_count')->default(1);
            $table->integer('click_count')->default(0);
            $table->timestamp('last_searched_at')->nullable();
            $table->timestamps();
            $table->unique('normalized_query');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
