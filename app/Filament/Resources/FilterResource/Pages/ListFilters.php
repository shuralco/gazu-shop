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
                        ->body('Ключі-ідентифікатори (артикул, крос-код) пропущено — фільтр із них марний.')
                        ->send();
                }),
            Actions\Action::make('fromTitles')
                ->label('Витягти характеристики з назв')
                ->icon('heroicon-o-language')
                ->color('gray')
                ->modalHeading('Витягти характеристики з назв товарів')
                ->modalDescription('Розпізнаємо в назвах обʼєм («3.5л»), оригінал/копію та місце встановлення («в салоні», «під капотом») і проставимо їх товарам. Нічого не видаляється, повторний запуск не дублює.')
                ->modalSubmitActionLabel('Витягти')
                ->requiresConfirmation()
                ->action(function () {
                    \Artisan::call('gazu:filters-from-titles');

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Характеристики витягнуто з назв')
                        ->body('Перевірте список нижче — зайве можна вимкнути або видалити.')
                        ->send();
                }),
        ];
    }
}
