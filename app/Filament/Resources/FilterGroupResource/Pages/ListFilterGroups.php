<?php

namespace App\Filament\Resources\FilterGroupResource\Pages;

use App\Filament\Resources\FilterGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilterGroups extends ListRecords
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
