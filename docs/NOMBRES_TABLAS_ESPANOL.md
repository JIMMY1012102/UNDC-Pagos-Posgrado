# Nombres en español de las tablas de negocio

Las **23 tablas propias de UNDC Market** usan el prefijo `tienda_` y nombres en español, sin tildes. Es una sola base de datos con siete módulos; no se crean bases independientes.

El nombre describe lo que almacena cada tabla según las migraciones existentes. No acredita que el flujo esté implementado o aprobado institucionalmente.

| Nombre anterior | Nombre nuevo | Función |
|---|---|---|
| `store_office_settings` | `tienda_configuracion_oficinas` | Configuración y publicación de cada oficina en la tienda. |
| `store_catalog_items` | `tienda_conceptos_catalogo` | Conceptos del catálogo vinculados a los maestros de Tesorería. |
| `store_catalog_price_versions` | `tienda_versiones_tarifas` | Importes y periodos de vigencia de las tarifas del catálogo. |
| `store_customer_profiles` | `tienda_perfiles_compradores` | Datos del perfil de cada comprador web. |
| `store_oauth_identities` | `tienda_identidades_oauth` | Vínculos entre cuentas web e identidades del proveedor OAuth. |
| `store_beneficiaries` | `tienda_beneficiarios` | Personas beneficiarias asociadas al perfil comprador. |
| `store_carts` | `tienda_carritos` | Cabeceras y estado de los carritos de compra. |
| `store_cart_items` | `tienda_detalles_carrito` | Líneas o conceptos incluidos en cada carrito. |
| `store_orders` | `tienda_pedidos` | Cabeceras de los pedidos y datos del comprador al confirmar. |
| `store_order_items` | `tienda_detalles_pedido` | Líneas del pedido con datos e importes conservados al confirmar. |
| `store_order_status_history` | `tienda_historial_estados_pedido` | Cambios de estado del pedido y su trazabilidad. |
| `store_payment_attempts` | `tienda_intentos_pago` | Intentos de iniciar un pago con un proveedor o canal. |
| `store_payment_transactions` | `tienda_transacciones_pago` | Cobros observados y su estado de verificación, incluso duplicados. |
| `store_order_payment_claims` | `tienda_pagos_aplicados_pedidos` | Aplicación de una transacción a un pedido; máximo una por pedido. No son reclamos. |
| `store_payment_webhook_events` | `tienda_eventos_notificacion_pago` | Eventos recibidos por webhook, validación de firma y procesamiento. |
| `store_payment_incidents` | `tienda_incidencias_pago` | Incidencias de cobros, estados inciertos o diferencias. |
| `store_refunds` | `tienda_reembolsos` | Solicitudes y resultados de devolución de un cobro. |
| `store_reconciliation_batches` | `tienda_lotes_conciliacion` | Cabeceras de lotes de conciliación bancaria o de pasarela. |
| `store_reconciliation_entries` | `tienda_detalles_conciliacion` | Filas de cada lote y sus coincidencias con cobros y registros históricos. |
| `store_external_system_links` | `tienda_vinculos_sistemas_externos` | Referencias a registros de otros sistemas y estado de sincronización. |
| `store_payment_validations` | `tienda_validaciones_pago` | Revisión y decisión de Tesorería sobre un pago. |
| `store_validation_certificates` | `tienda_constancias_validacion` | Constancias vinculadas a la validación de un pago, con emisión y revocación. |
| `store_audit_events` | `tienda_eventos_auditoria` | Eventos de auditoría con actor, entidad afectada y contexto seguro. |

## Alcance y compatibilidad

- Se conservan las seis tablas históricas `tm_*` y las tablas originales de soporte de Laravel/Spatie (`users`, `sessions`, `migrations`, permisos, caché y colas).
- Se conservan columnas, valores de estados, rutas HTTP, claves JSON, índices, restricciones y datos. La solicitud se aplica a los nombres de las tablas nuevas del negocio.
- Las migraciones originales conservan su nombre y contenido. La migración incremental `2026_11_09_000001_rename_store_business_tables_to_spanish.php` se ejecuta después de ellas; una instalación nueva también pasa por los nombres anteriores y termina con los nombres nuevos.
- El rollback de esta migración devuelve los 23 nombres originales sin borrar filas. Un rollback posterior de las migraciones originales conserva su comportamiento original, que sí elimina tablas; no confundir ambos alcances.

## Aplicación coordinada

El código de esta rama consulta `tienda_*` y requiere que se aplique la migración nueva en el mismo despliegue. Publicar solo el código sobre una base con `store_*` provoca errores de tablas inexistentes.

La aplicación en Oracle o en una base institucional requiere la autorización correspondiente. Preparar respaldo verificado y ventana de mantenimiento; detener peticiones y trabajadores que usen el esquema anterior y coordinar el cambio de versión con la migración. En MariaDB/MySQL se usa una sola sentencia `RENAME TABLE`; en SQLite, una transacción. Si falta una tabla origen o existe una tabla destino, la validación rechaza el cambio antes del primer renombrado, sin sobrescribir ni fusionar datos.

Ensayar primero en una copia de desarrollo autorizada. Después de la migración, comprobar catálogo, acceso, carrito y precompra. Si se revierte, coordinar también la versión del código con la recuperación de los nombres anteriores.

## Verificación automatizada

Las pruebas SQLite verifican los 23 nombres, conservación de columnas y datos, relaciones y rechazo de colisiones antes de modificar tablas. La CI MariaDB verifica el esquema completo antes y después de revertir y reaplicar el renombrado, las 12 FK al legado y la firma de las seis tablas históricas. Las pruebas existentes de negocio usan los nombres en español.
