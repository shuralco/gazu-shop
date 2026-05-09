<?php

namespace App\Filament\Resources\UpShipmentResource\Pages;

use App\Filament\Resources\UpShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUpShipments extends ListRecords
{
    protected static string $resource = UpShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Створити запис')];
    }
}
