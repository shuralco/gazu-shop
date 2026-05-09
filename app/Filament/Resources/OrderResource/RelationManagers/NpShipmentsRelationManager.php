<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\NpShipmentResource;
use App\Models\DisplaySetting;
use App\Models\NpShipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NpShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'npShipments';

    protected static ?string $title = 'ТТН Нова Пошта';

    protected static ?string $modelLabel = 'ТТН';

    protected static ?string $pluralModelLabel = 'ТТН';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ttn')
                    ->label('Номер ТТН')
                    ->disabled(),

                Forms\Components\TextInput::make('status')
                    ->label('Статус')
                    ->disabled(),

                Forms\Components\TextInput::make('np_status')
                    ->label('Статус НП')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ttn')
            ->columns([
                Tables\Columns\TextColumn::make('ttn')
                    ->label('ТТН')
                    ->copyable()
                    ->copyMessage('ТТН скопійовано')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        NpShipment::STATUS_NEW => 'gray',
                        NpShipment::STATUS_CREATED => 'info',
                        NpShipment::STATUS_SENT => 'warning',
                        NpShipment::STATUS_DELIVERED => 'success',
                        NpShipment::STATUS_RETURNED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        NpShipment::STATUS_NEW => 'Нова',
                        NpShipment::STATUS_CREATED => 'Створена',
                        NpShipment::STATUS_SENT => 'В дорозі',
                        NpShipment::STATUS_DELIVERED => 'Доставлена',
                        NpShipment::STATUS_RETURNED => 'Повернена',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('recipient_city_name')
                    ->label('Місто'),

                Tables\Columns\TextColumn::make('np_status')
                    ->label('Статус НП')
                    ->limit(30),

                Tables\Columns\TextColumn::make('shipping_cost')
                    ->label('Вартість')
                    ->money('UAH'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create_ttn')
                    ->label('Створити ТТН')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->url(fn () => NpShipmentResource::getUrl('create', [
                        'order_id' => $this->getOwnerRecord()->id,
                    ])),
            ])
            ->actions([
                Tables\Actions\Action::make('track')
                    ->label('Відстежити')
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->action(function (NpShipment $record) {
                        NpShipmentResource::trackShipment($record);
                    })
                    ->visible(fn (NpShipment $record) => $record->ttn && $record->needsTracking()),

                Tables\Actions\Action::make('print')
                    ->label('Друк')
                    ->icon('heroicon-o-printer')
                    ->url(function (NpShipment $record) {
                        if (! $record->ref) return null;
                        $apiKey = DisplaySetting::get('np_api_key', '');

                        return "https://my.novaposhta.ua/orders/printDocument/orders[]/{$record->ref}/type/pdf/apiKey/{$apiKey}";
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (NpShipment $record) => ! empty($record->ref)),

                Tables\Actions\Action::make('edit')
                    ->label('Редагувати')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (NpShipment $record) => NpShipmentResource::getUrl('edit', ['record' => $record->id])),
            ]);
    }
}
