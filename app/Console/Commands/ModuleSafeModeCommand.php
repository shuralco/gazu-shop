<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Support\Hooks;
use App\Support\ModuleManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Emergency safe-mode — disable ALL non-core modules so a broken module
 * stops crashing the site.
 *
 *   php artisan module:safe-mode
 *   php artisan module:safe-mode --only=related_products,reviews   (disable only listed)
 *
 * Coreові модулі (multi_warehouse) залишаються — без них магазин
 * взагалі не працює.
 */
class ModuleSafeModeCommand extends Command
{
    protected $signature = 'module:safe-mode
        {--only= : Comma-separated list of module keys to disable (default: all non-core)}
        {--yes : Skip confirmation}';

    protected $description = 'EMERGENCY: disable all non-core modules to recover from broken module crash.';

    /** Модулі що системі необхідні — НЕ disablимо в safe mode. */
    private const CORE_MODULES = ['multi_warehouse'];

    public function handle(): int
    {
        $only = $this->option('only');
        $targets = $only
            ? explode(',', (string) $only)
            : ModuleManager::all()
                ->filter(fn ($m) => $m->enabled() && ! in_array($m->key(), self::CORE_MODULES, true))
                ->keys()
                ->all();

        if (empty($targets)) {
            $this->info('Жодного модуля немає що вимикати.');
            return self::SUCCESS;
        }

        $this->warn('Вимкнути модулі:');
        foreach ($targets as $t) $this->line("  • {$t}");

        if (! $this->option('yes') && ! $this->confirm('Продовжити?', false)) {
            $this->info('Скасовано.');
            return self::SUCCESS;
        }

        foreach ($targets as $key) {
            Module::updateOrCreate(['key' => $key], [
                'enabled' => false,
                'disabled_at' => now(),
            ]);
            Hooks::do('module.disabled', $key, ['actions' => ['safe-mode']]);
            $this->line("  ○ {$key}");
        }

        ModuleManager::clearCache();
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        Artisan::call('responsecache:clear');
        Artisan::call('filament:cache-components');

        $this->info('✓ Safe mode applied. Сайт має повернутись до робочого стану.');
        $this->line('Розслідуй конкретний модуль через `module:list` + log файли.');

        return self::SUCCESS;
    }
}
