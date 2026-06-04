<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryTransferResource\Pages;
use App\Filament\Resources\InventoryTransferResource\RelationManagers;
use App\Models\InventoryTransfer;
use App\Services\Warehouse\TransferService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryTransferResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'multi_warehouse';

    protected static ?string $model = InventoryTransfer::class;

    protected static ?string $slug = 'inventory-transfers';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';

    protected static ?string $navigationGroup = 'Склад і доставка';

    protected static ?string $navigationLabel = 'Переміщення між складами';

    protected static ?string $modelLabel = 'Переміщення';

    protected static ?string $pluralModelLabel = 'Переміщення між складами';

    protected static ?int $navigationSort = 30;

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
                        Forms\Components\Select::make('from_warehouse_id')
                            ->label('Зі складу')
                            ->relationship('fromWarehouse', 'name')
                            ->required()
                            ->disabled(fn ($record) => $record && ! $record->isEditable()),
                        Forms\Components\Select::make('to_warehouse_id')
                            ->label('На склад')
                            ->relationship('toWarehouse', 'name')
                            ->required()
                            ->disabled(fn ($record) => $record && ! $record->isEditable()),
                        Forms\Components\TextInput::make('tracking_number')->label('Номер ТТН (опц.)'),
                        Forms\Components\TextInput::make('carrier')->label('Перевізник (опц.)'),
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
                Tables\Columns\TextColumn::make('fromWarehouse.code')->label('З'),
                Tables\Columns\TextColumn::make('toWarehouse.code')->label('На'),
                Tables\Columns\TextColumn::make('items_count')->label('Позицій')->counts('items'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Чернетка',
                        'sent' => 'Відправлено',
                        'received' => 'Отримано',
                        'cancelled' => 'Скасовано',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('shipped_at')->label('Відправлено')->dateTime('d.m.Y H:i')->placeholder('—'),
                Tables\Columns\TextColumn::make('received_at')->label('Отримано')->dateTime('d.m.Y H:i')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Створено')->dateTime('d.m.Y H:i')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Статус')->options([
                    'draft' => 'Чернетка',
                    'sent' => 'Відправлено',
                    'received' => 'Отримано',
                    'cancelled' => 'Скасовано',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('ship')
                    ->label('Відправити')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (InventoryTransfer $r) => $r->status === InventoryTransfer::STATUS_DRAFT && $r->items()->exists())
                    ->requiresConfirmation()
                    ->action(function (InventoryTransfer $record) {
                        try {
                            app(TransferService::class)->ship($record, auth()->id());
                            Notification::make()->title("Переміщення {$record->code} відправлено")->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Помилка')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('receive')
                    ->label('Прийняти')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (InventoryTransfer $r) => $r->status === InventoryTransfer::STATUS_SENT)
                    ->requiresConfirmation()
                    ->action(function (InventoryTransfer $record) {
                        try {
                            app(TransferService::class)->receive($record, auth()->id());
                            Notification::make()->title("Переміщення {$record->code} прийнято")->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Помилка')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Скасувати')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (InventoryTransfer $r) => in_array($r->status, ['draft', 'sent']))
                    ->requiresConfirmation()
                    ->form([Forms\Components\Textarea::make('reason')->label('Причина')->rows(2)])
                    ->action(function (InventoryTransfer $record, array $data) {
                        try {
                            app(TransferService::class)->cancel($record, auth()->id(), $data['reason'] ?? null);
                            Notification::make()->title('Скасовано')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Помилка')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\EditAction::make()
                    ->visible(fn (InventoryTransfer $r) => $r->isEditable()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryTransfers::route('/'),
            'create' => Pages\CreateInventoryTransfer::route('/create'),
            'edit' => Pages\EditInventoryTransfer::route('/{record}/edit'),
        ];
    }
}
