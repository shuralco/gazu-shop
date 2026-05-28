<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class SearchIndex extends Command
{
    protected $signature = 'search:index {--flush : Flush index before re-indexing}';

    protected $description = 'Index products for search (Meilisearch via Scout or collection fallback)';

    public function handle(): int
    {
        if (!class_exists(\Laravel\Scout\Searchable::class)) {
            $this->warn('Laravel Scout is not installed.');
            $this->newLine();
            $this->line('Install it with:');
            $this->info('  composer require laravel/scout meilisearch/meilisearch-php');
            $this->newLine();
            $this->line('Search currently works via SQL LIKE fallback — no indexing needed.');

            return self::SUCCESS;
        }

        if (!in_array(\Laravel\Scout\Searchable::class, class_uses_recursive(Product::class))) {
            $this->warn('The Searchable trait is not added to the Product model.');
            $this->newLine();
            $this->line('Add it to app/Models/Product.php:');
            $this->info('  use HasFactory, HasSeoMeta, HasTranslations, Searchable, Sluggable;');
            $this->newLine();
            $this->line('Then run this command again.');

            return self::SUCCESS;
        }

        $driver = config('scout.driver', 'collection');
        $this->info("Scout driver: {$driver}");

        if ($driver === 'collection') {
            $this->warn('Scout driver is set to "collection" — no external index to populate.');
            $this->line('Set SCOUT_DRIVER=meilisearch in .env to use Meilisearch.');

            return self::SUCCESS;
        }

        if ($this->option('flush')) {
            $this->info('Flushing product index...');
            $this->call('scout:flush', ['model' => Product::class]);
        }

        $this->info('Indexing products...');
        $this->call('scout:import', ['model' => Product::class]);

        $count = Product::where('is_active', true)->count();
        $this->info("Done. {$count} active products indexed.");

        return self::SUCCESS;
    }
}
