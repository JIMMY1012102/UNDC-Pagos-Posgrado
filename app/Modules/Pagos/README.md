# Pagos

Propietario de seis tablas: `tienda_intentos_pago`, `tienda_transacciones_pago`, `tienda_pagos_aplicados_pedidos`, `tienda_eventos_notificacion_pago`, `tienda_incidencias_pago`, `tienda_reembolsos`.

La base está preparada para intentos, verificaciones, incidencias y reembolsos, **no significa que Pay-me o cobros reales estén habilitados**. No exponer rutas hasta completar la integración autorizada y las verificaciones servidor-servidor.


## Núcleo de estados y aplicación (Fase 1)

- `AttemptStatus` distingue intentos abiertos de los terminales conocidos. Un estado desconocido nunca se considera terminal.
- `TransactionStatus::fromProviderState()` mapea los estados documentados del proveedor; un valor no reconocido queda como `uncertain` y nunca habilita la aplicación.
- `ApplyVerifiedPaymentService` bloquea transacción y pedido dentro de una transacción DB. Exige estado autorizable, referencia externa, fuente de verificación servidor-servidor y la columna física `verificado_en`; la fuente se almacena en `fuente_verificacion`; compara monto en centavos y moneda; inserta una sola aplicación por pedido y solo entonces establece `pagado_en` y `estado=paid`.
- Repeticiones de la misma transacción son idempotentes. Un segundo cobro verificado, un importe/moneda distinto o un pago de un pedido vencido se registra como incidencia abierta para Tesorería, sin marcar pagado.
- `OrderService::expireDue()` omite pedidos con intentos/transacciones no terminales o con pago aplicado. Los estados desconocidos se conservan como pendientes de resolución.
- Esto es lógica de dominio y pruebas; no habilita Pay-me, callbacks, rutas públicas ni cobros reales. `PAYME_ENABLED=false` permanece como valor predeterminado.


## Pendientes obligatorios antes de habilitar cobros

- **Intentos abandonados `initiated` (Fase 3):** definir cuándo reconciliarlos y cuándo cerrarlos. Consultar Pay-me servidor-servidor antes de marcar un intento terminal; el timeout por sí solo no demuestra ausencia de cargo. Si sigue incierto, no liberar el pedido para vencimiento ni iniciar otro cobro.
- **`EXTORNADO` sobre pedido pagado:** definir tratamiento contable/operativo con Tesorería, estado del pedido, historial, conciliación y eventual reembolso. No desmarcar `paid` ni borrar la aplicación automáticamente.
- **Escritura de evidencia:** solo el manejador de verificación servidor-servidor podrá persistir `verificado_en` y `fuente_verificacion`. La Fase 3 debe encapsular esta escritura y probar que callbacks de navegador, formularios y procesos no verificadores no pueden establecerla.
