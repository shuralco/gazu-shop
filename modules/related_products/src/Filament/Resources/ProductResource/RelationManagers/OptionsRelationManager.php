<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Адмін-вкладка «Опції» на продукті: користувач задає Колір/Розмір/Об'єм
 * та значення для кожного. На фронті це рендериться як класичний
 * вибір (radio-pills / color-swatches / dropdown) і впливає на вибір
 * варіанта (ProductVariant) через зведення option_value_ids → product_variants.
 */
class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    protected static ?string $title = 'Опції товару';

    protected static ?string $modelLabel = 'Опція';

    protected static ?string $pluralModelLabel = 'Опції';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Назва опції')
                ->placeholder('Колір, Розмір, Об\'єм...')
                ->required()
                ->maxLength(80),

            Forms\Components\Select::make('type')
                ->label('Тип відображення на фронті')
                ->options([
                    'text' => 'Кнопки-пігулки (S / M / L)',
                    'color' => 'Кольорові свотчі (з HEX)',
                    'image' => 'Картинки (з мініатюрою)',
                    'select' => 'Dropdown (список)',
                ])
                ->default('text')
                ->required(),

            Forms\Components\Toggle::make('is_active')->label('Активна')->default(true),

            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),

            Forms\Components\Repeater::make('values')
                ->label('Значення опції')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('value')
                        ->label('Значення')
                        ->required()
                        ->maxLength(80),

                    Forms\Components\ColorPicker::make('color_hex')
                        ->label('Колір (HEX)')
                        ->visible(fn ($get, $livewire) => $livewire->mountedTableActionRecord?->type === 'color'
                            || ($livewire->mountedTableActionRecord === null && request()->input('data.type') === 'color')),

                    Forms\Components\FileUpload::make('image')
                        ->label('Картинка')
                        ->image()
                        ->directory('option-images')
                        ->maxSize(2048)
                        ->disk('public'),

                    Forms\Components\TextInput::make('price_modifier')
                        ->label('+/- до ціни (грн)')
                        ->numeric()
                        ->default(0)
                        ->helperText('Додаток до базової ціни товару, може бути від\'ємним'),

                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Toggle::make('is_active')->label('Активне')->default(true),
                ])
                ->orderColumn('sort_order')
                ->reorderable()
                ->collapsible()
                ->itemLabel(fn (array $state) => $state['value'] ?? 'Нове значення')
                ->columns(2)
                ->defaultItems(0)
                ->addActionLabel('Додати значення')
                ->columnSpanFull(),
        ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Опція')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'color' => 'Колір',
                        'image' => 'Картинка',
                        'select' => 'Список',
                        default => 'Пігулки',
                    }),
                Tables\Columns\TextColumn::make('values_count')
                    ->label('Значень')
                    ->counts('values')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Порядок')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Створити опцію'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
