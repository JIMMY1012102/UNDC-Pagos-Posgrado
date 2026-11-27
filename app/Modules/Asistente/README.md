# Módulo Asistente

Responsabilidad: conversación asistida por Gemini con herramientas MCP de lectura, sin persistencia propia ni tablas.

## Estructura

- `Contracts/AssistantProvider.php`: contrato del proveedor.
- `Http/Controllers/AssistantController.php`: validación del endpoint público.
- `Services/AssistantService.php`: coordinación del caso de uso.
- `Services/AgentAssistantProvider.php`: adaptador Laravel hacia FastAPI, con compatibilidad Gemini HTTP mientras el agente no esté configurado.
- `Services/GeminiAssistantProvider.php`: adaptador heredado y cadena de modelos de respaldo.
- `Agent/`: aplicación Python/FastAPI, integración Gemini y servidor MCP.
- `Agent/mcp_server.py`: herramientas MCP que consumen la API de Catálogo.
- `routes.php`: endpoint propio del módulo, cargado desde el grupo API.

## Flujo

Navegador → Laravel (validación y límite de frecuencia) → FastAPI (token interno) → Gemini (selección de modelo) → cliente MCP → herramientas de lectura → API pública de Catálogo Laravel.

El agente Python se ejecuta como proceso independiente por tratarse de otro runtime, pero todo el código del contexto funcional queda agrupado en esta carpeta del módulo. Instrucciones de instalación y ejecución: `Agent/README.md`.

## Herramientas MCP

- `listar_oficinas_catalogo`: consulta las oficinas publicadas.
- `consultar_catalogo_oficina`: consulta conceptos publicados de una oficina.

Las herramientas usan exclusivamente los endpoints existentes del módulo Catálogo. No consultan directamente MariaDB ni tablas `tm_*`, no crean pedidos y no procesan pagos. Las futuras herramientas de Pedidos requieren contratos y autorización explícitos.

## Configuración

- `ASSISTANT_AGENT_URL`: URL interna FastAPI, por defecto `http://127.0.0.1:8091`.
- `ASSISTANT_AGENT_TOKEN`: secreto compartido Laravel/Python; obligatorio para activar el agente.
- `GEMINI_API_KEY`, `GEMINI_MODEL`, `GEMINI_FALLBACK_MODELS`: disponibles en el entorno del proceso Python.
- `LARAVEL_INTERNAL_BASE_URL`: URL local de Laravel consumida por el servidor MCP.

Si el token no está configurado, Laravel conserva temporalmente el proveedor HTTP Gemini existente. Una vez configurado, las solicitudes pasan por FastAPI y MCP. No versionar credenciales, no exponer el puerto interno del agente y no añadir secretos al repositorio.

No se agregan tablas, migraciones ni dependencias Composer.
