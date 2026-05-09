<?php

namespace App\Console\Commands;

use App\Support\ModuleManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Enable a module by writing MODULE_{KEY}=true into .env (or create the
 * line if missing). User must clear cache + rebuild Filament/views to
 * see the effect — we do that automatically here.
 */
class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable {key* : One or more module keys}';

    protected $description = 'Enable one or more modules (sets MODULE_{KEY}=true in .env).';

    public function handle(): int
    {
        foreach ((array) $this->argument('key') as $key) {
            if (! ModuleManager::for($key)->exists()) {
                $this->error("Unknown module: {$key}");
                continue;
            }
            $this->setEnvFlag($key, true);
            $this->info("Enabled: {$key}");
        }

        $this->call('config:clear');
        $this->call('cache:clear');
        ModuleManager::clearCache();

        return self::SUCCESS;
    }

    private function setEnvFlag(string $key, bool $value): void
    {
        $envPath = base_path('.env');
        $envKey = 'MODULE_'.strtoupper($key);
        $line = "{$envKey}=".($value ? 'true' : 'false');

        if (! File::exists($envPath)) {
            File::put($envPath, $line."\n");

            return;
        }

        $content = File::get($envPath);
        if (preg_match('/^'.preg_quote($envKey, '/').'=.*/m', $content)) {
            $content = preg_replace('/^'.preg_quote($envKey, '/').'=.*/m', $line, $content);
        } else {
            $content = rtrim($content, "\n")."\n".$line."\n";
        }
        File::put($envPath, $content);
    }
}
