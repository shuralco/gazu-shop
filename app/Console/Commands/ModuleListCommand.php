<?php

namespace App\Console\Commands;

use App\Support\Hooks;
use App\Support\ModuleManager;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List all registered modules with their enabled/disabled status.';

    public function handle(): int
    {
        $rows = ModuleManager::all()->map(function ($m) {
            $hookEvents = Hooks::eventsBySource($m->key());
            return [
                $m->key(),
                $m->name(),
                $m->enabled() ? '✓ on' : '✗ off',
                implode(', ', $m->requires()) ?: '—',
                count($hookEvents) > 0 ? (string) count($hookEvents) : '—',
            ];
        })->all();

        $this->table(['Key', 'Name', 'Status', 'Requires', 'Hooks'], $rows);

        return self::SUCCESS;
    }
}
