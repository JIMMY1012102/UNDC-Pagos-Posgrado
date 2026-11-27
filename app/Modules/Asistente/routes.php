<?php

use App\Modules\Asistente\Http\Controllers\AssistantController;
use Illuminate\Support\Facades\Route;

Route::post('/api/v1/asistente/mensajes', AssistantController::class)
    ->middleware('throttle:20,1')
    ->name('api.asistente.mensajes.store');
