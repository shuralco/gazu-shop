<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;
use Illuminate\Support\Facades\Http;

class MeilisearchIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'meilisearch';
    }

    public function getName(): string
    {
        return 'Meilisearch';
    }

    public function getDescription(): string
    {
        return 'Швидкий повнотекстовий пошук. Автодоповнення, фільтри, фасети.';
    }

    public function getGroup(): string
    {
        return 'analytics';
    }

    public function getIcon(): string
    {
        return '🔍';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'host', 'label' => 'Host', 'type' => 'text', 'placeholder' => 'http://127.0.0.1:7700'],
            ['key' => 'master_key', 'label' => 'Master Key', 'type' => 'password', 'placeholder' => ''],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }

        $cfg = $this->getConfig();
        $host = $cfg['host'] ?? config('scout.meilisearch.host');

        if (empty($host)) {
            return ['level' => 'error', 'message' => 'Не вказано host'];
        }

        if (! filter_var($host, FILTER_VALIDATE_URL)) {
            return ['level' => 'warning', 'message' => 'Host має невірний формат URL'];
        }

        if (config('scout.driver') !== 'meilisearch') {
            return ['level' => 'warning', 'message' => 'SCOUT_DRIVER ≠ meilisearch у .env'];
        }

        return ['level' => 'ok', 'message' => 'Налаштовано'];
    }
}
