<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleInstaller;
use Illuminate\Console\Command;

/**
 * Export an installed module to a ZIP archive.
 *
 *   php artisan module:export related_products
 *   php artisan module:export related_products --out=/tmp/myzip.zip
 */
class ModuleExportCommand extends Command
{
    protected $signature = 'module:export
        {key : Module key}
        {--out= : Path to save the ZIP archive (default: storage/app/tmp/modules/)}';

    protected $description = 'Export a module to a downloadable ZIP archive.';

    public function handle(): int
    {
        $key = (string) $this->argument('key');

        try {
            $path = ModuleInstaller::exportToZip($key);
        } catch (\Throwable $e) {
            $this->error('Export failed: '.$e->getMessage());
            return self::FAILURE;
        }

        if ($out = $this->option('out')) {
            $outPath = (string) $out;
            // Якщо --out папка існує, кладемо туди з оригінальним ім'ям.
            if (is_dir($outPath)) {
                $outPath = rtrim($outPath, '/').'/'.basename($path);
            }
            if (! @rename($path, $outPath)) {
                $this->error("Cannot move archive to: {$outPath}");
                return self::FAILURE;
            }
            $path = $outPath;
        }

        $size = filesize($path);
        $this->info("✓ Exported: {$path}");
        $this->line("  Size: ".number_format($size / 1024, 1).' KB');

        return self::SUCCESS;
    }
}
