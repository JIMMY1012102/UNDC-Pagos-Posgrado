# Base de datos compartida SISPAGOS + Tienda UNDC

**La fuente de datos institucional existente es `tesoreria.sql` (seis tablas `tm_*`).** La tienda Laravel y SISPAGOS deben apuntar al mismo esquema autorizado, incorporando únicamente las nuevas entidades `tienda_*`, sin recrear los maestros de Tesorería.

Se han diseñado **23 tablas `tienda_*` nuevas**, mediante las migraciones originales y una nueva migración incremental de renombrado (sin modificar el historial). La tienda reutiliza conceptualmente `tm_oficina`, `tm_conceptos`, `tm_usuario`, `tm_rol`, `tm_recaudacion` y `tm_resultado` mediante referencias `legacy_*` y consultas autorizadas. `users` (Laravel) es exclusivamente para compradores web; no sustituye `tm_usuario` del personal interno.

- **Documento central:** [Análisis del archivo real y esquema adaptado](../docs/BASE_DE_DATOS.md).
- **Preflight solo lectura:** `php artisan treasury:schema-check` verifica existencia de seis tablas y columnas críticas en la conexión de base de datos.
- **Pruebas SQLite:** `php artisan test` cubre migraciones, mapeo, snapshots y restricciones de duplicidad.
- **MariaDB:** el CI ensaya migraciones y **12 claves foráneas reales** con la estructura histórica ficticia, sin registros institucionales; en cualquier servidor UNDC se requiere autorización previa.
- **Catálogo ya enlazado en código:** `app/Modules/Tienda/Queries/PublishedCatalogQuery.php` consulta los maestros `tm_oficina` y `tm_conceptos` mediante JOIN; todavía no es una pantalla pública.
- **No se versiona** el respaldo `tesoreria.sql` original con sus datos personales.

Las migraciones no agregan, alteran ni eliminan tablas `tm_*`; tampoco se ejecutan en Oracle ni en Tesorería automáticamente. El backend aún debe verificar claves lógicas, reglas de precios, autenticación/roles, validación bancaria, control de concurrencia, constancias y conciliación.


[Equivalencias y aplicación coordinada del renombrado](../docs/NOMBRES_TABLAS_ESPANOL.md).
