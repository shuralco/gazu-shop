<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProducts extends BaseWidget
{
    protected static ?string $heading = 'Низький залишок (1–5 шт.)';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 2];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('is_active', true)
                    ->whereBetween('quantity', [1, 5])
                    ->orderBy('quantity', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Товар')
                    ->limit(40)
                    ->weight('medium')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('sku')
                    ->label('OEM/SKU')
                    ->fontFamily('mono')
                    ->size('xs'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Залиш.')
                    ->badge()
                    ->color(fn ($state) => $state <= 2 ? 'danger' : 'warning')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, '.', ' ').' ₴')
                    ->alignEnd(),
            ])
            ->emptyStateHeading('Усе ок')
            ->emptyStateDescription('Жоден товар не має критичного залишку.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
