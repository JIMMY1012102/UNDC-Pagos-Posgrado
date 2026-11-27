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
7. **Asistente** expone `POST /api/v1/asistente/mensajes` desde el grupo API de Laravel (sin CSRF de sesión). Laravel valida el mensaje y delega en FastAPI mediante un token interno; el agente Python usa Gemini y un cliente MCP para ejecutar herramientas de lectura. El servidor MCP consulta el catálogo exclusivamente mediante la API pública de Catalogo. Si el agente no está configurado, se conserva el adaptador HTTP Gemini anterior para compatibilidad. No se accede directamente a tablas de otros módulos.

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

El módulo Asistente es stateless: no guarda conversaciones ni agrega tablas. Su código Laravel y Python se agrupa en `app/Modules/Asistente/`; FastAPI se ejecuta como proceso independiente. Laravel conserva la validación, el límite de frecuencia y el endpoint público; el agente Python coordina Gemini con el cliente MCP. La cadena de modelos usa `GEMINI_MODEL`, `GEMINI_FALLBACK_MODELS` y la variable singular heredada `GEMINI_FALLBACK_MODEL`. Las herramientas MCP iniciales son de solo lectura y llaman a `GET /api/v1/tienda/catalogo/oficinas` y `GET /api/v1/tienda/catalogo?office_id=N`. No acceden a MariaDB ni a `tm_*`, ni crean pedidos o procesan pagos. El servicio Python requiere `fastapi`, `uvicorn`, `google-genai`, `mcp` y `httpx`, aislados en su propio entorno virtual; no se agregan dependencias Composer. El proceso interno debe escuchar en loopback y exigir el token compartido `ASSISTANT_AGENT_TOKEN`. La guía operativa está en `app/Modules/Asistente/Agent/README.md`.

Para la Fase 2, cualquier herramienta `consultar_tarifa` deberá reutilizar `Catalogo/Services/OrderQuoteService`, de modo que presente exactamente la misma cotización que Pedidos. No debe consultar `tm_*` ni duplicar reglas de precios.

## Pruebas y despliegue

La CI de GitHub Actions usa PHP/Laravel, SQLite para pruebas unitarias, **MariaDB 10.11 efímera** con seis tablas SISPAGOS ficticias y migraciones reales, y pruebas de navegador. El test MariaDB comprueba **44 tablas**, 13 FK a maestros históricos y que la firma del DDL original `tm_*` no cambie. Nunca se conecta a Oracle Cloud ni a datos reales institucionales.

**No hacer merge ni despliegue automático**: revisar pruebas, documentos, aprobación OTI/Tesorería y el estado real de la rama desplegada. No declarar pagos ni validaciones operativas.

[Mapa general de 44 tablas y 8 módulos](./modelo-datos/00_MAPA_GENERAL_43_TABLAS.md) · [Diagramas específicos](./modelo-datos/README.md).
