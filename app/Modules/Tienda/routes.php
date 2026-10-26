<?php

use App\Modules\Tienda\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

// Public read-only endpoint. Published content does not authorize checkout.
Route::middleware('throttle:60,1')
    ->prefix('api/v1/tienda')
    ->name('api.tienda.')
    ->group(function (): void {
        Route::get('/catalogo', CatalogController::class)->name('catalogo.index');
    });
