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

    /** Optional <link> hrefs (e.g. Google Fonts) the active theme wants loaded. */
    public static function fontLinks(): array
    {
        $links = (array) (self::manifest()['font_links'] ?? []);

        return array_values(array_filter($links, fn ($u) => is_string($u)
            && preg_match('#^https://[a-z0-9.\-/_?=&%,:;+@~]+$#i', $u)));
    }

    /**
     * Runtime CSS that re-skins the storefront to the ACTIVE theme — injected into
     * the layout <head> AFTER the built theme CSS. Because all storefront styling
     * references these vars via var(), a later :root/.gazu-theme override re-skins
     * live with NO npm build. Returns '' when the theme declares nothing.
     *
     * Manifest sections (all optional):
     *   tokens — colours: key→ --gazu-{key} (component CSS + var() in blades)
     *                         + --color-gazu-{key} (Tailwind bg-gazu-* utilities)
     *   radii  — key→ --radius-{key} (Tailwind rounded-* scale: sm/md/lg/xl/2xl/3xl)
     *   fonts  — key→ --gazu-font-{key} (.gazu-display/.gazu-mono/…) + --font-{key}
     */
    public static function cssVarOverrides(): string
    {
        $manifest = self::manifest();
        $themeScope = '';   // .gazu-theme  (component CSS + var() refs)
        $rootScope = '';    // :root        (Tailwind theme tokens)

        // 1) Colours
        foreach ((array) ($manifest['tokens'] ?? []) as $key => $value) {
            $v = self::safeCssValue($value, 'color');
            if ($v === null || ! self::safeKey($key)) {
                continue;
            }
            $themeScope .= "--gazu-{$key}:{$v};";
            $rootScope .= "--color-gazu-{$key}:{$v};";
        }

        // 2) Radii → Tailwind rounded-* scale (rounded-full стається літералом, не чіпаємо)
        foreach ((array) ($manifest['radii'] ?? []) as $key => $value) {
            $v = self::safeCssValue($value, 'length');
            if ($v === null || ! self::safeKey($key) || $key === 'full') {
                continue;
            }
            $rootScope .= "--radius-{$key}:{$v};";
        }

        // 3) Fonts → component-CSS var + Tailwind font-family token
        foreach ((array) ($manifest['fonts'] ?? []) as $key => $value) {
            $v = self::safeCssValue($value, 'font');
            if ($v === null || ! self::safeKey($key)) {
                continue;
            }
            $themeScope .= "--gazu-font-{$key}:{$v};";
            $rootScope .= "--font-{$key}:{$v};";
        }

        if ($themeScope === '' && $rootScope === '') {
            return '';
        }

        return ($themeScope !== '' ? ".gazu-theme{{$themeScope}}" : '')
            . ($rootScope !== '' ? ":root{{$rootScope}}" : '');
    }

    private static function safeKey(mixed $key): bool
    {
        return is_string($key) && preg_match('/^[a-z0-9_-]+$/i', $key) === 1;
    }

    /**
     * Validate a token value before injecting it into a <style> block. Rejects
     * anything that could break out of the declaration (;, {, }, <, >, @).
     */
    private static function safeCssValue(mixed $value, string $type): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $v = trim($value);
        if ($v === '' || preg_match('/[;{}<>@]/', $v)) {
            return null;
        }

        $pattern = match ($type) {
            'color' => '/^[#a-z0-9(),.%\/ -]+$/i',                 // #hex, rgb()/hsl(), color-mix via name only
            'length' => '/^[a-z0-9.%() \/+-]+$/i',                 // 8px, .5rem, calc(...), 0
            'font' => "/^[a-z0-9 ,_'\"\\-]+$/iu",                  // font-family list with quotes
            default => '/^[a-z0-9 .,#%()\/_\"\'-]+$/i',
        };

        return preg_match($pattern, $v) === 1 ? $v : null;
    }

    public static function cssEntry(): ?string
    {
        return self::manifest()['css_entry'] ?? null;
    }

    /**
     * Абсолютний шлях до blade-views активної теми (для override core-блейдів
     * через View::prependLocation). null якщо теки немає.
     *
     * Так НОВИЙ ШАБЛОН перекриває будь-який core-blade, просто дзеркалячи його
     * шлях у themes/<name>/resources/views/ (напр. gazu/home/v1.blade.php) —
     * БЕЗ правки core. GAZU має 0 override-views → поведінка незмінна.
     */
    public static function viewsPath(?string $name = null): ?string
    {
        $root = self::path($name);
        if ($root === null) {
            return null;
        }
        $name ??= self::active();
        $rel = self::themes()[$name]['views_path'] ?? 'resources/views';
        $dir = rtrim($root, '/').'/'.ltrim((string) $rel, '/');

        return is_dir($dir) ? $dir : null;
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
