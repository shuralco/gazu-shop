<?php

namespace App\Http\Middleware;

use App\Support\ModuleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: blocks access if the named module is disabled.
 *
 * Usage in routes:
 *   Route::middleware('module:loyalty')->group(...)
 */
class RequiresModule
{
    public function handle(Request $request, Closure $next, string $key): Response
    {
        if (! ModuleManager::for($key)->enabled()) {
            abort(404, "Module '{$key}' is not enabled for this shop.");
        }

        return $next($request);
    }
}
