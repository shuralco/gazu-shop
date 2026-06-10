<?php

namespace App\Services\TurboSms;

use App\Models\DisplaySetting;
use Illuminate\Support\Facades\Http;

/**
 * HTTP-клієнт шлюзу TurboSMS (https://api.turbosms.ua).
 *
 * Налаштування зберігаються в DisplaySetting (редагуються на сторінці
 * «TurboSMS» в адмінці):
 *   turbosms_token        — API-токен з кабінету TurboSMS
 *   turbosms_sms_sender   — альфа-ім'я SMS (погоджується в TurboSMS)
 *   turbosms_viber_sender — Viber-відправник
 *
 * Відповідь шлюзу: {response_code: 0|…, response_status: "OK"|…, response_result: …}
 * code 0 = OK, 802 = частковий успіх (частина номерів прийнята).
 */
class TurboSmsClient
{
    private const BASE = 'https://api.turbosms.ua';

    public function configured(): bool
    {
        return $this->token() !== '';
    }

    public function token(): string
    {
        return trim((string) DisplaySetting::get('turbosms_token', ''));
    }

    public function smsSender(): string
    {
        return trim((string) DisplaySetting::get('turbosms_sms_sender', '')) ?: 'TurboSMS';
    }

    public function viberSender(): string
    {
        return trim((string) DisplaySetting::get('turbosms_viber_sender', '')) ?: $this->smsSender();
    }

    /**
     * Відправка повідомлення. Канал визначає, які блоки піде у запит:
     * sms — лише sms; viber — лише viber; hybrid — обидва (TurboSMS сам
     * зробить fallback Viber→SMS, тарифікується лише доставлений канал).
     *
     * Viber-опції ($viberOpts, усі необовʼязкові):
     *   button_text + button_url — кнопка під повідомленням (API: caption+action)
     *   image_url               — картинка у повідомленні
     *   is_transactional        — транзакційний пріоритет доставки
     *   ttl                     — час життя Viber-повідомлення, сек (60–86400)
     *
     * @param  list<string>  $recipients  номери у форматі 380XXXXXXXXX
     * @return array{ok:bool, code:int, status:string, message_ids:array<string,string>, error:?string, raw:array}
     */
    public function send(array $recipients, string $channel, string $smsText, ?string $viberText = null, array $viberOpts = []): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'code' => -1, 'status' => 'NOT_CONFIGURED', 'message_ids' => [], 'error' => 'TurboSMS token не задано (адмінка → TurboSMS)', 'raw' => []];
        }

        $payload = ['recipients' => array_values($recipients)];

        if (in_array($channel, [SmsChannel::SMS, SmsChannel::HYBRID], true)) {
            $payload['sms'] = ['sender' => $this->smsSender(), 'text' => $smsText];
        }
        if (in_array($channel, [SmsChannel::VIBER, SmsChannel::HYBRID], true)) {
            $viber = ['sender' => $this->viberSender(), 'text' => $viberText ?: $smsText];
            if (! empty($viberOpts['button_text']) && ! empty($viberOpts['button_url'])) {
                $viber['caption'] = (string) $viberOpts['button_text'];
                $viber['action'] = (string) $viberOpts['button_url'];
                $viber['count_clicks'] = 1; // обовʼязковий при caption/action — лічильник переходів
            }
            if (! empty($viberOpts['image_url'])) {
                $viber['image_url'] = (string) $viberOpts['image_url'];
            }
            if (! empty($viberOpts['is_transactional'])) {
                $viber['is_transactional'] = 1;
            }
            if (! empty($viberOpts['ttl'])) {
                $viber['ttl'] = max(60, min(86400, (int) $viberOpts['ttl']));
            }
            $payload['viber'] = $viber;
        }

        try {
            $resp = Http::withToken($this->token())
                ->timeout(15)
                ->post(self::BASE.'/message/send.json', $payload)
                ->json();
        } catch (\Throwable $e) {
            return ['ok' => false, 'code' => -2, 'status' => 'HTTP_ERROR', 'message_ids' => [], 'error' => $e->getMessage(), 'raw' => []];
        }

        $code = (int) ($resp['response_code'] ?? -3);
        $ok = in_array($code, [0, 802], true);

        // response_result: [{phone, response_code, message_id, response_status}, ...]
        $ids = [];
        $err = null;
        foreach ((array) ($resp['response_result'] ?? []) as $r) {
            if (! empty($r['message_id'])) {
                $ids[$r['phone'] ?? ''] = (string) $r['message_id'];
            } elseif (! $err && ! empty($r['response_status'])) {
                $err = $r['response_status'];
            }
        }
        if (! $ok && ! $err) {
            $err = (string) ($resp['response_status'] ?? 'UNKNOWN');
        }

        return ['ok' => $ok, 'code' => $code, 'status' => (string) ($resp['response_status'] ?? ''), 'message_ids' => $ids, 'error' => $err, 'raw' => (array) $resp];
    }

    /** Баланс кабінету TurboSMS (кредити). null = помилка/не налаштовано. */
    public function balance(): ?float
    {
        if (! $this->configured()) {
            return null;
        }

        try {
            $resp = Http::withToken($this->token())
                ->timeout(10)
                ->post(self::BASE.'/user/balance.json')
                ->json();

            $b = $resp['response_result']['balance'] ?? null;

            return $b === null ? null : (float) $b;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Статуси повідомлень по message_id (Queued/Sent/Delivered/Read/Failed...).
     *
     * @param  list<string>  $messageIds
     * @return array<string,string> message_id => status
     */
    public function statuses(array $messageIds): array
    {
        if (! $this->configured() || $messageIds === []) {
            return [];
        }

        try {
            $resp = Http::withToken($this->token())
                ->timeout(15)
                ->post(self::BASE.'/message/status.json', ['messages' => array_values($messageIds)])
                ->json();

            $out = [];
            foreach ((array) ($resp['response_result'] ?? []) as $r) {
                if (! empty($r['message_id'])) {
                    $out[(string) $r['message_id']] = (string) ($r['status'] ?? 'Unknown');
                }
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /** Нормалізація телефону до 380XXXXXXXXX. null якщо не схоже на укр. номер. */
    public static function normalizePhone(?string $phone): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($d, '380') && strlen($d) === 12) {
            return $d;
        }
        if (str_starts_with($d, '0') && strlen($d) === 10) {
            return '38'.$d;
        }
        if (strlen($d) === 9) { // без префікса: 671234567
            return '380'.$d;
        }

        return null;
    }
}
