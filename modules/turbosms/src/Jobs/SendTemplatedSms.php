<?php

namespace App\Jobs;

use App\Models\SmsMessage;
use App\Models\SmsTemplate;
use App\Services\TurboSms\TurboSmsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Відправка SMS/Viber за шаблоном sms_templates (модуль turbosms).
 * Queued — шлюз не блокує запит користувача. Результат пишеться в журнал
 * sms_messages (статус sent/failed + message_id для подальших статусів).
 */
class SendTemplatedSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public string $templateKey,
        public string $phone,
        public array $vars = [],
        public ?int $orderId = null,
    ) {}

    public function handle(TurboSmsClient $client): void
    {
        $phone = TurboSmsClient::normalizePhone($this->phone);
        if (! $phone) {
            Log::warning("[turbosms] Невалідний номер '{$this->phone}' для шаблону {$this->templateKey} — пропущено");

            return;
        }

        $tpl = SmsTemplate::findByKey($this->templateKey);
        if (! $tpl) {
            Log::warning("[turbosms] Шаблон '{$this->templateKey}' відсутній/вимкнений — пропущено");

            return;
        }

        $rendered = $tpl->render($this->vars);

        $log = SmsMessage::create([
            'phone' => $phone,
            'template_key' => $this->templateKey,
            'channel' => $rendered['channel'],
            'text' => $rendered['sms_text'],
            'status' => 'queued',
            'order_id' => $this->orderId,
        ]);

        $result = $client->send([$phone], $rendered['channel'], $rendered['sms_text'], $rendered['viber_text']);

        $log->update([
            'status' => $result['ok'] ? 'sent' : 'failed',
            'message_id' => $result['message_ids'][$phone] ?? null,
            'error' => $result['ok'] ? null : $result['error'],
        ]);

        if (! $result['ok']) {
            // NOT_CONFIGURED — не падаємо в retry (модуль увімкнено без токена);
            // решта помилок — кидаємо, щоб спрацював backoff/tries.
            if ($result['status'] !== 'NOT_CONFIGURED') {
                throw new \RuntimeException("[turbosms] {$result['status']}: {$result['error']}");
            }
        }
    }
}
