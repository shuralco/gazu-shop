<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The first dedup migration (2026_05_12_150000_dedupe_categories_and_brands)
 * cleaned the table, but the AutoPartsSeeder ran right after with the OLD
 * non-idempotent firstOrCreate logic and re-created the same duplicates.
 *
 * This migration runs again AFTER the seeder is fixed. Same logic.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dedupe('categories', 'title', function (int $keep, array $drop): void {
            DB::table('categories')->whereIn('parent_id', $drop)->update(['parent_id' => $keep]);
            DB::table('products')->whereIn('category_id', $drop)->update(['category_id' => $keep]);
        });

        $this->dedupe('brands', 'name', function (int $keep, array $drop): void {
            DB::table('products')->whereIn('brand_id', $drop)->update(['brand_id' => $keep]);
        });

        // Products: dedupe by title (Str::slug created different slugs each
        // time even for the same title, so dups are by title only).
        $this->dedupe('products', 'title', function (int $keep, array $drop): void {
            if (\Schema::hasTable('order_products')) {
                DB::table('order_products')->whereIn('product_id', $drop)->update(['product_id' => $keep]);
            }
            if (\Schema::hasTable('inventory')) {
                DB::table('inventory')->whereIn('product_id', $drop)->delete();
            }
        });
    }

    public function down(): void
    {
        // Irreversible.
    }

    private function dedupe(string $table, string $titleCol, \Closure $rePointReferences): void
    {
        if (! \Schema::hasTable($table)) return;
        if (! \Schema::hasColumn($table, $titleCol)) return;

        $rows = DB::table($table)
            ->select(['id', $titleCol])
            ->orderBy('id')
            ->get();

        $groups = [];
        foreach ($rows as $r) {
            $raw = (string) ($r->{$titleCol} ?? '');
            $key = mb_strtolower(trim($this->unwrap($raw)));
            if ($key === '') continue;
            $groups[$key][] = (int) $r->id;
        }

        foreach ($groups as $ids) {
            if (count($ids) < 2) continue;
            $keep = array_shift($ids);
            $rePointReferences($keep, $ids);
            DB::table($table)->whereIn('id', $ids)->delete();
        }
    }

    private function unwrap(string $value): string
    {
        if ($value === '' || $value[0] !== '{') return $value;
        $decoded = json_decode($value, true);
        if (! is_array($decoded)) return $value;
        return (string) ($decoded['uk'] ?? $decoded['en'] ?? reset($decoded) ?? '');
    }
};
