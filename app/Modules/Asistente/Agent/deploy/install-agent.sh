#!/usr/bin/env bash
set -Eeuo pipefail

# Install the private FastAPI/MCP agent on a Linux systemd host.
# Run as root from the checked-out project. This script does not deploy Laravel.

PROJECT_ROOT="${PROJECT_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../../../" && pwd)}"
VENV_DIR="/opt/undc-asistente/venv"
ENV_DIR="/etc/undc-asistente"
ENV_FILE="${ENV_DIR}/agent.env"
UNIT_FILE="/etc/systemd/system/undc-asistente-agent.service"
PORT="${ASSISTANT_AGENT_PORT:-8091}"

fail() { echo "ERROR: $*" >&2; exit 1; }
[[ "$(id -u)" -eq 0 ]] || fail "Ejecuta con sudo/root."
[[ -f "${PROJECT_ROOT}/artisan" ]] || fail "No encuentro artisan en PROJECT_ROOT=${PROJECT_ROOT}."
[[ -f "${PROJECT_ROOT}/.env" ]] || fail "No existe ${PROJECT_ROOT}/.env. Configura Laravel primero."
command -v python3.11 >/dev/null || fail "Se requiere Python 3.11 (python3.11)."
command -v systemctl >/dev/null || fail "Se requiere systemd."
command -v openssl >/dev/null || fail "Se requiere openssl."
command -v curl >/dev/null || fail "Se requiere curl."
command -v runuser >/dev/null || fail "Se requiere runuser."

SERVICE_USER="${SERVICE_USER:-$(stat -c '%U' "${PROJECT_ROOT}/.env")}"
[[ -n "${SERVICE_USER}" && "${SERVICE_USER}" != "root" ]] || fail "Define SERVICE_USER con el usuario no-root que ejecuta Laravel, por ejemplo SERVICE_USER=undcdeploy."
id "${SERVICE_USER}" >/dev/null 2>&1 || fail "El usuario ${SERVICE_USER} no existe."
SERVICE_GROUP="$(id -gn "${SERVICE_USER}")"
[[ "${PORT}" =~ ^[0-9]+$ ]] && (( PORT >= 1024 && PORT <= 65535 )) || fail "Puerto inválido: ${PORT}"

echo "Proyecto: ${PROJECT_ROOT}"
echo "Usuario del servicio: ${SERVICE_USER}:${SERVICE_GROUP}"
echo "Puerto local del agente: ${PORT}"
echo
echo "La clave Gemini se guardará solo en ${ENV_FILE}; nunca se escribirá en Git."
read -r -s -p "Pega la GEMINI_API_KEY (no se mostrará): " GEMINI_API_KEY
echo
[[ -n "${GEMINI_API_KEY}" && "${GEMINI_API_KEY}" != *$'\n'* ]] || fail "La clave Gemini es obligatoria."
read -r -p "Modelo Gemini principal (ID vigente habilitado en tu cuenta): " GEMINI_MODEL
[[ -n "${GEMINI_MODEL}" && "${GEMINI_MODEL}" != *[[:space:]]* ]] || fail "Indica un ID de modelo válido, sin espacios."
read -r -p "Modelos alternativos separados por coma (opcional): " GEMINI_FALLBACK_MODELS
[[ "${GEMINI_FALLBACK_MODELS}" != *$'\n'* ]] || fail "La lista de modelos no puede contener saltos de línea."

