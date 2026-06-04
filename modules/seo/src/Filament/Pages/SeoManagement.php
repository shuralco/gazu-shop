<?php

namespace App\Filament\Pages;

use App\Filament\Pages\SeoLimitsPage;
use App\Filament\Widgets\SeoOverviewWidget;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Services\SeoMetaGenerator;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class SeoManagement extends Page implements HasActions, HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?string $title = 'SEO Управління';

    protected static ?string $navigationLabel = 'SEO Dashboard';

    protected static string $view = 'filament.pages.seo-management';

    protected static ?int $navigationSort = 110;

    public function getActions(): array
    {
        return [
            Action::make('seo_limits')
                ->label('⚙️ Налаштувати ліміти')
                ->color('gray')
                ->url(SeoLimitsPage::getUrl())
                ->openUrlInNewTab(),

            Action::make('generate_all_seo')
                ->label('🚀 Генерувати весь SEO')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Масова генерація SEO')
                ->modalDescription('Це створить SEO для всіх категорій, товарів та статичних сторінок')
                ->action(function () {
                    $generator = new SeoMetaGenerator;

                    $categoriesCount = $generator->generateBulkForCategories('uk');
                    $productsCount = $generator->generateBulkForProducts('uk');

                    $staticPages = [
                        'homepage' => [],
                        'specials' => [],
                        'hits' => [],
                        'new' => [],
                        'about' => [],
                        'contacts' => [],
                        'delivery' => [],
                        'payment' => [],
                    ];
                    $pagesCount = $generator->generateBulkForPages($staticPages, 'uk');

                    Cache::flush();

                    Notification::make()
                        ->title('SEO масово згенеровано')
                        ->body("Категорії: {$categoriesCount}, Товари: {$productsCount}, Сторінки: {$pagesCount}")
                        ->success()
                        ->duration(10000)
                        ->send();
                }),

            Action::make('generate_categories_seo')
                ->label('🏷️ Категорії')
                ->color('success')
                ->action(function () {
                    $generator = new SeoMetaGenerator;
                    $count = $generator->generateBulkForCategories('uk');

                    Notification::make()
                        ->title('SEO для категорій згенеровано')
                        ->body("Оброблено категорій: {$count}")
                        ->success()
                        ->send();
                }),

            Action::make('generate_products_seo')
                ->label('📦 Товари')
                ->color('info')
                ->action(function () {
                    $generator = new SeoMetaGenerator;
                    $count = $generator->generateBulkForProducts('uk');

                    Notification::make()
                        ->title('SEO для товарів згенеровано')
                        ->body("Оброблено товарів: {$count}")
                        ->success()
                        ->send();
                }),

            Action::make('generate_pages_seo')
                ->label('📄 Сторінки')
                ->color('warning')
                ->action(function () {
                    $generator = new SeoMetaGenerator;
                    $staticPages = [
                        'homepage' => [],
                        'specials' => [],
                        'hits' => [],
                        'new' => [],
                        'about' => [],
                        'contacts' => [],
                        'delivery' => [],
                        'payment' => [],
                    ];
                    $count = $generator->generateBulkForPages($staticPages, 'uk');

                    Notification::make()
                        ->title('SEO для сторінок згенеровано')
                        ->body("Оброблено сторінок: {$count}")
                        ->success()
                        ->send();
                }),

            Action::make('clear_seo_cache')
                ->label('🧹 SEO кеш')
                ->color('gray')
                ->action(function () {
                    $cacheKeys = [
                        'sitemap_index',
                        'sitemap_main',
                        'sitemap_categories',
                        'sitemap_products',
                    ];

                    foreach ($cacheKeys as $key) {
                        Cache::forget($key);
                    }

                    Notification::make()
                        ->title('SEO кеш очищено')
                        ->success()
                        ->send();
                }),

            Action::make('clear_all_cache')
                ->label('🧹 Весь кеш')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    Cache::flush();

                    Notification::make()
                        ->title('Весь кеш очищено')
                        ->success()
                        ->send();
                }),

            Action::make('generate_robots_txt')
                ->label('🤖 Robots.txt')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Textarea::make('robots_content')
                        ->label('Вміст robots.txt')
                        ->rows(10)
                        ->default($this->getDefaultRobotsContent())
                        ->helperText('Налаштуйте правила для пошукових роботів'),
                ])
                ->action(function (array $data) {
                    \Illuminate\Support\Facades\Storage::disk('public')->put('robots.txt', $data['robots_content']);

                    Notification::make()
                        ->title('Robots.txt оновлено')
                        ->body('Файл robots.txt збережено в public/storage/')
                        ->success()
                        ->send();
                }),

            Action::make('seo_templates_settings')
                ->label('📝 Шаблони')
                ->color('info')
                ->url(fn () => route('filament.admin.pages.seo-templates')),

            Action::make('view_seo_data_table')
                ->label('📊 SEO Таблиця')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.seo-metas.index')),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            SeoOverviewWidget::class,
        ];
    }

    public function getSeoStats(): array
    {
        return [
            'total_seo' => SeoMeta::count(),
            'categories_total' => Category::where('is_active', true)->count(),
            'categories_with_seo' => SeoMeta::where('seoable_type', Category::class)->distinct('seoable_id')->count(),
            'products_total' => Product::where('is_active', true)->count(),
            'products_with_seo' => SeoMeta::where('seoable_type', Product::class)->distinct('seoable_id')->count(),
            'static_pages' => SeoMeta::whereNull('seoable_type')->count(),
            'ukrainian_seo' => SeoMeta::where('language', 'uk')->count(),
            'sitemap_entries' => SeoMeta::where('sitemap_include', true)->count(),
        ];
    }

    public function getRecentSeoUpdates()
    {
        return SeoMeta::with(['seoable'])
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($seoMeta) {
                return [
                    'id' => $seoMeta->id,
                    'title' => $seoMeta->meta_title,
                    'type' => class_basename($seoMeta->seoable_type ?? 'Static Page'),
                    'object_title' => $seoMeta->seoable?->title ?? $seoMeta->page_type,
                    'language' => $seoMeta->language,
                    'updated_at' => $seoMeta->updated_at,
                ];
            });
    }

    public function getSeoIssues(): array
    {
        $issues = [];

        // Check for missing SEO
        $categoriesWithoutSeo = Category::where('is_active', true)
            ->whereDoesntHave('seoMeta')
            ->count();

        if ($categoriesWithoutSeo > 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => "🏷️ {$categoriesWithoutSeo} категорій без SEO",
                'action' => 'generate_categories_seo',
            ];
        }

        $productsWithoutSeo = Product::where('is_active', true)
            ->whereDoesntHave('seoMeta')
            ->count();

        if ($productsWithoutSeo > 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => "📦 {$productsWithoutSeo} товарів без SEO",
                'action' => 'generate_products_seo',
            ];
        }

        // Check for SEO quality issues
        $longTitles = SeoMeta::whereRaw('LENGTH(meta_title) > 60')->count();
        if ($longTitles > 0) {
            $issues[] = [
                'type' => 'error',
                'message' => "⚠️ {$longTitles} заголовків довші 60 символів",
                'action' => 'fix_long_titles',
            ];
        }

        $longDescriptions = SeoMeta::whereRaw('LENGTH(meta_description) > 155')->count();
        if ($longDescriptions > 0) {
            $issues[] = [
                'type' => 'error',
                'message' => "⚠️ {$longDescriptions} описів довші 155 символів",
                'action' => 'fix_long_descriptions',
            ];
        }

        return $issues;
    }

    public function getDefaultRobotsContent(): string
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
}
