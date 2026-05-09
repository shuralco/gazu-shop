<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class CarbonServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Carbon::setLocale('uk');
        setlocale(LC_TIME, 'uk_UA.UTF-8');
    }
}
