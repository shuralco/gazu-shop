<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterResource\Pages;
use App\Models\Filter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FilterResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = Filter::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $navigationLabel = 'Характеристики';

    protected static ?string $modelLabel = 'Характеристика';

    protected static ?string $pluralModelLabel = 'Характеристики';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Інформація про характеристику')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Назва')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('filter_group_id')
                            ->label('Група характеристик')
                            ->relationship('filterGroup', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Назва')
                    ->searchable(),
                Tables\Columns\TextColumn::make('filterGroup.title')
                    ->label('Група характеристик')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('filter_group_id')
                    ->label('Група характеристик')
                    ->relationship('filterGroup', 'title')
                    // title — translatable JSON; інакше у фільтрі рендериться сирий JSON.
                    ->getOptionLabelFromRecordUsing(fn ($record) => is_array($record->title)
                        ? ($record->title['uk'] ?? reset($record->title))
                        : (json_decode($record->title, true)['uk'] ?? $record->title))
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активність')
                    ->placeholder('Усі')
                    ->trueLabel('Лише активні')
                    ->falseLabel('Лише вимкнені'),
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
            'index' => Pages\ListFilters::route('/'),
            'create' => Pages\CreateFilter::route('/create'),
            'edit' => Pages\EditFilter::route('/{record}/edit'),
        ];
    }
}
