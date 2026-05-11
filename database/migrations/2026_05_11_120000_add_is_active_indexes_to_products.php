<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active', 'products_is_active_index');
            $table->index(['is_active', 'category_id'], 'products_active_category_index');
            $table->index(['is_active', 'manufacturer'], 'products_active_manufacturer_index');
            $table->index(['is_active', 'rating'], 'products_active_rating_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_is_active_index');
            $table->dropIndex('products_active_category_index');
            $table->dropIndex('products_active_manufacturer_index');
            $table->dropIndex('products_active_rating_index');
        });
    }
};
