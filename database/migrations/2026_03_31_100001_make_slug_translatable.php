<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Products: drop unique index, convert slug to text for JSON storage
        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Check if unique index exists before dropping
            $indexes = collect(DB::select("SHOW INDEX FROM products WHERE Key_name = 'products_slug_unique'"));
            if ($indexes->isNotEmpty()) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropUnique(['slug']);
                });
            }

            DB::statement('ALTER TABLE products MODIFY slug LONGTEXT NULL');

            // Categories: drop unique index, convert slug to text
            $indexes = collect(DB::select("SHOW INDEX FROM categories WHERE Key_name = 'categories_slug_unique'"));
            if ($indexes->isNotEmpty()) {
                Schema::table('categories', function (Blueprint $table) {
                    $table->dropUnique(['slug']);
                });
            }

            DB::statement('ALTER TABLE categories MODIFY slug LONGTEXT NULL');
        } else {
            // SQLite: recreate without unique constraint (SQLite does not support DROP INDEX on unique columns easily)
            // For SQLite, we just note that the unique constraint will be ignored for JSON content
            // The slug field already works as text in SQLite
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE products MODIFY slug VARCHAR(255) NULL');
            Schema::table('products', function (Blueprint $table) {
                $table->unique('slug');
            });

            DB::statement('ALTER TABLE categories MODIFY slug VARCHAR(255) NULL');
            Schema::table('categories', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
    }
};
