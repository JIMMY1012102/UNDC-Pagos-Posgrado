<?php

use App\Modules\Pedidos\Http\Controllers\CartController;
use App\Modules\Pedidos\Http\Controllers\PrepurchaseController;
use Illuminate\Support\Facades\Route;

// URL and route names unchanged. Payment/purchase creation is not available.
Route::view('/tienda/carrito', 'modules.tienda.cart')->name('tienda.carrito');

Route::middleware('throttle:60,1')
    ->prefix('api/v1/tienda')
    ->name('api.tienda.')
    ->group(function (): void {
        Route::post('/carritos', [CartController::class, 'create'])->name('carritos.create');
        Route::get('/carritos/actual', [CartController::class, 'show'])->name('carritos.show');
        Route::post('/carritos/actual/items', [CartController::class, 'add'])->name('carritos.add');
        Route::delete('/carritos/actual/items/{line}', [CartController::class, 'remove'])
            ->whereNumber('line')->name('carritos.remove');
    });

Route::middleware(['auth', 'throttle:30,1'])
    ->prefix('api/v1/tienda')
    ->name('api.tienda.')
    ->group(function (): void {
        Route::post('/precompra/vincular-carrito', [PrepurchaseController::class, 'claim'])->name('precompra.claim');
        Route::get('/precompra/resumen', [PrepurchaseController::class, 'summary'])->name('precompra.summary');
    });
