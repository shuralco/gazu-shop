<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\LoyaltyTransaction;
use App\Services\LoyaltyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'loyaltyTransactions';

    protected static ?string $title = 'Транзакції балів';

    protected static ?string $modelLabel = 'транзакція';

    protected static ?string $pluralModelLabel = 'транзакції';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('adjustPoints')
                    ->label('Коригувати бали')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
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
                        $user = $this->getOwnerRecord();
                        $loyaltyService = app(LoyaltyService::class);
                        $loyaltyService->adjustPoints($user, (int) $data['points'], $data['description']);

                        Notification::make()
                            ->success()
                            ->title('Бали скориговано')
                            ->body("Скориговано {$data['points']} балів.")
                            ->send();
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
