<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Defense-in-depth до FK RESTRICT, що валили видалення (500), на додачу до
 * модельних хуків (Filter/FilterGroup/Order):
 *   - filter_products.*       → CASCADE (видалення характеристики/групи/товару) [GlitchTip #116]
 *   - filters.filter_group_id → CASCADE (видалення групи характеристик)
 *   - order_products.order_id → CASCADE (видалення замовлення)                  [GlitchTip #108/#105]
 * (order_products.product_id — без FK, там snapshot назви/ціни.)
 */
return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'filter_products' => [
                'filter_id' => ['filters', 'CASCADE'],
                'product_id' => ['products', 'CASCADE'],
                'filter_group_id' => ['filter_groups', 'CASCADE'],
            ],
            'filters' => [
                'filter_group_id' => ['filter_groups', 'CASCADE'],
            ],
            'order_products' => [
                'order_id' => ['orders', 'CASCADE'],
            ],
        ];

        foreach ($map as $table => $cols) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($cols as $col => [$ref, $onDelete]) {
                if (! Schema::hasColumn($table, $col)) {
                    continue;
                }
                $this->dropForeignByColumn($table, $col);
                $this->addForeign($table, $col, $ref, 'id', $onDelete);
            }
        }
    }

    public function down(): void
    {
        // No-op: лишаємо безпечні FK (повертати RESTRICT означало б знову ламкі видалення).
    }

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
        }
    }
};
