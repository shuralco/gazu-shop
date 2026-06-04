<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Cache control panel. Three tiers:
 *
 *   1. NUCLEAR — Clear All (response + app + config + view + route + OPcache).
 *   2. PER-DRIVER — окремо: response cache / app cache / config / view / route /
 *      OPcache / sessions / filament assets.
 *   3. PER-DOMAIN — granular tag flush (products, categories, brands, blog,
 *      cars, settings, warehouses).
 *
 * Plus: Redis stats, Octane reload, warm-up critical pages.
 */
class CacheManagement extends Page
{
    use \App\Filament\Concerns\GatedPage;

    protected static ?string $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = 'Керування кешами';
    protected static ?string $title = 'Cache Control Panel';
    protected static ?string $navigationGroup = 'Обслуговування';
    protected static ?int $navigationSort = 30;
    protected static string $view = 'filament.pages.cache-management';


    public function getCacheStats(): array
    {
        $cacheDir = storage_path('framework/cache/data');
        $viewCacheDir = storage_path('framework/views');
        $configCacheFile = base_path('bootstrap/cache/config.php');
        $routeCacheFile = base_path('bootstrap/cache/routes-v7.php');

        // Redis stats (gracefully handle missing connection)
        $redisInfo = null;
        try {
            $info = Redis::connection()->info('memory');
            $keys = count(Redis::connection()->keys('*'));
            $redisInfo = [
                'used_memory_human' => $info['Memory']['used_memory_human'] ?? $info['used_memory_human'] ?? 'n/a',
                'total_keys' => $keys,
            ];
        } catch (\Throwable $e) {
            $redisInfo = ['error' => $e->getMessage()];
        }

        return [
            'cache_files' => File::exists($cacheDir) ? count(File::files($cacheDir)) : 0,
            'cache_size' => File::exists($cacheDir) ? $this->formatBytes($this->getDirectorySize($cacheDir)) : '0 B',
            'view_cache_files' => File::exists($viewCacheDir) ? count(File::files($viewCacheDir)) : 0,
            'view_cache_size' => File::exists($viewCacheDir) ? $this->formatBytes($this->getDirectorySize($viewCacheDir)) : '0 B',
            'config_cached' => File::exists($configCacheFile),
            'routes_cached' => File::exists($routeCacheFile),
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status(false) !== false,
            'octane_active' => extension_loaded('swoole') || extension_loaded('openswoole'),
            'cache_driver' => config('cache.default'),
            'response_cache_driver' => config('responsecache.cache_store'),
            'redis' => $redisInfo,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── NUCLEAR — clear everything + reload Octane ─────────────────────
            Action::make('clear_all')
                ->label('Очистити ВЕСЬ кеш')
                ->color('danger')
                ->icon('heroicon-o-fire')
                ->requiresConfirmation()
                ->modalHeading('Очистити абсолютно весь кеш + reload Octane?')
                ->modalDescription('Response cache + application + config + routes + views + OPcache + Octane workers. Це знадобиться після деплою, коли потрібно щоб нові assets / config підхопились без рестарту контейнера.')
                ->modalSubmitActionLabel('ТАК, ОЧИСТИТИ')
                ->action(function () {
                    $this->safely(function () {
                        ResponseCache::clear();
                        Artisan::call('cache:clear');
                        Artisan::call('config:clear');
                        Artisan::call('route:clear');
                        Artisan::call('view:clear');
                        Artisan::call('optimize:clear');
                        if (function_exists('opcache_reset')) opcache_reset();
                        try { Artisan::call('octane:reload'); } catch (\Throwable $e) { /* not on octane env */ }
                    }, '✅ Весь кеш очищено + Octane reload', 'Response + Application + Config + Routes + Views + OPcache + workers reload');
                }),

            // ── PER-DRIVER actions grouped ──────────────────────────────────────
            ActionGroup::make([
                Action::make('clear_response_cache')
                    ->label('Response cache (HTML)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => $this->safely(
                        fn () => ResponseCache::clear(),
                        'Response cache очищено',
                        'Spatie ResponseCache (Redis tag «gazu-response») спорожнено'
                    )),
                Action::make('clear_app_cache')
                    ->label('Application cache (Cache::*)')
                    ->icon('heroicon-o-cube')
                    ->action(fn () => $this->safely(
                        fn () => Artisan::call('cache:clear'),
                        'Application cache очищено',
                        'Cache::store(default)->flush() виконано'
                    )),
                Action::make('clear_config')
                    ->label('Config cache')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->action(fn () => $this->safely(
                        fn () => Artisan::call('config:clear'),
                        'Config cache очищено',
                        'bootstrap/cache/config.php видалено'
                    )),
                Action::make('clear_view')
                    ->label('View cache (Blade)')
                    ->icon('heroicon-o-eye')
                    ->action(fn () => $this->safely(
                        fn () => Artisan::call('view:clear'),
                        'View cache очищено',
                        'Compiled Blade views видалено'
                    )),
                Action::make('clear_route')
                    ->label('Route cache')
                    ->icon('heroicon-o-map')
                    ->action(fn () => $this->safely(
                        fn () => Artisan::call('route:clear'),
                        'Route cache очищено',
                        'bootstrap/cache/routes-v7.php видалено'
                    )),
                Action::make('clear_opcache')
                    ->label('OPcache')
                    ->icon('heroicon-o-bolt')
                    ->action(fn () => $this->safely(function () {
                        if (function_exists('opcache_reset')) {
                            opcache_reset();
                        } else {
                            throw new \RuntimeException('OPcache не доступний на сервері');
                        }
                    }, 'OPcache reset', 'Compiled bytecode скинуто')),
                Action::make('clear_filament')
                    ->label('Filament assets')
                    ->icon('heroicon-o-paint-brush')
                    ->action(fn () => $this->safely(
                        fn () => Artisan::call('filament:cache'),
                        'Filament cache оновлено',
                        'Components / assets discovered'
                    )),
                Action::make('clear_sessions')
                    ->label('Sessions')
                    ->icon('heroicon-o-user-group')
                    ->requiresConfirmation()
                    ->modalDescription('Усі юзери будуть логаут.')
                    ->action(fn () => $this->safely(
                        fn () => $this->clearSessions(),
                        'Sessions очищено',
                        'Усіх юзерів вилогінено'
                    )),
            ])
                ->label('По типу cache')
                ->icon('heroicon-o-squares-2x2')
                ->color('warning')
                ->button(),

            // ── PER-DOMAIN tags ────────────────────────────────────────────────
            ActionGroup::make([
                Action::make('tag_products')
                    ->label('Товари')
                    ->icon('heroicon-o-cube')
                    ->action(fn () => $this->flushDomain('products', 'Товари')),
                Action::make('tag_categories')
                    ->label('Категорії')
                    ->icon('heroicon-o-folder')
                    ->action(fn () => $this->flushDomain('categories', 'Категорії')),
                Action::make('tag_brands')
                    ->label('Бренди')
                    ->icon('heroicon-o-bookmark')
                    ->action(fn () => $this->flushDomain('brands', 'Бренди')),
                Action::make('tag_blog')
                    ->label('Блог + статті')
                    ->icon('heroicon-o-document')
                    ->action(fn () => $this->flushDomain('blog', 'Блог')),
                Action::make('tag_cars')
                    ->label('Авто (марки/моделі/двигуни)')
                    ->icon('heroicon-o-truck')
                    ->action(fn () => $this->flushDomain('cars', 'Авто-сумісність')),
                Action::make('tag_settings')
                    ->label('Налаштування магазину')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->action(fn () => $this->flushDomain('settings', 'Налаштування')),
                Action::make('tag_warehouses')
                    ->label('Склади + інвентар')
                    ->icon('heroicon-o-building-storefront')
                    ->action(fn () => $this->flushDomain('warehouses', 'Склади')),
            ])
                ->label('По домену')
                ->icon('heroicon-o-tag')
                ->color('info')
                ->button(),

            // ── OPS actions ────────────────────────────────────────────────────
            ActionGroup::make([
                Action::make('warm_up')
                    ->label('Прогріти кеш')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->action(fn () => $this->safely(
                        fn () => $this->warmCriticalCache(),
                        'Кеш прогрітий',
                        'Запитано критичні сторінки + категорії'
                    )),
                Action::make('octane_reload')
                    ->label('Octane reload')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn () => $this->safely(
                        fn () => Artisan::call('octane:reload'),
                        'Octane workers reload',
                        'Workers перезавантажено (zero-downtime)'
                    )),
                Action::make('optimize_prod')
                    ->label('Оптимізувати для production')
                    ->icon('heroicon-o-rocket-launch')
                    ->requiresConfirmation()
                    ->modalDescription('config:cache + route:cache + view:cache + event:cache. Дає швидкий cold-boot.')
                    ->action(fn () => $this->safely(function () {
                        Artisan::call('config:cache');
                        Artisan::call('route:cache');
                        Artisan::call('view:cache');
                        Artisan::call('event:cache');
                    }, 'Оптимізовано', 'Config + routes + views + events закешовано')),
            ])
                ->label('Операції')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('gray')
                ->button(),
        ];
    }

    /** Flush a single Cache::tags() bucket + ResponseCache (to ensure UI refreshes). */
    private function flushDomain(string $tag, string $label): void
    {
        $this->safely(function () use ($tag) {
            $store = Cache::store();
            if (method_exists($store->getStore(), 'tags')) {
                Cache::tags($tag)->flush();
            }
            ResponseCache::clear();
        }, "{$label} cache очищено", "Tag «{$tag}» + response cache спорожнено");
    }

    private function safely(\Closure $fn, string $title, string $body): void
    {
        try {
            $fn();
            $this->logCacheAction($title, $body);
            Notification::make()->title($title)->body($body)->success()->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Помилка')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function clearSessions(): void
    {
        if (config('session.driver') === 'redis') {
            try {
                Redis::connection()->flushdb();
            } catch (\Throwable $e) { /* keep going */ }
        }
        $sessionPath = storage_path('framework/sessions');
        if (File::exists($sessionPath)) {
            foreach (File::files($sessionPath) as $file) {
                File::delete($file->getPathname());
            }
        }
    }

    private function warmCriticalCache(): void
    {
        \App\Models\Category::whereNull('parent_id')->withCount('products')->get();
        \App\Models\Product::where('is_hit', 1)->limit(20)->get();
        \App\Models\Product::where('is_new', 1)->limit(20)->get();

        // HTTP warm-up for top pages (rebuilds ResponseCache).
        $urls = ['/', '/catalog', '/novynky', '/khity', '/akcii', '/blog'];
        foreach ($urls as $url) {
            try {
                $client = new \GuzzleHttp\Client(['timeout' => 10, 'verify' => false]);
                $client->get(config('app.url').$url);
            } catch (\Throwable $e) { /* network might be unavailable, skip */ }
        }
    }

    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        if (! File::exists($directory)) {
            return 0;
        }
        // Race-safe: view/cache dirs get files created/deleted during scan.
        // stat() throws on deleted files — skip rather than crash the page.
        foreach (File::allFiles($directory) as $file) {
            try {
                $size += $file->getSize();
            } catch (\Throwable) {
                continue;
            }
        }
        return $size;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) $bytes /= 1024;
        return round($bytes, 2).' '.$units[$i];
    }

    private function logCacheAction(string $action, string $details): void
    {
        $logs = session('cache_logs', []);
        array_unshift($logs, [
            'time' => now()->format('H:i:s'),
            'action' => $action,
            'details' => $details,
        ]);
        session(['cache_logs' => array_slice($logs, 0, 30)]);
    }
}
