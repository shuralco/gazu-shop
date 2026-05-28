<?php
namespace Modules\Currency;
use Illuminate\Support\ServiceProvider;
class CurrencyServiceProvider extends ServiceProvider {
    public function register(): void {
        if ($this->app->runningInConsole() && class_exists(\App\Console\Commands\UpdateCurrencyRates::class)) {
            $this->commands([\App\Console\Commands\UpdateCurrencyRates::class]);
        }
    }
    public function boot(): void {}
}
