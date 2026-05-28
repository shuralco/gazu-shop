<?php

namespace App\Console\Commands;

use App\Models\SeoMeta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearSeoCommand extends Command
{
    protected $signature = 'seo:clear
                            {--type=all : Type of content to clear SEO for (all, cache, database)}
                            {--language= : Specific language to clear (uk, en)}
                            {--model= : Specific model to clear (Category, Product)}
                            {--confirm : Skip confirmation prompt}';

    protected $description = 'Clear SEO meta data and cache';

    public function handle(): int
    {
        $type = $this->option('type');
        $language = $this->option('language');
        $model = $this->option('model');
        $confirm = $this->option('confirm');

        if (! $confirm) {
            $confirmAction = $this->confirm('Are you sure you want to clear SEO data? This action cannot be undone.');
            if (! $confirmAction) {
                $this->info('Operation cancelled.');

                return Command::SUCCESS;
            }
        }

        $deleted = 0;

        if ($type === 'all' || $type === 'cache') {
            $this->info('Clearing SEO cache...');
            $cacheCleared = Cache::flush();
            if ($cacheCleared) {
                $this->info('✓ SEO cache cleared successfully');
            } else {
                $this->error('✗ Failed to clear SEO cache');
            }
        }

        if ($type === 'all' || $type === 'database') {
            $this->info('Clearing SEO database records...');

            $query = SeoMeta::query();

            if ($language) {
                $query->where('language', $language);
                $this->info("Filtering by language: {$language}");
            }

            if ($model) {
                $modelClass = "App\\Models\\{$model}";
                if (class_exists($modelClass)) {
                    $query->where('seoable_type', $modelClass);
                    $this->info("Filtering by model: {$model}");
                } else {
                    $this->error("Model {$model} does not exist");

                    return Command::FAILURE;
                }
            }

            $total = $query->count();

            if ($total > 0) {
                $bar = $this->output->createProgressBar($total);
                $bar->start();

                $query->chunk(100, function ($seoRecords) use (&$deleted, $bar) {
                    foreach ($seoRecords as $seoRecord) {
                        $seoRecord->delete();
                        $deleted++;
                        $bar->advance();
                    }
                });

                $bar->finish();
                $this->newLine();
            }
        }

        $this->info('SEO clearing completed!');
        $this->table(
            ['Action', 'Count'],
            [
                ['Deleted Records', $deleted],
                ['Cache Cleared', $type === 'all' || $type === 'cache' ? 'Yes' : 'No'],
            ]
        );

        return Command::SUCCESS;
    }
}
