#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_ROOT="\${PROJECT_ROOT:-$(cd "$(dirname "\${BASH_SOURCE[0]}")/../../../../../" && pwd)}"
PORT="\${ASSISTANT_AGENT_PORT:-8091}"
HOME_DIR="\${HOME:?HOME no está definido}"
DATA_DIR="\${HOME_DIR}/.local/share/undc-asistente"
STATE_DIR="\${HOME_DIR}/.local/state/undc-asistente"
CONFIG_DIR="\${HOME_DIR}/.config/undc-asistente"
VENV_DIR="\${DATA_DIR}/venv"
ENV_FILE="\${CONFIG_DIR}/agent.env"
START_SCRIPT="\${DATA_DIR}/start-agent.sh"
LOG_FILE="\${STATE_DIR}/agent.log"
CRON_MARKER="# undc-asistente-agent"

fail() { echo "ERROR: $*" >&2; exit 1; }
[[ "$(id -u)" -ne 0 ]] || fail "Ejecuta como usuario normal, sin sudo."
[[ -f "\${PROJECT_ROOT}/artisan" ]] || fail "No encuentro artisan en \${PROJECT_ROOT}."
[[ -f "\${PROJECT_ROOT}/.env" ]] || fail "No existe \${PROJECT_ROOT}/.env."
[[ -w "\${PROJECT_ROOT}/.env" ]] || fail "No puedes modificar \${PROJECT_ROOT}/.env."
for cmd in python3 curl flock crontab php; do command -v "$cmd" >/dev/null || fail "Falta el comando: $cmd"; done
python3 -m venv --help >/dev/null 2>&1 || fail "El módulo venv no está disponible."
[[ "\${PORT}" =~ ^[0-9]+$ ]] && (( PORT >= 1024 && PORT <= 65535 )) || fail "Puerto inválido."

echo "Proyecto: \${PROJECT_ROOT}"
echo "Usuario: $(id -un)"
echo "Python: $(python3 --version)"
echo "Instalación privada: \${DATA_DIR}"
echo
echo "La clave Gemini se guardará en \${ENV_FILE} con permisos 600."
read -r -s -p "Pega la GEMINI_API_KEY (no se mostrará): " GEMINI_API_KEY
echo
[[ -n "\${GEMINI_API_KEY}" && "\${GEMINI_API_KEY}" != *$'\n'* ]] || fail "La clave Gemini es obligatoria."
read -r -p "Modelo Gemini principal (ID habilitado en tu cuenta): " GEMINI_MODEL
[[ -n "\${GEMINI_MODEL}" && "\${GEMINI_MODEL}" != *[[:space:]]* ]] || fail "ID de modelo inválido."
read -r -p "Modelos alternativos separados por coma (opcional): " GEMINI_FALLBACK_MODELS
[[ "\${GEMINI_FALLBACK_MODELS}" != *$'\n'* ]] || fail "Lista de modelos inválida."

