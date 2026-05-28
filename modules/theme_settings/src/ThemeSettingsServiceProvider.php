<?php
namespace Modules\ThemeSettings;
use Illuminate\Support\ServiceProvider;
class ThemeSettingsServiceProvider extends ServiceProvider {
    public function register(): void {
        if ($this->app->runningInConsole() && class_exists(\App\Console\Commands\ThemeSetCommand::class)) {
            $this->commands([\App\Console\Commands\ThemeSetCommand::class]);
        }
    }
    public function boot(): void {}
}
