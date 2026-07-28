<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerGroupResource\Pages;
use App\Filament\Resources\CustomerGroupResource\RelationManagers;
use App\Models\CustomerGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerGroupResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'wholesale';

    protected static ?string $model = CustomerGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Продажі';
    protected static ?string $navigationLabel = 'Групи клієнтів';

    protected static ?string $modelLabel = 'Група клієнтів';

    protected static ?string $pluralModelLabel = 'Групи клієнтів';

    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва (системна)')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('display_name')
                            ->label('Назва для відображення')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('markup_percentage')
                            ->label('Відсоток націнки')
                            ->helperText('Ціна для покупця = базова ціна товару + цей відсоток. 0 — ціна дорівнює базовій, відʼємний — дешевше за базову (гурт, партнери).')
                            ->numeric()
                            ->default(0)
                            ->minValue(-100)
                            ->maxValue(1000)
                            ->step(0.01)
                            ->suffix('%')
                            ->live(onBlur: true),
                        Forms\Components\Placeholder::make('markup_preview')
                            ->label('Приклад')
                            ->content(function (Forms\Get $get): string {
                                $percent = (float) ($get('markup_percentage') ?? 0);
                                $price = round(max(0, 1000 * (1 + $percent / 100)), 2);

                                return 'Товар із базовою ціною 1 000 ₴ покупець цієї групи побачить за '
                                    .number_format($price, 2, ',', ' ').' ₴';
                            }),
                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('Відсоток знижки')
                            ->helperText('Застосовується ПОВЕРХ націненої ціни (акційні групи). Зазвичай 0.')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('min_order_amount')
                            ->label('Мінімальна сума замовлення')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₴'),
                        Forms\Components\Textarea::make('payment_terms')
                            ->label('Умови оплати')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Стандартна група')
                            ->helperText('Її отримують неавторизовані покупці й клієнти без призначеної групи. Стандартна може бути лише одна — щойно позначите цю, з попередньої позначка зніметься сама.'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортування')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Назва')
                    ->searchable(),
                Tables\Columns\TextColumn::make('markup_percentage')
                    ->label('Націнка')
                    ->suffix('%')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Знижка')
                    ->suffix('%')
                    ->badge(),
                Tables\Columns\TextColumn::make('min_order_amount')
                    ->label('Мін. сума замовлення')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2, '.', ' ').' грн' : '-'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Кількість користувачів')
                    ->counts('users'),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Стандартна')
                    ->boolean()
                    ->tooltip('Ціна для неавторизованих покупців'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->reorderable('sort_order')
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
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerGroups::route('/'),
            'create' => Pages\CreateCustomerGroup::route('/create'),
            'edit' => Pages\EditCustomerGroup::route('/{record}/edit'),
        ];
    }
}
