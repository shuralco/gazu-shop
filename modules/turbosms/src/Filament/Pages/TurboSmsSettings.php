<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use App\Services\TurboSms\TurboSmsClient;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Налаштування шлюзу TurboSMS: токен, альфа-імена, тогли подій.
 * Зберігається в DisplaySetting (turbosms_*) — переноситься між
 * інсталяціями стандартним механізмом налаштувань.
 */
class TurboSmsSettings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;
    use \App\Filament\Concerns\RequiresModule;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?string $navigationLabel = 'TurboSMS';

    protected static ?string $title = 'TurboSMS — SMS і Viber';

    protected static ?int $navigationSort = 32;

    protected static string $view = 'turbosms::settings';

    public ?array $data = [];

    private const KEYS = [
        'turbosms_token', 'turbosms_sms_sender', 'turbosms_viber_sender', 'turbosms_simulate',
        'turbosms_event_order_created', 'turbosms_event_order_paid',
        'turbosms_event_order_shipped', 'turbosms_event_status_changed',
    ];

    private const BOOL_KEYS = [
        'turbosms_simulate', 'turbosms_event_order_created', 'turbosms_event_order_paid',
        'turbosms_event_order_shipped', 'turbosms_event_status_changed',
    ];

    public static function moduleEnabled(): bool
    {
        return \App\Support\ModuleManager::for('turbosms')->enabled();
    }

    public function mount(): void
    {
        $state = [];
        foreach (self::KEYS as $key) {
            $state[$key] = DisplaySetting::get($key, in_array($key, self::BOOL_KEYS, true) ? false : '');
        }
        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Section::make('Підключення')
                ->description('Токен — у кабінеті turbosms.ua → HTTP API. Альфа-імена погоджуються через підтримку TurboSMS.')
                ->schema([
                    Forms\Components\TextInput::make('turbosms_token')
                        ->label('API токен')
                        ->password()
                        ->revealable()
                        ->helperText('Зберігається в БД. Без токена відправки логуються як NOT_CONFIGURED.'),
                    Forms\Components\TextInput::make('turbosms_sms_sender')
                        ->label('Відправник SMS (альфа-імʼя)')
                        ->placeholder('TurboSMS')
                        ->maxLength(25),
                    Forms\Components\TextInput::make('turbosms_viber_sender')
                        ->label('Відправник Viber')
                        ->placeholder('= відправник SMS')
                        ->maxLength(50),
                    Forms\Components\Toggle::make('turbosms_simulate')
                        ->label('Режим імітації')
                        ->helperText('Повідомлення НЕ йдуть у TurboSMS — лише пишуться в журнал як «simulated». Для тесту ланцюга без витрати балансу.')
                        ->columnSpanFull(),
                ])->columns(3),

            Forms\Components\Section::make('Події (кому і коли слати)')
                ->description('Тексти повідомлень редагуються в «Шаблони SMS/Viber». Відправка йде клієнту замовлення на його номер.')
                ->schema([
                    Forms\Components\Toggle::make('turbosms_event_order_created')
                        ->label('Замовлення створено')
                        ->helperText('Шаблон: order.created'),
                    Forms\Components\Toggle::make('turbosms_event_order_paid')
                        ->label('Оплату отримано')
                        ->helperText('Шаблон: order.paid'),
                    Forms\Components\Toggle::make('turbosms_event_order_shipped')
                        ->label('Відправлено (створено ТТН)')
                        ->helperText('Шаблон: order.shipped'),
                    Forms\Components\Toggle::make('turbosms_event_status_changed')
                        ->label('Зміна статусу замовлення')
                        ->helperText('Шаблон: order.status_changed'),
                ])->columns(2),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        foreach (self::KEYS as $key) {
            DisplaySetting::set($key, $state[$key] ?? (in_array($key, self::BOOL_KEYS, true) ? false : ''));
        }

        Notification::make()->title('Збережено')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('balance')
                ->label('Перевірити баланс')
                ->icon('heroicon-o-banknotes')
                ->action(function () {
                    $b = app(TurboSmsClient::class)->balance();
                    Notification::make()
                        ->title($b === null ? 'Не вдалося отримати баланс' : 'Баланс: '.number_format($b, 2).' грн')
                        ->body($b === null ? 'Перевірте токен (і збережіть форму перед перевіркою).' : null)
                        ->{$b === null ? 'danger' : 'success'}()
                        ->send();
                }),
            Action::make('test_sms')
                ->label('Тестове повідомлення')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Forms\Components\TextInput::make('phone')->label('Телефон')->placeholder('380671234567')->required()->tel(),
                    Forms\Components\Textarea::make('text')->label('Текст')->default('GAZU: тестове повідомлення TurboSMS.')->required()->rows(2),
                    Forms\Components\Select::make('channel')->label('Канал')
                        ->options(\App\Models\SmsTemplate::CHANNELS)
                        ->default('sms')->required()->native(false),
                ])
                ->action(function (array $data) {
                    $client = app(TurboSmsClient::class);
                    $phone = TurboSmsClient::normalizePhone($data['phone']);
                    if (! $phone) {
                        Notification::make()->title('Невалідний номер')->danger()->send();

                        return;
                    }
                    $res = $client->send([$phone], $data['channel'], $data['text']);

                    \App\Models\SmsMessage::create([
                        'phone' => $phone,
                        'template_key' => null,
                        'channel' => $data['channel'],
                        'text' => $data['text'],
                        'status' => $res['ok'] ? 'sent' : 'failed',
                        'message_id' => $res['message_ids'][$phone] ?? null,
                        'error' => $res['ok'] ? null : $res['error'],
                    ]);

                    Notification::make()
                        ->title($res['ok'] ? 'Відправлено ✓' : 'Помилка відправки')
                        ->body($res['ok'] ? ('message_id: '.($res['message_ids'][$phone] ?? '—')) : $res['error'])
                        ->{$res['ok'] ? 'success' : 'danger'}()
                        ->send();
                }),
        ];
    }

    /** Останні відправки для журналу на сторінці. */
    public function getRecentMessagesProperty()
    {
        return \App\Models\SmsMessage::query()->latest()->limit(15)->get();
    }
}
