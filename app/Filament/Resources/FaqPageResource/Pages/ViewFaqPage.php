<?php

namespace App\Filament\Resources\FaqPageResource\Pages;

use App\Filament\Resources\FaqPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFaqPage extends ViewRecord
{
    protected static string $resource = FaqPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
