<?php
namespace Modules\TelegramNotify;
use Illuminate\Support\ServiceProvider;
class TelegramNotifyServiceProvider extends ServiceProvider {
    public function register(): void {
        if ($this->app->runningInConsole() && class_exists(\App\Console\Commands\TelegramTest::class)) {
            $this->commands([\App\Console\Commands\TelegramTest::class]);
        }
    }
    public function boot(): void {}
}
