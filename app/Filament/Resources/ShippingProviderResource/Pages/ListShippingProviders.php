<?php

namespace App\Filament\Resources\ShippingProviderResource\Pages;

use App\Filament\Resources\ShippingProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShippingProviders extends ListRecords
{
    protected static string $resource = ShippingProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Провайдери доставки';
    }
}
