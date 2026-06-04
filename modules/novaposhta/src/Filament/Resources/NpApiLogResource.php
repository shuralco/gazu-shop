<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NpApiLogResource\Pages;
use App\Filament\Resources\NpShipmentResource;
use App\Models\NpApiLog;
use App\Models\ShippingApiLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NpApiLogResource extends Resource
{
    protected static ?string $model = ShippingApiLog::class;

    protected static ?string $slug = 'shipping-api-logs';

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?string $navigationGroup = 'Склад і доставка';

    protected static ?string $navigationLabel = 'Нова Пошта: API-логи';

    protected static ?string $modelLabel = 'API лог';

    protected static ?string $pluralModelLabel = 'API логи';

    protected static ?int $navigationSort = 140;

    public static function getNavigationBadge(): ?string
    {
        $count = ShippingApiLog::where('success', false)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Метадані запиту')
                ->schema([
                    Forms\Components\TextInput::make('endpoint_model')->label('Model')->disabled(),
                    Forms\Components\TextInput::make('endpoint_method')->label('Method')->disabled(),
                    Forms\Components\Toggle::make('success')->label('Успіх')->disabled(),
                    Forms\Components\TextInput::make('http_status')->label('HTTP Status')->disabled(),
                    Forms\Components\TextInput::make('duration_ms')
                        ->label('Тривалість')
                        ->disabled()
                        ->formatStateUsing(fn ($state) => $state !== null ? "{$state} ms" : '—'),
                    Forms\Components\TextInput::make('caller')->label('Caller')->disabled()->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Помилки / попередження')
                ->schema([
                    Forms\Components\Textarea::make('errors')
                        ->label('Errors')
                        ->disabled()
                        ->rows(3)
                        ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : ($state ?? ''))
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('warnings')
                        ->label('Warnings')
                        ->disabled()
                        ->rows(2)
                        ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : ($state ?? ''))
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => $record && (! empty($record->errors) || ! empty($record->warnings)))
                ->collapsible(),

            Forms\Components\Section::make('Request payload')
                ->schema([
                    Forms\Components\Textarea::make('request_payload')
                        ->label('')
                        ->disabled()
                        ->rows(15)
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($state ?? ''))
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            Forms\Components\Section::make('Response payload')
                ->schema([
                    Forms\Components\Textarea::make('response_payload')
                        ->label('')
                        ->disabled()
                        ->rows(15)
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($state ?? ''))
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Час')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('provider')
                    ->label('Провайдер')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'novaposhta' => 'info',
                        'ukrposhta' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'novaposhta' => 'НП',
                        'ukrposhta' => 'УП',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('endpoint')
                    ->label('Ендпоінт')
                    ->searchable(['endpoint_model', 'endpoint_method'])
                    ->sortable(['endpoint_model']),
                Tables\Columns\IconColumn::make('success')
                    ->label('Успіх')
                    ->boolean(),
                Tables\Columns\TextColumn::make('http_status')
                    ->label('HTTP')
                    ->badge()
                    ->color(fn ($state) => $state >= 200 && $state < 300 ? 'success' : ($state ? 'danger' : 'gray')),
                Tables\Columns\TextColumn::make('duration_label')
                    ->label('Тривалість'),
                Tables\Columns\TextColumn::make('caller')
                    ->label('Caller')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->caller),
                Tables\Columns\TextColumn::make('related_shipment')
                    ->label('Shipment')
                    ->state(function (ShippingApiLog $record) {
                        $sh = $record->getRelatedShipment();
                        if (! $sh) {
                            return null;
                        }

                        return $sh->ttn ? "TTN {$sh->ttn}" : "#{$sh->id} (без TTN)";
                    })
                    ->url(function (ShippingApiLog $record) {
                        $sh = $record->getRelatedShipment();

                        return $sh ? NpShipmentResource::getUrl('edit', ['record' => $sh->id]) : null;
                    })
                    ->color('primary')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('errors')
                    ->label('Помилки')
                    ->limit(60)
                    ->tooltip(fn ($record) => is_array($record->errors) ? implode('; ', $record->errors) : '')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode('; ', $state) : ($state ?? '')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->label('Провайдер')
                    ->multiple()
                    ->options([
                        'novaposhta' => 'Нова Пошта',
                        'ukrposhta' => 'УкрПошта',
                    ]),
                Tables\Filters\TernaryFilter::make('success')
                    ->label('Тільки помилки')
                    ->queries(
                        true: fn ($query) => $query,
                        false: fn ($query) => $query->where('success', false),
                        blank: fn ($query) => $query,
                    ),
                Tables\Filters\SelectFilter::make('endpoint_model')
                    ->label('Model')
                    ->options(fn () => ShippingApiLog::query()
                        ->select('endpoint_model')
                        ->distinct()
                        ->orderBy('endpoint_model')
                        ->pluck('endpoint_model', 'endpoint_model')
                        ->toArray()),
                Tables\Filters\Filter::make('last_24h')
                    ->label('Останні 24 години')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subDay())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Деталі'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('clear_old')
                    ->label('Очистити старі (>7 днів)')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function () {
                        $deleted = ShippingApiLog::where('created_at', '<', now()->subDays(7))->delete();
                        \Filament\Notifications\Notification::make()
                            ->title("Видалено {$deleted} записів")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('clear_all')
                    ->label('Очистити все')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Видалити ВСІ логи? Цю дію не можна скасувати.')
                    ->action(function () {
                        $deleted = ShippingApiLog::query()->delete();
                        \Filament\Notifications\Notification::make()
                            ->title("Видалено {$deleted} записів")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNpApiLogs::route('/'),
            'view' => Pages\ViewNpApiLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
