<?php

namespace App\Support\Modules;

use App\Support\ModuleDiscovery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

/**
 * Installs / exports modules as ZIP archives — admin UI flow that
 * complements the drop-in file workflow (manual git deploy).
 *
 * Install flow:
 *   1. Validate uploaded ZIP (size, structure, manifest)
 *   2. Read module.json `name` → target dir `modules/{name}/`
 *   3. Refuse to overwrite enabled modules unless force=true
 *   4. Extract into modules/{name}/
 *   5. composer dump-autoload so new classes resolve
 *   6. responsecache/view/route clear
 *
 * Export flow:
 *   1. Read modules/{name}/ → ZIP archive in storage tmp
 *   2. Return file path for download
 *
 * Security:
 *   - Module name must match /^[a-z][a-z0-9_]{1,40}$/
 *   - Max archive size 10 MB (configurable)
 *   - Refuses to extract path-traversal entries
 *   - Refuses missing module.json
 */
class ModuleInstaller
{
    private const MODULE_NAME_PATTERN = '/^[a-z][a-z0-9_]{1,40}$/';
    private const MAX_ARCHIVE_SIZE = 10 * 1024 * 1024; // 10 MB

    /**
     * Install a module from an uploaded ZIP file.
     *
     * @return array{key: string, version: ?string, action: string}
     */
    public static function installFromZip(UploadedFile $file, bool $force = false): array
    {
        if (! $file->isValid()) {
            throw new RuntimeException('Файл пошкоджено під час завантаження.');
        }

        if ($file->getSize() > self::MAX_ARCHIVE_SIZE) {
            throw new RuntimeException('Архів перевищує ліміт 10 MB.');
        }

        if (! extension_loaded('zip')) {
            throw new RuntimeException('PHP zip extension не доступне на цьому сервері.');
        }

        $tmpPath = $file->getRealPath();
        $zip = new ZipArchive();
        if ($zip->open($tmpPath) !== true) {
            throw new RuntimeException('Не вдалося відкрити ZIP-архів.');
        }

        try {
            // 1. Find module.json (must be at root OR in a single root dir)
            $manifestRaw = self::readManifestFromZip($zip);
            $manifest = json_decode($manifestRaw, true);
            if (! is_array($manifest) || empty($manifest['name'])) {
                throw new RuntimeException('module.json не містить поля "name".');
            }

            $moduleName = (string) $manifest['name'];
            if (! preg_match(self::MODULE_NAME_PATTERN, $moduleName)) {
                throw new RuntimeException("Невалідне ім'я модуля: «{$moduleName}». Лише lowercase a-z, 0-9, _.");
            }

            $targetDir = base_path('modules/'.$moduleName);
            $alreadyExists = is_dir($targetDir);

            if ($alreadyExists && ! $force) {
                throw new RuntimeException("Модуль «{$moduleName}» вже встановлено. Передай force=true для перевстановлення.");
            }

            // 2. Detect ZIP layout — root-level files vs. wrapper-folder
            $prefix = self::detectZipPrefix($zip);

            // 3. Wipe target if force-reinstall
            if ($alreadyExists) {
                File::deleteDirectory($targetDir);
            }
            File::makeDirectory($targetDir, 0755, true, true);

            // 4. Extract entries
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if ($entry === false) continue;

                // Skip __MACOSX / .DS_Store noise
                if (str_starts_with($entry, '__MACOSX/') || str_ends_with($entry, '/.DS_Store')) continue;

                // Strip the optional wrapper-folder prefix
                $relative = $prefix !== '' && str_starts_with($entry, $prefix)
                    ? substr($entry, strlen($prefix))
                    : $entry;
                if ($relative === '' || $relative === false) continue;

                // Defence in depth — refuse path traversal
                if (str_contains($relative, '..')) {
                    throw new RuntimeException("Архів містить підозрілий шлях: {$entry}");
                }

                $destPath = $targetDir.'/'.$relative;

                if (str_ends_with($relative, '/')) {
                    File::makeDirectory($destPath, 0755, true, true);
                    continue;
                }

                $parent = dirname($destPath);
                if (! is_dir($parent)) {
                    File::makeDirectory($parent, 0755, true, true);
                }

                $stream = $zip->getStream($entry);
                if ($stream === false) {
                    throw new RuntimeException("Не вдалося прочитати {$entry} з архіву.");
                }
                file_put_contents($destPath, stream_get_contents($stream));
                fclose($stream);
            }

            $zip->close();
        } catch (\Throwable $e) {
            $zip->close();
            throw $e;
        }

