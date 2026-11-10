<?php

use Illuminate\Support\Facades\Route;

// Página inicial actual, antes del catálogo público real.
Route::get('/', function () {
    return view('welcome');
})->name('inicio');

// Interfaz de precompra: no crea pedidos ni procesa pagos.
Route::get('/tienda', function (\Illuminate\Http\Request $request) {
    if ($request->query('login') === '1') {
        return redirect()->route('tienda.login');
    }

    return view('tienda');
})->name('tienda');


// Seven bounded contexts in one Laravel deployment.
foreach (['Sispagos', 'Catalogo', 'Usuarios', 'Pedidos', 'Pagos', 'Tesoreria', 'Seguridad'] as $module) {
    require app_path("Modules/{$module}/routes.php");
}
