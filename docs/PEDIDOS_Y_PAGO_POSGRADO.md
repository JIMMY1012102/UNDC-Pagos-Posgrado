# Pedidos y revisión de pago de Posgrado

## Alcance implementado
Carrito identificado → revisión de tarifas aprobadas → confirmación explícita → pedido pendiente → Mis pedidos y detalle con historial. El código UNDC identifica el pedido, no es una autorización de pago. Generar el pedido o pagar un concepto no inicia automáticamente el trámite académico.

Se reutilizan `tienda_pedidos`, `tienda_detalles_pedido`, `tienda_historial_estados_pedido` y las tablas de carrito existentes. No hay migraciones nuevas ni cambios a `tm_*`.

- Catalogo cotiza leyendo los datos canónicos mediante el adaptador Sispagos. Exige una sola versión PEN vigente, positiva, aprobada y con referencia de aprobación en `tienda_versiones_tarifas`. Nunca usa el costo legado o importes del navegador como tarifa pagable. Conceptos que requieren elegibilidad siguen bloqueados hasta implementar esa validación.
- Pedidos autentica y filtra por propietario; no consulta directamente las tablas históricas. Guarda una instantánea del comprador, conceptos, cantidades e importes calculados en centavos. No admite beneficiarios distintos al comprador en esta etapa.
- Generar el pedido convierte el carrito. Una transacción y bloqueo de ese carrito serializan reintentos: el mismo carrito devuelve el mismo pedido, aunque el perfil o las tarifas cambien después.
- Vencimiento inicial: 24 horas. Se materializa el estado `expired` al consultar el pedido o su página de historial; un cambio condicional registra la transición una sola vez. No hay tarea programada nueva. No se expira un pedido ya pagado.
- Mis pedidos muestra 10 pedidos por página. Los detalles y cotizaciones se sirven con `Cache-Control: no-store`.

## Rutas
- El carrito activo vinculado permite agregar y quitar conceptos mediante las mismas rutas de carrito, usando la sesión del propietario sin token anónimo. Un token consumido no vuelve a ser válido. Se conserva el límite de 20 unidades y el bloqueo transaccional; carritos vencidos o convertidos no se editan.
- `POST /api/v1/tienda/pedidos/cotizar`: `{cart_id}`; valida carrito activo propio y cotiza.
- `POST /api/v1/tienda/pedidos`: `{cart_id}`; vuelve a verificar tarifas y perfil dentro de la transacción.
- `GET /api/v1/tienda/pedidos?page=1`: historial del propietario.
- `GET /api/v1/tienda/pedidos/{code}`: detalle, estado, vencimiento e historial del propietario.
- `GET /tienda/mis-pedidos`: pantalla autenticada.
Todas las API requieren sesión y tienen límite de peticiones; escrituras sujetas al middleware web/CSRF existente.

## Pendiente para cobrar
La selección de tarjeta y billetera electrónica es una preparación visual. `payment_enabled` permanece falso: no se crea sesión bancaria, QR de cobro, transacción ni intento ficticio. No se reciben datos de tarjeta ni códigos de Yape. No existe endpoint que permita al navegador establecer `paid`.

Para habilitar cobros se requiere el contrato y credenciales de la pasarela autorizada, documentación técnica, tarifas aprobadas y validación académica donde corresponda. El siguiente incremento en Pagos debe implementar un adaptador de proveedor, creación idempotente de intento, vencimiento coordinado con la sesión bancaria, notificaciones autenticadas y verificación servidor-servidor de referencia, monto y moneda, registro de cobros inciertos/tardíos y una sola aplicación al pedido. Una redirección o captura del cliente nunca confirma un pago.

La aprobación de tarifas es institucional; este cambio no aprueba ni inserta tarifas por su cuenta. Si no existen versiones aprobadas, el comprador ve el motivo y no puede generar el pedido.

## Validación
Pruebas HTTP nuevas: importe adulterado, precio legado distinto, tarifas faltantes/ambiguas, perfil incompleto, elegibilidad pendiente, publicación retirada, carrito vencido, aislamiento de usuarios, reintentos, instantáneas y vencimiento sin marcar pagado. Pruebas JavaScript: cotización del servidor, texto sin HTML, doble clic y bloqueo sin tarifas.
