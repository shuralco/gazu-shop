<?php

namespace App\Filament\Resources\FilterLandingResource\Pages;

use App\Filament\Resources\FilterLandingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilterLandings extends ListRecords
{
    protected static string $resource = FilterLandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Створити лендінг'),
        ];
    }
}
