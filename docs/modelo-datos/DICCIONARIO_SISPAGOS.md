# Diccionario institucional SISPAGOS — fuente histórica parcial

**Origen:** `docs/BASE_DE_DATOS.md` construido a partir del respaldo de febrero de 2026. **No** se almacena en GitHub el volcado con datos personales, ni se confunde con el DDL vigente del servidor.

| Tabla | Clave primaria documentada | Responsabilidad | Columnas identificadas (lista parcial) |
|---|---|---|---|
| `tm_oficina` | `oficina_id` — INT firmado | Oficinas institucionales | `oficina_nom, estado` |
| `tm_rol` | `rol_id` — INT firmado | Roles internos SISPAGOS | `rol_nom, estado` |
| `tm_usuario` | `usu_id` — INT firmado | Operadores institucionales | `rol_id, oficina_id, usu_correo, estado, fech_elim` |
| `tm_conceptos` | `id_concepto` — INT firmado | Conceptos e importes oficiales | `codigo_concepto, concepto, costo, centro_costo, codigo_banco` |
| `tm_recaudacion` | `id_recaudacion` — INT firmado | Archivo de recaudación bancaria | `codigo_deudor, codigo_cuota, referencia1/2, concepto1..concepto7, tipo_operacion` |
| `tm_resultado` | `i_resultado` — INT firmado | Resultados bancarios importados | `codigo_deudor, codigo_cuota, numero_operacion, importe_pagado, fecha_pago, hora_pago` |

## Integridad y relaciones
- El respaldo histórico documentado tiene PRIMARY KEY y no declara FOREIGN KEY ni UNIQUE adicionales dentro de las seis tablas originales.
- `tm_usuario.rol_id` y `tm_usuario.oficina_id` sugieren asociaciones lógicas con `tm_rol` y `tm_oficina`; no dibujar como FK físicas.
- `tm_conceptos.centro_costo` **no** constituye FK confirmada a `tm_oficina`.
- Los 12 enlaces físicos documentados son desde tablas nuevas hacia cinco tablas originales y se definen únicamente en la migración 000005.

## Alcance no verificado
- No se dispone en el repositorio de un DDL completo y sanitizado de estas seis tablas; no es responsable inventar tipos, nullability, índices o columnas restantes.
- Se requiere un **SHOW CREATE TABLE** / `information_schema` de solo lectura y sin filas de datos, autorizado por OTI/Tesorería, para cerrar el diccionario completo.
- No publicar archivos SQL que incluyan información de estudiantes, contribuyentes, contraseñas o registros bancarios.

## Regla de conservación
No renombrar, alterar, eliminar ni recrear tablas `tm_*` durante esta fase documental.