<?php

namespace App\Services\Integrations\Concrete;

use App\Models\DisplaySetting;
use App\Models\NpCity;
use App\Models\NpWarehouse;
use App\Models\ShippingProvider;
use App\Services\Integrations\AbstractIntegration;
use Illuminate\Support\Facades\Schema;

class NovaPoshtaIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'novaposhta';
    }

    public function getName(): string
    {
        return 'Нова Пошта';
    }

    public function getDescription(): string
    {
        return 'Доставка у відділення, поштомати, кур’єр. ТТН, реєстри, відстеження.';
    }

    public function getGroup(): string
    {
        return 'shipping';
    }

    public function getIcon(): string
    {
        return '📦';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'placeholder' => '32 символи з my.novaposhta.ua'],
            ['key' => 'sender_ref', 'label' => 'Ref відправника', 'type' => 'text', 'placeholder' => ''],
            ['key' => 'sender_address', 'label' => 'Ref адреси відправника', 'type' => 'text', 'placeholder' => ''],
            ['key' => 'sender_contact', 'label' => 'Ref контактної особи', 'type' => 'text', 'placeholder' => ''],
        ];
    }

    public function getSettingsRoute(): ?string
    {
        return 'filament.admin.pages.nova-poshta-settings';
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }

        $provider = ShippingProvider::where('code', 'novaposhta')->first();
        $cfg = $provider?->configuration ?? [];

        $apiKey = $cfg['api_key']
            ?? DisplaySetting::get('np_api_key')
            ?? config('novaposhta.api_key');

        if (empty($apiKey)) {
            return ['level' => 'error', 'message' => 'API ключ не налаштовано'];
        }

        $senderRef = $cfg['sender_ref'] ?? DisplaySetting::get('np_sender_ref');
        if (empty($senderRef)) {
            return ['level' => 'warning', 'message' => 'Не визначено відправника'];
        }

        if (Schema::hasTable('np_cities') && Schema::hasTable('np_warehouses')) {
            $cities = NpCity::count();
            $warehouses = NpWarehouse::count();
            if ($cities === 0 || $warehouses === 0) {
                return ['level' => 'warning', 'message' => 'Базу міст/відділень не синхронізовано'];
            }
        }

        return ['level' => 'ok', 'message' => 'Готовий до роботи'];
    }
}
