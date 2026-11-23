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

    /** Return orders with unresolved provider evidence, in a bounded batch. */
    public function unresolvedOrderIds(array $ids): array
    {
        if ($ids === []) return [];
        $unresolved = DB::table('tienda_intentos_pago')->whereIn('order_id', $ids)
            ->whereNotIn('status', AttemptStatus::terminalValues())->distinct()->pluck('order_id')->all();
        $unresolved = array_merge($unresolved, DB::table('tienda_transacciones_pago')->whereIn('order_id', $ids)
            ->whereNotIn('status', TransactionStatus::terminalValues())->distinct()->pluck('order_id')->all());
        $unresolved = array_merge($unresolved, DB::table('tienda_pagos_aplicados_pedidos')
            ->whereIn('order_id', $ids)->distinct()->pluck('order_id')->all());
        return array_values(array_unique($unresolved));
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


    /** A pending order with unresolved provider evidence is shown for review, not as expired. */
    public function hasUnresolvedPayment(int $orderId): bool
    {
        if (DB::table('tienda_intentos_pago')->where('order_id', $orderId)
            ->whereNotIn('status', AttemptStatus::terminalValues())->exists()) {
            return true;
        }

        if (DB::table('tienda_transacciones_pago')->where('order_id', $orderId)
            ->whereNotIn('status', TransactionStatus::terminalValues())->exists()) {
            return true;
        }

        return DB::table('tienda_pagos_aplicados_pedidos')->where('order_id', $orderId)->exists();
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
