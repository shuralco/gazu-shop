<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Події /v1/event йдуть через tyn_event бібліотеки (власний curl), тому замість Http::fake
 * піднімається локальна заглушка сервісу (php -S 127.0.0.1).
 */
class TynEventTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @var resource|null */
    private $proc = null;
    private string $log = '';
    private int $port = 0;

    protected function tearDown(): void
    {
        if (is_resource($this->proc)) {
            proc_terminate($this->proc);
            proc_close($this->proc);
        }
        if ($this->log !== '' && is_file($this->log)) {
            @unlink($this->log);
        }
        parent::tearDown();
    }

    private function startStub(int $status = 200): void
    {
        $sock = stream_socket_server('tcp://127.0.0.1:0', $e, $m);
        $this->port = (int) substr(strrchr(stream_socket_get_name($sock, false), ':'), 1);
        fclose($sock);
        $this->log = sys_get_temp_dir() . '/tyn-stub-' . getmypid() . '-' . $this->port . '.log';
        @unlink($this->log);
        $this->proc = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $this->port, __DIR__ . '/../Support/tyn_stub_server.php'],
            [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']],
            $pipes,
            null,
            ['TYN_TEST_LOG' => $this->log, 'TYN_TEST_STATUS' => (string) $status, 'PATH' => getenv('PATH')]
        );
        for ($i = 0; $i < 50; $i++) {
            $c = @fsockopen('127.0.0.1', $this->port, $en, $es, 0.1);
            if ($c) {
                fclose($c);
                break;
            }
            usleep(100000);
        }
    }

    private function enable(string $base): void
    {
        config([
            'tyn.enabled' => true, 'tyn.site_key' => 'test-site-key', 'tyn.secret' => 'test-secret',
            'tyn.base_url' => $base, 'tyn.mode' => 'soft', 'tyn.events' => true,
        ]);
        Http::fake(['*/v1/siteverify' => Http::response(['ok' => true, 'attempted' => true])]);
    }

    /** @return list<array{uri:string,post:array<string,mixed>}> */
    private function events(): array
    {
        if (!is_file($this->log)) {
            return [];
        }

        return array_map(fn ($l) => json_decode($l, true), array_filter(explode("\n", (string) file_get_contents($this->log))));
    }

    public function test_form_submit_has_right_fields_and_no_form_content(): void
    {
        $this->startStub();
        $this->enable('http://127.0.0.1:' . $this->port);

        $this->postJson(route('gazu.callback.store'), ['name' => 'Іван', 'phone' => '+380671234567', 'source' => 'footer', 'tyn_token' => 'tok_abc-1'])->assertOk();

        $ev = $this->events();
        $this->assertCount(1, $ev);
        $this->assertSame('/v1/event', $ev[0]['uri']);
        $this->assertEqualsCanonicalizing(['secret', 'event', 'token', 'form', 'fill_s', 'honeypot'], array_keys($ev[0]['post']));
        $this->assertSame('form_submit', $ev[0]['post']['event']);
        $this->assertSame('callback', $ev[0]['post']['form']);
        $this->assertSame('tok_abc-1', $ev[0]['post']['token']);
        $this->assertSame('test-secret', $ev[0]['post']['secret']);
        $this->assertStringNotContainsString('380671234567', json_encode($ev[0], JSON_UNESCAPED_UNICODE));
        $this->assertStringNotContainsString('Іван', json_encode($ev[0], JSON_UNESCAPED_UNICODE));
    }

    public function test_form_no_token_carries_session_chain_and_never_the_session_id(): void
    {
        $this->startStub();
        $this->enable('http://127.0.0.1:' . $this->port);

        $ids = [];
        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567'])->assertStatus(422);
        $ids[] = $this->app['session.store']->getId();
        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'unavailable'])->assertOk();
        $ids[] = $this->app['session.store']->getId();

        $ev = $this->events();
        $this->assertCount(2, $ev);
        foreach ($ev as $i => $e) {
            $this->assertSame('form_no_token', $e['post']['event']);
            $this->assertArrayNotHasKey('token', $e['post']);
            $this->assertSame(substr(sha1($ids[$i] . '|tyn|test-secret'), 0, 32), $e['post']['chain']);
            $this->assertSame(32, strlen($e['post']['chain']));
            $this->assertSame('callback', $e['post']['form']);
            $this->assertStringNotContainsString($ids[$i], json_encode($e));
        }
    }

    public function test_observe_mode_sends_event_without_siteverify(): void
    {
        $this->startStub();
        $this->enable('http://127.0.0.1:' . $this->port);

        $this->postJson(route('gazu.checkout.one-click'), ['product_id' => 999999, 'phone' => '+380671234567', 'tyn_token' => 'tok_abc'])
            ->assertJsonMissingValidationErrors('tyn_token');
        $this->postJson(route('gazu.checkout.one-click'), ['product_id' => 999999, 'phone' => '+380671234567']);

        $ev = $this->events();
        $this->assertCount(2, $ev);
        $this->assertSame('form_submit', $ev[0]['post']['event']);
        $this->assertSame('tok_abc', $ev[0]['post']['token']);
        $this->assertSame('other', $ev[0]['post']['form']);
        $this->assertSame('form_no_token', $ev[1]['post']['event']);
        Http::assertNothingSent();
    }

    public function test_no_session_means_no_no_token_event(): void
    {
        $this->startStub();
        $this->enable('http://127.0.0.1:' . $this->port);
        $mw = new \App\Support\Tyn\VerifyTyn();
        $req = \Illuminate\Http\Request::create('/x', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

        $mw->handle($req, fn () => response('ok'), 'callback');
        $mw->terminate($req, response('ok'));

        $this->assertSame([], $this->events());
    }

    public function test_events_switch_off(): void
    {
        $this->startStub();
        $this->enable('http://127.0.0.1:' . $this->port);
        config(['tyn.events' => false]);

        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'tok_abc'])->assertOk();
        $this->assertSame([], $this->events());
    }

    public function test_no_events_without_key(): void
    {
        $this->startStub();
        $this->enable('http://127.0.0.1:' . $this->port);
        config(['tyn.site_key' => null, 'tyn.secret' => null]);

        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'tok_abc'])->assertOk();
        $this->assertSame([], $this->events());
    }

    public function test_service_5xx_and_unreachable_do_not_break_the_response(): void
    {
        $this->startStub(500);
        $this->enable('http://127.0.0.1:' . $this->port);
        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671234567', 'tyn_token' => 'tok_abc'])
            ->assertOk()->assertJson(['ok' => true]);
        $this->assertCount(1, $this->events());

        $this->enable('http://127.0.0.1:9'); // порт закритий
        $this->postJson(route('gazu.callback.store'), ['phone' => '+380671235555', 'tyn_token' => 'tok_abc2'])
            ->assertOk()->assertJson(['ok' => true]);
    }
}
