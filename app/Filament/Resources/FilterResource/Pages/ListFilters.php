<?php

namespace App\Filament\Resources\FilterResource\Pages;

use App\Filament\Resources\FilterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilters extends ListRecords
{
    protected static string $resource = FilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generateFromSpecs')
                ->label('Згенерувати з характеристик товарів')
                ->icon('heroicon-o-sparkles')
                ->color('gray')
                ->modalHeading('Згенерувати фільтри з характеристик товарів')
                ->modalDescription('Пройдемо по всіх товарах і перетворимо їхні характеристики на групи фільтрів та значення. Уже наявні фільтри не видаляються — тільки доповнюються.')
                ->modalSubmitActionLabel('Згенерувати')
                ->requiresConfirmation()
                ->action(function () {
                    \Artisan::call('filters:generate-from-specs');

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Фільтри згенеровано')
                        ->body('Каталог оновиться протягом хвилини.')
                        ->send();
                }),
        ];
    }
}
