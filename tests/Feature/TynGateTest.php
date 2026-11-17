<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Заслін Тин на весь сайт: режими, винятки, позначка проходу, сторінка проходу, порядок у групі web. */
class TynGateTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'tyn.enabled' => true, 'tyn.site_key' => 'test-site-key', 'tyn.secret' => 'test-secret',
            'tyn.base_url' => 'https://tyn.test', 'tyn.events' => false, 'tyn.traffic' => false,
            'tyn.gate' => 'all', 'tyn.gate_skip' => [], 'tyn.gate_bots' => [], 'tyn.gate_bind' => false,
        ]);
        $this->verify = Http::response(['ok' => true, 'attempted' => true]);
        Http::fake(fn ($req) => $this->verify);
    }

    private $verify;

    private function srv(array $over = []): array
    {
        return array_merge([
            'HTTP_USER_AGENT' => self::CHROME,
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'uk-UA,uk;q=0.9',
        ], $over);
    }

    private function visit(string $uri = '/login', array $server = [], array $cookies = [], string $method = 'GET')
    {
        return $this->call($method, $uri, [], $cookies, [], $this->srv($server));
    }

    private function mark(int $ttl = 3600, string $secret = 'test-secret'): string
    {
        require_once app_path('Support/Tyn/tyn-lib.php');

        return \tyn_gate_mark_make($secret, $ttl);
    }

    public function test_off_by_default_and_without_key_changes_nothing(): void
    {
        config(['tyn.gate' => 'off']);
        $this->visit()->assertOk()->assertDontSee('id="tyn-gate"', false);
        config(['tyn.gate' => 'all', 'tyn.secret' => null]);
        $this->visit()->assertOk()->assertDontSee('id="tyn-gate"', false);
        config(['tyn.secret' => 's', 'tyn.site_key' => null]);
        $this->visit()->assertOk()->assertDontSee('id="tyn-gate"', false);
        config(['tyn.site_key' => 'k', 'tyn.enabled' => false]);
        $this->visit()->assertOk()->assertDontSee('id="tyn-gate"', false);
    }

    public function test_all_mode_shows_gate_page_no_store_without_secret(): void
    {
        $r = $this->visit('/login?x=1');
        $r->assertOk()->assertSee('id="tyn-gate"', false)->assertSee('data-tyn data-key="test-site-key"', false)
            ->assertSee('action="/tyn/pass"', false)->assertSee('name="return" value="/login?x=1"', false)
            ->assertDontSee('test-secret', false);
        $this->assertStringContainsString('no-store', (string) $r->headers->get('Cache-Control'));
        $this->assertStringContainsString('noindex', (string) $r->headers->get('X-Robots-Tag'));
    }

    public function test_gate_is_before_response_cache_in_web_group(): void
    {
        $web = $this->app['router']->getMiddlewareGroups()['web'];
        $gate = array_search(\App\Http\Middleware\TynGate::class, $web, true);
        $cache = array_search(\Spatie\ResponseCache\Middlewares\CacheResponse::class, $web, true);
        $this->assertNotFalse($gate);
        $this->assertNotFalse($cache);
        $this->assertLessThan($cache, $gate);
    }

    public static function exempt(): array
    {
        return [
            'up' => ['/up'], 'csrf' => ['/csrf-token'], 'np' => ['/api/np/cities'], 'ukrposhta' => ['/api/up/cities'],
            'webhook' => ['/webhooks/liqpay'], 'sitemap' => ['/sitemap.xml'], 'feed' => ['/feed/google.xml'],
            'admin' => ['/admin/login'], 'livewire' => ['/livewire/update'], 'pass' => ['/tyn/pass'],
        ];
    }

    /** @dataProvider exempt */
    public function test_exempt_paths_never_get_gate(string $uri): void
    {
        $r = $this->visit($uri);
        $this->assertStringNotContainsString('id="tyn-gate"', (string) $r->getContent());
    }

    public function test_extra_skip_pattern_from_config(): void
    {
        config(['tyn.gate_skip' => ['login*']]);
        $this->visit('/login')->assertOk()->assertDontSee('id="tyn-gate"', false);
    }

    public function test_non_get_ajax_non_document_and_bots_are_not_gated(): void
    {
        $this->assertStringNotContainsString('id="tyn-gate"', (string) $this->visit('/login', [], [], 'POST')->getContent());
        $this->visit('/login', ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])->assertDontSee('id="tyn-gate"', false);
        $this->visit('/login', ['HTTP_SEC_FETCH_DEST' => 'empty'])->assertDontSee('id="tyn-gate"', false);
        $this->visit('/login', ['HTTP_ACCEPT' => 'application/json'])->assertDontSee('id="tyn-gate"', false);
        $this->visit('/login', ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->assertOk()->assertDontSee('id="tyn-gate"', false);
        config(['tyn.gate_bots' => ['mycustombot']]);
        $this->visit('/login', ['HTTP_USER_AGENT' => 'MyCustomBot/1'])->assertDontSee('id="tyn-gate"', false);
    }

    public function test_logged_in_user_is_not_gated(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->call('GET', '/account', [], [], [], $this->srv())->assertDontSee('id="tyn-gate"', false);
    }

    public function test_valid_mark_passes_forged_and_expired_do_not(): void
    {
        $this->visit('/login', [], ['tyn_pass' => $this->mark()])->assertOk()->assertDontSee('id="tyn-gate"', false);
        $this->visit('/login', [], ['tyn_pass' => $this->mark(3600, 'other-secret')])->assertSee('id="tyn-gate"', false);
        $this->visit('/login', [], ['tyn_pass' => '1.9999999999.0.' . str_repeat('a', 32)])->assertSee('id="tyn-gate"', false);
        $this->visit('/login', [], ['tyn_pass' => '1.1.0.' . str_repeat('a', 32)])->assertSee('id="tyn-gate"', false);
        $this->visit('/login', [], ['tyn_pass' => 'garbage'])->assertSee('id="tyn-gate"', false);
    }

    public function test_suspicious_mode_gates_only_non_browsers(): void
    {
        config(['tyn.gate' => 'suspicious']);
        $this->visit()->assertOk()->assertDontSee('id="tyn-gate"', false);
        $this->visit('/login', ['HTTP_USER_AGENT' => 'curl/8.4.0'])->assertSee('id="tyn-gate"', false);
        $this->visit('/login', ['HTTP_USER_AGENT' => 'python-requests/2.31'])->assertSee('id="tyn-gate"', false);
        $this->visit('/login', ['HTTP_ACCEPT_LANGUAGE' => ''])->assertSee('id="tyn-gate"', false);
        $this->visit('/login', ['HTTP_USER_AGENT' => 'curl/8.4.0'], ['tyn_pass' => $this->mark()])->assertDontSee('id="tyn-gate"', false);
    }

    private function observeLog(): string
    {
        return storage_path('app/tyn') . '/gate-' . gmdate('Ymd') . '.log';
    }

    public function test_observe_never_stops_anyone_and_leaks_nothing_to_the_client(): void
    {
        config(['tyn.gate' => 'observe']);
        $file = $this->observeLog();
        @unlink($file);

        $r = $this->visit('/login?secretq=1', ['HTTP_USER_AGENT' => 'curl/8.4.0', 'REMOTE_ADDR' => '203.0.113.55']);
        $r->assertOk()->assertDontSee('id="tyn-gate"', false);
        $this->assertFalse($r->headers->has('X-Tyn-Gate'));
        $this->visit('/login', ['HTTP_USER_AGENT' => 'python-requests/2.31']);
        $this->visit('/login')->assertOk();
        $this->visit('/login', ['HTTP_USER_AGENT' => 'curl/8.4.0'], ['tyn_pass' => $this->mark()]); // з позначкою не рахується

        $lines = array_values(array_filter(explode("\n", (string) file_get_contents($file))));
        $this->assertCount(2, $lines);
        foreach ($lines as $line) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z suspicious:ua_script$/', $line);
        }
        $raw = (string) file_get_contents($file);
        foreach (['203.0.113.55', 'secretq', '/login', 'curl', 'python'] as $leak) {
            $this->assertStringNotContainsString($leak, $raw);
        }
        @unlink($file);
    }

    public function test_observe_log_is_size_capped(): void
    {
        config(['tyn.gate' => 'observe']);
        $file = $this->observeLog();
        @mkdir(dirname($file), 0700, true);
        file_put_contents($file, str_repeat('x', \App\Support\Tyn\TynGateSupport::OBSERVE_LOG_MAX));

        $this->visit('/login', ['HTTP_USER_AGENT' => 'curl/8.4.0'])->assertOk();
        $this->assertSame(\App\Support\Tyn\TynGateSupport::OBSERVE_LOG_MAX, filesize($file));
        @unlink($file);
    }

    public function test_observe_logs_a_warning_on_first_hit_of_the_day(): void
    {
        config(['tyn.gate' => 'observe']);
        @unlink($this->observeLog());
        \Illuminate\Support\Facades\Log::spy();

        $this->visit('/login', ['HTTP_USER_AGENT' => 'curl/8.4.0']);
        $this->visit('/login', ['HTTP_USER_AGENT' => 'curl/8.4.0']);

        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->withArgs(fn ($m) => str_contains($m, 'tyn gate: would show'))->once();
        @unlink($this->observeLog());
    }

    public function test_gate_page_has_no_livewire_scripts(): void
    {
        // Octane між запитами сам викликає Livewire::flushState (PrepareLivewireForNextOperation); у тестах робимо те саме.
        \Livewire\Livewire::flushState();
        $this->visit('/login')->assertSee('id="tyn-gate"', false)->assertDontSee('livewire', false);
    }

    public function test_signed_warm_requests_are_never_gated_or_counted(): void
    {
        $warm = ['HTTP_USER_AGENT' => 'gazu-warm/1.0', 'HTTP_X_GAZU_WARM' => \App\Support\Tyn\TynGateSupport::warmToken()];
        config(['tyn.gate' => 'all']);
        $this->visit('/login', $warm)->assertOk()->assertDontSee('id="tyn-gate"', false);

        config(['tyn.gate' => 'observe']);
        @unlink($this->observeLog());
        $this->visit('/login', $warm)->assertOk();
        $this->assertFileDoesNotExist($this->observeLog());

        // підробний або чужий підпис не діє
        config(['tyn.gate' => 'all']);
        $this->visit('/login', ['HTTP_X_GAZU_WARM' => 'forged'])->assertSee('id="tyn-gate"', false);
        $this->visit('/login', ['HTTP_X_GAZU_WARM' => '1'])->assertSee('id="tyn-gate"', false);
    }

    public function test_spoofed_private_forwarded_address_does_not_bypass_the_gate(): void
    {
        $this->visit('/login', ['REMOTE_ADDR' => '203.0.113.9', 'HTTP_X_FORWARDED_FOR' => '10.0.0.1'])->assertSee('id="tyn-gate"', false);
    }

    public static function allowedAgents(): array
    {
        return [
            'googlebot' => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
            'adsbot' => ['AdsBot-Google (+http://www.google.com/adsbot.html)'],
            'adsbot-mobile' => ['Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X) AppleWebKit/537.36 (compatible; AdsBot-Google-Mobile)'],
            'bingbot' => ['Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'],
            'facebook' => ['facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'],
            'telegram' => ['TelegramBot (like TwitterBot)'],
        ];
    }

    /** @dataProvider allowedAgents */
    public function test_search_ad_and_social_bots_pass_in_all_mode(string $ua): void
    {
        $this->visit('/catalog?gclid=1', ['HTTP_USER_AGENT' => $ua])->assertDontSee('id="tyn-gate"', false);
    }

    public static function storefrontPassPaths(): array
    {
        return [['/cart'], ['/checkout'], ['/checkout/success/1'], ['/zamovlennya/1/oplata'], ['/robots.txt'], ['/webhooks/wayforpay'], ['/webhooks/monobank'], ['/api/callback'], ['/yml/prom.xml']];
    }

    /** @dataProvider storefrontPassPaths */
    public function test_cart_checkout_payment_feeds_and_callbacks_are_never_gated(string $uri): void
    {
        $this->visit($uri)->assertDontSee('id="tyn-gate"', false);
        $this->visit($uri, [], [], 'POST')->assertDontSee('id="tyn-gate"', false);
    }

    public function test_gate_page_is_not_stored_by_response_cache_and_cached_pages_do_not_bypass_it(): void
    {
        config(['responsecache.enabled' => true]);
        \Spatie\ResponseCache\Facades\ResponseCache::clear();
        // перший відвідувач із позначкою кешує сторінку; наступний без позначки однаково бачить заслін
        $this->visit('/login', [], ['tyn_pass' => $this->mark()])->assertOk()->assertDontSee('id="tyn-gate"', false);
        $this->visit('/login')->assertSee('id="tyn-gate"', false);
        $this->visit('/login')->assertSee('id="tyn-gate"', false);
        $this->visit('/login', [], ['tyn_pass' => $this->mark()])->assertDontSee('id="tyn-gate"', false);
    }

    public function test_wrong_answer_still_passes_in_soft_mode_and_returns_with_utm(): void
    {
        $this->verify = Http::response(['ok' => true, 'attempted' => true, 'correct' => false]);
        $r = $this->post('/tyn/pass', ['tyn_token' => 'tok_wrong', 'return' => '/catalog?utm_source=g&gclid=abc&utm_medium=cpc'], $this->srv());
        $r->assertRedirect('/catalog?utm_source=g&gclid=abc&utm_medium=cpc');
        $this->assertNotNull(collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass'));
    }

    public function test_service_down_fails_open_to_a_short_mark_and_page(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('down'));
        $r = $this->post('/tyn/pass', ['tyn_token' => 'tok', 'return' => '/catalog?gclid=1'], $this->srv());
        $r->assertRedirect('/catalog?gclid=1');
        $this->assertNotNull(collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass'));
    }

    public function test_pass_with_token_sets_hardened_mark_and_redirects_back(): void
    {
        $r = $this->post('/tyn/pass', ['tyn_token' => 'tok_abc', 'return' => '/catalog?page=2'], $this->srv());
        $r->assertRedirect('/catalog?page=2');
        $this->assertStringContainsString('no-store', (string) $r->headers->get('Cache-Control'));
        $c = collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass');
        $this->assertNotNull($c);
        $this->assertTrue($c->isHttpOnly());
        $this->assertSame('lax', $c->getSameSite());
        $this->assertSame('/', $c->getPath());
        $this->assertNull($c->getDomain());
        $this->assertGreaterThan(time() + 11 * 3600, $c->getExpiresTime());
        // з цією позначкою заслону вже немає
        $this->visit('/login', [], ['tyn_pass' => $c->getValue()])->assertDontSee('id="tyn-gate"', false);
        Http::assertSent(fn ($req) => str_ends_with($req->url(), '/v1/siteverify') && $req['token'] === 'tok_abc' && $req['secret'] === 'test-secret');
    }

    public function test_pass_without_token_or_rejected_shows_gate_again_without_cookie(): void
    {
        app()->setLocale('uk');
        $r = $this->post('/tyn/pass', ['return' => '/x'], $this->srv());
        $r->assertOk()->assertSee('id="tyn-gate"', false)->assertSee('Перевірку не завершено', false);
        $this->assertNull(collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass'));

        $this->verify = Http::response(['ok' => false]);
        $r = $this->post('/tyn/pass', ['tyn_token' => 'tok_old', 'return' => '/x'], $this->srv());
        $r->assertOk()->assertSee('id="tyn-gate"', false);
        $this->assertNull(collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass'));
    }

    public function test_pass_service_failure_and_unavailable_give_short_mark(): void
    {
        $this->verify = Http::response('x', 503);
        $r = $this->post('/tyn/pass', ['tyn_token' => 'tok_abc', 'return' => '/'], $this->srv());
        $r->assertRedirect('/');
        $c = collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass');
        $this->assertNotNull($c);
        $this->assertLessThanOrEqual(time() + 301, $c->getExpiresTime());

        $r = $this->post('/tyn/pass', ['tyn_token' => 'unavailable', 'return' => '/'], $this->srv());
        $c = collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass');
        $this->assertNotNull($c);
        $this->assertLessThanOrEqual(time() + 301, $c->getExpiresTime());
    }

    public static function badReturns(): array
    {
        return [['//evil.example/x'], ['https://evil.example/'], ['/\\evil.example'], ['javascript:alert(1)'], ["/ok\r\nSet-Cookie: a=b"], ['%2F%2Fevil.example'], [str_repeat('/a', 2000)]];
    }

    /** @dataProvider badReturns */
    public function test_pass_return_is_only_same_site_relative(string $return): void
    {
        $r = $this->post('/tyn/pass', ['tyn_token' => 'tok_abc', 'return' => $return], $this->srv());
        $r->assertRedirect('/');
    }

    public function test_pass_does_nothing_when_gate_off(): void
    {
        config(['tyn.gate' => 'off']);
        $r = $this->post('/tyn/pass', ['tyn_token' => 'tok_abc', 'return' => '/x'], $this->srv());
        $r->assertRedirect('/x');
        $this->assertNull(collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass'));
    }

    public function test_loop_protection_disables_autosubmit_and_explains_cookies(): void
    {
        app()->setLocale('en');
        $r = $this->visit('/login?a=1', ['HTTP_REFERER' => 'http://localhost/login?a=1', 'HTTP_HOST' => 'localhost']);
        $r->assertSee('id="tyn-gate"', false)->assertSee('does not seem to keep cookies', false);
        $this->assertStringContainsString('"0"!=="1"', $r->getContent());
    }

    public function test_bind_ties_mark_to_network(): void
    {
        config(['tyn.gate_bind' => true]);
        $r = $this->post('/tyn/pass', ['tyn_token' => 'tok_abc', 'return' => '/'], $this->srv(['REMOTE_ADDR' => '203.0.113.7']));
        $c = collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_pass');
        $same = $this->visit('/login', ['REMOTE_ADDR' => '203.0.113.99'], ['tyn_pass' => $c->getValue()]);
        $other = $this->visit('/login', ['REMOTE_ADDR' => '198.51.100.9'], ['tyn_pass' => $c->getValue()]);
        $same->assertDontSee('id="tyn-gate"', false);
        $other->assertSee('id="tyn-gate"', false);
    }

    /** Octane: один процес обслуговує запити поспіль, між ними нічого не лишається. */
    public function test_no_state_leaks_between_requests(): void
    {
        $m = $this->mark();
        $this->visit('/login')->assertSee('id="tyn-gate"', false);
        $this->visit('/login', [], ['tyn_pass' => $m])->assertDontSee('id="tyn-gate"', false);
        $this->visit('/login')->assertSee('id="tyn-gate"', false);
        config(['tyn.gate' => 'observe']);
        $this->visit('/login', ['HTTP_USER_AGENT' => 'curl/8.4.0'])->assertDontSee('id="tyn-gate"', false);
        config(['tyn.gate' => 'all']);
        $this->visit('/login', ['HTTP_USER_AGENT' => 'curl/8.4.0'])->assertSee('id="tyn-gate"', false);
    }
}
