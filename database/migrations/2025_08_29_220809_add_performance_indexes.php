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
        Schema::table('products', function (Blueprint $table) {
            // Індекси для фільтрації на головній сторінці
            $table->index(['is_hit'], 'idx_products_is_hit');
            $table->index(['is_new'], 'idx_products_is_new');

            // Індекси для сортування та фільтрації
            $table->index(['price'], 'idx_products_price');
            $table->index(['category_id', 'price'], 'idx_products_category_price');
            $table->index(['category_id', 'is_hit'], 'idx_products_category_hit');
            $table->index(['category_id', 'is_new'], 'idx_products_category_new');

            // Fulltext індекс для пошуку (тільки для MySQL)
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'content'], 'idx_products_fulltext');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            // Індекс для ієрархічних запитів
            $table->index(['parent_id'], 'idx_categories_parent');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Індекси для швидкого пошуку замовлень користувача
            $table->index(['user_id', 'created_at'], 'idx_orders_user_date');
            $table->index(['created_at'], 'idx_orders_date');
            $table->index(['status'], 'idx_orders_status');
        });

        Schema::table('filter_products', function (Blueprint $table) {
            // Індекси для фільтрації товарів
            $table->index(['product_id'], 'idx_filter_products_product');
            $table->index(['filter_group_id'], 'idx_filter_products_group');
            $table->index(['filter_id', 'filter_group_id'], 'idx_filter_products_filter_group');
            $table->index(['product_id', 'filter_group_id'], 'idx_filter_products_product_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_is_hit');
            $table->dropIndex('idx_products_is_new');
            $table->dropIndex('idx_products_price');
            $table->dropIndex('idx_products_category_price');
            $table->dropIndex('idx_products_category_hit');
            $table->dropIndex('idx_products_category_new');

            // Видалити fulltext індекс тільки для MySQL
            if (config('database.default') === 'mysql') {
                $table->dropFullText('idx_products_fulltext');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_parent');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_date');
            $table->dropIndex('idx_orders_date');
            $table->dropIndex('idx_orders_status');
        });

        Schema::table('filter_products', function (Blueprint $table) {
            $table->dropIndex('idx_filter_products_product');
            $table->dropIndex('idx_filter_products_group');
            $table->dropIndex('idx_filter_products_filter_group');
            $table->dropIndex('idx_filter_products_product_group');
        });
    }
};
