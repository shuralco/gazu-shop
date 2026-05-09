<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Індекси для фільтрації товарів
            $table->index('is_hit', 'products_is_hit_index');
            $table->index('is_new', 'products_is_new_index');
            $table->index('price', 'products_price_index');

            // Складений індекс для пошуку активних товарів
            $table->index(['category_id', 'is_hit'], 'products_category_hit_index');
            $table->index(['category_id', 'is_new'], 'products_category_new_index');

            // Індекс для сортування за ціною в межах категорії
            $table->index(['category_id', 'price'], 'products_category_price_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Індекс для ієрархічної структури
            $table->index('parent_id', 'categories_parent_id_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Індекси для пошуку замовлень
            $table->index('status', 'orders_status_index');
            $table->index(['user_id', 'status'], 'orders_user_status_index');
            $table->index('created_at', 'orders_created_at_index');
        });

        Schema::table('filter_products', function (Blueprint $table) {
            // Індекси для швидкої фільтрації
            $table->index(['product_id', 'filter_group_id'], 'filter_products_product_group_index');
            $table->index('filter_group_id', 'filter_products_group_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_is_hit_index');
            $table->dropIndex('products_is_new_index');
            $table->dropIndex('products_price_index');
            $table->dropIndex('products_category_hit_index');
            $table->dropIndex('products_category_new_index');
            $table->dropIndex('products_category_price_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_parent_id_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_user_status_index');
            $table->dropIndex('orders_created_at_index');
        });

        Schema::table('filter_products', function (Blueprint $table) {
            $table->dropIndex('filter_products_product_group_index');
            $table->dropIndex('filter_products_group_index');
        });
    }
};
