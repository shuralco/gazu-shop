<?php

namespace App\Support;

use App\Models\DisplaySetting;
use Illuminate\Support\Collection;

/**
 * Resolves the active visual theme + provides a lookup over all installed themes.
 *
 * Active theme resolution waterfall:
 *   1. DisplaySetting::get('active_theme') — UI/preset toggle (DB)
 *   2. env('THEME') — per-environment override
 *   3. config('themes.default', 'gazu') — fallback
 *
 * Tokens (colors, fonts) from the active theme's manifest are exposed via
 * tokens() for runtime CSS-var injection or just for reading in code.
 *
 * @see themes/README.md
 * @see App\Support\ThemeDiscovery
 */
class ThemeManager
{
    private static ?string $active = null;

    /** @var array<string,array<string,mixed>>|null */
    private static ?array $themesCache = null;

    public static function active(): string
    {
        if (self::$active !== null) {
            return self::$active;
        }

        try {
            $stored = DisplaySetting::get('active_theme');
            if (is_string($stored) && $stored !== '') {
                return self::$active = $stored;
            }
        } catch (\Throwable) {
            // DisplaySetting may not exist during early boot / fresh install.
        }

        $env = env('THEME');
        if (is_string($env) && $env !== '') {
            return self::$active = $env;
        }

        return self::$active = (string) config('themes.default', 'gazu');
    }

    public static function setActive(string $name): void
    {
        DisplaySetting::set('active_theme', $name);
        self::$active = null; // force re-resolution next call
    }

    /**
     * Manifest of the currently-active theme.
     *
     * @return array<string,mixed>
     */
    public static function manifest(): array
    {
        $name = self::active();

        return self::themes()[$name] ?? [];
    }

    public static function tokens(): array
    {
        return (array) (self::manifest()['tokens'] ?? []);
    }

    /**
     * Runtime CSS that re-skins the storefront to the ACTIVE theme's color
     * tokens — injected into the layout <head> AFTER the built theme CSS.
     *
     * Every token maps to BOTH families the storefront uses:
     *   --gazu-{key}        (component CSS + var() references in blades)
     *   --color-gazu-{key}  (Tailwind utilities like bg-gazu-blue)
     *
     * Because all storefront styling references these vars via var(), a later
     * :root / .gazu-theme override re-skins live — NO npm build required.
     * Returns '' when the active theme declares no tokens.
     */
    public static function cssVarOverrides(): string
    {
        $tokens = self::tokens();
        if (empty($tokens)) {
            return '';
        }

        $gazu = '';
        $color = '';
        foreach ($tokens as $key => $value) {
            // Hardening: token files are admin-authored, but never trust blindly.
            if (! is_string($value) || ! preg_match('/^[a-z0-9_-]+$/i', (string) $key)) {
                continue;
            }
            $val = trim($value);
            if ($val === '' || ! preg_match('/^[#a-z0-9(),.%\/ -]+$/i', $val)) {
                continue; // only colours / safe CSS colour values
            }
            $gazu .= "--gazu-{$key}:{$val};";
            $color .= "--color-gazu-{$key}:{$val};";
        }

        if ($gazu === '' && $color === '') {
            return '';
        }

        return ".gazu-theme{{$gazu}}:root{{$color}}";
    }

    public static function cssEntry(): ?string
    {
        return self::manifest()['css_entry'] ?? null;
    }

    /**
     * Path to the active theme's root directory (or null if not installed).
     */
    public static function path(?string $name = null): ?string
    {
        $name ??= self::active();
        $manifest = self::themes()[$name] ?? null;
        if ($manifest === null) {
            return null;
        }

        return $manifest['_path'] ?? null;
    }

    /**
     * All discovered themes keyed by name.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function themes(): array
    {
        if (self::$themesCache !== null) {
            return self::$themesCache;
        }

        $root = base_path('themes');
        if (! is_dir($root)) {
            return self::$themesCache = [];
        }

        $result = [];
        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '_')) {
                continue;
            }
            $dir = $root.DIRECTORY_SEPARATOR.$entry;
            if (! is_dir($dir)) {
                continue;
            }
            $manifestFile = $dir.'/theme.json';
            if (! is_file($manifestFile)) {
                continue;
            }
            $data = json_decode((string) file_get_contents($manifestFile), true);
            if (! is_array($data) || empty($data['name'])) {
                continue;
            }
            $data['_path'] = $dir;
            $result[$data['name']] = $data;
        }

        return self::$themesCache = $result;
    }

    /**
     * @return Collection<int,string>
     */
    public static function names(): Collection
    {
        return collect(array_keys(self::themes()));
    }

    public static function clearCache(): void
    {
        self::$active = null;
        self::$themesCache = null;
    }
}
