# Módulo Asistente

Responsabilidad: exponer un endpoint HTTP para enviar un mensaje a un proveedor de IA, sin acoplar el módulo a Gemini.

## Estructura

- `Contracts/AssistantProvider.php`: contrato del proveedor.
- `Services/AssistantService.php`: coordinación de la solicitud.
- `Services/GeminiAssistantProvider.php`: adaptador HTTP de Gemini.
- `Http/Controllers/AssistantController.php`: validación y respuesta JSON.
- `Exceptions/AssistantUnavailable.php`: indisponibilidad comunicable al cliente.
- `routes.php`: ruta propia del módulo.

## Endpoint

`POST /api/v1/asistente/mensajes`

Entrada JSON: `{"message":"..."}`. El mensaje es obligatorio y admite hasta 4.000 caracteres. La respuesta correcta entrega el texto en `data.answer`; errores de proveedor devuelven HTTP 503 con un mensaje amigable. La validación devuelve HTTP 422.

La ruta tiene límite de 20 solicitudes por minuto y no requiere acceso a los módulos de pagos o checkout.

## Configuración

Definir `GEMINI_API_KEY` y `GEMINI_MODEL` en el entorno privado. Sus valores se leen desde `config/services.php`; no se versionan credenciales ni se fija un modelo en el código. Sin ambos valores, el servicio responde como no disponible.

Las pruebas usan `Http::fake()`; nunca llaman a Gemini real. El módulo no crea tablas ni migraciones.

## Fuera de alcance

La carpeta `Tools/` y la herramienta `consultar_tarifa` corresponden a la Fase 2. Esa futura herramienta deberá reutilizar `Catalogo/Services/OrderQuoteService` para obtener el mismo importe que cotiza Pedidos, sin consultar directamente tablas `tm_*`.
