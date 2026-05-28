<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleInstaller;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

/**
 * Dry-run preview — показує що буде створено при install БЕЗ виконання.
 * CLI equivalent of the UI "Preview" button.
 *
 *   php artisan module:preview /path/to/module.zip
 */
class ModulePreviewCommand extends Command
{
    protected $signature = 'module:preview {path : Шлях до ZIP-архіву модуля}';

    protected $description = 'Dry-run preview install — показує що буде створено без виконання.';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $upload = new UploadedFile($path, basename($path), 'application/zip', null, true);

        try {
            $preview = ModuleInstaller::previewFromZip($upload);
        } catch (\Throwable $e) {
            $this->error('Preview failed: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info($preview['label'] ?? $preview['module_name']);
        $this->line('  key:     '.($preview['module_name'] ?? '—'));
        $this->line('  version: '.($preview['version'] ?? '—'));
        if (! empty($preview['description'])) {
            $this->line('  desc:    '.$preview['description']);
        }
        $this->line('');

        if (! empty($preview['will_create_tables'])) {
            $this->info('Tables to create:');
            foreach ($preview['will_create_tables'] as $t) $this->line("  • {$t}");
            $this->line('');
        }
        if (! empty($preview['routes'])) {
            $this->info('Routes registered:');
            foreach ($preview['routes'] as $r) $this->line("  • {$r}");
            $this->line('');
        }
        if (! empty($preview['filament_resources'])) {
            $this->info('Filament Resources:');
            foreach ($preview['filament_resources'] as $r) $this->line("  • {$r}");
            $this->line('');
        }
        if (! empty($preview['providers'])) {
            $this->info('Service Providers:');
            foreach ($preview['providers'] as $p) $this->line("  • {$p}");
            $this->line('');
        }
        if (! empty($preview['hooks_listened'])) {
            $this->info('Hook listeners:');
            foreach ($preview['hooks_listened'] as $h) $this->line("  • {$h}");
            $this->line('');
        }
        if (! empty($preview['requires_modules'])) {
            $this->warn('Requires modules: '.implode(', ', $preview['requires_modules']));
        }

        return self::SUCCESS;
    }
}
