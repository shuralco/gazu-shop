<?php

namespace App\Support;

use Illuminate\Support\Facades\Event;

/**
 * Thin WordPress-style facade over Laravel's Event system, so modules have
 * a documented extension surface without poking into core code.
 *
 * Two flavours:
 *
 *   Hooks::do('order.paid', $order);
 *     Fire-and-forget action. Modules listen with Event::listen('order.paid', ...).
 *
 *   $price = Hooks::filter('product.price', $price, $product);
 *     Value-transforming filter. Listeners receive the current value plus context
 *     and return the new value. Pipeline pattern.
 *
 * The official hook registry — names, payloads, who listens — lives in
 * docs/MODULE-HOOKS.md. Add new hooks to that doc when you add them in code.
 */
class Hooks
{
    /**
     * Fire a named action. Listeners run in registration order; return values ignored.
     *
     * @param  string         $name  Dotted hook name, e.g. 'order.paid'
     * @param  mixed          $payload  Single payload object or array
     */
    public static function do(string $name, mixed ...$payload): void
    {
        Event::dispatch("hooks.{$name}", $payload);
    }

    /**
     * Run a value through a filter pipeline. Listeners receive the current
     * value as first arg + any context as additional args, and must return
     * the (possibly modified) value.
     *
     * @template T
     *
     * @param  string  $name  Dotted hook name
     * @param  T       $value Initial value
     * @param  mixed   ...$context  Extra read-only context
     * @return T
     */
    public static function filter(string $name, mixed $value, mixed ...$context): mixed
    {
        $results = Event::dispatch("hooks.filter.{$name}", [$value, ...$context]);

        // Walk listener returns in order, threading value through each.
        // Laravel's Event::dispatch returns array of listener returns
        // (or null if no listeners). Pick latest non-null.
        foreach ((array) $results as $result) {
            if ($result !== null) {
                $value = $result;
            }
        }

        return $value;
    }

    /**
     * Register a listener for an action. Sugar over Event::listen so module
     * code reads naturally:
     *
     *   Hooks::on('order.paid', function (Order $order) { ... });
     */
    public static function on(string $name, callable|string $listener): void
    {
        Event::listen("hooks.{$name}", $listener);
    }

    /**
     * Register a filter listener. Listener returns the (possibly modified) value.
     *
     *   Hooks::addFilter('product.price', function (float $price, Product $p) {
     *       return $p->is_on_sale ? $price * 0.9 : $price;
     *   });
     */
    public static function addFilter(string $name, callable|string $listener): void
    {
        Event::listen("hooks.filter.{$name}", $listener);
    }
}
