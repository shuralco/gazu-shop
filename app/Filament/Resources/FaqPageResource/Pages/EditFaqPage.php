<?php

namespace App\Filament\Resources\FaqPageResource\Pages;

use App\Filament\Resources\FaqPageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaqPage extends EditRecord
{
    protected static string $resource = FaqPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
