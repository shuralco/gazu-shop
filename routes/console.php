<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('loyalty:expire-points')->daily()->at('02:00');
Schedule::command('loyalty:birthday-bonuses')->daily()->at('08:00');
Schedule::command('loyalty:recalculate-tiers')->weekly()->sundays()->at('03:00');

Schedule::command('checkbox:open-shift')->daily()->at('08:00');
Schedule::command('checkbox:close-shift')->daily()->at('23:00');

Schedule::command('feeds:generate')->daily()->at('04:00');

Schedule::command('currency:update-rates')->twiceDaily(9, 15);

Schedule::command('stock:check')->daily()->at('09:00');

Schedule::command('np:sync --warehouses-only')->dailyAt('04:00');
Schedule::command('np:sync-references --areas --cities')->weeklyOn(1, '03:00');
Schedule::command('np:sync-references --warehouses')->dailyAt('03:30');
Schedule::command('np:track')->everyThirtyMinutes();
Schedule::command('np:clean-api-logs')->dailyAt('02:30');

Schedule::command('up:sync-references --regions')->weeklyOn(1, '03:00');
Schedule::command('up:sync-references --cities')->weeklyOn(1, '03:30');
Schedule::command('up:track')->everyThirtyMinutes();

// Cold-cache safety-net: cause-agnostic guard щохвилини детектить холодний
// storefront (нескомпільовані view / повільний TTFB) і сам доварює → будь-яке
// cold-вікно ≤60с. Плюс легкий плановий warm (home+категорії+бренди) кожні
// 30хв і повний (з товарами) щогодини. Див. app/Console/Commands/EnsureWarm.php.
Schedule::command('gazu:ensure-warm')->everyMinute()->withoutOverlapping(5)->runInBackground();
Schedule::command('cache:warm')->everyThirtyMinutes()->withoutOverlapping();
Schedule::command('cache:warm --products')->hourly()->withoutOverlapping();
