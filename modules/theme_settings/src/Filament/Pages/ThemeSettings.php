<?php

namespace App\Filament\Pages;

use App\Support\ThemeManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

/**
 * Admin UI for switching the active storefront theme — instantly, with NO build.
 *
 * Backed by the real theme system (App\Support\ThemeManager + themes/*\/theme.json):
 *   - active theme persisted in DisplaySetting('active_theme') (DB)
 *   - storefront layout injects the theme's color tokens at runtime
 *     (ThemeManager::cssVarOverrides()) → re-skins live without npm build
 *   - saving the DisplaySetting auto-busts the storefront ResponseCache
 *
 * To add a theme: drop themes/<name>/theme.json (copy themes/gazu) — it appears
 * here automatically and switches instantly.
 */
class ThemeSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?string $navigationLabel = 'Тема магазину';

    protected static ?string $title = 'Тема магазину';

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'theme-settings';

    protected static string $view = 'filament.pages.theme-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    public string $activeTheme = 'gazu';

    /** @var array<int,array{name:string,label:string,description:?string,tokens:array<string,string>}> */
    public array $themes = [];

    public function mount(): void
    {
        $this->loadThemes();
    }

    protected function loadThemes(): void
    {
        ThemeManager::clearCache();
        $this->activeTheme = ThemeManager::active();

        $this->themes = collect(ThemeManager::themes())
            ->map(fn (array $m, string $name) => [
                'name' => $name,
                'label' => $m['label'] ?? $name,
                'description' => $m['description'] ?? null,
                'tokens' => (array) ($m['tokens'] ?? []),
            ])
            ->values()
            ->all();
    }

    public function activateTheme(string $name): void
    {
        ThemeManager::clearCache();

        if (! ThemeManager::names()->contains($name)) {
            Notification::make()->title('Невідома тема')->danger()->send();

            return;
        }

        // Persists DisplaySetting('active_theme') → ResponseCacheObserver flushes
        // the storefront cache automatically. Belt-and-suspenders explicit clear.
        ThemeManager::setActive($name);
        try {
            Artisan::call('responsecache:clear');
        } catch (\Throwable) {
            // ResponseCache may be disabled in some envs — observer already handled it.
        }

        ThemeManager::clearCache();
        $this->activeTheme = $name;

        Notification::make()
            ->title("Тема активована: {$name}")
            ->body('Застосовано миттєво — без перебудови. Оновіть вітрину, щоб побачити.')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    public function previewToken(string $themeName, string $key): ?string
    {
        foreach ($this->themes as $t) {
            if ($t['name'] === $themeName) {
                $v = $t['tokens'][$key] ?? null;

                return is_string($v) ? $v : null;
            }
        }

        return null;
    }
}
