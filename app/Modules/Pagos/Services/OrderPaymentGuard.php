<?php

namespace App\Modules\Pagos\Services;

use Illuminate\Support\Facades\DB;

final class OrderPaymentGuard
{
    /** Any payment evidence requires resolution before replacing an order. */
    public function canModify(int $orderId): bool
    {
        foreach (['tienda_intentos_pago', 'tienda_transacciones_pago', 'tienda_pagos_aplicados_pedidos'] as $table) {
            if (DB::table($table)->where('order_id', $orderId)->exists()) {
                return false;
            }
        }

        return true;
    }
}
