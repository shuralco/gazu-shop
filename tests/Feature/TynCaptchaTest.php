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

    private function enable(): void
    {
        config([
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

    /** @dataProvider forms */
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

    /** @dataProvider forms */
    public function test_attempted_token_passes_and_calls_siteverify(string $route, array $payload, bool $json): void
    {
        $this->enable();
        Http::fake(['c.tyn.com.ua/*' => Http::response(['ok' => true, 'attempted' => true, 'correct' => false])]);

        $this->assertPassed($this->send($route, $payload + ['tyn_token' => 'tok_abc-123'], $json), $json);
        Http::assertSent(fn ($req) => $req->url() === 'https://c.tyn.com.ua/v1/siteverify'
            && $req['secret'] === 'test-secret' && $req['token'] === 'tok_abc-123');
    }

    /** @dataProvider forms */
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
}
