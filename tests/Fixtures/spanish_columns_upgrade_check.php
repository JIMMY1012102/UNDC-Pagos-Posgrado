<?php

declare(strict_types=1);

use App\Models\User;
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
    'tienda_configuracion_oficinas', 'tienda_conceptos_catalogo', 'tienda_versiones_tarifas',
    'tienda_perfiles_compradores', 'tienda_identidades_oauth', 'tienda_beneficiarios',
    'tienda_carritos', 'tienda_detalles_carrito', 'tienda_pedidos', 'tienda_detalles_pedido',
    'tienda_historial_estados_pedido', 'tienda_intentos_pago', 'tienda_transacciones_pago',
    'tienda_pagos_aplicados_pedidos', 'tienda_eventos_notificacion_pago', 'tienda_incidencias_pago',
    'tienda_reembolsos', 'tienda_lotes_conciliacion', 'tienda_detalles_conciliacion',
    'tienda_vinculos_sistemas_externos', 'tienda_validaciones_pago', 'tienda_constancias_validacion',
    'tienda_eventos_auditoria', 'tienda_categorias',
];
$legacyTables = ['tm_conceptos', 'tm_oficina', 'tm_recaudacion', 'tm_resultado', 'tm_rol', 'tm_usuario'];
$grammar = DB::connection()->getQueryGrammar();

$legacyBefore = [];
foreach ($legacyTables as $table) {
    $legacyBefore[$table] = (array) DB::selectOne('SHOW CREATE TABLE '.$grammar->wrapTable($table));
}

$user = User::factory()->create();
$cartId = DB::table('tienda_carritos')->insertGetId([
    'usuario_id' => $user->id,
    'estado' => 'active',
    'moneda' => 'PEN',
    'vence_en' => now()->addDay(),
    'created_at' => now(),
    'updated_at' => now(),
]);
$generatedBefore = DB::table('tienda_carritos')->where('id', $cartId)->value('usuario_activo_id');
if ((int) $generatedBefore !== (int) $user->id) {
    throw new RuntimeException('La columna generada usuario_activo_id no conserva el propietario activo.');
}

$snapshot = static function () use ($tables, $grammar): array {
    $result = [];
    foreach ($tables as $table) {
        $ddl = (array) DB::selectOne('SHOW CREATE TABLE '.$grammar->wrapTable($table));
        $rows = array_map(static fn ($row): array => (array) $row, DB::table($table)->orderBy('id')->get()->all());
        $result[$table] = [$ddl['Create Table'], $rows];
    }

    return $result;
};

$before = $snapshot();
$migration = require __DIR__.'/../../database/migrations/2026_11_24_000001_rename_store_business_columns_to_spanish.php';
$migration->down();

$oldColumns = [
    ['tienda_conceptos_catalogo', 'category'],
    ['tienda_carritos', 'active_user_id'],
    ['tienda_pedidos', 'total_amount'],
    ['tienda_eventos_auditoria', 'subject_id'],
    ['tienda_eventos_auditoria', 'correlation_id'],
];
foreach ($oldColumns as [$table, $column]) {
    if (! Schema::hasColumn($table, $column)) {
        throw new RuntimeException("Rollback incompleto: {$table}.{$column}");
    }
}
if ((int) DB::table('tienda_carritos')->where('id', $cartId)->value('active_user_id') !== (int) $user->id) {
    throw new RuntimeException('La columna generada active_user_id perdió su expresión durante down().');
}

$migration->up();
if ($snapshot() !== $before) {
    throw new RuntimeException('El ciclo down/up alteró datos, índices, FK o definiciones de columnas.');
}
if ((int) DB::table('tienda_carritos')->where('id', $cartId)->value('usuario_activo_id') !== (int) $user->id) {
    throw new RuntimeException('La columna generada usuario_activo_id no funciona después del upgrade.');
}
foreach ($legacyTables as $table) {
    if ((array) DB::selectOne('SHOW CREATE TABLE '.$grammar->wrapTable($table)) !== $legacyBefore[$table]) {
        throw new RuntimeException("La migración alteró el DDL histórico de {$table}.");
    }
}

DB::table('tienda_carritos')->where('id', $cartId)->delete();
DB::table('users')->where('id', $user->id)->delete();

echo "PASS: upgrade/downgrade de columnas preserva datos, índices, FK, columna generada y seis tablas tm_* en MariaDB 10.11.\n";
