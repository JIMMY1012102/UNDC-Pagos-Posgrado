<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Read-only preflight for the six canonical SISPAGOS treasury tables.
 *
 * The shared database must import the original schema through the institution's
 * authorized process. Store migrations intentionally do not recreate it.
 */
final class LegacyTreasurySchema
{
    public const REQUIRED_COLUMNS = [
        'tm_oficina' => ['oficina_id', 'oficina_nom', 'estado'],
        'tm_rol' => ['rol_id', 'rol_nom', 'estado'],
        'tm_usuario' => ['usu_id', 'usu_nom', 'usu_ape', 'usu_correo', 'rol_id', 'oficina_id', 'estado'],
        'tm_conceptos' => ['id_concepto', 'codigo_concepto', 'concepto', 'costo', 'centro_costo'],
        'tm_recaudacion' => ['id_recaudacion', 'codigo_deudor', 'codigo_cuota', 'nombre_deudor', 'concepto1', 'concepto7', 'tipo_operacion'],
        'tm_resultado' => ['i_resultado', 'codigo_deudor', 'codigo_cuota', 'nombre_deudor', 'importe_pagado', 'numero_operacion', 'fecha_pago'],
    ];

    /** @return list<string> */
    public static function missing(): array
    {
        $problems = [];

        foreach (self::REQUIRED_COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $problems[] = "Tabla ausente: {$table}";
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $problems[] = "Columna ausente: {$table}.{$column}";
                }
            }
        }

        return $problems;
    }
}
