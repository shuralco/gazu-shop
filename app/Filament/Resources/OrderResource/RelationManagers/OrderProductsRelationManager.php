<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderProduct;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Str;
use Filament\Tables;
use Filament\Tables\Table;

class OrderProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderProducts';

    protected static ?string $title = 'Товари замовлення';

    protected static ?string $modelLabel = 'товар';

    protected static ?string $pluralModelLabel = 'товари';

    protected static ?string $navigationLabel = 'Товари';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Інформація про товар')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Товар')
                            ->relationship('product', 'title')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Product $record): string => "{$record->title} (₴{$record->price})")
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('price', $product->price);
                                        $set('title', $product->title);
                                        $set('slug', $product->getLocalizedSlug());
                                        $set('image', $product->image);
                                    }
                                }
                            })
                            ->required()
                            ->placeholder('Виберіть товар...')
                            ->helperText('Почніть вводити назву товару для пошуку')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Кількість')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(999)
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->required()
                                    ->suffix('шт.')
                                    ->helperText('Мінімум 1 шт., максимум 999 шт.')
                                    ->reactive(),

                                Forms\Components\TextInput::make('price')
                                    ->label('Ціна за одиницю')
                                    ->numeric()
                                    ->prefix('₴')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->live(onBlur: true)
                                    ->required()
                                    ->helperText('Ціна може відрізнятися від базової ціни товару')
                                    ->reactive(),
                            ]),

                        Forms\Components\Placeholder::make('subtotal_display')
                            ->label('Підсумок')
                            ->content(function (Forms\Get $get): string {
                                $quantity = (int) ($get('quantity') ?? 1);
                                $price = (float) ($get('price') ?? 0);
                                $subtotal = $quantity * $price;

                                return '₴'.number_format($subtotal, 2);
                            })
                            ->columnSpanFull(),

                        // Скриті поля для зберігання даних товару
                        Forms\Components\Hidden::make('title'),
                        Forms\Components\Hidden::make('slug'),
                        Forms\Components\Hidden::make('image'),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn ($query) => $query->with(['product', 'warehouse']))
            ->defaultGroup(
                Tables\Grouping\Group::make('warehouse_id')
                    ->label('Склад відправлення')
                    ->getTitleFromRecordUsing(fn (OrderProduct $r) => $r->warehouse
                        ? ($r->warehouse->city ?: $r->warehouse->name).
                          ($r->warehouse->delivery_eta ? ' · '.$r->warehouse->delivery_eta : '')
                        : 'Без складу')
                    ->collapsible()
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->size(64)
                    ->extraImgAttributes(['class' => 'rounded-lg ring-1 ring-black/5 object-cover bg-gray-50'])
                    ->defaultImageUrl(asset('assets/img/placeholder.svg'))
                    ->checkFileExistence(false)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('warehouse.city')
                    ->label('Склад')
                    ->placeholder('—')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Назва товару')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->limit(50)
                    ->tooltip(function (OrderProduct $record): ?string {
                        return Str::length($record->title) > 50 ? $record->title : null;
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна за од.')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->color('primary')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кількість')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->suffix(' шт.')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Сума')
                    ->getStateUsing(function (OrderProduct $record): float {
                        return $record->price * $record->quantity;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->color('success')
                    ->weight('bold')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('product_id')
                    ->label('ID товару')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->copyable()
                    ->copyMessage('ID товару скопійовано')
                    ->copyMessageDuration(1500),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Товар')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати товар')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Автоматично заповнюємо дані товару при створенні
                        if (isset($data['product_id'])) {
                            $product = Product::find($data['product_id']);
                            if ($product) {
                                $data['title'] = $product->title;
                                $data['slug'] = $product->getLocalizedSlug();
                                $data['image'] = $product->image;
                            }
                        }

                        return $data;
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Товар додано до замовлення')
                            ->body('Не забудьте оновити загальну суму замовлення.')
                    ),

                Tables\Actions\Action::make('calculateTotal')
                    ->label('Перерахувати суму')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->action(function () {
                        $this->recalculateOrderTotal();
                    })
                    ->tooltip('Автоматично перерахує загальну суму замовлення'),

                Tables\Actions\Action::make('importProducts')
                    ->label('Імпорт товарів')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('products')
                            ->label('Виберіть товари для додавання')
                            ->multiple()
                            // Без options(pluck) + preload: вантажило ВСІ 1277 товарів
                            // + сирий JSON title. Пошук нижче (getSearchResultsUsing)
                            // читабельний через аксесор — лишаємо лише його.
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => Product::where('is_active', true)
                                ->where('title', 'like', "%{$search}%")
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn (Product $record): array => [$record->id => "{$record->title} (₴{$record->price})"]
                                )
                                ->toArray()
                            )
                            ->helperText('Виберіть один або кілька товарів для швидкого додавання')
                            ->required(),
                        Forms\Components\TextInput::make('default_quantity')
                            ->label('Кількість за замовчуванням')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->suffix('шт.')
                            ->helperText('Ця кількість буде встановлена для всіх вибраних товарів'),
                    ])
                    ->action(function (array $data) {
                        $this->importMultipleProducts($data);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('quickEdit')
                    ->label('Швидке редагування')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Кількість')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->suffix('шт.'),
                                Forms\Components\TextInput::make('price')
                                    ->label('Ціна')
                                    ->numeric()
                                    ->prefix('₴')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(),
                            ]),
                    ])
                    ->fillForm(fn (OrderProduct $record): array => [
                        'quantity' => $record->quantity,
                        'price' => $record->price,
                    ])
                    ->action(function (OrderProduct $record, array $data): void {
                        $record->update([
                            'quantity' => $data['quantity'],
                            'price' => $data['price'],
                        ]);

                        $this->recalculateOrderTotal();

                        Notification::make()
                            ->success()
                            ->title('Товар оновлено')
                            ->body('Кількість та ціна товару змінені.')
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->label('Редагувати')
                    ->icon('heroicon-o-pencil')
                    ->color('info')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Оновлюємо дані товару при редагуванні
                        if (isset($data['product_id'])) {
                            $product = Product::find($data['product_id']);
                            if ($product) {
                                $data['title'] = $product->title;
                                $data['slug'] = $product->getLocalizedSlug();
                                $data['image'] = $product->image;
                            }
                        }

                        return $data;
                    })
                    ->after(function () {
                        $this->recalculateOrderTotal();
                    }),

                Tables\Actions\Action::make('viewProduct')
                    ->label('Перегляд товару')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (OrderProduct $record): string => $record->product ? "/product/{$record->product->slug}" : '#'
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (OrderProduct $record): bool => $record->product !== null
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label('Видалити')
                    ->icon('heroicon-o-trash')
                    ->after(function () {
                        $this->recalculateOrderTotal();
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Товар видалено')
                            ->body('Загальна сума замовлення оновлена.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            $this->recalculateOrderTotal();
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Товари видалено')
                                ->body('Загальна сума замовлення оновлена.')
                        ),

                    Tables\Actions\BulkAction::make('updateQuantity')
                        ->label('Змінити кількість')
                        ->icon('heroicon-o-calculator')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('new_quantity')
                                ->label('Нова кількість')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->suffix('шт.')
                                ->helperText('Ця кількість буде встановлена для всіх вибраних товарів'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function (OrderProduct $record) use ($data) {
                                $record->update(['quantity' => $data['new_quantity']]);
                            });

                            $this->recalculateOrderTotal();

                            Notification::make()
                                ->success()
                                ->title('Кількість оновлено')
                                ->body('Кількість товарів змінена для '.$records->count().' позицій.')
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('applyDiscount')
                        ->label('Застосувати знижку')
                        ->icon('heroicon-o-tag')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('discount_type')
                                ->label('Тип знижки')
                                ->options([
                                    'percentage' => 'Відсоток (%)',
                                    'fixed' => 'Фіксована сума (₴)',
                                ])
                                ->required()
                                ->live(),
                            Forms\Components\TextInput::make('discount_value')
                                ->label('Розмір знижки')
                                ->numeric()
                                ->required()
                                ->suffix(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? '%' : '₴')
                                ->minValue(0)
                                ->maxValue(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? 100 : 999999),
                        ])
                        ->action(function (array $data, $records) {
                            $this->applyBulkDiscount($data, $records);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->poll('30s') // Оновлення кожні 30 секунд
            ->description(function () {
                $stats = $this->getOwnerRecord()->orderProducts()
                    ->selectRaw('COUNT(*) as total_products, SUM(quantity) as total_quantity, SUM(price * quantity) as total_amount')
                    ->first();

                return "Всього товарів: {$stats->total_products} | Загальна кількість: ".($stats->total_quantity ?? 0)." шт. | Сума товарів: ₴".number_format($stats->total_amount ?? 0, 2);
            });
    }

    /**
     * Перерахувати загальну суму замовлення
     */
    protected function recalculateOrderTotal(): void
    {
        $order = $this->getOwnerRecord();

        $subtotal = $order->orderProducts()
            ->selectRaw('SUM(price * quantity) as total')
            ->value('total') ?? 0;

        $total = $subtotal + $order->shipping_cost;

        $order->update(['total' => $total]);

        Notification::make()
            ->success()
            ->title('Сума замовлення оновлена')
            ->body('Нова загальна сума: ₴'.number_format($total, 2))
            ->send();
    }

    /**
     * Імпорт кількох товарів одночасно
     */
    protected function importMultipleProducts(array $data): void
    {
        $order = $this->getOwnerRecord();
        $productIds = $data['products'];
        $defaultQuantity = $data['default_quantity'] ?? 1;
        $addedCount = 0;

        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }

            // Перевіряємо чи товар уже є в замовленні
            $existingOrderProduct = $order->orderProducts()
                ->where('product_id', $productId)
                ->first();

            if ($existingOrderProduct) {
                // Якщо товар вже є, збільшуємо кількість
                $existingOrderProduct->increment('quantity', $defaultQuantity);
            } else {
                // Створюємо новий запис
                $order->orderProducts()->create([
                    'product_id' => $productId,
                    'title' => $product->title,
                    'slug' => $product->getLocalizedSlug(),
                    'image' => $product->image,
                    'price' => $product->price,
                    'quantity' => $defaultQuantity,
                ]);
                $addedCount++;
            }
        }

        $this->recalculateOrderTotal();

        Notification::make()
            ->success()
            ->title('Товари імпортовано')
            ->body("Додано {$addedCount} нових товарів до замовлення.")
            ->send();
    }

    /**
     * Застосування групової знижки
     */
    protected function applyBulkDiscount(array $data, $records): void
    {
        $discountType = $data['discount_type'];
        $discountValue = $data['discount_value'];
        $updatedCount = 0;

        $records->each(function (OrderProduct $record) use ($discountType, $discountValue, &$updatedCount) {
            $currentPrice = $record->price;
            $newPrice = $currentPrice;

            if ($discountType === 'percentage') {
                $newPrice = $currentPrice * (1 - ($discountValue / 100));
            } else {
                $newPrice = max(0, $currentPrice - $discountValue);
            }

            if ($newPrice !== $currentPrice) {
                $record->update(['price' => $newPrice]);
                $updatedCount++;
            }
        });

        $this->recalculateOrderTotal();

        Notification::make()
            ->success()
            ->title('Знижку застосовано')
            ->body("Оновлено ціни для {$updatedCount} товарів.")
            ->send();
    }
}