DEFAULT_LARAVEL_URL="\${LARAVEL_INTERNAL_BASE_URL:-http://127.0.0.1:8000}"
read -r -p "URL base de Laravel accesible desde este servidor [\${DEFAULT_LARAVEL_URL}]: " INPUT_LARAVEL_URL
LARAVEL_INTERNAL_BASE_URL="\${INPUT_LARAVEL_URL:-\${DEFAULT_LARAVEL_URL}}"
[[ "\${LARAVEL_INTERNAL_BASE_URL}" =~ ^https?://[^[:space:]]+$ ]] || fail "URL Laravel inválida."

ASSISTANT_AGENT_TOKEN="$(python3 -c 'import secrets; print(secrets.token_hex(32))')"
ASSISTANT_MAX_TOOL_ROUNDS="\${ASSISTANT_MAX_TOOL_ROUNDS:-5}"
export GEMINI_API_KEY GEMINI_MODEL GEMINI_FALLBACK_MODELS ASSISTANT_AGENT_TOKEN PORT LARAVEL_INTERNAL_BASE_URL ASSISTANT_MAX_TOOL_ROUNDS

install -d -m 0700 "\${CONFIG_DIR}" "\${DATA_DIR}" "\${STATE_DIR}"
python3 -m venv "\${VENV_DIR}"
"\${VENV_DIR}/bin/python" -m pip install --upgrade pip
"\${VENV_DIR}/bin/pip" install -r "\${PROJECT_ROOT}/app/Modules/Asistente/Agent/requirements.txt"
"\${VENV_DIR}/bin/pip" check

"\${VENV_DIR}/bin/python" - "\${ENV_FILE}" <<'PY'
import os, pathlib, shlex, sys
path = pathlib.Path(sys.argv[1])
keys = ("GEMINI_API_KEY", "GEMINI_MODEL", "GEMINI_FALLBACK_MODELS",
        "ASSISTANT_AGENT_TOKEN", "LARAVEL_INTERNAL_BASE_URL",
        "ASSISTANT_MAX_TOOL_ROUNDS")
content = "".join(f"export {key}={shlex.quote(os.environ.get(key, ''))}\n" for key in keys)
content += f"export ASSISTANT_AGENT_PORT={shlex.quote(os.environ.get('PORT', '8091'))}\n"
path.write_text(content, encoding="utf-8")
path.chmod(0o600)
PY

"\${VENV_DIR}/bin/python" - "\${PROJECT_ROOT}/.env" <<'PY'
import os, pathlib, re, sys
path = pathlib.Path(sys.argv[1])
text = path.read_text(encoding="utf-8")
updates = {
    "ASSISTANT_AGENT_URL": f"http://127.0.0.1:{os.environ['PORT']}",
    "ASSISTANT_AGENT_TOKEN": os.environ["ASSISTANT_AGENT_TOKEN"],
}
for key, value in updates.items():
    line = f"{key}={value}"
    pattern = re.compile(rf"(?m)^{re.escape(key)}=.*$")
    if pattern.search(text):
        text = pattern.sub(lambda _: line, text, count=1)
    else:
        text = text + ("" if not text or text.endswith("\n") else "\n") + line + "\n"
path.write_text(text, encoding="utf-8")
PY

# Generate a launcher with fixed absolute paths and a lock to prevent duplicate agents.
"\${VENV_DIR}/bin/python" - "\${START_SCRIPT}" "\${PROJECT_ROOT}" "\${ENV_FILE}" "\${VENV_DIR}" "\${DATA_DIR}/agent.lock" "\${PORT}" <<'PY'
import pathlib, shlex, sys
script, project, env_file, venv, lock, port = sys.argv[1:]
q = shlex.quote
content = f"""#!/usr/bin/env bash
set -Eeuo pipefail
cd {q(project)}
source {q(env_file)}
exec flock -n {q(lock)} {q(venv + '/bin/uvicorn')} app.Modules.Asistente.Agent.main:app --host 127.0.0.1 --port {q(port)} --workers 1
"""
path = pathlib.Path(script)
path.write_text(content, encoding="utf-8")
path.chmod(0o700)
PY

# Preserve the user's existing crontab and replace only this installer's entry.
CRON_TMP="$(mktemp)"
trap 'rm -f "\${CRON_TMP}"' EXIT
(crontab -l 2>/dev/null || true) | grep -Fv "\${CRON_MARKER}" > "\${CRON_TMP}" || true
printf -v CRON_COMMAND '%q >> %q 2>&1 &' "\${START_SCRIPT}" "\${LOG_FILE}"
printf '@reboot /bin/bash -lc %q %s\n' "\${CRON_COMMAND}" "\${CRON_MARKER}" >> "\${CRON_TMP}"
crontab "\${CRON_TMP}"

# Refresh Laravel's cached configuration as the deployment user.
(cd "\${PROJECT_ROOT}" && php artisan config:cache)

# Start now; the cron entry will start it again after a server reboot.
nohup "\${START_SCRIPT}" >> "\${LOG_FILE}" 2>&1 </dev/null &
for _ in $(seq 1 25); do
    if curl --fail --silent "http://127.0.0.1:\${PORT}/health" >/dev/null; then
        break
    fi
    sleep 1
done
curl --fail --silent "http://127.0.0.1:\${PORT}/health" >/dev/null || {
    echo "ERROR: FastAPI no respondió. Revisa: \${LOG_FILE}" >&2
    exit 1
}

echo
echo "Agente instalado en modo usuario y respondiendo en 127.0.0.1:\${PORT}."
echo "Entorno privado: \${ENV_FILE}"
echo "Logs: \${LOG_FILE}"
if ps -eo comm | grep -Eq '^(cron|crond)
echo "Comprobar: curl http://127.0.0.1:\${PORT}/health"
echo "No se abrió ningún puerto público ni se ejecutaron migraciones."
; then
    echo "Arranque automático: crontab @reboot (daemon cron detectado)."
else
    echo "AVISO: no se detecta cron/crond activo. El agente funciona ahora, pero el arranque tras reinicio requiere que el administrador active cron o que lo inicies manualmente."
fi
echo "Comprobar: curl http://127.0.0.1:\${PORT}/health"
echo "No se abrió ningún puerto público ni se ejecutaron migraciones."
