# Pantalla de medios de pago

Implementación del 6 de noviembre de 2026 sobre `feat/undc-market-avance-local`.

Al pulsar «Realizar pago» con sesión y perfil completo, se abre la revisión del carrito con los datos del comprador, conceptos seleccionables, resumen y opciones de tarjeta o billetera electrónica. Se conserva el carrito al marcar o desmarcar conceptos. Las instrucciones se actualizan según el medio elegido; cambiar la selección oculta las instrucciones anteriores. La vista usa las variables de tema de la tienda y dispone de reglas adaptables para móvil.

## Alcance

- Vista de demostración basada en las referencias compartidas; no es una integración con UCV ni Pay-me.
- Los importes continúan siendo referenciales. Los conceptos institucionales sin precio y las tarifas por unidad producen «Por confirmar», sin inventar un monto fijo.
- «Ver instrucciones» solo muestra información local. No solicita datos de tarjeta, genera QR pagable, inicia pedidos, crea autorizaciones ni actualiza estados de pago.
- No se incluye un contador ficticio: la vigencia y el QR deberán proceder del proveedor cuando exista un intento de pago real.
- Los datos del comprador se consultan con la API existente; no se copian los datos personales de las capturas al código.

## Validación

- `node --check public/js/undc-tienda.js`: correcto.
- `node --test tests/frontend/market-pages.cjs`: 13 pruebas aprobadas, incluidas selección, total vacío, conservación del carrito, instrucciones sin llamadas de pago, cambio de medio e importes por confirmar.
- No se ejecutó `php artisan test`: el entorno de edición no dispone de PHP/Composer ni de las dependencias Laravel. Ejecutar la suite antes de integrar.
- No se completó la revisión visual en Chromium: Playwright está disponible pero su ejecutable de navegador no está instalado. No se instalaron dependencias. Revisar escritorio, móvil y temas antes de integrar.

## Integración posterior

Validar tarifas en servidor y generar pedido pendiente; conectar el proveedor autorizado; generar intentos y su vigencia; mostrar QR o formulario alojado del proveedor; verificar el resultado en servidor con idempotencia; mantener seguimiento de pendientes, rechazados, confirmados e inciertos. Un clic del usuario o una redirección nunca debe marcar un pago como realizado.
