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

    if ($request->hasAny(['q', 'category', 'view'])) {
        return redirect('/tienda/catalogo'.($request->getQueryString() ? '?'.$request->getQueryString() : ''));
    }

    return view('tienda', ['isMarketHome' => true]);
})->name('tienda');

// Siete módulos web; Asistente se registra en routes/api.php bajo middleware API.
foreach (['Sispagos', 'Catalogo', 'Usuarios', 'Pedidos', 'Pagos', 'Tesoreria', 'Seguridad'] as $module) {
    require app_path("Modules/{$module}/routes.php");
}
