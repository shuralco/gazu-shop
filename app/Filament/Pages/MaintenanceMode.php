<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Режим технічного обслуговування — глушить фронт для відвідувачів, лишаючи
 * адмінку робочою. Логіка — у StorefrontMaintenance middleware.
 * Доступ: супер-адмін або пресет admin_full (технічний адмін).
 */
class MaintenanceMode extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Обслуговування';

    protected static ?string $navigationLabel = 'Технічне обслуговування';

    protected static ?string $title = 'Режим технічного обслуговування';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.maintenance-mode';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return $u && ($u->is_admin === true || optional($u->accessPreset)->key === 'admin_full');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill([
            'maintenance_mode' => (bool) DisplaySetting::get('maintenance_mode', false),
            'maintenance_message' => (string) (DisplaySetting::get('maintenance_message') ?: ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Доступність сайту')
                    ->description('Тимчасово закрити вітрину для відвідувачів, не зупиняючи адмінку.')
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label('Технічне обслуговування (заглушити сайт)')
                            ->helperText('УВІМКНЕНО: звичайні відвідувачі бачать сторінку «Технічне обслуговування» (503), фронт фактично недоступний. ВИМКНЕНО: сайт працює як зазвичай. Адмінка /admin працює завжди, незалежно від цього перемикача. Залогінені адміни та персонал (з пресетом) бачать сайт нормально — можна спокійно перевіряти зміни під час обслуговування.')
                            ->inline(false),

                        Textarea::make('maintenance_message')
                            ->label('Текст для відвідувачів')
                            ->rows(3)
                            ->placeholder('Сайт тимчасово на технічному обслуговуванні. Зайдіть, будь ласка, трохи пізніше.')
                            ->helperText('Цей текст показується на сторінці-заглушці. Якщо порожньо — використовується стандартний.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        DisplaySetting::set('maintenance_mode', (bool) ($state['maintenance_mode'] ?? false), 'Режим тех-обслуговування');
        DisplaySetting::set('maintenance_message', (string) ($state['maintenance_message'] ?? ''), 'Повідомлення тех-обслуговування');

        // Скинути кеші, щоб заглушка/повернення зʼявились одразу.
        DisplaySetting::flushSettingsCache();
        foreach (['gazu-menu', 'storefront'] as $tag) {
            try {
                \Illuminate\Support\Facades\Cache::tags([$tag])->flush();
            } catch (\Throwable) {
            }
        }
        try {
            app(\Spatie\ResponseCache\ResponseCache::class)->clear();
        } catch (\Throwable) {
        }

        Notification::make()
            ->title(($state['maintenance_mode'] ?? false) ? 'Сайт переведено на обслуговування' : 'Сайт знову доступний')
            ->success()
            ->send();
    }
}
