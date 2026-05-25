<?php

namespace App\Filament\Resources\NpScanSheetResource\Pages;

use App\Filament\Resources\NpScanSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNpScanSheet extends EditRecord
{
    protected static string $resource = NpScanSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
