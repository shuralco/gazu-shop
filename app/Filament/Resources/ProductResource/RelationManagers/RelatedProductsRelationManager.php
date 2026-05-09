<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RelatedProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'relatedProducts';

    protected static ?string $title = 'Пов\'язані товари';
    protected static ?string $icon = 'heroicon-o-link';

    protected static ?string $modelLabel = 'Пов\'язаний товар';

    protected static ?string $pluralModelLabel = 'Пов\'язані товари';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->size(48)
                    ->extraImgAttributes(['class' => 'rounded-lg ring-1 ring-black/5 object-cover bg-gray-50'])
                    ->defaultImageUrl(asset('assets/img/placeholder.svg'))
                    ->checkFileExistence(false),

                Tables\Columns\TextColumn::make('title')
                    ->label('Назва')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Product $record): string => \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $record]))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('sku')
                    ->label('Код')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ? str_replace('SKU-', '', $state) : '-'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ') . ' грн')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'related' => 'primary',
                        'cross_sell' => 'success',
                        'upsell' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'related' => 'Пов\'язаний',
                        'cross_sell' => 'Супутній',
                        'upsell' => 'Рекомендований',
                        default => $state,
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Додати товар')
                    ->recordSelect(
                        fn (Forms\Components\Select $select) => $select
                            ->label('Оберіть товар')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                return Product::query()
                                    ->where(function ($query) use ($search) {
                                        $query->where('title', 'like', "%{$search}%")
                                            ->orWhere('sku', 'like', "%{$search}%");
                                    })
                                    ->where('id', '!=', $this->ownerRecord->id)
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn (Product $product) => [
                                        $product->id => $product->title . ' (' . str_replace('SKU-', '', $product->sku ?? '') . ')',
                                    ])
                                    ->toArray();
                            })
                            ->preload()
                    )
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),

                        Forms\Components\Select::make('type')
                            ->label('Тип зв\'язку')
                            ->options([
                                'related' => 'Пов\'язаний',
                                'cross_sell' => 'Супутній',
                                'upsell' => 'Рекомендований',
                            ])
                            ->default('related')
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = 0;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Видалити'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Видалити обрані'),
                ]),
            ])
            ->emptyStateHeading('Пов\'язані товари не додано')
            ->emptyStateDescription('Додайте пов\'язані, супутні або рекомендовані товари')
            ->emptyStateActions([
                Tables\Actions\AttachAction::make()
                    ->label('Додати товар'),
            ])
            ->defaultSort('pivot_sort_order');
    }
}
