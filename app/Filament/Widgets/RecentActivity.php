<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Showcase of the latest catalog additions — useful when there are no
 * orders yet (e.g. fresh demo seed), so the dashboard never looks empty.
 */
class RecentActivity extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    protected static ?string $heading = 'Останні додані товари';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 2];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('is_active', true)
                    ->latest('id')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Товар')
                    ->limit(50)
                    ->weight('medium')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Категорія')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, '.', ' ').' ₴')
                    ->alignEnd()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('На складі')
                    ->badge()
                    ->color(fn ($state) => $state > 5 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->alignEnd(),
            ])
            ->emptyStateHeading('Каталог порожній')
            ->paginated(false);
    }
}
