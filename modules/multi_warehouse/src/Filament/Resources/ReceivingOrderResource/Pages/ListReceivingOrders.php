<?php

namespace App\Filament\Resources\ReceivingOrderResource\Pages;

use App\Filament\Resources\ReceivingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReceivingOrders extends ListRecords
{
    protected static string $resource = ReceivingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Нове приходування'),
        ];
    }
}
