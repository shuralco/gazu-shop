<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleInstaller;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

/**
 * Install a module from a ZIP file via CLI — WP-CLI equivalent of the
 * "Завантажити модуль з .zip" UI form. Fires the same lifecycle hooks
 * (module.installing / installed / install_failed) so existing listeners
 * (Telegram, audit log) work identically headless.
 *
 *   php artisan module:install /path/to/module.zip
 *   php artisan module:install /path/to/module.zip --force --enable
 */
class ModuleInstallCommand extends Command
{
    protected $signature = 'module:install
        {path : Шлях до ZIP-архіву модуля}
        {--force : Перезаписати модуль якщо вже існує}
        {--enable : Одразу увімкнути після install}';

    protected $description = 'Install a module from a ZIP archive (WP-CLI style headless installer).';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $upload = new UploadedFile($path, basename($path), 'application/zip', null, true);

        try {
            $result = ModuleInstaller::installFromZip($upload, (bool) $this->option('force'));
        } catch (\Throwable $e) {
            $this->error('Install failed: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info("✓ {$result['action']}: {$result['key']} v".($result['version'] ?? '—'));

        if ($this->option('enable')) {
            $this->line('');
            $this->call('module:enable', ['key' => [$result['key']]]);
        } else {
            $this->line("Run `php artisan module:enable {$result['key']}` to activate.");
        }

        return self::SUCCESS;
    }
}
