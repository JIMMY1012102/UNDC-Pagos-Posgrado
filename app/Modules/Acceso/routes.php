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
