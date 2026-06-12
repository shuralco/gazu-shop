<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * Налаштування магазину — РОБОЧІ параметри, що реально впливають на систему:
 * листи (email_*), модерація відгуків, адмін-сповіщення, favicon/логотип листів,
 * та контактні дані для ЛИСТІВ і SEO-meta (shop_name/phone/email/address).
 *
 * Прибрано легасі-вкладки SimpleShop, що нічого не робили або дублювали інші
 * розділи: Валюти(→модуль Currency), Мови(→multilang), Доставка та оплата
 * (→shipping-модулі + Інтеграції(оплата) + gazu_payment_enabled), SEO/Аналітика
 * (→панель «Інтеграції» для GA/Pixel; robots/noindex → «GAZU візуальні блоки»),
 * Telegram-токен(→інтеграція Telegram). Контакти НА ВІТРИНІ керуються у
 * «GAZU візуальні блоки» (gazu_phone/gazu_brand_name/gazu_contacts_*), а не тут.
 */
class ShopSettings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Налаштування магазину';

    protected static ?string $title = 'Налаштування магазину';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.shop-settings';

    protected static ?string $slug = 'store-configuration';

    public ?array $data = [];

    public function mount(): void
    {
        $defaults = $this->getDefaults();

        $loaded = [];
        foreach (array_keys($defaults) as $key) {
            $loaded[$key] = DisplaySetting::get($key, $defaults[$key]);
        }

        $this->form->fill($loaded);
    }

    protected function getDefaults(): array
    {
        return [
            // General (контакти — для листів і SEO-meta; на вітрині — gazu-visual)
            'shop_name' => 'GAZU',
            'shop_phone' => '0 800 750 010',
            'shop_email' => 'info@gazu.uno',
            'shop_address' => '',
            'logo_type' => 'text',
            'logo_text' => 'GAZU',
            'logo_image' => null,
            'favicon' => null,

            // Reviews
            'reviews_moderation_enabled' => true,
            'reviews_auto_approve' => false,

            // Notifications
            'notify_new_order' => true,
            'notify_low_stock' => false,

            // Email
            'email_from_name' => 'GAZU',
            'email_from_address' => 'no-reply@gazu.uno',
            'email_reply_to' => '',
            'email_show_logo' => true,
            'email_footer_text' => '',
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('settings')
                    ->tabs([
                        $this->generalTab(),
                        $this->emailTab(),
                        $this->reviewsTab(),
                        $this->notificationsTab(),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    protected function generalTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Загальнi')
            ->label('Загальнi')
            ->icon('heroicon-o-building-storefront')
            ->schema([
                Section::make('Iнформацiя про магазин')
                    ->description('Використовується у листах і SEO-описі. Контакти, що показуються НА ВІТРИНІ (шапка/футер/Контакти), редагуються в «GAZU візуальні блоки».')
                    ->schema([
                        TextInput::make('shop_name')
                            ->label('Назва магазину')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('GAZU'),

                        TextInput::make('shop_phone')
                            ->label('Телефон магазину')
                            ->tel()
                            ->helperText('У листах і SEO. Телефон на сайті — «GAZU візуальні блоки → Шапка».')
                            ->placeholder('0 800 750 010'),

                        TextInput::make('shop_email')
                            ->label('Email магазину')
                            ->email()
                            ->required()
                            ->placeholder('info@gazu.uno'),

                        Textarea::make('shop_address')
                            ->label('Адреса магазину')
                            ->rows(3)
                            ->placeholder('м. Київ, Україна'),
                    ])
                    ->columns(2),

                Section::make('Логотип для листів та favicon')
                    ->description('Логотип САЙТУ завантажується в «GAZU візуальні блоки → Шапка». Тут — лого у email-листах і favicon.')
                    ->schema([
                        Select::make('logo_type')
                            ->label('Тип логотипу в листах')
                            ->options([
                                'text' => 'Текстовий',
                                'image' => 'Зображення',
                            ])
                            ->default('text')
                            ->reactive(),

                        TextInput::make('logo_text')
                            ->label('Текст логотипу')
                            ->placeholder('GAZU')
                            ->visible(fn (callable $get) => $get('logo_type') === 'text'),

                        FileUpload::make('logo_image')
                            ->label('Зображення логотипу (листи)')
                            ->image()
                            ->maxSize(2048)
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                            ->visible(fn (callable $get) => $get('logo_type') === 'image'),

                        FileUpload::make('favicon')
                            ->label('Favicon')
                            ->image()
                            ->maxSize(512)
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'])
                            ->helperText('ICO, PNG або SVG, до 512KB. Іконка сайту й адмінки.'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function emailTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Email')
            ->icon('heroicon-o-envelope')
            ->schema([
                Section::make('Налаштування email-листiв')
                    ->description('Параметри вiдправки та вiдображення email-повiдомлень')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email_from_name')
                                ->label('Iм\'я вiдправника')
                                ->placeholder('GAZU')
                                ->helperText('Вiдображається як iм\'я вiдправника в листах'),

                            TextInput::make('email_from_address')
                                ->label('Email вiдправника')
                                ->email()
                                ->placeholder('no-reply@gazu.uno')
                                ->helperText('Адреса, з якої надсилаються листи'),
                        ]),

                        TextInput::make('email_reply_to')
                            ->label('Reply-To адреса')
                            ->email()
                            ->placeholder('support@gazu.uno')
                            ->helperText('Адреса для вiдповiдей клiєнтiв (якщо порожньо - використовується email вiдправника)'),

                        Toggle::make('email_show_logo')
                            ->label('Показувати логотип в листах')
                            ->helperText('Вiдображати логотип магазину у шапцi email-листiв')
                            ->default(true),

                        Textarea::make('email_footer_text')
                            ->label('Текст футера')
                            ->rows(2)
                            ->placeholder('Дякуємо, що обираєте нас!')
                            ->helperText('Додатковий текст у футерi кожного листа'),
                    ]),

                Section::make('Тестування')
                    ->description('Надiслати тестовий лист для перевiрки налаштувань')
                    ->schema([
                        Placeholder::make('email_test_hint')
                            ->label('')
                            ->content('Збережiть налаштування, а потiм скористайтесь кнопкою "Тестовий лист" для перевiрки вiдправки.'),
                    ]),
            ]);
    }

    protected function reviewsTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Відгуки')
            ->icon('heroicon-o-star')
            ->schema([
                Section::make('Модерація відгуків')
                    ->description('Налаштування модерації та автоматичного схвалення відгуків')
                    ->schema([
                        Toggle::make('reviews_moderation_enabled')
                            ->label('Модерація увімкнена')
                            ->helperText('Якщо вимкнено, всі відгуки публікуються одразу без перевірки')
                            ->reactive(),

                        Toggle::make('reviews_auto_approve')
                            ->label('Автосхвалення для підтверджених покупок')
                            ->helperText('Відгуки від покупців, які придбали товар, автоматично схвалюються')
                            ->visible(fn (callable $get) => (bool) $get('reviews_moderation_enabled')),
                    ])
                    ->columns(2),
            ]);
    }

    protected function notificationsTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Сповiщення')
            ->icon('heroicon-o-bell')
            ->schema([
                Section::make('Сповiщення адмiнiстратора')
                    ->description('Telegram-сповіщення налаштовуються в розділі «Розширення → Інтеграції → Telegram».')
                    ->schema([
                        Toggle::make('notify_new_order')
                            ->label('Сповiщення про нове замовлення')
                            ->helperText('Повiдомляти адмінів при кожному новому замовленнi (email + Telegram, якщо налаштовано)'),

                        Toggle::make('notify_low_stock')
                            ->label('Сповiщення про низький залишок')
                            ->helperText('Повiдомляти, коли товар закiнчується'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Зберегти')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),

            Action::make('reindex')
                ->label('Переiндексувати товари')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Переiндексувати всi товари?')
                ->modalDescription('Це запустить повну переiндексацiю товарiв. Процес може зайняти деякий час.')
                ->action('reindexProducts'),

            Action::make('test_email')
                ->label('Тестовий лист')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Надiслати тестовий лист?')
                ->modalDescription('Тестовий лист буде надiслано на email адмiнiстратора магазину.')
                ->action(function () {
                    try {
                        $adminEmail = auth()->user()->email;
                        $user = auth()->user();

                        Mail::to($adminEmail)->send(new \App\Mail\WelcomeEmail($user, 'TEST10'));

                        Notification::make()
                            ->success()
                            ->title('Тестовий лист надiслано')
                            ->body("Лист надiслано на {$adminEmail}")
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Помилка вiдправки')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('clear_cache')
                ->label('Очистити кеш')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->action(function () {
                    Cache::flush();
                    Artisan::call('cache:clear');
                    Artisan::call('view:clear');

                    Notification::make()
                        ->success()
                        ->title('Кеш очищено')
                        ->send();
                }),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $typeMap = $this->getTypeMap();
        $groupMap = $this->getGroupMap();

        foreach ($data as $key => $value) {
            $type = $typeMap[$key] ?? 'string';
            $group = $groupMap[$key] ?? 'general';

            $storeValue = match ($type) {
                'boolean' => $value ? 'true' : 'false',
                'array', 'json' => is_array($value) ? json_encode($value) : (string) $value,
                'integer' => (string) (int) $value,
                default => (string) ($value ?? ''),
            };

            DisplaySetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $storeValue,
                    'type' => $type,
                    'group' => $group,
                    'title' => $this->getSettingTitle($key),
                    'is_active' => true,
                ]
            );
        }

        // Flush relevant caches
        DisplaySetting::flushHeaderCache();
        Cache::forget('shop_settings');
        Cache::forget('seo_settings');

        Notification::make()
            ->success()
            ->title('Налаштування збережено')
            ->body('Всi змiни збережено в базi даних')
            ->send();
    }

    public function reindexProducts(): void
    {
        try {
            Artisan::call('scout:flush', ['model' => 'App\\Models\\Product']);
            Artisan::call('scout:import', ['model' => 'App\\Models\\Product']);

            Cache::put('search_last_sync', now()->format('d.m.Y H:i:s'));

            Notification::make()
                ->success()
                ->title('Переiндексацiю завершено')
                ->body('Всi товари переiндексовано')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка переiндексацiї')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getTypeMap(): array
    {
        return [
            // General
            'shop_name' => 'string',
            'shop_phone' => 'string',
            'shop_email' => 'string',
            'shop_address' => 'string',
            'logo_type' => 'string',
            'logo_text' => 'string',
            'logo_image' => 'string',
            'favicon' => 'string',

            // Email
            'email_from_name' => 'string',
            'email_from_address' => 'string',
            'email_reply_to' => 'string',
            'email_show_logo' => 'boolean',
            'email_footer_text' => 'string',

            // Reviews
            'reviews_moderation_enabled' => 'boolean',
            'reviews_auto_approve' => 'boolean',

            // Notifications
            'notify_new_order' => 'boolean',
            'notify_low_stock' => 'boolean',
        ];
    }

    protected function getGroupMap(): array
    {
        $groups = [
            'general' => [
                'shop_name', 'shop_phone', 'shop_email', 'shop_address',
                'logo_type', 'logo_text', 'logo_image', 'favicon',
            ],
            'email' => [
                'email_from_name', 'email_from_address', 'email_reply_to',
                'email_show_logo', 'email_footer_text',
            ],
            'reviews' => [
                'reviews_moderation_enabled', 'reviews_auto_approve',
            ],
            'notifications' => [
                'notify_new_order', 'notify_low_stock',
            ],
        ];

        $map = [];
        foreach ($groups as $group => $keys) {
            foreach ($keys as $key) {
                $map[$key] = $group;
            }
        }

        return $map;
    }

    protected function getSettingTitle(string $key): string
    {
        $titles = [
            'shop_name' => 'Назва магазину',
            'shop_phone' => 'Телефон магазину',
            'shop_email' => 'Email магазину',
            'shop_address' => 'Адреса магазину',
            'logo_type' => 'Тип логотипу',
            'logo_text' => 'Текст логотипу',
            'logo_image' => 'Зображення логотипу',
            'favicon' => 'Favicon',
            'email_from_name' => 'Iм\'я вiдправника email',
            'email_from_address' => 'Email вiдправника',
            'email_reply_to' => 'Reply-To адреса',
            'email_show_logo' => 'Логотип в листах',
            'email_footer_text' => 'Текст футера email',
            'reviews_moderation_enabled' => 'Модерація відгуків',
            'reviews_auto_approve' => 'Автосхвалення відгуків',
            'notify_new_order' => 'Нове замовлення',
            'notify_low_stock' => 'Низький залишок',
        ];

        return $titles[$key] ?? ucfirst(str_replace(['_', '-'], ' ', $key));
    }
}
