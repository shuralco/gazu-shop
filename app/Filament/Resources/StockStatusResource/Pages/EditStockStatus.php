<?php

namespace App\Filament\Resources\StockStatusResource\Pages;

use App\Filament\Resources\StockStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockStatus extends EditRecord
{
    protected static string $resource = StockStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
