<?php

namespace App\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    public function getDefault(): string
    {
        return config('currencies.default', 'UAH');
    }

    public function getCurrent(): string
    {
        return session('currency', $this->getDefault());
    }

    public function setCurrent(string $code): void
    {
        $available = $this->getAvailable();
        if (isset($available[$code])) {
            session()->put('currency', $code);
        }
    }

    public function getAvailable(): array
    {
        return config('currencies.available', []);
    }

    public function convert(float $amount, ?string $from = null, ?string $to = null): float
    {
        $from = $from ?? $this->getDefault();
        $to = $to ?? $this->getCurrent();

        if ($from === $to) return $amount;

        $currencies = $this->getAvailable();
        $fromRate = $currencies[$from]['rate'] ?? 1;
        $toRate = $currencies[$to]['rate'] ?? 1;

        // Convert to base (UAH), then to target
        $baseAmount = $amount / $fromRate;

        return round($baseAmount * $toRate, $currencies[$to]['decimals'] ?? 2);
    }

    public function format(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? $this->getCurrent();
        $config = $this->getAvailable()[$currency] ?? $this->getAvailable()[$this->getDefault()];

        $formatted = number_format($amount, $config['decimals'] ?? 0, '.', ' ');

        if (($config['position'] ?? 'after') === 'before') {
            return $config['symbol'] . $formatted;
        }

        return $formatted . ' ' . $config['symbol'];
    }

    public function convertAndFormat(float $amountInDefault, ?string $to = null): string
    {
        $converted = $this->convert($amountInDefault, null, $to);

        return $this->format($converted, $to ?? $this->getCurrent());
    }

    public function getSymbol(?string $currency = null): string
    {
        $currency = $currency ?? $this->getCurrent();
        $config = $this->getAvailable()[$currency] ?? [];

        return $config['symbol'] ?? '₴';
    }

    /**
     * Update exchange rates from NBU API (National Bank of Ukraine)
     */
    public function updateRatesFromNBU(): array
    {
        try {
            $response = Http::connectTimeout(5)->timeout(10)
                ->get('https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json');

            if (! $response->successful()) return [];

            $rates = $response->json();
            $updated = [];

            foreach ($rates as $rate) {
                $code = $rate['cc'] ?? '';
                if (in_array($code, ['USD', 'EUR'])) {
                    $nbuRate = (float) ($rate['rate'] ?? 0);
                    if ($nbuRate > 0) {
                        $updated[$code] = round(1 / $nbuRate, 6);
                    }
                }
            }

            if (! empty($updated)) {
                Cache::put('currency_rates', $updated, now()->addHours(12));
            }

            return $updated;
        } catch (\Throwable $e) {
            Log::error('NBU rate update failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get rate with NBU cache fallback
     */
    public function getLiveRate(string $code): ?float
    {
        $cached = Cache::get('currency_rates', []);

        return $cached[$code] ?? ($this->getAvailable()[$code]['rate'] ?? null);
    }
}
