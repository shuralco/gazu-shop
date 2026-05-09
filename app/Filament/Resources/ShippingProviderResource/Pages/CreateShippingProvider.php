<?php

namespace App\Filament\Resources\ShippingProviderResource\Pages;

use App\Filament\Resources\ShippingProviderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingProvider extends CreateRecord
{
    protected static string $resource = ShippingProviderResource::class;

    public function getTitle(): string
    {
        return 'Створити провайдера доставки';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Провайдера доставки створено';
    }
}
