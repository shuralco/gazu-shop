<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Services\SeoMetaGenerator;
use Illuminate\Console\Command;

class GenerateSeoCommand extends Command
{
    protected $signature = 'seo:generate
                            {--type=all : Type of content to generate SEO for (all, categories, products)}
                            {--language=uk : Language for SEO generation (uk, en)}
                            {--force : Force regeneration of existing SEO data}
                            {--chunk=100 : Number of records to process at once}';

    protected $description = 'Generate SEO meta data for products, categories and other content';

    public function handle(): int
    {
        $type = $this->option('type');
        $language = $this->option('language');
        $force = $this->option('force');
        $chunk = (int) $this->option('chunk');

        $this->info("Starting SEO generation for type: {$type}, language: {$language}");

        $generator = new SeoMetaGenerator;
        $generated = 0;
        $updated = 0;
        $skipped = 0;

        if ($type === 'all' || $type === 'categories') {
            $this->info('Processing categories...');
            $categories = Category::query()->whereNotNull('title')->get();

            $bar = $this->output->createProgressBar($categories->count());
            $bar->start();

            foreach ($categories as $category) {
                $existing = SeoMeta::where([
                    'seoable_type' => Category::class,
                    'seoable_id' => $category->id,
                    'language' => $language,
                ])->exists();

                if ($existing && ! $force) {
                    $skipped++;
                } else {
                    try {
                        $category->generateSeoMeta($language);
                        $existing ? $updated++ : $generated++;
                    } catch (\Exception $e) {
                        $this->error("Failed to generate SEO for category {$category->id}: ".$e->getMessage());
                    }
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        if ($type === 'all' || $type === 'products') {
            $this->info('Processing products...');

            Product::query()
                ->whereNotNull('title')
                ->chunk($chunk, function ($products) use ($language, $force, &$generated, &$updated, &$skipped) {
                    $bar = $this->output->createProgressBar($products->count());
                    $bar->start();

                    foreach ($products as $product) {
                        $existing = SeoMeta::where([
                            'seoable_type' => Product::class,
                            'seoable_id' => $product->id,
                            'language' => $language,
                        ])->exists();

                        if ($existing && ! $force) {
                            $skipped++;
                        } else {
                            try {
                                $product->generateSeoMeta($language);
                                $existing ? $updated++ : $generated++;
                            } catch (\Exception $e) {
                                $this->error("Failed to generate SEO for product {$product->id}: ".$e->getMessage());
                            }
                        }
                        $bar->advance();
                    }
                    $bar->finish();
                    $this->newLine();
                });
        }

        $this->info('SEO generation completed!');
        $this->table(
            ['Action', 'Count'],
            [
                ['Generated', $generated],
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Total', $generated + $updated + $skipped],
            ]
        );

        return Command::SUCCESS;
    }
}
