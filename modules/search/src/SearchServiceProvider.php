<?php
namespace Modules\Search;
use Illuminate\Support\ServiceProvider;
class SearchServiceProvider extends ServiceProvider {
    public function register(): void {
        if ($this->app->runningInConsole()) {
            foreach ([
                \App\Console\Commands\SearchIndex::class,
                \App\Console\Commands\SetupMeilisearch::class,
                \App\Console\Commands\GenerateSearchTags::class,
            ] as $c) if (class_exists($c)) $this->commands([$c]);
        }
    }
    public function boot(): void {}
}
