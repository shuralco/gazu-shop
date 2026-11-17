<?php

declare(strict_types=1);

namespace App\Support\Tyn;

use Closure;
use Throwable;

/**
 * Чиста логіка рішення (без Laravel), та сама, що в php/tyn.php::tyn_check.
 * Транспорт підставляється: fn(string $url, array $fields, float $timeout): array{status:int, body:string}.
 * status 0 = збій мережі чи тайм-аут.
 */
final class TynVerifier
{
    public const TOKEN_MAX = 512;
    public const RESPONSE_MAX = 65536;

    public const MESSAGES = [
        'missing' => 'Увімкніть JavaScript і пройдіть перевірку',
        'rejected' => 'Перевірка застаріла, спробуйте ще раз',
        'not_attempted' => 'Увімкніть JavaScript і пройдіть перевірку',
        'incorrect' => 'Перевірку не пройдено, спробуйте ще раз',
    ];

    /** @param Closure(string,array<string,string>,float):array{status:int,body:string} $transport */
    public function __construct(
        private Closure $transport,
        private ?Closure $logger = null,
    ) {
    }

    public static function normalizeBase(mixed $base): string
    {
        if (!is_string($base)) {
            return '';
        }
        $base = rtrim(trim($base), '/');
        if ($base === '' || preg_match('/[\x00-\x20\x7f\\\\@]/', $base)) {
            return '';
        }
        if (preg_match('#^https://[^/?\#]+#i', $base)) {
            return $base;
        }
        if (preg_match('#^http://(127\.0\.0\.1|localhost)(:[0-9]{1,5})?(/|$)#i', $base)) {
            return $base;
        }
        return '';
    }

    public static function tokenOk(mixed $token): bool
    {
        return is_string($token) && $token !== '' && strlen($token) <= self::TOKEN_MAX
            && preg_match('/^[A-Za-z0-9_.\-]+$/', $token) === 1;
    }

    /**
     * @param array{base_url?:mixed,mode?:string,messages?:array<string,string>,timeout?:float|int} $options
     * @return array{allow:bool,reason:string,attempted:?bool,correct:?bool,chain:?string,message:string}
     */
    public function check(?string $secret, mixed $token, array $options = []): array
    {
        try {
            return $this->decide($secret, $token, $options);
        } catch (Throwable) {
            // Будь-який виняток не ламає форму сайту.
            return $this->result(true, 'service_error');
        }
    }

    private function decide(?string $secret, mixed $token, array $options): array
    {
        $messages = array_merge(self::MESSAGES, $options['messages'] ?? []);
        $enforce = ($options['mode'] ?? 'soft') === 'enforce';

        if (!is_string($token) || $token === '') {
            return $this->result(false, 'missing', message: $messages['missing']);
        }
        if ($token === 'unavailable') {
            $this->log('widget unavailable');
            return $this->result(true, 'unavailable');
        }
        if (!self::tokenOk($token)) {
            return $this->result(false, 'missing', message: $messages['missing']);
        }

        $base = self::normalizeBase($options['base_url'] ?? 'https://c.tyn.com.ua');
        if ($base === '') {
            $this->log('config error: bad base_url');
            return $this->result(true, 'config_error');
        }
        if (!is_string($secret) || $secret === '') {
            $this->log('config error: empty secret');
            return $this->result(true, 'config_error');
        }

        $resp = ($this->transport)($base . '/v1/siteverify', ['secret' => $secret, 'token' => $token], (float) ($options['timeout'] ?? 2));
        $status = (int) ($resp['status'] ?? 0);
        if ($status !== 200) {
            $this->log('service error', $status === 0 ? 'network' : 'http ' . $status);
            return $this->result(true, 'service_error');
        }
        $body = (string) ($resp['body'] ?? '');
        $json = strlen($body) <= self::RESPONSE_MAX ? json_decode($body, true) : null;
        if (!is_array($json) || !isset($json['ok']) || !is_bool($json['ok'])) {
            $this->log('service error', 'bad json');
            return $this->result(true, 'service_error');
        }

        $chain = (isset($json['chain']) && is_string($json['chain']) && preg_match('/^[a-f0-9]{8,64}$/', $json['chain']))
            ? $json['chain'] : null;
        if ($json['ok'] === false) {
            return $this->result(false, 'rejected', chain: $chain, message: $messages['rejected']);
        }
        $correct = (isset($json['correct']) && is_bool($json['correct'])) ? $json['correct'] : null;
        if (($json['attempted'] ?? null) !== true) {
            return $this->result(false, 'not_attempted', false, $correct, $chain, $messages['not_attempted']);
        }
        if ($enforce && $correct !== true) {
            return $this->result(false, 'incorrect', true, $correct, $chain, $messages['incorrect']);
        }
        return $this->result(true, 'ok', true, $correct, $chain);
    }

    private function result(bool $allow, string $reason, ?bool $attempted = null, ?bool $correct = null, ?string $chain = null, string $message = ''): array
    {
        return compact('allow', 'reason', 'attempted', 'correct', 'chain', 'message');
    }

    /** Один короткий рядок: без секрету й без токена. */
    private function log(string $code, string $detail = ''): void
    {
        if ($this->logger === null) {
            return;
        }
        $line = 'tyn: ' . $code;
        if ($detail !== '') {
            $line .= ' (' . preg_replace('/[^A-Za-z0-9 _:.\/-]/', '', substr($detail, 0, 80)) . ')';
        }
        try {
            ($this->logger)($line);
        } catch (Throwable) {
        }
    }
}
