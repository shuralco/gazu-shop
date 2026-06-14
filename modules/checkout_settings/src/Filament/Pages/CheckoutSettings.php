<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use App\Support\Checkout\CheckoutConfig;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Налаштування кошика та оформлення замовлення (модуль checkout_settings).
 * Зберігає все в DisplaySetting; checkout/cart читають через CheckoutConfig.
 */
class CheckoutSettings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Кошик та оформлення';

    protected static ?string $title = 'Кошик та оформлення';

    protected static ?string $navigationGroup = 'Оплата і доставка';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.checkout-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'checkout_min_order' => (int) CheckoutConfig::minOrderAmount(),
            'checkout_free_shipping_threshold' => (int) CheckoutConfig::freeShippingThreshold(),
            'checkout_oneclick_enabled' => CheckoutConfig::oneClickEnabled(),
            'checkout_promo_enabled' => CheckoutConfig::promoEnabled(),
            'checkout_qty_min' => CheckoutConfig::qtyMin(),
            'checkout_qty_max' => CheckoutConfig::qtyMax(),
            'checkout_fields' => array_values(CheckoutConfig::fields()),
            'checkout_custom_fields' => CheckoutConfig::customFields(),
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()->tabs([
                    Forms\Components\Tabs\Tab::make('Кошик')
                        ->icon('heroicon-o-shopping-bag')
                        ->schema([
                            Forms\Components\TextInput::make('checkout_min_order')
                                ->label('Мінімальна сума замовлення')
                                ->numeric()->minValue(0)->suffix('грн')->default(0)
                                ->helperText('0 = без обмеження. Нижче цієї суми оформлення блокується.'),
                            Forms\Components\TextInput::make('checkout_free_shipping_threshold')
                                ->label('Поріг безкоштовної доставки')
                                ->numeric()->minValue(0)->suffix('грн')->default(0)
                                ->helperText('0 = вимкнено. Від цієї суми показуємо «безкоштовна доставка».'),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Toggle::make('checkout_oneclick_enabled')
                                    ->label('Кнопка «Купити в 1 клік»')->default(true),
                                Forms\Components\Toggle::make('checkout_promo_enabled')
                                    ->label('Поле промокоду на checkout')->default(true),
                            ]),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('checkout_qty_min')
                                    ->label('Мін. кількість одного товару')
                                    ->numeric()->minValue(1)->default(1),
                                Forms\Components\TextInput::make('checkout_qty_max')
                                    ->label('Макс. кількість одного товару')
                                    ->numeric()->minValue(0)->default(0)
                                    ->helperText('0 = без обмеження'),
                            ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('Поля оформлення')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Forms\Components\Placeholder::make('fields_hint')
                                ->label('')
                                ->content('Імʼя та телефон — завжди обовʼязкові. Нижче керуйте рештою полів.'),
                            Forms\Components\Repeater::make('checkout_fields')
                                ->label('')
                                ->addable(false)->deletable(false)->reorderable(false)
                                ->schema([
                                    Forms\Components\Hidden::make('key'),
                                    Forms\Components\TextInput::make('label')->label('Назва поля')->required(),
                                    Forms\Components\TextInput::make('placeholder')->label('Підказка (placeholder)'),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\Toggle::make('visible')->label('Показувати')->default(true),
                                        Forms\Components\Toggle::make('required')->label('Обовʼязкове')->default(false),
                                    ]),
                                ])
                                ->itemLabel(fn (array $state): string => $state['label'] ?? ($state['key'] ?? 'Поле'))
                                ->columns(1),
                        ]),

                    Forms\Components\Tabs\Tab::make('Кастомні поля')
                        ->icon('heroicon-o-plus-circle')
                        ->schema([
                            Forms\Components\Repeater::make('checkout_custom_fields')
                                ->label('Додаткові поля (зберігаються в коментарі до замовлення)')
                                ->schema([
                                    Forms\Components\TextInput::make('key')->label('Технічний ключ')
                                        ->required()->helperText('Латиниця/цифри, напр. edrpou'),
                                    Forms\Components\TextInput::make('label')->label('Назва поля')->required(),
                                    Forms\Components\TextInput::make('placeholder')->label('Підказка'),
                                    Forms\Components\Toggle::make('required')->label('Обовʼязкове')->default(false),
                                ])
                                ->itemLabel(fn (array $state): string => $state['label'] ?? 'Нове поле')
                                ->addActionLabel('Додати поле')
                                ->columns(2)
                                ->defaultItems(0),
                        ]),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DisplaySetting::set('checkout_min_order', (int) ($data['checkout_min_order'] ?? 0), 'Мін. сума замовлення');
        DisplaySetting::set('checkout_free_shipping_threshold', (int) ($data['checkout_free_shipping_threshold'] ?? 0), 'Поріг безкоштовної доставки');
        DisplaySetting::set('checkout_oneclick_enabled', (bool) ($data['checkout_oneclick_enabled'] ?? true), 'Кнопка 1-клік');
        DisplaySetting::set('checkout_promo_enabled', (bool) ($data['checkout_promo_enabled'] ?? true), 'Поле промокоду');
        DisplaySetting::set('checkout_qty_min', max(1, (int) ($data['checkout_qty_min'] ?? 1)), 'Мін. кількість товару');
        DisplaySetting::set('checkout_qty_max', max(0, (int) ($data['checkout_qty_max'] ?? 0)), 'Макс. кількість товару');

        // Поля — лишаємо тільки керовані ключі, нормалізуємо типи.
        $fields = [];
        foreach ((array) ($data['checkout_fields'] ?? []) as $row) {
            $key = $row['key'] ?? null;
            if (! in_array($key, CheckoutConfig::MANAGEABLE_FIELDS, true)) {
                continue;
            }
            $fields[] = [
                'key' => $key,
                'label' => (string) ($row['label'] ?? ''),
                'placeholder' => (string) ($row['placeholder'] ?? ''),
                'visible' => (bool) ($row['visible'] ?? true),
                'required' => (bool) ($row['required'] ?? false),
            ];
        }
        DisplaySetting::set('checkout_fields', $fields, 'Поля оформлення');

        // Кастомні поля — нормалізація ключів.
        $custom = [];
        foreach ((array) ($data['checkout_custom_fields'] ?? []) as $row) {
            $rawKey = (string) ($row['key'] ?? '');
            $key = preg_replace('/[^a-z0-9_]/', '', \Illuminate\Support\Str::slug($rawKey, '_'));
            if ($key === '' || trim((string) ($row['label'] ?? '')) === '') {
                continue;
            }
            $custom[] = [
                'key' => $key,
                'label' => (string) $row['label'],
                'placeholder' => (string) ($row['placeholder'] ?? ''),
                'required' => (bool) ($row['required'] ?? false),
            ];
        }
        DisplaySetting::set('checkout_custom_fields', $custom, 'Кастомні поля checkout');

        DisplaySetting::flushSettingsCache();

        Notification::make()->title('Збережено')->body('Налаштування кошика та оформлення оновлено.')->success()->send();
    }
}
