<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Support\Hooks;
use App\Support\ModuleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Web-accessible emergency safe-mode trigger. Викликається коли модуль
 * ламає сайт настільки, що адмінка теж не вантажиться. URL:
 *
 *   /safe-mode?token={APP_KEY first 16 chars}
 *
 * Token = sha1(env('APP_KEY')) substr 0,16 — admin зі знанням ENV
 * single source of truth. Без правильного токена — 403.
 *
 * Не запускає lifecycle hooks (бо вони можуть теж бути broken).
 * Просто пише в DB enabled=false і чистить caches.
 */
class SafeModeController extends Controller
{
    private const CORE_MODULES = ['multi_warehouse'];

    public function trigger(Request $request)
    {
        $token = (string) $request->query('token', '');
        $expected = substr(sha1((string) config('app.key')), 0, 16);

        if (! hash_equals($expected, $token)) {
            return response('Forbidden — invalid safe-mode token. Use: '
                .'sha1(APP_KEY) substr 0,16 as query param.', 403);
        }

        $disabled = [];
        try {
            $candidates = ModuleManager::all()
                ->filter(fn ($m) => $m->enabled() && ! in_array($m->key(), self::CORE_MODULES, true))
                ->keys()
                ->all();
        } catch (\Throwable $e) {
            // Якщо навіть ModuleManager падає — fallback на raw DB query.
            $candidates = \DB::table('modules')->where('enabled', 1)
                ->whereNotIn('key', self::CORE_MODULES)
                ->pluck('key')->all();
        }

        foreach ($candidates as $key) {
            try {
                Module::updateOrCreate(['key' => $key], [
                    'enabled' => false,
                    'disabled_at' => now(),
                ]);
                $disabled[] = $key;
            } catch (\Throwable $e) {
                \Log::error("[SafeMode] failed to disable {$key}: ".$e->getMessage());
            }
        }

        // Best-effort cache clearing — кожен виклик в try щоб navіть якщо
        // один артізан-call падає, інші продовжують.
        foreach (['config:clear', 'view:clear', 'cache:clear', 'responsecache:clear', 'filament:cache-components', 'route:clear'] as $cmd) {
            try { Artisan::call($cmd); } catch (\Throwable $e) { /* silent */ }
        }

        ModuleManager::clearCache();

        // Fire безбагово event — listeners що теж broken можуть кинути.
        try {
            foreach ($disabled as $key) {
                Hooks::do('module.disabled', $key, ['actions' => ['safe-mode']]);
            }
        } catch (\Throwable $e) { /* silent */ }

        $list = implode(', ', $disabled) ?: '(нічого не було активним)';
        return response()->view('errors.safe-mode-success', [
            'disabled' => $disabled,
            'count' => count($disabled),
        ], 200);
    }
}
