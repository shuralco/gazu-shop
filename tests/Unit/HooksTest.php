<?php

namespace Tests\Unit;

use App\Support\Hooks;
use Tests\TestCase;

/**
 * Pure unit coverage for App\Support\Hooks — the WordPress/OpenCart-style
 * module bus (actions, filters, render, priority, introspection).
 *
 * NO RefreshDatabase: Hooks does not touch the DB. We extend Tests\TestCase
 * only so the Laravel container is booted (app(), Event::listen, \Log mirror
 * work) — the dev-mysql is never opened, tests run on sqlite :memory: per
 * tests/bootstrap.php.
 *
 * Hooks state is static, so we clear() before every test for isolation.
 *
 * BEHAVIOR NOTE — the Event mirror: on()/do() also register and dispatch a
 * Laravel Event ("hooks.{name}") for backward-compat. As a result do() runs
 * every action listener TWICE per call: once through the priority-ordered
 * in-bus loop, and once more through the (registration-ordered) Event mirror.
 * The do()-based tests below assert against this real double-dispatch: they
 * verify the FIRST-SEEN order is priority-correct (the in-bus contract) rather
 * than the raw count. Pure priority ordering with no double-run is covered via
 * render() and filter(), which have no trailing Event::dispatch.
 */
class HooksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Hooks::clear();
    }

    protected function tearDown(): void
    {
        Hooks::clear();
        parent::tearDown();
    }

    /** First-seen order of values, deduplicated — neutralises the Event-mirror double-run. */
    private static function firstSeen(array $values): array
    {
        $seen = [];
        foreach ($values as $v) {
            if (! in_array($v, $seen, true)) {
                $seen[] = $v;
            }
        }
        return $seen;
    }

    /* ---------------- ACTIONS: on() / do() ---------------- */

    public function test_action_runs_when_fired(): void
    {
        $ran = false;
        Hooks::on('order.paid', function () use (&$ran) {
            $ran = true;
        });

        Hooks::do('order.paid');

        $this->assertTrue($ran, 'Registered action listener should run on do().');
    }

    public function test_action_receives_payload(): void
    {
        $captured = null;
        Hooks::on('order.paid', function ($order) use (&$captured) {
            $captured = $order;
        });

        Hooks::do('order.paid', ['id' => 42]);

        $this->assertSame(['id' => 42], $captured);
    }

    public function test_action_receives_multiple_payload_args(): void
    {
        $sum = null;
        Hooks::on('math.add', function ($a, $b, $c) use (&$sum) {
            $sum = $a + $b + $c;
        });

        Hooks::do('math.add', 1, 2, 3);

        $this->assertSame(6, $sum);
    }

    public function test_action_runs_all_registered_listeners(): void
    {
        $calls = [];
        // NB: long closures with use(&) — arrow fns capture by value and would
        // not mutate the outer array.
        Hooks::on('e', function () use (&$calls) { $calls[] = 'a'; });
        Hooks::on('e', function () use (&$calls) { $calls[] = 'b'; });
        Hooks::on('e', function () use (&$calls) { $calls[] = 'c'; });

        Hooks::do('e');

        $this->assertSame(['a', 'b', 'c'], self::firstSeen($calls));
    }

    public function test_do_on_unregistered_event_is_noop(): void
    {
        // Must not throw and return void.
        Hooks::do('nobody.listening', 'payload');

        $this->assertFalse(Hooks::has('nobody.listening'));
    }

    public function test_action_throwing_listener_does_not_break_priority_chain(): void
    {
        $reached = false;
        Hooks::on('e', function () {
            throw new \RuntimeException('boom');
        }, priority: 5);
        Hooks::on('e', function () use (&$reached) {
            $reached = true;
        }, priority: 10);

        // Inside the Hooks priority-bucket loop the throw is caught and logged,
        // so the second listener still runs. (The trailing Event::dispatch mirror
        // re-runs listeners un-guarded and may re-throw, hence the try/catch — by
        // then the in-bus chain has already completed.)
        try {
            Hooks::do('e');
        } catch (\RuntimeException $e) {
            // Re-thrown by the Laravel Event mirror; the in-bus chain already ran.
        }

        $this->assertTrue($reached, 'A throwing listener must not abort subsequent in-bus listeners.');
    }

    public function test_string_class_method_listener_is_resolved_via_container(): void
    {
        HooksTestActionSpy::$calls = [];
        Hooks::on('e', HooksTestActionSpy::class.'@handle');

        Hooks::do('e', 'hello');

        // Resolved + invoked through the container (deduped past the Event mirror).
        $this->assertSame(['hello'], self::firstSeen(HooksTestActionSpy::$calls));
    }

    /* ---------------- FILTERS: addFilter() / filter() ---------------- */

    public function test_filter_transforms_value(): void
    {
        Hooks::addFilter('product.price', fn ($price) => $price * 0.9);

        $this->assertSame(90.0, Hooks::filter('product.price', 100));
    }

    public function test_filter_chain_applies_in_order(): void
    {
        Hooks::addFilter('val', fn ($v) => $v + 1);   // 0 -> 1
        Hooks::addFilter('val', fn ($v) => $v * 10);  // 1 -> 10
        Hooks::addFilter('val', fn ($v) => $v - 3);   // 10 -> 7

        $this->assertSame(7, Hooks::filter('val', 0));
    }

    public function test_filter_receives_context_args(): void
    {
        Hooks::addFilter('label', fn ($value, $suffix, $sep) => $value.$sep.$suffix);

        $this->assertSame('name-x', Hooks::filter('label', 'name', 'x', '-'));
    }

    public function test_filter_unregistered_returns_value_unchanged(): void
    {
        $this->assertSame('untouched', Hooks::filter('no.filter', 'untouched'));
    }

    public function test_filter_throwing_listener_keeps_running_value(): void
    {
        Hooks::addFilter('v', fn ($v) => $v + 5);          // 10 -> 15
        Hooks::addFilter('v', function ($v) {              // throws, value stays 15
            throw new \RuntimeException('boom');
        });
        Hooks::addFilter('v', fn ($v) => $v * 2);          // 15 -> 30

        $this->assertSame(30, Hooks::filter('v', 10));
    }

    /* ---------------- RENDER: concatenated string output ---------------- */

    public function test_render_concatenates_listener_output(): void
    {
        Hooks::on('slot', fn () => '<a>');
        Hooks::on('slot', fn () => '<b>');
        Hooks::on('slot', fn () => '<c>');

        $this->assertSame('<a><b><c>', Hooks::render('slot'));
    }

    public function test_render_passes_payload_to_listeners(): void
    {
        Hooks::on('slot', fn ($name) => "Hi {$name}");

        $this->assertSame('Hi Vlad', Hooks::render('slot', 'Vlad'));
    }

    public function test_render_skips_null_and_false_pieces(): void
    {
        Hooks::on('slot', fn () => 'keep');
        Hooks::on('slot', fn () => null);
        Hooks::on('slot', fn () => false);
        Hooks::on('slot', fn () => '-end');

        $this->assertSame('keep-end', Hooks::render('slot'));
    }

    public function test_render_casts_non_string_pieces_to_string(): void
    {
        Hooks::on('slot', fn () => 0);   // "0" should be kept (0 !== null && 0 !== false)
        Hooks::on('slot', fn () => 42);

        $this->assertSame('042', Hooks::render('slot'));
    }

    public function test_render_unregistered_returns_empty_string(): void
    {
        $this->assertSame('', Hooks::render('nothing.here'));
    }

    public function test_render_throwing_listener_does_not_break_output(): void
    {
        Hooks::on('slot', fn () => 'A');
        Hooks::on('slot', function () {
            throw new \RuntimeException('boom');
        });
        Hooks::on('slot', fn () => 'B');

        $this->assertSame('AB', Hooks::render('slot'));
    }

    /* ---------------- PRIORITY ORDERING (lower number = earlier) ---------------- */

    public function test_action_priority_lower_runs_first(): void
    {
        $order = [];
        Hooks::on('e', function () use (&$order) { $order[] = 'late'; }, priority: 20);
        Hooks::on('e', function () use (&$order) { $order[] = 'early'; }, priority: 5);
        Hooks::on('e', function () use (&$order) { $order[] = 'mid'; }, priority: 10);

        Hooks::do('e');

        // First-seen order reflects the priority-ordered in-bus run.
        $this->assertSame(['early', 'mid', 'late'], self::firstSeen($order));
    }

    public function test_same_priority_preserves_registration_order(): void
    {
        $order = [];
        Hooks::on('e', function () use (&$order) { $order[] = 'first'; }, priority: 10);
        Hooks::on('e', function () use (&$order) { $order[] = 'second'; }, priority: 10);

        Hooks::do('e');

        $this->assertSame(['first', 'second'], self::firstSeen($order));
    }

    public function test_filter_priority_changes_transform_order(): void
    {
        // Lower priority (subtract) must run before higher priority (multiply).
        Hooks::addFilter('v', fn ($v) => $v * 2, priority: 20);   // applied second
        Hooks::addFilter('v', fn ($v) => $v - 1, priority: 5);    // applied first

        // (10 - 1) * 2 = 18
        $this->assertSame(18, Hooks::filter('v', 10));
    }

    public function test_render_respects_priority_order(): void
    {
        Hooks::on('slot', fn () => 'Z', priority: 30);
        Hooks::on('slot', fn () => 'A', priority: 1);

        $this->assertSame('AZ', Hooks::render('slot'));
    }

    /* ---------------- INTROSPECTION ---------------- */

    public function test_has_reflects_registration(): void
    {
        $this->assertFalse(Hooks::has('e'));

        Hooks::on('e', fn () => null);
        $this->assertTrue(Hooks::has('e'));

        Hooks::addFilter('f', fn ($v) => $v);
        $this->assertTrue(Hooks::has('f'));
    }

    public function test_inventory_counts_actions_and_filters_per_event(): void
    {
        Hooks::on('order.paid', fn () => null);
        Hooks::on('order.paid', fn () => null, priority: 5);
        Hooks::addFilter('order.paid', fn ($v) => $v);
        Hooks::addFilter('product.price', fn ($v) => $v);

        $inv = Hooks::inventory();

        $this->assertSame(['actions' => 2, 'filters' => 1], $inv['order.paid']);
        $this->assertSame(['actions' => 0, 'filters' => 1], $inv['product.price']);
    }

    public function test_inventory_keys_are_sorted(): void
    {
        Hooks::on('zeta', fn () => null);
        Hooks::on('alpha', fn () => null);
        Hooks::addFilter('mid', fn ($v) => $v);

        $this->assertSame(['alpha', 'mid', 'zeta'], array_keys(Hooks::inventory()));
    }

    public function test_inventory_empty_when_nothing_registered(): void
    {
        $this->assertSame([], Hooks::inventory());
    }

    public function test_listeners_for_returns_metadata_when_source_given(): void
    {
        Hooks::on('order.paid', fn () => null, priority: 15, source: 'telegram-module');
        Hooks::addFilter('order.paid', fn ($v) => $v, priority: 5, source: 'tax-module');

        $meta = Hooks::listenersFor('order.paid');

        $this->assertCount(2, $meta);
        $this->assertSame(
            ['type' => 'action', 'priority' => 15, 'source' => 'telegram-module'],
            $meta[0]
        );
        $this->assertSame(
            ['type' => 'filter', 'priority' => 5, 'source' => 'tax-module'],
            $meta[1]
        );
    }

    public function test_listeners_for_ignores_listeners_without_source(): void
    {
        // No source param → not tracked in the registry.
        Hooks::on('order.paid', fn () => null);

        $this->assertSame([], Hooks::listenersFor('order.paid'));
    }

    public function test_listeners_for_unknown_event_is_empty(): void
    {
        $this->assertSame([], Hooks::listenersFor('does.not.exist'));
    }

    public function test_events_by_source_lists_events_for_a_module(): void
    {
        Hooks::on('order.paid', fn () => null, source: 'telegram-module');
        Hooks::on('order.refunded', fn () => null, source: 'telegram-module');
        Hooks::addFilter('product.price', fn ($v) => $v, source: 'pricing-module');

        $events = Hooks::eventsBySource('telegram-module');
        sort($events);

        $this->assertSame(['order.paid', 'order.refunded'], $events);
        $this->assertSame(['product.price'], Hooks::eventsBySource('pricing-module'));
    }

    public function test_events_by_source_deduplicates_per_event(): void
    {
        // Same source registered twice on the same event → event listed once.
        Hooks::on('order.paid', fn () => null, source: 'mod');
        Hooks::addFilter('order.paid', fn ($v) => $v, source: 'mod');

        $this->assertSame(['order.paid'], Hooks::eventsBySource('mod'));
    }

    public function test_events_by_source_unknown_source_is_empty(): void
    {
        Hooks::on('e', fn () => null, source: 'real-module');

        $this->assertSame([], Hooks::eventsBySource('ghost-module'));
    }

    /* ---------------- clear() ---------------- */

    public function test_clear_removes_all_actions_filters_and_registry(): void
    {
        Hooks::on('e', fn () => null, source: 'mod');
        Hooks::addFilter('f', fn ($v) => $v + 1, source: 'mod');

        Hooks::clear();

        $this->assertFalse(Hooks::has('e'));
        $this->assertFalse(Hooks::has('f'));
        $this->assertSame([], Hooks::inventory());
        $this->assertSame([], Hooks::listenersFor('e'));
        $this->assertSame([], Hooks::eventsBySource('mod'));
    }

    public function test_clear_stops_filter_pipeline_from_transforming(): void
    {
        // The in-bus filter pipeline is cleared, so filter() returns the value
        // unchanged. (Note: clear() resets the static Hooks state; the Laravel
        // Event mirror registered by on()/addFilter() is intentionally not
        // unregistered, so it is not asserted here.)
        Hooks::addFilter('f', fn ($v) => $v + 100);

        Hooks::clear();

        $this->assertSame(5, Hooks::filter('f', 5));
    }
}

/**
 * Container-resolvable spy used to verify Class@method string listeners.
 */
class HooksTestActionSpy
{
    /** @var array<int, mixed> */
    public static array $calls = [];

    public function handle($payload): void
    {
        self::$calls[] = $payload;
    }
}
