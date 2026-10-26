# Diccionario SISPAGOS — DDL histórico sanitizado de CI

**Origen:** `tests/Fixtures/tesoreria_schema_ci.sql`. Las definiciones son de un fixture sintético basado en el respaldo del 20/02/2026; **no verifican el esquema institucional vigente**. No se versionan registros privados.

La columna **Descripción funcional** señala el propósito inferido del nombre y contexto, no un atributo DDL.

## `tm_oficina`

| Campo | Tipo SQL del fixture | ¿Admite NULL? | PK / predeterminado / notas | Descripción funcional |
|---|---|---|---|---|
| `oficina_id` | `INT` | No | PRIMARY KEY; AUTO_INCREMENT | Identificador o referencia |
| `oficina_nom` | `VARCHAR(100)` | No | — | Nombre descriptivo |
| `estado` | `INT` | No | DEFAULT 1 | Estado del registro |

El fixture únicamente define PK individuales; no declara FK internas ni índices UNIQUE secundarios.

## `tm_rol`

| Campo | Tipo SQL del fixture | ¿Admite NULL? | PK / predeterminado / notas | Descripción funcional |
|---|---|---|---|---|
| `rol_id` | `INT` | No | PRIMARY KEY; AUTO_INCREMENT | Identificador o referencia |
| `rol_nom` | `VARCHAR(50)` | No | — | Nombre descriptivo |
| `estado` | `INT` | No | DEFAULT 1 | Estado del registro |

El fixture únicamente define PK individuales; no declara FK internas ni índices UNIQUE secundarios.

## `tm_usuario`

