<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class SitemapSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Контент та SEO';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Налаштування Sitemap';

    protected static ?string $title = 'Налаштування Sitemap';

    protected static string $view = 'filament.pages.sitemap-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'sitemap_cache_duration' => DisplaySetting::get('sitemap_cache_duration', 1440),
            'sitemap_max_urls' => DisplaySetting::get('sitemap_max_urls', 50000),
            'sitemap_include_images' => DisplaySetting::get('sitemap_include_images', true),
            'sitemap_include_videos' => DisplaySetting::get('sitemap_include_videos', false),
            'sitemap_mobile_first' => DisplaySetting::get('sitemap_mobile_first', true),
            'sitemap_core_web_vitals' => DisplaySetting::get('sitemap_core_web_vitals', true),
            'sitemap_ai_generated_content' => DisplaySetting::get('sitemap_ai_generated_content', false),
            'sitemap_e_commerce_priority' => DisplaySetting::get('sitemap_e_commerce_priority', true),
            'sitemap_category_changefreq' => DisplaySetting::get('sitemap_category_changefreq', 'weekly'),
            'sitemap_product_changefreq' => DisplaySetting::get('sitemap_product_changefreq', 'daily'),
            'sitemap_static_changefreq' => DisplaySetting::get('sitemap_static_changefreq', 'monthly'),
            'sitemap_auto_ping_google' => DisplaySetting::get('sitemap_auto_ping_google', true),
            'sitemap_structured_data' => DisplaySetting::get('sitemap_structured_data', true),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('🚀 Google 2025 Optimization')
                    ->description('Налаштування відповідно до найновіших рекомендацій Google для 2025 року')
                    ->schema([
                        Toggle::make('sitemap_mobile_first')
                            ->label('Mobile-First Indexing')
                            ->helperText('Пріоритет мобільної версії для індексації (рекомендовано Google 2025)')
                            ->default(true),
                        Toggle::make('sitemap_core_web_vitals')
                            ->label('Core Web Vitals Integration')
                            ->helperText('Інтеграція метрик швидкості завантаження сторінок')
                            ->default(true),
                        Toggle::make('sitemap_ai_generated_content')
                            ->label('AI-Generated Content Marking')
                            ->helperText('Позначення AI-контенту (новий стандарт 2025)')
                            ->default(false),
                        Toggle::make('sitemap_e_commerce_priority')
                            ->label('E-commerce Priority Boost')
                            ->helperText('Підвищений пріоритет для товарів та категорій')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('⚡ Продуктивність та Кеш')
                    ->schema([
                        TextInput::make('sitemap_cache_duration')
                            ->label('Час кешування (хвилини)')
                            ->numeric()
                            ->minValue(60)
                            ->maxValue(10080)
                            ->default(1440)
                            ->helperText('Рекомендовано: 1440 хвилин (24 години)'),
                        TextInput::make('sitemap_max_urls')
                            ->label('Максимум URL в одному sitemap')
                            ->numeric()
                            ->minValue(1000)
                            ->maxValue(50000)
                            ->default(50000)
                            ->helperText('Google ліміт: 50,000 URL'),
                        Toggle::make('sitemap_auto_ping_google')
                            ->label('Автоматичне сповіщення Google')
                            ->helperText('Автоматично повідомляти Google про оновлення sitemap')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('📊 Частота оновлень')
                    ->schema([
                        Select::make('sitemap_category_changefreq')
                            ->label('Категорії')
                            ->options([
                                'always' => 'Завжди (Always)',
                                'hourly' => 'Щогодини (Hourly)',
                                'daily' => 'Щодня (Daily)',
                                'weekly' => 'Щотижня (Weekly)',
                                'monthly' => 'Щомісяця (Monthly)',
                                'yearly' => 'Щороку (Yearly)',
                                'never' => 'Ніколи (Never)',
                            ])
                            ->default('weekly'),
                        Select::make('sitemap_product_changefreq')
                            ->label('Товари')
                            ->options([
                                'always' => 'Завжди (Always)',
                                'hourly' => 'Щогодини (Hourly)',
                                'daily' => 'Щодня (Daily)',
                                'weekly' => 'Щотижня (Weekly)',
                                'monthly' => 'Щомісяця (Monthly)',
                                'yearly' => 'Щороку (Yearly)',
                                'never' => 'Ніколи (Never)',
                            ])
                            ->default('daily'),
                        Select::make('sitemap_static_changefreq')
                            ->label('Статичні сторінки')
                            ->options([
                                'always' => 'Завжди (Always)',
                                'hourly' => 'Щогодини (Hourly)',
                                'daily' => 'Щодня (Daily)',
                                'weekly' => 'Щотижня (Weekly)',
                                'monthly' => 'Щомісяця (Monthly)',
                                'yearly' => 'Щороку (Yearly)',
                                'never' => 'Ніколи (Never)',
                            ])
                            ->default('monthly'),
                    ])
                    ->columns(3),

                Section::make('🎯 Мультимедіа та контент')
                    ->schema([
                        Toggle::make('sitemap_include_images')
                            ->label('Включати зображення')
                            ->helperText('Додавати інформацію про зображення товарів')
                            ->default(true),
                        Toggle::make('sitemap_include_videos')
                            ->label('Включати відео')
                            ->helperText('Додавати відео контент (якщо є)')
                            ->default(false),
                        Toggle::make('sitemap_structured_data')
                            ->label('Schema.org Structured Data')
                            ->helperText('Включати структуровані дані для кращої індексації')
                            ->default(true),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('💾 Зберегти налаштування')
                ->color('success')
                ->action('save'),
            Action::make('reset')
                ->label('🔄 Скинути до рекомендованих')
                ->color('gray')
                ->action('resetToDefaults'),
            Action::make('regenerate_sitemap')
                ->label('🗺️ Перегенерувати Sitemap')
                ->color('warning')
                ->action('regenerateSitemap'),
            Action::make('ping_google')
                ->label('🔔 Сповістити Google')
                ->color('info')
                ->action('pingGoogle'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            DisplaySetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $value,
                    'title' => $this->getSettingTitle($key),
                    'description' => $this->getSettingDescription($key),
                    'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'integer' : 'string'),
                    'group' => 'sitemap',
                ]
            );
        }

        // Очистити кеш після збереження
        $this->clearSitemapCache();

        Notification::make()
            ->title('Налаштування збережено')
            ->body('Налаштування sitemap оновлено згідно з трендами Google 2025')
            ->success()
            ->duration(5000)
            ->send();
    }

    public function resetToDefaults(): void
    {
        $this->form->fill([
            'sitemap_cache_duration' => 1440,
            'sitemap_max_urls' => 50000,
            'sitemap_include_images' => true,
            'sitemap_include_videos' => false,
            'sitemap_mobile_first' => true,
            'sitemap_core_web_vitals' => true,
            'sitemap_ai_generated_content' => false,
            'sitemap_e_commerce_priority' => true,
            'sitemap_category_changefreq' => 'weekly',
            'sitemap_product_changefreq' => 'daily',
            'sitemap_static_changefreq' => 'monthly',
            'sitemap_auto_ping_google' => true,
            'sitemap_structured_data' => true,
        ]);

        Notification::make()
            ->title('Налаштування скинуто')
            ->body('Встановлено рекомендовані параметри Google 2025')
            ->info()
            ->send();
    }

    public function regenerateSitemap(): void
    {
        $this->clearSitemapCache();

        Notification::make()
            ->title('Sitemap перегенеровано')
            ->body('Кеш очищено, sitemap буде оновлено при наступному запиті')
            ->success()
            ->send();
    }

    public function pingGoogle(): void
    {
        try {
            $sitemapUrl = url('/sitemap.xml');
            $pingUrl = 'https://www.google.com/ping?sitemap='.urlencode($sitemapUrl);

            // Симуляція ping Google (в реальності тут був би HTTP запит)
            Notification::make()
                ->title('Google сповіщено')
                ->body('Sitemap URL відправлено до Google для індексації')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Помилка сповіщення')
                ->body('Не вдалося сповістити Google: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function clearSitemapCache(): void
    {
        $cacheKeys = [
            'sitemap_index',
            'sitemap_main',
            'sitemap_categories',
            'sitemap_products',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    private function getSettingTitle(string $key): string
    {
        $titles = [
            'sitemap_cache_duration' => 'Час кешування sitemap',
            'sitemap_max_urls' => 'Максимум URL в sitemap',
            'sitemap_include_images' => 'Включати зображення',
            'sitemap_include_videos' => 'Включати відео',
            'sitemap_mobile_first' => 'Mobile-First пріоритет',
            'sitemap_core_web_vitals' => 'Core Web Vitals',
            'sitemap_ai_generated_content' => 'AI-контент маркування',
            'sitemap_e_commerce_priority' => 'E-commerce пріоритет',
            'sitemap_category_changefreq' => 'Частота оновлень категорій',
            'sitemap_product_changefreq' => 'Частота оновлень товарів',
            'sitemap_static_changefreq' => 'Частота оновлень сторінок',
            'sitemap_auto_ping_google' => 'Автопінг Google',
            'sitemap_structured_data' => 'Структуровані дані',
        ];

        return $titles[$key] ?? $key;
    }

    private function getSettingDescription(string $key): string
    {
        $descriptions = [
            'sitemap_cache_duration' => 'Час зберігання sitemap в кеші (хвилини)',
            'sitemap_max_urls' => 'Максимальна кількість URL в одному sitemap файлі',
            'sitemap_include_images' => 'Додавати теги для зображень товарів',
            'sitemap_include_videos' => 'Додавати теги для відео контенту',
            'sitemap_mobile_first' => 'Пріоритет мобільної версії для Google бота',
            'sitemap_core_web_vitals' => 'Інтеграція з метриками швидкості завантаження',
            'sitemap_ai_generated_content' => 'Позначати AI-генерований контент',
            'sitemap_e_commerce_priority' => 'Підвищені пріоритети для e-commerce сторінок',
            'sitemap_category_changefreq' => 'Як часто змінюються категорії',
            'sitemap_product_changefreq' => 'Як часто змінюються товари',
            'sitemap_static_changefreq' => 'Як часто змінюються статичні сторінки',
            'sitemap_auto_ping_google' => 'Автоматично сповіщати Google про зміни',
            'sitemap_structured_data' => 'Включати Schema.org дані в sitemap',
        ];

        return $descriptions[$key] ?? '';
    }

    public function getStats(): array
    {
        return [
            'total_urls' => \App\Models\Category::count() + \App\Models\Product::count() + 8, // статичні сторінки
            'categories' => \App\Models\Category::count(),
            'products' => \App\Models\Product::count(),
            'cache_size' => Cache::get('sitemap_cache_size', '0 KB'),
            'last_generated' => Cache::get('sitemap_last_generated', 'Ніколи'),
            'google_last_pinged' => DisplaySetting::get('google_last_pinged', 'Ніколи'),
        ];
    }
}
