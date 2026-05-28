<?php

namespace App\Support;

use Illuminate\Support\Facades\Event;

/**
 * Hook & Filter registry — WordPress/OpenCart-style модульна шина.
 *
 * Два класи API:
 *
 *   Actions (fire-and-forget, side effects):
 *     Hooks::on('order.paid', fn($o) => Telegram::notify($o), priority: 10);
 *     Hooks::do('order.paid', $order);
 *
 *   Filters (value-transforming pipeline):
 *     Hooks::addFilter('product.price', fn($p, $product) => $p * 0.9);
 *     $final = Hooks::filter('product.price', 100, $product);
 *
 *   Render-style (Blade output points):
 *     Hooks::on('product.page.before_buy_panel', fn($p) => view('mymod::extra-info', compact('p')));
 *     Blade: @hookAction('product.page.before_buy_panel', $product)
 *
 * Priority: менше число = раніше (WP convention). За замовчанням 10.
 *
 * Інтроспекція:
 *   Hooks::inventory()  → ['event' => listener_count, ...]
 *   Hooks::listenersFor('event')  → метадані хто підписаний
 *
 * Backward-compat: do() / filter() / on() / addFilter() лишилися як були,
 * додатково діспатчиться Laravel Event щоб старі listener'и не зламались.
 *
 * Офіційний реєстр хук-points → docs/MODULE-HOOKS.md.
 */
class Hooks
{
    /**
     * @var array<string, array<int, list<callable>>>  event → priority → listeners
     */
    private static array $actions = [];

    /**
     * @var array<string, array<int, list<callable>>>
     */
    private static array $filters = [];

    /** Метадані для /admin/modules introspection. */
    private static array $registry = [];

    /* ---------------- ACTIONS (side effects) ---------------- */

    /**
     * Register an action listener.
     */
    public static function on(string $name, callable|string $listener, int $priority = 10, ?string $source = null): void
    {
        $cb = self::normalize($listener);
        self::$actions[$name][$priority][] = $cb;
        ksort(self::$actions[$name]);
        self::trackRegistry($name, 'action', $priority, $source);

        // Mirror to Laravel Event for backward-compatibility with code that
        // does Event::listen("hooks.{$name}", ...).
        Event::listen("hooks.{$name}", $cb);
    }

    /**
     * Fire an action. Listeners run by priority, errors logged, return values discarded.
     */
    public static function do(string $name, mixed ...$payload): void
    {
        foreach (self::$actions[$name] ?? [] as $bucket) {
            foreach ($bucket as $listener) {
                try {
                    $listener(...$payload);
                } catch (\Throwable $e) {
                    self::reportError($name, $listener, $e);
                }
            }
        }
        Event::dispatch("hooks.{$name}", $payload);
    }

    /**
     * Render-style action — collect string outputs from each listener and concatenate.
     * Used by Blade `@hookAction('event', $args)` directive to insert
     * output from modules into core templates.
     */
    public static function render(string $name, mixed ...$payload): string
    {
        $out = '';
        foreach (self::$actions[$name] ?? [] as $bucket) {
            foreach ($bucket as $listener) {
                try {
                    $piece = $listener(...$payload);
                    if ($piece !== null && $piece !== false) {
                        $out .= (string) $piece;
                    }
                } catch (\Throwable $e) {
                    self::reportError($name, $listener, $e);
                }
            }
        }
        return $out;
    }

    /* ---------------- FILTERS (value transforms) ---------------- */

    /**
     * Register a filter listener. Listener signature: function ($value, ...$context): mixed
     */
    public static function addFilter(string $name, callable|string $listener, int $priority = 10, ?string $source = null): void
    {
        $cb = self::normalize($listener);
        self::$filters[$name][$priority][] = $cb;
        ksort(self::$filters[$name]);
        self::trackRegistry($name, 'filter', $priority, $source);
        Event::listen("hooks.filter.{$name}", $cb);
    }

    /**
     * Run value through filter pipeline.
     */
    public static function filter(string $name, mixed $value, mixed ...$context): mixed
    {
        foreach (self::$filters[$name] ?? [] as $bucket) {
            foreach ($bucket as $listener) {
                try {
                    $value = $listener($value, ...$context);
                } catch (\Throwable $e) {
                    self::reportError($name, $listener, $e);
                }
            }
        }
        return $value;
    }

    /* ---------------- INTROSPECTION ---------------- */

    /**
     * @return array<string, array{actions: int, filters: int}>
     */
    public static function inventory(): array
    {
        $names = array_unique(array_merge(array_keys(self::$actions), array_keys(self::$filters)));
        sort($names);
        $out = [];
        foreach ($names as $n) {
            $out[$n] = [
                'actions' => array_sum(array_map('count', self::$actions[$n] ?? [])),
                'filters' => array_sum(array_map('count', self::$filters[$n] ?? [])),
            ];
        }
        return $out;
    }

    /**
     * Source/priority metadata per event (only for listeners registered with `source` param).
     *
     * @return array<int, array{type:string, priority:int, source:string}>
     */
    public static function listenersFor(string $name): array
    {
        return self::$registry[$name] ?? [];
    }

    /**
     * @return array<string>  events on which the given source has listeners
     */
    public static function eventsBySource(string $source): array
    {
        $events = [];
        foreach (self::$registry as $event => $list) {
            foreach ($list as $r) {
                if ($r['source'] === $source) {
                    $events[] = $event;
                    break;
                }
            }
        }
        return $events;
    }

    public static function has(string $name): bool
    {
        return ! empty(self::$actions[$name]) || ! empty(self::$filters[$name]);
    }

    public static function clear(): void
    {
        self::$actions = [];
        self::$filters = [];
        self::$registry = [];
    }

    /* ---------------- INTERNAL ---------------- */

    private static function normalize(callable|string $listener): callable
    {
        if (is_string($listener) && str_contains($listener, '@')) {
            [$class, $method] = explode('@', $listener);
            return fn (...$args) => app($class)->{$method}(...$args);
        }
        if (is_string($listener) && class_exists($listener)) {
            return fn (...$args) => app($listener)(...$args);
        }
        return $listener;
    }

    private static function trackRegistry(string $event, string $type, int $priority, ?string $source): void
    {
        if (! $source) return;
        self::$registry[$event][] = compact('type', 'priority', 'source');
    }

    private static function reportError(string $event, callable $listener, \Throwable $e): void
    {
        $name = is_object($listener) ? $listener::class
            : (is_array($listener) ? (is_object($listener[0]) ? $listener[0]::class : $listener[0]).'::'.$listener[1]
            : (is_string($listener) ? $listener : 'closure'));
        \Log::warning("[Hooks] {$event} listener {$name} threw: ".$e->getMessage(), [
            'exception' => $e::class,
            'file' => $e->getFile().':'.$e->getLine(),
        ]);
    }
}
