<?php

use Illuminate\Support\Facades\Route;

// Routes for module: gazu_garage
// Auto-loaded by ModuleDiscovery::bootModuleResources() when module is enabled.
// Wrapped in 'web' middleware via discovery; we only add module-specific layers.

Route::middleware('locale')->name('gazu.')->group(function () {
    Route::get('/garage', fn () => redirect('/garazh', 301)); // legacy alias

    Route::middleware('auth')->group(function () {
        $garage = \App\Http\Controllers\Gazu\GarageController::class;
        Route::get('/garazh', [$garage, 'index'])->name('garage');
        Route::post('/garazh', [$garage, 'store'])->name('garage.store');
        Route::post('/garazh/{car}', [$garage, 'update'])->name('garage.update');
        Route::post('/garazh/{car}/primary', [$garage, 'makePrimary'])->name('garage.primary');
        Route::delete('/garazh/{car}', [$garage, 'destroy'])->name('garage.destroy');
    });
});
