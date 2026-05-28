<?php
namespace Modules\FiscalCheckbox;
use Illuminate\Support\ServiceProvider;
class FiscalCheckboxServiceProvider extends ServiceProvider {
    public function register(): void {
        if ($this->app->runningInConsole()) {
            foreach ([\App\Console\Commands\CheckboxOpenShift::class, \App\Console\Commands\CheckboxCloseShift::class] as $c) {
                if (class_exists($c)) $this->commands([$c]);
            }
        }
    }
    public function boot(): void {}
}
