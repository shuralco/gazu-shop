<?php

namespace App\Filament\Resources\ShippingProviderResource\Pages;

use App\Filament\Resources\ShippingProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewShippingProvider extends ViewRecord
{
    protected static string $resource = ShippingProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Перегляд провайдера доставки';
    }
}
