# Optimización segura del modelo relacional — UNDC Market

**Alcance:** propuesta incremental, lista para revisión en PR. **No desplegada a Oracle Cloud.**

## Regla de compatibilidad

- No cambiar estructura, nombre, contenido ni claves de las seis tablas históricas `tm_oficina`, `tm_rol`, `tm_usuario`, `tm_conceptos`, `tm_recaudacion` y `tm_resultado`.
- No cambiar índices existentes ni eliminar columnas de `store_*`. Conservar claves foráneas y relaciones documentadas.
- Las pruebas de GitHub Actions usan **MariaDB 10.11 efímera y tablas tm_* ficticias**, sin conectarse a `tesoreria_dev` ni a Oracle.
- No ejecutar automáticamente `php artisan migrate` en ningún servidor; la revisión y autorización de OTI/Tesorería sigue siendo obligatoria.

## Mejora incremental propuesta

`app/Modules/Tienda/Queries/PublishedCatalogQuery.php` filtra por `publication.legacy_office_id` y `publication.is_published`. Las claves únicas originales de `store_catalog_items` comienzan por `legacy_office_id` pero no incluyen el estado `is_published`.

Se agrega un índice no único `idx_store_catalog_office_published (legacy_office_id, is_published)` mediante una **nueva migración aditiva**. El índice puede reducir la lectura de productos no publicados en oficinas con catálogos grandes; **no se afirma una mejora medida de rendimiento** hasta ejecutar `EXPLAIN` y cargas representativas.

## Evidencia prevista en CI

1. Crear las seis tablas históricas ficticias en una MariaDB temporal y registrar su firma de esquema (DDL).
2. Ejecutar migraciones Laravel solo en la base temporal.
3. Comprobar las 12 FK de `store_*` hacia `tm_*` y que el DDL histórico conserve la misma firma.
4. Comprobar la presencia y orden de columnas del nuevo índice.
5. Ejecutar pruebas Laravel, flujo de precompra, compilación frontend y pruebas de navegador existentes.

## Revisión antes de cualquier despliegue

- Comprobar el `EXPLAIN` de la consulta de catálogo con datos sintéticos de volumen representativo; una base pequeña puede no beneficiarse del índice.
- Revisar posibles índices redundantes, consultas frecuentes, `slow query log` y coste de escrituras.
- Verificar que el código y las migraciones desplegadas en Oracle coinciden con la rama realmente instalada.
- Realizar respaldo y revisión de OTI/Tesorería antes de aplicar migraciones en la base conectada a la aplicación.

**No se cambia** el esquema de SISPAGOS ni se considera implementada la integración real de pagos o Pay-me.
