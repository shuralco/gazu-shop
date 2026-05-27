<?php

namespace App\Filament\Resources\FilterLandingResource\Pages;

use App\Filament\Resources\FilterLandingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilterLanding extends EditRecord
{
    protected static string $resource = FilterLandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view')
                ->label('Подивитись на сайті')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => url('/lp/'.$this->record->slug), shouldOpenInNewTab: true),
            Actions\DeleteAction::make(),
        ];
    }
}
