# UNDC Market — arquitectura modular basada en DDD ligero

**Estado: propuesta implementada en rama de desarrollo, no desplegada.** Un Laravel, una MariaDB y siete módulos responsables de **43 tablas**.

| Módulo real (`app/Modules/`) | Tablas | Alcance |
|---|---:|---|
| Sispagos | 6 | Seis maestros históricos de SISPAGOS (sin cambios) |
| Catalogo | 3 | Oficinas publicadas, conceptos y versiones de tarifa |
| Usuarios | 4 | Cuentas web, perfiles, identidades OAuth y beneficiarios |
| Pedidos | 5 | Carrito, líneas, órdenes y sus estados |
| Pagos | 6 | Intentos, transacciones, cobro aplicado, webhooks e incidencias |
| Tesoreria | 6 | Conciliación, validación, certificados y auditoría |
| Seguridad | 13 | Roles y permisos Spatie, sesiones y soporte de Laravel |

## Flujo actual y contratos

`Frontend → controlador del módulo → servicio del módulo → tablas propias`.

1. **Catalogo** muestra las publicaciones; usa un **adaptador de solo lectura Sispagos** que une `tienda_conceptos_catalogo` con los conceptos y oficinas maestros `tm_*`.
2. **Usuarios** conserva cuentas y perfiles; las rutas originales `/tienda/login` y `/api/v1/acceso/*` no cambian.
3. **Pedidos** mantiene carrito y precompra; para verificar disponibilidad de un producto consume `Catalogo/Contracts/CatalogItemLookup`, **sin consultar directamente `tm_*`**.
4. **Pagos** tiene estructura de datos preparada, pero no hay cobro real Pay-me implementado.
5. **Tesoreria** tiene estructura para conciliación, validación y constancias, todavía sin un flujo funcional institucional validado.
6. **Seguridad** soporta permisos, sesiones, caché y colas sin introducir endpoints administrativos.

Las vistas existentes en `resources/views/modules/acceso` y `resources/views/modules/tienda` conservan su nombre por compatibilidad de presentación; **no** son módulos de backend aparte de los siete.

## Regla de datos

- Una única base `tesoreria_dev` con las 43 tablas actuales.
- Cada módulo administra las tablas asignadas; evitar escrituras directas desde otros módulos.
- **No alterar ninguna de las seis `tm_*`**. Los módulos nuevos las consultan a través del adaptador Sispagos bajo permisos autorizados.
- No mover, renombrar ni volver a ejecutar migraciones Laravel ya instaladas. Sus nombres están registrados en la tabla `migrations`.
- Mantener las FK físicas que garantizan integridad relacional. Contratos de servicio no sustituyen relaciones SQL.
- Las 23 tablas de negocio usan `tienda_*`; se conserva el historial original y se agrega una migración de renombrado. [Equivalencias y despliegue coordinado](./NOMBRES_TABLAS_ESPANOL.md).
- Para funcionalidades futuras, agregar servicios y migraciones nuevas de forma incremental, no reestructurar todo.

## Pruebas y despliegue

La CI de GitHub Actions usa PHP/Laravel, SQLite para pruebas unitarias, **MariaDB 10.11 efímera** con seis tablas SISPAGOS ficticias y migraciones reales, y pruebas de navegador. El test MariaDB comprueba **43 tablas**, 12 FK a maestros históricos y que la firma del DDL original `tm_*` no cambie. Nunca se conecta a Oracle Cloud ni a datos reales institucionales.

**No hacer merge ni despliegue automático**: revisar pruebas, documentos, aprobación OTI/Tesorería y el estado real de la rama desplegada. No declarar pagos ni validaciones operativas.

[Mapa general de 43 tablas y 7 colores](./modelo-datos/00_MAPA_GENERAL_43_TABLAS.md) · [Diagramas específicos](./modelo-datos/README.md).
