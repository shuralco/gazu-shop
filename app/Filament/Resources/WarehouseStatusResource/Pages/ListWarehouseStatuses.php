<?php

namespace App\Filament\Resources\WarehouseStatusResource\Pages;

use App\Filament\Resources\WarehouseStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseStatuses extends ListRecords
{
    protected static string $resource = WarehouseStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
