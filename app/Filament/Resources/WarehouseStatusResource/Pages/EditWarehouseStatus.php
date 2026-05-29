<?php

namespace App\Filament\Resources\WarehouseStatusResource\Pages;

use App\Filament\Resources\WarehouseStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseStatus extends EditRecord
{
    protected static string $resource = WarehouseStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
