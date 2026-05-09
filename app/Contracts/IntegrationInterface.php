<?php

namespace App\Contracts;

interface IntegrationInterface
{
    public function getKey(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function getGroup(): string;

    public function getIcon(): string;

    public function isEnabled(): bool;

    public function enable(): void;

    public function disable(): void;

    public function getConfigFields(): array;

    public function getConfig(): array;

    public function setConfig(array $config): void;

    /**
     * Filament page route name for dedicated settings page, or null.
     * Example: 'filament.admin.pages.nova-poshta-settings'.
     */
    public function getSettingsRoute(): ?string;

    /**
     * Module health: ['level' => 'ok'|'warning'|'error'|'unknown', 'message' => string].
     */
    public function getStatus(): array;
}
