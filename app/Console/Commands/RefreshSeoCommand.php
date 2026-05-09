<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RefreshSeoCommand extends Command
{
    protected $signature = 'seo:refresh
                            {--cache-only : Only refresh cache without regenerating database records}
                            {--silent : Run without progress bars and detailed output}';

    protected $description = 'Refresh SEO cache and optionally regenerate all SEO meta data';

    public function handle(): int
    {
        $cacheOnly = $this->option('cache-only');
        $silent = $this->option('silent');

        if (! $silent) {
            $this->info('Starting SEO refresh...');
        }

        $this->clearSeoCache($silent);

        if (! $cacheOnly) {
            $this->regenerateAllSeo($silent);
        }

        if (! $silent) {
            $this->info('SEO refresh completed successfully!');
        }

        return Command::SUCCESS;
    }

    private function clearSeoCache(bool $silent): void
    {
        if (! $silent) {
            $this->info('Clearing SEO cache...');
        }

        $cacheKeys = [
            'seo_meta_*',
            'sitemap_*',
            'canonical_urls_*',
            'structured_data_*',
        ];

        foreach ($cacheKeys as $pattern) {
            Cache::forget($pattern);
        }

        Cache::flush();

        if (! $silent) {
            $this->info('✓ SEO cache cleared');
        }
    }

    private function regenerateAllSeo(bool $silent): void
    {
        if (! $silent) {
            $this->info('Regenerating SEO data for all languages...');
        }

        $languages = config('seo.generator.languages', ['uk', 'en']);

        foreach ($languages as $language) {
            if (! $silent) {
                $this->info("Regenerating SEO for language: {$language}");
            }

            $this->call('seo:generate', [
                '--type' => 'all',
                '--language' => $language,
                '--force' => true,
                '--chunk' => 50,
            ]);
        }

        if (! $silent) {
            $this->info('✓ All SEO data regenerated');
        }
    }
}
