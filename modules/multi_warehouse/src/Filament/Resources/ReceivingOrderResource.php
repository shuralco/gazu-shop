<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivingOrderResource\Pages;
use App\Filament\Resources\ReceivingOrderResource\RelationManagers;
use App\Models\ReceivingOrder;
use App\Services\Warehouse\ReceivingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReceivingOrderResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'multi_warehouse';

    protected static ?string $model = ReceivingOrder::class;

    protected static ?string $slug = 'receiving-orders';

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Склад і доставка';

    protected static ?string $navigationLabel = 'Приходування товару';

    protected static ?string $modelLabel = 'Приходування';

    protected static ?string $pluralModelLabel = 'Приходування';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основне')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Код')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('status')
                            ->label('Статус')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Склад приходу')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->disabled(fn ($record) => $record && ! $record->isEditable()),
                        Forms\Components\TextInput::make('supplier_name')->label('Постачальник'),
                        Forms\Components\TextInput::make('invoice_number')->label('Номер накладної'),
                        Forms\Components\DatePicker::make('invoice_date')->label('Дата накладної'),
                    ]),
                    Forms\Components\Textarea::make('note')->label('Примітка')->rows(2)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Код')->sortable()->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('warehouse.code')->label('Склад'),
                Tables\Columns\TextColumn::make('supplier_name')->label('Постачальник')->placeholder('—'),
                Tables\Columns\TextColumn::make('invoice_number')->label('№ накладної')->placeholder('—'),
                Tables\Columns\TextColumn::make('items_count')->label('Позицій')->counts('items'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Чернетка',
                        'received' => 'Прийнято',
                        'cancelled' => 'Скасовано',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('received_at')->label('Прийнято')->dateTime('d.m.Y H:i')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Створено')->dateTime('d.m.Y H:i')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Статус')->options([
                    'draft' => 'Чернетка',
                    'received' => 'Прийнято',
                    'cancelled' => 'Скасовано',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('receive')
                    ->label('Прийняти')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ReceivingOrder $r) => $r->status === ReceivingOrder::STATUS_DRAFT && $r->items()->exists())
                    ->requiresConfirmation()
                    ->action(function (ReceivingOrder $record) {
                        try {
                            app(ReceivingService::class)->receive($record, auth()->id());
                            Notification::make()->title("Приходування {$record->code} прийнято — інвентар оновлено")->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Помилка')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Скасувати')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ReceivingOrder $r) => $r->status === ReceivingOrder::STATUS_DRAFT)
                    ->requiresConfirmation()
                    ->form([Forms\Components\Textarea::make('reason')->label('Причина')->rows(2)])
                    ->action(function (ReceivingOrder $record, array $data) {
                        try {
                            app(ReceivingService::class)->cancel($record, $data['reason'] ?? null);
                            Notification::make()->title('Скасовано')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Помилка')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\EditAction::make()
                    ->visible(fn (ReceivingOrder $r) => $r->isEditable()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Eager-load warehouse — колонка warehouse.code інакше = N+1 щорядка.
        return parent::getEloquentQuery()->with(['warehouse']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceivingOrders::route('/'),
            'create' => Pages\CreateReceivingOrder::route('/create'),
            'edit' => Pages\EditReceivingOrder::route('/{record}/edit'),
        ];
    }
}
