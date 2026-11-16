# Revisión previa a Pay-me — rama Claud

- Precios oficiales y disponibilidad de cobro separados: `PricingPolicy`, `PAYME_ENABLED=false` por defecto. No se implementa un cobro real en esta revisión.
- Catálogo expone `orderable` y `orderable_reason`; el carrito rechaza conceptos que la cotización no permite comprar.
- Vinculación serializada por comprador: fusiona conceptos y beneficiarios iguales, consume el token y conserva un solo carrito activo. Si la suma supera 20, revierte toda la operación con 422, sin perder selecciones.
- Nueva migración incremental: unicidad de carrito activo mediante columna generada, unicidad de pedido por carrito e índice de vencimiento. Si ya hay duplicados, se detiene antes del DDL: requiere conciliación explícita, sin borrar historial.
- `market:expire`, cada cinco minutos: actualizaciones en lotes de 200 con eventos de vencimiento en la misma transacción. Retira carritos caducados y revoca tokens, conservando sus líneas para trazabilidad. No elimina pedidos ni evidencia de pago.
- Listado: carga por lote las líneas, historial y evidencia de pagos; máximo diez pedidos y una fila adicional para paginación.

## Operación

La nueva migración debe revisarse y aplicarse de manera coordinada al desplegar. No se ejecuta contra bases institucionales desde esta rama. El servidor necesita el cron estándar de Laravel (`php artisan schedule:run` cada minuto) para ejecutar la tarea; registrar el horario en código no instala el cron del servidor. Las notificaciones futuras de Pay-me deben verificar importe, identidad e idempotencia y reconciliar pagos tardíos incluso si el pedido ya venció. Esta rama no implementa webhooks.

No se elimina ninguna tabla: la revisión recibida no identifica cuál sería prescindible y borrar estructura de pagos/tesorería excedería estos cinco ajustes.