        // 5. Refresh autoload + caches so new classes resolve immediately
        self::refreshAutoload();

        ModuleDiscovery::clearCache();

        return [
            'key' => $moduleName,
            'version' => $manifest['version'] ?? null,
            'action' => $alreadyExists ? 'reinstalled' : 'installed',
        ];
    }

    /**
     * Export an installed module as a downloadable ZIP archive.
     * Returns the full path to the archive in storage/app/tmp/.
     */
    public static function exportToZip(string $moduleName): string
    {
        if (! preg_match(self::MODULE_NAME_PATTERN, $moduleName)) {
            throw new RuntimeException("Невалідне ім'я модуля: «{$moduleName}».");
        }

        $sourceDir = base_path('modules/'.$moduleName);
        if (! is_dir($sourceDir)) {
            throw new RuntimeException("Модуль не знайдено: modules/{$moduleName}");
        }
        if (! is_file($sourceDir.'/module.json')) {
            throw new RuntimeException('Відсутній module.json — модуль некоректно встановлений.');
        }

        if (! extension_loaded('zip')) {
            throw new RuntimeException('PHP zip extension не доступне.');
        }

        $tmpDir = storage_path('app/tmp/modules');
        if (! is_dir($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true, true);
        }

        $archivePath = $tmpDir.'/'.$moduleName.'-'.date('Ymd-His').'.zip';
        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Не вдалося створити архів.');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $skip = ['.git', '.idea', '.DS_Store', 'node_modules', 'vendor'];
        foreach ($files as $file) {
            $name = $file->getFilename();
            if (in_array($name, $skip, true)) continue;

            $relative = ltrim(str_replace($sourceDir, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            if ($file->isDir()) {
                $zip->addEmptyDir($relative);
            } else {
                $zip->addFile($file->getPathname(), $relative);
            }
        }

        $zip->close();

        return $archivePath;
    }

    /**
     * Uninstall a module. Two modes:
     *
     *   purgeData=false (default) — soft uninstall:
     *     1. delete modules/{name}/ directory
     *     2. refresh autoload + clear caches
     *     DB tables/data залишаються — переустановка з тим самим іменем
     *     відновить доступ до старих даних.
     *
     *   purgeData=true — hard uninstall:
     *     1. rollback module migrations (drops tables)
     *     2. delete modules table row + activity log
     *     3. delete modules/{name}/ directory
     *     4. refresh autoload
     *
     * @return array{mode: string, files_removed: int, tables_dropped: ?int}
     */
    public static function uninstall(string $moduleName, bool $purgeData = false): array
    {
        if (! preg_match(self::MODULE_NAME_PATTERN, $moduleName)) {
            throw new RuntimeException("Невалідне ім'я модуля: «{$moduleName}».");
        }

        $targetDir = base_path('modules/'.$moduleName);
        if (! is_dir($targetDir)) {
            throw new RuntimeException("Модуль не знайдено: modules/{$moduleName}");
        }

        // Refuse if enabled — admin must disable first to trigger
        // proper lifecycle disable() hooks.
        $enabled = (bool) optional(\DB::table('modules')->where('key', $moduleName)->first())->enabled;
        if ($enabled) {
            throw new RuntimeException("Спершу вимкніть модуль «{$moduleName}», потім видаляйте.");
        }

        // Refuse if other enabled modules depend on this one.
        $dependents = self::activeDependentsOf($moduleName);
        if (! empty($dependents)) {
            throw new RuntimeException(
                "Від «{$moduleName}» залежать активні модулі: ".implode(', ', $dependents).
                '. Спочатку вимкніть їх.'
            );
        }

        $tablesDropped = null;

        if ($purgeData) {
            // Rollback all migrations in this module — drops the tables.
            $migPath = $targetDir.'/database/migrations';
            if (is_dir($migPath)) {
                $output = new \Symfony\Component\Console\Output\BufferedOutput();
                Artisan::call('migrate:rollback', [
                    '--path' => 'modules/'.$moduleName.'/database/migrations',
                    '--force' => true,
                ], $output);
                // crude count: lines containing "Rolling back" or "Rolled back"
                $tablesDropped = substr_count($output->fetch(), "\n");
            }

            // Drop DB-level state.
            \DB::table('modules')->where('key', $moduleName)->delete();
            if (\Schema::hasTable('module_activity_logs')) {
                \DB::table('module_activity_logs')->where('module_key', $moduleName)->delete();
            }
        }

        // Delete folder.
        $fileCount = self::countFiles($targetDir);
        File::deleteDirectory($targetDir);

        // Refresh autoload — composer-classmap now no longer points to
        // the (deleted) module classes.
        self::refreshAutoload();
        ModuleDiscovery::clearCache();

        return [
            'mode' => $purgeData ? 'hard' : 'soft',
            'files_removed' => $fileCount,
            'tables_dropped' => $tablesDropped,
        ];
    }

    /**
     * Names of modules that (a) require $moduleName AND (b) are currently enabled.
     *
     * @return array<int,string>
     */
    private static function activeDependentsOf(string $moduleName): array
    {
        $dependents = [];
        foreach (ModuleDiscovery::manifests() as $key => $manifest) {
            if (! in_array($moduleName, $manifest['requires_modules'] ?? [], true)) continue;
            $enabled = (bool) optional(\DB::table('modules')->where('key', $key)->first())->enabled;
            if ($enabled) $dependents[] = $key;
        }
        return $dependents;
    }

    private static function countFiles(string $dir): int
    {
        $count = 0;
        try {
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iter as $f) {
                if ($f->isFile()) $count++;
            }
        } catch (\Throwable $e) {
            // ignore — just report what we counted
        }
        return $count;
    }

    private static function readManifestFromZip(ZipArchive $zip): string
    {
        // Try root-level module.json first
        $contents = $zip->getFromName('module.json');
        if ($contents !== false) return $contents;

        // Then any single-wrapper-folder/module.json (first match)
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name !== false && str_ends_with($name, '/module.json') && substr_count($name, '/') === 1) {
                $contents = $zip->getFromName($name);
                if ($contents !== false) return $contents;
            }
        }

        throw new RuntimeException('У ZIP не знайдено module.json — це не модуль SimpleShop.');
    }

    /**
     * If module.json sits inside `myname/module.json`, return `myname/` as prefix to strip.
     * If it sits at root, return ''.
     */
    private static function detectZipPrefix(ZipArchive $zip): string
    {
        if ($zip->getFromName('module.json') !== false) return '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name !== false && str_ends_with($name, '/module.json') && substr_count($name, '/') === 1) {
                return substr($name, 0, strpos($name, '/') + 1);
            }
        }
        return '';
    }

    private static function refreshAutoload(): void
    {
        // Try composer first — most reliable for new classes.
        $composer = self::findComposerBinary();
        if ($composer) {
            // Use proc_open so we capture exit code without throwing.
            $cmd = $composer.' dump-autoload --no-interaction --no-scripts 2>&1';
            exec($cmd, $output, $exitCode);
            if ($exitCode === 0) {
                Artisan::call('view:clear');
                Artisan::call('cache:clear');
                Artisan::call('responsecache:clear');
                Artisan::call('filament:cache-components');
                return;
            }
        }
        // Fallback — at least drop framework caches so views/routes re-resolve.
        Artisan::call('optimize:clear');
        Artisan::call('responsecache:clear');
        Artisan::call('filament:cache-components');
    }

    private static function findComposerBinary(): ?string
    {
        foreach (['/usr/local/bin/composer', '/usr/bin/composer', 'composer'] as $candidate) {
            $check = $candidate === 'composer' ? trim(shell_exec('which composer 2>/dev/null') ?? '') : (is_executable($candidate) ? $candidate : '');
            if ($check && file_exists($check)) {
                return escapeshellarg($check);
            }
        }
        return null;
    }
}
