<?php

namespace Tests\Feature;

use App\Http\Middleware\TynTraffic;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Облік відвідувань для «Тин»: буфер-файл за бібліотекою php/tyn.php. Мережі в тестах немає:
 * базова адреса вказує на закритий локальний порт, пачка не доставляється й лишається в робочому файлі.
 */
class TynTrafficTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->dir = storage_path('app/tyn');
        $this->wipe();
        config([
            'tyn.enabled' => true, 'tyn.site_key' => 'test-site-key', 'tyn.secret' => 'test-secret',
            'tyn.base_url' => 'http://127.0.0.1:9', 'tyn.traffic' => true, 'tyn.events' => false,
        ]);
        Http::fake();
        // Типова адреса тестового клієнта loopback не обліковується: даємо публічну, як у справжнього відвідувача.
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.50']);
    }

    protected function tearDown(): void
    {
        $this->wipe();
        parent::tearDown();
    }

    private function wipe(): void
    {
        foreach (glob($this->dir . '/tyn-traffic.*') ?: [] as $f) {
            @unlink($f);
        }
    }

    /** @return list<array<string,mixed>> */
    private function records(): array
    {
        $out = [];
        foreach (['tyn-traffic.work', 'tyn-traffic.buf'] as $name) {
            $f = $this->dir . '/' . $name;
            if (is_file($f)) {
                foreach (explode("\n", trim((string) file_get_contents($f))) as $line) {
                    if ($line !== '') {
                        $out[] = json_decode($line, true);
                    }
                }
            }
        }

        return $out;
    }

    private function raw(): string
    {
        $all = '';
        foreach (glob($this->dir . '/tyn-traffic.*') ?: [] as $f) {
            $all .= (string) file_get_contents($f);
        }

        return $all;
    }

    public function test_off_by_default(): void
    {
        config(['tyn.traffic' => false]);
        $this->get('/login')->assertOk();
        $this->assertSame([], $this->records());
    }

    public function test_nothing_without_key(): void
    {
        config(['tyn.site_key' => null, 'tyn.secret' => null]);
        $this->get('/login')->assertOk();
        $this->assertSame([], $this->records());
    }

    public function test_page_hit_is_recorded_without_query_and_cookie_values(): void
    {
        $this->withHeaders(['Referer' => 'https://www.google.com/search?q=SECRETQUERY', 'User-Agent' => 'TestBrowser/1.0', 'Accept-Language' => 'uk-UA,uk;q=0.9'])
            ->get('/login?token=QUERYVALUE456&utm=x', ['REMOTE_ADDR' => '203.0.113.77', 'HTTP_COOKIE' => 'sess_cookie=COOKIEVALUE123'])
            ->assertOk();

        $recs = $this->records();
        $this->assertCount(1, $recs);
        $r = $recs[0];
        $this->assertSame('/login', $r['p']);
        $this->assertSame('GET', $r['m']);
        $this->assertSame(200, $r['s']);
        $this->assertSame('203.0.113.0/24', $r['net']);
        $this->assertSame('www.google.com', $r['ref']);
        $this->assertSame('uk', $r['lang']);
        $this->assertSame(1, $r['ck']);
        $this->assertSame('TestBrowser/1.0', $r['ua']);
        $raw = $this->raw();
        foreach (['COOKIEVALUE123', 'QUERYVALUE456', 'SECRETQUERY', '203.0.113.77', '?'] as $bad) {
            $this->assertStringNotContainsString($bad, $raw, $bad);
        }
        $this->assertStringNotContainsString('test-secret', $raw);
    }

    public function test_real_client_ip_via_trusted_proxy_header(): void
    {
        $this->get('/login', ['REMOTE_ADDR' => '10.0.0.5', 'HTTP_X_FORWARDED_FOR' => '198.51.100.23'])->assertOk();
        $recs = $this->records();
        $this->assertCount(1, $recs);
        $this->assertSame('198.51.100.0/24', $recs[0]['net']);
    }

    public static function privateAddresses(): array
    {
        return [['10.1.2.3'], ['172.20.0.5'], ['192.168.1.9'], ['127.0.0.1'], ['::1'], ['fd00::5']];
    }

    /** @dataProvider privateAddresses */
    public function test_private_and_loopback_addresses_are_not_recorded(string $ip): void
    {
        $this->get('/login', ['REMOTE_ADDR' => $ip])->assertOk();
        $this->assertSame([], $this->records());
    }

    public function test_public_address_is_recorded(): void
    {
        $this->get('/login', ['REMOTE_ADDR' => '203.0.113.9'])->assertOk();
        $this->assertCount(1, $this->records());
    }

    public function test_signed_warm_requests_are_not_recorded_even_from_public_address(): void
    {
        $this->get('/login', ['REMOTE_ADDR' => '203.0.113.9', 'HTTP_USER_AGENT' => 'gazu-warm/1.0', 'HTTP_X_GAZU_WARM' => \App\Support\Tyn\TynGateSupport::warmToken()])->assertOk();
        $this->assertSame([], $this->records());
        $this->get('/login', ['REMOTE_ADDR' => '203.0.113.9', 'HTTP_X_GAZU_WARM' => 'forged'])->assertOk();
        $this->assertCount(1, $this->records());
    }

    public function test_only_storefront_html_get_is_recorded(): void
    {
        $this->post('/login', ['email' => 'a@b.c', 'password' => 'x']);
        $this->getJson('/csrf-token');
        $this->get('/admin/login');
        $this->get('/livewire/update');
        $this->get('/up');
        $this->get('/login', ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $this->get('/logo.png');
        $this->assertSame([], $this->records());

        $this->get('/login')->assertOk();
        $this->assertCount(1, $this->records());
    }

    public function test_batch_interval_is_at_least_two_seconds(): void
    {
        require_once app_path('Support/Tyn/tyn-lib.php');
        $f = $this->dir . '/tyn-traffic.lock';
        $this->get('/login')->assertOk(); // створює буфер і замок
        $url = 'http://127.0.0.1:9';

        $t = time();
        // Сама сторінка вже спробувала надіслати: час останнього надсилання = $t (або раніше).
        file_put_contents($f, (string) $t);

        tyn_traffic_flush($this->dir, $url, 'k', 's', ['now' => $t + 1]); // менше 2 с: нічого не робить
        $this->assertSame((string) $t, trim((string) file_get_contents($f)));

        tyn_traffic_flush($this->dir, $url, 'k', 's', ['now' => $t + 2]); // рівно 2 с: надсилає
        $this->assertSame((string) ($t + 2), trim((string) file_get_contents($f)));

        // Збій доставки: записи лишаються, але нова спроба знову не раніше ніж через 2 с.
        tyn_traffic_flush($this->dir, $url, 'k', 's', ['now' => $t + 3]);
        $this->assertSame((string) ($t + 2), trim((string) file_get_contents($f)));
    }

    public function test_flush_failure_is_silent(): void
    {
        // Порт закритий: запит до сервісу падає, відповідь сторінки не страждає.
        $this->get('/login')->assertOk();
        $this->get('/login')->assertOk();
        $this->assertNotEmpty($this->records());
    }

    public function test_middleware_is_registered_in_web_group(): void
    {
        $groups = app('router')->getMiddlewareGroups();
        $this->assertContains(TynTraffic::class, $groups['web']);
    }
}
