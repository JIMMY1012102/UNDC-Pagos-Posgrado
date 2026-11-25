<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Process\Process;
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

// La migración down() ya dejó el esquema antiguo. Simular que su registro no
// existe porque un intento anterior falló antes de completar todos los DDL.
$migrationName = '2026_11_24_000001_rename_store_business_columns_to_spanish';
DB::table('migrations')->where('migration', $migrationName)->delete();

$runMigrate = static function (): array {
    $process = new Process([PHP_BINARY, base_path('artisan'), 'migrate', '--force'], base_path());
    $process->run();

    return [$process->getExitCode(), $process->getOutput().$process->getErrorOutput()];
};

// Conflicto deliberado: origen y destino presentes a la vez. La validación
// debe fallar antes de tocar cualquier otra columna pendiente.
DB::statement('ALTER TABLE tienda_conceptos_catalogo ADD COLUMN categoria_codigo VARCHAR(255) NULL');
$conflictSnapshot = $snapshot();
[$conflictExit, $conflictOutput] = $runMigrate();
if ($conflictExit === 0 || ! str_contains($conflictOutput, 'Conflicto en tienda_conceptos_catalogo')) {
    throw new RuntimeException("El conflicto de columnas no produjo el error esperado: {$conflictOutput}");
}
if ($snapshot() !== $conflictSnapshot) {
    throw new RuntimeException('La migración modificó el esquema o los datos a pesar del conflicto de validación.');
}
DB::statement('ALTER TABLE tienda_conceptos_catalogo DROP COLUMN categoria_codigo');

// Simular un fallo a mitad de la migración: tres renombrados de tablas distintas
// ya quedaron confirmados en MariaDB, mientras el resto conserva nombres antiguos.
DB::statement('ALTER TABLE tienda_conceptos_catalogo RENAME COLUMN category TO categoria_codigo');
DB::statement('ALTER TABLE tienda_perfiles_compradores RENAME COLUMN user_id TO usuario_id');
DB::statement('ALTER TABLE tienda_intentos_pago RENAME COLUMN order_id TO pedido_id');

[$migrateExit, $migrateOutput] = $runMigrate();
if ($migrateExit !== 0) {
    throw new RuntimeException("migrate --force no reanudó el esquema parcialmente aplicado: {$migrateOutput}");
}
if (! DB::table('migrations')->where('migration', $migrationName)->exists()) {
    throw new RuntimeException('La migración reanudada no quedó registrada en la tabla migrations.');
}

// Comprobar cada equivalencia, no solo las tres columnas usadas en la simulación.
$renameMap = (new ReflectionClass($migration))->getReflectionConstant('RENAMES')->getValue();
foreach ($renameMap as $table => $columns) {
    foreach ($columns as $source => $target) {
        if (! Schema::hasColumn($table, $target) || Schema::hasColumn($table, $source)) {
            throw new RuntimeException("Renombrado incompleto tras reanudar: {$table}.{$source} -> {$target}");
        }
    }
}
if ((int) DB::table('tienda_carritos')->where('id', $cartId)->value('usuario_activo_id') !== (int) $user->id) {
    throw new RuntimeException('usuario_activo_id no funciona después de reanudar la migración.');
}
if (! DB::table('tienda_carritos')->where('id', $cartId)->where('usuario_activo_id', $user->id)->exists()) {
    throw new RuntimeException('El índice uq_cart_active_user no permite localizar correctamente el carrito activo.');
}
$activeIndex = DB::selectOne("SHOW INDEX FROM tienda_carritos WHERE Key_name = 'uq_cart_active_user'");
if (! $activeIndex || $activeIndex->Column_name !== 'usuario_activo_id') {
    throw new RuntimeException('El índice uq_cart_active_user no quedó asociado a usuario_activo_id.');
}
if ($snapshot() !== $before) {
    throw new RuntimeException('La reanudación parcial alteró DDL o datos respecto del esquema completo esperado.');
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
