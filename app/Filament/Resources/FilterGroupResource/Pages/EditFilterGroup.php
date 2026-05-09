<?php

namespace App\Filament\Resources\FilterGroupResource\Pages;

use App\Filament\Resources\FilterGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilterGroup extends EditRecord
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
