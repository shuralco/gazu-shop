<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class GoogleShoppingIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'google_shopping';
    }

    public function getName(): string
    {
        return 'Google Shopping';
    }

    public function getDescription(): string
    {
        return 'Експорт товарів у Google Merchant Center для рекламних кампаній.';
    }

    public function getGroup(): string
    {
        return 'marketing';
    }

    public function getIcon(): string
    {
        return '🛒';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text', 'placeholder' => '123456789'],
        ];
    }
}
