<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SearchQueryResource\Pages;
use App\Models\SearchQuery;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SearchQueryResource extends Resource
{
    protected static ?string $model = SearchQuery::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationGroup = 'Аналітика';
    protected static ?string $navigationLabel = 'Пошукові запити';
    protected static ?string $modelLabel = 'Пошуковий запит';
    protected static ?string $pluralModelLabel = 'Пошукові запити';
    protected static ?int $navigationSort = 20;

    public static function getNavigationBadge(): ?string
    {
        $zero = SearchQuery::where('results_count', 0)->where('search_count', '>=', 2)->count();
        return $zero > 0 ? (string) $zero : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('query')
                    ->label('Запит')
                    ->searchable()
                    ->weight('medium')
                    ->copyable(),
                Tables\Columns\TextColumn::make('search_count')
                    ->label('Шукали')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state >= 10 ? 'success' : ($state >= 3 ? 'warning' : 'gray')),
                Tables\Columns\TextColumn::make('results_count')
                    ->label('Знайдено')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state === 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state === 0 ? '⚠ 0' : (string) $state),
                Tables\Columns\TextColumn::make('click_count')
                    ->label('Клікали')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_searched_at')
                    ->label('Останній раз')
                    ->dateTime('d.m H:i')
                    ->description(fn ($record) => $record->last_searched_at?->diffForHumans())
                    ->sortable(),
            ])
            ->defaultSort('search_count', 'desc')
            ->filters([
                Tables\Filters\Filter::make('zero_results')
                    ->label('Тільки 0 результатів')
                    ->query(fn ($q) => $q->where('results_count', 0))
                    ->toggle(),
                Tables\Filters\Filter::make('popular')
                    ->label('Тільки популярні (≥3 пошуків)')
                    ->query(fn ($q) => $q->where('search_count', '>=', 3))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('search')
                    ->label('Перевірити')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => url('/search?q='.urlencode($record->query)))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()->label(''),
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
            'index' => Pages\ListSearchQueries::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
