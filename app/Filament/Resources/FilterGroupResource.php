<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterGroupResource\Pages;
use App\Models\FilterGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FilterGroupResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = FilterGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $navigationLabel = 'Групи характеристик';

    protected static ?string $modelLabel = 'Група характеристик';

    protected static ?string $pluralModelLabel = 'Групи характеристик';

    protected static ?int $navigationSort = 41;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Назва')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активність')
                    ->placeholder('Усі')
                    ->trueLabel('Лише активні')
                    ->falseLabel('Лише вимкнені'),
                Tables\Filters\TernaryFilter::make('has_filters')
                    ->label('Характеристики')
                    ->placeholder('Усі')
                    ->trueLabel('З характеристиками')
                    ->falseLabel('Порожні')
                    ->queries(
                        true: fn ($query) => $query->whereHas('filters'),
                        false: fn ($query) => $query->whereDoesntHave('filters'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->filtersFormColumns(['sm' => 1, 'lg' => 2])
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
            'index' => Pages\ListFilterGroups::route('/'),
            'create' => Pages\CreateFilterGroup::route('/create'),
            'edit' => Pages\EditFilterGroup::route('/{record}/edit'),
        ];
    }
}
