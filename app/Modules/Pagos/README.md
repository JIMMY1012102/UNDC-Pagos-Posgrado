# Pagos

Propietario de seis tablas: `tienda_intentos_pago`, `tienda_transacciones_pago`, `tienda_pagos_aplicados_pedidos`, `tienda_eventos_notificacion_pago`, `tienda_incidencias_pago`, `tienda_reembolsos`.

La base está preparada para intentos, verificaciones, incidencias y reembolsos, **no significa que Pay-me o cobros reales estén habilitados**. No exponer rutas hasta completar la integración autorizada y las verificaciones servidor-servidor.


## Núcleo de estados y aplicación (Fase 1)

- `AttemptStatus` distingue intentos abiertos de los terminales conocidos. Un estado desconocido nunca se considera terminal.
- `TransactionStatus::fromProviderState()` mapea los estados documentados del proveedor; un valor no reconocido queda como `uncertain` y nunca habilita la aplicación.
- `ApplyVerifiedPaymentService` bloquea transacción y pedido dentro de una transacción DB. Exige estado autorizable, referencia externa, fuente de verificación servidor-servidor y `verified_at`; compara monto en centavos y moneda; inserta una sola aplicación por pedido y solo entonces establece `paid_at` y `status=paid`.
- Repeticiones de la misma transacción son idempotentes. Un segundo cobro verificado, un importe/moneda distinto o un pago de un pedido vencido se registra como incidencia abierta para Tesorería, sin marcar pagado.
- `OrderService::expireDue()` omite pedidos con intentos/transacciones no terminales o con pago aplicado. Los estados desconocidos se conservan como pendientes de resolución.
- Esto es lógica de dominio y pruebas; no habilita Pay-me, callbacks, rutas públicas ni cobros reales. `PAYME_ENABLED=false` permanece como valor predeterminado.
