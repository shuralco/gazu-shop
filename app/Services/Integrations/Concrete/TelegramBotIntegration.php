<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class TelegramBotIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'telegram';
    }

    public function getName(): string
    {
        return 'Telegram Bot';
    }

    public function getDescription(): string
    {
        return 'Сповіщення про замовлення в Telegram. Миттєві повідомлення в чат або групу.';
    }

    public function getGroup(): string
    {
        return 'communication';
    }

    public function getIcon(): string
    {
        return '✈️';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'bot_token', 'label' => 'Bot Token', 'type' => 'text', 'placeholder' => '123456:ABC-DEF...'],
            ['key' => 'chat_id', 'label' => 'Chat ID', 'type' => 'text', 'placeholder' => '-1001234567890'],
            ['key' => 'notify_new_orders', 'label' => 'Сповіщення про нові замовлення', 'type' => 'toggle', 'default' => true],
            ['key' => 'notify_status_changes', 'label' => 'Сповіщення про зміну статусу', 'type' => 'toggle', 'default' => false],
            ['key' => 'notify_low_stock', 'label' => 'Сповіщення про низький залишок', 'type' => 'toggle', 'default' => true],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }

        $cfg = $this->getConfig();
        $token = $cfg['bot_token'] ?? '';
        $chatId = $cfg['chat_id'] ?? '';

        if (empty($token)) {
            return ['level' => 'error', 'message' => 'Не вказано Bot Token'];
        }

        if (! preg_match('/^\d+:[A-Za-z0-9_-]{30,}$/', $token)) {
            return ['level' => 'warning', 'message' => 'Bot Token має невірний формат'];
        }

        if (empty($chatId)) {
            return ['level' => 'warning', 'message' => 'Не вказано Chat ID'];
        }

        $notifyAny = ($cfg['notify_new_orders'] ?? true) || ($cfg['notify_status_changes'] ?? false) || ($cfg['notify_low_stock'] ?? true);
        if (! $notifyAny) {
            return ['level' => 'warning', 'message' => 'Усі типи сповіщень вимкнено'];
        }

        return ['level' => 'ok', 'message' => 'Готовий до роботи'];
    }
}
