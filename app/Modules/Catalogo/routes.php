<?php

use App\Modules\Catalogo\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

// Keep existing API name and path for browser compatibility.
Route::middleware('throttle:60,1')
    ->prefix('api/v1/tienda')
    ->name('api.tienda.')
    ->get('/catalogo', CatalogController::class)
    ->name('catalogo.index');
