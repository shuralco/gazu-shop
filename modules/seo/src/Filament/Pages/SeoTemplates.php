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
        $this->form->fill([
            'category_title_template' => DisplaySetting::get('seo_category_title_template', '%s | SimpleShop'),
            'category_description_template' => DisplaySetting::get('seo_category_description_template', 'Великий вибір товарів у категорії %s. Швидка доставка по Україні. Гарантія якості.'),
            'product_title_template' => DisplaySetting::get('seo_product_title_template', 'Купити %s за %s грн | SimpleShop'),
            'product_description_template' => DisplaySetting::get('seo_product_description_template', 'Купити %s за найкращою ціною %s грн. %s. Швидка доставка по Україні.'),
            'page_title_template' => DisplaySetting::get('seo_page_title_template', '%s | SimpleShop'),
            'page_description_template' => DisplaySetting::get('seo_page_description_template', '%s - корисна інформація від SimpleShop.'),
            'home_title' => DisplaySetting::get('seo_home_title', 'SimpleShop - Інтернет-магазин якісних товарів'),
            'home_description' => DisplaySetting::get('seo_home_description', 'Великий вибір якісних товарів за найкращими цінами. Швидка доставка по Україні. Гарантія якості.'),
            'robots_txt_content' => $this->getCurrentRobotsContent(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('🏠 Головна сторінка')
                    ->schema([
                        TextInput::make('home_title')
                            ->label('Заголовок головної сторінки')
                            ->maxLength(60)
                            ->required(),
                        Textarea::make('home_description')
                            ->label('Опис головної сторінки')
                            ->maxLength(160)
                            ->rows(3)
                            ->required(),
                    ])
                    ->columns(1),

                Section::make('🏷️ Шаблони для категорій')
                    ->schema([
                        TextInput::make('category_title_template')
                            ->label('Шаблон заголовку')
                            ->placeholder('%s - назва категорії')
                            ->helperText('Використовуйте %s для підстановки назви категорії')
                            ->required(),
                        Textarea::make('category_description_template')
                            ->label('Шаблон опису')
                            ->placeholder('Великий вибір товарів у категорії %s')
                            ->helperText('Використовуйте %s для підстановки назви категорії')
                            ->rows(3)
                            ->required(),
                    ])
                    ->columns(1),

                Section::make('📦 Шаблони для товарів')
                    ->schema([
                        TextInput::make('product_title_template')
                            ->label('Шаблон заголовку')
                            ->placeholder('Купити %s за %s грн')
                            ->helperText('Перший %s - назва товару, другий %s - ціна')
                            ->required(),
                        Textarea::make('product_description_template')
                            ->label('Шаблон опису')
                            ->placeholder('Купити %s за найкращою ціною %s грн. %s.')
                            ->helperText('Перший %s - назва, другий %s - ціна, третій %s - опис')
                            ->rows(3)
                            ->required(),
                    ])
                    ->columns(1),

                Section::make('📄 Шаблони для статичних сторінок')
                    ->schema([
                        TextInput::make('page_title_template')
                            ->label('Шаблон заголовку')
                            ->placeholder('%s | SimpleShop')
                            ->helperText('Використовуйте %s для підстановки назви сторінки')
                            ->required(),
                        Textarea::make('page_description_template')
                            ->label('Шаблон опису')
                            ->placeholder('%s - корисна інформація від SimpleShop')
                            ->helperText('Використовуйте %s для підстановки назви сторінки')
                            ->rows(3)
                            ->required(),
                    ])
                    ->columns(1),

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
                \Illuminate\Support\Facades\Storage::disk('public')->put('robots.txt', $value);

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
        $this->form->fill([
            'category_title_template' => '%s | SimpleShop',
            'category_description_template' => 'Великий вибір товарів у категорії %s. Швидка доставка по Україні. Гарантія якості.',
            'product_title_template' => 'Купити %s за %s грн | SimpleShop',
            'product_description_template' => 'Купити %s за найкращою ціною %s грн. %s. Швидка доставка по Україні.',
            'page_title_template' => '%s | SimpleShop',
            'page_description_template' => '%s - корисна інформація від SimpleShop.',
            'home_title' => 'SimpleShop - Інтернет-магазин якісних товарів',
            'home_description' => 'Великий вибір якісних товарів за найкращими цінами. Швидка доставка по Україні. Гарантія якості.',
            'robots_txt_content' => $this->getDefaultRobotsContent(),
        ]);

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
            'page_title_template' => 'Шаблон заголовку сторінки',
            'page_description_template' => 'Шаблон опису сторінки',
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
