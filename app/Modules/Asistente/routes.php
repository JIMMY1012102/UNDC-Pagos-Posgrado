<?php

use App\Modules\Asistente\Http\Controllers\AssistantController;
use Illuminate\Support\Facades\Route;

// routes/api.php agrega automáticamente el prefijo /api y el middleware API.
Route::post('/v1/asistente/mensajes', AssistantController::class)
    ->middleware('throttle:20,1')
    ->name('api.asistente.mensajes.store');
