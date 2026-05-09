<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Composite index for category filtering and sorting
            $table->index(['category_id', 'price', 'created_at'], 'products_category_price_date_idx');
            $table->index(['category_id', 'title'], 'products_category_title_idx');
            $table->index(['category_id', 'id'], 'products_category_id_idx');

            // Individual indexes for common sort fields
            $table->index('price', 'products_price_idx');
            $table->index('is_hit', 'products_is_hit_idx');
            $table->index('is_new', 'products_is_new_idx');
        });

        Schema::table('filter_products', function (Blueprint $table) {
            // Composite index for filter queries
            $table->index(['product_id', 'filter_id', 'filter_group_id'], 'filter_products_composite_idx');
            $table->index(['filter_id', 'filter_group_id'], 'filter_products_filter_group_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            // Index for product reviews pagination
            $table->index(['product_id', 'is_approved', 'created_at'], 'reviews_product_approved_date_idx');
            $table->index(['is_approved', 'created_at'], 'reviews_approved_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_price_date_idx');
            $table->dropIndex('products_category_title_idx');
            $table->dropIndex('products_category_id_idx');
            $table->dropIndex('products_price_idx');
            $table->dropIndex('products_is_hit_idx');
            $table->dropIndex('products_is_new_idx');
        });

        Schema::table('filter_products', function (Blueprint $table) {
            $table->dropIndex('filter_products_composite_idx');
            $table->dropIndex('filter_products_filter_group_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_product_approved_date_idx');
            $table->dropIndex('reviews_approved_date_idx');
        });
    }
};
