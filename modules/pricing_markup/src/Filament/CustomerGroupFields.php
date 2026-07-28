<?php

namespace Modules\PricingMarkup\Filament;

use Filament\Forms;
use Filament\Tables;

/**
 * Поля націнки для картки групи клієнтів.
 *
 * Живуть у ЦЬОМУ модулі, а не в `wholesale`, щоб модуль ставився в будь-який
 * магазин на движку без правки чужих файлів. Підключаються через точки
 * розширення `wholesale.customer_group.form` / `.columns`
 * (див. PricingMarkupServiceProvider).
 */
class CustomerGroupFields
{
    /** Базова ціна для прикладу біля поля відсотка. */
    private const SAMPLE_BASE = 1000;

    /** @return array<int, \Filament\Forms\Components\Component> */
    public static function formSchema(): array
    {
        return [
            Forms\Components\Section::make('Ціноутворення: націнка')
                ->description('Ціна в картці товару — базова. Покупець цієї групи бачить базову ціну плюс відсоток нижче.')
                ->schema([
                    Forms\Components\TextInput::make('markup_percentage')
                        ->label('Відсоток націнки')
                        ->helperText('0 — ціна дорівнює базовій. Відʼємний відсоток робить ціну нижчою за базову (гурт, партнери).')
                        ->numeric()
                        ->default(0)
                        ->minValue(-100)
                        ->maxValue(1000)
                        ->step(0.01)
                        ->suffix('%')
                        ->live(onBlur: true),
                    Forms\Components\Placeholder::make('markup_preview')
                        ->label('Приклад')
                        ->content(function (Forms\Get $get): string {
                            $percent = (float) ($get('markup_percentage') ?? 0);
                            $price = round(max(0, self::SAMPLE_BASE * (1 + $percent / 100)), 2);

                            return 'Товар із базовою ціною '.number_format(self::SAMPLE_BASE, 0, ',', ' ')
                                .' ₴ покупець цієї групи побачить за '
                                .number_format($price, 2, ',', ' ').' ₴';
                        }),
                ])
                ->columns(2),
        ];
    }

    /** @return array<int, \Filament\Tables\Columns\Column> */
    public static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('markup_percentage')
                ->label('Націнка')
                ->suffix('%')
                ->badge()
                ->color(fn ($state) => (float) $state > 0 ? 'success' : ((float) $state < 0 ? 'warning' : 'gray'))
                ->sortable(),
        ];
    }
}
