<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class WayForPayIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'wayforpay';
    }

    public function getName(): string
    {
        return 'WayForPay';
    }

    public function getDescription(): string
    {
        return 'Платіжний шлюз WayForPay. Visa, Mastercard, Apple Pay, Google Pay.';
    }

    public function getGroup(): string
    {
        return 'payments';
    }

    public function getIcon(): string
    {
        return '💰';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'merchant_account', 'label' => 'Merchant Account', 'type' => 'text', 'placeholder' => 'test_merchant'],
            ['key' => 'merchant_secret', 'label' => 'Merchant Secret Key', 'type' => 'password', 'placeholder' => ''],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }
        $cfg = $this->getConfig();
        if (empty($cfg['merchant_account']) || empty($cfg['merchant_secret'])) {
            return ['level' => 'error', 'message' => 'Не вказано Merchant Account або Secret Key'];
        }
        if (str_starts_with((string) $cfg['merchant_account'], 'test_')) {
            return ['level' => 'warning', 'message' => 'Тестовий merchant — для production змініть'];
        }

        return ['level' => 'ok', 'message' => 'Готовий до роботи'];
    }
}
