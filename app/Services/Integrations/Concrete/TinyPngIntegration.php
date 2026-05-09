<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class TinyPngIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'tinypng';
    }

    public function getName(): string
    {
        return 'TinyPNG';
    }

    public function getDescription(): string
    {
        return 'Автоматична оптимізація зображень. Зменшує розмір до 80% без втрати якості. WebP конвертація.';
    }

    public function getGroup(): string
    {
        return 'analytics';
    }

    public function getIcon(): string
    {
        return '🖼️';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'placeholder' => 'DcRy...'],
            ['key' => 'max_width', 'label' => 'Макс. ширина (px)', 'type' => 'text', 'default' => '1920'],
            ['key' => 'convert_webp', 'label' => 'Конвертувати в WebP', 'type' => 'toggle', 'default' => true],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }
        $key = $this->getConfig()['api_key'] ?? '';
        if (empty($key)) {
            return ['level' => 'error', 'message' => 'Не вказано API Key'];
        }
        if (strlen($key) < 30) {
            return ['level' => 'warning', 'message' => 'API Key виглядає закоротким'];
        }

        return ['level' => 'ok', 'message' => 'Готовий до оптимізації'];
    }
}
