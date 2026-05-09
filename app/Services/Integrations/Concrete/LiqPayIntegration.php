<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class LiqPayIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'liqpay';
    }

    public function getName(): string
    {
        return 'LiqPay';
    }

    public function getDescription(): string
    {
        return 'Платіжна система від PrivatBank. Apple Pay, Google Pay, картки.';
    }

    public function getGroup(): string
    {
        return 'payments';
    }

    public function getIcon(): string
    {
        return '💳';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'public_key', 'label' => 'Public Key', 'type' => 'text', 'placeholder' => 'sandbox_...'],
            ['key' => 'private_key', 'label' => 'Private Key', 'type' => 'password', 'placeholder' => 'sandbox_...'],
            ['key' => 'sandbox', 'label' => 'Режим тестування (Sandbox)', 'type' => 'toggle', 'default' => true],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }

        $cfg = $this->getConfig();
        $public = $cfg['public_key'] ?? '';
        $private = $cfg['private_key'] ?? '';

        if (empty($public) || empty($private)) {
            return ['level' => 'error', 'message' => 'Не вказано Public/Private Key'];
        }

        $publicSandbox = str_starts_with($public, 'sandbox_');
        $privateSandbox = str_starts_with($private, 'sandbox_');
        if ($publicSandbox !== $privateSandbox) {
            return ['level' => 'warning', 'message' => 'Public та Private Key з різних середовищ'];
        }

        if (! empty($cfg['sandbox'])) {
            return ['level' => 'warning', 'message' => 'Працює в режимі Sandbox'];
        }

        return ['level' => 'ok', 'message' => 'Production-режим'];
    }
}
