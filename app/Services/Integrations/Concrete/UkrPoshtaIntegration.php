<?php

namespace App\Services\Integrations\Concrete;

use App\Models\DisplaySetting;
use App\Models\ShippingProvider;
use App\Services\Integrations\AbstractIntegration;

class UkrPoshtaIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'ukrposhta';
    }

    public function getName(): string
    {
        return 'УкрПошта';
    }

    public function getDescription(): string
    {
        return 'Доставка у відділення УкрПошти. Address Classifier API: міста, відділення, поштові індекси.';
    }

    public function getGroup(): string
    {
        return 'shipping';
    }

    public function getIcon(): string
    {
        return '📮';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'api_url', 'label' => 'API URL', 'type' => 'text', 'placeholder' => 'https://www.ukrposhta.ua/'],
            ['key' => 'bearer_token', 'label' => 'Bearer Token (ecom)', 'type' => 'password', 'placeholder' => 'optional, для TTN'],
            ['key' => 'sandbox', 'label' => 'Sandbox', 'type' => 'toggle', 'default' => true],
        ];
    }

    public function getSettingsRoute(): ?string
    {
        return 'filament.admin.pages.ukr-poshta-settings';
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }

        // Address Classifier is public — primary health signal.
        $apiUrl = config('ukrposhta.api');
        if (empty($apiUrl)) {
            return ['level' => 'error', 'message' => 'API URL не налаштовано'];
        }

        // Bearer + sender UUID required for ecom (TTN creation).
        $provider = ShippingProvider::where('code', 'ukrposhta')->first();
        $cfg = $provider?->configuration ?? [];

        $ecom = DisplaySetting::get('up_ecom_bearer')
            ?? $cfg['bearer_token']
            ?? config('ukrposhta.bearer_token');
        $senderUuid = DisplaySetting::get('up_sender_uuid');

        if (empty($ecom)) {
            return ['level' => 'warning', 'message' => 'Address Classifier OK, ecom Bearer не задано'];
        }
        if (empty($senderUuid)) {
            return ['level' => 'warning', 'message' => 'ecom OK, sender UUID не задано'];
        }

        return ['level' => 'ok', 'message' => 'Готовий до роботи (ecom активний)'];
    }
}
