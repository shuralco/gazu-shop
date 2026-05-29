<?php

namespace App\Http\Middleware;

use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use Closure;
use Illuminate\Http\Request;

/**
 * Runtime-guard для адмінки: якщо поточний Filament-роут належить сторінці/
 * ресурсу ВИМКНЕНОГО модуля — віддаємо 404, незалежно від стану кешу
 * компонентів (bootstrap/cache/filament).
 *
 * Навіщо: реєстрація сторінок модуля gated по enabled() лише на момент
 * побудови панелі й кешується у admin.php. Якщо модуль вимкнули через UI, а
 * кеш не перебудувався (або opcache віддає стару версію) — сторінка лишалась
 * доступною. Цей middleware перевіряє стан модуля НА КОЖЕН запит → stale-кеш
 * більше не «протікає» вимкненими модулями.
 */
class EnsureModuleEnabled
{
    /** Лінива мапа route-фрагмент → ім'я модуля (будується раз на процес). */
    private static ?array $map = null;

    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()?->getName();
        if (is_string($routeName) && str_contains($routeName, 'filament.')) {
            $module = $this->moduleForRoute($routeName);
            if ($module !== null && ! ModuleManager::for($module)->enabled()) {
                abort(404);
            }
        }

        return $next($request);
    }

    private function moduleForRoute(string $routeName): ?string
    {
        foreach (self::map() as $fragment => $module) {
            if (str_contains($routeName, $fragment)) {
                return $module;
            }
        }

        return null;
    }

    /**
     * @return array<string,string> route-фрагмент => модуль
     *   '.pages.batch-editor' => 'batch_editor'
     *   '.resources.payments.' => 'payments'
     */
    private static function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        $map = [];
        foreach (ModuleDiscovery::manifests() as $module => $manifest) {
            foreach (($manifest['filament_pages'] ?? []) as $cls) {
                if ($slug = self::slug($cls)) {
                    $map['.pages.'.$slug] = $module;
                }
            }
            foreach (($manifest['filament_resources'] ?? []) as $cls) {
                if ($slug = self::slug($cls)) {
                    $map['.resources.'.$slug.'.'] = $module;
                }
            }
        }

        return self::$map = $map;
    }

    private static function slug(mixed $cls): ?string
    {
        if (! is_string($cls) || ! class_exists($cls) || ! method_exists($cls, 'getSlug')) {
            return null;
        }
        try {
            return (string) $cls::getSlug();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