ASSISTANT_AGENT_TOKEN="$(openssl rand -hex 32)"
DEFAULT_LARAVEL_URL="${LARAVEL_INTERNAL_BASE_URL:-http://127.0.0.1:8000}"
read -r -p "URL base de Laravel accesible desde este servidor [${DEFAULT_LARAVEL_URL}]: " INPUT_LARAVEL_URL
LARAVEL_INTERNAL_BASE_URL="${INPUT_LARAVEL_URL:-${DEFAULT_LARAVEL_URL}}"
[[ "${LARAVEL_INTERNAL_BASE_URL}" =~ ^https?://[^[:space:]]+$ ]] || fail "Indica una URL http(s) válida, sin espacios."
ASSISTANT_MAX_TOOL_ROUNDS="${ASSISTANT_MAX_TOOL_ROUNDS:-5}"
export GEMINI_API_KEY GEMINI_MODEL GEMINI_FALLBACK_MODELS ASSISTANT_AGENT_TOKEN PORT LARAVEL_INTERNAL_BASE_URL ASSISTANT_MAX_TOOL_ROUNDS

install -d -m 0750 -o root -g "${SERVICE_GROUP}" "${ENV_DIR}"
install -d -m 0755 -o root -g root /opt/undc-asistente
python3.11 -m venv "${VENV_DIR}"
"${VENV_DIR}/bin/python" -m pip install --upgrade pip
"${VENV_DIR}/bin/pip" install -r "${PROJECT_ROOT}/app/Modules/Asistente/Agent/requirements.txt"

# Write the Python process secrets as a root-owned, group-readable systemd EnvironmentFile.
python3.11 - "${ENV_FILE}" <<'PY'
import os, pathlib, sys
path = pathlib.Path(sys.argv[1])
keys = ("GEMINI_API_KEY", "GEMINI_MODEL", "GEMINI_FALLBACK_MODELS",
        "ASSISTANT_AGENT_TOKEN", "LARAVEL_INTERNAL_BASE_URL",
        "ASSISTANT_MAX_TOOL_ROUNDS")
for key in keys:
    value = os.environ.get(key, "")
    if any(ch in value for ch in "\\r\\n") or '"' in value:
        raise SystemExit(f"Valor no permitido para {key}")
content = "".join(f'{key}="{os.environ.get(key, "")}"\\n' for key in keys)
path.write_text(content, encoding="utf-8")
os.chmod(path, 0o640)
PY
chown root:"${SERVICE_GROUP}" "${ENV_FILE}"

# Keep Laravel and FastAPI on the exact same generated token.
python3.11 - "${PROJECT_ROOT}/.env" <<'PY'
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
        if text and not text.endswith("\\n"):
            text += "\\n"
        text += line + "\\n"
path.write_text(text, encoding="utf-8")
PY

cat > "${UNIT_FILE}" <<UNIT
[Unit]
Description=UNDC Asistente FastAPI and MCP agent
Wants=network-online.target
After=network-online.target

[Service]
Type=simple
User=${SERVICE_USER}
Group=${SERVICE_GROUP}
WorkingDirectory=${PROJECT_ROOT}
EnvironmentFile=${ENV_FILE}
Environment=PYTHONDONTWRITEBYTECODE=1
ExecStart=${VENV_DIR}/bin/uvicorn app.Modules.Asistente.Agent.main:app --host 127.0.0.1 --port ${PORT} --workers 1
Restart=on-failure
RestartSec=5
TimeoutStartSec=30
UMask=0077
NoNewPrivileges=true
PrivateTmp=true
ProtectHome=true
ProtectSystem=strict
ReadOnlyPaths=${PROJECT_ROOT}

[Install]
WantedBy=multi-user.target
UNIT
chmod 0644 "${UNIT_FILE}"

systemctl daemon-reload
systemctl enable --now undc-asistente-agent.service
sleep 2
systemctl is-active --quiet undc-asistente-agent.service || {
    systemctl --no-pager --full status undc-asistente-agent.service || true
    fail "El servicio no inició. Revisa journalctl -u undc-asistente-agent -n 80."
}
curl --fail --silent "http://127.0.0.1:${PORT}/health" >/dev/null || fail "El health check local falló."

# Cache Laravel configuration as the deployment owner so cache permissions stay correct.
runuser -u "${SERVICE_USER}" -- sh -c "cd '${PROJECT_ROOT}' && php artisan config:cache"

echo
echo "Agente Asistente instalado y activo."
echo "Servicio: systemctl status undc-asistente-agent"
echo "Logs:     journalctl -u undc-asistente-agent -f"
echo "Health:   curl http://127.0.0.1:${PORT}/health"
echo "Puerto:   solo loopback; no abrirlo en firewall ni publicarlo en Nginx."
echo "Token:    generado automáticamente y sincronizado entre Laravel y FastAPI."
echo "Clave Gemini: guardada en ${ENV_FILE} con permisos restringidos."
