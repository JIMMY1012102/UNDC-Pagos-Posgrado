# Módulo Asistente

Responsabilidad: exponer un endpoint HTTP para enviar un mensaje a un proveedor de IA, sin acoplar el módulo a Gemini.

## Estructura

- `Contracts/AssistantProvider.php`: contrato del proveedor.
- `Services/AssistantService.php`: coordinación de la solicitud.
- `Services/GeminiAssistantProvider.php`: adaptador HTTP de Gemini con modelo principal y alternativo.
- `Http/Controllers/AssistantController.php`: validación y respuesta JSON.
- `Exceptions/AssistantUnavailable.php`: indisponibilidad comunicable al cliente.
- `routes.php`: ruta propia del módulo, cargada desde el grupo API.

## Endpoint

`POST /api/v1/asistente/mensajes`

Entrada JSON: `{"message":"..."}`. El mensaje es obligatorio y admite hasta 4.000 caracteres. La respuesta correcta entrega el texto en `data.answer`; errores de proveedor devuelven HTTP 503 con un mensaje amigable. La validación devuelve HTTP 422.

La ruta se carga como API (sin middleware CSRF de sesión), tiene límite de 20 solicitudes por minuto y no requiere acceso a los módulos de pagos o checkout.

## Configuración

Definir en el entorno privado:

- `GEMINI_API_KEY`: credencial de Gemini.
- `GEMINI_MODEL`: modelo principal.
- `GEMINI_FALLBACK_MODEL`: modelo alternativo opcional, por ejemplo otra versión compatible de Gemini Flash.

Primero se intenta el modelo principal. Si hay error de conexión, modelo no encontrado, cuota/rate limit, error temporal del servidor o respuesta vacía, se intenta el modelo alternativo. Los errores de autenticación o solicitudes inválidas no provocan reintentos innecesarios. Si ambos fallan, se devuelve HTTP 503. No se registra ni se devuelve al cliente la clave ni el detalle interno del proveedor.

Las pruebas usan `Http::fake()`; nunca llaman a Gemini real. El módulo no crea tablas ni migraciones.

## Fuera de alcance

La carpeta `Tools/` y la herramienta `consultar_tarifa` corresponden a la Fase 2. Esa futura herramienta deberá reutilizar `Catalogo/Services/OrderQuoteService` para obtener el mismo importe que cotiza Pedidos, sin consultar directamente tablas `tm_*`.
