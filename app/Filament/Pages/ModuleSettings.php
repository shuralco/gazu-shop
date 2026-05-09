<?php

namespace App\Filament\Pages;

use App\Support\ModuleManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Admin UI to toggle modules on/off without CLI. Mirrors
 * `php artisan module:enable / module:disable` logic.
 */
class ModuleSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?string $navigationLabel = 'Модулі';

    protected static ?string $title = 'Модулі магазину';

    protected static ?int $navigationSort = 51;

    protected static ?string $slug = 'modules';

    protected static string $view = 'filament.pages.module-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    public function getModules(): array
    {
        return ModuleManager::all()
            ->map(fn ($m) => [
                'key' => $m->key(),
                'name' => $m->name(),
                'description' => $m->description(),
                'enabled' => $m->enabled(),
                'requires' => $m->requires(),
                'dependents' => ModuleManager::all()
                    ->filter(fn ($x) => in_array($m->key(), $x->requires(), true))
                    ->keys()
                    ->all(),
            ])
            ->all();
    }

    public function toggleModule(string $key, bool $enable): void
    {
        if (! ModuleManager::for($key)->exists()) {
            Notification::make()->title('Невідомий модуль')->danger()->send();

            return;
        }

        $envKey = 'MODULE_'.strtoupper($key);
        $line = "{$envKey}=".($enable ? 'true' : 'false');
        $envPath = base_path('.env');

        $content = File::exists($envPath) ? File::get($envPath) : '';
        if (preg_match('/^'.preg_quote($envKey, '/').'=.*/m', $content)) {
            $content = preg_replace('/^'.preg_quote($envKey, '/').'=.*/m', $line, $content);
        } else {
            $content = rtrim($content, "\n")."\n".$line."\n";
        }
        File::put($envPath, $content);

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        ModuleManager::clearCache();

        Notification::make()
            ->title($enable ? "Модуль увімкнено: {$key}" : "Модуль вимкнено: {$key}")
            ->body($enable
                ? 'Можливо потрібно перебудувати CSS, якщо модуль додає frontend елементи.'
                : 'Дані залишаються в БД — re-enable відновить функціонал миттєво.')
            ->success()
            ->send();

        // Force fresh load of module state
        $this->dispatch('$refresh');
    }
}
