<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Видалення категорії падало (500): FK RESTRICT на products.category_id
 * (категорія-корінь з товарами), categories.parent_id (є підкатегорії),
 * category_filters. Модельний хук Category переносить товари/дітей до батька,
 * але для кореневих категорій з прямими товарами переносити нікуди.
 *
 * Робимо FK безпечними на рівні БД (defense-in-depth):
 *   - products.category_id     → nullable + ON DELETE SET NULL
 *   - categories.parent_id     → ON DELETE SET NULL (діти стають кореневими)
 *   - category_filters.*       → ON DELETE CASCADE (звʼязки вмирають з категорією/групою)
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL-специфічно (information_schema, MODIFY, ADD/DROP CONSTRAINT).
        // На sqlite (тести) FK задаються у схемних міграціях — тут no-op.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // products.category_id → nullable + SET NULL
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'category_id')) {
            $this->dropForeignByColumn('products', 'category_id');
            // Зробити колонку nullable (raw — щоб не залежати від doctrine/dbal).
            DB::statement('ALTER TABLE `products` MODIFY `category_id` BIGINT UNSIGNED NULL');
            $this->addForeign('products', 'category_id', 'categories', 'id', 'SET NULL');
        }

        // categories.parent_id → SET NULL
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'parent_id')) {
            $this->dropForeignByColumn('categories', 'parent_id');
            $this->addForeign('categories', 'parent_id', 'categories', 'id', 'SET NULL');
        }

        // category_filters.* → CASCADE
        if (Schema::hasTable('category_filters')) {
            foreach (['category_id' => 'categories', 'filter_group_id' => 'filter_groups'] as $col => $ref) {
                if (Schema::hasColumn('category_filters', $col)) {
                    $this->dropForeignByColumn('category_filters', $col);
                    $this->addForeign('category_filters', $col, $ref, 'id', 'CASCADE');
                }
            }
        }
    }

    public function down(): void
    {
        // Незворотно по семантиці (RESTRICT назад робив би видалення знову ламким) —
        // лишаємо безпечні FK. No-op.
    }

    /**
     * Drop the FK constraint(s) on a given column by looking up the real
     * constraint name in information_schema (Laravel-generated names vary).
     */
    private function dropForeignByColumn(string $table, string $column): void
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME AS name FROM information_schema.KEY_COLUMN_USAGE '
            .'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? '
            .'AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$db, $table, $column]
        );
        foreach ($rows as $r) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$r->name}`");
            } catch (\Throwable $e) {
                // вже відсутній — ок
            }
        }
    }

    private function addForeign(string $table, string $column, string $refTable, string $refCol, string $onDelete): void
    {
        $name = $table.'_'.$column.'_foreign';
        try {
            DB::statement(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$name}` FOREIGN KEY (`{$column}`) "
                ."REFERENCES `{$refTable}` (`{$refCol}`) ON DELETE {$onDelete}"
            );
        } catch (\Throwable $e) {
            // constraint вже існує з потрібною дією — ок
        }
    }
};
