<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolidation migration: adds any missing performance indexes
     * after auditing existing index coverage.
     *
     * Already covered by earlier migrations:
     *   products: category_id (FK), is_hit, is_new, price, brand_id+is_active,
     *             category_id+price, category_id+is_hit, category_id+is_new,
     *             category_id+is_active, is_hit+is_active, is_new+is_active,
     *             category_id+price+created_at, category_id+title, category_id+id
     *   orders:   user_id+created_at, status, created_at, user_id+status, status+created_at
     *   reviews:  product_id+is_approved, rating, user_id, product_id,
     *             product_id+is_approved+created_at
     *
     * This migration adds only what is missing.
     */
    public function up(): void
    {
        $this->addIndexIfMissing('products', 'is_active', 'idx_products_is_active');
        $this->addIndexIfMissing('products', 'brand_id', 'idx_products_brand_id');
        $this->addIndexIfMissing('products', 'created_at', 'idx_products_created_at');
        $this->addCompositeIndexIfMissing('products', ['is_active', 'category_id', 'price'], 'idx_products_active_category_price');
        $this->addCompositeIndexIfMissing('products', ['is_active', 'is_hit'], 'idx_products_active_hit');
        $this->addCompositeIndexIfMissing('products', ['is_active', 'is_new'], 'idx_products_active_new');

        // orders: user_id standalone (FK but sometimes queried alone)
        $this->addIndexIfMissing('orders', 'user_id', 'idx_orders_user_id');

        // reviews: status (is_approved) standalone for admin moderation queries
        $this->addIndexIfMissing('reviews', 'is_approved', 'idx_reviews_is_approved');

        // display_settings: key+is_active for the bulk-load query
        $this->addCompositeIndexIfMissing('display_settings', ['key', 'is_active'], 'idx_display_settings_key_active');

        // homepage_modules: is_active+sort_order for homepage query
        if (Schema::hasTable('homepage_modules')) {
            $this->addCompositeIndexIfMissing('homepage_modules', ['is_active', 'sort_order'], 'idx_homepage_modules_active_sort');
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('products', 'idx_products_is_active');
        $this->dropIndexIfExists('products', 'idx_products_brand_id');
        $this->dropIndexIfExists('products', 'idx_products_created_at');
        $this->dropIndexIfExists('products', 'idx_products_active_category_price');
        $this->dropIndexIfExists('products', 'idx_products_active_hit');
        $this->dropIndexIfExists('products', 'idx_products_active_new');
        $this->dropIndexIfExists('orders', 'idx_orders_user_id');
        $this->dropIndexIfExists('reviews', 'idx_reviews_is_approved');
        $this->dropIndexIfExists('display_settings', 'idx_display_settings_key_active');

        if (Schema::hasTable('homepage_modules')) {
            $this->dropIndexIfExists('homepage_modules', 'idx_homepage_modules_active_sort');
        }
    }

    /**
     * Add a single-column index only if it does not already exist.
     */
    private function addIndexIfMissing(string $table, string $column, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($column, $indexName) {
            $t->index($column, $indexName);
        });
    }

    /**
     * Add a composite index only if it does not already exist.
     */
    private function addCompositeIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
            $t->index($columns, $indexName);
        });
    }

    /**
     * Check if an index exists on the given table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = config('database.default');

        if ($driver === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                ->pluck('Key_name')
                ->unique();

            return $indexes->contains($indexName);
        }

        // SQLite
        $indexes = collect(DB::select("PRAGMA index_list('{$table}')"))
            ->pluck('name');

        return $indexes->contains($indexName);
    }

    /**
     * Drop an index if it exists.
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        $driver = config('database.default');

        if ($driver === 'sqlite') {
            DB::statement("DROP INDEX IF EXISTS {$indexName}");
        } else {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        }
    }
};
