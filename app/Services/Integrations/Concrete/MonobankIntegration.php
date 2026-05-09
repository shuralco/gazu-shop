<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class MonobankIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'monobank';
    }

    public function getName(): string
    {
        return 'Monobank Acquiring';
    }

    public function getDescription(): string
    {
        return 'Інтернет-еквайринг від Monobank. Оплата карткою, Apple Pay, Google Pay.';
    }

    public function getGroup(): string
    {
        return 'payments';
    }

    public function getIcon(): string
    {
        return '🏦';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'token', 'label' => 'API Token', 'type' => 'password', 'placeholder' => 'uXPl...'],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }

        $token = $this->getConfig()['token'] ?? '';
        if (empty($token)) {
            return ['level' => 'error', 'message' => 'Не вказано API Token'];
        }

        if (strlen($token) < 20) {
            return ['level' => 'warning', 'message' => 'API Token виглядає закоротким'];
        }

        return ['level' => 'ok', 'message' => 'Готовий до роботи'];
    }
}
