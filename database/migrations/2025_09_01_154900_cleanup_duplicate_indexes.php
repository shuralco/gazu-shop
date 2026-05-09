<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Видаляємо дублікати індексів для зменшення розміру БД

        if (config('database.default') === 'sqlite') {
            // SQLite: видаляємо дублікати індексів
            $duplicateIndexes = [
                'products_price_idx',           // Залишаємо products_price_index
                'products_is_hit_idx',          // Залишаємо products_is_hit_index
                'products_is_new_idx',          // Залишаємо products_is_new_index
                'idx_products_price',           // Дублікат
                'idx_products_is_hit',          // Дублікат
                'idx_products_is_new',          // Дублікат
                'idx_products_category_price',  // Залишаємо products_category_price_index
                'idx_products_category_hit',    // Залишаємо products_category_hit_index
                'idx_products_category_new',    // Залишаємо products_category_new_index
            ];

            foreach ($duplicateIndexes as $index) {
                try {
                    DB::statement("DROP INDEX IF EXISTS {$index}");
                } catch (Exception $e) {
                    // Ігноруємо якщо індекс не існує
                }
            }
        }

        // MySQL: створюємо оптимальні індекси (тільки якщо не існують)
        if (config('database.default') === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM products"))->pluck('Key_name')->unique();

            Schema::table('products', function (Blueprint $table) use ($indexes) {
                if (!$indexes->contains('idx_products_category_price')) {
                    $table->index(['category_id', 'price'], 'idx_products_category_price');
                }
                if (!$indexes->contains('idx_products_category_hit')) {
                    $table->index(['category_id', 'is_hit'], 'idx_products_category_hit');
                }
                if (!$indexes->contains('idx_products_category_new')) {
                    $table->index(['category_id', 'is_new'], 'idx_products_category_new');
                }
                if (!$indexes->contains('idx_products_price')) {
                    $table->index('price', 'idx_products_price');
                }
                if (!$indexes->contains('idx_products_is_hit')) {
                    $table->index('is_hit', 'idx_products_is_hit');
                }
                if (!$indexes->contains('idx_products_is_new')) {
                    $table->index('is_new', 'idx_products_is_new');
                }
                if (!$indexes->contains('idx_products_stock')) {
                    $table->index('stock_status', 'idx_products_stock');
                }
            });
        }
    }

    public function down(): void
    {
        // Rollback не потрібен - індекси будуть перестворені при потребі
    }
};
