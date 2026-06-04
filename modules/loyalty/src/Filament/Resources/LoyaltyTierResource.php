<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyTierResource\Pages;
use App\Models\LoyaltyTier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyTierResource extends Resource
{
    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'loyalty';

    protected static ?string $model = LoyaltyTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Продажі';
    protected static ?string $navigationLabel = 'Рівні лояльності';

    protected static ?string $modelLabel = 'Рівень лояльності';

    protected static ?string $pluralModelLabel = 'Рівні лояльності';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Рівень')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва (системна)')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('display_name')
                            ->label('Назва для відображення')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('min_points')
                            ->label('Мінімум балів')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('points_multiplier')
                            ->label('Множник балів')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->step(0.1),
                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('Відсоток знижки')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Колір'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортування')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активний')
                            ->default(true),
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
                    ->badge()
                    ->color(fn ($record) => $record->color ? null : 'primary')
                    ->extraAttributes(fn ($record) => $record->color ? ['style' => "background-color: {$record->color}; color: #fff;"] : []),
                Tables\Columns\TextColumn::make('min_points')
                    ->label('Мінімум балів')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('points_multiplier')
                    ->label('Множник')
                    ->suffix('x'),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Знижка')
                    ->suffix('%'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активний')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyTiers::route('/'),
            'create' => Pages\CreateLoyaltyTier::route('/create'),
            'edit' => Pages\EditLoyaltyTier::route('/{record}/edit'),
        ];
    }
}
