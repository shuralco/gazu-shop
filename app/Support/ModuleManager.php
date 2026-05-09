<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Single source of truth for module state.
 * Resolves config('modules.{key}') and respects requires-chain.
 *
 * @see config/modules.php
 * @see docs/MULTI-CLIENT-ARCHITECTURE.md
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
        return $this->cfg['name'] ?? $this->key;
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
     */
    public function enabled(): bool
    {
        if (! $this->exists()) {
            return false;
        }
        if (empty($this->cfg['enabled'])) {
            return false;
        }
        foreach ($this->cfg['requires'] ?? [] as $dep) {
            if (! self::for($dep)->enabled()) {
                return false;
            }
        }

        return true;
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
    }
}
