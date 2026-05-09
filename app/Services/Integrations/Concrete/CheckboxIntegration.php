<?php

namespace App\Services\Integrations\Concrete;

use App\Services\Integrations\AbstractIntegration;

class CheckboxIntegration extends AbstractIntegration
{
    public function getKey(): string
    {
        return 'checkbox';
    }

    public function getName(): string
    {
        return 'Checkbox';
    }

    public function getDescription(): string
    {
        return 'Фіскалізація чеків через Checkbox. Автоматична видача фіскальних чеків.';
    }

    public function getGroup(): string
    {
        return 'fiscal';
    }

    public function getIcon(): string
    {
        return '🧾';
    }

    public function getConfigFields(): array
    {
        return [
            ['key' => 'login', 'label' => 'Логін касира', 'type' => 'text', 'placeholder' => 'cashier@example.com'],
            ['key' => 'password', 'label' => 'Пароль касира', 'type' => 'password', 'placeholder' => ''],
            ['key' => 'license_key', 'label' => 'Ліцензійний ключ каси', 'type' => 'text', 'placeholder' => ''],
        ];
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return ['level' => 'unknown', 'message' => 'Модуль вимкнено'];
        }
        $cfg = $this->getConfig();
        $missing = array_filter(['login', 'password', 'license_key'], fn ($k) => empty($cfg[$k]));
        if ($missing) {
            return ['level' => 'error', 'message' => 'Не заповнено: '.implode(', ', $missing)];
        }
        if (! filter_var($cfg['login'], FILTER_VALIDATE_EMAIL)) {
            return ['level' => 'warning', 'message' => 'Логін має бути email-адресою касира'];
        }

        return ['level' => 'ok', 'message' => 'Каса налаштована'];
    }
}
