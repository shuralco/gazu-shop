<?php

namespace App\Filament\Resources\ShippingWarehouseResource\Pages;

use App\Filament\Resources\ShippingWarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShippingWarehouse extends EditRecord
{
    protected static string $resource = ShippingWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
