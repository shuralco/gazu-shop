<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyTransactionResource\Pages;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use App\Services\LoyaltyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyTransactionResource extends Resource
{
    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'loyalty';

    protected static ?string $model = LoyaltyTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Продажі';
    protected static ?string $navigationLabel = 'Транзакції балів';

    protected static ?string $modelLabel = 'Транзакція балів';

    protected static ?string $pluralModelLabel = 'Транзакції балів';

    protected static ?int $navigationSort = 50;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Користувач')
                    ->searchable()
                    ->url(fn ($record) => $record->user ? UserResource::getUrl('edit', ['record' => $record->user_id]) : null),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        LoyaltyTransaction::TYPE_EARNED => 'success',
                        LoyaltyTransaction::TYPE_SPENT => 'warning',
                        LoyaltyTransaction::TYPE_EXPIRED => 'danger',
                        LoyaltyTransaction::TYPE_ADJUSTED => 'info',
                        LoyaltyTransaction::TYPE_BIRTHDAY => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        LoyaltyTransaction::TYPE_EARNED => 'Нараховано',
                        LoyaltyTransaction::TYPE_SPENT => 'Списано',
                        LoyaltyTransaction::TYPE_EXPIRED => 'Прострочено',
                        LoyaltyTransaction::TYPE_ADJUSTED => 'Коригування',
                        LoyaltyTransaction::TYPE_BIRTHDAY => 'День народження',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('points')
                    ->label('Бали')
                    ->numeric()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Баланс після')
                    ->numeric(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Опис')
                    ->limit(50),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Замовлення')
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : '-')
                    ->url(fn ($record) => $record->order_id ? OrderResource::getUrl('edit', ['record' => $record->order_id]) : null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        LoyaltyTransaction::TYPE_EARNED => 'Нараховано',
                        LoyaltyTransaction::TYPE_SPENT => 'Списано',
                        LoyaltyTransaction::TYPE_EXPIRED => 'Прострочено',
                        LoyaltyTransaction::TYPE_ADJUSTED => 'Коригування',
                        LoyaltyTransaction::TYPE_BIRTHDAY => 'День народження',
                    ]),
                Tables\Filters\SelectFilter::make('user')
                    ->label('Користувач')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->label('Дата')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Від'),
                        Forms\Components\DatePicker::make('until')
                            ->label('До'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('adjustPoints')
                    ->label('Коригувати бали')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('Користувач')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('points')
                            ->label('Кількість балів')
                            ->numeric()
                            ->required()
                            ->helperText('Може бути від\'ємним для списання'),
                        Forms\Components\Textarea::make('description')
                            ->label('Опис')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data): void {
                        $user = User::findOrFail($data['user_id']);
                        $loyaltyService = app(LoyaltyService::class);
                        $loyaltyService->adjustPoints($user, (int) $data['points'], $data['description']);

                        Notification::make()
                            ->success()
                            ->title('Бали скориговано')
                            ->body("Користувачу {$user->name} скориговано {$data['points']} балів.")
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->size('lg')
                    ->tooltip('Перегляд'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyTransactions::route('/'),
        ];
    }
}
