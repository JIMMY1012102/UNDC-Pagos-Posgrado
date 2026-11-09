<?php

/** Prueba solo para MariaDB efímera de CI, después de mariadb_check.php verify. */
declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (getenv('APP_ENV') !== 'testing' || getenv('UNDC_INTEGRATION_FIXTURE') !== '1') {
    throw new RuntimeException('Esta prueba requiere el entorno efímero de integración.');
}

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tables = [
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
$grammar = DB::connection()->getQueryGrammar();
$snapshot = static function (array $names) use ($grammar): array {
    $result = [];
    foreach ($names as $name) {
        $ddl = (array) DB::selectOne('SHOW CREATE TABLE '.$grammar->wrapTable($name));
        $rows = array_map(static fn ($row): array => (array) $row, DB::table($name)->orderBy('id')->get()->all());
        $result[$name] = [$ddl['Create Table'], $rows];
    }

    return $result;
};
$legacyTables = ['tm_conceptos', 'tm_oficina', 'tm_recaudacion', 'tm_resultado', 'tm_rol', 'tm_usuario'];
$legacyDdl = static function () use ($legacyTables, $grammar): array {
    $result = [];
    foreach ($legacyTables as $table) {
        $result[$table] = (array) DB::selectOne('SHOW CREATE TABLE '.$grammar->wrapTable($table));
    }

    return $result;
};

$before = $snapshot(array_values($tables));
$legacyBefore = $legacyDdl();
$migration = require __DIR__.'/../../database/migrations/2026_11_09_000001_rename_store_business_tables_to_spanish.php';
$migration->down();
foreach ($tables as $old => $new) {
    if (! Schema::hasTable($old) || Schema::hasTable($new)) {
        throw new RuntimeException("Rollback incompleto: {$new}");
    }
}
// Comprobar los registros semilla de catálogo también con los nombres originales.
if (DB::table('store_catalog_items')->count() !== count($before['tienda_conceptos_catalogo'][1])) {
    throw new RuntimeException('Se perdieron filas de catálogo al revertir los nombres.');
}
$migration->up();
if ($snapshot(array_values($tables)) !== $before) {
    throw new RuntimeException('El renombrado alteró columnas, índices, FK o datos de la tienda.');
}
if ($legacyDdl() !== $legacyBefore) {
    throw new RuntimeException('El renombrado alteró el DDL histórico SISPAGOS.');
}
foreach ($tables as $old => $new) {
    if (Schema::hasTable($old) || ! Schema::hasTable($new)) {
        throw new RuntimeException("Renombrado incompleto: {$old}");
    }
}

// También probar que InnoDB sigue rechazando referencias a conceptos inexistentes.
try {
    DB::table('tienda_conceptos_catalogo')->insert([
        'legacy_office_id' => 7, 'legacy_concept_id' => 999999, 'slug' => 'invalido-tras-renombrar',
    ]);
    throw new RuntimeException('Se perdió la FK del catálogo al renombrar.');
} catch (\Illuminate\Database\QueryException $e) {
    if ((string) $e->getCode() !== '23000') {
        throw $e;
    }
}

echo "PASS: 23 tablas renombradas y revertidas sin pérdida de datos, índices ni FK; legado intacto.\n";
