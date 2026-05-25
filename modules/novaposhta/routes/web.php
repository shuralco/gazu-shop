<?php

use Illuminate\Support\Facades\Route;

// Routes for module: novaposhta
// Auto-loaded by ModuleDiscovery::bootModuleResources() when module is enabled.

Route::post('/api/np-webhook', \App\Http\Controllers\NpWebhookController::class)
    ->name('webhooks.np')
    ->withoutMiddleware([
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ]);
