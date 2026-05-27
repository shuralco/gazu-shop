<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Livewire\Exceptions\ComponentNotFoundException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catches Livewire ComponentNotFoundException — fired when client-side
 * snapshot references a server-side component that no longer exists
 * (e.g. after a module was disabled).
 *
 * Returns 200 with a tiny JS payload that forces a full-page reload,
 * dropping the stale snapshot. The user sees a brief flicker instead
 * of a hard 500 "Server Error" page.
 *
 * Applied only to /livewire/update endpoint via bootstrap/app.php or
 * AppServiceProvider.
 */
class HandleStaleLivewireComponent
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (ComponentNotFoundException $e) {
            // Tell client: refresh yourself. Most predictable response is
            // a Livewire-shaped reply with redirect effect.
            $payload = [
                'effects' => [
                    'redirect' => $request->headers->get('Referer') ?: url()->previous(),
                ],
                'serverMemo' => [],
                'snapshot' => '{}',
            ];

            return response()->json($payload, 200)
                ->header('X-Livewire-Reload', 'stale-component');
        }
    }
}
