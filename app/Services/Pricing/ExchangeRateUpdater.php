<?php

namespace App\Services\Pricing;

use App\Models\DisplaySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Авто-оновлення курсів валют з НБУ → DisplaySetting (fx_usd_uah / fx_eur_uah /
 * fx_cny_uah), які читає ChinesePriceCalculator::fxRate() для перерахунку цін
 * товарів у грн на вітрині.
 *
 * Ручний override: якщо DisplaySetting('fx_manual_override') == '1' — авто-апдейт
 * пропускається, лишаються курси, задані вручну адміном.
 */
class ExchangeRateUpdater
{
    private const NBU_URL = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json';

    /** Валюти, які тягнемо з НБУ. */
    private const CURRENCIES = ['USD', 'EUR', 'CNY'];

    public function isManualOverride(): bool
    {
        try {
            return (string) DisplaySetting::get('fx_manual_override') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, float> оновлені курси (cc => rate), або порожньо
     */
    public function update(bool $force = false): array
    {
        if (! $force && $this->isManualOverride()) {
            return [];
        }

        try {
            $resp = Http::timeout(15)->retry(2, 500)->get(self::NBU_URL);
            if (! $resp->ok()) {
                Log::warning('[fx] NBU API не ok', ['status' => $resp->status()]);
                return [];
            }
            $rows = $resp->json();
        } catch (\Throwable $e) {
            Log::warning('[fx] NBU API помилка: '.$e->getMessage());
            return [];
        }

        if (! is_array($rows)) {
            return [];
        }

        $updated = [];
        foreach ($rows as $row) {
            $cc = strtoupper((string) ($row['cc'] ?? ''));
            if (! in_array($cc, self::CURRENCIES, true)) {
                continue;
            }
            $rate = (float) ($row['rate'] ?? 0);
            if ($rate <= 0) {
                continue;
            }
            $key = 'fx_'.strtolower($cc).'_uah';
            DisplaySetting::set($key, (string) round($rate, 4));
            $updated[$cc] = round($rate, 4);
        }

        if ($updated) {
            DisplaySetting::set('fx_updated_at', now()->toDateTimeString());
        }

        return $updated;
    }
}
