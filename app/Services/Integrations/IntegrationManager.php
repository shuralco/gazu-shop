<?php

namespace App\Services\Integrations;

use App\Contracts\IntegrationInterface;
use Illuminate\Support\Collection;

class IntegrationManager
{
    private array $integrations = [];

    public function register(string $key, IntegrationInterface $integration): void
    {
        $this->integrations[$key] = $integration;
    }

    public function get(string $key): ?IntegrationInterface
    {
        return $this->integrations[$key] ?? null;
    }

    public function all(): array
    {
        return $this->integrations;
    }

    public function getEnabled(): Collection
    {
        return collect($this->integrations)->filter(fn ($i) => $i->isEnabled());
    }

    public function getByGroup(string $group): Collection
    {
        return collect($this->integrations)->filter(fn ($i) => $i->getGroup() === $group);
    }

    public function getGroups(): array
    {
        return [
            'payments' => 'Платежі',
            'shipping' => 'Доставка',
            'analytics' => 'Аналітика',
            'marketing' => 'Маркетплейси',
            'communication' => 'Комунікація',
            'fiscal' => 'Фіскалізація',
        ];
    }
}
