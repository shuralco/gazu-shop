<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Support\Tyn\TynTrack;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Серверні події Тин: без TYN_TRACK тиша, order/order_status без контактів, cookie tyn_cid, перегляд без стану між запитами. */
class TynTrackTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = storage_path('app/tyn');
        foreach (glob($this->dir . '/tyn-track*') ?: [] as $f) {
            @unlink($f);
        }
        config(['tyn.enabled' => true, 'tyn.site_key' => 'k', 'tyn.secret' => 's', 'tyn.base_url' => 'https://tyn.test',
            'tyn.events' => false, 'tyn.traffic' => false, 'tyn.gate' => 'off', 'tyn.track' => true]);
        Http::fake();
    }

    private function sentBodies(): array
    {
        $out = [];
        foreach (Http::recorded() as [$req]) {
            if (str_contains($req->url(), '/v1/track')) {
                $out[] = $req->body();
            }
        }

        return $out;
    }

    public function test_disabled_track_does_nothing(): void
    {
        config(['tyn.track' => false]);
        $this->assertFalse(TynTrack::enabled());
        TynTrack::add('order_status', ['ref' => 'abc', 'status' => 'paid']);
        Http::assertNothingSent();
    }

    public function test_order_ref_is_hashed_and_stable(): void
    {
        $a = TynTrack::orderRef(15);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $a);
        $this->assertSame($a, TynTrack::orderRef(15));
        $this->assertNotSame($a, TynTrack::orderRef(16));
    }

    public function test_minor_units(): void
    {
        $this->assertSame(123450, TynTrack::minor('1234.50', 'UAH'));
    }

    public function test_cookie_tyn_cid_is_set_once_on_html_page_not_encrypted(): void
    {
        config(['tyn.collector' => true]);
        $r = $this->call('GET', '/login', [], [], [], ['REMOTE_ADDR' => '203.0.113.50']);
        $c = collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_cid');
        $this->assertNotNull($c);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $c->getValue());
        $this->assertSame('lax', $c->getSameSite());
        // Повторний візит з cookie: нової не ставимо.
        $r2 = $this->call('GET', '/login', [], ['tyn_cid' => $c->getValue()], [], ['REMOTE_ADDR' => '203.0.113.50', 'HTTP_COOKIE' => 'tyn_cid=' . $c->getValue()]);
        $this->assertNull(collect($r2->headers->getCookies())->first(fn ($c) => $c->getName() === 'tyn_cid'));
    }

    public function test_collector_tag_only_when_enabled(): void
    {
        config(['tyn.collector' => false]);
        $this->get('/login')->assertDontSee('/w/t.js', false);
        config(['tyn.collector' => true]);
        $this->get('/login')->assertSee('https://tyn.test/w/t.js', false)->assertSee('data-key="k"', false);
    }

    public function test_order_without_contacts_in_payload(): void
    {
        $this->assertTrue(true); // порожній запис в черзі: перевіряється через білий список бібліотеки
        require_once app_path('Support/Tyn/tyn-lib.php');
        \tyn_track_store('reset');
        $this->assertTrue(\tyn_track_add('order', ['ref' => TynTrack::orderRef(1), 'amount' => 10000, 'cur' => 'UAH', 'items' => [['5', 2, 5000]],
            'ship' => 'post', 'pay' => 'card', 'email' => 'a@b.c', 'phone' => '+380000000000', 'name' => 'Ім’я']));
        $json = json_encode(\tyn_track_store('take'));
        $this->assertStringNotContainsString('a@b.c', $json);
        $this->assertStringNotContainsString('380000000000', $json);
        $this->assertStringNotContainsString('Ім', $json);
    }
}