| Campo | Tipo SQL del fixture | ¿Admite NULL? | PK / predeterminado / notas | Descripción funcional |
|---|---|---|---|---|
| `usu_id` | `INT` | No | PRIMARY KEY; AUTO_INCREMENT | Identificador o referencia |
| `usu_nom` | `VARCHAR(45)` | No | — | Nombre descriptivo |
| `usu_ape` | `VARCHAR(45)` | No | — | Atributo histórico institucional |
| `usu_telf` | `VARCHAR(11)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `usu_correo` | `VARCHAR(100)` | No | — | Atributo histórico institucional |
| `rol_id` | `INT` | No | — | Identificador o referencia |
| `oficina_id` | `INT` | No | — | Identificador o referencia |
| `fech_crea` | `DATETIME` | No | DEFAULT CURRENT_TIMESTAMP | Fecha / marca temporal |
| `fech_modi` | `DATETIME` | Sí | DEFAULT NULL; ON UPDATE CURRENT_TIMESTAMP | Fecha / marca temporal |
| `fech_elim` | `DATETIME` | Sí | DEFAULT NULL | Fecha / marca temporal |
| `estado` | `INT` | No | DEFAULT 1 | Estado del registro |

El fixture únicamente define PK individuales; no declara FK internas ni índices UNIQUE secundarios.

## `tm_conceptos`

| Campo | Tipo SQL del fixture | ¿Admite NULL? | PK / predeterminado / notas | Descripción funcional |
|---|---|---|---|---|
| `id_concepto` | `INT` | No | PRIMARY KEY; AUTO_INCREMENT | Identificador o referencia |
| `codigo_concepto` | `VARCHAR(50)` | No | — | Código de correlación o clasificación |
| `codigo_banco` | `VARCHAR(50)` | Sí | DEFAULT NULL | Código de correlación o clasificación |
| `es_gravado` | `TINYINT(1)` | Sí | DEFAULT 0 | Atributo histórico institucional |
| `concepto` | `VARCHAR(255)` | No | — | Atributo histórico institucional |
| `clasificador_ingresos` | `INT` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `costo` | `DECIMAL(10,2)` | Sí | DEFAULT NULL | Monto o componente monetario |
| `cuenta_corriente` | `INT` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `plan_contable` | `INT` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `centro_costo` | `INT` | Sí | DEFAULT NULL | Monto o componente monetario |
| `afecto_division` | `TINYINT(1)` | Sí | DEFAULT 0 | Atributo histórico institucional |
| `creado_en` | `TIMESTAMP` | No | DEFAULT CURRENT_TIMESTAMP | Fecha / marca temporal |

El fixture únicamente define PK individuales; no declara FK internas ni índices UNIQUE secundarios.

## `tm_recaudacion`

| Campo | Tipo SQL del fixture | ¿Admite NULL? | PK / predeterminado / notas | Descripción funcional |
|---|---|---|---|---|
| `id_recaudacion` | `INT` | No | PRIMARY KEY; AUTO_INCREMENT | Identificador o referencia |
| `codigo_deudor` | `VARCHAR(20)` | Sí | DEFAULT NULL | Código de correlación o clasificación |
| `codigo_cuota` | `VARCHAR(20)` | Sí | DEFAULT NULL | Código de correlación o clasificación |
| `nombre_deudor` | `VARCHAR(150)` | Sí | DEFAULT NULL | Nombre descriptivo |
| `referencia1` | `VARCHAR(50)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `referencia2` | `VARCHAR(50)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `emision` | `DATE` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `vencimiento` | `DATE` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `documento` | `VARCHAR(10)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `moneda` | `VARCHAR(5)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `concepto1` | `DECIMAL(10,2)` | Sí | DEFAULT NULL | Monto o componente monetario |
| `concepto2` | `DECIMAL(10,2)` | Sí | DEFAULT NULL | Monto o componente monetario |
| `concepto3` | `DECIMAL(10,2)` | Sí | DEFAULT NULL | Monto o componente monetario |
| `concepto4` | `DECIMAL(10,2)` | Sí | DEFAULT NULL | Monto o componente monetario |
| `concepto5` | `DECIMAL(10,2)` | Sí | DEFAULT NULL | Monto o componente monetario |
| `concepto6` | `DECIMAL(10,2)` | Sí | DEFAULT NULL | Monto o componente monetario |
| `concepto7` | `DECIMAL(10,2)` | Sí | DEFAULT NULL | Monto o componente monetario |
| `tipo_operacion` | `CHAR(1)` | Sí | DEFAULT NULL | Atributo histórico institucional |

El fixture únicamente define PK individuales; no declara FK internas ni índices UNIQUE secundarios.

## `tm_resultado`

| Campo | Tipo SQL del fixture | ¿Admite NULL? | PK / predeterminado / notas | Descripción funcional |
|---|---|---|---|---|
| `i_resultado` | `INT` | No | PRIMARY KEY; AUTO_INCREMENT | Atributo histórico institucional |
| `codigo_rubro` | `VARCHAR(5)` | No | — | Código de correlación o clasificación |
| `codigo_empresa` | `VARCHAR(5)` | No | — | Código de correlación o clasificación |
| `codigo_servicio` | `VARCHAR(5)` | No | — | Código de correlación o clasificación |
| `moneda` | `VARCHAR(10)` | No | — | Atributo histórico institucional |
| `codigo_deudor` | `VARCHAR(20)` | No | — | Código de correlación o clasificación |
| `codigo_cuota` | `VARCHAR(30)` | No | — | Código de correlación o clasificación |
| `numero_documento` | `VARCHAR(20)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `nombre_deudor` | `VARCHAR(150)` | No | — | Nombre descriptivo |
| `fecha_pago` | `DATE` | Sí | DEFAULT NULL | Fecha / marca temporal |
| `hora_pago` | `TIME` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `importe_pagado` | `DECIMAL(12,2)` | Sí | DEFAULT 0; DEFAULT 0.00 | Monto o componente monetario |
| `importe_mora` | `DECIMAL(12,2)` | Sí | DEFAULT 0; DEFAULT 0.00 | Monto o componente monetario |
| `importe_descuento` | `DECIMAL(12,2)` | Sí | DEFAULT 0; DEFAULT 0.00 | Monto o componente monetario |
| `fecha_emision` | `DATE` | Sí | DEFAULT NULL | Fecha / marca temporal |
| `fecha_vencimiento` | `DATE` | Sí | DEFAULT NULL | Fecha / marca temporal |
| `numero_operacion` | `VARCHAR(30)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `numero_cheque` | `VARCHAR(30)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `indicador_cheque` | `VARCHAR(30)` | Sí | DEFAULT NULL | Atributo histórico institucional |
| `created_at` | `TIMESTAMP` | No | DEFAULT CURRENT_TIMESTAMP | Fecha / marca temporal |

El fixture únicamente define PK individuales; no declara FK internas ni índices UNIQUE secundarios.

## Integridad y alcance

- `tm_usuario.rol_id` y `tm_usuario.oficina_id` representan vínculos lógicos con roles y oficinas, sin FK declarada en el fixture.
- `tm_conceptos.centro_costo` no se trata como FK hacia `tm_oficina`.
- Los 12 enlaces físicos con las tablas de tienda se expresan en la migración 000005, sin tocar las tablas históricas.
- Verificación pendiente: contrastar el DDL del fixture con el institucional vigente mediante metadatos sanitizados autorizados.
- Se prohíbe publicar filas reales, correos, contraseñas, referencias personales o registros bancarios.
