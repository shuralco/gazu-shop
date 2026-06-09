<?php

namespace App\Filament\Resources\StockStatusResource\Pages;

use App\Filament\Resources\StockStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockStatuses extends ListRecords
{
    protected static string $resource = StockStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
