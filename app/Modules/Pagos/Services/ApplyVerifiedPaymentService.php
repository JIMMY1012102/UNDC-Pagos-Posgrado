<?php

namespace App\Modules\Pagos\Services;

use App\Modules\Pagos\Enums\IncidentKind;
use App\Modules\Pagos\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ApplyVerifiedPaymentService
{
    /**
     * Apply a transaction only after its provider status has been verified
     * server-to-server and the verification evidence has been persisted.
     *
     * @return array{result: string, order_id: int}
     */
    public function apply(int $transactionId): array
    {
        return DB::transaction(function () use ($transactionId): array {
            $transaction = DB::table('tienda_transacciones_pago')
                ->where('id', $transactionId)->lockForUpdate()->first();

            if (! $transaction) {
                throw new InvalidArgumentException('La transacción de pago no existe.');
            }

            $order = DB::table('tienda_pedidos')
                ->where('id', $transaction->order_id)->lockForUpdate()->first();

            if (! $order) {
                throw new InvalidArgumentException('El pedido asociado no existe.');
            }

            $status = TransactionStatus::tryFrom((string) $transaction->status);
            $hasServerVerification = $transaction->verified_at !== null
                && is_string($transaction->verification_source)
                && trim($transaction->verification_source) !== ''
                && is_string($transaction->provider_transaction_reference)
                && trim($transaction->provider_transaction_reference) !== '';

            if (! $hasServerVerification || ! $status?->isPayable()) {
                return ['result' => 'not_verified', 'order_id' => (int) $order->id];
            }

            $existing = DB::table('tienda_pagos_aplicados_pedidos')
                ->where('order_id', $order->id)->first();

            if ($existing) {
                if ((int) $existing->payment_transaction_id === (int) $transaction->id) {
                    return ['result' => 'already_applied', 'order_id' => (int) $order->id];
                }

                $this->recordIncident(
                    (int) $order->id,
                    (int) $transaction->id,
                    IncidentKind::DuplicateCharge,
                    'Se observó otro cobro verificado para un pedido que ya tiene un pago aplicado. Requiere revisión de Tesorería.',
                );

                return ['result' => 'duplicate_charge', 'order_id' => (int) $order->id];
            }

            if (
                $transaction->currency !== $order->currency
                || self::toCents((string) $transaction->amount) !== self::toCents((string) $order->total_amount)
            ) {
                $this->recordIncident(
                    (int) $order->id,
                    (int) $transaction->id,
                    IncidentKind::Mismatch,
                    'El monto o la moneda del cobro verificado no coincide con la instantánea del pedido.',
                );

                return ['result' => 'mismatch', 'order_id' => (int) $order->id];
            }

            $now = now();
            $expired = $order->status === 'expired'
                || ($order->expires_at !== null && $now->greaterThanOrEqualTo($order->expires_at));

            if ($expired || $order->status !== 'pending' || $order->paid_at !== null) {
                $this->recordIncident(
                    (int) $order->id,
                    (int) $transaction->id,
                    IncidentKind::LatePayment,
                    'Se verificó un cobro cuando el pedido ya no estaba pendiente y vigente. No se aplicó automáticamente; requiere revisión de Tesorería.',
                );

                return ['result' => 'late_payment', 'order_id' => (int) $order->id];
            }

            DB::table('tienda_pagos_aplicados_pedidos')->insert([
                'order_id' => $order->id,
                'payment_transaction_id' => $transaction->id,
                'claimed_at' => $now,
            ]);

            DB::table('tienda_pedidos')->where('id', $order->id)->update([
                'status' => 'paid',
                'paid_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('tienda_historial_estados_pedido')->insert([
                'order_id' => $order->id,
                'from_status' => 'pending',
                'to_status' => 'paid',
                'reason' => 'Pago aplicado después de verificar servidor-servidor la referencia, el monto y la moneda.',
                'occurred_at' => $now,
            ]);

            return ['result' => 'applied', 'order_id' => (int) $order->id];
        }, 3);
    }

    private function recordIncident(int $orderId, int $transactionId, IncidentKind $kind, string $description): void
    {
        $exists = DB::table('tienda_incidencias_pago')
            ->where('payment_transaction_id', $transactionId)
            ->where('kind', $kind->value)
            ->where('status', 'open')
            ->exists();

        if (! $exists) {
            DB::table('tienda_incidencias_pago')->insert([
                'order_id' => $orderId,
                'payment_transaction_id' => $transactionId,
                'kind' => $kind->value,
                'status' => 'open',
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private static function toCents(string $amount): int
    {
        if (! preg_match('/^\d{1,12}\.\d{2}$/', $amount)) {
            throw new InvalidArgumentException('El monto debe tener dos decimales y formato decimal válido.');
        }

        [$units, $cents] = explode('.', $amount);

        return ((int) $units * 100) + (int) $cents;
    }
}
