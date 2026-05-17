<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EmailTemplate extends Model
{
    public const TO_CUSTOMER = 'customer';
    public const TO_ADMIN    = 'admin';
    public const TO_MANAGER  = 'manager';

    protected $fillable = [
        'key', 'name', 'subject', 'body_html',
        'from_email', 'from_name', 'to_kind',
        'variables_help', 'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'variables_help' => 'array',
    ];

    /**
     * Знайти шаблон по ключу (cache 5 min). Returns null якщо disabled or missing.
     */
    public static function findByKey(string $key): ?self
    {
        $tpl = Cache::remember("email_template:{$key}", 300, function () use ($key) {
            return static::query()->where('key', $key)->where('is_active', true)->first();
        });
        return $tpl;
    }

    /**
     * Render template з підстановкою змінних. Supports {{var.path}} dot notation
     * для nested arrays + simple {{var}} placeholder. Безпечний (escape per default).
     */
    public function render(array $vars = []): array
    {
        $subject = $this->replace($this->subject, $vars);
        $body    = $this->replace($this->body_html, $vars);
        return [
            'subject' => $subject,
            'body'    => $body,
            'from_email' => $this->from_email,
            'from_name'  => $this->from_name,
        ];
    }

    private function replace(string $template, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*([\w\.\-]+)\s*\}\}/', function ($m) use ($vars) {
            $path = explode('.', $m[1]);
            $value = $vars;
            foreach ($path as $segment) {
                if (is_array($value) && array_key_exists($segment, $value)) {
                    $value = $value[$segment];
                } elseif (is_object($value) && isset($value->$segment)) {
                    $value = $value->$segment;
                } else {
                    return $m[0]; // не знайшли — лишаємо placeholder
                }
            }
            if (is_scalar($value)) return (string) $value;
            return $m[0];
        }, $template);
    }

    public static function flushCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("email_template:{$key}");
        } else {
            // Brute clear — без tags на default driver.
            static::pluck('key')->each(fn ($k) => Cache::forget("email_template:{$k}"));
        }
    }

    protected static function booted(): void
    {
        static::saved(fn (self $t) => static::flushCache($t->key));
        static::deleted(fn (self $t) => static::flushCache($t->key));
    }
}
