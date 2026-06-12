<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Налаштування сторінки 404 (gazu/404.blade.php).
 *
 * ВАЖЛИВО: керує тими самими DisplaySetting-ключами, які реально читає
 * вʼюха через $gazuSettings — gazu_404_title / gazu_404_desc / gazu_404_badge.
 * (Раніше сторінка писала осиротілі error_404_* через ShopSettings і НЕ
 * впливала на реальну 404 — звідси «немає відповідності інтерфейсу до
 * налаштувань». Виправлено.)
 *
 * Ті самі ключі також доступні у «GAZU візуальні блоки» (gazu-visual) —
 * обидві сторінки пишуть один DisplaySetting, тож значення синхронні.
 */
class Error404Settings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Сторінка 404';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?int $navigationSort = 150;

    protected static string $view = 'filament.pages.error404-settings';

    /** Ключі, що їх читає gazu/404.blade.php (через $gazuSettings). */
    private const KEYS = [
        'gazu_404_title' => 'Запчастину не знайдено',
        'gazu_404_desc' => 'Можливо, сторінку перенесли або URL застарів. Спробуйте знайти потрібну деталь через каталог.',
        'gazu_404_badge' => '',
    ];

    public ?array $data = [];

    public function mount(): void
    {
        $loaded = [];
        foreach (self::KEYS as $key => $default) {
            $val = DisplaySetting::get($key);
            $loaded[$key] = $val !== null ? $val : $default;
        }
        $this->form->fill($loaded);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('gazu_404_title')
                    ->label('Заголовок')
                    ->helperText('Великий заголовок під числом «404».')
                    ->required()
                    ->maxLength(120),

                Textarea::make('gazu_404_desc')
                    ->label('Опис')
                    ->helperText('Текст-пояснення під заголовком.')
                    ->required()
                    ->rows(3)
                    ->maxLength(400),

                TextInput::make('gazu_404_badge')
                    ->label('Бейдж біля «404» (необовʼязково)')
                    ->helperText('Маленький напис у кутку числа 404. Залиште порожнім — бейдж буде прихований.')
                    ->maxLength(20),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (array_keys(self::KEYS) as $key) {
            DisplaySetting::set($key, $state[$key] ?? '');
        }

        DisplaySetting::flushSettingsCache();

        Notification::make()
            ->title('Налаштування сторінки 404 збережено')
            ->success()
            ->send();
    }
}
