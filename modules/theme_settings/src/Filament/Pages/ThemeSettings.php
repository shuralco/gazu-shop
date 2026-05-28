<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;

/**
 * Admin UI for switching the active storefront theme without CLI.
 * Mirrors `php artisan theme:use {name}` logic.
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

    public string $activeTheme = 'brutal';

    public array $availableThemes = [];

    public function mount(): void
    {
        $this->loadThemes();
    }

    protected function loadThemes(): void
    {
        $tokensDir = resource_path('css/tokens');
        $this->availableThemes = collect(File::files($tokensDir))
            ->map(fn ($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
            ->filter(fn ($n) => $n !== 'active')
            ->values()
            ->all();

        $appCss = File::get(resource_path('css/app.css'));
        if (preg_match('/@import\s+\'\.\/tokens\/([a-z0-9-]+)\.css\';/i', $appCss, $m)) {
            $this->activeTheme = $m[1];
        }
    }

    public function getActions(): array
    {
        return [];
    }

    public function activateTheme(string $name): void
    {
        if (! in_array($name, $this->availableThemes, true)) {
            Notification::make()->title('Невідома тема')->danger()->send();

            return;
        }

        $appCss = resource_path('css/app.css');
        $css = File::get($appCss);
        $updated = preg_replace(
            '/@import\s+\'\.\/tokens\/[a-z0-9-]+\.css\';/i',
            "@import './tokens/{$name}.css';",
            $css,
            1,
        );

        if ($updated === null || $updated === $css) {
            Notification::make()
                ->title('Не знайдено імпорт токенів у app.css')
                ->danger()
                ->send();

            return;
        }

        File::put($appCss, $updated);
        $this->activeTheme = $name;

        Notification::make()
            ->title("Тема активована: {$name}")
            ->body('Тепер виконайте `npm run build` (або `vite dev`) щоб новий стиль застосувався в браузері.')
            ->success()
            ->persistent()
            ->send();
    }

    public function previewToken(string $theme, string $token): ?string
    {
        $path = resource_path("css/tokens/{$theme}.css");
        if (! File::exists($path)) {
            return null;
        }
        $content = File::get($path);
        if (preg_match('/--'.preg_quote($token, '/').'\s*:\s*([^;]+);/', $content, $m)) {
            return trim($m[1]);
        }

        return null;
    }
}
