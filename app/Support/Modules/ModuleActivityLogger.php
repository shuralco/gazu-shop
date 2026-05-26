<?php

namespace App\Support\Modules;

use App\Models\ModuleActivityLog;
use Illuminate\Support\Facades\Schema;

/**
 * Records module lifecycle events with current user + payload.
 *
 * Actions:
 *   - enabled, disabled
 *   - installed, upgraded, uninstalled
 *   - settings_saved, settings_reset
 *   - migration_ran
 *   - cache_cleared
 */
class ModuleActivityLogger
{
    public static function log(string $key, string $action, array $payload = []): void
    {
        // Schema check: don't crash if table not migrated yet
        try {
            if (! Schema::hasTable('module_activity_logs')) {
                return;
            }
            ModuleActivityLog::create([
                'module_key' => $key,
                'action' => $action,
                'payload' => $payload,
                'user_id' => auth()->id(),
                'ip' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Don't break user flow on logging errors
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ModuleActivityLog>
     */
    public static function recent(string $key, int $limit = 50)
    {
        if (! Schema::hasTable('module_activity_logs')) {
            return collect();
        }

        return ModuleActivityLog::where('module_key', $key)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
