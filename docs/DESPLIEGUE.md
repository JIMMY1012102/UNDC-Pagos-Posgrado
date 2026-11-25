# Actualización manual de Oracle — DEMO

## Decisión vigente
Desarrollo en laptop con Codex; GitHub para ramas/PR y CI de pruebas; Oracle **solo se actualiza manualmente**, cuando el responsable decida publicar. No hay despliegue automático configurado.

## Preparación pendiente
Los intentos anteriores de `rsync` fallaron por permisos en `/var/www/undc-pagos/bootstrap`. Es posible que el directorio contenga archivos copiados parcialmente y una página HTML provisional. **Todavía no se puede asumir que es un clon Git válido**.

Antes de ejecutar `git pull` en Oracle, inspeccionar la carpeta exclusiva `/var/www/undc-pagos`, respaldar `.env` y `storage/`, resolver permisos locales de `bootstrap`, configurar acceso seguro del servidor al repositorio privado y establecer una copia Git limpia. Hacerlo en una sesión SSH autorizada y sin tocar directorios de otros proyectos.

## Publicación posterior
1. Validar localmente, abrir PR y fusionar a `main`.
2. Ingresar por SSH a Oracle; comprobar la ruta y estado de Git.
3. Solo en el clon ya preparado, `git pull --ff-only origin main`.
4. Actualizar dependencias/build **si corresponde**, considerando que la instancia tiene ~1 GiB RAM.
5. Evaluar migraciones y respaldos separadamente; nunca alterar SISPAGOS productivo por defecto.
6. Revisar cachés de Laravel, Nginx/PHP-FPM y prueba HTTP.

## Seguridad y alcance
La demo es HTTP con **datos ficticios exclusivamente**. Sin Google OAuth real, datos estudiantiles personales, pasarela real, webhooks ni base institucional. No modificar Wachihualito, RSU-CNI o PerúCompras.

Eliminar los secretos de Actions `ORACLE_DEPLOY_KEY` y `ORACLE_KNOWN_HOSTS` y revocar en Oracle la clave pública de despliegue `undc-github-actions` si aún permanecen. Conservar las claves normales de administración.


## Actualización de columnas de tienda (PR #138)

La migración `2026_11_24_000001_rename_store_business_columns_to_spanish.php` solo renombra columnas propias de `tienda_*`. No se despliega mediante `deploy-oracle.yml`; la publicación Oracle continúa siendo manual y requiere autorización del responsable.

### Comprobaciones previas

1. Confirmar que la versión del motor es compatible con la probada en CI (MariaDB 10.11):
   `SELECT VERSION();`. Si la versión difiere, validar compatibilidad antes de continuar.
2. Respaldar la base y comprobar que el archivo existe y no está vacío. Se recomienda probar la restauración en una base temporal antes de intervenir la demo.
3. Respaldar también `.env` y `storage/`; operar solo en `/var/www/undc-pagos`.

### Publicación manual

Ejecutar en una sesión SSH autorizada. Definir un nombre de respaldo único y revisar cada salida:

```bash
cd /var/www/undc-pagos &&
BACKUP_FILE="backup_tesoreria_dev_antes_columnas_$(date +%F_%H%M%S).sql" &&
mysqldump --single-transaction --routines --triggers tesoreria_dev > "$BACKUP_FILE" &&
test -s "$BACKUP_FILE" &&
SELECT_VERSION="SELECT VERSION();" &&
mysql tesoreria_dev -Nse "$SELECT_VERSION" &&
test -z "$(git status --porcelain)" &&
php artisan down &&
git pull --ff-only origin main &&
composer install --no-dev --optimize-autoloader &&
php artisan migrate --force &&
php artisan optimize:clear &&
php artisan config:cache &&
php artisan route:cache &&
php artisan view:cache &&
php artisan queue:restart &&
php artisan up
```

El resultado de `SELECT VERSION();` debe revisarse y compararse con MariaDB 10.11 del CI antes de iniciar la publicación. `test -s` confirma que el dump no está vacío; no sustituye una prueba de restauración. La comprobación de `git status --porcelain` exige un clon limpio antes del pull. La cadena con `&&` impide ejecutar `php artisan up` si falla cualquier paso anterior; ante un error, el modo mantenimiento permanece activo.

### Si migrate falla

- Mantener la aplicación en modo mantenimiento y conservar la salida/logs del error.
- **NO ejecutar `php artisan migrate:rollback`**: la migración fallida puede no estar registrada en `migrations`, y ese rollback revertiría la migración anterior que sí está registrada.
- Revisar cuidadosamente `SHOW CREATE TABLE` de las tablas afectadas y el contenido de la tabla `migrations`.
- La migración es reanudable: si cada columna está íntegramente en su estado antiguo o nuevo, volver a ejecutar `php artisan migrate --force`; la validación detecta conflictos (origen y destino presentes, o ambos ausentes) antes de aplicar DDL.
- Si el esquema presenta un conflicto no resoluble con seguridad, o no se puede determinar el estado, detenerse y restaurar el respaldo validado mediante el procedimiento autorizado. No improvisar renombrados ni alterar tablas `tm_*`.

### Checklist posterior

- [ ] Login e inicio de sesión.
- [ ] Registro de cuenta.
- [ ] Catálogo y carrito.
- [ ] Perfil del comprador.
- [ ] Creación de un pedido de prueba.
- [ ] Consulta de **Mis pedidos**.
- [ ] Verificar logs, colas y respuesta HTTP.

Esta guía no autoriza desplegar ni fusionar el PR.
