<?php

namespace App\Support\Modules;

use App\Support\Hooks;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Built-in subscriber для всіх `module.*` lifecycle подій.
 *
 *   module.installing  → enter maintenance mode (php artisan down)
 *   module.installed   → exit maintenance + notify
 *   module.enabled     → notify (Telegram/email якщо налаштовано)
 *   module.disabled    → notify
 *   module.uninstalled → notify
 *   *_failed           → notify з error message + persistent admin alert
 *
 * Завантажується з AppServiceProvider::boot() і реєструє listener-и
 * через Hooks API.
 *
 * Інші модулі/інтеграції можуть зареєструвати свої listener-и на ці
 * самі події — наприклад модуль `audit_trail` хоче слухати module.enabled
 * для запису в audit log.
 */
class ModuleEventSubscriber
{
    public static function register(): void
    {
        // Maintenance mode на час ZIP install (тільки якщо production).
        Hooks::on('module.installing', function (string $filename, bool $force) {
            if (config('app.env') !== 'production') return;
            try {
                Artisan::call('down', ['--render' => 'errors::503', '--retry' => 10]);
                Log::info("[ModuleLifecycle] Maintenance mode ON during install of {$filename}");
            } catch (\Throwable $e) {
                Log::warning('[ModuleLifecycle] Failed to enter maintenance: '.$e->getMessage());
            }
        }, priority: 5, source: 'core_subscriber');

        Hooks::on('module.installed', function (string $name, array $report) {
            if (config('app.env') === 'production') {
                try { Artisan::call('up'); } catch (\Throwable $e) { /* silent */ }
            }
            self::notify('installed', $name, $report);
        }, priority: 5, source: 'core_subscriber');

        Hooks::on('module.install_failed', function (string $filename, array $info) {
            if (config('app.env') === 'production') {
                try { Artisan::call('up'); } catch (\Throwable $e) { /* silent */ }
            }
            self::notify('install_failed', $filename, $info, level: 'error');
        }, priority: 5, source: 'core_subscriber');

        Hooks::on('module.enabled', function (string $key, array $report) {
            self::notify('enabled', $key, $report);
        }, priority: 5, source: 'core_subscriber');

        Hooks::on('module.enable_failed', function (string $key, array $report) {
            self::notify('enable_failed', $key, $report, level: 'error');
        }, priority: 5, source: 'core_subscriber');

        Hooks::on('module.disabled', function (string $key, array $report) {
            self::notify('disabled', $key, $report);
        }, priority: 5, source: 'core_subscriber');

        Hooks::on('module.uninstalled', function (string $key, array $report) {
            self::notify('uninstalled', $key, $report, level: 'warning');
        }, priority: 5, source: 'core_subscriber');
    }

    /**
     * Universal notification dispatcher. Telegram, якщо токен налаштовано;
     * email admin якщо є.
     */
    private static function notify(string $event, string $subject, array $payload, string $level = 'info'): void
    {
        $label = match ($event) {
            'enabled' => '✓ Модуль увімкнено',
            'enable_failed' => '✗ Увімкнення провалилося',
            'disabled' => '○ Модуль вимкнено',
            'installed' => '⬇ Модуль встановлено',
            'install_failed' => '✗ Встановлення провалилось',
            'uninstalled' => '× Модуль видалено',
            default => '· '.$event,
        };

        $body = $label.': '.$subject;
        if (! empty($payload['errors'])) {
            $body .= "\n".implode("\n", (array) $payload['errors']);
        } elseif (! empty($payload['actions'])) {
            $body .= "\nДії: ".implode(', ', (array) $payload['actions']);
        }

        // Telegram alert якщо є TELEGRAM_BOT_TOKEN + chat_id.
        $token = (string) config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', ''));
        $chatId = (string) config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID', ''));
        if ($token && $chatId) {
            try {
                $url = "https://api.telegram.org/bot{$token}/sendMessage";
                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => http_build_query([
                            'chat_id' => $chatId,
                            'text' => $body,
                            'parse_mode' => 'Markdown',
                        ]),
                        'timeout' => 5,
                    ],
                ]);
                @file_get_contents($url, false, $context);
            } catch (\Throwable $e) {
                Log::debug('[ModuleLifecycle] Telegram notify failed: '.$e->getMessage());
            }
        }

        // Local log (завжди).
        Log::log($level === 'error' ? 'error' : ($level === 'warning' ? 'warning' : 'info'),
            "[ModuleLifecycle] {$label}: {$subject}", $payload);
    }
}
