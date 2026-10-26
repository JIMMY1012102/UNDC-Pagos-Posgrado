<?php

use App\Modules\Acceso\Http\Controllers\BuyerProfileController;
use Illuminate\Support\Facades\Route;

// Authentication is provided by a trusted session, never by user_id in JSON.
Route::middleware(['auth', 'throttle:30,1'])
    ->prefix('api/v1/acceso')
    ->name('api.acceso.')
    ->group(function (): void {
        Route::get('/perfil', [BuyerProfileController::class, 'show'])->name('perfil.show');
        Route::put('/perfil', [BuyerProfileController::class, 'update'])->name('perfil.update');
    });

// Web-session OAuth routes: keep Google login disabled until OTI configuration.
Route::middleware('throttle:15,1')->group(function (): void {
    Route::get('/auth/google/redirect', [\App\Modules\Acceso\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [\App\Modules\Acceso\Http\Controllers\GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});
Route::post('/auth/logout', [\App\Modules\Acceso\Http\Controllers\GoogleAuthController::class, 'logout'])->middleware(['auth', 'throttle:15,1'])->name('auth.logout');
