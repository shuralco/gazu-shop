<?php

namespace App\Console\Commands;

use App\Services\Currency\CurrencyService;
use Illuminate\Console\Command;

class UpdateCurrencyRates extends Command
{
    protected $signature = 'currency:update-rates';

    protected $description = 'Update currency exchange rates from NBU';

    public function handle(CurrencyService $service): int
    {
        $rates = $service->updateRatesFromNBU();

        if (empty($rates)) {
            $this->warn('Failed to update rates');

            return self::FAILURE;
        }

        foreach ($rates as $code => $rate) {
            $this->info("{$code}: {$rate}");
        }

        return self::SUCCESS;
    }
}
