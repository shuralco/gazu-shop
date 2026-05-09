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
        // Critical foreign key indexes for performance optimization

        // Order products table indexes
        Schema::table('order_products', function (Blueprint $table) {
            $table->index('order_id', 'idx_order_products_order_id');
            $table->index('product_id', 'idx_order_products_product_id');
        });

        // Filter products pivot table indexes
        Schema::table('filter_products', function (Blueprint $table) {
            $table->index('filter_id', 'idx_filter_products_filter_id');
            $table->index('product_id', 'idx_filter_products_product_id');
            $table->index(['product_id', 'filter_group_id'], 'idx_filter_products_composite');
        });

        // Reviews table indexes
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('user_id', 'idx_reviews_user_id');
            $table->index('product_id', 'idx_reviews_product_id');
            $table->index(['product_id', 'is_approved'], 'idx_reviews_approved');
            $table->index('rating', 'idx_reviews_rating');
        });

        // SEO meta table indexes
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->index(['seoable_type', 'seoable_id'], 'idx_seo_meta_seoable');
        });

        // Category filters pivot table indexes
        Schema::table('category_filters', function (Blueprint $table) {
            $table->index('category_id', 'idx_category_filters_category_id');
            $table->index('filter_group_id', 'idx_category_filters_filter_group_id');
        });

        // Additional performance indexes for common queries
        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'is_active'], 'idx_products_category_active');
            $table->index(['brand_id', 'is_active'], 'idx_products_brand_active');
            $table->index(['is_hit', 'is_active'], 'idx_products_hit_active');
            $table->index(['is_new', 'is_active'], 'idx_products_new_active');
        });

        // Orders performance indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index(['status', 'created_at'], 'idx_orders_status_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_status_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_category_active');
            $table->dropIndex('idx_products_brand_active');
            $table->dropIndex('idx_products_hit_active');
            $table->dropIndex('idx_products_new_active');
        });

        Schema::table('category_filters', function (Blueprint $table) {
            $table->dropIndex('idx_category_filters_category_id');
            $table->dropIndex('idx_category_filters_filter_group_id');
        });

        Schema::table('seo_meta', function (Blueprint $table) {
            $table->dropIndex('idx_seo_meta_seoable');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_user_id');
            $table->dropIndex('idx_reviews_product_id');
            $table->dropIndex('idx_reviews_approved');
            $table->dropIndex('idx_reviews_rating');
        });

        Schema::table('filter_products', function (Blueprint $table) {
            $table->dropIndex('idx_filter_products_filter_id');
            $table->dropIndex('idx_filter_products_product_id');
            $table->dropIndex('idx_filter_products_composite');
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->dropIndex('idx_order_products_order_id');
            $table->dropIndex('idx_order_products_product_id');
        });
    }
};
