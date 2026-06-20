<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Inventory;
use App\Models\MerchantWarehouse;
use App\Services\Warehouse\InventoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventory';

    protected static ?string $title = 'Інвентар по складах';

    protected static ?string $modelLabel = 'Запис інвентарю';

    protected static ?string $pluralModelLabel = 'Запаси';

    /** Ціна + валюта рядка + (≈ грн за курсом /admin/currencies). */
    public static function fmtPrice($state, $record): ?string
    {
        if ($state === null || $state === '') {
            return null;
        }
        $cur = $record->price_currency ?: 'UAH';
        $out = number_format((float) $state, 2, '.', ' ').' '.$cur;
        if ($cur !== 'UAH') {
            $out .= ' (≈ '.number_format(\App\Models\Currency::toBase($state, $cur), 0, '.', ' ').' ₴)';
        }

        return $out;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('warehouse_id')
                ->label('Склад')
                ->relationship('warehouse', 'name')
                ->required()
                ->disabledOn('edit'),
            Forms\Components\TextInput::make('quantity')
                ->label('Фізично є')
                ->numeric()
                ->minValue(0)
                ->required()
                ->default(0),
            Forms\Components\TextInput::make('reserved_quantity')
                ->label('Заброньовано')
                ->numeric()
                ->minValue(0)
                ->required()
                ->default(0),
            Forms\Components\Select::make('price_currency')
                ->label('Валюта ціни складу')
                ->options(fn () => \App\Models\Currency::selectOptions())
                ->default(fn () => \App\Models\Currency::baseCode() ?: 'UAH')
                ->selectablePlaceholder(false)
                ->native(false),
            Forms\Components\TextInput::make('price')
                ->label('Ціна на цьому складі')
                ->helperText('Якщо порожньо — використовується базова ціна товару. На сайті показується в грн за курсом.')
                ->numeric()
                ->minValue(0)
                ->step(0.01),
            Forms\Components\TextInput::make('compare_at_price')
                ->label('Стара ціна / акційне порівняння')
                ->helperText('Показується закресленою біля поточної (у валюті ціни складу)')
                ->numeric()
                ->minValue(0)
                ->step(0.01),
            Forms\Components\TextInput::make('reorder_point')
                ->label('Поріг для алерту')
                ->numeric()
                ->minValue(0),
            Forms\Components\TextInput::make('reorder_quantity')
                ->label('Скільки замовляти')
                ->numeric()
                ->minValue(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('warehouse_id')
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.code')->label('Код')->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')->label('Склад')->sortable(),
                Tables\Columns\TextColumn::make('warehouse.city')->label('Місто')->placeholder('—'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Фізично')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('reserved_quantity')
                    ->label('Заброн.')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('available_quantity')
                    ->label('Вільно')
                    ->state(fn (Inventory $r) => $r->available_quantity)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(fn ($state, $record) => self::fmtPrice($state, $record))
                    ->placeholder('базова')
                    ->sortable(),
                Tables\Columns\TextColumn::make('compare_at_price')
                    ->label('Стара ціна')
                    ->formatStateUsing(fn ($state, $record) => self::fmtPrice($state, $record))
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reorder_point')
                    ->label('Поріг')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_counted_at')
                    ->label('Останній підрахунок')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати склад')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['product_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('adjust')
                    ->label('Інвентаризація')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('new_quantity')
                            ->label('Нова кількість (фізично)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Причина / нотатка')
                            ->rows(2),
                    ])
                    ->action(function (array $data, Inventory $record): void {
                        $product = $record->product;
                        $warehouse = $record->warehouse;

                        try {
                            app(InventoryService::class)->adjust(
                                $product,
                                $warehouse,
                                (int) $data['new_quantity'],
                                userId: auth()->id(),
                                reason: $data['reason'] ?? null,
                            );

                            Notification::make()
                                ->title('Інвентаризацію проведено')
                                ->body("Склад {$warehouse->code}: товар «{$product->title}» → {$data['new_quantity']} шт.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Помилка інвентаризації')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
