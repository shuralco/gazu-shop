<?php

namespace App\Filament\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Database\Seeders\ChineseAutoPartsSeeder;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

/**
 * Admin-сторінка для одно-кнопкового запуску демо-сидера каталогу.
 * Показує поточну статистику (категорії/бренди/товари) + дозволяє
 * відсіяти та заповнити демо-каталог одним кліком.
 */
class DemoCatalogGenerator extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Демо-каталог';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $title = 'Генератор демо-каталогу';

    protected static ?int $navigationSort = 130;

    // Прибрано з меню (службовий інструмент). URL лишається доступним.
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $slug = 'demo-catalog-generator';

    protected static string $view = 'filament.pages.demo-catalog-generator';

    public ?array $data = ['profile' => 'chinese', 'wipe_existing' => true];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Профіль каталогу')
                    ->description('Який тип демо-даних згенерувати.')
                    ->schema([
                        Forms\Components\Radio::make('profile')
                            ->label('Профіль')
                            ->options([
                                'chinese' => '🇨🇳 Запчастини для китайських авто (BYD/Chery/Geely/Haval...)',
                            ])
                            ->default('chinese')
                            ->required()
                            ->descriptions([
                                'chinese' => '8 L1 категорій → ~30 L2 → ~50 L3, 38 брендів, 600+ SKU з compatibility до китайських марок.',
                            ]),
                        Forms\Components\Toggle::make('wipe_existing')
                            ->label('Видалити існуючі категорії/бренди/товари перед заповненням')
                            ->helperText('Inventory, products, brands, categories будуть очищені (замовлення зберігаються).')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Поточна статистика')
                    ->schema([
                        Forms\Components\Placeholder::make('stats')
                            ->label('')
                            ->content(fn () => $this->statsHtml()),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('generate')
                ->label('Згенерувати каталог')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Підтвердження')
                ->modalDescription('Якщо ввімкнено «Видалити існуючі» — поточні категорії, бренди, товари та inventory будуть видалені. Замовлення (orders) залишаться. Продовжити?')
                ->modalSubmitActionLabel('Так, згенерувати')
                ->action(fn () => $this->generate()),

            Actions\Action::make('generate_seo')
                ->label('Згенерувати SEO meta')
                ->icon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Згенерувати SEO meta для всіх категорій і товарів')
                ->modalDescription('Перезапише title/description/keywords у seo_metas згідно з шаблонами. Дві мови (uk/en).')
                ->action(fn () => $this->generateSeo()),
        ];
    }

    public function generateSeo(): void
    {
        try {
            $gen = new \App\Services\SeoMetaGenerator;
            $catsUk = $gen->generateBulkForCategories('uk');
            $catsEn = $gen->generateBulkForCategories('en');
            $prodUk = $gen->generateBulkForProducts('uk');
            $prodEn = $gen->generateBulkForProducts('en');
            $brandsUk = $gen->generateBulkForBrands('uk');
            $brandsEn = $gen->generateBulkForBrands('en');

            Notification::make()
                ->title('SEO згенеровано')
                ->body(sprintf(
                    'Категорії: %d UK + %d EN · Товари: %d UK + %d EN · Бренди: %d UK + %d EN',
                    $catsUk, $catsEn, $prodUk, $prodEn, $brandsUk, $brandsEn
                ))
                ->success()->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Помилка SEO генерації')
                ->body($e->getMessage())
                ->danger()->send();
            report($e);
        }
    }

    public function generate(): void
    {
        $state = $this->form->getState();
        $profile = $state['profile'] ?? 'chinese';

        if ($profile !== 'chinese') {
            Notification::make()->title('Невідомий профіль')->danger()->send();
            return;
        }

        if (! ($state['wipe_existing'] ?? false)) {
            Notification::make()
                ->title('Тільки wipe-режим')
                ->body('Поки що seeder підтримує лише режим повної переустановки. Увімкніть «Видалити існуючі».')
                ->warning()->send();
            return;
        }

        try {
            // Run seeder synchronously
            $seeder = app(ChineseAutoPartsSeeder::class);
            $seeder->setCommand(new \Illuminate\Console\Command());
            $seeder->run();

            Notification::make()
                ->title('Готово')
                ->body(sprintf(
                    'Створено: %d категорій, %d брендів, %d товарів.',
                    Category::count(), Brand::count(), Product::count()
                ))
                ->success()->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Помилка генерації')
                ->body($e->getMessage())
                ->danger()->send();
            report($e);
        }
    }

    private function statsHtml(): \Illuminate\Support\HtmlString
    {
        $cats = Category::count();
        $catsLeafs = Category::query()->whereDoesntHave('children')->count();
        $brands = Brand::count();
        $products = Product::count();
        $inv = Inventory::count();

        return new \Illuminate\Support\HtmlString(<<<HTML
            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;font-size:13px;">
                <div><div style="font-size:11px;text-transform:uppercase;color:#888;">Категорій</div><div style="font-size:22px;font-weight:700;">{$cats}</div></div>
                <div><div style="font-size:11px;text-transform:uppercase;color:#888;">З них leaf</div><div style="font-size:22px;font-weight:700;">{$catsLeafs}</div></div>
                <div><div style="font-size:11px;text-transform:uppercase;color:#888;">Брендів</div><div style="font-size:22px;font-weight:700;">{$brands}</div></div>
                <div><div style="font-size:11px;text-transform:uppercase;color:#888;">Товарів</div><div style="font-size:22px;font-weight:700;">{$products}</div></div>
                <div><div style="font-size:11px;text-transform:uppercase;color:#888;">Inventory</div><div style="font-size:22px;font-weight:700;">{$inv}</div></div>
            </div>
        HTML);
    }
}
