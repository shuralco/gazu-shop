<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'reviews';

    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Відгуки';

    protected static ?string $modelLabel = 'відгук';

    protected static ?string $pluralModelLabel = 'відгуки';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 60;

    public static function getNavigationBadge(): ?string
    {
        $count = Review::where('status', Review::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Інформація про відгук')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Товар')
                            ->relationship('product', 'title')
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Користувач')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->nullable(),

                                Forms\Components\Select::make('rating')
                                    ->label('Рейтинг')
                                    ->options([
                                        1 => '★ — 1 зірка',
                                        2 => '★★ — 2 зірки',
                                        3 => '★★★ — 3 зірки',
                                        4 => '★★★★ — 4 зірки',
                                        5 => '★★★★★ — 5 зірок',
                                    ])
                                    ->required(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('author_name')
                                    ->label('Ім\'я автора')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('author_email')
                                    ->label('Email автора')
                                    ->email()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Textarea::make('comment')
                            ->label('Коментар')
                            ->required()
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Модерація')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Статус')
                                    ->options([
                                        Review::STATUS_PENDING => 'На модерації',
                                        Review::STATUS_APPROVED => 'Схвалено',
                                        Review::STATUS_REJECTED => 'Відхилено',
                                    ])
                                    ->default(Review::STATUS_PENDING)
                                    ->required(),

                                Forms\Components\Toggle::make('is_verified_purchase')
                                    ->label('Підтверджена покупка')
                                    ->inline(false),
                            ]),

                        Forms\Components\Textarea::make('admin_reply')
                            ->label('Відповідь адміністратора')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Відповідь буде показана під відгуком на сторінці товару')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')
                    ->label('Товар')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn (Review $record): string => $record->product?->title ?? ''),

                Tables\Columns\TextColumn::make('author_name')
                    ->label('Автор')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Рейтинг')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Коментар')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(fn (Review $record): string => $record->comment),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Review::STATUS_PENDING => 'На модерації',
                        Review::STATUS_APPROVED => 'Схвалено',
                        Review::STATUS_REJECTED => 'Відхилено',
                        default => $state,
                    })
                    ->colors([
                        'warning' => Review::STATUS_PENDING,
                        'success' => Review::STATUS_APPROVED,
                        'danger' => Review::STATUS_REJECTED,
                    ])
                    ->icons([
                        'heroicon-o-clock' => Review::STATUS_PENDING,
                        'heroicon-o-check-circle' => Review::STATUS_APPROVED,
                        'heroicon-o-x-circle' => Review::STATUS_REJECTED,
                    ]),

                Tables\Columns\IconColumn::make('is_verified_purchase')
                    ->label('Покупка')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('admin_reply')
                    ->label('Відповідь')
                    ->boolean()
                    ->getStateUsing(fn (Review $record): bool => ! empty($record->admin_reply))
                    ->trueIcon('heroicon-o-chat-bubble-left-right')
                    ->falseIcon('heroicon-o-chat-bubble-left')
                    ->trueColor('info')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        Review::STATUS_PENDING => 'На модерації',
                        Review::STATUS_APPROVED => 'Схвалено',
                        Review::STATUS_REJECTED => 'Відхилено',
                    ]),

                Tables\Filters\SelectFilter::make('rating')
                    ->label('Рейтинг')
                    ->options([
                        1 => '1 зірка',
                        2 => '2 зірки',
                        3 => '3 зірки',
                        4 => '4 зірки',
                        5 => '5 зірок',
                    ]),

                Tables\Filters\TernaryFilter::make('has_reply')
                    ->label('Є відповідь')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('admin_reply')->where('admin_reply', '!=', ''),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('admin_reply')->orWhere('admin_reply', '')),
                    ),

                Tables\Filters\TernaryFilter::make('is_verified_purchase')
                    ->label('Підтверджена покупка'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Схвалити')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record): bool => $record->status !== Review::STATUS_APPROVED)
                    ->action(function (Review $record): void {
                        $record->approve();
                        Notification::make()
                            ->success()
                            ->title('Відгук схвалено')
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Відхилити')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record): bool => $record->status !== Review::STATUS_REJECTED)
                    ->action(function (Review $record): void {
                        $record->reject();
                        Notification::make()
                            ->success()
                            ->title('Відгук відхилено')
                            ->send();
                    }),

                Tables\Actions\Action::make('reply')
                    ->label('Відповісти')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->form([
                        Forms\Components\Textarea::make('admin_reply')
                            ->label('Відповідь адміністратора')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->default(fn (Review $record): ?string => $record->admin_reply),
                    ])
                    ->action(function (Review $record, array $data): void {
                        $record->setAdminReply($data['admin_reply']);
                        Notification::make()
                            ->success()
                            ->title('Відповідь збережено')
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Схвалити обрані')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status !== Review::STATUS_APPROVED) {
                                    $record->approve();
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->success()
                                ->title("Схвалено відгуків: {$count}")
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('reject_selected')
                        ->label('Відхилити обрані')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status !== Review::STATUS_REJECTED) {
                                    $record->reject();
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->success()
                                ->title("Відхилено відгуків: {$count}")
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
