<?php
namespace Modules\ImageOptimization;
use Illuminate\Support\ServiceProvider;
class ImageOptimizationServiceProvider extends ServiceProvider {
    public function register(): void {
        if ($this->app->runningInConsole() && class_exists(\App\Console\Commands\OptimizeImages::class)) {
            $this->commands([\App\Console\Commands\OptimizeImages::class]);
        }
    }
    public function boot(): void {}
}
