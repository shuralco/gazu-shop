<?php

namespace App\Console\Commands;

use App\Support\ModuleManager;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List all registered modules with their enabled/disabled status.';

    public function handle(): int
    {
        $rows = ModuleManager::all()->map(fn ($m) => [
            $m->key(),
            $m->name(),
            $m->enabled() ? '✓ on' : '✗ off',
            implode(', ', $m->requires()) ?: '—',
        ])->all();

        $this->table(['Key', 'Name', 'Status', 'Requires'], $rows);

        return self::SUCCESS;
    }
}
