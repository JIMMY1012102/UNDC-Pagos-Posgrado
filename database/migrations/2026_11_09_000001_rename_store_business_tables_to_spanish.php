<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Se conserva el historial original; solo se renombran las 23 tablas de negocio.
    private const TABLES = [
        'store_office_settings' => 'tienda_configuracion_oficinas',
        'store_catalog_items' => 'tienda_conceptos_catalogo',
        'store_catalog_price_versions' => 'tienda_versiones_tarifas',
        'store_customer_profiles' => 'tienda_perfiles_compradores',
        'store_oauth_identities' => 'tienda_identidades_oauth',
        'store_beneficiaries' => 'tienda_beneficiarios',
        'store_carts' => 'tienda_carritos',
        'store_cart_items' => 'tienda_detalles_carrito',
        'store_orders' => 'tienda_pedidos',
        'store_order_items' => 'tienda_detalles_pedido',
        'store_order_status_history' => 'tienda_historial_estados_pedido',
        'store_payment_attempts' => 'tienda_intentos_pago',
        'store_payment_transactions' => 'tienda_transacciones_pago',
        'store_order_payment_claims' => 'tienda_pagos_aplicados_pedidos',
        'store_payment_webhook_events' => 'tienda_eventos_notificacion_pago',
        'store_payment_incidents' => 'tienda_incidencias_pago',
        'store_refunds' => 'tienda_reembolsos',
        'store_reconciliation_batches' => 'tienda_lotes_conciliacion',
        'store_reconciliation_entries' => 'tienda_detalles_conciliacion',
        'store_external_system_links' => 'tienda_vinculos_sistemas_externos',
        'store_payment_validations' => 'tienda_validaciones_pago',
        'store_validation_certificates' => 'tienda_constancias_validacion',
        'store_audit_events' => 'tienda_eventos_auditoria',
    ];

    public function up(): void
    {
        $this->renameTables(self::TABLES);
    }

    public function down(): void
    {
        $this->renameTables(array_reverse(array_flip(self::TABLES), true));
    }

    private function renameTables(array $tables): void
    {
        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            throw new RuntimeException("Controlador no compatible con el renombrado: {$driver}");
        }

        // Validar TODO antes del primer DDL: nunca sobrescribir ni fusionar tablas.
        foreach ($tables as $source => $target) {
            if (! Schema::hasTable($source) || Schema::hasTable($target)) {
                throw new RuntimeException(
                    "No se puede renombrar {$source} a {$target}: debe existir solo la tabla de origen."
                );
            }
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // Una sola sentencia conserva datos/FK y evita renombrados parciales.
            // wrapTable respeta el prefijo configurado de la conexión.
            $grammar = DB::connection()->getQueryGrammar();
            $renames = [];
            foreach ($tables as $source => $target) {
                $renames[] = $grammar->wrapTable($source).' TO '.$grammar->wrapTable($target);
            }
            DB::statement('RENAME TABLE '.implode(', ', $renames));

            return;
        }

        // SQLite no tiene RENAME múltiple; su DDL sí participa en transacciones.
        DB::transaction(function () use ($tables): void {
            foreach ($tables as $source => $target) {
                Schema::rename($source, $target);
            }
        });
    }
};
