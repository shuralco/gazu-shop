<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for module state.
 *
 * Resolution priority (first wins):
 *   1. `modules` DB table — UI toggle without redeploy
 *   2. ENV `MODULE_{KEY}` — CI/per-deploy override
 *   3. `config/modules.{key}.enabled` — manifest default
 *
 * The DB layer is cached (`modules:state` key, 1h ttl) and invalidated
 * by App\Models\Module observer + ModuleManager::clearCache().
 *
 * Module discovery itself (manifests from modules/*\/module.json) is
 * handled by App\Support\ModuleDiscovery — this class only owns enabled-state.
 *
 * @see config/modules.php
 * @see modules/README.md
 */
class ModuleManager
{
    private string $key;

    private array $cfg;

    private static array $cache = [];

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->cfg = (array) config("modules.{$key}", []);
    }

    public static function for(string $key): self
    {
        return self::$cache[$key] ??= new self($key);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function name(): string
    {
        return $this->cfg['name'] ?? $this->cfg['label'] ?? $this->key;
    }

    public function description(): string
    {
        return $this->cfg['description'] ?? '';
    }

    public function exists(): bool
    {
        return ! empty($this->cfg);
    }

    /**
     * True only if module is enabled AND all its required dependencies are enabled.
     * Resolution: DB row → ENV → config default.
     */
    public function enabled(): bool
    {
        if (! $this->exists()) {
            return false;
        }

        $resolved = $this->resolveEnabled();

        if (! $resolved) {
            return false;
        }

        foreach ($this->cfg['requires'] ?? [] as $dep) {
            if (! self::for($dep)->enabled()) {
                return false;
            }
        }

        return true;
    }

    /**
     * DB → ENV → config waterfall. DB takes priority so UI toggle works
     * without redeploy. ENV stays as override for CI / per-environment.
     */
    private function resolveEnabled(): bool
    {
        $state = self::loadDbState();

        if (array_key_exists($this->key, $state)) {
            return (bool) $state[$this->key];
        }

        $envKey = 'MODULE_'.strtoupper($this->key);
        $env = env($envKey);
        if ($env !== null) {
            return filter_var($env, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) ($this->cfg['enabled'] ?? false);
    }

    /**
     * Per-module settings overrides from DB. Merges manifest defaults
     * with stored values. Returns empty array if column not yet migrated.
     */
    public function settings(): array
    {
        $state = self::loadDbStateSettings();
        $defaults = [];
        foreach ($this->cfg['settings_schema'] ?? [] as $key => $schema) {
            $defaults[$key] = $schema['default'] ?? null;
        }

        return array_merge($defaults, $state[$this->key] ?? []);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings()[$key] ?? $default;
    }

    public function requires(): array
    {
        return $this->cfg['requires'] ?? [];
    }

    /**
     * @return Collection<string, ModuleManager>
     */
    public static function all(): Collection
    {
        $keys = array_keys(config('modules', []));

        return collect($keys)->mapWithKeys(fn ($k) => [$k => self::for($k)]);
    }

    public static function clearCache(): void
    {
        self::$cache = [];
        self::$tableExists = null;
        Cache::forget('modules:state');
        Cache::forget('modules:settings');
    }

    /**
     * @return array<string,bool>
     */
    private static function loadDbState(): array
    {
        try {
            return Cache::remember('modules:state', 3600, function (): array {
                if (! self::tableExists()) {
                    return [];
                }

                return \App\Models\Module::query()
                    ->pluck('enabled', 'key')
                    ->map(fn ($v) => (bool) $v)
                    ->all();
            });
        } catch (\Throwable) {
            // DB/cache not ready (e.g. during register() phase, fresh install, or
            // octane warmup) — gracefully fall back to ENV/config layer only.
            return [];
        }
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private static function loadDbStateSettings(): array
    {
        try {
            return Cache::remember('modules:settings', 3600, function (): array {
                if (! self::tableExists()) {
                    return [];
                }

                return \App\Models\Module::query()
                    ->whereNotNull('settings')
                    ->pluck('settings', 'key')
                    ->map(fn ($v) => is_array($v) ? $v : (json_decode((string) $v, true) ?: []))
                    ->all();
            });
        } catch (\Throwable) {
            return [];
        }
    }

    private static ?bool $tableExists = null;

    private static function tableExists(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }
        try {
            return self::$tableExists = Schema::hasTable('modules');
        } catch (\Throwable) {
            return self::$tableExists = false;
        }
    }
}
