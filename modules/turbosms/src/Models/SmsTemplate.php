<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Шаблон SMS/Viber-повідомлення (модуль turbosms). Той самий контракт
 * плейсхолдерів, що EmailTemplate: {{var}} і {{var.path}} (nested arrays).
 */
class SmsTemplate extends Model
{
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_VIBER = 'viber';
    public const CHANNEL_HYBRID = 'hybrid'; // Viber → SMS fallback (TurboSMS робить сам)

    public const CHANNELS = [
        self::CHANNEL_SMS => 'Лише SMS',
        self::CHANNEL_VIBER => 'Лише Viber',
        self::CHANNEL_HYBRID => 'Viber → SMS (гібрид)',
    ];

    protected $fillable = [
        'key', 'name', 'channel', 'text', 'viber_text',
        'variables_help', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables_help' => 'array',
    ];

    protected static function booted(): void
    {
        $flush = fn (self $tpl) => Cache::forget("sms_template:{$tpl->key}");
        static::saved($flush);
        static::deleted($flush);
    }

    /** Знайти активний шаблон по ключу (cache 5 хв). */
    public static function findByKey(string $key): ?self
    {
        return Cache::remember("sms_template:{$key}", 300, function () use ($key) {
            return static::query()->where('key', $key)->where('is_active', true)->first();
        });
    }

    /**
     * @return array{sms_text:?string, viber_text:?string, channel:string}
     */
    public function render(array $vars = []): array
    {
        $sms = $this->replace($this->text, $vars);
        $viber = $this->viber_text ? $this->replace($this->viber_text, $vars) : $sms;

        return [
            'sms_text' => $sms,
            'viber_text' => $viber,
            'channel' => $this->channel,
        ];
    }

    private function replace(string $template, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*([\w\.\-]+)\s*\}\}/', function ($m) use ($vars) {
            $value = $vars;
            foreach (explode('.', $m[1]) as $segment) {
                if (is_array($value) && array_key_exists($segment, $value)) {
                    $value = $value[$segment];
                } elseif (is_object($value) && isset($value->$segment)) {
                    $value = $value->$segment;
                } else {
                    return $m[0];
                }
            }

            return is_scalar($value) ? (string) $value : $m[0];
        }, $template);
    }
}
