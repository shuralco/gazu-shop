<?php

namespace App\Console\Commands;

use App\Services\Pricing\ExchangeRateUpdater;
use Illuminate\Console\Command;

/**
 * Оновлює курси DisplaySetting fx_usd_uah / fx_eur_uah / fx_cny_uah з НБУ.
 * Ці курси читає ChinesePriceCalculator::fxRate() для:
 *   - перерахунку закупки в QuickFill (cost → retail);
 *   - відображення ціни товару (price_currency) у грн на вітрині.
 * Пропускається, якщо ввімкнено ручний override (DisplaySetting fx_manual_override=1).
 */
class UpdateFxRates extends Command
{
    protected $signature = 'gazu:fx-update {--force : Оновити навіть при ручному override}';

    protected $description = 'Оновити курси валют (fx_*_uah) з НБУ для перерахунку цін у грн';

    public function handle(ExchangeRateUpdater $updater): int
    {
        if (! $this->option('force') && $updater->isManualOverride()) {
            $this->warn('Ручний override увімкнено (fx_manual_override=1) — авто-оновлення пропущено.');

            return self::SUCCESS;
        }

        $updated = $updater->update((bool) $this->option('force'));

        if (empty($updated)) {
            $this->warn('Курси не оновлено (НБУ недоступний або порожня відповідь).');

            return self::FAILURE;
        }

        foreach ($updated as $cc => $rate) {
            $this->info("{$cc}: {$rate} ₴");
        }

        return self::SUCCESS;
    }
}
