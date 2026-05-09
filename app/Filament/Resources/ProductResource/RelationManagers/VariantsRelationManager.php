<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Варіанти товару';
    protected static ?string $icon = 'heroicon-o-squares-2x2';

    protected static ?string $modelLabel = 'Варіант';

    protected static ?string $pluralModelLabel = 'Варіанти';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->label('Артикул (SKU)')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('price')
                    ->label('Ціна')
                    ->numeric()
                    ->prefix('₴')
                    ->helperText('Залиште порожнім для використання базової ціни + модифікатори'),

                Forms\Components\TextInput::make('old_price')
                    ->label('Стара ціна')
                    ->numeric()
                    ->prefix('₴'),

                Forms\Components\TextInput::make('quantity')
                    ->label('Кількість')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Forms\Components\Select::make('stock_status')
                    ->label('Статус наявності')
                    ->options([
                        'in_stock' => 'В наявності',
                        'out_of_stock' => 'Немає в наявності',
                        'preorder' => 'Під замовлення',
                    ])
                    ->default('in_stock'),

                Forms\Components\FileUpload::make('image')
                    ->label('Зображення варіанту')
                    ->image()
                    ->directory('products/variants')
                    ->visibility('public'),

                Forms\Components\TextInput::make('weight')
                    ->label('Вага (кг)')
                    ->numeric(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Активний')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('option_values')
                    ->label('Опції')
                    ->formatStateUsing(function ($state) {
                        if (!$state || !is_array($state)) return '-';
                        return implode(' / ', array_values($state));
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(function ($state, ProductVariant $record) {
                        $effectivePrice = $record->getEffectivePrice();
                        if ($state !== null) {
                            return number_format($effectivePrice, 2) . ' ₴';
                        }
                        return number_format($effectivePrice, 2) . ' ₴ (авто)';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('old_price')
                    ->label('Стара ціна')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' ₴' : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кількість')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'out_of_stock' => 'danger',
                        'preorder' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_stock' => 'В наявності',
                        'out_of_stock' => 'Немає',
                        'preorder' => 'Під замовлення',
                    }),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Активний'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати варіант'),

                Tables\Actions\Action::make('generateVariants')
                    ->label('Генерувати варіанти')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Генерувати варіанти')
                    ->modalDescription('Буде створено варіанти для всіх комбінацій активних опцій. Існуючі варіанти не будуть змінені.')
                    ->action(function () {
                        $product = $this->ownerRecord;
                        $options = $product->options()
                            ->where('is_active', true)
                            ->with(['values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
                            ->orderBy('sort_order')
                            ->get();

                        if ($options->isEmpty()) {
                            Notification::make()
                                ->title('Помилка')
                                ->body('Спочатку додайте опції та їх значення на вкладці "Варіанти".')
                                ->danger()
                                ->send();
                            return;
                        }

                        $optionValueSets = $options->map(fn ($option) => $option->values->toArray())->toArray();

                        $combinations = $this->cartesianProduct($optionValueSets);

                        $created = 0;
                        foreach ($combinations as $combination) {
                            $optionValuesJson = [];
                            $valueIds = [];
                            $skuParts = [$product->sku ?? 'PRD'];

                            foreach ($combination as $index => $valueData) {
                                $option = $options[$index];
                                $optionValuesJson[$option->name] = $valueData['value'];
                                $valueIds[] = $valueData['id'];
                                $skuParts[] = Str::upper(Str::limit(Str::slug($valueData['value'], ''), 6, ''));
                            }

                            // Check if this exact combination exists
                            $existingVariant = $product->variants()
                                ->whereJsonContains('option_values', $optionValuesJson)
                                ->first();

                            if ($existingVariant) {
                                continue;
                            }

                            $sku = implode('-', $skuParts);
                            // Ensure SKU uniqueness
                            $counter = 0;
                            $originalSku = $sku;
                            while (ProductVariant::where('sku', $sku)->exists()) {
                                $counter++;
                                $sku = $originalSku . '-' . $counter;
                            }

                            $variant = $product->variants()->create([
                                'sku' => $sku,
                                'price' => null,
                                'old_price' => null,
                                'quantity' => 0,
                                'stock_status' => 'in_stock',
                                'option_values' => $optionValuesJson,
                                'is_active' => true,
                            ]);

                            $variant->optionValues()->attach($valueIds);
                            $created++;
                        }

                        Notification::make()
                            ->title('Варіанти згенеровано')
                            ->body("Створено {$created} нових варіантів.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Варіанти не додано')
            ->emptyStateDescription('Додайте опції товару, потім натисніть "Генерувати варіанти" або створіть вручну.')
            ->defaultSort('id');
    }

    /**
     * Compute the cartesian product of multiple arrays.
     */
    private function cartesianProduct(array $sets): array
    {
        if (empty($sets)) {
            return [];
        }

        $result = [[]];

        foreach ($sets as $set) {
            $newResult = [];
            foreach ($result as $existing) {
                foreach ($set as $item) {
                    $newResult[] = array_merge($existing, [$item]);
                }
            }
            $result = $newResult;
        }

        return $result;
    }
}
