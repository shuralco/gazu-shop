<?php

namespace App\Filament\Resources\AccessPresetResource\Pages;

use App\Filament\Resources\AccessPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccessPresets extends ListRecords
{
    protected static string $resource = AccessPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
