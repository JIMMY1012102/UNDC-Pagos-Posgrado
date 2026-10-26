# Base de datos de la tienda UNDC — propuesta v1

Esta rama incorpora **25 tablas de negocio** mediante cuatro migraciones Laravel agrupadas por identidad/catálogo, comercio, pagos y Tesorería. También conserva las migraciones estándar de Laravel y contiene pruebas de integridad y deduplicación.

Ver el documento principal: [Modelo relacional, decisiones y controles pendientes](../docs/BASE_DE_DATOS.md).

## Seguridad de ejecución

- **No se ha migrado** ninguna base institucional ni el servidor Oracle desde esta rama.
- El SQL local `~/Proyectos/undc-pagos-db` (14 tablas, 4 vistas, 9 triggers según reporte anterior) no se ha importado: hace falta comparar entidades y validar con Tesorería.
- Esta base es el esquema de la **nueva tienda**, NO una sustitución automática de las tablas históricas `tm_recaudacion` y `tm_resultado` de SISPAGOS.
- Ejecutar `php artisan test` para pruebas SQLite en memoria. Migrar únicamente una base nueva de prueba autorizada y configurada explícitamente.
- Los servicios, políticas, controladores, restricciones transaccionales de aplicación e integraciones bancarias están pendientes. Las migraciones **no** constituyen un flujo de pago en funcionamiento.
