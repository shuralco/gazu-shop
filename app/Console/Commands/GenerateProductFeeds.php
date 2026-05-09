<?php

namespace App\Console\Commands;

use App\Services\FeedGenerator\YmlFeedGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateProductFeeds extends Command
{
    protected $signature = 'feeds:generate';

    protected $description = 'Clear feed cache and regenerate all product feeds (Google, Rozetka, Prom)';

    public function handle(): int
    {
        $this->info('Clearing feed cache...');

        Cache::forget('product_feed_google');
        Cache::forget('product_feed_rozetka');
        Cache::forget('product_feed_prom');

        $generator = app(YmlFeedGenerator::class);

        $types = ['google', 'rozetka', 'prom'];

        foreach ($types as $type) {
            $this->info("Generating {$type} feed...");
            $generator->generate($type);
            $this->info("Feed {$type} generated successfully.");
        }

        $this->info('All product feeds regenerated.');

        return self::SUCCESS;
    }
}
