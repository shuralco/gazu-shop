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
                // ✨ Auto-discover: find products in same category that share
                // most specifications with this one (differing only in 1-2
                // values like size/volume). Attach them as 'related'.
                Tables\Actions\Action::make('autoRelatedBySpecs')
                    ->label('Авто-зв\'язати схожі')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalDescription('Знайде до 20 товарів у тій самій категорії що мають спільні характеристики (відрізняються лише розміром/обʼємом/типом). Прив\'яже їх як «Пов\'язані».')
                    ->action(function () {
                        /** @var \App\Models\Product $owner */
                        $owner = $this->ownerRecord;
                        $specs = is_array($owner->specifications)
                            ? $owner->specifications
                            : (json_decode((string) $owner->specifications, true) ?: []);

                        if (! $owner->category_id || empty($specs)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Нема даних для пошуку')
                                ->body('Потрібна category_id + specifications заповнені')
                                ->warning()->send();
                            return;
                        }

                        // candidates — same category, different product
                        $candidates = Product::query()
                            ->where('category_id', $owner->category_id)
                            ->where('id', '!=', $owner->id)
                            ->whereNotNull('specifications')
                            ->limit(200)
                            ->get(['id', 'title', 'sku', 'specifications']);

                        $scored = [];
                        foreach ($candidates as $c) {
                            $cs = is_array($c->specifications)
                                ? $c->specifications
                                : (json_decode((string) $c->specifications, true) ?: []);
                            if (empty($cs)) continue;

                            $common = 0; $diff = 0;
                            foreach ($specs as $k => $v) {
                                if (! isset($cs[$k])) continue;
                                if ((string) $cs[$k] === (string) $v) $common++;
                                else $diff++;
                            }
                            // score = shared keys, prefer those with 1-2 diff (true variants)
                            if ($common >= 1 && $diff >= 1 && $diff <= 3) {
                                $scored[] = ['id' => $c->id, 'score' => $common - $diff * 0.5];
                            }
                        }
                        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
                        $picks = array_slice($scored, 0, 20);

                        $attached = 0;
                        $existing = $owner->relatedProducts()->pluck('related_product_id')->all();
                        foreach ($picks as $pick) {
                            if (in_array($pick['id'], $existing, true)) continue;
                            $owner->relatedProducts()->attach($pick['id'], [
                                'type' => 'related',
                                'sort_order' => 0,
                            ]);
                            $attached++;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title("Прив'язано {$attached} товарів")
                            ->body($attached > 0
                                ? 'Знайдено за спільними характеристиками '.implode(', ', array_slice(array_keys($specs), 0, 3))
                                : 'Не знайдено схожих — спробуй більше товарів у категорії')
                            ->success()->send();
                    }),

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
            ->reorderable('sort_order')
            ->defaultSort('pivot_sort_order');
    }
}
