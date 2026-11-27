# Agente Python + MCP

Este proceso es parte del módulo funcional `Asistente`, pero se ejecuta separado de PHP. FastAPI recibe las solicitudes internas de Laravel; Gemini decide cuándo utilizar herramientas MCP; el servidor MCP ofrece exclusivamente lecturas del catálogo público a través de la API existente de Laravel.

## Desarrollo local

Desde la raíz del repositorio:

```bash
python3 -m venv .venv-asistente
source .venv-asistente/bin/activate
pip install -r app/Modules/Asistente/Agent/requirements.txt
export GEMINI_API_KEY="..."
export GEMINI_MODEL="modelo-disponible"
export ASSISTANT_AGENT_TOKEN="token-largo-aleatorio"
export LARAVEL_INTERNAL_BASE_URL="http://127.0.0.1:8000"
uvicorn app.Modules.Asistente.Agent.main:app --host 127.0.0.1 --port 8091
```

Laravel debe usar el mismo token en `ASSISTANT_AGENT_TOKEN` y llamar a `http://127.0.0.1:8091`. No exponer el puerto 8091 a Internet. En servidores, ejecutar con un usuario de servicio, un entorno virtual aislado y un supervisor (systemd); no instalar paquetes globalmente.

## Herramientas MCP incluidas

- `listar_oficinas_catalogo`: usa `GET /api/v1/tienda/catalogo/oficinas`.
- `consultar_catalogo_oficina`: usa `GET /api/v1/tienda/catalogo?office_id=N`.

Las herramientas no consultan MariaDB ni tablas `tm_*`, no escriben datos, no crean pedidos y no procesan pagos. La cotización mostrada procede de la API del módulo Catálogo. Añadir herramientas de pedidos exige primero definir contratos de lectura y autorización con el módulo correspondiente.

## Seguridad

- FastAPI escucha solo en loopback por defecto.
- `/messages` exige Bearer token interno; `/health` no expone configuración.
- La clave Gemini solo se configura en el entorno del proceso Python.
- No versionar claves, tokens ni archivos de entorno.
