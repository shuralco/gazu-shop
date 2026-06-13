<?php

namespace App\Octane;

use Illuminate\Support\Facades\Artisan;
use Laravel\Octane\Events\WorkerStarting;

/**
 * Octane WorkerStarting listener (invokable class — Octane вимагає class-string,
 * НЕ closure: closure у config/octane.php listeners ламає package:discover з
 * "Object of class Closure could not be converted to string").
 *
 * Self-heal: якщо Blade-view не скомпільовані (після view:clear / частого
 * рестарту) — компілюємо ОДРАЗУ при старті воркера, не чекаючи першого хіта
 * (інакше перший відвідувач ловить ~500ms recompile-спайк). Idempotent → no-op
 * коли view вже є.
 */
class EnsureCompiledViews
{
    public function __invoke(?WorkerStarting $event = null): void
    {
        try {
            if (count(glob(storage_path('framework/views/*.php')) ?: []) === 0) {
                Artisan::call('view:cache');
            }
        } catch (\Throwable $e) {
            // не валимо старт воркера через прогрів view
        }
    }
}
