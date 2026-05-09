<?php

namespace App\Console\Commands;

use App\Support\ModuleManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable {key* : One or more module keys}';

    protected $description = 'Disable one or more modules (data preserved, UI hidden).';

    public function handle(): int
    {
        foreach ((array) $this->argument('key') as $key) {
            if (! ModuleManager::for($key)->exists()) {
                $this->error("Unknown module: {$key}");
                continue;
            }

            // Warn about cascading dependencies
            $dependents = ModuleManager::all()
                ->filter(fn ($m) => in_array($key, $m->requires(), true) && $m->enabled())
                ->keys();
            if ($dependents->isNotEmpty()) {
                $this->warn(sprintf(
                    'Disabling "%s" will also break these modules (they require it): %s',
                    $key,
                    $dependents->implode(', '),
                ));
            }

            $this->setEnvFlag($key, false);
            $this->info("Disabled: {$key}");
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
