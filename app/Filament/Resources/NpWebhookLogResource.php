<?php

namespace App\Filament\Resources;

use App\Models\NpWebhookLog;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NpWebhookLogResource extends Resource
{
    protected static ?string $model = NpWebhookLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Доставка та оплата';
    protected static ?string $navigationLabel = 'Webhook логи (НП)';
    protected static ?string $modelLabel = 'Webhook лог';
    protected static ?string $pluralModelLabel = 'Webhook логи';
    protected static ?int $navigationSort = 30;

    public static function getNavigationBadge(): ?string
    {
        $count = NpWebhookLog::where('processed', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return NpWebhookLog::where('signature_valid', false)->exists() ? 'danger' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Метадані')
                ->schema([
                    Forms\Components\TextInput::make('ttn')->disabled(),
                    Forms\Components\TextInput::make('status_code')->disabled(),
                    Forms\Components\TextInput::make('status')->disabled(),
                    Forms\Components\TextInput::make('ip')->disabled(),
                    Forms\Components\Toggle::make('signature_valid')->disabled(),
                    Forms\Components\Toggle::make('processed')->disabled(),
                    Forms\Components\Textarea::make('error')->disabled()->columnSpanFull(),
                ])
                ->columns(2),
            Forms\Components\Section::make('Payload')
                ->schema([
                    Forms\Components\KeyValue::make('payload')->disabled()->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Час')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ttn')->label('ТТН')->copyable()->searchable(),
                Tables\Columns\BadgeColumn::make('status_code')->label('Code')
                    ->colors([
                        'gray' => fn ($state) => in_array($state, ['1', '2']),
                        'warning' => fn ($state) => in_array($state, ['4', '5', '6', '7', '8']),
                        'success' => fn ($state) => in_array($state, ['9', '10', '11']),
                        'danger' => fn ($state) => in_array($state, ['14', '102', '103', '108']),
                    ]),
                Tables\Columns\TextColumn::make('status')->label('Статус')->wrap()->limit(50),
                Tables\Columns\IconColumn::make('signature_valid')
                    ->label('Підпис')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('processed')
                    ->label('Обр.')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('error')->label('Помилка')->wrap()->limit(40)->color('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('signature_valid')->label('Підпис'),
                Tables\Filters\TernaryFilter::make('processed')->label('Оброблено'),
                Tables\Filters\Filter::make('with_error')
                    ->label('Тільки з помилкою')
                    ->query(fn ($q) => $q->whereNotNull('error')),
                Tables\Filters\Filter::make('today')
                    ->label('Сьогодні')
                    ->query(fn ($q) => $q->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('clear_old')
                    ->label('Видалити старі (>30 днів)')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        $deleted = NpWebhookLog::where('created_at', '<', now()->subDays(30))->delete();
                        \Filament\Notifications\Notification::make()
                            ->title("Видалено {$deleted} старих логів")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\NpWebhookLogResource\Pages\ListNpWebhookLogs::route('/'),
            'view' => \App\Filament\Resources\NpWebhookLogResource\Pages\ViewNpWebhookLog::route('/{record}'),
        ];
    }
}
