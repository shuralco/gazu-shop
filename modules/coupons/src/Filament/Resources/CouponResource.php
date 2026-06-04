<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'coupons';

    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $navigationLabel = 'Купони';

    protected static ?string $modelLabel = 'Купон';

    protected static ?string $pluralModelLabel = 'Купони';

    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Код купону')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('Наприклад: SUMMER20, NEWUSER, SALE50'),

                Forms\Components\Select::make('type')
                    ->label('Тип знижки')
                    ->required()
                    ->options([
                        'percentage' => 'Відсоткова знижка (%)',
                        'fixed_amount' => 'Фіксована сума',
                        'free_shipping' => 'Безкоштовна доставка',
                    ])
                    ->reactive(),

                Forms\Components\TextInput::make('value')
                    ->label('Значення')
                    ->required()
                    ->numeric()
                    ->helperText(fn (callable $get) => match ($get('type')) {
                        'percentage' => 'Відсоток знижки (наприклад: 20 для 20%)',
                        'fixed_amount' => 'Сума знижки в '.shopCurrency(),
                        'free_shipping' => 'Залиште 0 для безкоштовної доставки',
                        default => 'Введіть значення знижки'
                    }),
                Forms\Components\TextInput::make('minimum_amount')
                    ->label('Мінімальна сума замовлення')
                    ->numeric()
                    ->step(0.01)
                    ->helperText('Мінімальна сума для застосування купону'),

                Forms\Components\TextInput::make('maximum_discount')
                    ->label('Максимальна знижка')
                    ->numeric()
                    ->step(0.01)
                    ->helperText('Максимальна сума знижки (тільки для відсоткових)'),
                Forms\Components\TextInput::make('usage_limit')
                    ->label('Загальний ліміт використань')
                    ->numeric()
                    ->helperText('Залиште порожнім для безлімітного використання'),

                Forms\Components\TextInput::make('usage_limit_per_user')
                    ->label('Ліміт на користувача')
                    ->numeric()
                    ->helperText('Скільки разів один користувач може використати'),

                Forms\Components\TextInput::make('used_count')
                    ->label('Кількість використань')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('Активний')
                    ->default(true),

                Forms\Components\DateTimePicker::make('valid_from')
                    ->label('Дійсний з')
                    ->required()
                    ->default(now()),

                Forms\Components\DateTimePicker::make('valid_until')
                    ->label('Дійсний до')
                    ->required()
                    ->after('valid_from'),

                Forms\Components\Textarea::make('description')
                    ->label('Опис')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Опис купону для внутрішнього використання'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Код')
                    ->searchable()
                    ->copyable()
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed_amount' => 'warning',
                        'free_shipping' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Відсотки',
                        'fixed_amount' => 'Фіксована сума',
                        'free_shipping' => 'Безкоштовна доставка',
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Значення')
                    ->formatStateUsing(fn (string $state, $record): string => $record->type === 'percentage' ? $state.'%' : formatPrice($state)
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_amount')
                    ->label('Мінімальна сума')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maximum_discount')
                    ->label('Максимальна знижка')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Ліміт використань')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('used_count')
                    ->label('Використано')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit_per_user')
                    ->label('Ліміт на користувача')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активний')
                    ->boolean(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Дійсний з')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Дійсний до')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->size('lg')
                    ->tooltip('Перегляд'),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->size('lg')
                    ->tooltip('Змінити'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->size('lg')
                    ->tooltip('Видалити'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
