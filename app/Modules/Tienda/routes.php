<?php

use App\Modules\Tienda\Http\Controllers\CatalogController;
use App\Modules\Tienda\Http\Controllers\CartController;
use App\Modules\Tienda\Http\Controllers\PrepurchaseController;
use Illuminate\Support\Facades\Route;

// Public read-only endpoint. Published content does not authorize checkout.
Route::middleware('throttle:60,1')
    ->prefix('api/v1/tienda')
    ->name('api.tienda.')
    ->group(function (): void {
        Route::get('/catalogo', CatalogController::class)->name('catalogo.index');

        // Anonymous pre-cart. No prices, orders, payments, or checkout.
        Route::post('/carritos', [CartController::class, 'create'])->name('carritos.create');
        Route::get('/carritos/actual', [CartController::class, 'show'])->name('carritos.show');
        Route::post('/carritos/actual/items', [CartController::class, 'add'])->name('carritos.add');
        Route::delete('/carritos/actual/items/{line}', [CartController::class, 'remove'])->whereNumber('line')->name('carritos.remove');
    });

Route::middleware(['auth', 'throttle:30,1'])->prefix('api/v1/tienda')->name('api.tienda.')
    ->group(function (): void {
        Route::post('/precompra/vincular-carrito', [PrepurchaseController::class, 'claim'])->name('precompra.claim');
        Route::get('/precompra/resumen', [PrepurchaseController::class, 'summary'])->name('precompra.summary');
    });
