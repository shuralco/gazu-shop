<?php

namespace App\Filament\Resources\UserCarResource\Pages;

use App\Filament\Resources\UserCarResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUserCars extends ManageRecords
{
    protected static string $resource = UserCarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
