<?php

namespace App\Modules\Pagos\Services;

use App\Modules\Pagos\Enums\AttemptStatus;
use App\Modules\Pagos\Enums\TransactionStatus;
use Illuminate\Database\Query\Builder;
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

    /**
     * Unknown statuses are deliberately treated as unresolved: only known
     * terminal states allow an unpaid order to expire.
     */
    public function excludeOrdersWithUnresolvedPayments(Builder $orders): Builder
    {
        return $orders
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')->from('tienda_intentos_pago')
                    ->whereColumn('tienda_intentos_pago.order_id', 'tienda_pedidos.id')
                    ->whereNotIn('tienda_intentos_pago.status', AttemptStatus::terminalValues());
            })
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')->from('tienda_transacciones_pago')
                    ->whereColumn('tienda_transacciones_pago.order_id', 'tienda_pedidos.id')
                    ->whereNotIn('tienda_transacciones_pago.status', TransactionStatus::terminalValues());
            })
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')->from('tienda_pagos_aplicados_pedidos')
                    ->whereColumn('tienda_pagos_aplicados_pedidos.order_id', 'tienda_pedidos.id');
            });
    }
}
