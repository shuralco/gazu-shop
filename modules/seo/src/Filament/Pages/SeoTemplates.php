<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\DisplaySetting;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Services\SeoMetaGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SeoTemplates extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?string $navigationLabel = 'Шаблони SEO';

    protected static ?string $title = 'Налаштування SEO шаблонів';

    protected static string $view = 'filament.pages.seo-templates';

    protected static ?int $navigationSort = 120;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(array_merge(
            $this->templateState(),
            ['robots_txt_content' => $this->getCurrentRobotsContent()],
        ));
    }

    /**
     * Поточний стан усіх шаблонів: override з DisplaySetting або базовий
     * GAZU-дефолт із \App\Support\SeoTemplates::DEFAULTS (єдине джерело).
     */
    private function templateState(): array
    {
        $state = [];
        foreach (\App\Support\SeoTemplates::DEFAULTS as $key => $fields) {
            foreach (['title', 'description'] as $field) {
                $state[$this->fieldName($key, $field)] = \App\Support\SeoTemplates::template($key, $field);
            }
        }

        return $state;
    }

    /** Ім'я поля форми: home → home_title, інші → {key}_{field}_template. */
    private function fieldName(string $key, string $field): string
    {
        return $key === 'home' ? "home_{$field}" : "{$key}_{$field}_template";
    }

    /** Розділи шаблонів: ключ таксономії => [заголовок секції, доступні плейсхолдери]. */
    private const SECTIONS = [
        'home' => ['🏠 Головна сторінка', '{shop}'],
        'category' => ['🏷️ Категорії', '{name} — назва категорії, {count} — к-сть товарів, {shop}'],
        'product' => ['📦 Товари', '{name}, {price}, {sku}, {brand} — виробник, {category}, {excerpt}, {shop}'],
        'brand' => ['🏭 Сторінка бренду', '{name} — назва бренду, {count} — к-сть товарів, {shop}'],
        'brands' => ['🗂️ Список усіх брендів', '{shop}'],
        'car' => ['🚗 Каталог за авто (марка/модель/рік)', '{car} — напр. «Chery Tiggo 7 2020», {count}, {shop}'],
        'search' => ['🔍 Результати пошуку', '{query} — запит, {count} — знайдено, {shop}'],
        'page' => ['📄 Інфо-сторінки', '{name} — назва сторінки, {shop}'],
        'blog' => ['📰 Блог (список статей)', '{shop}'],
        'blog_post' => ['📝 Стаття блогу', '{name} — заголовок статті, {shop}'],
    ];

    public function form(Form $form): Form
    {
        $sections = [];
        foreach (self::SECTIONS as $key => [$label, $placeholders]) {
            $sections[] = Section::make($label)
                ->collapsible()
                ->collapsed($key !== 'home')
                ->schema([
                    TextInput::make($this->fieldName($key, 'title'))
                        ->label('Шаблон заголовку (title)')
                        ->helperText('Плейсхолдери: '.$placeholders)
                        ->required(),
                    Textarea::make($this->fieldName($key, 'description'))
                        ->label('Шаблон опису (description)')
                        ->helperText('Плейсхолдери: '.$placeholders)
                        ->rows(3)
                        ->required(),
                ])
                ->columns(1);
        }

        return $form
            ->schema([
                ...$sections,

                Section::make('🤖 Robots.txt')
                    ->schema([
                        Textarea::make('robots_txt_content')
                            ->label('Вміст robots.txt')
                            ->rows(15)
                            ->helperText('Правила для пошукових роботів')
                            ->required(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_all_seo')
                ->label('🗑️ Очистити весь SEO')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Видалити всі SEO дані?')
                ->modalDescription('Це видалить ВСІ SEO записи з бази даних. Дія незворотна!')
                ->action(function () {
                    SeoMeta::truncate();

                    Notification::make()
                        ->title('SEO дані очищено')
                        ->body('Всі SEO записи видалено з бази даних')
                        ->warning()
                        ->send();
                }),
            Action::make('regenerate_all_seo')
                ->label('🚀 Повна перегенерація')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Перегенерувати весь SEO?')
                ->modalDescription('Це очистить існуючі SEO дані та створить нові для всіх товарів, категорій та сторінок')
                ->action(function () {
                    // Clear existing
                    SeoMeta::truncate();

                    $generator = new SeoMetaGenerator;

                    // Generate for categories
                    $categoriesCount = $generator->generateBulkForCategories('uk');

                    // Generate for products
                    $productsCount = $generator->generateBulkForProducts('uk');

                    // Generate for pages
                    $pagesCount = $generator->generateBulkForPages([
                        'homepage' => [],
                        'specials' => [],
                        'hits' => [],
                        'new' => [],
                    ], 'uk');

                    Notification::make()
                        ->title('SEO перегенеровано')
                        ->body("Створено SEO для {$productsCount} товарів, {$categoriesCount} категорій, {$pagesCount} сторінок")
                        ->success()
                        ->duration(10000)
                        ->send();
                }),
            Action::make('save')
                ->label('💾 Зберегти шаблони')
                ->color('success')
                ->action('save'),
            Action::make('reset')
                ->label('🔄 Скинути до стандартних')
                ->color('gray')
                ->action('resetToDefaults'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if ($key === 'robots_txt_content') {
                // Пишемо туди ж, звідки читає getCurrentRobotsContent() і
                // звідки реально серветься /robots.txt (public/, не storage/).
                @file_put_contents(public_path('robots.txt'), $value);

                continue;
            }

            DisplaySetting::updateOrCreate(
                ['key' => 'seo_'.$key],
                [
                    'value' => (string) $value,
                    'title' => $this->getSettingTitle($key),
                    'description' => $this->getSettingDescription($key),
                    'type' => 'string',
                    'group' => 'seo_templates',
                ]
            );
        }

        $stats = $this->getSeoStats();

        Notification::make()
            ->title('Шаблони збережено')
            ->body("SEO шаблони оновлено. Товарів з SEO: {$stats['products_with_seo']}/{$stats['total_products']}, Категорій: {$stats['categories_with_seo']}/{$stats['total_categories']}")
            ->success()
            ->duration(8000)
            ->send();
    }

    public function resetToDefaults(): void
    {
        $defaults = [];
        foreach (\App\Support\SeoTemplates::DEFAULTS as $key => $fields) {
            foreach (['title', 'description'] as $field) {
                $defaults[$this->fieldName($key, $field)] = $fields[$field] ?? '';
            }
        }
        $defaults['robots_txt_content'] = $this->getDefaultRobotsContent();

        $this->form->fill($defaults);

        Notification::make()
            ->title('Шаблони скинуто')
            ->body('Встановлено стандартні SEO шаблони')
            ->info()
            ->send();
    }

    private function getCurrentRobotsContent(): string
    {
        $robotsPath = public_path('robots.txt');
        if (file_exists($robotsPath)) {
            return file_get_contents($robotsPath);
        }

        return $this->getDefaultRobotsContent();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('💾 Зберегти')
                ->submit('save'),
        ];
    }

    private function getDefaultRobotsContent(): string
    {
        return 'User-agent: *
Allow: /

# Заборонені розділи
Disallow: /admin/
Disallow: /cart
Disallow: /checkout
Disallow: /user/
Disallow: /login
Disallow: /register
Disallow: /search?*

# Дозволені для індексації
Allow: /assets/
Allow: /storage/
Allow: /sitemap*.xml

# Затримка для роботів
Crawl-delay: 1

# Sitemap
Sitemap: '.url('/sitemap.xml').'

# Додаткові sitemap
Sitemap: '.url('/sitemap-categories.xml').'
Sitemap: '.url('/sitemap-products.xml').'
Sitemap: '.url('/sitemap-main.xml').'

# Google бот
User-agent: Googlebot
Allow: /

# Bing бот  
User-agent: Bingbot
Allow: /

# Яндекс бот
User-agent: YandexBot
Allow: /
';
    }

    private function getSettingTitle(string $key): string
    {
        $titles = [
            'category_title_template' => 'Шаблон заголовку категорії',
            'category_description_template' => 'Шаблон опису категорії',
            'product_title_template' => 'Шаблон заголовку товару',
            'product_description_template' => 'Шаблон опису товару',
            'brand_title_template' => 'Шаблон заголовку бренду',
            'brand_description_template' => 'Шаблон опису бренду',
            'brands_title_template' => 'Шаблон заголовку списку брендів',
            'brands_description_template' => 'Шаблон опису списку брендів',
            'car_title_template' => 'Шаблон заголовку каталогу за авто',
            'car_description_template' => 'Шаблон опису каталогу за авто',
            'search_title_template' => 'Шаблон заголовку пошуку',
            'search_description_template' => 'Шаблон опису пошуку',
            'page_title_template' => 'Шаблон заголовку сторінки',
            'page_description_template' => 'Шаблон опису сторінки',
            'blog_title_template' => 'Шаблон заголовку блогу',
            'blog_description_template' => 'Шаблон опису блогу',
            'blog_post_title_template' => 'Шаблон заголовку статті блогу',
            'blog_post_description_template' => 'Шаблон опису статті блогу',
            'home_title' => 'Заголовок головної сторінки',
            'home_description' => 'Опис головної сторінки',
        ];

        return $titles[$key] ?? $key;
    }

    private function getSettingDescription(string $key): string
    {
        $descriptions = [
            'category_title_template' => 'Шаблон для автогенерації заголовків категорій',
            'category_description_template' => 'Шаблон для автогенерації описів категорій',
            'product_title_template' => 'Шаблон для автогенерації заголовків товарів',
            'product_description_template' => 'Шаблон для автогенерації описів товарів',
            'page_title_template' => 'Шаблон для автогенерації заголовків статичних сторінок',
            'page_description_template' => 'Шаблон для автогенерації описів статичних сторінок',
            'home_title' => 'Статичний заголовок для головної сторінки',
            'home_description' => 'Статичний опис для головної сторінки',
        ];

        return $descriptions[$key] ?? '';
    }

    public function getSeoStats(): array
    {
        return [
            'total_products' => Product::count(),
            'products_with_seo' => SeoMeta::where('seoable_type', Product::class)->count(),
            'total_categories' => Category::count(),
            'categories_with_seo' => SeoMeta::where('seoable_type', Category::class)->count(),
            'total_pages' => SeoMeta::whereNotNull('page_type')->count(),
            'auto_generated' => SeoMeta::where('auto_generated', true)->count(),
        ];
    }
}
