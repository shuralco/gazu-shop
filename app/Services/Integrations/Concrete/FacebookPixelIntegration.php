<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class FacebookPixelIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'facebook_pixel';
    }

    public function getName(): string
    {
        return 'Facebook Pixel';
    }

    public function getDescription(): string
    {
        return 'Піксель Facebook для відстеження конверсій та ретаргетингу.';
    }

    public function getGroup(): string
    {
        return 'analytics';
    }

    public function getIcon(): string
    {
        return '📱';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'pixel_id', 'label' => 'Pixel ID', 'type' => 'text', 'placeholder' => '1234567890'],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }
        $id = $this->getConfig()['pixel_id'] ?? '';
        if (empty($id)) {
            return ['level' => 'error', 'message' => 'Не вказано Pixel ID'];
        }
        if (! preg_match('/^\d{10,16}$/', $id)) {
            return ['level' => 'warning', 'message' => 'Pixel ID має складатись лише з цифр (10-16 символів)'];
        }

        return ['level' => 'ok', 'message' => 'Pixel активний'];
    }
}
