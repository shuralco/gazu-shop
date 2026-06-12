<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\DisplaySetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MegaMenuEditor extends Page
{
    use \App\Filament\Concerns\GatedPage;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'Мега-меню';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?int $navigationSort = 90;

    protected static ?string $title = 'Налаштування меню';

    protected static string $view = 'filament.pages.mega-menu-editor';

    // Horizontal menu settings
    public bool $horizontalEnabled = true;

    public array $horizontalItems = [];

    // Main mega menu settings
    public string $catalogTrigger = 'click'; // click, hover, both

    public bool $megaMenuEnabled = true;

    public array $megaMenuColumns = [];

    // Promo settings
    public bool $showPromo = true;

    public string $promoTitle = 'АКЦІЇ ТИЖНЯ';

    public string $promoSubtitle = '';

    public string $promoButton = 'ПЕРЕГЛЯНУТИ ВСІ';

    public string $promoUrl = '/specials';

    // Footer settings
    public string $footerAbout = '';

    /** @var list<array{title:string,links:list<array{label:string,url:string}>}> */
    public array $footerColumns = [];

    public string $footerPayments = '';

    public string $footerCopyright = '';

    public function mount(): void
    {
        // Load horizontal menu items
        $this->horizontalEnabled = (bool) DisplaySetting::get('enable_horizontal_menu', true);
        $storedItems = DisplaySetting::get('horizontal_menu_items', null);

        if ($storedItems) {
            $items = $storedItems;
            if (is_string($items)) {
                $items = json_decode($items, true) ?? [];
            }
            $this->horizontalItems = is_array($items) ? array_values($items) : [];
        } else {
            // Fall back to generating from HeaderService
            $headerService = app(\App\Services\HeaderService::class);
            $config = $headerService->getHorizontalMenuConfig();
            $this->horizontalItems = $config['menu_items'] ?? [];
        }

        // Load mega menu structure
        $this->catalogTrigger = (string) DisplaySetting::get('catalog_trigger', 'click');
        $this->megaMenuEnabled = (bool) DisplaySetting::get('mega_menu_enabled', true);
        $structure = DisplaySetting::get('main_mega_menu_structure', null);

        if ($structure) {
            if (is_string($structure)) {
                $structure = json_decode($structure, true) ?? [];
            }
            $this->megaMenuColumns = $structure['columns'] ?? [];
        }

        // Структура ще не збережена → префіл з реальних категорій (те, що
        // зараз показує фронт). Редактор більше не відкривається порожнім.
        if (empty($this->megaMenuColumns)) {
            $this->megaMenuColumns = $this->buildColumnsFromCategories();
        }

        // Promo
        $this->showPromo = (bool) DisplaySetting::get('main_show_promo', true);
        $this->promoTitle = (string) (DisplaySetting::get('main_mega_menu_promo_title', 'АКЦІЇ ТИЖНЯ') ?: '');
        $this->promoSubtitle = (string) (DisplaySetting::get('main_mega_menu_promo_subtitle', '') ?: '');
        $this->promoButton = (string) (DisplaySetting::get('main_mega_menu_promo_button', 'ПЕРЕГЛЯНУТИ ВСІ') ?: '');
        $this->promoUrl = (string) (DisplaySetting::get('main_mega_menu_promo_url', '/specials') ?: '/specials');

        // Footer
        $this->footerAbout = (string) (DisplaySetting::get('gazu_footer_about', '') ?: '');
        $storedColumns = DisplaySetting::get('gazu_footer_columns', null);
        if ($storedColumns) {
            if (is_string($storedColumns)) {
                $storedColumns = json_decode($storedColumns, true) ?? [];
            }
            $this->footerColumns = is_array($storedColumns) ? array_values($storedColumns) : [];
        }
        $this->footerPayments = (string) (DisplaySetting::get('gazu_footer_payments', 'Visa, Mastercard, Apple Pay, Google Pay') ?: '');
        $this->footerCopyright = (string) (DisplaySetting::get('gazu_footer_copyright', '© '.date('Y').' GAZU. Усі права захищено.') ?: '');
    }

    // --- Footer editor methods ---

    public function addFooterColumn(): void
    {
        $this->footerColumns[] = ['title' => 'Нова колонка', 'links' => []];
    }

    public function removeFooterColumn(int $colIndex): void
    {
        unset($this->footerColumns[$colIndex]);
        $this->footerColumns = array_values($this->footerColumns);
    }

    public function addFooterLink(int $colIndex): void
    {
        if (! isset($this->footerColumns[$colIndex])) {
            return;
        }
        $this->footerColumns[$colIndex]['links'][] = ['label' => 'Нове посилання', 'url' => '/'];
    }

    public function removeFooterLink(int $colIndex, int $linkIndex): void
    {
        if (! isset($this->footerColumns[$colIndex]['links'][$linkIndex])) {
            return;
        }
        unset($this->footerColumns[$colIndex]['links'][$linkIndex]);
        $this->footerColumns[$colIndex]['links'] = array_values($this->footerColumns[$colIndex]['links']);
    }

    /**
     * Стартовий футер з 3 колонками: Каталог · Інформація · Контакти.
     */
    public function generateFooterDefaults(): void
    {
        $this->footerColumns = [
            ['title' => 'Каталог', 'links' => [
                ['label' => 'Усі товари', 'url' => '/catalog'],
                ['label' => 'Акції', 'url' => '/akcii'],
                ['label' => 'Хіти продажів', 'url' => '/khity'],
                ['label' => 'Новинки', 'url' => '/novynky'],
                ['label' => 'Бренди', 'url' => '/brand'],
            ]],
            ['title' => 'Інформація', 'links' => [
                ['label' => 'Про нас', 'url' => '/about'],
                ['label' => 'Доставка та оплата', 'url' => '/dostavka'],
                ['label' => 'Гарантія', 'url' => '/garantia'],
                ['label' => 'Повернення', 'url' => '/povernennya'],
                ['label' => 'FAQ', 'url' => '/faq'],
                ['label' => 'Блог', 'url' => '/blog'],
            ]],
            ['title' => 'Контакти', 'links' => [
                ['label' => 'Контакти', 'url' => '/contacts'],
                ['label' => 'Гурт та СТО', 'url' => '/wholesale'],
                ['label' => 'Кабінет', 'url' => '/kabinet'],
            ]],
        ];

        Notification::make()
            ->title('Футер заповнено стандартними колонками')
            ->success()
            ->send();
    }

    public function getCategories(): array
    {
        return Category::where(function ($q) {
            $q->whereNull('parent_id')->orWhere('parent_id', 0);
        })
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    // --- Horizontal menu ---

    public function addHorizontalItem(): void
    {
        $this->horizontalItems[] = ['text' => '', 'url' => ''];
    }

    public function removeHorizontalItem(int $index): void
    {
        unset($this->horizontalItems[$index]);
        $this->horizontalItems = array_values($this->horizontalItems);
    }

    public function moveHorizontalItem(int $index, string $direction): void
    {
        $newIndex = $direction === 'up' ? $index - 1 : $index + 1;
        if ($newIndex < 0 || $newIndex >= count($this->horizontalItems)) {
            return;
        }
        $items = $this->horizontalItems;
        [$items[$index], $items[$newIndex]] = [$items[$newIndex], $items[$index]];
        $this->horizontalItems = array_values($items);
    }

    // --- Mega menu manual editing ---

    public function addMegaColumn(): void
    {
        $this->megaMenuColumns[] = [];
    }

    public function removeMegaColumn(int $colIndex): void
    {
        unset($this->megaMenuColumns[$colIndex]);
        $this->megaMenuColumns = array_values($this->megaMenuColumns);
    }

    public function addCategoryToColumn(int $colIndex, int $categoryId): void
    {
        $category = Category::with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])->find($categoryId);
        if (! $category) {
            return;
        }

        $this->megaMenuColumns[$colIndex][] = [
            'type' => 'category',
            'title' => $category->title,
            'slug' => $category->slug,
            'category_id' => $category->id,
            'show_all_link' => $category->children->count() > 6,
            'children' => $category->children->take(8)->map(fn ($c) => [
                'title' => $c->title,
                'slug' => $c->slug,
            ])->values()->toArray(),
        ];
    }

    public function addCustomLinkToColumn(int $colIndex): void
    {
        $this->megaMenuColumns[$colIndex][] = [
            'type' => 'custom_link',
            'title' => 'Нове посилання',
            'url' => '/',
        ];
    }

    public function removeItemFromColumn(int $colIndex, int $itemIndex): void
    {
        unset($this->megaMenuColumns[$colIndex][$itemIndex]);
        $this->megaMenuColumns[$colIndex] = array_values($this->megaMenuColumns[$colIndex]);
    }

    public function updateMegaItem(int $colIndex, int $itemIndex, string $field, $value): void
    {
        if (isset($this->megaMenuColumns[$colIndex][$itemIndex])) {
            $this->megaMenuColumns[$colIndex][$itemIndex][$field] = $value;
        }
    }

    public function addChildToItem(int $colIndex, int $itemIndex, ?int $childCategoryId = null): void
    {
        if (! isset($this->megaMenuColumns[$colIndex][$itemIndex])) {
            return;
        }

        if ($childCategoryId) {
            $child = Category::find($childCategoryId);
            if ($child) {
                $this->megaMenuColumns[$colIndex][$itemIndex]['children'][] = [
                    'title' => $child->title,
                    'slug' => $child->slug,
                ];
                return;
            }
        }

        $this->megaMenuColumns[$colIndex][$itemIndex]['children'][] = [
            'title' => 'Нове посилання',
            'slug' => 'new-link',
        ];
    }

    public function getChildCategoriesFor(int $categoryId): array
    {
        return Category::where('parent_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('title', 'id')
            ->toArray();
    }

    public function removeChildFromItem(int $colIndex, int $itemIndex, int $childIndex): void
    {
        if (isset($this->megaMenuColumns[$colIndex][$itemIndex]['children'][$childIndex])) {
            unset($this->megaMenuColumns[$colIndex][$itemIndex]['children'][$childIndex]);
            $this->megaMenuColumns[$colIndex][$itemIndex]['children'] = array_values($this->megaMenuColumns[$colIndex][$itemIndex]['children']);
        }
    }

    public function updateChild(int $colIndex, int $itemIndex, int $childIndex, string $field, $value): void
    {
        if (isset($this->megaMenuColumns[$colIndex][$itemIndex]['children'][$childIndex])) {
            $this->megaMenuColumns[$colIndex][$itemIndex]['children'][$childIndex][$field] = $value;
        }
    }

    public function moveChild(int $colIndex, int $itemIndex, int $childIndex, string $direction): void
    {
        $children = $this->megaMenuColumns[$colIndex][$itemIndex]['children'] ?? [];
        $newIndex = $direction === 'up' ? $childIndex - 1 : $childIndex + 1;
        if ($newIndex < 0 || $newIndex >= count($children)) {
            return;
        }
        [$children[$childIndex], $children[$newIndex]] = [$children[$newIndex], $children[$childIndex]];
        $this->megaMenuColumns[$colIndex][$itemIndex]['children'] = array_values($children);
    }

    public function moveItemInColumn(int $colIndex, int $itemIndex, string $direction): void
    {
        $newIndex = $direction === 'up' ? $itemIndex - 1 : $itemIndex + 1;
        if ($newIndex < 0 || $newIndex >= count($this->megaMenuColumns[$colIndex])) {
            return;
        }
        $items = $this->megaMenuColumns[$colIndex];
        [$items[$itemIndex], $items[$newIndex]] = [$items[$newIndex], $items[$itemIndex]];
        $this->megaMenuColumns[$colIndex] = array_values($items);
    }

    public function moveColumnLeft(int $colIndex): void
    {
        if ($colIndex <= 0) {
            return;
        }
        $cols = $this->megaMenuColumns;
        [$cols[$colIndex - 1], $cols[$colIndex]] = [$cols[$colIndex], $cols[$colIndex - 1]];
        $this->megaMenuColumns = array_values($cols);
    }

    public function moveColumnRight(int $colIndex): void
    {
        if ($colIndex >= count($this->megaMenuColumns) - 1) {
            return;
        }
        $cols = $this->megaMenuColumns;
        [$cols[$colIndex], $cols[$colIndex + 1]] = [$cols[$colIndex + 1], $cols[$colIndex]];
        $this->megaMenuColumns = array_values($cols);
    }

    public function getRootCategories(): array
    {
        return Category::where(function ($q) {
            $q->whereNull('parent_id')->orWhere('parent_id', 0);
        })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('title', 'id')
            ->toArray();
    }

    // --- Mega menu auto-generation ---

    public function autoGenerateMegaMenu(): void
    {
        $this->megaMenuColumns = $this->buildColumnsFromCategories();
        Notification::make()->success()->title('Мега-меню згенеровано з категорій')->send();
    }

    /**
     * Колонки редактора з дерева категорій — та сама вибірка/дедуплікація,
     * що й у MegaMenuBuilder::fromDatabase() (фронт), тому редактор
     * відображає реальний стан меню.
     */
    private function buildColumnsFromCategories(): array
    {
        $categories = Category::where(function ($q) {
            $q->whereNull('parent_id')->orWhere('parent_id', 0);
        })
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->limit(40)
            ->get()
            // Той самий дедуп, що в MegaMenuBuilder — сідер колись наплодив
            // дублікати категорій через JSON-обгорнутий slug.
            ->unique(fn (Category $r) => mb_strtolower(trim((string) ($r->title ?? ''))))
            ->take(12)
            ->values();

        $columns = [];
        $columnItems = [];
        $perColumn = max(1, (int) ceil($categories->count() / 4));

        foreach ($categories as $i => $category) {
            $columnItems[] = [
                'type' => 'category',
                'title' => $category->title,
                'slug' => $category->slug,
                'category_id' => $category->id,
                'show_all_link' => $category->children->count() > 6,
                'children' => $category->children->take(8)->map(fn ($c) => [
                    'title' => $c->title,
                    'slug' => $c->slug,
                ])->values()->toArray(),
            ];

            if (count($columnItems) >= $perColumn || $i === $categories->count() - 1) {
                $columns[] = $columnItems;
                $columnItems = [];
            }
        }

        return $columns;
    }

    public function autoGenerateHorizontal(): void
    {
        $categories = Category::where(function ($q) {
            $q->whereNull('parent_id')->orWhere('parent_id', 0);
        })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $this->horizontalItems = [];
        foreach ($categories as $cat) {
            $this->horizontalItems[] = ['text' => $cat->title, 'url' => '/' . $cat->slug];
        }
        $this->horizontalItems[] = ['text' => 'БРЕНДИ', 'url' => '/brands'];
        $this->horizontalItems[] = ['text' => 'АКЦІЇ', 'url' => '/specials'];
        $this->horizontalItems[] = ['text' => 'ХІТИ', 'url' => '/hits'];
        $this->horizontalItems[] = ['text' => 'НОВИНКИ', 'url' => '/new'];

        Notification::make()->success()->title('Горизонтальне меню згенеровано з категорій')->send();
    }

    public function save(): void
    {
        // Save horizontal menu toggle
        DisplaySetting::updateOrCreate(['key' => 'enable_horizontal_menu'], [
            'value' => $this->horizontalEnabled ? 'true' : 'false',
            'type' => 'boolean',
            'title' => 'Горизонтальне меню',
            'group' => 'horizontal_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Save horizontal items
        DisplaySetting::updateOrCreate(['key' => 'horizontal_menu_items'], [
            'value' => json_encode($this->horizontalItems),
            'type' => 'json',
            'title' => 'Пункти горизонтального меню',
            'group' => 'horizontal_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Save catalog trigger mode
        DisplaySetting::updateOrCreate(['key' => 'catalog_trigger'], [
            'value' => $this->catalogTrigger,
            'type' => 'string',
            'title' => 'Спосіб відкриття каталогу',
            'group' => 'mega_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Save mega menu toggle
        DisplaySetting::updateOrCreate(['key' => 'mega_menu_enabled'], [
            'value' => $this->megaMenuEnabled ? 'true' : 'false',
            'type' => 'boolean',
            'title' => 'Мега-меню',
            'group' => 'mega_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Save mega menu structure
        DisplaySetting::updateOrCreate(['key' => 'main_mega_menu_structure'], [
            'value' => json_encode(['columns' => $this->megaMenuColumns]),
            'type' => 'json',
            'title' => 'Структура мега-меню',
            'group' => 'mega_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Promo settings
        DisplaySetting::updateOrCreate(['key' => 'main_show_promo'], [
            'value' => $this->showPromo ? 'true' : 'false',
            'type' => 'boolean',
            'title' => 'Промо',
            'group' => 'mega_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        DisplaySetting::updateOrCreate(['key' => 'main_mega_menu_promo_title'], [
            'value' => $this->promoTitle,
            'type' => 'string',
            'title' => 'Промо заголовок',
            'group' => 'mega_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        DisplaySetting::updateOrCreate(['key' => 'main_mega_menu_promo_subtitle'], [
            'value' => $this->promoSubtitle,
            'type' => 'string',
            'title' => 'Промо опис',
            'group' => 'mega_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        DisplaySetting::updateOrCreate(['key' => 'main_mega_menu_promo_button'], [
            'value' => $this->promoButton,
            'type' => 'string',
            'title' => 'Промо кнопка',
            'group' => 'mega_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        DisplaySetting::updateOrCreate(['key' => 'main_mega_menu_promo_url'], [
            'value' => $this->promoUrl,
            'type' => 'string',
            'title' => 'Промо URL',
            'group' => 'mega_menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Clear caches
        cache()->forget('mega_menu_structure');
        cache()->forget('mega_menu_config');
        cache()->forget('horizontal_menu_config');
        cache()->forget('header_config');
        cache()->forget('header_main_config');
        DisplaySetting::flushHeaderCache();

        // GAZU storefront: кеш дерева мега-меню (gazu_mega_*) + повний
        // response-кеш сторінок — інакше фронт показує старе меню до 10 хв
        // (а ResponseCache — і довше).
        \App\View\Composers\GazuMenuComposer::flushMenuCache();
        try {
            \Illuminate\Support\Facades\Artisan::call('responsecache:clear');
        } catch (\Throwable) {
            // responsecache може бути не встановлений — не критично
        }

        // Footer
        DisplaySetting::set('gazu_footer_about', $this->footerAbout);
        DisplaySetting::set('gazu_footer_columns', $this->footerColumns);
        DisplaySetting::set('gazu_footer_payments', $this->footerPayments);
        DisplaySetting::set('gazu_footer_copyright', $this->footerCopyright);

        Notification::make()->success()->title('Меню + футер збережено')->send();
    }
}
