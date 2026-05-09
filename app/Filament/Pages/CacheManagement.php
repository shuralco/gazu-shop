<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CacheManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationLabel = '🧹 Керування кешами';

    protected static ?string $title = 'Керування всіма кешами системи';

    protected static ?string $navigationGroup = 'Система';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.cache-management';

    public function getCacheStats(): array
    {
        $cacheDir = storage_path('framework/cache/data');
        $viewCacheDir = storage_path('framework/views');
        $configCacheFile = bootstrap_path('cache/config.php');
        $routeCacheFile = bootstrap_path('cache/routes-v7.php');

        return [
            'cache_files' => File::exists($cacheDir) ? count(File::files($cacheDir)) : 0,
            'cache_size' => File::exists($cacheDir) ? $this->formatBytes($this->getDirectorySize($cacheDir)) : '0 B',
            'view_cache_files' => File::exists($viewCacheDir) ? count(File::files($viewCacheDir)) : 0,
            'view_cache_size' => File::exists($viewCacheDir) ? $this->formatBytes($this->getDirectorySize($viewCacheDir)) : '0 B',
            'config_cached' => File::exists($configCacheFile),
            'routes_cached' => File::exists($routeCacheFile),
        ];
    }

    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        if (File::exists($directory)) {
            foreach (File::allFiles($directory) as $file) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_all_cache')
                ->label('🔥 ОЧИСТИТИ ВСЬ КЕШ')
                ->color('danger')
                ->icon('heroicon-o-fire')
                ->requiresConfirmation()
                ->modalHeading('Очистити абсолютно весь кеш?')
                ->modalDescription('Це видалить ВСІ кеші системи: application, config, routes, views, compiled, OPcache')
                ->modalSubmitActionLabel('ТАК, ОЧИСТИТИ ВСЕ!')
                ->action(function () {
                    try {
                        // Очистити всі Laravel кеші
                        Artisan::call('cache:clear');
                        Artisan::call('config:clear');
                        Artisan::call('route:clear');
                        Artisan::call('view:clear');
                        Artisan::call('optimize:clear');

                        // Очистити OPcache якщо доступний
                        if (function_exists('opcache_reset')) {
                            opcache_reset();
                        }

                        // Видалити всі файли кешу вручну
                        $this->clearAllCacheFiles();

                        Notification::make()
                            ->title('✅ ВСЬ КЕШ ОЧИЩЕНО!')
                            ->body('Абсолютно всі кеші системи видалено успішно')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('❌ Помилка очищення кешу')
                            ->body('Деталі: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('clear_app_cache')
                ->label('📦 Application Cache')
                ->color('warning')
                ->icon('heroicon-o-cube')
                ->action(function () {
                    Artisan::call('cache:clear');

                    Notification::make()
                        ->title('Application cache очищено')
                        ->success()
                        ->send();
                }),

            Action::make('clear_config_cache')
                ->label('⚙️ Config Cache')
                ->color('info')
                ->icon('heroicon-o-cog-6-tooth')
                ->action(function () {
                    Artisan::call('config:clear');

                    Notification::make()
                        ->title('Config cache очищено')
                        ->success()
                        ->send();
                }),

            Action::make('clear_view_cache')
                ->label('👁️ View Cache')
                ->color('success')
                ->icon('heroicon-o-eye')
                ->action(function () {
                    Artisan::call('view:clear');

                    Notification::make()
                        ->title('View cache очищено')
                        ->success()
                        ->send();
                }),

            Action::make('warm_cache')
                ->label('🔥 Прогріти кеш')
                ->color('gray')
                ->icon('heroicon-o-fire')
                ->action(function () {
                    $this->warmCriticalCache();

                    Notification::make()
                        ->title('🔥 Кеш прогрітий!')
                        ->body('Критичні дані завантажені в кеш')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function clearAllCacheFiles(): void
    {
        $paths = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            bootstrap_path('cache'),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                File::deleteDirectory($path);
                File::makeDirectory($path, 0755, true);
            }
        }
    }

    private function warmCriticalCache(): void
    {
        // Прогрітий кеш для критичних даних
        \App\Models\Category::whereNull('parent_id')->withCount('products')->get();
        \App\Models\Product::where('is_hit', 1)->limit(20)->get();
        \App\Models\Product::where('is_new', 1)->limit(20)->get();

        // Популярні категорії
        $popularSlugs = ['laptops', 'phones', 'accessories', 'clothing'];
        foreach ($popularSlugs as $slug) {
            $category = \App\Models\Category::findBySlug($slug);
            if ($category) {
                \App\Models\Product::where('category_id', $category->id)->limit(50)->get();
            }
        }
    }

    public function clearSpecificCache(string $pattern): void
    {
        try {
            if ($pattern === 'home_page_data') {
                Cache::forget('home_page_data');
                Cache::forget('home_categories');
                $message = 'Кеш головної сторінки очищено';
            } elseif (str_contains($pattern, 'category_*')) {
                $this->clearCacheByPattern('category_*');
                $message = 'Кеш всіх категорій очищено';
            } elseif (str_contains($pattern, 'hit_products_*')) {
                $this->clearCacheByPattern('hit_products_*');
                $message = 'Кеш хіт товарів очищено';
            } elseif (str_contains($pattern, 'new_products_*')) {
                $this->clearCacheByPattern('new_products_*');
                $message = 'Кеш нових товарів очищено';
            } else {
                Cache::forget($pattern);
                $message = "Кеш '{$pattern}' очищено";
            }

            $this->logCacheAction('Селективне очищення', $message);

            Notification::make()
                ->title('✅ Кеш очищено')
                ->body($message)
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Помилка')
                ->body('Не вдалося очистити кеш: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearOpcache(): void
    {
        try {
            if (function_exists('opcache_reset')) {
                opcache_reset();
                $message = 'OPcache очищено успішно';
            } else {
                $message = 'OPcache недоступний на цьому сервері';
            }

            $this->logCacheAction('OPcache', $message);

            Notification::make()
                ->title('🚀 OPcache')
                ->body($message)
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Помилка OPcache')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearSessions(): void
    {
        try {
            $sessionPath = storage_path('framework/sessions');
            if (File::exists($sessionPath)) {
                $files = File::files($sessionPath);
                foreach ($files as $file) {
                    File::delete($file->getPathname());
                }
                $message = 'Видалено '.count($files).' файлів сесій';
            } else {
                $message = 'Папка сесій не знайдена';
            }

            $this->logCacheAction('Сесії', $message);

            Notification::make()
                ->title('👥 Сесії очищено')
                ->body($message)
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Помилка очищення сесій')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function optimizeForProduction(): void
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            Artisan::call('optimize');

            $this->logCacheAction('Продакшн оптимізація', 'Кешування config, routes, views завершено');

            Notification::make()
                ->title('⚡ Оптимізовано для продакшн!')
                ->body('Config, routes та views закешовано для максимальної швидкості')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Помилка оптимізації')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function clearCacheByPattern(string $pattern): void
    {
        $cacheDir = storage_path('framework/cache/data');
        if (File::exists($cacheDir)) {
            $pattern = str_replace('*', '', $pattern);
            $files = File::files($cacheDir);
            $deletedCount = 0;

            foreach ($files as $file) {
                if (str_contains($file->getFilename(), $pattern)) {
                    File::delete($file->getPathname());
                    $deletedCount++;
                }
            }
        }
    }

    private function logCacheAction(string $action, string $details): void
    {
        $logs = session('cache_logs', []);

        // Додати новий лог
        array_unshift($logs, [
            'time' => now()->format('H:i:s'),
            'action' => $action,
            'details' => $details,
        ]);

        // Зберегти тільки останні 20 записів
        $logs = array_slice($logs, 0, 20);

        session(['cache_logs' => $logs]);
    }
}
