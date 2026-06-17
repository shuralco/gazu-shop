<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use App\Services\Pricing\ExchangeRateUpdater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Курси валют для перерахунку цін товарів у грн. Курси зберігаються у
 * DisplaySetting (fx_usd_uah / fx_eur_uah / fx_cny_uah), які читає
 * ChinesePriceCalculator::fxRate() — і для QuickFill (cost→retail), і для
 * відображення price_currency товару на вітрині.
 *
 * Авто-оновлення з НБУ (gazu:fx-update, двічі на день) — або ручний режим.
 */
class ExchangeRates extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?string $navigationLabel = 'Курси валют';

    protected static ?string $title = 'Курси валют (грн)';

    protected static ?int $navigationSort = 70;

    protected static string $view = 'filament.pages.exchange-rates';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return $u && ($u->is_admin === true || $u->access_preset_id !== null);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill([
            'fx_manual_override' => (string) DisplaySetting::get('fx_manual_override') === '1',
            'fx_usd_uah' => (string) (DisplaySetting::get('fx_usd_uah') ?: ''),
            'fx_eur_uah' => (string) (DisplaySetting::get('fx_eur_uah') ?: ''),
            'fx_cny_uah' => (string) (DisplaySetting::get('fx_cny_uah') ?: ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Режим оновлення')
                    ->schema([
                        Toggle::make('fx_manual_override')
                            ->label('Ручний режим (не оновлювати з НБУ автоматично)')
                            ->helperText('ВИМКНЕНО: курси оновлюються з НБУ двічі на день автоматично. УВІМКНЕНО: курси фіксуються вручну нижче, авто-оновлення пропускається.')
                            ->inline(false),
                        Placeholder::make('fx_updated_at')
                            ->label('Останнє авто-оновлення')
                            ->content(fn () => DisplaySetting::get('fx_updated_at') ?: '— ще не оновлювалось'),
                    ]),
                Section::make('Курси до гривні (1 одиниця валюти = X ₴)')
                    ->description('Ціна товару, заведена в USD/EUR/CNY, множиться на цей курс і показується покупцю в грн.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('fx_usd_uah')->label('Долар (USD → ₴)')->numeric()->step('0.0001')->suffix('₴'),
                        TextInput::make('fx_eur_uah')->label('Євро (EUR → ₴)')->numeric()->step('0.0001')->suffix('₴'),
                        TextInput::make('fx_cny_uah')->label('Юань (CNY → ₴)')->numeric()->step('0.0001')->suffix('₴'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        DisplaySetting::set('fx_manual_override', ($state['fx_manual_override'] ?? false) ? '1' : '0', 'Ручний режим курсів');
        foreach (['fx_usd_uah', 'fx_eur_uah', 'fx_cny_uah'] as $key) {
            if (($state[$key] ?? '') !== '') {
                DisplaySetting::set($key, (string) $state[$key], 'Курс валюти');
            }
        }

        DisplaySetting::flushSettingsCache();
        $this->flushStorefront();

        Notification::make()->title('Курси збережено')->success()->send();
    }

    public function fetchFromNbu(): void
    {
        $updated = app(ExchangeRateUpdater::class)->update(true);

        if (empty($updated)) {
            Notification::make()->title('НБУ недоступний — курси не оновлено')->warning()->send();

            return;
        }

        $this->form->fill([
            'fx_manual_override' => (string) DisplaySetting::get('fx_manual_override') === '1',
            'fx_usd_uah' => (string) (DisplaySetting::get('fx_usd_uah') ?: ''),
            'fx_eur_uah' => (string) (DisplaySetting::get('fx_eur_uah') ?: ''),
            'fx_cny_uah' => (string) (DisplaySetting::get('fx_cny_uah') ?: ''),
        ]);
        DisplaySetting::flushSettingsCache();
        $this->flushStorefront();

        Notification::make()->title('Курси оновлено з НБУ')->body(collect($updated)->map(fn ($r, $c) => "$c: $r ₴")->implode(' · '))->success()->send();
    }

    private function flushStorefront(): void
    {
        foreach (['catalog', 'storefront'] as $tag) {
            try {
                \Illuminate\Support\Facades\Cache::tags([$tag])->flush();
            } catch (\Throwable) {
            }
        }
        try {
            app(\Spatie\ResponseCache\ResponseCache::class)->clear();
        } catch (\Throwable) {
        }
    }
}
