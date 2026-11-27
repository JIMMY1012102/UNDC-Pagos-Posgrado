# Despliegue del agente Asistente (Linux)

Este procedimiento instala **solo el proceso Python FastAPI/MCP**. No hace `git pull`, no despliega Laravel, no ejecuta migraciones ni toca pagos. El checkout debe estar actualizado y tener `.env` configurado.

## Opción A: instalación como usuario (sin sudo)

Usa esta opción cuando el usuario de despliegue no tiene permisos administrativos. Requisitos:

- Python 3.10 o posterior, con `venv` disponible.
- PHP CLI y acceso de escritura del usuario al `.env` y a `bootstrap/cache` de Laravel.
- `curl`, `flock`, `crontab` y acceso HTTPS saliente a Google Gemini.
- El usuario puede ejecutar `crontab`. Para arrancar automáticamente tras reiniciar, el daemon `cron`/`crond` del sistema debe estar activo. Un usuario sin privilegios no puede activar ese daemon si está detenido.

Desde el checkout actualizado:

```bash
cd /var/www/undc-pagos-nuevo
bash app/Modules/Asistente/Agent/deploy/install-agent-user.sh
```

No uses `sudo` con este instalador. Solicita de forma oculta la clave Gemini, el modelo principal, modelos alternativos opcionales y la URL base de Laravel accesible desde el propio servidor. Si Laravel no escucha en `127.0.0.1:8000`, introduce la URL real o interna que el servidor pueda alcanzar.

El instalador:

1. Crea el entorno virtual en `~/.local/share/undc-asistente/venv` usando el Python disponible (incluido Python 3.12).
2. Instala las dependencias solo dentro de ese entorno.
3. Guarda las variables privadas en `~/.config/undc-asistente/agent.env`, con permisos `0600`.
4. Genera un token aleatorio y sincroniza el mismo valor con `ASSISTANT_AGENT_TOKEN` y `ASSISTANT_AGENT_URL` en el `.env` de Laravel.
5. Crea un lanzador con bloqueo para evitar procesos duplicados y registra una tarea `@reboot` en el crontab del usuario.
6. Inicia el agente ahora y comprueba `/health`.
7. Reconstruye la caché de configuración con `php artisan config:cache`.

El proceso escucha únicamente en `127.0.0.1:8091` por defecto. No abras ese puerto en el firewall ni lo publiques en Nginx. La clave y el token no se imprimen ni se guardan en Git.

### Verificación y operación sin sudo

```bash
curl --fail http://127.0.0.1:8091/health
crontab -l
tail -n 80 ~/.local/state/undc-asistente/agent.log
```

El arranque automático tras reinicio depende de que el daemon cron esté activo. Si el instalador advierte que cron no está activo, el agente seguirá funcionando durante la sesión actual, pero el administrador deberá activar cron para habilitar `@reboot`; alternativamente, puedes iniciar manualmente el lanzador después de reiniciar:

```bash
~/.local/share/undc-asistente/start-agent.sh >> ~/.local/state/undc-asistente/agent.log 2>&1 &
```

El endpoint `/health` solo comprueba que FastAPI responde; no realiza una llamada facturable a Gemini. La primera consulta desde el chat comprueba Gemini y MCP.

## Opción B: instalación administrada con systemd

Usa esta opción si tienes acceso root/sudo y quieres que systemd administre el proceso.

Requisitos: Ubuntu/Debian con systemd, Python 3.11 como `python3.11`, PHP CLI, `openssl`, `curl`, `runuser` y permisos administrativos.

```bash
cd /var/www/undc-pagos-nuevo
sudo SERVICE_USER=undcdeploy bash app/Modules/Asistente/Agent/deploy/install-agent.sh
```

Este instalador crea el entorno en `/opt/undc-asistente/venv`, guarda secretos en `/etc/undc-asistente/agent.env` (root:grupo-servicio, modo 0640), configura `undc-asistente-agent.service`, verifica `/health` y reconstruye la caché de Laravel.

Verificación:

```bash
sudo systemctl status undc-asistente-agent --no-pager
sudo journalctl -u undc-asistente-agent -n 80 --no-pager
curl --fail http://127.0.0.1:8091/health
```

## Configuración y límites de seguridad

Laravel utiliza:

```dotenv
ASSISTANT_AGENT_URL=http://127.0.0.1:8091
ASSISTANT_AGENT_TOKEN=<mismo-token-aleatorio>
```

El agente requiere `GEMINI_API_KEY`, `GEMINI_MODEL`, `GEMINI_FALLBACK_MODELS` (opcional), `ASSISTANT_AGENT_TOKEN`, `LARAVEL_INTERNAL_BASE_URL` y `ASSISTANT_MAX_TOOL_ROUNDS=5`. Ambos instaladores sincronizan el token automáticamente.

- No ejecutar `php artisan migrate` como parte de esta instalación.
- No copiar archivos de secretos al repositorio ni enviarlos por chat.
- No abrir el puerto del agente a Internet.
- El agente usa exclusivamente herramientas MCP de lectura del Catálogo; no consulta MariaDB ni tablas `tm_*`, no crea pedidos y no procesa pagos.
- Si PHP-FPM usa otro usuario, asegúrate de que pueda leer el `.env` y que la configuración de Laravel esté cacheada; no hagas el archivo globalmente legible.
- Ninguno de estos instaladores actualiza Laravel ni instala dependencias globales.
