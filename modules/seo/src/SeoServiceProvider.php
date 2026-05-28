<?php
namespace Modules\Seo;
use Illuminate\Support\ServiceProvider;
class SeoServiceProvider extends ServiceProvider {
    public function register(): void {
        if ($this->app->runningInConsole()) {
            foreach ([
                \App\Console\Commands\ClearSeoCommand::class,
                \App\Console\Commands\GenerateSeoCommand::class,
                \App\Console\Commands\RefreshSeoCommand::class,
            ] as $c) if (class_exists($c)) $this->commands([$c]);
        }
    }
    public function boot(): void {}
}
