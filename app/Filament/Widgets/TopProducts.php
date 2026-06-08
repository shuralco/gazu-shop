<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProducts extends BaseWidget
{
    public static function canView(): bool
    {
        return \App\Support\Access\AccessControl::can('OrderResource', 'view');
    }

    protected static ?string $heading = 'Топ товарів';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('is_hit', true)
                    ->orWhere('is_new', true)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->size(48)
                    ->extraImgAttributes(['class' => 'rounded-lg ring-1 ring-black/5 object-cover bg-gray-50'])
                    ->defaultImageUrl(asset('assets/img/placeholder.svg'))
                    ->checkFileExistence(false),
                Tables\Columns\TextColumn::make('title')
                    ->label('Назва')
                    ->limit(30)
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Категорія')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_hit')
                    ->boolean()
                    ->trueIcon('heroicon-o-fire')
                    ->trueColor('danger')
                    ->label('Популярний'),
                Tables\Columns\IconColumn::make('is_new')
                    ->boolean()
                    ->trueIcon('heroicon-o-sparkles')
                    ->trueColor('success')
                    ->label('Новий'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('viewAll')
                    ->label('Переглянути всі товари')
                    ->url('/admin/products')
                    ->icon('heroicon-o-arrow-right'),
            ]);
    }
}
