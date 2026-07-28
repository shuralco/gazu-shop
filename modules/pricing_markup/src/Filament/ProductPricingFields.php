<?php

namespace Modules\PricingMarkup\Filament;

use App\Models\CustomerGroup;
use Filament\Forms;
use Filament\Tables;

/**
 * Ціноутворення в картці товару: закупка + що з неї побачить покупець.
 *
 * Колонки `cost_price` / `cost_currency` вже існували в базі (їх заповнював
 * імпорт quick_fill), але в картці товару їх не було — редагувати руками було
 * неможливо. Тут ми їх ПОКАЗУЄМО, а не заводимо нові.
 *
 * Живе в модулі, підключається через `product.form.pricing` і
 * `product.table.columns` — тож ставиться в чужий магазин без правки його файлів.
 */
class ProductPricingFields
{
    /** @return array<int, \Filament\Forms\Components\Component> */
    public static function formSchema(): array
    {
        return [
            Forms\Components\Fieldset::make('Закупка та ціни по групах')
                ->schema([
                    Forms\Components\TextInput::make('cost_price')
                        ->label('Ціна закупки')
                        ->helperText('Внутрішня цифра — покупець її ніде не бачить. Потрібна, щоб рахувати маржу.')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->live(onBlur: true)
                        ->columnSpan(1),

                    Forms\Components\Select::make('cost_currency')
                        ->label('Валюта закупки')
                        ->options(fn () => \App\Models\Currency::selectOptions())
                        ->default(fn () => self::baseCode())
                        // У старих товарів валюта порожня. Без цього Select мовчки
                        // показував би ПЕРШУ валюту зі списку (наприклад CNY) —
                        // виглядало б як свідомо обрана, хоча її ніхто не ставив.
                        ->afterStateHydrated(fn ($state, Forms\Set $set) => $state ?: $set('cost_currency', self::baseCode()))
                        ->selectablePlaceholder(false)
                        ->native(false)
                        ->live()
                        ->columnSpan(1),

                    Forms\Components\Placeholder::make('markup_breakdown')
                        ->label('Що побачить покупець')
                        ->content(fn (Forms\Get $get) => new \Illuminate\Support\HtmlString(self::breakdown($get)))
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    /** @return array<int, \Filament\Tables\Columns\Column> */
    public static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('cost_price')
                ->label('Закупка')
                ->formatStateUsing(fn ($state, $record) => $state
                    ? number_format((float) $state, 2, '.', ' ').' '.($record->cost_currency ?: '')
                    : '—')
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),

            Tables\Columns\TextColumn::make('margin_hint')
                ->label('Маржа')
                ->state(fn ($record) => self::marginLabel($record))
                ->badge()
                ->color(fn ($state) => str_starts_with((string) $state, '−') ? 'danger' : 'success')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /** Маржа відносно закупки за ціною стандартної групи. */
    private static function marginLabel($record): string
    {
        $cost = self::toBase((float) ($record->cost_price ?? 0), (string) ($record->cost_currency ?? ''));
        if ($cost <= 0) {
            return '—';
        }

        $price = (float) $record->priceViewForUser(null)['price'];
        $delta = $price - $cost;
        $percent = round($delta / $cost * 100, 1);

        return ($delta < 0 ? '−' : '+').number_format(abs($delta), 2, '.', ' ').' ₴ ('.$percent.' %)';
    }

    /** Таблиця «група → % → ціна → маржа», рахується просто у формі. */
    private static function breakdown(Forms\Get $get): string
    {
        $base = self::toBase((float) ($get('price') ?? 0), (string) ($get('price_currency') ?? ''));
        $cost = self::toBase((float) ($get('cost_price') ?? 0), (string) ($get('cost_currency') ?? ''));

        if ($base <= 0) {
            return '<span style="color:#71717a">Вкажіть ціну товару — тут зʼявиться, скільки заплатить кожна група.</span>';
        }

        $groups = CustomerGroup::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->get(['display_name', 'markup_percentage', 'is_default']);

        if ($groups->isEmpty()) {
            return '<span style="color:#71717a">Немає активних груп клієнтів.</span>';
        }

        $rows = '';
        foreach ($groups as $g) {
            $percent = (float) $g->markup_percentage;
            $price = round(max(0, $base * (1 + $percent / 100)), 2);
            $margin = $cost > 0
                ? (($price - $cost < 0 ? '−' : '+').number_format(abs($price - $cost), 2, '.', ' ').' ₴')
                : '—';

            $rows .= '<tr>'
                .'<td style="padding:4px 10px 4px 0">'.e($g->display_name)
                .($g->is_default ? ' <span style="font-size:11px;color:#0284c7">· стандартна</span>' : '')
                .'</td>'
                .'<td style="padding:4px 10px;text-align:right;white-space:nowrap">'.($percent > 0 ? '+' : '').rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.').' %</td>'
                .'<td style="padding:4px 10px;text-align:right;white-space:nowrap;font-weight:600">'.number_format($price, 2, '.', ' ').' ₴</td>'
                .'<td style="padding:4px 0 4px 10px;text-align:right;white-space:nowrap;color:#71717a">'.$margin.'</td>'
                .'</tr>';
        }

        $head = '<tr style="font-size:11px;color:#71717a;text-transform:uppercase">'
            .'<td style="padding-bottom:2px">Група</td>'
            .'<td style="text-align:right;padding-bottom:2px">Націнка</td>'
            .'<td style="text-align:right;padding-bottom:2px">Ціна</td>'
            .'<td style="text-align:right;padding-bottom:2px">Маржа</td></tr>';

        $note = $cost > 0
            ? '<div style="font-size:11px;color:#71717a;margin-top:6px">Маржа рахується від закупки '
                .number_format($cost, 2, '.', ' ').' ₴.</div>'
            : '<div style="font-size:11px;color:#71717a;margin-top:6px">Вкажіть закупку — покажемо маржу по кожній групі.</div>';

        return '<table style="width:100%;font-size:13px">'.$head.$rows.'</table>'.$note;
    }

    /** Валюта магазину за замовчуванням. */
    private static function baseCode(): string
    {
        try {
            return \App\Models\Currency::baseCode() ?: 'UAH';
        } catch (\Throwable) {
            return 'UAH';
        }
    }

    /** Сума у валюті → гривня (той самий довідник, що й на вітрині). */
    private static function toBase(float $amount, string $currency): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        try {
            return (float) \App\Models\Currency::toBase($amount, $currency ?: null);
        } catch (\Throwable) {
            return $amount;
        }
    }
}
