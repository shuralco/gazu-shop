<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class MeestIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'meest';
    }

    public function getName(): string
    {
        return 'Meest';
    }

    public function getDescription(): string
    {
        return 'Міжнародна доставка Meest. Поштомати, відділення, кур\'єрська доставка.';
    }

    public function getGroup(): string
    {
        return 'shipping';
    }

    public function getIcon(): string
    {
        return '🚚';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'placeholder' => ''],
            ['key' => 'username', 'label' => 'Логін', 'type' => 'text', 'placeholder' => ''],
            ['key' => 'password', 'label' => 'Пароль', 'type' => 'password', 'placeholder' => ''],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }
        $cfg = $this->getConfig();
        $missing = array_filter(['api_key', 'username', 'password'], fn ($k) => empty($cfg[$k]));
        if ($missing) {
            return ['level' => 'error', 'message' => 'Не заповнено: '.implode(', ', $missing)];
        }

        return ['level' => 'ok', 'message' => 'Готовий до роботи'];
    }
}
