<?php

namespace App\Filament\Widgets;

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
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class SeoOverviewWidget extends Widget implements HasActions, HasForms
{
    public static function canView(): bool
    {
        return \App\Support\Access\AccessControl::can('SeoMetaResource', 'view');
    }

    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.seo-overview';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;

    public function generateAllCategoriesAction(): Action
    {
        return Action::make('generate_all_categories')
            ->label('Генерувати SEO для категорій')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Генерація SEO для всіх категорій')
            ->modalDescription('Це створить або оновить SEO дані для всіх активних категорій (UK + EN)')
            ->action(function () {
                $generator = new SeoMetaGenerator;
                $ukCount = $generator->generateBulkForCategories('uk');
                $enCount = $generator->generateBulkForCategories('en');

                Notification::make()
                    ->title('SEO для категорій згенеровано')
                    ->body("{$ukCount} українських, {$enCount} англійських записів")
                    ->success()
                    ->send();
            });
    }

    public function generateAllProductsAction(): Action
    {
        return Action::make('generate_all_products')
            ->label('Генерувати SEO для товарів')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Генерація SEO для всіх товарів')
            ->modalDescription('Це створить або оновить SEO дані для всіх активних товарів (UK + EN)')
            ->action(function () {
                $generator = new SeoMetaGenerator;
                $ukCount = $generator->generateBulkForProducts('uk');
                $enCount = $generator->generateBulkForProducts('en');

                Notification::make()
                    ->title('SEO для товарів згенеровано')
                    ->body("{$ukCount} українських, {$enCount} англійських записів")
                    ->success()
                    ->send();
            });
    }

    public function generateStaticPagesAction(): Action
    {
        return Action::make('generate_static_pages')
            ->label('Генерувати SEO для сторінок')
            ->color('warning')
            ->requiresConfirmation()
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

                $ukCount = $generator->generateBulkForPages($staticPages, 'uk');
                $enCount = $generator->generateBulkForPages($staticPages, 'en');

                Notification::make()
                    ->title('SEO для статичних сторінок згенеровано')
                    ->body("{$ukCount} українських, {$enCount} англійських записів")
                    ->success()
                    ->send();
            });
    }

    public function clearSeoCacheAction(): Action
    {
        return Action::make('clear_seo_cache')
            ->label('Очистити SEO кеш')
            ->color('danger')
            ->action(function () {
                Cache::flush();

                Notification::make()
                    ->title('SEO кеш очищено')
                    ->success()
                    ->send();
            });
    }

    public function getStats(): array
    {
        return [
            'total_seo_records' => SeoMeta::count(),
            'categories_with_seo' => SeoMeta::where('seoable_type', Category::class)->distinct('seoable_id')->count(),
            'products_with_seo' => SeoMeta::where('seoable_type', Product::class)->distinct('seoable_id')->count(),
            'static_pages_seo' => SeoMeta::whereNull('seoable_type')->count(),
            'total_categories' => Category::where('is_active', true)->count(),
            'total_products' => Product::where('is_active', true)->count(),
            'missing_categories' => Category::where('is_active', true)->count() - SeoMeta::where('seoable_type', Category::class)->distinct('seoable_id')->count(),
            'missing_products' => Product::where('is_active', true)->count() - SeoMeta::where('seoable_type', Product::class)->distinct('seoable_id')->count(),
            'ukrainian_records' => SeoMeta::where('language', 'uk')->count(),
            'english_records' => SeoMeta::where('language', 'en')->count(),
            'sitemap_cached' => Cache::has('sitemap_index'),
        ];
    }
}
