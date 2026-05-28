<?php

use Illuminate\Support\Facades\Route;

/*
 * Routes for module: related_products
 * Auto-loaded by ModuleDiscovery::bootModuleResources() when module is enabled.
 * Disabled module = endpoints зникають, variant picker на фронті теж сховається.
 *
 * Ці маршрути живуть під префіксом /{locale} (gazu.* group), тому
 * визначаємо їх з тим самим іменам-префіксом 'gazu.' для сумісності
 * з frontend (URL.canonical).
 */

Route::middleware('web')->group(function () {
    Route::get('/api/products/{id}/snapshot', [
        \App\Http\Controllers\Gazu\ProductSnapshotController::class,
        'show',
    ])->whereNumber('id')->name('api.products.snapshot');

    Route::get('/api/products/{id}/variant-by-options', [
        \App\Http\Controllers\Gazu\ProductSnapshotController::class,
        'variantByOptions',
    ])->whereNumber('id')->name('api.products.variant-by-options');
});
