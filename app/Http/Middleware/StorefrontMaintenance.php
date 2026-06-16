<?php

namespace App\Http\Middleware;

use App\Models\DisplaySetting;
use Closure;
use Illuminate\Http\Request;

/**
 * Режим технічного обслуговування фронту. Коли DisplaySetting('maintenance_mode')
 * увімкнено — звичайні відвідувачі бачать сторінку-заглушку (503). Завжди
 * пропускаємо: адмін-панель (/admin), Livewire, статику, а також залогінених
 * адмінів/персонал (щоб вони бачили сайт нормально).
 *
 * Додається у групу 'web' ПЕРЕД ResponseCache (інакше гостю віддасться кеш).
 */
class StorefrontMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        if (! DisplaySetting::get('maintenance_mode', false)) {
            return $next($request);
        }

        // Адмінка, Livewire, логін-панель, статика — завжди доступні.
        if ($request->is(
            'admin', 'admin/*', 'livewire/*', 'filament/*',
            'build/*', 'css/*', 'js/*', 'fonts/*', 'storage/*', 'img/*',
            'favicon.ico', 'robots.txt', 'safe-mode',
        )) {
            return $next($request);
        }

        // Залогінений адмін / персонал (з пресетом) бачить сайт нормально.
        $u = $request->user();
        if ($u && ($u->is_admin === true || $u->access_preset_id !== null)) {
            return $next($request);
        }

        $message = DisplaySetting::get('maintenance_message')
            ?: 'Сайт тимчасово на технічному обслуговуванні. Зайдіть, будь ласка, трохи пізніше.';

        return response()->view('maintenance', ['message' => $message], 503)
            ->header('Retry-After', '3600');
    }
}
