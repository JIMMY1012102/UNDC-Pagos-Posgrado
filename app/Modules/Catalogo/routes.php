<?php

use App\Modules\Catalogo\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/api/v1/tienda/catalogo/oficinas', [CatalogController::class, 'offices'])
    ->middleware('throttle:60,1')->name('api.tienda.catalogo.oficinas');

// Keep existing API name and path for browser compatibility.
Route::middleware('throttle:60,1')
    ->prefix('api/v1/tienda')
    ->name('api.tienda.')
    ->get('/catalogo', CatalogController::class)
    ->name('catalogo.index');

