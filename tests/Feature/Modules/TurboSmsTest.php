<?php

namespace Tests\Feature\Modules;

use App\Jobs\SendTemplatedSms;
use App\Models\DisplaySetting;
use App\Models\SmsMessage;
use App\Models\SmsTemplate;
use App\Services\TurboSms\TurboSmsClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurboSmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Міграції модуля реєструються лише коли модуль увімкнено на boot —
        // у тестах ганяємо їх напряму (ідемпотентні).
        foreach (glob(base_path('modules/turbosms/database/migrations/*.php')) as $file) {
            (require $file)->up();
        }
    }

    // ---------------------------------------------------------------- phone

    public function test_phone_normalization(): void
    {
        $this->assertSame('380671234567', TurboSmsClient::normalizePhone('067 123 45 67'));
        $this->assertSame('380671234567', TurboSmsClient::normalizePhone('+38 (067) 123-45-67'));
        $this->assertSame('380671234567', TurboSmsClient::normalizePhone('380671234567'));
        $this->assertSame('380671234567', TurboSmsClient::normalizePhone('671234567'));
        $this->assertNull(TurboSmsClient::normalizePhone('12345'));
        $this->assertNull(TurboSmsClient::normalizePhone(null));
        $this->assertNull(TurboSmsClient::normalizePhone('14155551234567')); // не-укр
    }

    // ------------------------------------------------------------ templates

    public function test_migration_seeds_default_templates_idempotently(): void
    {
        $this->assertSame(4, SmsTemplate::count());

        // ручна правка
        SmsTemplate::where('key', 'order.created')->update(['text' => 'CUSTOM']);

        // повторний прогін сіда не перетирає
        foreach (glob(base_path('modules/turbosms/database/migrations/*.php')) as $file) {
            (require $file)->up();
        }

        $this->assertSame(4, SmsTemplate::count());
        $this->assertSame('CUSTOM', SmsTemplate::where('key', 'order.created')->value('text'));
    }

    public function test_render_substitutes_nested_placeholders(): void
    {
        $r = SmsTemplate::findByKey('order.shipped')->render([
            'order' => ['id' => '77', 'ttn' => '20450123456789'],
        ]);

        $this->assertStringContainsString('№77', $r['sms_text']);
        $this->assertStringContainsString('20450123456789', $r['sms_text']);
        // viber_text окремий і теж відрендерений
        $this->assertStringContainsString('20450123456789', $r['viber_text']);
        // кнопка: URL з підставленим ТТН
        $this->assertSame('https://novaposhta.ua/tracking/?cargo_number=20450123456789', $r['viber_opts']['button_url']);
        $this->assertTrue($r['viber_opts']['is_transactional']);
    }

    public function test_render_keeps_unknown_placeholders_intact(): void
    {
        $r = SmsTemplate::findByKey('order.created')->render([]);

        $this->assertStringContainsString('{{order.id}}', $r['sms_text']);
    }

    public function test_find_by_key_ignores_inactive(): void
    {
        SmsTemplate::where('key', 'order.paid')->update(['is_active' => false]);
        // mass update не тригерить saved-observer — чистимо кеш руками
        \Illuminate\Support\Facades\Cache::forget('sms_template:order.paid');

        $this->assertNull(SmsTemplate::findByKey('order.paid'));
    }

    // --------------------------------------------------------------- client

    public function test_client_builds_hybrid_payload_with_viber_button(): void
    {
        DisplaySetting::set('turbosms_token', 'test-token');
        DisplaySetting::set('turbosms_sms_sender', 'GAZU');
        DisplaySetting::resetRequestCache();

        Http::fake([
            'api.turbosms.ua/*' => Http::response([
                'response_code' => 0, 'response_status' => 'OK',
                'response_result' => [['phone' => '380671234567', 'message_id' => 'mid-1']],
            ]),
        ]);

        $res = app(TurboSmsClient::class)->send(
            ['380671234567'], 'hybrid', 'sms text', 'viber text',
            ['button_text' => 'Відстежити', 'button_url' => 'https://x/1', 'is_transactional' => true, 'ttl' => 7200],
        );

        $this->assertTrue($res['ok']);
        $this->assertSame('mid-1', $res['message_ids']['380671234567']);

        Http::assertSent(function ($req) {
            return $req['sms']['sender'] === 'GAZU'
                && $req['viber']['caption'] === 'Відстежити'
                && $req['viber']['action'] === 'https://x/1'
                && $req['viber']['count_clicks'] === 1
                && $req['viber']['is_transactional'] === 1
                && $req['viber']['ttl'] === 7200;
        });
    }

    public function test_client_sms_only_channel_has_no_viber_block(): void
    {
        DisplaySetting::set('turbosms_token', 'test-token');
        DisplaySetting::resetRequestCache();

        Http::fake(['api.turbosms.ua/*' => Http::response(['response_code' => 0, 'response_status' => 'OK', 'response_result' => []])]);

        app(TurboSmsClient::class)->send(['380671234567'], 'sms', 'text');

        Http::assertSent(fn ($req) => isset($req['sms']) && ! isset($req['viber']));
    }

    public function test_simulate_mode_sends_no_http_and_returns_simulated(): void
    {
        DisplaySetting::set('turbosms_simulate', true);
        DisplaySetting::set('turbosms_token', ''); // навіть без токена — імітація працює
        DisplaySetting::resetRequestCache();
        Http::fake();

        $res = app(TurboSmsClient::class)->send(['380671234567'], 'hybrid', 'text');

        $this->assertTrue($res['ok']);
        $this->assertSame('SIMULATED', $res['status']);
        $this->assertStringStartsWith('SIM-', $res['message_ids']['380671234567']);
        Http::assertNothingSent();
    }

    public function test_job_in_simulate_mode_logs_simulated_status(): void
    {
        DisplaySetting::set('turbosms_simulate', true);
        DisplaySetting::resetRequestCache();
        Http::fake();

        (new SendTemplatedSms('order.created', '0671234567', ['order' => ['id' => '9', 'total' => '50']]))
            ->handle(app(TurboSmsClient::class));

        $log = SmsMessage::sole();
        $this->assertSame('simulated', $log->status);
        $this->assertStringStartsWith('SIM-', $log->message_id);
        Http::assertNothingSent();
    }

    public function test_client_without_token_returns_not_configured_without_http(): void
    {
        DisplaySetting::set('turbosms_token', '');
        DisplaySetting::resetRequestCache();

        Http::fake();

        $res = app(TurboSmsClient::class)->send(['380671234567'], 'sms', 'text');

        $this->assertFalse($res['ok']);
        $this->assertSame('NOT_CONFIGURED', $res['status']);
        Http::assertNothingSent();
    }

    // ------------------------------------------------------------------ job

    public function test_job_sends_and_logs_message(): void
    {
        DisplaySetting::set('turbosms_token', 'test-token');
        DisplaySetting::resetRequestCache();

        Http::fake([
            'api.turbosms.ua/*' => Http::response([
                'response_code' => 0, 'response_status' => 'OK',
                'response_result' => [['phone' => '380671234567', 'message_id' => 'mid-9']],
            ]),
        ]);

        (new SendTemplatedSms('order.created', '0671234567', ['order' => ['id' => '5', 'total' => '100']], 5))
            ->handle(app(TurboSmsClient::class));

        $log = SmsMessage::sole();
        $this->assertSame('380671234567', $log->phone);
        $this->assertSame('sent', $log->status);
        $this->assertSame('mid-9', $log->message_id);
        $this->assertSame(5, (int) $log->order_id);
        $this->assertStringContainsString('№5', $log->text);
    }

    public function test_job_without_token_logs_failed_and_does_not_throw(): void
    {
        DisplaySetting::set('turbosms_token', '');
        DisplaySetting::resetRequestCache();
        Http::fake();

        (new SendTemplatedSms('order.created', '0671234567', ['order' => ['id' => '5']]))
            ->handle(app(TurboSmsClient::class));

        $this->assertSame('failed', SmsMessage::sole()->status);
        Http::assertNothingSent();
    }

    public function test_job_skips_invalid_phone_silently(): void
    {
        (new SendTemplatedSms('order.created', '123', []))->handle(app(TurboSmsClient::class));

        $this->assertSame(0, SmsMessage::count());
    }

    public function test_job_gateway_error_marks_failed_and_throws_for_retry(): void
    {
        DisplaySetting::set('turbosms_token', 'test-token');
        DisplaySetting::resetRequestCache();

        Http::fake(['api.turbosms.ua/*' => Http::response(['response_code' => 203, 'response_status' => 'REQUIRED_TOKEN', 'response_result' => null])]);

        $this->expectException(\RuntimeException::class);

        try {
            (new SendTemplatedSms('order.created', '0671234567', ['order' => ['id' => '5']]))
                ->handle(app(TurboSmsClient::class));
        } finally {
            $this->assertSame('failed', SmsMessage::sole()->status);
        }
    }

    // ----------------------------------------------------------------- gate

    public function test_resource_gated_when_module_disabled(): void
    {
        // модуль вимкнено (немає DB-row, config default false)
        $this->assertFalse(\App\Filament\Resources\SmsTemplateResource::moduleEnabled());

        \App\Models\Module::create(['key' => 'turbosms', 'enabled' => true]);
        \App\Support\ModuleManager::clearCache();

        $this->assertTrue(\App\Filament\Resources\SmsTemplateResource::moduleEnabled());
    }
}
