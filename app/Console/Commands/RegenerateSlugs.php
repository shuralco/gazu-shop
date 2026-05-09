<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\TransliterationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegenerateSlugs extends Command
{
    protected $signature = 'slugs:regenerate
        {--model=all : product|category|all}
        {--force : Overwrite existing slugs}';

    protected $description = 'Regenerate transliterated slugs for all locales';

    public function handle(TransliterationService $service): int
    {
        $model = $this->option('model');
        $force = $this->option('force');
        $locales = config('slugs.locales', ['uk', 'en']);
        $appendId = config('slugs.append_id', true);
        $maxLen = config('slugs.max_length', 100);
        $separator = config('slugs.separator', '-');

        if (in_array($model, ['product', 'all'])) {
            $this->info('Regenerating product slugs...');
            $count = Product::count();

            if ($count === 0) {
                $this->warn('No products found.');
            } else {
                $bar = $this->output->createProgressBar($count);

                Product::chunk(50, function ($products) use ($service, $locales, $appendId, $maxLen, $separator, $force, $bar) {
                    foreach ($products as $product) {
                        $slugs = [];

                        foreach ($locales as $locale) {
                            $title = $product->getTranslation('title', $locale, false);
                            if (!$title) {
                                continue;
                            }

                            if (!$force) {
                                $existing = $product->getTranslation('slug', $locale, false);
                                if ($existing) {
                                    $slugs[$locale] = $existing;
                                    continue;
                                }
                            }

                            $slug = $service->generateSlug($title, $locale);

                            if ($appendId) {
                                $slug .= $separator . $product->id;
                            }

                            $slugs[$locale] = Str::limit($slug, $maxLen, '');
                        }

                        if (!empty($slugs)) {
                            DB::table('products')->where('id', $product->id)->update([
                                'slug' => json_encode($slugs, JSON_UNESCAPED_UNICODE),
                            ]);
                        }

                        $bar->advance();
                    }
                });

                $bar->finish();
                $this->newLine();
            }
        }

        if (in_array($model, ['category', 'all'])) {
            $this->info('Regenerating category slugs...');
            $count = Category::count();

            if ($count === 0) {
                $this->warn('No categories found.');
            } else {
                $bar = $this->output->createProgressBar($count);

                Category::chunk(50, function ($categories) use ($service, $locales, $maxLen, $force, $bar) {
                    foreach ($categories as $category) {
                        $slugs = [];

                        foreach ($locales as $locale) {
                            $title = $category->getTranslation('title', $locale, false);
                            if (!$title) {
                                continue;
                            }

                            if (!$force) {
                                $existing = $category->getTranslation('slug', $locale, false);
                                if ($existing) {
                                    $slugs[$locale] = $existing;
                                    continue;
                                }
                            }

                            $slug = $service->generateSlug($title, $locale);
                            $slugs[$locale] = Str::limit($slug, $maxLen, '');
                        }

                        if (!empty($slugs)) {
                            DB::table('categories')->where('id', $category->id)->update([
                                'slug' => json_encode($slugs, JSON_UNESCAPED_UNICODE),
                            ]);
                        }

                        $bar->advance();
                    }
                });

                $bar->finish();
                $this->newLine();
            }
        }

        $this->info('Done! Run `php artisan cache:clear` to clear cached data.');

        return self::SUCCESS;
    }
}
