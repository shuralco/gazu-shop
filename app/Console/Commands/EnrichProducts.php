<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\PartImage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Заповнює SEO meta_title / meta_description + gallery (URLs на part-image
 * webp pool) для demo-products у які бракує цих полів.
 *
 * Run: php artisan products:enrich [--reset]
 */
class EnrichProducts extends Command
{
    protected $signature = 'products:enrich {--reset : переписати навіть якщо вже заповнено}';

    protected $description = 'Generate meta_title/description + gallery URLs for products';

    public function handle(): int
    {
        $query = Product::query();
        if (! $this->option('reset')) {
            $query->where(function ($q) {
                $q->whereNull('meta_title')
                    ->orWhereNull('meta_description')
                    ->orWhereNull('gallery')
                    ->orWhere('gallery', '[]')
                    ->orWhere('gallery', '');
            });
        }
        $products = $query->with('category:id,title', 'brand:id,name')
            ->get(['id', 'title', 'slug', 'sku', 'excerpt', 'price', 'category_id', 'brand_id', 'specifications', 'meta_title', 'meta_description', 'gallery']);

        if ($products->isEmpty()) {
            $this->info('Усі товари вже enriched.');
            return self::SUCCESS;
        }

        $this->info("Обробляю {$products->count()} товарів…");
        $bar = $this->output->createProgressBar($products->count());

        foreach ($products as $p) {
            $title = is_array($p->title) ? ($p->title['uk'] ?? '') : (string) $p->title;
            $brandName = $p->brand?->name ? (is_array($p->brand->name) ? ($p->brand->name['uk'] ?? '') : (string) $p->brand->name) : '';
            $catTitle = $p->category?->title ? (is_array($p->category->title) ? ($p->category->title['uk'] ?? '') : (string) $p->category->title) : '';
            $sku = $p->sku ?: '—';
            $price = number_format((float) $p->price, 0, '.', ' ');

            // META TITLE: "Title (Brand) — купити SKU в Україні | GAZU"
            $metaTitle = $title;
            if ($brandName && ! str_contains(mb_strtolower($metaTitle), mb_strtolower($brandName))) {
                $metaTitle .= ' '.$brandName;
            }
            $metaTitle = Str::limit($metaTitle, 55, '');
            $metaTitle .= ' · {$price} ₴ · GAZU';
            $metaTitle = str_replace('{$price}', $price, $metaTitle);
            $metaTitle = Str::limit($metaTitle, 70, '');

            // META DESCRIPTION
            $excerpt = is_array($p->excerpt) ? ($p->excerpt['uk'] ?? '') : (string) ($p->excerpt ?? '');
            $excerpt = trim(strip_tags($excerpt));
            if (mb_strlen($excerpt) < 30) {
                $excerpt = "Купити {$title}";
                if ($brandName) $excerpt .= " ({$brandName})";
                $excerpt .= " — оригінал та аналоги. Доставка Нова Пошта, УкрПошта. Гарантія 12+ міс.";
            }
            $metaDesc = Str::limit($excerpt, 155, '');

            // GALLERY: 3 part-image URLs from kind pool
            $kind = PartImage::kindFromCategory($catTitle);
            $gallery = [];
            if ($kind) {
                $poolDir = public_path("img/parts/{$kind}");
                if (is_dir($poolDir)) {
                    $files = collect(glob($poolDir.'/*.webp') ?: [])->map('basename')->values();
                    if ($files->isNotEmpty()) {
                        // Pick 3 different photos using id as seed offset
                        $seed = (int) $p->id;
                        $count = min(3, $files->count());
                        $picks = [];
                        for ($i = 0; $i < $count; $i++) {
                            $picks[] = "/img/parts/{$kind}/".$files[(abs(crc32((string) $seed + $i))) % $files->count()];
                        }
                        $gallery = array_values(array_unique($picks));
                    }
                }
            }

            $update = [];
            if ($this->option('reset') || empty($p->meta_title)) $update['meta_title'] = $metaTitle;
            if ($this->option('reset') || empty($p->meta_description)) $update['meta_description'] = $metaDesc;
            $currentGallery = is_array($p->gallery) ? $p->gallery : [];
            if (! empty($gallery) && ($this->option('reset') || empty($currentGallery))) {
                $update['gallery'] = $gallery;
            }

            if (! empty($update)) {
                $p->update($update);
            }

            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->table(['Поле', 'Заповнено / 1278'], [
            ['meta_title', Product::whereNotNull('meta_title')->where('meta_title', '!=', '')->count()],
            ['meta_description', Product::whereNotNull('meta_description')->where('meta_description', '!=', '')->count()],
            ['gallery (≥1)', Product::whereNotNull('gallery')->where('gallery', '!=', '[]')->where('gallery', '!=', '')->count()],
        ]);

        return self::SUCCESS;
    }
}
