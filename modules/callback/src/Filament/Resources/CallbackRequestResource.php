<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallbackRequestResource\Pages;
use App\Models\CallbackRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CallbackRequestResource extends Resource
{
    protected static ?string $model = CallbackRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone-arrow-down-left';

    protected static ?string $navigationGroup = 'Продажі';

    protected static ?string $navigationLabel = 'Заявки на дзвінок';

    protected static ?string $modelLabel = 'Заявка на дзвінок';

    protected static ?string $pluralModelLabel = 'Заявки на дзвінок';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = CallbackRequest::where('status', CallbackRequest::STATUS_NEW)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return CallbackRequest::where('status', CallbackRequest::STATUS_NEW)->exists() ? 'danger' : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Контакт')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Ім\'я')
                    ->maxLength(100),
                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->required()
                    ->tel()
                    ->maxLength(32),
            ])->columns(2),

            Forms\Components\Section::make('Статус та нотатки')->schema([
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options(CallbackRequest::STATUSES)
                    ->required()
                    ->default(CallbackRequest::STATUS_NEW),
                Forms\Components\TextInput::make('source')
                    ->label('Джерело')
                    ->helperText('footer / product_page / hero')
                    ->maxLength(32),
                Forms\Components\Textarea::make('notes')
                    ->label('Нотатка менеджера')
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Технічні дані')->schema([
                Forms\Components\TextInput::make('referrer_url')
                    ->label('Сторінка-джерело')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ip_address')->label('IP')->disabled(),
                Forms\Components\TextInput::make('user_agent')->label('User-Agent')->disabled(),
                Forms\Components\Placeholder::make('created_at')
                    ->label('Створено')
                    ->content(fn ($record) => $record?->created_at?->format('d.m.Y H:i:s')),
            ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Коли')
                    ->dateTime('d.m H:i')
                    ->description(fn ($record) => $record->created_at?->diffForHumans())
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->weight('bold')
                    ->icon('heroicon-o-phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Ім\'я')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Джерело')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => CallbackRequest::statusLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        CallbackRequest::STATUS_NEW         => 'danger',
                        CallbackRequest::STATUS_IN_PROGRESS => 'warning',
                        CallbackRequest::STATUS_DONE        => 'success',
                        CallbackRequest::STATUS_SPAM        => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Нотатка')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(CallbackRequest::STATUSES)
                    ->default(null),
                Tables\Filters\SelectFilter::make('source')
                    ->label('Джерело')
                    ->options(fn () => CallbackRequest::query()->distinct('source')->pluck('source', 'source')->all()),
            ])
            ->actions([
                Tables\Actions\Action::make('inProgress')
                    ->label('В роботу')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === CallbackRequest::STATUS_NEW)
                    ->action(function ($record) {
                        $record->update(['status' => CallbackRequest::STATUS_IN_PROGRESS]);
                        Notification::make()->title('В роботі')->success()->send();
                    }),
                Tables\Actions\Action::make('done')
                    ->label('Готово')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, [CallbackRequest::STATUS_NEW, CallbackRequest::STATUS_IN_PROGRESS]))
                    ->action(function ($record) {
                        $record->update(['status' => CallbackRequest::STATUS_DONE]);
                        Notification::make()->title('Оброблено')->success()->send();
                    }),
                Tables\Actions\Action::make('spam')
                    ->label('Спам')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->visible(fn ($record) => $record->status !== CallbackRequest::STATUS_SPAM)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => CallbackRequest::STATUS_SPAM]);
                        Notification::make()->title('Позначено як спам')->success()->send();
                    }),
                Tables\Actions\EditAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCallbackRequests::route('/'),
            'edit' => Pages\EditCallbackRequest::route('/{record}/edit'),
        ];
    }
}
