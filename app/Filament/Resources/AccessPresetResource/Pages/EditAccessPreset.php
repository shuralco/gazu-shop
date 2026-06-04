<?php

namespace App\Filament\Resources\AccessPresetResource\Pages;

use App\Filament\Resources\AccessPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccessPreset extends EditRecord
{
    protected static string $resource = AccessPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->hidden(fn () => $this->record->is_system),
        ];
    }
}
