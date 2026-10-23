# Despliegue de demostración: GitHub Actions → Oracle Cloud

Este repositorio tiene dos flujos:
- `.github/workflows/ci.yml`: pruebas de Laravel y compilación frontend tras cambios en `main` o pull requests.
- `.github/workflows/deploy-demo.yml`: despliegue **manual** a Oracle. Solo pasará a automático tras comprobar el primer despliegue.

## Ámbito de seguridad

Este es un **entorno personal de demostración, servido únicamente por HTTP**, por decisión del responsable de la demo. Debe operar exclusivamente con **datos ficticios**. No usar Google OAuth real, contraseñas personales de estudiantes, SISPAGOS institucional de producción, credenciales de pasarelas reales ni pagos reales. La universidad debe aprobar y operar separadamente su ambiente productivo.

La aplicación se despliega en `/var/www/undc-pagos`. No modifica configuraciones de Nginx ni otros directorios.

## Preparación inicial del servidor (una sola vez)

Ejecutar por SSH con el usuario `ubuntu`. Verificar antes que `/var/www/undc-pagos` sea únicamente la demo nueva.

```bash
sudo adduser --disabled-password --gecos '' undcdeploy
sudo install -d -m 700 -o undcdeploy -g undcdeploy /home/undcdeploy/.ssh

sudo chown -R undcdeploy:www-data /var/www/undc-pagos
sudo install -d -m 2775 -o undcdeploy -g www-data /var/www/undc-pagos/storage
sudo install -d -m 2775 -o undcdeploy -g www-data /var/www/undc-pagos/bootstrap/cache
sudo -u undcdeploy mkdir -p /var/www/undc-pagos/storage/{app/private,app/public,framework/cache/data,framework/sessions,framework/views,logs}
sudo chgrp -R www-data /var/www/undc-pagos/storage /var/www/undc-pagos/bootstrap/cache
sudo chmod -R g+rwX /var/www/undc-pagos/storage /var/www/undc-pagos/bootstrap/cache
sudo find /var/www/undc-pagos/storage /var/www/undc-pagos/bootstrap/cache -type d -exec chmod g+s {} +

sudo -u undcdeploy bash -c '
  umask 077
  {
    echo "APP_NAME=\"UNDC Pagos Demo\""
    echo "APP_ENV=production"
    echo "APP_DEBUG=false"
    echo "APP_URL=http://undc-pagos.163-176-230-238.sslip.io"
    echo "APP_KEY=base64:$(openssl rand -base64 32)"
    echo "APP_LOCALE=es"
    echo "LOG_CHANNEL=single"
    echo "DB_CONNECTION=sqlite"
    echo "SESSION_DRIVER=file"
    echo "CACHE_STORE=file"
    echo "QUEUE_CONNECTION=sync"
  } > /var/www/undc-pagos/.env
'
```

La configuración demo usa drivers de sesión y caché por archivos. **No ejecutamos migraciones automáticas contra la base compartida SISPAGOS**. `DB_CONNECTION=sqlite` es provisional; no activar funcionalidad que consulte BD hasta configurar un entorno de prueba aislado y compatible.

## Clave SSH de despliegue

Crear una clave exclusiva y restringirla al usuario `undcdeploy`. Puede generarse en Codespaces y registrar la clave pública en `/home/undcdeploy/.ssh/authorized_keys`. No pegar claves privadas en chats, commits, issues ni documentación.

Verificar la identidad SSH del servidor mediante una fuente fiable (p. ej. archivo de clave pública leído directamente del servidor) y guardar la línea de known_hosts para `163.176.230.238`.

En el repositorio GitHub, sección **Settings → Secrets and variables → Actions**, registrar:

| Secreto | Contenido |
| --- | --- |
| `ORACLE_DEPLOY_KEY` | Clave privada SSH exclusiva del usuario `undcdeploy` |
| `ORACLE_KNOWN_HOSTS` | Línea `163.176.230.238 ssh-ed25519 AAAA...` obtenida de forma fiable del servidor |

Nunca utilizar para este flujo la clave SSH personal de administración de la instancia Oracle.

## Primera ejecución y validación

1. Confirmar que `undcdeploy` puede conectarse por SSH y escribir **solo** en `/var/www/undc-pagos`.
2. En **GitHub → Actions → Desplegar demo en Oracle Cloud (manual) → Run workflow**, elegir `main`.
3. Revisar que los pasos de pruebas, compilación, rsync y verificación HTTP concluyan correctamente.
4. Abrir `http://undc-pagos.163-176-230-238.sslip.io` y confirmar que muestra Laravel.
5. Únicamente entonces activar el evento `push` a `main` en el flujo `deploy-demo.yml`.

## Qué conserva el despliegue

El proceso usa `rsync --delete` **limitado al directorio UNDC**, excluyendo `.env`, `storage/` y `database/database.sqlite`, para conservar la configuración y datos de prueba. No copia `node_modules`, GitHub Actions ni los tests. No ejecuta migraciones ni activa HTTPS.

El servidor Oracle tiene ~1 GiB de RAM; Composer, Node.js, npm y la compilación se ejecutan en GitHub Actions, no en Oracle.

## Diagnóstico

- Si falla CI: revisar dependencias y tests antes de desplegar.
- Si falla SSH: comprobar secretos, huella de host, usuario `undcdeploy`, permisos y puerto 22.
- Si falla rsync: comprobar propiedad de `/var/www/undc-pagos` y existencia de `.env` y `storage`.
- Si falla HTTP: revisar `sudo nginx -t`, `sudo journalctl -u php8.3-fpm`, permisos y logs de Laravel. No habilitar `APP_DEBUG=true` en la demo pública.
