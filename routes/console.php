<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Lectura únicamente: detectar incompatibilidades antes de activar la tienda.
Artisan::command('treasury:schema-check', function () {
    $missing = \App\Support\LegacyTreasurySchema::missing();

    if ($missing !== []) {
        foreach ($missing as $problem) {
            $this->error($problem);
        }
        return 1;
    }

    $this->info('Las seis tablas esperadas de SISPAGOS están presentes.');
    $this->warn('No verifica reglas de negocio, datos, ni la versión en producción.');
    return 0;
})->purpose('Comprobar compatibilidad estructural con la base compartida SISPAGOS sin modificarla');

Artisan::command('market:expire', function () {
    $orders = app(\App\Modules\Pedidos\Services\OrderService::class)->expireDue();
    $carts = \Illuminate\Support\Facades\DB::table('tienda_carritos')->where('status', 'active')
        ->where('expires_at', '<=', now())->update(['status' => 'expired', 'token_hash' => null, 'updated_at' => now()]);
    $this->info("Pedidos vencidos: {$orders}; carritos vencidos: {$carts}");
})->purpose('Vencer pedidos y retirar carritos caducados sin eliminar historial');

\Illuminate\Support\Facades\Schedule::command('market:expire')->everyFiveMinutes()->withoutOverlapping();
