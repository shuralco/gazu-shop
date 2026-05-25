<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\RequiresModule;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use App\Services\Pricing\ChinesePriceCalculator;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Швидке наповнення товарів від китайського постачальника.
 * Excel-стиль: одна форма-Repeater, кожен рядок = один товар.
 * Автоматично рахує retail з cost × fx × markup.
 */
class QuickFillProducts extends Page implements HasForms
{
    use InteractsWithForms;
    use RequiresModule;

    protected static string $moduleKey = 'quick_fill';

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Швидке наповнення';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $title = 'Швидке наповнення (китайський постачальник)';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'quick-fill';

    protected static string $view = 'filament.pages.quick-fill-products';

    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importCsv')
                ->label('Імпорт з CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Імпорт товарів з CSV')
                ->modalDescription('Очікувані колонки: sku, title, manufacturer, cost_price, cost_currency, markup_percent, quantity, condition, supplier_url, image_url, original_name_cn. Категорію можна задати у колонці category_id (число) або category_slug.')
                ->form([
                    Forms\Components\FileUpload::make('csv')
                        ->label('CSV-файл')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/csv'])
                        ->disk('local')
                        ->directory('quick-fill-imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->handleCsvImport($data['csv'] ?? null);
                }),

            Actions\Action::make('downloadSample')
                ->label('Завантажити приклад CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $headers = "sku,title,manufacturer,cost_price,cost_currency,markup_percent,quantity,condition,supplier_url,image_url,original_name_cn,category_slug\n";
                    $row1 = "TEST-001,Тестовий товар,Bosch,100,CNY,100,5,new,https://detail.1688.com/offer/123.html,https://i1.aliimg.com/.../img.jpg,机油滤清器,engine\n";
                    $row2 = "TEST-002,Лампа H7,Osram,30,CNY,150,10,new,,,H7灯泡,electric\n";
                    $csv = $headers.$row1.$row2;

                    return response()->streamDownload(fn () => print($csv), 'quick-fill-sample.csv', [
                        'Content-Type' => 'text/csv',
                    ]);
                }),
        ];
    }

    public function mount(): void
    {
        $calc = app(ChinesePriceCalculator::class);

        $this->form->fill([
            'default_currency' => 'CNY',
            'default_markup' => $calc->defaultMarkup(),
            'fx_rate_hint' => 'CNY: '.$calc->fxRate('CNY').' · USD: '.$calc->fxRate('USD'),
            'rows' => [$this->emptyRow($calc->defaultMarkup())],
        ]);
    }

    private function emptyRow(?float $markup = null): array
    {
        return [
            'sku' => null,
            'title' => null,
            'original_name_cn' => null,
            'category_id' => null,
            'manufacturer' => null,
            'cost_price' => null,
            'cost_currency' => 'CNY',
            'markup_percent' => $markup ?? 100,
            'price' => null,
            'quantity' => 1,
            'condition' => 'new',
            'supplier_url' => null,
            'image_url' => null,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Налаштування за замовчуванням')
                    ->description('Застосовується до нових рядків. Курси налаштовуються в DisplaySetting (fx_cny_uah / fx_usd_uah).')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('default_currency')
                                ->label('Валюта закупки')
                                ->options(['CNY' => 'CNY (юань)', 'USD' => 'USD (долар)', 'EUR' => 'EUR (євро)', 'UAH' => 'UAH (гривня)'])
                                ->default('CNY'),
                            Forms\Components\TextInput::make('default_markup')
                                ->label('Націнка %, за замовчуванням')
                                ->numeric()
                                ->suffix('%')
                                ->default(100),
                            Forms\Components\Placeholder::make('fx_rate_hint')
                                ->label('Поточні курси')
                                ->content(fn () => app(ChinesePriceCalculator::class)->fxRate('CNY').' ₴/¥ · '.app(ChinesePriceCalculator::class)->fxRate('USD').' ₴/$'),
                        ]),
                    ]),

                Forms\Components\Repeater::make('rows')
                    ->label('Товари до додавання')
                    ->columnSpanFull()
                    ->reorderable(false)
                    ->cloneable()
                    ->collapsible()
                    ->itemLabel(fn (array $state) => trim(($state['sku'] ?? '').' · '.($state['title'] ?? 'Новий товар')))
                    ->schema([
                        Forms\Components\Grid::make(12)->schema([
                            Forms\Components\TextInput::make('sku')
                                ->label('SKU / OEM')
                                ->placeholder('06A 115 561 B')
                                ->columnSpan(3)
                                ->required(),

                            Forms\Components\TextInput::make('title')
                                ->label('Назва (українською)')
                                ->placeholder('Фільтр масляний')
                                ->columnSpan(5)
                                ->required(),

                            Forms\Components\TextInput::make('original_name_cn')
                                ->label('Оригінальна назва (CN)')
                                ->placeholder('机油滤清器')
                                ->columnSpan(4),

                            Forms\Components\Select::make('category_id')
                                ->label('Категорія')
                                ->options(fn () => Category::query()
                                    ->where('is_active', true)
                                    ->orderBy('parent_id')
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => ($c->parent_id ? '— ' : '').((string) ($c->title ?? $c->name ?? 'Категорія '.$c->id)),
                                    ])
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(4),

                            Forms\Components\Select::make('manufacturer')
                                ->label('Виробник')
                                ->options(fn () => Brand::query()->orderBy('name')->pluck('name', 'name')->all())
                                ->searchable()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                ])
                                ->createOptionUsing(function (array $data) {
                                    Brand::firstOrCreate(['name' => $data['name']], ['slug' => Str::slug($data['name']), 'is_active' => true]);
                                    return $data['name'];
                                })
                                ->columnSpan(3),

                            Forms\Components\TextInput::make('cost_price')
                                ->label('Закупка')
                                ->numeric()
                                ->step('0.01')
                                ->columnSpan(2),

                            Forms\Components\Select::make('cost_currency')
                                ->label('Валюта')
                                ->options(['CNY' => '¥', 'USD' => '$', 'EUR' => '€', 'UAH' => '₴'])
                                ->default('CNY')
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('markup_percent')
                                ->label('Націнка')
                                ->numeric()
                                ->step('1')
                                ->suffix('%')
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('price')
                                ->label('Ціна, ₴')
                                ->numeric()
                                ->step('0.01')
                                ->prefix('₴')
                                ->columnSpan(2)
                                ->placeholder(function ($get) {
                                    $cost = (float) ($get('cost_price') ?? 0);
                                    if ($cost <= 0) return null;
                                    return number_format(
                                        app(ChinesePriceCalculator::class)->calculate(
                                            $cost,
                                            $get('cost_currency') ?? 'CNY',
                                            $get('markup_percent') ? (float) $get('markup_percent') : null,
                                        ),
                                        2, '.', ' '
                                    );
                                })
                                ->helperText('Авто = закупка × курс × (1+націнка). Натисніть «Перерахувати» 🧮 щоб обчислити.'),

                            Forms\Components\TextInput::make('quantity')
                                ->label('К-ть')
                                ->numeric()
                                ->default(1)
                                ->columnSpan(1),

                            Forms\Components\Select::make('condition')
                                ->label('Стан')
                                ->options(['new' => 'Новий', 'used' => 'Б/у', 'refurbished' => 'Відновлений'])
                                ->default('new')
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('supplier_url')
                                ->label('URL постачальника')
                                ->placeholder('https://detail.1688.com/offer/...')
                                ->url()
                                ->prefixIcon('heroicon-o-link')
                                ->columnSpan(6),

                            Forms\Components\TextInput::make('image_url')
                                ->label('URL фото')
                                ->placeholder('https://...')
                                ->url()
                                ->prefixIcon('heroicon-o-photo')
                                ->columnSpan(4),
                        ]),
                    ])
                    ->defaultItems(1)
                    ->minItems(1)
                    ->addActionLabel('+ Додати ще товар')
                    ->extraItemActions([
                        FormAction::make('recalcPrice')
                            ->label('Перерахувати ціну')
                            ->icon('heroicon-o-calculator')
                            ->action(function (array $arguments, $component) {
                                $items = $component->getState();
                                $key = $arguments['item'];
                                if (! isset($items[$key])) return;
                                $r = $items[$key];
                                $price = app(ChinesePriceCalculator::class)
                                    ->calculate(
                                        (float) ($r['cost_price'] ?? 0),
                                        $r['cost_currency'] ?? 'CNY',
                                        isset($r['markup_percent']) ? (float) $r['markup_percent'] : null,
                                    );
                                $items[$key]['price'] = $price > 0 ? $price : null;
                                $component->state($items);

                                Notification::make()
                                    ->title('Ціна оновлена: '.number_format($price, 2, '.', ' ').' ₴')
                                    ->success()
                                    ->send();
                            }),
                    ]),
            ]);
    }

    private function recalc(callable $get): ?float
    {
        $cost = $get('cost_price');
        $cur = $get('cost_currency') ?? 'CNY';
        $markup = $get('markup_percent');
        if (! is_numeric($cost) || $cost <= 0) return null;

        return app(ChinesePriceCalculator::class)->calculate((float) $cost, (string) $cur, $markup !== null ? (float) $markup : null);
    }

    public function saveAll(): void
    {
        $state = $this->form->getState();
        $rows = $state['rows'] ?? [];
        if (empty($rows)) {
            Notification::make()->title('Немає рядків для збереження')->warning()->send();
            return;
        }

        $created = 0;
        $errors = [];

        DB::transaction(function () use ($rows, &$created, &$errors) {
            foreach ($rows as $i => $r) {
                if (empty($r['sku']) || empty($r['title']) || empty($r['category_id'])) {
                    $errors[] = 'Рядок '.($i + 1).': пропущено (потрібні SKU, назва, категорія)';
                    continue;
                }

                if (Product::where('sku', $r['sku'])->exists()) {
                    $errors[] = 'Рядок '.($i + 1).': SKU «'.$r['sku'].'» вже існує';
                    continue;
                }

                $finalPrice = $r['price'];
                if (! $finalPrice) {
                    $finalPrice = app(ChinesePriceCalculator::class)
                        ->calculate((float) ($r['cost_price'] ?? 0), $r['cost_currency'] ?? 'CNY', isset($r['markup_percent']) ? (float) $r['markup_percent'] : null);
                }

                Product::create([
                    'sku' => $r['sku'],
                    'title' => ['uk' => $r['title']],
                    'slug' => ['uk' => Str::slug($r['title']).'-'.Str::lower(Str::random(4))],
                    'content' => ['uk' => ' '],
                    'category_id' => $r['category_id'] ?? null,
                    'manufacturer' => $r['manufacturer'] ?? null,
                    'price' => $finalPrice,
                    'cost_price' => $r['cost_price'] ?? null,
                    'cost_currency' => $r['cost_currency'] ?? 'CNY',
                    'markup_percent' => $r['markup_percent'] ?? null,
                    'supplier_url' => $r['supplier_url'] ?? null,
                    'original_name_cn' => $r['original_name_cn'] ?? null,
                    'condition' => $r['condition'] ?? 'new',
                    'quantity' => (int) ($r['quantity'] ?? 1),
                    'stock_status' => ((int) ($r['quantity'] ?? 0)) > 0 ? 'in_stock' : 'out_of_stock',
                    'image' => $r['image_url'] ?? null,
                    'is_active' => true,
                ]);
                $created++;
            }
        });

        if ($created > 0) {
            Notification::make()
                ->title("Додано $created товарів")
                ->body(empty($errors) ? null : 'Помилки: '.implode('; ', array_slice($errors, 0, 3)))
                ->success()
                ->send();
            $this->form->fill([
                'default_currency' => 'CNY',
                'default_markup' => app(ChinesePriceCalculator::class)->defaultMarkup(),
                'rows' => [$this->emptyRow(app(ChinesePriceCalculator::class)->defaultMarkup())],
            ]);
        } else {
            Notification::make()->title('Жоден товар не додано')->body(implode(' · ', $errors))->danger()->send();
        }
    }

    private function handleCsvImport(?string $relativePath): void
    {
        if (! $relativePath) {
            Notification::make()->title('Файл не вибрано')->danger()->send();
            return;
        }

        if (! Storage::disk('local')->exists($relativePath)) {
            Notification::make()->title('Файл не знайдено')->danger()->send();
            return;
        }

        $absolutePath = Storage::disk('local')->path($relativePath);
        $h = @fopen($absolutePath, 'r');
        if (! $h) {
            Notification::make()->title('Не вдалось прочитати файл')->danger()->send();
            return;
        }

        $header = fgetcsv($h);
        if (! $header) {
            fclose($h);
            Notification::make()->title('CSV порожній')->warning()->send();
            return;
        }
        $header = array_map(fn ($x) => Str::lower(trim((string) $x)), $header);

        $rows = [];
        while (($line = fgetcsv($h)) !== false) {
            $combined = @array_combine($header, $line);
            if (! $combined) continue;
            $rows[] = array_merge($this->emptyRow(), $this->mapCsvRow($combined));
        }
        fclose($h);

        if (empty($rows)) {
            Notification::make()->title('CSV не містить даних')->warning()->send();
            return;
        }

        $current = $this->data['rows'] ?? [];
        $this->data = array_merge($this->data, [
            'rows' => array_merge($current, $rows),
        ]);

        Notification::make()
            ->title('Імпортовано '.count($rows).' рядків')
            ->body('Перевірте форму та натисніть «Зберегти всі товари»')
            ->success()
            ->send();
    }

    /**
     * Очікувані заголовки CSV (порядок не важливий, можна частково):
     * sku, title, manufacturer, cost_price, cost_currency, markup_percent,
     * quantity, condition, supplier_url, image_url, original_name_cn,
     * category_id, category_slug
     */
    private function mapCsvRow(array $row): array
    {
        $categoryId = null;
        if (! empty($row['category_id'])) {
            $categoryId = (int) $row['category_id'];
        } elseif (! empty($row['category_slug'])) {
            $cat = Category::query()
                ->where('slug->uk', $row['category_slug'])
                ->orWhere('slug->en', $row['category_slug'])
                ->first();
            $categoryId = $cat?->id;
        }

        return array_filter([
            'sku' => $row['sku'] ?? null,
            'title' => $row['title'] ?? $row['name'] ?? null,
            'original_name_cn' => $row['original_name_cn'] ?? $row['name_cn'] ?? null,
            'category_id' => $categoryId,
            'manufacturer' => $row['manufacturer'] ?? $row['brand'] ?? null,
            'cost_price' => isset($row['cost_price']) ? (float) $row['cost_price'] : null,
            'cost_currency' => strtoupper($row['cost_currency'] ?? 'CNY'),
            'markup_percent' => isset($row['markup_percent']) ? (float) $row['markup_percent'] : null,
            'quantity' => isset($row['quantity']) ? (int) $row['quantity'] : 1,
            'condition' => $row['condition'] ?? 'new',
            'supplier_url' => $row['supplier_url'] ?? null,
            'image_url' => $row['image_url'] ?? $row['image'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    public function getViewData(): array
    {
        return [
            'csvSampleHeaders' => 'sku,title,manufacturer,cost_price,cost_currency,markup_percent,quantity,condition,supplier_url,image_url',
        ];
    }
}
