# Diccionario SISPAGOS — estructura histórica reproducida en CI

Fuente: `tests/Fixtures/tesoreria_schema_ci.sql`. Esta es una copia sanitizada de estructura histórica de febrero de 2026, **no** un DDL obtenido de producción actual. No contiene datos personales.

Los campos, tipos y restricciones siguientes se extraen directamente del fixture SQL.

## `tm_oficina`

| Campo | Tipo SQL | Nulabilidad | Claves y valores predeterminados |
|---|---|---|---|
| `oficina_id` | `INT` | NO | PRIMARY KEY; AUTO_INCREMENT |
| `estado` | `INT` | NO | DEFAULT 1 |

No se declaran FK ni UNIQUE adicionales para esta tabla en el fixture.

## `tm_rol`

| Campo | Tipo SQL | Nulabilidad | Claves y valores predeterminados |
|---|---|---|---|
| `rol_id` | `INT` | NO | PRIMARY KEY; AUTO_INCREMENT |
| `estado` | `INT` | NO | DEFAULT 1 |

No se declaran FK ni UNIQUE adicionales para esta tabla en el fixture.

## `tm_usuario`

| Campo | Tipo SQL | Nulabilidad | Claves y valores predeterminados |
|---|---|---|---|
| `usu_id` | `INT` | NO | PRIMARY KEY; AUTO_INCREMENT |
| `rol_id` | `INT` | NO | — |
| `oficina_id` | `INT` | NO | — |
| `fech_crea` | `DATETIME` | NO | DEFAULT CURRENT_TIMESTAMP |
| `fech_modi` | `DATETIME` | SÍ | DEFAULT NULL; ON UPDATE CURRENT_TIMESTAMP |
| `fech_elim` | `DATETIME` | SÍ | DEFAULT NULL |
| `estado` | `INT` | NO | DEFAULT 1 |

No se declaran FK ni UNIQUE adicionales para esta tabla en el fixture.

## `tm_conceptos`

| Campo | Tipo SQL | Nulabilidad | Claves y valores predeterminados |
|---|---|---|---|
| `id_concepto` | `INT` | NO | PRIMARY KEY; AUTO_INCREMENT |
| `clasificador_ingresos` | `INT` | SÍ | DEFAULT NULL |
| `cuenta_corriente` | `INT` | SÍ | DEFAULT NULL |
| `plan_contable` | `INT` | SÍ | DEFAULT NULL |
| `centro_costo` | `INT` | SÍ | DEFAULT NULL |
| `creado_en` | `TIMESTAMP` | NO | DEFAULT CURRENT_TIMESTAMP |

No se declaran FK ni UNIQUE adicionales para esta tabla en el fixture.

## `tm_recaudacion`

| Campo | Tipo SQL | Nulabilidad | Claves y valores predeterminados |
|---|---|---|---|
| `id_recaudacion` | `INT` | NO | PRIMARY KEY; AUTO_INCREMENT |
| `emision` | `DATE` | SÍ | DEFAULT NULL |
| `vencimiento` | `DATE` | SÍ | DEFAULT NULL |

No se declaran FK ni UNIQUE adicionales para esta tabla en el fixture.

## `tm_resultado`

| Campo | Tipo SQL | Nulabilidad | Claves y valores predeterminados |
|---|---|---|---|
| `i_resultado` | `INT` | NO | PRIMARY KEY; AUTO_INCREMENT |
| `fecha_pago` | `DATE` | SÍ | DEFAULT NULL |
| `hora_pago` | `TIME` | SÍ | DEFAULT NULL |
| `fecha_emision` | `DATE` | SÍ | DEFAULT NULL |
| `fecha_vencimiento` | `DATE` | SÍ | DEFAULT NULL |
| `created_at` | `TIMESTAMP` | NO | DEFAULT CURRENT_TIMESTAMP |

No se declaran FK ni UNIQUE adicionales para esta tabla en el fixture.

## Relaciones y límites

- `tm_usuario.rol_id` y `tm_usuario.oficina_id` son referencias lógicas; el fixture no declara FK históricas.
- `tm_conceptos.centro_costo` no es una FK demostrada a `tm_oficina`.
- Las 12 FK desde tienda hacia `tm_*` se definen en la migración 000005 y se documentan por separado.
- El modelo vigente institucional aún debe validarse mediante metadatos autorizados, sin exportar registros personales.
- No modificar las tablas `tm_*` ni ejecutar SQL para este trabajo.