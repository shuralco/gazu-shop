<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Оновлюємо canonical URLs, видаляючи префікси /c/ та /p/
        DB::table('seo_meta')
            ->where('canonical_url', 'like', '%/c/%')
            ->update([
                'canonical_url' => DB::raw("REPLACE(canonical_url, '/c/', '/')"),
            ]);

        DB::table('seo_meta')
            ->where('canonical_url', 'like', '%/p/%')
            ->update([
                'canonical_url' => DB::raw("REPLACE(canonical_url, '/p/', '/')"),
            ]);

        // Також видаляємо повні домени, залишаючи тільки відносні шляхи
        $records = DB::table('seo_meta')
            ->whereNotNull('canonical_url')
            ->where('canonical_url', '!=', '')
            ->where('canonical_url', 'like', 'http%')
            ->get();

        foreach ($records as $record) {
            $url = $record->canonical_url;
            $path = parse_url($url, PHP_URL_PATH) ?: '/';

            DB::table('seo_meta')
                ->where('id', $record->id)
                ->update(['canonical_url' => $path]);
        }
    }

    public function down(): void
    {
        // Повертаємо префікси назад (для rollback)
        DB::table('seo_meta')
            ->join('categories', 'categories.id', '=', 'seo_meta.seoable_id')
            ->where('seo_meta.seoable_type', 'App\\Models\\Category')
            ->whereNotNull('seo_meta.canonical_url')
            ->update([
                'seo_meta.canonical_url' => DB::raw("CONCAT('/c', seo_meta.canonical_url)"),
            ]);

        DB::table('seo_meta')
            ->join('products', 'products.id', '=', 'seo_meta.seoable_id')
            ->where('seo_meta.seoable_type', 'App\\Models\\Product')
            ->whereNotNull('seo_meta.canonical_url')
            ->update([
                'seo_meta.canonical_url' => DB::raw("CONCAT('/p', seo_meta.canonical_url)"),
            ]);
    }
};
