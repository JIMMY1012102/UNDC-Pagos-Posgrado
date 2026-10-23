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
