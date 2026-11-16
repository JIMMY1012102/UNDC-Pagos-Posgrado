<?php

namespace App\Modules\Pagos\Services;

use Illuminate\Support\Facades\DB;

final class OrderPaymentGuard
{
    public function blockedOrderIds(array $ids): array
    {
        $blocked = [];
        foreach (['tienda_intentos_pago', 'tienda_transacciones_pago', 'tienda_pagos_aplicados_pedidos'] as $table) {
            $blocked = array_merge($blocked, DB::table($table)->whereIn('order_id', $ids)->distinct()->pluck('order_id')->all());
        }
        return array_unique($blocked);
    }

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
