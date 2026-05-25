<?php

namespace App\Filament\Resources\MerchantWarehouseResource\Pages;

use App\Filament\Resources\MerchantWarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMerchantWarehouses extends ListRecords
{
    protected static string $resource = MerchantWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Створити склад'),
        ];
    }
}
