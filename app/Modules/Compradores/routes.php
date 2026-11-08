<?php

use App\Modules\Compradores\Http\Controllers\BuyerProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/tienda/login', 'modules.acceso.login')->name('tienda.login');

// Authentication is provided by a trusted session, never by user_id in JSON.
Route::middleware(['auth', 'throttle:30,1'])
    ->prefix('api/v1/acceso')
    ->name('api.acceso.')
    ->group(function (): void {
        Route::get('/estado', [BuyerProfileController::class, 'state'])->name('estado');
        Route::get('/perfil', [BuyerProfileController::class, 'show'])->name('perfil.show');
        Route::put('/perfil', [BuyerProfileController::class, 'update'])->name('perfil.update');
    });

// Web-session OAuth routes: keep Google login disabled until OTI configuration.
Route::middleware('throttle:15,1')->group(function (): void {
    Route::get('/auth/google/redirect', [\App\Modules\Compradores\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [\App\Modules\Compradores\Http\Controllers\GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});
Route::post('/auth/logout', [\App\Modules\Compradores\Http\Controllers\GoogleAuthController::class, 'logout'])->middleware(['auth', 'throttle:15,1'])->name('auth.logout');


// Account and profile web forms use Laravel CSRF and session protection.
Route::middleware('throttle:10,1')->group(function (): void {
    Route::get('/tienda/registro', [\App\Modules\Compradores\Http\Controllers\AccountController::class, 'registerForm'])->name('tienda.registro');
    Route::post('/tienda/registro', [\App\Modules\Compradores\Http\Controllers\AccountController::class, 'register'])->name('tienda.registro.store');
    Route::post('/tienda/login', [\App\Modules\Compradores\Http\Controllers\AccountController::class, 'login'])->name('tienda.login.store');
});
Route::get('/tienda/perfil', [\App\Modules\Compradores\Http\Controllers\AccountController::class, 'profileForm'])->name('tienda.perfil');
Route::post('/tienda/perfil', [\App\Modules\Compradores\Http\Controllers\AccountController::class, 'saveProfile'])->middleware('throttle:20,1')->name('tienda.perfil.store');
