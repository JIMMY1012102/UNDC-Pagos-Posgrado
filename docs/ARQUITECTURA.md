# UNDC Market — arquitectura modular basada en DDD ligero

**Estado: propuesta implementada en rama de desarrollo, no desplegada.** Un Laravel, una MariaDB y ocho módulos; los siete módulos de negocio existentes conservan sus **44 tablas** y Asistente no agrega tablas.

| Módulo real (`app/Modules/`) | Tablas | Alcance |
|---|---:|---|
| Sispagos | 6 | Seis maestros históricos de SISPAGOS (sin cambios) |
| Catalogo | 4 | Oficinas publicadas, conceptos y versiones de tarifa |
| Usuarios | 4 | Cuentas web, perfiles, identidades OAuth y beneficiarios |
| Pedidos | 5 | Carrito, líneas, órdenes y sus estados |
| Pagos | 6 | Intentos, transacciones, cobro aplicado, webhooks e incidencias |
| Tesoreria | 6 | Conciliación, validación, certificados y auditoría |
| Seguridad | 13 | Roles y permisos Spatie, sesiones y soporte de Laravel |
| Asistente | 0 | API conversacional y adaptador de proveedor Gemini; sin persistencia |

## Flujo actual y contratos

`Frontend → controlador del módulo → servicio del módulo → tablas propias`.

1. **Catalogo** muestra las publicaciones; usa un **adaptador de solo lectura Sispagos** que une `tienda_conceptos_catalogo` con los conceptos y oficinas maestros `tm_*`.
2. **Usuarios** conserva cuentas y perfiles; las rutas originales `/tienda/login` y `/api/v1/acceso/*` no cambian.
3. **Pedidos** mantiene carrito, precompra y pedidos pendientes con tarifas aprobadas e historial por propietario (ver [flujo de pedidos](./PEDIDOS_Y_PAGO_POSGRADO.md)); para verificar disponibilidad de un producto consume `Catalogo/Contracts/CatalogItemLookup`, **sin consultar directamente `tm_*`**.
4. **Pagos** tiene estructura de datos preparada, pero no hay cobro real Pay-me implementado.
5. **Tesoreria** tiene estructura para conciliación, validación y constancias, todavía sin un flujo funcional institucional validado.
6. **Seguridad** soporta permisos, sesiones, caché y colas sin introducir endpoints administrativos.
7. **Asistente** expone `POST /api/v1/asistente/mensajes` desde el grupo API de Laravel (sin CSRF de sesión). El controlador valida mensajes de hasta 4.000 caracteres; el servicio utiliza el contrato `Asistente/Contracts/AssistantProvider`, enlazado al adaptador HTTP Gemini. El proveedor recorre una lista ordenada de modelos configurada por entorno y no consulta tablas de otros módulos. Las pruebas usan HTTP falso y no realizan llamadas externas.

Las vistas existentes en `resources/views/modules/acceso` y `resources/views/modules/tienda` conservan su nombre por compatibilidad de presentación; **no** son módulos de backend aparte de los ocho.

## Regla de datos

- Una única base `tesoreria_dev` con las 44 tablas actuales de los módulos con persistencia. Asistente no agrega tablas.
- Cada módulo administra las tablas asignadas; evitar escrituras directas desde otros módulos.
- **No alterar ninguna de las seis `tm_*`**. Los módulos nuevos que necesiten información de SISPAGOS deben consumir las capacidades públicas del adaptador correspondiente; Asistente no accede a esas tablas.
- No mover, renombrar ni volver a ejecutar migraciones Laravel ya instaladas. Sus nombres están registrados en la tabla `migrations`.
- Mantener las FK físicas que garantizan integridad relacional. Contratos de servicio no sustituyen relaciones SQL.
- Las 24 tablas de negocio usan `tienda_*`; se conserva el historial original y se agrega una migración de renombrado de las 23 originales y otra para `tienda_categorias`. [Equivalencias y despliegue coordinado](./NOMBRES_TABLAS_ESPANOL.md).
- Para funcionalidades futuras, agregar servicios y migraciones nuevas de forma incremental, no reestructurar todo.

## Asistente y evolución

El módulo Asistente es stateless: no guarda conversaciones ni añade dependencias Composer. La integración usa el cliente HTTP de Laravel y configuración privada por entorno. El proveedor intenta primero `GEMINI_MODEL` y después cada elemento de `GEMINI_FALLBACK_MODELS`, respetando el orden configurado; mantiene compatibilidad con la variable singular heredada `GEMINI_FALLBACK_MODEL`. Reintenta ante fallos de conexión, límites de cuota y errores temporales del proveedor. Si todos los modelos fallan, responde con indisponibilidad controlada. Esta estrategia no sustituye una conexión a Internet ni evita una caída general del servicio o el agotamiento compartido de cuota. La carpeta `Tools/` todavía no forma parte del módulo.

Para la Fase 2, cualquier herramienta `consultar_tarifa` deberá reutilizar `Catalogo/Services/OrderQuoteService`, de modo que presente exactamente la misma cotización que Pedidos. No debe consultar `tm_*` ni duplicar reglas de precios.

## Pruebas y despliegue

La CI de GitHub Actions usa PHP/Laravel, SQLite para pruebas unitarias, **MariaDB 10.11 efímera** con seis tablas SISPAGOS ficticias y migraciones reales, y pruebas de navegador. El test MariaDB comprueba **44 tablas**, 13 FK a maestros históricos y que la firma del DDL original `tm_*` no cambie. Nunca se conecta a Oracle Cloud ni a datos reales institucionales.

**No hacer merge ni despliegue automático**: revisar pruebas, documentos, aprobación OTI/Tesorería y el estado real de la rama desplegada. No declarar pagos ni validaciones operativas.

[Mapa general de 44 tablas y 8 módulos](./modelo-datos/00_MAPA_GENERAL_43_TABLAS.md) · [Diagramas específicos](./modelo-datos/README.md).
