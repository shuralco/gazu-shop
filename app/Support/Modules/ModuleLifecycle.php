<?php

namespace App\Support\Modules;

/**
 * Optional lifecycle handler for a module. Implement this and declare in
 * module.json:
 *
 *   "lifecycle": "Modules\\Loyalty\\Lifecycle"
 *
 * ModuleLifecycleRunner calls the appropriate method when the module is
 * enabled, disabled, freshly installed, or upgraded.
 *
 * All methods are OPTIONAL — implement only what you need. The runner
 * defensively checks method existence before calling.
 */
interface ModuleLifecycle
{
    /**
     * Called once when the module is enabled for the first time
     * (installed_version was null). After this, the runner sets
     * installed_version = manifest.version. Typical work:
     *
     *   - Seed default data into module tables
     *   - Set up Redis structures / external services
     *   - Send activation telemetry
     *
     * Migrations are run automatically by the runner BEFORE this method —
     * don't run them yourself.
     */
    public function install(): void;

    /**
     * Called when module.version changes after enable. $from is the
     * previously-installed version (string), $to is the new one.
     *
     * Use to migrate data structures, rename settings keys, etc.
     * Migrations from migrations_path run automatically — handle data
     * transformations here.
     */
    public function upgrade(string $from, string $to): void;

    /**
     * Called when the module is explicitly purged (not just disabled).
     * Should drop module-specific tables and clean up external resources.
     *
     * Not invoked on disable — only on `module:purge` artisan command.
     */
    public function uninstall(): void;

    /**
     * Called every time the module is enabled (after install if applicable).
     * Lightweight — runs on every boot when enabled. Use to register
     * runtime event listeners, schedule cron jobs, warm caches.
     *
     * Don't put database writes here — this runs on every request boot.
     */
    public function boot(): void;

    /**
     * Called when module is disabled (but data preserved). Use to clean
     * up runtime resources: cancel jobs, flush module-scoped caches.
     */
    public function disable(): void;
}
