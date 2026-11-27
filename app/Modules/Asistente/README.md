# Módulo Asistente

Responsabilidad: exponer un endpoint HTTP para enviar un mensaje a un proveedor de IA, sin acoplar el módulo a Gemini.

## Estructura

- `Contracts/AssistantProvider.php`: contrato del proveedor.
- `Services/AssistantService.php`: coordinación de la solicitud.
- `Services/GeminiAssistantProvider.php`: adaptador HTTP de Gemini y selección secuencial de modelos.
- `Http/Controllers/AssistantController.php`: validación y respuesta JSON.
- `Exceptions/AssistantUnavailable.php`: indisponibilidad comunicable al cliente.
- `routes.php`: ruta propia del módulo, cargada desde el grupo API.

## Endpoint

`POST /api/v1/asistente/mensajes`

Entrada JSON: `{"message":"..."}`. El mensaje es obligatorio y admite hasta 4.000 caracteres. La respuesta correcta entrega el texto en `data.answer`; errores de proveedor devuelven HTTP 503 con un mensaje amigable. La validación devuelve HTTP 422.

La ruta se carga como API (sin middleware CSRF de sesión), tiene límite de 20 solicitudes por minuto y no requiere acceso a los módulos de pagos o checkout.

## Configuración de modelos

Definir en el entorno privado:

- `GEMINI_API_KEY`: credencial de Gemini.
- `GEMINI_MODEL`: modelo principal.
- `GEMINI_FALLBACK_MODELS`: lista opcional de modelos alternativos, separada por comas y en orden de prioridad.
- `GEMINI_FALLBACK_MODEL`: variable singular heredada; se conserva por compatibilidad si todavía existe una configuración anterior.

Ejemplo de cadena con cuatro intentos (un principal y tres respaldos):

```dotenv
GEMINI_MODEL=gemini-2.5-flash
GEMINI_FALLBACK_MODELS=gemini-3.5-flash-lite,gemini-3.6-flash,gemini-3.5-flash
```

Los identificadores del ejemplo proceden de pruebas anteriores en otro proyecto. Antes de habilitarlos en producción, confirmar que estén disponibles para el proyecto y la clave de Google AI usados por este despliegue.

El proveedor intenta los modelos en orden ante errores de conexión, HTTP 404, 408, 429, errores 5xx o respuestas sin texto. Los errores de autenticación y solicitudes inválidas no provocan reintentos, porque cambiar de modelo no los corrige. Si ningún modelo responde, devuelve HTTP 503 sin exponer detalles internos ni credenciales.

La lista admite tantos respaldos como se configuren; la prueba automatizada verifica cuatro intentos en orden. Esta cadena mejora la tolerancia a límites y saturación de un modelo, pero no garantiza disponibilidad si el servidor pierde Internet o si todos los modelos comparten una cuota agotada.

Las pruebas usan `Http::fake()`; nunca llaman a Gemini real. El módulo no crea tablas ni migraciones.

## Fuera de alcance

La carpeta `Tools/` y la herramienta `consultar_tarifa` corresponden a la Fase 2. Esa futura herramienta deberá reutilizar `Catalogo/Services/OrderQuoteService` para obtener el mismo importe que cotiza Pedidos, sin consultar directamente tablas `tm_*`.
