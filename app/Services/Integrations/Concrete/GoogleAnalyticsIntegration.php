<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class GoogleAnalyticsIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'google_analytics';
    }

    public function getName(): string
    {
        return 'Google Analytics';
    }

    public function getDescription(): string
    {
        return 'Аналітика відвідувачів Google Analytics 4. Відстеження конверсій та поведінки.';
    }

    public function getGroup(): string
    {
        return 'analytics';
    }

    public function getIcon(): string
    {
        return '📊';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'measurement_id', 'label' => 'Measurement ID', 'type' => 'text', 'placeholder' => 'G-XXXXXXXXXX'],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }
        $id = $this->getConfig()['measurement_id'] ?? '';
        if (empty($id)) {
            return ['level' => 'error', 'message' => 'Не вказано Measurement ID'];
        }
        if (! preg_match('/^G-[A-Z0-9]{8,12}$/', $id)) {
            return ['level' => 'warning', 'message' => 'Measurement ID має формат G-XXXXXXXXXX'];
        }

        return ['level' => 'ok', 'message' => 'GA4 активний'];
    }
}
