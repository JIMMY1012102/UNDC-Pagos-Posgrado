<?php

use App\Modules\Pedidos\Http\Controllers\CartController;
use App\Modules\Pedidos\Http\Controllers\OrderController;
use App\Modules\Pedidos\Http\Controllers\PrepurchaseController;
use Illuminate\Support\Facades\Route;

// Carrito y pedidos: los cobros reales siguen deshabilitados.
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

Route::middleware('auth')->get('/tienda/mis-pedidos', fn () => response()->view('modules.tienda.orders')->header('Cache-Control', 'no-store'))->name('tienda.pedidos');
Route::middleware(['auth', 'throttle:30,1'])->prefix('api/v1/tienda')->group(function (): void {
    Route::post('/pedidos/cotizar', [OrderController::class, 'quote']);
    Route::post('/pedidos', [OrderController::class, 'store']);
    Route::get('/pedidos', [OrderController::class, 'index']);
    Route::post('/pedidos/{code}/modificar', [OrderController::class, 'modify'])->where('code', 'UNDC-[0-9A-Z]{26}');
    Route::get('/pedidos/{code}', [OrderController::class, 'show'])->where('code', 'UNDC-[0-9A-Z]{26}');
});
