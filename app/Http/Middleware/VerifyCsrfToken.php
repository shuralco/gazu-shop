<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // API endpoints если потребуется
        // 'api/*',

        // Webhook endpoints для платіжних систем
        'webhooks/*',

        // Payment success callbacks від платіжних систем
        'orders/*/success',

        // Mobile test page
        'mobile-test',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        // Додаткова логіка для Livewire если потрібно
        if ($request->is('livewire/*')) {
            // Livewire має власну систему CSRF захисту
            \Log::debug('Livewire request processed with CSRF: '.$request->path());
        }

        return parent::handle($request, $next);
    }
}
