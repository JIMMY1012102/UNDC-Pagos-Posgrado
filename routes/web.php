<?php

use Illuminate\Support\Facades\Route;

// Página inicial actual, antes del catálogo público real.
Route::get('/', function () {
    return view('welcome');
})->name('inicio');

// Interfaz de precompra: no crea pedidos ni procesa pagos.
Route::get('/tienda', fn () => view('tienda'))->name('tienda');


// Enrutamiento modular: cada dominio registra solo funcionalidades listas.
require app_path('Modules/Acceso/routes.php');
require app_path('Modules/Tienda/routes.php');
require app_path('Modules/Pagos/routes.php');
require app_path('Modules/Tesoreria/routes.php');
