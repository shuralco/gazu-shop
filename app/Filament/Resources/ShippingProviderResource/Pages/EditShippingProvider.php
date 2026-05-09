<?php

namespace App\Filament\Resources\ShippingProviderResource\Pages;

use App\Filament\Resources\ShippingProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShippingProvider extends EditRecord
{
    protected static string $resource = ShippingProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Редагувати провайдера доставки';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Провайдера доставки оновлено';
    }
}
