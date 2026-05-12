<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * AutoPartsSeeder ran multiple times in production because firstOrCreate
 * matched against the raw 'slug' column but the translatable migration
 * earlier wrapped slug values into JSON ({"uk":"..."}), so the lookup
 * never matched existing rows. Result: 8+ duplicate "Акумулятори" /
 * "Фільтри" rows showing on the home category tiles.
 *
 * This cleanup keeps the FIRST row per (lower-trimmed title) for both
 * categories and brands, repoints child references to the kept row,
 * and deletes the rest.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dedupe('categories', 'title', function (int $keep, array $drop): void {
            // Repoint child categories (parent_id) and products (category_id)
            DB::table('categories')->whereIn('parent_id', $drop)->update(['parent_id' => $keep]);
            DB::table('products')->whereIn('category_id', $drop)->update(['category_id' => $keep]);
        });

        $this->dedupe('brands', 'name', function (int $keep, array $drop): void {
            DB::table('products')->whereIn('brand_id', $drop)->update(['brand_id' => $keep]);
        });
    }

    public function down(): void
    {
        // Irreversible — dropped rows are gone.
    }

    private function dedupe(string $table, string $titleCol, \Closure $rePointReferences): void
    {
        if (! \Schema::hasTable($table)) return;

        // Group rows by lower-trimmed JSON-unwrapped title.
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
