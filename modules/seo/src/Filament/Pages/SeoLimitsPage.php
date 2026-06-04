<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\DisplaySetting;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Services\SeoMetaGenerator;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SeoLimitsPage extends Page implements HasActions, HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?string $title = 'SEO Ліміти';

    protected static ?string $navigationLabel = 'Ліміти символів';

    protected static string $view = 'filament.pages.seo-limits-page';

    protected static ?int $navigationSort = 140;

    public ?array $data = [];

    protected function getViewData(): array
    {
        return [
            'productsWithSeoCount' => Product::whereNotNull('meta_title')->count(),
            'categoriesWithSeoCount' => Category::whereNotNull('meta_title')->count(),
            'seoMetaCount' => SeoMeta::count(),
        ];
    }

    public function mount(): void
    {
        $this->form->fill([
            'seo_title_min_length' => DisplaySetting::get('seo_title_min_length', 10),
            'seo_title_max_length' => DisplaySetting::get('seo_title_max_length', 60),
            'seo_description_min_length' => DisplaySetting::get('seo_description_min_length', 50),
            'seo_description_max_length' => DisplaySetting::get('seo_description_max_length', 160),
            'seo_keywords_max_count' => DisplaySetting::get('seo_keywords_max_count', 10),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ліміти символів для SEO')
                    ->description('Налаштуйте ліміти символів для автоматичної генерації та валідації SEO контенту')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('seo_title_min_length')
                                    ->label('Мінімальна довжина заголовка')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->default(10)
                                    ->helperText('Мінімальна кількість символів для SEO заголовка')
                                    ->required(),

                                TextInput::make('seo_title_max_length')
                                    ->label('Максимальна довжина заголовка')
                                    ->numeric()
                                    ->minValue(10)
                                    ->maxValue(200)
                                    ->default(60)
                                    ->helperText('Рекомендовано: 50-60 символів для Google')
                                    ->required(),

                                TextInput::make('seo_description_min_length')
                                    ->label('Мінімальна довжина опису')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(200)
                                    ->default(50)
                                    ->helperText('Мінімальна кількість символів для SEO опису')
                                    ->required(),

                                TextInput::make('seo_description_max_length')
                                    ->label('Максимальна довжина опису')
                                    ->numeric()
                                    ->minValue(50)
                                    ->maxValue(300)
                                    ->default(160)
                                    ->helperText('Рекомендовано: 150-160 символів для Google')
                                    ->required(),

                                TextInput::make('seo_keywords_max_count')
                                    ->label('Максимальна кількість ключових слів')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(20)
                                    ->default(10)
                                    ->helperText('Рекомендована кількість ключових слів')
                                    ->required(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            DisplaySetting::set($key, $value);
        }

        Notification::make()
            ->title('Налаштування збережено')
            ->body('SEO ліміти оновлено успішно')
            ->success()
            ->send();
    }

    public function getActions(): array
    {
        return [
            Action::make('save')
                ->label('Зберегти налаштування')
                ->color('primary')
                ->action('save'),

            Action::make('apply_limits')
                ->label('Застосувати ліміти до існуючих записів')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Застосувати нові ліміти')
                ->modalDescription('Це обріже всі існуючі SEO записи відповідно до нових лімітів')
                ->action(function () {
                    $generator = new SeoMetaGenerator;
                    $count = $generator->applyLimitsToExistingRecords();

                    Notification::make()
                        ->title('Ліміти застосовано')
                        ->body("Оновлено {$count} записів відповідно до нових лімітів")
                        ->success()
                        ->send();
                }),
        ];
    }
}
