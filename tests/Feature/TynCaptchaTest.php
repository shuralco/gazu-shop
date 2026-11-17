<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Капча «Тин» на формах: вхід, реєстрація, зворотний дзвінок, «повідомити про наявність», замовлення в 1 клік.
 * Сервіс підроблено через Http::fake: у мережу тести не ходять.
 */
class TynCaptchaTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const MSG_MISSING = 'Увімкніть JavaScript і пройдіть перевірку';
    private const MSG_REJECTED = 'Перевірка застаріла, спробуйте ще раз';

    /** @return array<string, array{0:string,1:array<string,mixed>,2:bool}> [route name, payload, json?] */
    public static function forms(): array
    {
        return [
            'login'    => ['gazu.auth.login', ['email' => 'a@example.com', 'password' => 'secret1'], false],
            'register' => ['gazu.auth.register', ['name' => 'Тест', 'email' => 'b@example.com', 'password' => 'secret12', 'password_confirmation' => 'secret12'], false],
            'callback' => ['gazu.callback.store', ['phone' => '+380671234567', 'source' => 'footer'], true],
            'stock'    => ['gazu.stock.notify', ['product_id' => 999999, 'email' => 'c@example.com'], true],
            'oneclick' => ['gazu.checkout.one-click', ['product_id' => 999999, 'phone' => '+380671234567'], true],
        ];
    }

    /** @return array<string, array{0:string,1:array<string,mixed>,2:bool}> форми без observe */
    public static function blockingForms(): array
    {
        return array_diff_key(self::forms(), ['oneclick' => 1]);
    }

    private function enable(): void
    {
        config([
            'tyn.events' => false, // події перевіряються окремими тестами
            'tyn.enabled' => true,
            'tyn.site_key' => 'test-site-key',
            'tyn.secret' => 'test-secret',
            'tyn.base_url' => 'https://c.tyn.com.ua',
            'tyn.mode' => 'soft',
        ]);
    }

    private function send(string $route, array $payload, bool $json)
    {
        $url = route($route);

        return $json ? $this->postJson($url, $payload) : $this->post($url, $payload);
    }

    private function assertBlocked($response, bool $json, string $message): void
    {
        if ($json) {
            $response->assertStatus(422)
                ->assertJson(['ok' => false, 'message' => $message])
                ->assertJsonValidationErrors('tyn_token');
        } else {
            $response->assertSessionHasErrors(['tyn_token' => $message]);
        }
    }

    private function assertPassed($response, bool $json): void
    {
        if ($json) {
            $response->assertJsonMissingValidationErrors('tyn_token');
        } else {
            $response->assertSessionDoesntHaveErrors('tyn_token');
        }
    }

    /** @dataProvider forms */
    public function test_without_key_request_passes_untouched(string $route, array $payload, bool $json): void
    {
        config(['tyn.site_key' => null, 'tyn.secret' => null]);
        Http::fake();

        $this->assertPassed($this->send($route, $payload, $json), $json);
        Http::assertNothingSent();
    }

    /** @dataProvider forms */
    public function test_disabled_flag_passes_untouched(string $route, array $payload, bool $json): void
    {
        $this->enable();
        config(['tyn.enabled' => false]);
        Http::fake();

        $this->assertPassed($this->send($route, $payload, $json), $json);
        Http::assertNothingSent();
    }

    /** @dataProvider blockingForms */
    public function test_missing_token_is_rejected_in_form_format(string $route, array $payload, bool $json): void
    {
        $this->enable();
        Http::fake();

        $this->assertBlocked($this->send($route, $payload, $json), $json, self::MSG_MISSING);
        Http::assertNothingSent();
    }

    /** @dataProvider forms */
    public function test_unavailable_token_passes_without_call(string $route, array $payload, bool $json): void
    {
        $this->enable();
        Http::fake();

        $this->assertPassed($this->send($route, $payload + ['tyn_token' => 'unavailable'], $json), $json);
        Http::assertNothingSent();
    }

    /** @dataProvider blockingForms */
    public function test_attempted_token_passes_and_calls_siteverify(string $route, array $payload, bool $json): void
    {
        $this->enable();
        Http::fake(['c.tyn.com.ua/*' => Http::response(['ok' => true, 'attempted' => true, 'correct' => false])]);

        $this->assertPassed($this->send($route, $payload + ['tyn_token' => 'tok_abc-123'], $json), $json);
        Http::assertSent(fn ($req) => $req->url() === 'https://c.tyn.com.ua/v1/siteverify'
            && $req['secret'] === 'test-secret' && $req['token'] === 'tok_abc-123');
    }

    /** @dataProvider blockingForms */
    public function test_used_token_is_rejected(string $route, array $payload, bool $json): void
    {
        $this->enable();
        Http::fake(['c.tyn.com.ua/*' => Http::response(['ok' => false])]);

        $this->assertBlocked($this->send($route, $payload + ['tyn_token' => 'tok_used'], $json), $json, self::MSG_REJECTED);
    }

    /** @dataProvider forms */
    public function test_service_timeout_passes(string $route, array $payload, bool $json): void
    {
        $this->enable();
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->assertPassed($this->send($route, $payload + ['tyn_token' => 'tok_abc'], $json), $json);
    }

    /** @dataProvider forms */
    public function test_service_5xx_passes(string $route, array $payload, bool $json): void
    {
        $this->enable();
        Http::fake(['c.tyn.com.ua/*' => Http::response('boom', 503)]);

        $this->assertPassed($this->send($route, $payload + ['tyn_token' => 'tok_abc'], $json), $json);
    }

    public function test_no_attempt_is_rejected_in_soft_mode(): void
    {
        $this->enable();
        Http::fake(['c.tyn.com.ua/*' => Http::response(['ok' => true, 'attempted' => false])]);

        $this->assertBlocked($this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'tok_abc']), true, self::MSG_MISSING);
    }

    public function test_callback_route_is_throttled(): void
    {
        $route = app('router')->getRoutes()->getByName('gazu.callback.store');
        $this->assertContains('throttle:10,1', $route->gatherMiddleware());
        $this->assertContains('tyn:callback', $route->gatherMiddleware());
    }

    public function test_widget_and_script_render_only_with_key(): void
    {
        $this->withoutVite();
        config(['tyn.site_key' => null, 'tyn.secret' => null]);
        $this->get('/login')->assertOk()->assertDontSee('data-tyn data-key', false)->assertDontSee('/w/tyn.js', false);

        $this->enable();
        $html = $this->get('/login')->assertOk()
            ->assertSee('<div data-tyn data-key="test-site-key" data-form="login"></div>', false)
            ->assertSee('<div data-tyn data-key="test-site-key" data-form="register"></div>', false)
            ->assertSee('<script async src="https://c.tyn.com.ua/w/tyn.js"></script>', false)
            ->getContent();
        $this->assertSame(1, substr_count($html, '/w/tyn.js'));
        $this->assertStringNotContainsString('test-secret', $html);
    }

    public function test_login_error_is_shown_only_in_its_form(): void
    {
        $this->withoutVite();
        $this->enable();
        $this->post(route('gazu.auth.login'), ['email' => 'a@example.com', 'password' => 'secret1']);
        $page = $this->get('/login')->assertOk()->getContent();
        $this->assertStringContainsString(self::MSG_MISSING, $page);
        $this->assertSame(1, substr_count($page, self::MSG_MISSING));
    }

    // --- Режим observe (замовлення в 1 клік) ---

    public function test_one_click_route_uses_observe_mode(): void
    {
        $mw = app('router')->getRoutes()->getByName('gazu.checkout.one-click')->gatherMiddleware();
        $this->assertContains('tyn:other,observe', $mw);
        // Решта форм без observe.
        foreach (['gazu.callback.store', 'gazu.stock.notify', 'gazu.auth.login', 'gazu.auth.register'] as $name) {
            foreach (app('router')->getRoutes()->getByName($name)->gatherMiddleware() as $m) {
                $this->assertStringNotContainsString('observe', (string) $m);
            }
        }
    }

    public function test_observe_never_rejects(): void
    {
        $this->enable();
        $payload = ['product_id' => 999999, 'phone' => '+380671234567'];
        $cases = [
            'no token' => [[], Http::response(['ok' => true, 'attempted' => true])],
            'ok false' => [['tyn_token' => 'tok_used'], Http::response(['ok' => false])],
            'not attempted' => [['tyn_token' => 'tok_x'], Http::response(['ok' => true, 'attempted' => false])],
            'garbage token' => [['tyn_token' => 'bad token!'], Http::response('x', 500)],
            'unavailable' => [['tyn_token' => 'unavailable'], Http::response('x', 500)],
        ];
        foreach ($cases as $label => [$extra, $fake]) {
            Http::fake(['c.tyn.com.ua/*' => $fake]);
            $r = $this->postJson(route('gazu.checkout.one-click'), $payload + $extra);
            $r->assertJsonMissingValidationErrors('tyn_token');
            $this->assertStringNotContainsString('JavaScript', $r->getContent(), $label);
            Http::assertNotSent(fn ($req) => str_contains($req->url(), '/v1/siteverify'));
        }
    }

    public function test_observe_passes_through_to_the_controller(): void
    {
        $this->enable();
        Http::fake();
        // Без токена: контролер усе одно виконується (тут відсутній товар дає свою помилку валідації).
        $this->postJson(route('gazu.checkout.one-click'), ['product_id' => 999999, 'phone' => '+380671234567'])
            ->assertStatus(422)->assertJsonValidationErrors('product_id');
    }

    // --- Події /v1/event ---

    private function events(): array
    {
        $found = [];
        foreach (Http::recorded() as [$req]) {
            if ($req->url() === 'https://c.tyn.com.ua/v1/event') {
                $found[] = $req->data();
            }
        }

        return $found;
    }

    public function test_event_form_submit_has_right_fields_and_no_form_content(): void
    {
        $this->enable();
        config(['tyn.events' => true]);
        Http::fake(['c.tyn.com.ua/v1/siteverify' => Http::response(['ok' => true, 'attempted' => true]), 'c.tyn.com.ua/v1/event' => Http::response(['ok' => true])]);

        $this->postJson(route('gazu.callback.store'), ['name' => 'Іван', 'phone' => '+380671234567', 'source' => 'footer', 'tyn_token' => 'tok_abc-1']);

        $ev = $this->events();
        $this->assertCount(1, $ev);
        $this->assertEqualsCanonicalizing(['secret', 'event', 'form', 'fill_s', 'honeypot', 'token'], array_keys($ev[0]));
        $this->assertSame('form_submit', $ev[0]['event']);
        $this->assertSame('callback', $ev[0]['form']);
        $this->assertSame('tok_abc-1', $ev[0]['token']);
        $this->assertSame('test-secret', $ev[0]['secret']);
        $this->assertStringNotContainsString('380671234567', json_encode($ev[0]));
        $this->assertStringNotContainsString('Іван', json_encode($ev[0]));
    }

    public function test_event_form_no_token_when_token_missing_or_unavailable(): void
    {
        $this->enable();
        config(['tyn.events' => true]);
        Http::fake(['c.tyn.com.ua/*' => Http::response(['ok' => true])]);

        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567']);
        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'unavailable']);

        $ev = $this->events();
        $this->assertCount(2, $ev);
        foreach ($ev as $e) {
            $this->assertSame('form_no_token', $e['event']);
            $this->assertArrayNotHasKey('token', $e);
            $this->assertSame('callback', $e['form']);
        }
    }

    public function test_event_is_sent_in_observe_mode_too(): void
    {
        $this->enable();
        config(['tyn.events' => true]);
        Http::fake(['c.tyn.com.ua/*' => Http::response(['ok' => true])]);

        $this->postJson(route('gazu.checkout.one-click'), ['product_id' => 999999, 'phone' => '+380671234567']);

        $ev = $this->events();
        $this->assertCount(1, $ev);
        $this->assertSame('form_no_token', $ev[0]['event']);
        $this->assertSame('other', $ev[0]['form']);
    }

    public function test_events_switch_off(): void
    {
        $this->enable();
        config(['tyn.events' => false]);
        Http::fake(['c.tyn.com.ua/*' => Http::response(['ok' => true, 'attempted' => true])]);

        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'tok_abc']);
        $this->assertSame([], $this->events());
    }

    public function test_no_events_without_key(): void
    {
        config(['tyn.site_key' => null, 'tyn.secret' => null]);
        Http::fake();
        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'tok_abc']);
        Http::assertNothingSent();
    }

    public function test_event_failure_does_not_break_the_response(): void
    {
        $this->enable();
        config(['tyn.events' => true]);
        $calls = 0;
        Http::fake([
            'c.tyn.com.ua/v1/siteverify' => Http::response(['ok' => true, 'attempted' => true]),
            'c.tyn.com.ua/v1/event' => function () use (&$calls) {
                $calls++;
                throw new ConnectionException('timeout');
            },
        ]);

        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'tok_abc'])
            ->assertOk()->assertJson(['ok' => true]);
        $this->assertSame(1, $calls);
    }

    public function test_event_http_500_does_not_break_the_response(): void
    {
        $this->enable();
        config(['tyn.events' => true]);
        Http::fake(['c.tyn.com.ua/v1/event' => Http::response('boom', 500), 'c.tyn.com.ua/*' => Http::response(['ok' => true, 'attempted' => true])]);

        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'tok_abc'])
            ->assertOk()->assertJson(['ok' => true]);
    }
}
