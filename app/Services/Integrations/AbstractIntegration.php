<?php

namespace App\Services\Integrations;

use App\Contracts\IntegrationInterface;
use App\Models\ShopSettings;

abstract class AbstractIntegration implements IntegrationInterface
{
    public function isEnabled(): bool
    {
        return (bool) shopSetting("integration_{$this->getKey()}_enabled", false);
    }

    public function enable(): void
    {
        ShopSettings::set("integration_{$this->getKey()}_enabled", true, 'boolean', 'integrations');
    }

    public function disable(): void
    {
        ShopSettings::set("integration_{$this->getKey()}_enabled", false, 'boolean', 'integrations');
    }

    public function getConfig(): array
    {
        $config = shopSetting("integration_{$this->getKey()}_config", '{}');

        return is_string($config) ? json_decode($config, true) ?? [] : (array) $config;
    }

    public function setConfig(array $config): void
    {
        ShopSettings::set("integration_{$this->getKey()}_config", json_encode($config), 'json', 'integrations');
    }

    public function getConfigFields(): array
    {
        return [];
    }

    public function getSettingsRoute(): ?string
    {
        // Default: route to the universal config page when the integration
        // has at least one config field. Subclasses can override to point at
        // a bespoke page (e.g. NovaPoshtaSettings).
        if (count($this->getConfigFields()) === 0) {
            return null;
        }

        return null; // Falls back to modal in IntegrationsPage; URL handled separately
    }

    /**
     * Generic config-page URL helper used by IntegrationsPage when there is
     * no dedicated settings route.
     */
    public function getGenericConfigUrl(): ?string
    {
        if (count($this->getConfigFields()) === 0) {
            return null;
        }

        try {
            return route('filament.admin.pages.integration-config', ['key' => $this->getKey()]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Вимкнено'];
        }

        $config = $this->getConfig();
        foreach ($this->getConfigFields() as $field) {
            $key = $field['key'] ?? null;
            if (! $key) {
                continue;
            }
            $type = $field['type'] ?? 'text';
            if ($type === 'toggle') {
                continue;
            }
            if (empty($config[$key])) {
                return ['level' => 'warning', 'message' => 'Не заповнено: '.($field['label'] ?? $key)];
            }
        }

        return ['level' => 'ok', 'message' => 'Готово до роботи'];
    }
}
