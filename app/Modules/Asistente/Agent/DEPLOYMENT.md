# Despliegue del agente Asistente (Linux + systemd)

Este procedimiento instala **solo el proceso Python FastAPI/MCP**. No hace `git pull`, no despliega Laravel, no ejecuta migraciones ni toca pagos. Debe ejecutarse después de actualizar el checkout de la aplicación a una versión que incluya el módulo Asistente.

## Requisitos

- Ubuntu/Debian con systemd.
- Python 3.11 disponible como `python3.11`.
- PHP CLI con las mismas extensiones y configuración que usa Laravel.
- El usuario del servicio debe poder leer el checkout y Laravel debe poder leer su propio `.env`.
- Acceso HTTPS saliente a Google Gemini y acceso local al endpoint API de Laravel.
- Ejecutar el instalador como root mediante sudo.

## Instalación de un solo paso

Desde el checkout actualizado, por ejemplo:

```bash
cd /var/www/undc-pagos-nuevo
sudo SERVICE_USER=undcdeploy bash app/Modules/Asistente/Agent/deploy/install-agent.sh
```

Cambia `undcdeploy` por el usuario real que ejecuta Laravel/PHP-FPM si fuera distinto. El script pregunta de forma oculta la clave Gemini, solicita el modelo principal y la lista opcional de modelos alternativos, y pide la URL base de Laravel que el servidor pueda alcanzar. Si Laravel no escucha en `127.0.0.1:8000`, escribe la URL real del sitio (por ejemplo, `https://tu-dominio`) o la URL interna configurada en Nginx.

El instalador:

1. Crea un entorno virtual aislado en `/opt/undc-asistente/venv`.
2. Instala las dependencias de `Agent/requirements.txt`.
3. Genera un token aleatorio de 256 bits.
4. Guarda la clave Gemini y el token del agente en `/etc/undc-asistente/agent.env` (root:grupo-servicio, modo 0640).
5. Escribe el mismo token y la URL local en el `.env` de Laravel.
6. Crea y habilita `undc-asistente-agent.service` con systemd.
7. Deja Uvicorn escuchando únicamente en `127.0.0.1`.
8. Verifica `/health` y reconstruye la caché de configuración de Laravel como usuario de despliegue.

No se imprime ni se versiona la clave Gemini o el token. No copiar `agent.env` al repositorio ni enviarlo por chat.

## Verificación posterior

```bash
sudo systemctl status undc-asistente-agent --no-pager
sudo journalctl -u undc-asistente-agent -n 80 --no-pager
curl --fail http://127.0.0.1:8091/health
```

El endpoint `/health` solo verifica que FastAPI responde; no realiza una llamada facturable a Gemini. La primera consulta real desde el chat valida Gemini y MCP.

## Configuración manual equivalente

Laravel:

```dotenv
ASSISTANT_AGENT_URL=http://127.0.0.1:8091
ASSISTANT_AGENT_TOKEN=<mismo-token-aleatorio>
```

Entorno del proceso Python, almacenado fuera del repositorio en `/etc/undc-asistente/agent.env`:

```dotenv
GEMINI_API_KEY=<clave-privada>
GEMINI_MODEL=<id-de-modelo-habilitado>
GEMINI_FALLBACK_MODELS=<modelo-alternativo-1,modelo-alternativo-2>
ASSISTANT_AGENT_TOKEN=<mismo-token-aleatorio>
LARAVEL_INTERNAL_BASE_URL=<URL-base-de-Laravel-accesible-desde-el-servidor>
ASSISTANT_MAX_TOOL_ROUNDS=5
```

El instalador sincroniza automáticamente el token. Si se cambia manualmente, debe ser idéntico en ambos procesos y luego reiniciar FastAPI y reconstruir la caché Laravel.

## Operación

```bash
sudo systemctl restart undc-asistente-agent
sudo systemctl stop undc-asistente-agent
sudo systemctl disable undc-asistente-agent
```

El agente usa únicamente herramientas MCP de lectura del Catálogo. No consultar directamente MariaDB ni tablas `tm_*`, no crear pedidos ni procesar pagos.

## Importante

- El puerto 8091 no debe exponerse a Internet ni añadirse a Nginx; Laravel se comunica por loopback.
- El token se genera al instalar. Para rotarlo, ejecutar el instalador de nuevo y verificar que el agente y la caché Laravel queden sincronizados.
- No ejecutar `php artisan migrate` como parte de esta instalación.
- Si el usuario de PHP-FPM no puede leer el `.env`, corrige permisos/grupos según la política del servidor; no hagas el archivo globalmente legible.
- El instalador necesita que el proyecto ya tenga `.env` y Laravel instalado. No instala ni actualiza la aplicación Laravel.
