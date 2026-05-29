<?php

use App\Http\Controllers\Gazu\ComparisonController;
use Illuminate\Support\Facades\Route;

/*
 * Routes for module: comparison
 * Auto-loaded by ModuleDiscovery::bootModuleResources() when module is enabled.
 * Already wrapped in Route::middleware('web')->group() by the discovery layer,
 * so session + CSRF are active here.
 *
 * Disabled module = ці маршрути зникають (кнопка «порівняти» теж ховається).
 */

Route::get('/comparison', [ComparisonController::class, 'index'])->name('gazu.comparison');
Route::post('/comparison/add', [ComparisonController::class, 'add'])->name('gazu.comparison.add');
Route::post('/comparison/remove', [ComparisonController::class, 'remove'])->name('gazu.comparison.remove');
Route::post('/comparison/clear', [ComparisonController::class, 'clear'])->name('gazu.comparison.clear');
