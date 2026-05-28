<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleInstaller;
use Illuminate\Console\Command;

/**
 * Uninstall a module via CLI.
 *
 *   php artisan module:uninstall related_products
 *   php artisan module:uninstall related_products --purge   (drop tables + data)
 *
 * Fires module.uninstalled hook event. Refuses if module is enabled
 * or has active dependents (same guards as UI).
 */
class ModuleUninstallCommand extends Command
{
    protected $signature = 'module:uninstall
        {key : Module key}
        {--purge : Hard delete — also drop tables and clear module DB rows}
        {--yes : Skip confirmation prompt}';

    protected $description = 'Uninstall a module (soft: лише папка; --purge: + drop tables).';

    public function handle(): int
    {
        $key = (string) $this->argument('key');
        $purge = (bool) $this->option('purge');

        if (! $this->option('yes')) {
            $mode = $purge ? 'HARD (drop tables + DB rows + folder)' : 'SOFT (видалити лише папку)';
            if (! $this->confirm("Видалити модуль «{$key}» — {$mode}?", false)) {
                $this->info('Скасовано.');
                return self::SUCCESS;
            }
        }

        try {
            $result = ModuleInstaller::uninstall($key, $purge);
        } catch (\Throwable $e) {
            $this->error('Uninstall failed: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info("✓ Mode: {$result['mode']}");
        $this->line("  Files removed: {$result['files_removed']}");
        if ($result['tables_dropped'] !== null) {
            $this->line("  Tables dropped: {$result['tables_dropped']}");
        }

        return self::SUCCESS;
    }
}
